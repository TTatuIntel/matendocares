<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\Patient;
use App\Models\DoctorPatient;
use App\Models\EmergencyContact;
use App\Models\EmergencyProtocol;
use App\Models\EmergencyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class EmergencyController extends Controller
{
    /**
     * Display emergency dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found.');
        }

        // Get patient IDs that this doctor has access to
        $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->pluck('patient_id');

        try {
            // Active emergency alerts
            $activeAlerts = Alert::whereIn('patient_id', $patientIds)
                ->where('severity', 'critical')
                ->where('status', 'active')
                ->with(['patient.user'])
                ->orderBy('triggered_at', 'desc')
                ->get();

            // Recent emergency responses
            $recentResponses = EmergencyResponse::whereHas('alert', function($query) use ($patientIds) {
                $query->whereIn('patient_id', $patientIds);
            })->with(['alert.patient.user', 'respondedBy'])
                ->orderBy('responded_at', 'desc')
                ->take(10)
                ->get();

            // Emergency statistics
            $stats = [
                'active_alerts' => $activeAlerts->count(),
                'today_alerts' => Alert::whereIn('patient_id', $patientIds)
                    ->whereDate('triggered_at', today())
                    ->count(),
                'this_week_alerts' => Alert::whereIn('patient_id', $patientIds)
                    ->where('triggered_at', '>=', now()->startOfWeek())
                    ->count(),
                'avg_response_time' => $this->getAverageResponseTime($patientIds),
                'critical_patients' => Alert::whereIn('patient_id', $patientIds)
                    ->where('severity', 'critical')
                    ->where('status', 'active')
                    ->distinct('patient_id')
                    ->count()
            ];

            // High-risk patients requiring monitoring
            $highRiskPatients = Patient::whereIn('id', $patientIds)
                ->whereHas('alerts', function($query) {
                    $query->where('severity', 'critical')
                        ->where('triggered_at', '>=', now()->subHours(24));
                })
                ->with(['user', 'latestVitalSign'])
                ->get();

            return view('doctor.emergency.index', compact(
                'activeAlerts',
                'recentResponses', 
                'stats',
                'highRiskPatients'
            ));

        } catch (\Exception $e) {
            \Log::error('Emergency Dashboard Error: ' . $e->getMessage());
            
            return view('doctor.emergency.index', [
                'activeAlerts' => collect(),
                'recentResponses' => collect(),
                'stats' => [
                    'active_alerts' => 0,
                    'today_alerts' => 0,
                    'this_week_alerts' => 0,
                    'avg_response_time' => 0,
                    'critical_patients' => 0
                ],
                'highRiskPatients' => collect()
            ]);
        }
    }

    /**
     * Display all alerts
     */
    public function alerts(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->pluck('patient_id');

        $query = Alert::whereIn('patient_id', $patientIds)
            ->with(['patient.user']);

        // Filter by severity
        if ($request->has('severity') && $request->severity !== 'all') {
            $query->where('severity', $request->severity);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('triggered_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('triggered_at', '<=', $request->date_to);
        }

        // Default to recent alerts
        if (!$request->has('date_from') && !$request->has('show_all')) {
            $query->where('triggered_at', '>=', now()->subDays(30));
        }

        $alerts = $query->orderBy('triggered_at', 'desc')->paginate(20);

        // Alert statistics
        $alertStats = [
            'total' => Alert::whereIn('patient_id', $patientIds)->count(),
            'active' => Alert::whereIn('patient_id', $patientIds)
                ->where('status', 'active')->count(),
            'critical' => Alert::whereIn('patient_id', $patientIds)
                ->where('severity', 'critical')->count(),
            'resolved_today' => Alert::whereIn('patient_id', $patientIds)
                ->where('status', 'resolved')
                ->whereDate('resolved_at', today())->count()
        ];

        return view('doctor.emergency.alerts', compact('alerts', 'alertStats'));
    }

    /**
     * Trigger emergency alert
     */
    public function triggerAlert(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'type' => 'required|in:vital_signs,medication,behavioral,environmental,system',
            'severity' => 'required|in:low,medium,high,critical',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'trigger_source' => 'nullable|string|max:100',
            'recommended_action' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $request->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to trigger alert for this patient.'
            ], 403);
        }

        try {
            $alert = Alert::create([
                'patient_id' => $request->patient_id,
                'type' => $request->type,
                'severity' => $request->severity,
                'title' => $request->title,
                'message' => $request->message,
                'status' => 'active',
                'triggered_at' => now(),
                'triggered_by' => $user->id,
                'trigger_source' => $request->trigger_source ?? 'manual',
                'recommended_action' => $request->recommended_action
            ]);

            // Send notifications based on severity
            $this->sendEmergencyNotifications($alert);

            // Execute emergency protocols if applicable
            $this->executeEmergencyProtocols($alert);

            return response()->json([
                'success' => true,
                'message' => 'Emergency alert triggered successfully.',
                'alert_id' => $alert->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Emergency Alert Trigger Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error triggering emergency alert.'
            ], 500);
        }
    }

    /**
     * Respond to emergency alert
     */
    public function respondToAlert(Request $request, Alert $alert)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this alert's patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $alert->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to respond to this alert.'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'response_type' => 'required|in:acknowledged,investigating,resolved,escalated',
            'response_notes' => 'nullable|string|max:1000',
            'action_taken' => 'nullable|string|max:1000',
            'escalate_to' => 'nullable|string|max:255',
            'follow_up_required' => 'nullable|boolean',
            'follow_up_date' => 'nullable|date|after:now'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create emergency response record
            $response = EmergencyResponse::create([
                'alert_id' => $alert->id,
                'responded_by' => $user->id,
                'response_type' => $request->response_type,
                'response_notes' => $request->response_notes,
                'action_taken' => $request->action_taken,
                'escalate_to' => $request->escalate_to,
                'follow_up_required' => $request->follow_up_required ?? false,
                'follow_up_date' => $request->follow_up_date,
                'responded_at' => now()
            ]);

            // Update alert status based on response type
            $alertStatus = match($request->response_type) {
                'acknowledged' => 'acknowledged',
                'investigating' => 'investigating',
                'resolved' => 'resolved',
                'escalated' => 'escalated',
                default => $alert->status
            };

            $alert->update([
                'status' => $alertStatus,
                'last_response_at' => now(),
                'resolved_at' => $request->response_type === 'resolved' ? now() : null,
                'resolved_by' => $request->response_type === 'resolved' ? $user->id : null
            ]);

            // Handle escalation if required
            if ($request->response_type === 'escalated' && $request->escalate_to) {
                $this->handleAlertEscalation($alert, $request->escalate_to);
            }

            // Create follow-up task if required
            if ($request->follow_up_required && $request->follow_up_date) {
                $this->createFollowUpTask($alert, $request->follow_up_date);
            }

            return response()->json([
                'success' => true,
                'message' => 'Emergency response recorded successfully.',
                'response_id' => $response->id
            ]);

        } catch (\Exception $e) {
            \Log::error('Emergency Response Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error recording emergency response.'
            ], 500);
        }
    }

    /**
     * Display emergency protocols
     */
    public function protocols()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        try {
            // Get emergency protocols relevant to doctor's specialty
            $protocols = EmergencyProtocol::where('status', 'active')
                ->where(function($query) use ($doctor) {
                    $query->whereNull('specialty')
                        ->orWhere('specialty', $doctor->specialization ?? 'general');
                })
                ->orderBy('severity', 'desc')
                ->orderBy('type')
                ->get();

            // Group protocols by type
            $protocolsByType = $protocols->groupBy('type');

            // Recent protocol updates
            $recentUpdates = EmergencyProtocol::where('updated_at', '>=', now()->subDays(30))
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get();

            return view('doctor.emergency.protocols', compact('protocolsByType', 'recentUpdates'));

        } catch (\Exception $e) {
            \Log::error('Emergency Protocols Error: ' . $e->getMessage());
            return view('doctor.emergency.protocols', [
                'protocolsByType' => collect(),
                'recentUpdates' => collect()
            ]);
        }
    }

    /**
     * Get emergency contacts for patient
     */
    public function getEmergencyContacts(Patient $patient)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to patient emergency contacts.'
            ], 403);
        }

        try {
            $contacts = EmergencyContact::where('patient_id', $patient->id)
                ->where('status', 'active')
                ->orderBy('priority')
                ->get();

            return response()->json([
                'success' => true,
                'contacts' => $contacts
            ]);

        } catch (\Exception $e) {
            \Log::error('Emergency Contacts Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving emergency contacts.'
            ], 500);
        }
    }

    /**
     * Initiate emergency call
     */
    public function initiateEmergencyCall(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'contact_id' => 'nullable|exists:emergency_contacts,id',
            'call_type' => 'required|in:emergency_services,emergency_contact,family,hospital',
            'reason' => 'required|string|max:500',
            'phone_number' => 'nullable|string|max:20'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $request->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to initiate call for this patient.'
            ], 403);
        }

        try {
            // Log the emergency call initiation
            \Log::info('Emergency call initiated', [
                'doctor_id' => $doctor->id,
                'patient_id' => $request->patient_id,
                'call_type' => $request->call_type,
                'reason' => $request->reason,
                'contact_id' => $request->contact_id,
                'phone_number' => $request->phone_number
            ]);

            // Here you would integrate with a telephony service
            // For now, we'll just return success with call details

            $callDetails = [
                'call_id' => 'CALL_' . uniqid(),
                'initiated_at' => now()->toISOString(),
                'call_type' => $request->call_type,
                'status' => 'initiated'
            ];

            // Get contact details if contact_id provided
            if ($request->contact_id) {
                $contact = EmergencyContact::find($request->contact_id);
                $callDetails['contact_name'] = $contact->name;
                $callDetails['contact_phone'] = $contact->phone;
            } else {
                $callDetails['phone_number'] = $request->phone_number;
            }

            return response()->json([
                'success' => true,
                'message' => 'Emergency call initiated successfully.',
                'call_details' => $callDetails
            ]);

        } catch (\Exception $e) {
            \Log::error('Emergency Call Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error initiating emergency call.'
            ], 500);
        }
    }

    /**
     * Get emergency statistics
     */
    public function getEmergencyStats()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('status', 'active')
            ->pluck('patient_id');

        try {
            $stats = [
                'active_alerts' => Alert::whereIn('patient_id', $patientIds)
                    ->where('status', 'active')->count(),
                'critical_alerts' => Alert::whereIn('patient_id', $patientIds)
                    ->where('severity', 'critical')
                    ->where('status', 'active')->count(),
                'alerts_today' => Alert::whereIn('patient_id', $patientIds)
                    ->whereDate('triggered_at', today())->count(),
                'avg_response_time' => $this->getAverageResponseTime($patientIds),
                'alerts_by_type' => Alert::whereIn('patient_id', $patientIds)
                    ->where('triggered_at', '>=', now()->subDays(30))
                    ->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray(),
                'resolution_rate' => $this->getResolutionRate($patientIds)
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Emergency Stats Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving emergency statistics.'
            ], 500);
        }
    }
}