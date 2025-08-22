<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Patient;
use App\Models\DoctorPatient;
use App\Models\VitalSign;
use App\Models\Alert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Meds;
use App\Models\Document;

class PatientMonitorController extends Controller
{
    /**
     * Display list of patients for monitoring
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')->with('error', 'Doctor profile not found.');
        }

        try {
            // Get patients assigned to this doctor
            $query = Patient::whereHas('doctorPatients', function($q) use ($doctor) {
                $q->where('doctor_id', $doctor->id)
                  ->where('status', 'active');
            })->with([
                'user',
                'latestVitalSign',
                'alerts' => function($q) {
                    $q->where('status', 'active')
                      ->orderBy('triggered_at', 'desc')
                      ->take(3);
                }
            ]);

            // Apply filters
            if ($request->has('status') && $request->status !== 'all') {
                $query->whereHas('latestVitalSign', function($q) use ($request) {
                    switch($request->status) {
                        case 'critical':
                            $q->where(function($subQ) {
                                $subQ->where('systolic_bp', '>', 180)
                                     ->orWhere('diastolic_bp', '>', 110)
                                     ->orWhere('heart_rate', '>', 120)
                                     ->orWhere('heart_rate', '<', 50)
                                     ->orWhere('oxygen_saturation', '<', 90)
                                     ->orWhere('temperature', '>', 39);
                            });
                            break;
                        case 'warning':
                            $q->where(function($subQ) {
                                $subQ->whereBetween('systolic_bp', [140, 180])
                                     ->orWhereBetween('diastolic_bp', [90, 110])
                                     ->orWhereBetween('heart_rate', [100, 120])
                                     ->orWhereBetween('heart_rate', [50, 60])
                                     ->orWhereBetween('oxygen_saturation', [90, 95])
                                     ->orWhereBetween('temperature', [37.5, 39]);
                            });
                            break;
                        case 'normal':
                            $q->where('systolic_bp', '<=', 140)
                              ->where('diastolic_bp', '<=', 90)
                              ->whereBetween('heart_rate', [60, 100])
                              ->where('oxygen_saturation', '>=', 95)
                              ->where('temperature', '<=', 37.5);
                            break;
                    }
                });
            }

            // Search by patient name
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Sort patients
            $sortBy = $request->get('sort', 'last_reading');
            switch($sortBy) {
                case 'name':
                    $query->join('users', 'patients.user_id', '=', 'users.id')
                          ->orderBy('users.name');
                    break;
                case 'status':
                    // Custom sort by health status
                    $query->leftJoin('vital_signs as vs', function($join) {
                        $join->on('patients.id', '=', 'vs.patient_id')
                             ->whereRaw('vs.id = (SELECT MAX(id) FROM vital_signs WHERE patient_id = patients.id)');
                    })->orderByRaw('
                        CASE
                            WHEN vs.systolic_bp > 180 OR vs.diastolic_bp > 110 OR vs.heart_rate > 120 OR vs.heart_rate < 50 OR vs.oxygen_saturation < 90 OR vs.temperature > 39 THEN 1
                            WHEN vs.systolic_bp BETWEEN 140 AND 180 OR vs.diastolic_bp BETWEEN 90 AND 110 OR vs.heart_rate BETWEEN 100 AND 120 OR vs.heart_rate BETWEEN 50 AND 60 OR vs.oxygen_saturation BETWEEN 90 AND 95 OR vs.temperature BETWEEN 37.5 AND 39 THEN 2
                            ELSE 3
                        END
                    ');
                    break;
                case 'last_reading':
                default:
                    $query->leftJoin('vital_signs as vs2', function($join) {
                        $join->on('patients.id', '=', 'vs2.patient_id')
                             ->whereRaw('vs2.id = (SELECT MAX(id) FROM vital_signs WHERE patient_id = patients.id)');
                    })->orderBy('vs2.measured_at', 'desc');
                    break;
            }

            $patients = $query->paginate(15);

            // Get summary statistics
            $stats = $this->getPatientStats($doctor->id);

            return view('doctor.patients.index', compact('patients', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Patient Monitor Index Error: ' . $e->getMessage());
            return view('doctor.patients.index', ['patients' => collect(), 'stats' => []]);
        }
    }

    /**
     * Show detailed patient monitoring view
     */
    // public function show($patientId)
    // {
    //     $user = auth()->user();
    //     $doctor = $user->doctor;

    //     // Verify doctor has access to this patient
    //     $patient = Patient::whereHas('doctorPatients', function($q) use ($doctor) {
    //         $q->where('doctor_id', $doctor->id)
    //           ->where('status', 'active');
    //     })->with([
    //         'user',
    //         'vitalSigns' => function($q) {
    //             $q->orderBy('measured_at', 'desc')->take(50);
    //         },
    //         'alerts' => function($q) {
    //             $q->orderBy('triggered_at', 'desc')->take(10);
    //         }
    //     ])->findOrFail($patientId);

    //     // Get latest vital signs
    //     $latestVitals = $patient->vitalSigns->first();

    //     // Calculate health score
    //     $healthScore = $this->calculateHealthScore($latestVitals);

    //     // Get vital signs trend (last 30 days)
    //     $vitalsTrend = $this->getVitalsTrend($patientId);

    //     // Get recent alerts
    //     $recentAlerts = $patient->alerts->take(5);

    //     // Get medication adherence data (mock for now)
    //     $medicationAdherence = $this->getMedicationAdherence($patientId);

    //     return view('doctor.patients.show', compact(
    //         'patient',
    //         'latestVitals',
    //         'healthScore',
    //         'vitalsTrend',
    //         'recentAlerts',
    //         'medicationAdherence'
    //     ));
    // }

    /**
     * Get real-time vital signs data for a patient
     */
    public function getVitalSigns($patientId)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify access
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $vitalSigns = VitalSign::where('patient_id', $patientId)
                ->orderBy('measured_at', 'desc')
                ->take(1)
                ->first();

            if (!$vitalSigns) {
                return response()->json(['message' => 'No vital signs found'], 404);
            }

            $healthScore = $this->calculateHealthScore($vitalSigns);
            $status = $this->determineHealthStatus($vitalSigns);

            return response()->json([
                'vital_signs' => $vitalSigns,
                'health_score' => $healthScore,
                'status' => $status,
                'timestamp' => $vitalSigns->measured_at->toISOString()
            ]);

        } catch (\Exception $e) {
            \Log::error('Get Vital Signs Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch vital signs'], 500);
        }
    }

    /**
     * Get patient alerts
     */
    public function getAlerts($patientId)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify access
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $alerts = Alert::where('patient_id', $patientId)
                ->where('status', 'active')
                ->orderBy('triggered_at', 'desc')
                ->take(10)
                ->get()
                ->map(function($alert) {
                    return [
                        'id' => $alert->id,
                        'type' => $alert->type,
                        'severity' => $alert->severity,
                        'message' => $alert->message,
                        'triggered_at' => $alert->triggered_at->diffForHumans(),
                        'status' => $alert->status
                    ];
                });

            return response()->json(['alerts' => $alerts]);

        } catch (\Exception $e) {
            \Log::error('Get Alerts Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch alerts'], 500);
        }
    }

    /**
     * Update alert status
     */
    public function updateAlert(Request $request, $alertId)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        try {
            $alert = Alert::findOrFail($alertId);

            // Verify doctor has access to this alert's patient
            $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
                ->where('patient_id', $alert->patient_id)
                ->where('status', 'active')
                ->exists();

            if (!$hasAccess) {
                return response()->json(['error' => 'Access denied'], 403);
            }

            $alert->update([
                'status' => $request->status,
                'acknowledged_by' => $user->id,
                'acknowledged_at' => now(),
                'notes' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Alert Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update alert'], 500);
        }
    }

    /**
     * Get historical vital signs data
     */
    public function getHistoricalData($patientId, Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify access
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $days = $request->get('days', 30);
            $vitalType = $request->get('type', 'all');

            $query = VitalSign::where('patient_id', $patientId)
                ->where('measured_at', '>=', now()->subDays($days))
                ->orderBy('measured_at');

            $vitalSigns = $query->get();

            $data = $vitalSigns->map(function($vital) use ($vitalType) {
                $result = [
                    'timestamp' => $vital->measured_at->toISOString(),
                    'date' => $vital->measured_at->format('Y-m-d H:i')
                ];

                if ($vitalType === 'all' || $vitalType === 'blood_pressure') {
                    $result['systolic_bp'] = $vital->systolic_bp;
                    $result['diastolic_bp'] = $vital->diastolic_bp;
                }

                if ($vitalType === 'all' || $vitalType === 'heart_rate') {
                    $result['heart_rate'] = $vital->heart_rate;
                }

                if ($vitalType === 'all' || $vitalType === 'temperature') {
                    $result['temperature'] = $vital->temperature;
                }

                if ($vitalType === 'all' || $vitalType === 'oxygen_saturation') {
                    $result['oxygen_saturation'] = $vital->oxygen_saturation;
                }

                return $result;
            });

            return response()->json(['data' => $data]);

        } catch (\Exception $e) {
            \Log::error('Get Historical Data Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch historical data'], 500);
        }
    }

    /**
     * Export patient data
     */
    public function exportPatientData($patientId, Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify access
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $patient = Patient::with('user')->findOrFail($patientId);
            $days = $request->get('days', 30);

            $vitalSigns = VitalSign::where('patient_id', $patientId)
                ->where('measured_at', '>=', now()->subDays($days))
                ->orderBy('measured_at')
                ->get();

            $filename = 'patient_data_' . $patient->user->name . '_' . now()->format('Y-m-d') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($vitalSigns) {
                $file = fopen('php://output', 'w');

                // Headers
                fputcsv($file, [
                    'Date', 'Time', 'Systolic BP', 'Diastolic BP',
                    'Heart Rate', 'Temperature', 'Oxygen Saturation'
                ]);

                // Data
                foreach ($vitalSigns as $vital) {
                    fputcsv($file, [
                        $vital->measured_at->format('Y-m-d'),
                        $vital->measured_at->format('H:i:s'),
                        $vital->systolic_bp,
                        $vital->diastolic_bp,
                        $vital->heart_rate,
                        $vital->temperature,
                        $vital->oxygen_saturation
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Export Patient Data Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to export data'], 500);
        }
    }

    // Private helper methods

    /**
     * Get patient statistics for dashboard
     */
    private function getPatientStats($doctorId)
    {
        $patientIds = DoctorPatient::where('doctor_id', $doctorId)
            ->where('status', 'active')
            ->pluck('patient_id');

        $totalPatients = $patientIds->count();

        // Get patients with recent vital signs (last 7 days)
        $activePatients = VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(7))
            ->distinct('patient_id')
            ->count();

        // Count critical patients
        $criticalPatients = VitalSign::whereIn('patient_id', $patientIds)
            ->where('measured_at', '>=', now()->subDays(1))
            ->where(function($q) {
                $q->where('systolic_bp', '>', 180)
                  ->orWhere('diastolic_bp', '>', 110)
                  ->orWhere('heart_rate', '>', 120)
                  ->orWhere('heart_rate', '<', 50)
                  ->orWhere('oxygen_saturation', '<', 90)
                  ->orWhere('temperature', '>', 39);
            })
            ->distinct('patient_id')
            ->count();

        // Count active alerts
        $activeAlerts = Alert::whereIn('patient_id', $patientIds)
            ->where('status', 'active')
            ->count();

        return [
            'total_patients' => $totalPatients,
            'active_patients' => $activePatients,
            'critical_patients' => $criticalPatients,
            'active_alerts' => $activeAlerts,
            'activity_rate' => $totalPatients > 0 ? round(($activePatients / $totalPatients) * 100, 1) : 0
        ];
    }

    /**
     * Calculate health score based on vital signs
     */
    private function calculateHealthScore($vitalSigns)
    {
        if (!$vitalSigns) return 0;

        $score = 100;

        // Blood pressure scoring
        if ($vitalSigns->systolic_bp > 180 || $vitalSigns->diastolic_bp > 110) {
            $score -= 25;
        } elseif ($vitalSigns->systolic_bp > 140 || $vitalSigns->diastolic_bp > 90) {
            $score -= 15;
        } elseif ($vitalSigns->systolic_bp < 90 || $vitalSigns->diastolic_bp < 60) {
            $score -= 20;
        }

        // Heart rate scoring
        if ($vitalSigns->heart_rate > 120 || $vitalSigns->heart_rate < 50) {
            $score -= 20;
        } elseif ($vitalSigns->heart_rate > 100 || $vitalSigns->heart_rate < 60) {
            $score -= 10;
        }

        // Temperature scoring
        if ($vitalSigns->temperature > 39) {
            $score -= 20;
        } elseif ($vitalSigns->temperature > 37.5 || $vitalSigns->temperature < 36) {
            $score -= 10;
        }

        // Oxygen saturation scoring
        if ($vitalSigns->oxygen_saturation < 90) {
            $score -= 25;
        } elseif ($vitalSigns->oxygen_saturation < 95) {
            $score -= 15;
        }

        return max(0, $score);
    }

    /**
     * Determine health status based on vital signs
     */
    private function determineHealthStatus($vitalSigns)
    {
        if (!$vitalSigns) return 'unknown';

        // Critical conditions
        if ($vitalSigns->systolic_bp > 180 || $vitalSigns->diastolic_bp > 110 ||
            $vitalSigns->heart_rate > 120 || $vitalSigns->heart_rate < 50 ||
            $vitalSigns->oxygen_saturation < 90 || $vitalSigns->temperature > 39) {
            return 'critical';
        }

        // Warning conditions
        if ($vitalSigns->systolic_bp > 140 || $vitalSigns->diastolic_bp > 90 ||
            $vitalSigns->heart_rate > 100 || $vitalSigns->heart_rate < 60 ||
            $vitalSigns->oxygen_saturation < 95 || $vitalSigns->temperature > 37.5) {
            return 'warning';
        }

        return 'normal';
    }

    /**
     * Get vitals trend for chart
     */
    private function getVitalsTrend($patientId)
    {
        return VitalSign::where('patient_id', $patientId)
            ->where('measured_at', '>=', now()->subDays(30))
            ->orderBy('measured_at')
            ->get()
            ->map(function($vital) {
                return [
                    'date' => $vital->measured_at->format('Y-m-d'),
                    'systolic_bp' => $vital->systolic_bp,
                    'diastolic_bp' => $vital->diastolic_bp,
                    'heart_rate' => $vital->heart_rate,
                    'temperature' => $vital->temperature,
                    'oxygen_saturation' => $vital->oxygen_saturation,
                    'health_score' => $this->calculateHealthScore($vital)
                ];
            });
    }

    /**
     * Get medication adherence data (mock implementation)
     */
    private function getMedicationAdherence($patientId)
    {
        // Mock data - replace with actual medication tracking
        return [
            'overall_adherence' => 85,
            'weekly_trend' => [78, 82, 88, 85, 89, 83, 87],
            'missed_doses_this_week' => 3,
            'medications' => [
                ['name' => 'Lisinopril', 'adherence' => 90, 'last_taken' => '2 hours ago'],
                ['name' => 'Metformin', 'adherence' => 85, 'last_taken' => '4 hours ago'],
                ['name' => 'Aspirin', 'adherence' => 88, 'last_taken' => '1 day ago']
            ]
        ];
    }


 public function show($patientId)
    {
        // Get the patient with all related data
        $patient = User::with([
            'patient.vitalSigns' => function($query) {
                $query->orderBy('measured_at', 'desc');
            },
            'patient.meds' => function($query) {
                $query->orderBy('start_date', 'desc');
            },
            'patient.documents' => function($query) {
                $query->where('status', 'active')
                      ->orderBy('created_at', 'desc');
            }
        ])->findOrFail($patientId);

        // Get latest vital signs
        $latestVitals = $patient->patient->vitalSigns->first();

        return view('doctor.patient-monitor', compact('patient', 'latestVitals'));
    }


public function downloadDocument($userId, $documentId)
{
    // First get the patient ID from the user ID
    $patient = Patient::where('user_id', $userId)->firstOrFail();

    // Get the document - checking it belongs to this patient
    $document = Document::where('id', $documentId)
        ->where('patient_id', $patient->id)
        ->where('status', 'active')
        ->firstOrFail();

    // Verify and decode file data
    if (empty($document->file_data)) {
        abort(404, 'File data not found');
    }

    $fileContent = base64_decode($document->file_data);
    if ($fileContent === false) {
        abort(500, 'Invalid file data');
    }

    return response()->make(
        $fileContent,
        200,
        [
            'Content-Type' => $document->file_type,
            'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
            'Content-Length' => strlen($fileContent),
        ]
    );
}

public function storeDocument(Request $request, $userId)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'category' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt'
    ]);

    // Find the user and their associated patient record
    $user = User::findOrFail($userId);
    $patient = $user->patient;

    if (!$patient) {
        return response()->json([
            'success' => false,
            'message' => 'No patient record found for this user'
        ], 404);
    }

    // Verify doctor has access to this patient
    if (!auth()->user()->doctor->patients()->where('patients.id', $patient->id)->exists()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        $file = $request->file('file');
        $fileName = time().'_'.$file->getClientOriginalName();
        $filePath = $file->storeAs('patient_documents', $fileName, 'public');

        $document = $patient->documents()->create([
            'title' => $request->title,
            'category' => $request->category,
            'description' => $request->description,
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'uploaded_by' => auth()->id(),
            'user_id' => $userId  // Store user_id directly
        ]);

        // Notify patient
        $user->notify(new NewDocumentUploaded($document));

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'document' => $document
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload document: '.$e->getMessage()
        ], 500);
    }
}


}
