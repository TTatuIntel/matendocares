<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\VitalSign;
use App\Models\TempAccess;
use App\Models\Document;
use App\Models\DoctorMed;
use App\Models\HealthTip;
use App\Models\Meds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TempAccessController extends Controller
{
    /**
     * Generate a temporary access link for external doctors.
     */
    public function generateTempLink(Request $request, $patientId = null)
    {
        // Resolve patient id: route param -> request body -> auth user's patient
        if (!$patientId) {
            $patientId = $request->patient_id;
            if (!$patientId) {
                $authUser = $request->user();
                if (!$authUser || !$authUser->patient) {
                    return response()->json(['error' => 'Patient not found for current user'], 404);
                }
                $patientId = $authUser->patient->id;
            }
        }

        $request->validate([
            'access_reason' => 'required|string|max:500',
            'duration_days' => 'nullable|integer|min:1|max:7',
            'notes'         => 'nullable|string|max:1000'
        ]);

        $patient = Patient::findOrFail($patientId);

        // Only the owning patient can create a link
        if ((int) $patient->user_id !== (int) auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            // Deactivate previous active links
            TempAccess::where('patient_id', $patient->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            // Generate verification code (8 characters, uppercase alphanumeric)
            $verificationCode = strtoupper(Str::random(8));

            // Create fresh link
            $tempAccess = TempAccess::create([
                'patient_id'        => $patient->id,
                'generated_by'      => auth()->id(),
                'expires_at'        => now()->addDays($request->duration_days ?? 7),
                'access_reason'     => $request->access_reason,
                'notes'             => $request->notes,
                'verification_code' => $verificationCode,
                'permissions'       => [
                    'view_vitals'           => true,
                    'upload_documents'      => true,
                    'prescribe_medications' => true,
                    'add_health_tips'       => true,
                    'view_medical_history'  => true,
                    'export_data'           => false,
                ],
                'is_active'         => true,
                'doctor_verified'   => false,
                'access_count'      => 0,
            ]);

            $fullUrl  = route('temp.access.dashboard', ['token' => $tempAccess->token]);
            $shortUrl = route('temp.access.short', ['code' => substr($tempAccess->token, 0, 8)]);

            Log::info('Temporary access link generated', [
                'patient_id'        => $patient->id,
                'generated_by'      => auth()->id(),
                'expires_at'        => $tempAccess->expires_at,
                'token'             => $tempAccess->token,
                'verification_code' => $verificationCode,
            ]);

            return response()->json([
                'success'           => true,
                'url'               => $fullUrl,
                'short_url'         => $shortUrl,
                'expires_at'        => $tempAccess->expires_at->format('Y-m-d H:i:s'),
                'verification_code' => $verificationCode,
                'formatted_code'    => $tempAccess->formatted_verification_code,
                'message'           => 'Temporary access link generated successfully.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error generating temp access link', [
                'error'      => $e->getMessage(),
                'patient_id' => $patientId,
                'trace'      => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to generate access link'], 500);
        }
    }

    /**
     * Show the external doctor dashboard (verification-aware).
     */
    public function showDashboard($token)
    {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess) {
            return view('temp_access.error', ['message' => 'Access link not found or invalid']);
        }

        if (!$tempAccess->isActive()) {
            return view('temp_access.expired', compact('tempAccess'));
        }

        // Mark as accessed (increment count and update timestamp)
        $tempAccess->markAccessed();

        try {
            $patient = Patient::with('user')->findOrFail($tempAccess->patient_id);

            if (!$tempAccess->doctor_verified) {
                // Show verification form
                return view('temp_access.dashboard', [
                    'patient'           => $patient,
                    'tempAccess'        => $tempAccess,
                    'latestVitals'      => null,
                    'recentVitals'      => collect(),
                    'medications'       => collect(),
                    'doctorMedications' => collect(),
                    'documents'         => collect(),
                    'healthTips'        => collect(),
                    'chartData'         => ['labels'=>[], 'systolic'=>[], 'diastolic'=>[], 'heart_rate'=>[], 'temperature'=>[], 'oxygen'=>[]],
                ]);
            }

            // Verified -> load full data
            $latestVitals      = VitalSign::where('patient_id', $patient->id)->latest('measured_at')->first();
            $recentVitals      = VitalSign::where('patient_id', $patient->id)->latest('measured_at')->take(20)->get();
            $medications       = Meds::where('user_id', $patient->user_id)->latest('start_date')->get();
            $doctorMedications = DoctorMed::where('user_id', $patient->user_id)->latest('start_date')->get();
            $documents         = Document::where('patient_id', $patient->id)->where('status', 'active')->latest('created_at')->get();
            $healthTips        = HealthTip::where('user_id', $patient->user_id)->latest('created_at')->take(10)->get();
            $chartData         = $this->prepareChartData($patient->id);

            return view('temp_access.dashboard', compact(
                'patient', 'tempAccess', 'latestVitals', 'recentVitals',
                'medications', 'doctorMedications', 'documents', 'healthTips', 'chartData'
            ));

        } catch (\Throwable $e) {
            Log::error('Error loading temp access dashboard', [
                'error' => $e->getMessage(),
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);

            return view('temp_access.error', ['message' => 'Failed to load patient data']);
        }
    }

    /**
     * Doctor verification (must include exact 'verification_code').
     */
    public function verifyDoctor(Request $request, $token)
    {
        $request->validate([
            'doctor_name'       => 'required|string|max:255',
            'doctor_specialty'  => 'required|string|max:255',
            'doctor_facility'   => 'required|string|max:255',
            'doctor_phone'      => 'required|string|max:20',
            'verification_code' => 'required|string|min:6|max:10'
        ]);

        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess) {
            return response()->json(['error' => 'Invalid access link'], 404);
        }

        if (!$tempAccess->isActive()) {
            return response()->json(['error' => 'Access link has expired or been deactivated'], 403);
        }

        // Use the model's verification method that handles dashes, spaces, and case
        if (!$tempAccess->verificationCodeMatches($request->verification_code)) {
            Log::warning('Invalid verification code attempt', [
                'temp_access_id' => $tempAccess->id,
                'provided_code'  => $request->verification_code,
                'expected_code'  => $tempAccess->verification_code,
                'formatted_code' => $tempAccess->formatted_verification_code,
                'doctor_name'    => $request->doctor_name,
                'ip_address'     => $request->ip(),
                'user_agent'     => $request->userAgent(),
            ]);

            return response()->json([
                'error' => 'Invalid verification code. Please check the code provided by the patient. The code should be 8 characters (with or without dashes).'
            ], 422);
        }

        try {
            $tempAccess->verify([
                'doctor_name'      => $request->doctor_name,
                'doctor_specialty' => $request->doctor_specialty,
                'doctor_facility'  => $request->doctor_facility,
                'doctor_phone'     => $request->doctor_phone
            ]);

            Log::info('Doctor verified for temp access', [
                'temp_access_id'   => $tempAccess->id,
                'doctor_name'      => $request->doctor_name,
                'doctor_specialty' => $request->doctor_specialty,
                'doctor_facility'  => $request->doctor_facility,
                'verification_code' => $tempAccess->verification_code,
                'verified_at'      => now(),
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Verification successful! Access granted to patient data.'
            ]);

        } catch (\Throwable $e) {
            Log::error('Error verifying doctor', [
                'error' => $e->getMessage(),
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Verification failed. Please try again.'], 500);
        }
    }

    /**
     * Upload document via temp access
     */
    public function uploadDocument(Request $request, $token)
    {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('upload_documents')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'title'       => 'required|string|max:255',
            'category'    => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'file'        => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt'
        ]);

        try {
            $file        = $request->file('file');
            $fileContent = base64_encode(file_get_contents($file->path()));

            $document = Document::create([
                'patient_id'  => $tempAccess->patient_id,
                'title'       => $request->title,
                'category'    => $request->category,
                'description' => $request->description,
                'file_name'   => $file->getClientOriginalName(),
                'file_type'   => $file->getMimeType(),
                'file_size'   => $file->getSize(),
                'file_data'   => $fileContent,
                'status'      => 'active',
                'uploaded_by' => 'doctor_temp_access',
                'uploaded_via' => 'temp_access_' . $tempAccess->id,
            ]);

            Log::info('Document uploaded via temp access', [
                'document_id' => $document->id,
                'patient_id'  => $tempAccess->patient_id,
                'doctor_name' => $tempAccess->doctor_name,
                'file_name'   => $document->file_name,
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Document uploaded successfully',
                'document' => $document
            ]);

        } catch (\Throwable $e) {
            Log::error('Error uploading document via temp access', [
                'error' => $e->getMessage(),
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to upload document'], 500);
        }
    }

    /**
     * Download document via temp access
     */
    public function downloadDocument($token, $documentId)
    {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess()) {
            abort(403, 'Access denied');
        }

        $document = Document::where('id', $documentId)
            ->where('patient_id', $tempAccess->patient_id)
            ->where('status', 'active')
            ->firstOrFail();

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
                'Content-Type'        => $document->file_type,
                'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
                'Content-Length'      => strlen($fileContent),
            ]
        );
    }

    /**
     * Get vitals data via temp access
     */
    public function getVitalsData($token)
    {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('view_vitals')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        try {
            $vitals = VitalSign::where('patient_id', $tempAccess->patient_id)
                ->latest('measured_at')
                ->take(50)
                ->get();

            return response()->json([
                'success'    => true,
                'vitals'     => $vitals,
                'chart_data' => $this->prepareChartData($tempAccess->patient_id)
            ]);

        } catch (\Throwable $e) {
            Log::error('Error fetching vitals via temp access', [
                'error' => $e->getMessage(),
                'token' => $token
            ]);

            return response()->json(['error' => 'Failed to fetch vitals data'], 500);
        }
    }

    /**
     * Revoke access (patient action)
     */
    public function revokeAccess(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'reason'     => 'nullable|string|max:500'
        ]);

        $patient = Patient::findOrFail($request->patient_id);

        if ((int) $patient->user_id !== (int) auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $active = TempAccess::where('patient_id', $patient->id)->where('is_active', true)->first();

            if ($active) {
                $active->revoke();

                Log::info('Temp access revoked by patient', [
                    'temp_access_id' => $active->id,
                    'patient_id'     => $patient->id,
                    'reason'         => $request->reason
                ]);
            }

            return response()->json(['success' => true, 'message' => 'Doctor access has been revoked successfully']);

        } catch (\Throwable $e) {
            Log::error('Error revoking temp access', [
                'error'      => $e->getMessage(),
                'patient_id' => $request->patient_id
            ]);

            return response()->json(['error' => 'Failed to revoke access'], 500);
        }
    }

    /**
     * Prescribe medication via temp access
     */
    public function prescribeMedication(Request $request, $token)
    {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('prescribe_medications')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'name'         => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'dosage'       => 'required|string|max:100',
            'frequency'    => 'required|string|max:100',
            'start_date'   => 'required|date',
            'purpose'      => 'nullable|string|max:255',
            'instructions' => 'nullable|string|max:1000',
            'refills'      => 'nullable|integer|min:0|max:12'
        ]);

        try {
            $patient = Patient::findOrFail($tempAccess->patient_id);

            $medication = DoctorMed::create([
                'user_id'      => $patient->user_id,
                'name'         => $request->name,
                'generic_name' => $request->generic_name,
                'dosage'       => $request->dosage,
                'frequency'    => $request->frequency,
                'start_date'   => $request->start_date,
                'purpose'      => $request->purpose,
                'instructions' => $request->instructions,
                'refills'      => $request->refills ?? 0,
                'prescribed_by' => $tempAccess->doctor_name,
                'prescribed_via' => 'temp_access_' . $tempAccess->id,
                'status'       => 'active'
            ]);

            Log::info('Medication prescribed via temp access', [
                'medication_id' => $medication->id,
                'patient_id'    => $tempAccess->patient_id,
                'doctor_name'   => $tempAccess->doctor_name,
                'medication'    => $request->name,
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Medication prescribed successfully',
                'medication' => $medication
            ]);

        } catch (\Throwable $e) {
            Log::error('Error prescribing medication via temp access', [
                'error' => $e->getMessage(),
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to prescribe medication'], 500);
        }
    }

    /**
     * Add health tip via temp access
     */
    public function addHealthTip(Request $request, $token)
    {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('add_health_tips')) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $request->validate([
            'title'    => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content'  => 'required|string|max:2000'
        ]);

        try {
            $patient = Patient::findOrFail($tempAccess->patient_id);

            $healthTip = HealthTip::create([
                'user_id'    => $patient->user_id,
                'title'      => $request->title,
                'category'   => $request->category,
                'content'    => $request->content,
                'source'     => 'Doctor via Temp Access',
                'created_by' => $tempAccess->doctor_name,
                'created_via' => 'temp_access_' . $tempAccess->id,
                'priority'   => 'normal',
                'status'     => 'active'
            ]);

            Log::info('Health tip added via temp access', [
                'health_tip_id' => $healthTip->id,
                'patient_id'    => $tempAccess->patient_id,
                'doctor_name'   => $tempAccess->doctor_name,
                'title'         => $request->title,
            ]);

            return response()->json([
                'success'    => true,
                'message'    => 'Health tip added successfully',
                'health_tip' => $healthTip
            ]);

        } catch (\Throwable $e) {
            Log::error('Error adding health tip via temp access', [
                'error' => $e->getMessage(),
                'token' => $token,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Failed to add health tip'], 500);
        }
    }

    /**
     * Prepare chart data for visualizations
     */
    private function prepareChartData($patientId)
    {
        try {
            $vitals = VitalSign::where('patient_id', $patientId)
                ->where('measured_at', '>=', now()->subDays(30))
                ->orderBy('measured_at', 'asc')
                ->get();

            $labels = $systolic = $diastolic = $heart_rate = $temperature = $oxygen = [];

            foreach ($vitals as $v) {
                $labels[]      = $v->measured_at->format('M j');
                $systolic[]    = $v->systolic_bp ?? null;
                $diastolic[]   = $v->diastolic_bp ?? null;
                $heart_rate[]  = $v->heart_rate ?? null;
                $temperature[] = $v->temperature ?? null;
                $oxygen[]      = $v->oxygen_saturation ?? null;
            }

            return compact('labels', 'systolic', 'diastolic', 'heart_rate', 'temperature', 'oxygen');

        } catch (\Throwable $e) {
            Log::error('Error preparing chart data for temp access', [
                'error'      => $e->getMessage(),
                'patient_id' => (string)$patientId
            ]);

            return [
                'labels'      => [],
                'systolic'    => [],
                'diastolic'   => [],
                'heart_rate'  => [],
                'temperature' => [],
                'oxygen'      => [],
            ];
        }
    }
}