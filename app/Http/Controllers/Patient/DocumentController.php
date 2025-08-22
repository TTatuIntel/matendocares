<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Models\DoctorDocument;


class DocumentController extends Controller
{
    /**
     * Display a listing of the patient's documents.
     */
public function index(Request $request)
{
    $patient = auth()->user()->patient;

    if (!$patient) {
        return redirect()->route('patient.dashboard')
            ->with('error', 'Patient profile not found. Please contact support.');
    }

    // Get documents with eager loading if needed
    $documents = $patient->documents()
        ->orderBy('created_at', 'desc')
        ->get();

    // Get category counts for stats
    $stats = [
        'total' => $documents->count(),
        'lab_reports' => $documents->where('category', 'lab_report')->count(),
        'prescriptions' => $documents->where('category', 'prescription')->count(),
        'imaging' => $documents->where('category', 'imaging')->count(),
    ];

    $categories = [
        'lab_report' => 'Lab Reports',
        'imaging' => 'Medical Imaging',
        'prescription' => 'Prescriptions',
        'insurance' => 'Insurance Documents',
        'consultation_note' => 'Consultation Notes',
        'discharge_summary' => 'Discharge Summaries',
        'referral' => 'Referral Letters',
        'consent_form' => 'Consent Forms',
        'other' => 'Other Documents'
    ];

    return view('patient.documents', compact('documents', 'categories', 'stats'));
}

    /**
     * Store a newly created document.
     */
    // public function store(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'title' => 'required|string|max:255',
    //             'description' => 'nullable|string|max:1000',
    //             'category' => 'required|in:lab_report,imaging,prescription,insurance,consultation_note,discharge_summary,referral,consent_form,other',
    //             'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt,csv,xlsx', // 10MB max
    //             'tags' => 'nullable|string|max:500',
    //             'is_confidential' => 'boolean'
    //         ]);

    //         $patient = auth()->user()->patient;

    //         if (!$patient) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Patient profile not found'
    //             ], 404);
    //         }

    //         DB::beginTransaction();

    //         $file = $request->file('file');
    //         $fileName = $file->getClientOriginalName();
    //         $fileType = $file->getMimeType();
    //         $fileSize = $file->getSize();

    //         // Read file content and encode
    //         $fileContent = base64_encode(file_get_contents($file->getRealPath()));
    //         $fileHash = hash('sha256', $fileContent);

    //         // Process tags
    //         $tags = null;
    //         if ($request->tags) {
    //             $tagArray = array_map('trim', explode(',', $request->tags));
    //             $tagArray = array_filter($tagArray, function($tag) {
    //                 return !empty($tag) && strlen($tag) <= 50;
    //             });
    //             $tags = json_encode(array_values($tagArray));
    //         }

    //         // For demo purposes, we'll simulate creating a document
    //         // In real implementation, this would save to database
    //         Log::info('Document uploaded successfully', [
    //             'patient_id' => $patient->id,
    //             'file_name' => $fileName,
    //             'file_size' => $fileSize,
    //             'title' => $request->title,
    //             'category' => $request->category
    //         ]);

    //         DB::commit();

    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'message' => 'Document uploaded successfully!',
    //                 'document' => [
    //                     'id' => Str::uuid(),
    //                     'title' => $request->title,
    //                     'description' => $request->description,
    //                     'category' => $request->category,
    //                     'file_name' => $fileName,
    //                     'file_type' => $fileType,
    //                     'file_size' => $fileSize,
    //                     'file_size_human' => $this->formatFileSize($fileSize),
    //                     'created_at' => now()->format('Y-m-d'),
    //                     'created_at_human' => 'Just now',
    //                     'tags' => $tags ? json_decode($tags, true) : [],
    //                     'is_confidential' => $request->boolean('is_confidential', false),
    //                     'category_label' => $this->getCategoryLabel($request->category),
    //                     'file_icon' => $this->getFileIcon($fileType)
    //                 ]
    //             ]);
    //         }

    //         return redirect()->route('patient.documents.index')
    //             ->with('success', 'Document uploaded successfully!');

    //     } catch (\Illuminate\Validation\ValidationException $e) {
    //         DB::rollBack();
    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Validation failed',
    //                 'errors' => $e->errors()
    //             ], 422);
    //         }
    //         return back()->withErrors($e->errors())->withInput();

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error('Error uploading document: ' . $e->getMessage(), [
    //             'patient_id' => auth()->user()->patient->id ?? null,
    //             'file_name' => $request->file('file')?->getClientOriginalName()
    //         ]);

    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Failed to upload document. Please try again.'
    //             ], 500);
    //         }

    //         return back()->with('error', 'Failed to upload document. Please try again.');
    //     }
    // }

public function store(Request $request)
{
    try {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'required|in:lab_report,imaging,prescription,insurance,consultation_note,discharge_summary,referral,consent_form,other',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt,csv,xlsx',
            'tags' => 'nullable|string|max:500',
            'is_confidential' => 'sometimes|boolean'
        ]);

        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient profile not found'
            ], 404);
        }

        DB::beginTransaction();

        $file = $request->file('file');
        $fileContent = file_get_contents($file->getRealPath());

        $document = new Document([
            'patient_id' => $patient->id,
            'uploaded_by' => auth()->id(),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'file_data' => base64_encode($fileContent),
            'file_hash' => hash('sha256', $fileContent),
            'category' => $validated['category'],
            'tags' => $validated['tags'] ? json_encode(
                array_slice(
                    array_unique(
                        array_map('trim',
                            explode(',', $validated['tags'])
                        )
                    ),
                0, 5) // Limit to 5 tags
            ) : null,
            'is_confidential' => $validated['is_confidential'] ?? false,
            'status' => 'active'
        ]);

        $document->save();

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully!',
            'document' => [
                'id' => $document->id,
                'title' => $document->title,
                'description' => $document->description,
                'category' => $document->category,
                'file_name' => $document->file_name,
                'file_type' => $document->file_type,
                'file_size' => $document->file_size,
                'created_at' => $document->created_at->format('Y-m-d H:i:s'),
                'is_confidential' => $document->is_confidential,
            ]
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Document upload error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Failed to upload document: ' . $e->getMessage()
        ], 500);
    }
}





    /**
     * Display the specified document.
     */
    // public function show(Request $request, $documentId)
    // {
    //     try {
    //         $patient = auth()->user()->patient;

    //         if (!$patient) {
    //             abort(403, 'Patient profile not found');
    //         }

    //         // For demo, get sample document
    //         $documents = $this->getSampleDocuments($patient);
    //         $document = collect($documents)->firstWhere('id', $documentId);

    //         if (!$document) {
    //             abort(404, 'Document not found');
    //         }

    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => true,
    //                 'document' => [
    //                     'id' => $document['id'],
    //                     'title' => $document['title'],
    //                     'description' => $document['description'],
    //                     'category' => $document['category'],
    //                     'file_name' => $document['file_name'],
    //                     'file_type' => $document['file_type'],
    //                     'file_size' => $document['file_size'],
    //                     'file_size_human' => $this->formatFileSize($document['file_size']),
    //                     'created_at' => $document['created_at'],
    //                     'created_at_human' => Carbon::parse($document['created_at'])->diffForHumans(),
    //                     'tags' => $document['tags'] ?? [],
    //                     'is_confidential' => (bool) ($document['is_confidential'] ?? false),
    //                     'category_label' => $this->getCategoryLabel($document['category']),
    //                     'file_icon' => $this->getFileIcon($document['file_type'])
    //                 ]
    //             ]);
    //         }

    //         return view('patient.documents.show', compact('document'));

    //     } catch (\Exception $e) {
    //         Log::error('Error viewing document: ' . $e->getMessage());

    //         if ($request->ajax()) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => 'Document not found or access denied'
    //             ], 404);
    //         }

    //         abort(404, 'Document not found');
    //     }
    // }




    /**
     * Download the specified document.
     */
    // public function download($documentId)
    // {
    //     try {
    //         $patient = auth()->user()->patient;

    //         if (!$patient) {
    //             abort(403, 'Patient profile not found');
    //         }

    //         // For demo, simulate download
    //         $documents = $this->getSampleDocuments($patient);
    //         $document = collect($documents)->firstWhere('id', $documentId);

    //         if (!$document) {
    //             abort(404, 'Document not found');
    //         }

    //         // Log download activity
    //         Log::info('Document download requested', [
    //             'patient_id' => $patient->id,
    //             'document_id' => $documentId,
    //             'file_name' => $document['file_name']
    //         ]);

    //         // For demo, create a sample file response
    //         $sampleContent = "Sample document content for: " . $document['title'];

    //         return response($sampleContent)
    //             ->header('Content-Type', $document['file_type'])
    //             ->header('Content-Disposition', 'attachment; filename="' . $document['file_name'] . '"')
    //             ->header('Content-Length', strlen($sampleContent))
    //             ->header('Cache-Control', 'no-cache, no-store, must-revalidate');

    //     } catch (\Exception $e) {
    //         Log::error('Error downloading document: ' . $e->getMessage());
    //         abort(404, 'Document not found or access denied');
    //     }
    // }


public function download($documentId)
{
    try {
        $patient = auth()->user()->patient;

        if (!$patient) {
            abort(403, 'Patient profile not found');
        }

        $document = Document::where('id', $documentId)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        if (empty($document->file_data)) {
            abort(404, 'File content not found');
        }

        $fileContent = base64_decode($document->file_data);

        return response($fileContent)
            ->header('Content-Type', $document->file_type)
            ->header('Content-Disposition', 'attachment; filename="' . $document->file_name . '"');

    } catch (\Exception $e) {
        Log::error('Error downloading document: ' . $e->getMessage());
        abort(404, 'Document not found or access denied');
    }
}

public function show($documentId)
{
    try {
        $patient = auth()->user()->patient;

        if (!$patient) {
            abort(403, 'Patient profile not found');
        }

        $document = Document::where('id', $documentId)
            ->where('patient_id', $patient->id)
            ->firstOrFail();

        if (empty($document->file_data)) {
            abort(404, 'File content not found');
        }

        $fileContent = base64_decode($document->file_data);

        return response($fileContent)
            ->header('Content-Type', $document->file_type)
            ->header('Content-Disposition', 'inline; filename="' . $document->file_name . '"');

    } catch (\Exception $e) {
        Log::error('Error viewing document: ' . $e->getMessage());
        abort(404, 'Document not found or access denied');
    }
}


    /**
     * Update the specified document.
     */
    public function update(Request $request, $documentId)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'category' => 'required|in:lab_report,imaging,prescription,insurance,consultation_note,discharge_summary,referral,consent_form,other',
                'tags' => 'nullable|string|max:500',
                'is_confidential' => 'boolean'
            ]);

            $patient = auth()->user()->patient;

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient profile not found'
                ], 404);
            }

            // For demo, just log the update
            Log::info('Document update requested', [
                'patient_id' => $patient->id,
                'document_id' => $documentId,
                'title' => $request->title
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document updated successfully!'
                ]);
            }

            return redirect()->route('patient.documents.index')
                ->with('success', 'Document updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating document: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update document. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to update document. Please try again.');
        }
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Request $request, $documentId)
    {
        try {
            $patient = auth()->user()->patient;

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient profile not found'
                ], 404);
            }

            // For demo, just log the deletion
            Log::info('Document deletion requested', [
                'patient_id' => $patient->id,
                'document_id' => $documentId
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document deleted successfully!'
                ]);
            }

            return redirect()->route('patient.documents.index')
                ->with('success', 'Document deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting document: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete document. Please try again.'
                ], 500);
            }

            return back()->with('error', 'Failed to delete document. Please try again.');
        }
    }

    /**
     * Bulk upload documents.
     */
    public function bulkUpload(Request $request)
    {
        try {
            $request->validate([
                'files' => 'required|array|max:10',
                'files.*' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt,csv,xlsx',
                'category' => 'required|in:lab_report,imaging,prescription,insurance,consultation_note,discharge_summary,referral,consent_form,other',
                'tags' => 'nullable|string|max:500',
                'is_confidential' => 'boolean'
            ]);

            $patient = auth()->user()->patient;

            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient profile not found'
                ], 404);
            }

            $uploadedCount = count($request->file('files'));

            Log::info('Bulk upload requested', [
                'patient_id' => $patient->id,
                'file_count' => $uploadedCount,
                'category' => $request->category
            ]);

            return response()->json([
                'success' => true,
                'message' => "$uploadedCount documents uploaded successfully.",
                'uploaded_count' => $uploadedCount,
                'error_count' => 0,
                'errors' => []
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk upload: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get sample documents for demo purposes
     */
    private function getSampleDocuments($patient)
    {
        return [
            [
                'id' => '1',
                'title' => 'Blood Test Results - Complete Metabolic Panel',
                'description' => 'Comprehensive blood work including glucose, electrolytes, and kidney function markers',
                'category' => 'lab_report',
                'file_name' => 'blood_test_2024_01.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 1024576,
                'created_at' => '2024-01-15',
                'tags' => ['blood test', 'routine', '2024', 'metabolic'],
                'is_confidential' => false
            ],
            [
                'id' => '2',
                'title' => 'Chest X-Ray Report',
                'description' => 'Routine chest X-ray examination for annual physical',
                'category' => 'imaging',
                'file_name' => 'chest_xray_2024.jpg',
                'file_type' => 'image/jpeg',
                'file_size' => 2048576,
                'created_at' => '2024-01-10',
                'tags' => ['x-ray', 'chest', 'imaging'],
                'is_confidential' => false
            ],
            [
                'id' => '3',
                'title' => 'Prescription - Hypertension Medication',
                'description' => 'Monthly prescription for blood pressure medication',
                'category' => 'prescription',
                'file_name' => 'prescription_bp_meds.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 512000,
                'created_at' => '2024-01-08',
                'tags' => ['prescription', 'blood pressure', 'medication'],
                'is_confidential' => true
            ],
            [
                'id' => '4',
                'title' => 'Insurance Coverage Summary',
                'description' => 'Current health insurance plan details and coverage information',
                'category' => 'insurance',
                'file_name' => 'insurance_summary_2024.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 768000,
                'created_at' => '2024-01-05',
                'tags' => ['insurance', 'coverage', '2024'],
                'is_confidential' => true
            ],
            [
                'id' => '5',
                'title' => 'Consultation Notes - Cardiology',
                'description' => 'Follow-up consultation with Dr. Smith regarding heart health',
                'category' => 'consultation_note',
                'file_name' => 'cardiology_consult_jan2024.pdf',
                'file_type' => 'application/pdf',
                'file_size' => 640000,
                'created_at' => '2024-01-03',
                'tags' => ['cardiology', 'consultation', 'heart health'],
                'is_confidential' => false
            ]
        ];
    }

    /**
     * Helper method to format file size.
     */
    private function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Helper method to get category label.
     */
    private function getCategoryLabel($category)
    {
        $labels = [
            'lab_report' => 'Lab Report',
            'imaging' => 'Medical Imaging',
            'prescription' => 'Prescription',
            'insurance' => 'Insurance Document',
            'consultation_note' => 'Consultation Note',
            'discharge_summary' => 'Discharge Summary',
            'referral' => 'Referral Letter',
            'consent_form' => 'Consent Form',
            'other' => 'Other Document'
        ];

        return $labels[$category] ?? 'Document';
    }

    /**
     * Helper method to get file icon.
     */
    private function getFileIcon($fileType)
    {
        $icons = [
            'application/pdf' => '📄',
            'application/msword' => '📝',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '📝',
            'image/jpeg' => '🖼️',
            'image/jpg' => '🖼️',
            'image/png' => '🖼️',
            'text/plain' => '📋',
            'text/csv' => '📊',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '📊'
        ];

        return $icons[$fileType] ?? '📄';
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


public function getDoctorDocuments(Request $request)
{
    try {
        $userId = auth()->id();
        \Log::debug("Fetching documents for user ID: {$userId}");

        // Get documents with doctor information
        $documents = DoctorDocument::with(['doctor.user:id,name'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get([
                'id',
                'doctor_id',
                'title',
                'category',
                'file_name',
                'file_type',
                'file_size',
                'description',
                'created_at'
            ]);

        \Log::debug("Found documents count: " . $documents->count());

        if ($documents->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'No documents found',
                'documents' => []
            ]);
        }

        // Format documents with doctor name
        $formattedDocuments = $documents->map(function ($doc) {
            return [
                'id' => $doc->id,
                'title' => $doc->title,
                'category' => $doc->category,
                'file_name' => $doc->file_name,
                'file_type' => $doc->file_type,
                'file_size' => $doc->file_size,
                'created_at' => $doc->created_at,
                'description' => $doc->description,
                'doctor_name' => $doc->doctor->user->name ?? 'Doctor'
            ];
        });

        return response()->json([
            'success' => true,
            'documents' => $formattedDocuments,
            'debug_info' => [
                'user_id' => $userId,
                'document_count' => $documents->count()
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Failed to fetch doctor documents', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'success' => false,
            'message' => 'Failed to load documents: ' . $e->getMessage()
        ], 500);
    }
}
public function downloadDoctorDocument($id)
{
    try {
        $document = DoctorDocument::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($document->file_path && Storage::exists($document->file_path)) {
            return Storage::download($document->file_path, $document->file_name);
        }

        if ($document->file_content) {
            $fileContent = base64_decode($document->file_content);
            return response()->make(
                $fileContent,
                200,
                [
                    'Content-Type' => $document->file_type,
                    'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"'
                ]
            );
        }

        abort(404, 'File not found');

    } catch (\Exception $e) {
        \Log::error('Failed to download doctor document', [
            'document_id' => $id,
            'error' => $e->getMessage()
        ]);

        abort(404, 'Document not found or access denied');
    }
}

}

