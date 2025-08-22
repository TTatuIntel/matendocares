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
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class TempAccessController extends Controller
{
    /**
     * Generate a temporary access link for external doctors.
     * Creates both the access link and verification code.
     */
    public function generateTempLink(Request $request)
    {
        try {
            // Get the authenticated user's patient record
            $user = $request->user();
            $patient = $user->patient;

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'error' => 'Patient profile not found. Please complete your patient profile first.'
                ], 404);
            }

            // Validate the request
            $validated = $request->validate([
                'access_reason' => 'nullable|string|max:500',
                'duration_days' => 'nullable|integer|min:1|max:7',
                'notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            try {
                // Deactivate any existing active links for this patient
                TempAccess::where('patient_id', $patient->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);

                // Create new temp access record
                $tempAccess = TempAccess::create([
                    'patient_id' => $patient->id,
                    'generated_by' => $user->id,
                    'expires_at' => now()->addDays($validated['duration_days'] ?? 3),
                    'access_reason' => $validated['access_reason'] ?? 'Medical consultation and care coordination',
                    'notes' => $validated['notes'],
                    'permissions' => [
                        'view_vitals' => true,
                        'upload_documents' => true,
                        'prescribe_medications' => true,
                        'add_health_tips' => true,
                        'view_medical_history' => true,
                        'export_data' => false,
                    ],
                    'is_active' => true,
                    'doctor_verified' => false,
                    'access_count' => 0,
                ]);

                // Generate URLs
                $fullUrl = route('temp.access.dashboard', ['token' => $tempAccess->token]);
                $shortUrl = route('temp.access.short', ['code' => substr($tempAccess->token, 0, 8)]);

                DB::commit();

                Log::info('Temporary access link generated successfully', [
                    'patient_id' => $patient->id,
                    'user_id' => $user->id,
                    'temp_access_id' => $tempAccess->id,
                    'expires_at' => $tempAccess->expires_at,
                    'verification_code' => $tempAccess->verification_code,
                ]);

                return response()->json([
                    'success' => true,
                    'url' => $fullUrl,
                    'short_url' => $shortUrl,
                    'verification_code' => $tempAccess->verification_code,
                    'formatted_code' => $tempAccess->formatted_verification_code,
                    'expires_at' => $tempAccess->expires_at->format('Y-m-d H:i:s'),
                    'expires_human' => $tempAccess->expires_at->diffForHumans(),
                    'message' => 'Temporary access link generated successfully. Share both the link and verification code with your doctor.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error generating temporary access link', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate access link. Please try again.'
            ], 500);
        }
    }

    /**
     * Show the external doctor dashboard with verification flow
     */
    // public function showDashboard($token)
    // {
    //     try {
    //         // Find the temp access record
    //         $tempAccess = TempAccess::where('token', $token)->first();

    //         if (!$tempAccess) {
    //             return view('temp_access.error', [
    //                 'message' => 'Invalid access link. Please check the link provided by the patient.'
    //             ]);
    //         }

    //         // Check if access is still valid
    //         if (!$tempAccess->isActive()) {
    //             return view('temp_access.expired', compact('tempAccess'));
    //         }

    //         // Mark access attempt
    //         $tempAccess->markAccessed();

    //         // Load patient data
    //         $patient = Patient::with('user')->findOrFail($tempAccess->patient_id);

    //         // If not verified, show verification form with empty data
    //         if (!$tempAccess->doctor_verified) {
    //             return view('temp_access.dashboard', [
    //                 'patient' => $patient,
    //                 'tempAccess' => $tempAccess,
    //                 'latestVitals' => null,
    //                 'recentVitals' => collect(),
    //                 'medications' => collect(),
    //                 'doctorMedications' => collect(),
    //                 'documents' => collect(),
    //                 'healthTips' => collect(),
    //                 'chartData' => [
    //                     'labels' => [],
    //                     'systolic' => [],
    //                     'diastolic' => [],
    //                     'heart_rate' => [],
    //                     'temperature' => [],
    //                     'oxygen' => []
    //                 ],
    //             ]);
    //         }

    //         // Doctor is verified - load full data
    //         $latestVitals = VitalSign::where('patient_id', $patient->id)
    //             ->latest('measured_at')
    //             ->first();

    //         $recentVitals = VitalSign::where('patient_id', $patient->id)
    //             ->latest('measured_at')
    //             ->take(20)
    //             ->get();

    //         $medications = Meds::where('user_id', $patient->user_id)
    //             ->where('status', 'active')
    //             ->latest('start_date')
    //             ->get();

    //         $doctorMedications = DoctorMed::where('user_id', $patient->user_id)
    //             ->where('status', 'active')
    //             ->latest('start_date')
    //             ->get();

    //         $documents = Document::where('patient_id', $patient->id)
    //             ->where('status', 'active')
    //             ->latest('created_at')
    //             ->get();

    //         $healthTips = HealthTip::where('user_id', $patient->user_id)
    //             ->where('status', 'active')
    //             ->latest('created_at')
    //             ->take(10)
    //             ->get();

    //         $chartData = $this->prepareChartData($patient->id);

    //         return view('temp_access.dashboard', compact(
    //             'patient',
    //             'tempAccess',
    //             'latestVitals',
    //             'recentVitals',
    //             'medications',
    //             'doctorMedications',
    //             'documents',
    //             'healthTips',
    //             'chartData'
    //         ));

    //     } catch (\Exception $e) {
    //         Log::error('Error loading temp access dashboard', [
    //             'token' => $token,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return view('temp_access.error', [
    //             'message' => 'An error occurred while loading patient data. Please try again.'
    //         ]);
    //     }
    // }


// public function showDashboard($token)
// {
//     try {
//         // Find the temp access record
//         $tempAccess = TempAccess::where('token', $token)->first();

//         if (!$tempAccess) {
//             return view('temp_access.error', [
//                 'message' => 'Invalid access link. Please check the link provided by the patient.'
//             ]);
//         }

//         // Check if access is still valid
//         if (!$tempAccess->isActive()) {
//             return view('temp_access.expired', compact('tempAccess'));
//         }

//         // Mark access attempt
//         $tempAccess->markAccessed();

//         // Load patient data
//         $patient = Patient::with('user')->findOrFail($tempAccess->patient_id);

//         // If not verified, show verification form with empty data
//         if (!$tempAccess->doctor_verified) {
//             return view('temp_access.dashboard', [
//                 'patient' => $patient,
//                 'tempAccess' => $tempAccess,
//                 'latestVitals' => null,
//                 'recentVitals' => collect(),
//                 'medications' => collect(),
//                 'doctorMedications' => collect(),
//                 'documents' => collect(),
//                 'healthTips' => collect(),
//                 'chartData' => [
//                     'labels' => [],
//                     'systolic' => [],
//                     'diastolic' => [],
//                     'heart_rate' => [],
//                     'temperature' => [],
//                     'oxygen' => []
//                 ],
//             ]);
//         }

//         // Doctor is verified - load full data
//         $latestVitals = VitalSign::where('patient_id', $patient->id)
//             ->latest('measured_at')
//             ->first();

//         $recentVitals = VitalSign::where('patient_id', $patient->id)
//             ->latest('measured_at')
//             ->take(20)
//             ->get();

//         // REMOVED 'status' filter
//         $medications = Meds::where('user_id', $patient->user_id)
//             // ->where('status', 'active') // COMMENT THIS LINE OUT
//             ->latest('start_date')
//             ->get();

//         // REMOVED 'status' filter
//         $doctorMedications = DoctorMed::where('user_id', $patient->user_id)
//             // ->where('status', 'active') // COMMENT THIS LINE OUT
//             ->latest('start_date')
//             ->get();

//         // REMOVED 'status' filter. Also ensure this uses the correct column (patient_id vs user_id)
//         $documents = Document::where('patient_id', $patient->id) // or ->where('user_id', $patient->user_id)
//             // ->where('status', 'active') // COMMENT THIS LINE OUT
//             ->latest('created_at')
//             ->get();

//         // REMOVED 'status' filter - THIS WAS THE MAIN CAUSE OF THE ERROR
//         $healthTips = HealthTip::where('user_id', $patient->user_id)
//             // ->where('status', 'active') // COMMENT THIS LINE OUT
//             ->latest('created_at')
//             ->take(10)
//             ->get();

//         $chartData = $this->prepareChartData($patient->id);

//         return view('temp_access.dashboard', compact(
//             'patient',
//             'tempAccess',
//             'latestVitals',
//             'recentVitals',
//             'medications',
//             'doctorMedications',
//             'documents',
//             'healthTips',
//             'chartData'
//         ));

//     } catch (\Exception $e) {
//         Log::error('Error loading temp access dashboard', [
//             'token' => $token,
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);

//         return view('temp_access.error', [
//             'message' => 'An error occurred while loading patient data. Please try again.'
//         ]);
//     }
// }


public function showDashboard($token)
{
    try {
        // Find the temp access record
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess) {
            return view('temp_access.error', [
                'message' => 'Invalid access link. Please check the link provided by the patient.'
            ]);
        }

        // Check if access is still valid
        if (!$tempAccess->isActive()) {
            return view('temp_access.expired', compact('tempAccess'));
        }

        // Mark access attempt
        $tempAccess->markAccessed();

        // Load patient data
        $patient = Patient::with('user')->findOrFail($tempAccess->patient_id);

        // If not verified, show verification form with empty data
        if (!$tempAccess->doctor_verified) {
            return view('temp_access.dashboard', [
                'patient' => $patient,
                'tempAccess' => $tempAccess,
                'latestVitals' => null,
                'recentVitals' => collect(),
                'medications' => collect(),
                'doctorMeds' => collect(),
                'documents' => collect(),
                'healthTips' => collect(),
                'chartData' => [
                    'labels' => [],
                    'systolic' => [],
                    'diastolic' => [],
                    'heart_rate' => [],
                    'temperature' => [],
                    'oxygen' => []
                ],
            ]);
        }

        // Doctor is verified - load full data
        $latestVitals = VitalSign::where('patient_id', $patient->id)
            ->latest('measured_at')
            ->first();

        $recentVitals = VitalSign::where('patient_id', $patient->id)
            ->latest('measured_at')
            ->take(20)
            ->get();

        $medications = Meds::where('user_id', $patient->user_id)
            ->latest('start_date')
            ->get();

        $doctorMeds = DoctorMed::where('user_id', $patient->user_id)
            ->latest('start_date')
            ->get();

        $documents = Document::where('patient_id', $patient->id)
            ->latest('created_at')
            ->get();

        $healthTips = HealthTip::where('user_id', $patient->user_id)
            ->latest('created_at')
            ->take(10)
            ->get();

        $chartData = $this->prepareChartData($patient->id);

        return view('temp_access.dashboard', compact(
            'patient',
            'tempAccess',
            'latestVitals',
            'recentVitals',
            'medications',
            'doctorMeds',
            'documents',
            'healthTips',
            'chartData'
        ));

    } catch (\Exception $e) {
        Log::error('Error loading temp access dashboard', [
            'token' => $token,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return view('temp_access.error', [
            'message' => 'An error occurred while loading patient data. Please try again.'
        ]);
    }
}


    /**
     * Verify doctor credentials and grant access
     */
    public function verifyDoctor(Request $request, $token)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'doctor_name' => 'required|string|max:255',
                'doctor_specialty' => 'required|string|max:255',
                'doctor_facility' => 'required|string|max:255',
                'doctor_phone' => 'required|string|max:20',
                'verification_code' => 'required|string|min:6|max:12'
            ]);

            // Find temp access record
            $tempAccess = TempAccess::where('token', $token)->first();

            if (!$tempAccess) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid access link'
                ], 404);
            }

            if (!$tempAccess->isActive()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access link has expired or been deactivated'
                ], 403);
            }

            if ($tempAccess->doctor_verified) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access has already been verified'
                ], 403);
            }

            // Verify the code
            if (!$tempAccess->verificationCodeMatches($validated['verification_code'])) {
                Log::warning('Invalid verification code attempt', [
                    'temp_access_id' => $tempAccess->id,
                    'provided_code' => $validated['verification_code'],
                    'expected_code' => $tempAccess->verification_code,
                    'doctor_name' => $validated['doctor_name'],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return response()->json([
                    'success' => false,
                    'error' => 'Invalid verification code. Please check the code provided by the patient.'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Update temp access with doctor info and verify
                $tempAccess->update([
                    'doctor_name' => $validated['doctor_name'],
                    'doctor_specialty' => $validated['doctor_specialty'],
                    'doctor_facility' => $validated['doctor_facility'],
                    'doctor_phone' => $validated['doctor_phone'],
                    'doctor_verified' => true,
                    'verified_at' => now()
                ]);

                DB::commit();

                Log::info('Doctor verification successful', [
                    'temp_access_id' => $tempAccess->id,
                    'doctor_name' => $validated['doctor_name'],
                    'doctor_specialty' => $validated['doctor_specialty'],
                    'doctor_facility' => $validated['doctor_facility'],
                    'patient_id' => $tempAccess->patient_id,
                    'verified_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Verification successful! You now have access to patient data.'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Please check all required fields',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error during doctor verification', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Verification failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Upload document via temp access
     */
  /**
 * Upload document via temp access
 */
public function uploadDocument(Request $request, $token)
{

\Log::info('Upload request received', [
    'token' => $token,
    'category' => $request->input('category'),
    'all_data' => $request->all()
]);
    try {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('upload_documents')) {
            \Log::warning('Upload document access denied', ['token' => $token]);
            return response()->json([
                'success' => false,
                'error' => 'Access denied or insufficient permissions'
            ], 403);
        }

        // $validated = $request->validate([
        //     'title' => 'required|string|max:255',
        //     'category' => 'required|string|max:255|in:lab_report,imaging,prescription,insurance,consultation_note,discharge_summary,referral,consent_form,other', // Validate against ENUM
        //     'description' => 'nullable|string|max:1000',
        //     'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt'
        // ]);

$validated = $request->validate([
    'title' => 'required|string|max:255',
    'category' => [
        'required',
        'string',
        'max:255',
        function ($attribute, $value, $fail) {
            $allowed = ['lab_report', 'imaging', 'prescription', 'insurance', 'consultation_note', 'discharge_summary', 'referral', 'consent_form', 'other'];

            // Case-insensitive comparison
            if (!in_array(strtolower($value), array_map('strtolower', $allowed))) {
                $fail("The selected $attribute is invalid.");
            }
        }
    ],
    'description' => 'nullable|string|max:1000',
    'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt'
]);

        $file = $request->file('file');

        // Read file contents safely
        $filePath = $file->getPathname();
        if (!file_exists($filePath)) {
            throw new \Exception("Uploaded file could not be processed.");
        }
        $fileContent = base64_encode(file_get_contents($filePath));
        if ($fileContent === false) {
            throw new \Exception("Failed to encode file data.");
        }

        DB::beginTransaction();

        try {
            // $document = Document::create([
            //     'patient_id' => $tempAccess->patient_id,
            //     // 'doctor_id' => ... // Optional: Do you want to store the doctor's user ID from the temp access?
            //     'uploaded_by' => 'temp_access', // Or maybe $tempAccess->id? Changed from 'doctor_temp_access'
            //     'title' => $validated['title'],
            //     'category' => $validated['category'],
            //     'description' => $validated['description'],
            //     'file_name' => $file->getClientOriginalName(),
            //     'file_type' => $file->getMimeType(),
            //     'file_size' => $file->getSize(),
            //     'file_data' => $fileContent,
            //     'file_hash' => hash('sha256', $fileContent), // Consider generating a hash
            //     'status' => 'active', // Make sure this is set!
            //     // 'uploaded_via' => 'temp_access_' . $tempAccess->id, // Your table doesn't have this column
            // ]);

// In your uploadDocument method, change:
// $document = Document::create([
//     'patient_id' => $tempAccess->patient_id,
//     'uploaded_by' => $tempAccess->id, // Store the temp access ID instead
//     // 'uploaded_by' => null, // Or set to null if allowed
//     'title' => $validated['title'],
//     'category' => $validated['category'],
//     'description' => $validated['description'],
//     'file_name' => $file->getClientOriginalName(),
//     'file_type' => $file->getMimeType(),
//     'file_size' => $file->getSize(),
//     'file_data' => $fileContent,
//     'file_hash' => hash('sha256', $fileContent),
//     'status' => 'active',
// ]);


$document = Document::create([
    'patient_id' => $tempAccess->patient_id,
    'doctor_id' => null, // or assign a doctor ID if available
    'uploaded_by' => null, // Set to NULL for temp access uploads
    'upload_source' => 'temp_access', // Indicate the source
    'temp_access_id' => $tempAccess->id, // Use temp access ID (e.g. 15)
    'title' => $validated['title'],
    'category' => $validated['category'],
    'description' => $validated['description'],
    'file_name' => $file->getClientOriginalName(),
    'file_type' => $file->getMimeType(),
    'file_size' => $file->getSize(),
    'file_data' => $fileContent,
    'file_hash' => hash('sha256', $fileContent),
    'status' => 'active',
    // Add any other required fields here
]);

            DB::commit(); // THIS IS WHERE THE DATA IS ACTUALLY SAVED

            \Log::info('Document uploaded via temp access', [
                'document_id' => $document->id,
                'patient_id' => $tempAccess->patient_id,
                'doctor_name' => $tempAccess->doctor_name,
                'file_name' => $document->file_name,
                'temp_access_id' => $tempAccess->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'category' => $document->category,
                    'file_name' => $document->file_name,
                    'file_size' => $document->file_size,
                    'created_at' => $document->created_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create document record', ['error' => $e->getMessage()]);
            throw $e; // Re-throw to be caught by the outer catch
        }

    } catch (ValidationException $e) {
        \Log::warning('Document upload validation failed', ['errors' => $e->errors()]);
        return response()->json([
            'success' => false,
            'error' => 'Invalid file or data',
            'details' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        \Log::error('Error uploading document via temp access', [
            'token' => $token,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Failed to upload document. Please try again.'
        ], 500);
    }
}

    /**
     * Download document via temp access
     */
    public function downloadDocument($token, $documentId)
    {
        try {
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

            Log::info('Document downloaded via temp access', [
                'document_id' => $document->id,
                'temp_access_id' => $tempAccess->id,
                'doctor_name' => $tempAccess->doctor_name,
            ]);

            return response()->make(
                $fileContent,
                200,
                [
                    'Content-Type' => $document->file_type,
                    'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
                    'Content-Length' => strlen($fileContent),
                ]
            );

        } catch (\Exception $e) {
            Log::error('Error downloading document via temp access', [
                'token' => $token,
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);

            abort(500, 'Failed to download document');
        }
    }

    /**
     * Prescribe medication via temp access
     */
    // public function prescribeMedication(Request $request, $token)
    // {
    //     try {
    //         $tempAccess = TempAccess::where('token', $token)->first();

    //         if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('prescribe_medications')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'error' => 'Access denied or insufficient permissions'
    //             ], 403);
    //         }

    //         $validated = $request->validate([
    //             'name' => 'required|string|max:255',
    //             'generic_name' => 'nullable|string|max:255',
    //             'dosage' => 'required|string|max:100',
    //             'frequency' => 'required|string|max:100',
    //             'start_date' => 'required|date',
    //             'purpose' => 'nullable|string|max:255',
    //             'instructions' => 'nullable|string|max:1000',
    //             'refills' => 'nullable|integer|min:0|max:12'
    //         ]);

    //         $patient = Patient::findOrFail($tempAccess->patient_id);

    //         DB::beginTransaction();

    //         try {
    //             $medication = DoctorMed::create([
    //                 'user_id' => $patient->user_id,
    //                 'name' => $validated['name'],
    //                 'generic_name' => $validated['generic_name'],
    //                 'dosage' => $validated['dosage'],
    //                 'frequency' => $validated['frequency'],
    //                 'start_date' => $validated['start_date'],
    //                 'purpose' => $validated['purpose'],
    //                 'instructions' => $validated['instructions'],
    //                 'refills' => $validated['refills'] ?? 0,
    //                 'prescribed_by' => $tempAccess->doctor_name,
    //                 'prescribed_via' => 'temp_access_' . $tempAccess->id,
    //                 'status' => 'active'


    //             ]);

    //             DB::commit();

    //             Log::info('Medication prescribed via temp access', [
    //                 'medication_id' => $medication->id,
    //                 'patient_id' => $tempAccess->patient_id,
    //                 'doctor_name' => $tempAccess->doctor_name,
    //                 'medication_name' => $validated['name'],
    //                 'temp_access_id' => $tempAccess->id,
    //             ]);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Medication prescribed successfully',
    //                 'medication' => [
    //                     'id' => $medication->id,
    //                     'name' => $medication->name,
    //                     'dosage' => $medication->dosage,
    //                     'frequency' => $medication->frequency,
    //                     'start_date' => $medication->start_date,
    //                     'prescribed_by' => $medication->prescribed_by
    //                 ]
    //             ]);

    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             throw $e;
    //         }

    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Please check all required fields',
    //             'details' => $e->errors()
    //         ], 422);

    //     } catch (\Exception $e) {
    //         Log::error('Error prescribing medication via temp access', [
    //             'token' => $token,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Failed to prescribe medication. Please try again.'
    //         ], 500);
    //     }
    // }


public function prescribeMedication(Request $request, $token)
{
    try {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('prescribe_medications')) {
            return response()->json([
                'success' => false,
                'error' => 'Access denied or insufficient permissions'
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'dosage' => 'required|string|max:100',
            'frequency' => 'required|string|max:100',
            'start_date' => 'required|date',
            'purpose' => 'nullable|string|max:255',
            'instructions' => 'nullable|string|max:1000',
            'refills' => 'nullable|integer|min:0|max:12'
        ]);

        $patient = Patient::findOrFail($tempAccess->patient_id);

        DB::beginTransaction();

        try {
            $medication = DoctorMed::create([
                'user_id' => $patient->user_id,
                'doctor_id' => null, // Set to NULL for temp access
                'temp_access_id' => $tempAccess->id, // Use the temp access ID
                'name' => $validated['name'],
                'generic_name' => $validated['generic_name'],
                'dosage' => $validated['dosage'],
                'frequency' => $validated['frequency'],
                'start_date' => $validated['start_date'],
                'purpose' => $validated['purpose'],
                'instructions' => $validated['instructions'],
                'refills' => $validated['refills'] ?? 0,
                'prescribed_by' => $tempAccess->doctor_name,
                'status' => 'active'
            ]);

            DB::commit();

            Log::info('Medication prescribed via temp access', [
                'medication_id' => $medication->id,
                'patient_id' => $tempAccess->patient_id,
                'doctor_name' => $tempAccess->doctor_name,
                'medication_name' => $validated['name'],
                'temp_access_id' => $tempAccess->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Medication prescribed successfully',
                'medication' => [
                    'id' => $medication->id,
                    'name' => $medication->name,
                    'dosage' => $medication->dosage,
                    'frequency' => $medication->frequency,
                    'start_date' => $medication->start_date,
                    'prescribed_by' => $medication->prescribed_by
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'error' => 'Please check all required fields',
            'details' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        Log::error('Error prescribing medication via temp access', [
            'token' => $token,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Failed to prescribe medication. Please try again.'
        ], 500);
    }
}

    /**
     * Add health tip via temp access
     */
    // public function addHealthTip(Request $request, $token)
    // {
    //     try {
    //         $tempAccess = TempAccess::where('token', $token)->first();

    //         if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('add_health_tips')) {
    //             return response()->json([
    //                 'success' => false,
    //                 'error' => 'Access denied or insufficient permissions'
    //             ], 403);
    //         }

    //         $validated = $request->validate([
    //             'title' => 'required|string|max:255',
    //             'category' => 'required|string|max:100',
    //             'content' => 'required|string|max:2000'
    //         ]);

    //         $patient = Patient::findOrFail($tempAccess->patient_id);

    //         DB::beginTransaction();

    //         try {
    //             $healthTip = HealthTip::create([
    //                 'user_id' => $patient->user_id,
    //                 'title' => $validated['title'],
    //                 'category' => $validated['category'],
    //                 'content' => $validated['content'],
    //                 'source' => 'Doctor via Temp Access',
    //                 'created_by' => $tempAccess->doctor_name,
    //                 'created_via' => 'temp_access_' . $tempAccess->id,
    //                 'priority' => 'normal',
    //                 'status' => 'active'
    //             ]);

    //             DB::commit();

    //             Log::info('Health tip added via temp access', [
    //                 'health_tip_id' => $healthTip->id,
    //                 'patient_id' => $tempAccess->patient_id,
    //                 'doctor_name' => $tempAccess->doctor_name,
    //                 'title' => $validated['title'],
    //                 'temp_access_id' => $tempAccess->id,
    //             ]);

    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Health tip added successfully',
    //                 'health_tip' => [
    //                     'id' => $healthTip->id,
    //                     'title' => $healthTip->title,
    //                     'category' => $healthTip->category,
    //                     'content' => $healthTip->content,
    //                     'created_by' => $healthTip->created_by
    //                 ]
    //             ]);

    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             throw $e;
    //         }

    //     } catch (ValidationException $e) {
    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Please check all required fields',
    //             'details' => $e->errors()
    //         ], 422);

    //     } catch (\Exception $e) {
    //         Log::error('Error adding health tip via temp access', [
    //             'token' => $token,
    //             'error' => $e->getMessage(),
    //             'trace' => $e->getTraceAsString()
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'error' => 'Failed to add health tip. Please try again.'
    //         ], 500);
    //     }
    // }


public function addHealthTip(Request $request, $token)
{
    try {
        $tempAccess = TempAccess::where('token', $token)->first();

        if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('add_health_tips')) {
            return response()->json([
                'success' => false,
                'error' => 'Access denied or insufficient permissions'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'content' => 'required|string|max:2000'
        ]);

        $patient = Patient::findOrFail($tempAccess->patient_id);

        DB::beginTransaction();

        try {
            $healthTip = DoctorMed::create([
                'user_id' => $patient->user_id,
                'doctor_id' => null, // Set to null for temp access
                'temp_access_id' => $tempAccess->id, // Link to temp access
                'name' => $validated['title'], // Use title as name
                'generic_name' => $validated['category'], // Use category as generic_name
                'health_tips' => $validated['content'], // Health tips content
                'prescribed_by' => $tempAccess->doctor_name, // Doctor who created it
                'status' => 'active',
                // Set empty/default values for medication-specific fields
                'dosage' => 'N/A',
                'frequency' => 'N/A',
                'start_date' => now()->format('Y-m-d'),
                'purpose' => 'Health advice and guidance',
                'instructions' => 'Follow these health recommendations',
                'refills' => 0
            ]);

            DB::commit();

            Log::info('Health tip added via temp access', [
                'health_tip_id' => $healthTip->id,
                'patient_id' => $tempAccess->patient_id,
                'doctor_name' => $tempAccess->doctor_name,
                'title' => $validated['title'],
                'temp_access_id' => $tempAccess->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Health tip added successfully',
                'health_tip' => [
                    'id' => $healthTip->id,
                    'title' => $healthTip->name,
                    'category' => $healthTip->generic_name,
                    'content' => $healthTip->health_tips,
                    'created_by' => $healthTip->prescribed_by
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'error' => 'Please check all required fields',
            'details' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        Log::error('Error adding health tip via temp access', [
            'token' => $token,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => 'Failed to add health tip. Please try again.'
        ], 500);
    }
}
    /**
     * Get vitals data via temp access
     */
    public function getVitalsData($token)
    {
        try {
            $tempAccess = TempAccess::where('token', $token)->first();

            if (!$tempAccess || !$tempAccess->canAccess() || !$tempAccess->hasPermission('view_vitals')) {
                return response()->json([
                    'success' => false,
                    'error' => 'Access denied or insufficient permissions'
                ], 403);
            }

            $vitals = VitalSign::where('patient_id', $tempAccess->patient_id)
                ->latest('measured_at')
                ->take(50)
                ->get();

            return response()->json([
                'success' => true,
                'vitals' => $vitals,
                'chart_data' => $this->prepareChartData($tempAccess->patient_id)
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching vitals via temp access', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch vitals data'
            ], 500);
        }
    }

    /**
     * Revoke access (patient action)
     */
    public function revokeAccess(Request $request)
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'reason' => 'nullable|string|max:500'
            ]);

            $patient = Patient::findOrFail($validated['patient_id']);

            // Verify patient ownership
            if ((string)$patient->user_id !== (string)auth()->id()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access'
                ], 403);
            }

            DB::beginTransaction();

            try {
                $activeAccess = TempAccess::where('patient_id', $patient->id)
                    ->where('is_active', true)
                    ->first();

                if ($activeAccess) {
                    $activeAccess->update([
                        'is_active' => false,
                        'revoked_at' => now(),
                        'revocation_reason' => $validated['reason']
                    ]);

                    Log::info('Temp access revoked by patient', [
                        'temp_access_id' => $activeAccess->id,
                        'patient_id' => $patient->id,
                        'reason' => $validated['reason'],
                        'revoked_by' => auth()->id()
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Doctor access has been revoked successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request data',
                'details' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error revoking temp access', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to revoke access'
            ], 500);
        }
    }

    /**
     * Get access status for patient
     */
    public function getAccessStatus(Request $request)
    {
        try {
            $user = $request->user();
            $patient = $user->patient;

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'error' => 'Patient profile not found'
                ], 404);
            }

            $activeAccess = TempAccess::where('patient_id', $patient->id)
                ->where('is_active', true)
                ->latest('created_at')
                ->first();

            if (!$activeAccess) {
                return response()->json([
                    'success' => true,
                    'has_active_access' => false,
                    'message' => 'No active temporary access'
                ]);
            }

            return response()->json([
                'success' => true,
                'has_active_access' => true,
                'access' => [
                    'id' => $activeAccess->id,
                    'doctor_verified' => $activeAccess->doctor_verified,
                    'doctor_name' => $activeAccess->doctor_name,
                    'doctor_specialty' => $activeAccess->doctor_specialty,
                    'doctor_facility' => $activeAccess->doctor_facility,
                    'expires_at' => $activeAccess->expires_at->format('Y-m-d H:i:s'),
                    'expires_human' => $activeAccess->expires_at->diffForHumans(),
                    'access_count' => $activeAccess->access_count,
                    'verified_at' => $activeAccess->verified_at?->format('Y-m-d H:i:s'),
                    'access_reason' => $activeAccess->access_reason
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting access status', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get access status'
            ], 500);
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

            $labels = [];
            $systolic = [];
            $diastolic = [];
            $heart_rate = [];
            $temperature = [];
            $oxygen = [];

            foreach ($vitals as $vital) {
                $labels[] = $vital->measured_at->format('M j');
                $systolic[] = $vital->systolic_bp;
                $diastolic[] = $vital->diastolic_bp;
                $heart_rate[] = $vital->heart_rate;
                $temperature[] = $vital->temperature;
                $oxygen[] = $vital->oxygen_saturation;
            }

            return compact('labels', 'systolic', 'diastolic', 'heart_rate', 'temperature', 'oxygen');

        } catch (\Exception $e) {
            Log::error('Error preparing chart data for temp access', [
                'patient_id' => $patientId,
                'error' => $e->getMessage()
            ]);

            return [
                'labels' => [],
                'systolic' => [],
                'diastolic' => [],
                'heart_rate' => [],
                'temperature' => [],
                'oxygen' => [],
            ];
        }
    }
}
