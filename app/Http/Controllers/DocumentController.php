<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class DocumentController extends Controller
{
    /**
     * Display a listing of documents based on user role
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'patient') {
            return $this->patientDocumentsIndex($request);
        } elseif ($user->role === 'doctor') {
            return $this->doctorDocumentsIndex($request);
        } elseif ($user->role === 'admin') {
            return $this->adminDocumentsIndex($request);
        }
        
        abort(403, 'Unauthorized access');
    }

    /**
     * Patient documents index - exactly matching your existing blade template expectations
     */
    private function patientDocumentsIndex(Request $request)
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            return redirect()->route('patient.dashboard')
                ->with('error', 'Patient profile not found. Please contact support.');
        }

        // Get documents - return actual Document models for blade compatibility
        $documents = Document::where('patient_id', $patient->id)
            ->where('status', 'active')
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
     * Doctor documents index
     */
    private function doctorDocumentsIndex(Request $request)
    {
        $documents = Document::with(['patient.user'])
            ->where('uploaded_by', auth()->id())
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'documents' => $documents->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'title' => $doc->title,
                        'category' => $doc->category,
                        'file_name' => $doc->file_name,
                        'file_type' => $doc->file_type,
                        'file_size' => $doc->file_size,
                        'created_at' => $doc->created_at,
                        'patient_name' => $doc->patient->user->name ?? 'Unknown'
                    ];
                })
            ]);
        }

        return view('doctor.documents.index', compact('documents'));
    }

    /**
     * Admin documents index
     */
    private function adminDocumentsIndex(Request $request)
    {
        $documents = Document::with(['patient.user', 'uploadedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('admin.documents.index', compact('documents'));
    }

    /**
     * Store a newly created document
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        if ($user->role === 'patient') {
            return $this->storePatientDocument($request);
        } elseif ($user->role === 'doctor') {
            return $this->storeDoctorDocument($request);
        }
        
        abort(403, 'Unauthorized');
    }

    /**
     * Store patient document
     */
    private function storePatientDocument(Request $request)
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

            // Check for duplicates
            $existingDoc = Document::findByHash(hash('sha256', $fileContent), $patient->id);
            if ($existingDoc) {
                return response()->json([
                    'success' => false,
                    'message' => 'A document with identical content already exists.',
                    'existing_document' => $existingDoc->title
                ], 422);
            }

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
                            array_map('trim', explode(',', $validated['tags']))
                        ),
                    0, 5)
                ) : null,
                'is_confidential' => $validated['is_confidential'] ?? false,
                'status' => 'active'
            ]);

            // Set uploader_type if column exists
            if (Schema::hasColumn('documents', 'uploader_type')) {
                $document->uploader_type = 'patient';
            }

            $document->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully!',
                'document' => $document->toArray()
            ]);

        } catch (ValidationException $e) {
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
     * Store doctor document for patient
     */
    private function storeDoctorDocument(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'file' => 'required|file|max:10240',
                'description' => 'nullable|string'
            ]);

            // Find patient by user_id
            $patient = Patient::where('user_id', $validated['user_id'])->first();
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient not found'
                ], 404);
            }

            // Verify doctor has access to this patient
            if (!auth()->user()->doctor->patients()->where('patients.id', $patient->id)->exists()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $file = $request->file('file');
            $fileContent = file_get_contents($file->getRealPath());

            $document = Document::create([
                'id' => Str::uuid(),
                'patient_id' => $patient->id,
                'uploaded_by' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'file_data' => base64_encode($fileContent),
                'file_hash' => hash('sha256', $fileContent),
                'category' => $this->mapCategory($validated['category']),
                'uploader_type' => Schema::hasColumn('documents', 'uploader_type') ? 'registered_doctor' : null,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => $document->toArray()
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'doctor_id' => Auth::id(),
                'patient_id' => $request->input('user_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Display the specified document
     */
    public function show($documentId)
    {
        $document = $this->findDocumentWithAuth($documentId);
        
        if (!$document) {
            abort(404, 'Document not found or access denied');
        }

        if (!$document->hasFileContent()) {
            abort(404, 'File content not found');
        }

        $fileContent = $document->getFileContent();

        return response($fileContent)
            ->header('Content-Type', $document->file_type)
            ->header('Content-Disposition', 'inline; filename="' . $document->file_name . '"');
    }

    /**
     * Download the specified document
     */
    public function download($documentId)
    {
        $document = $this->findDocumentWithAuth($documentId);
        
        if (!$document) {
            abort(404, 'Document not found or access denied');
        }

        if ($document->file_data) {
            $fileContent = base64_decode($document->file_data);
            return response($fileContent)
                ->header('Content-Type', $document->file_type)
                ->header('Content-Disposition', 'attachment; filename="' . $document->file_name . '"');
        }

        abort(404, 'File content not found');
    }

    /**
     * Update the specified document
     */
    public function update(Request $request, $documentId)
    {
        try {
            $document = $this->findDocumentWithAuth($documentId, true);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found or access denied'
                ], 404);
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'category' => 'required|in:lab_report,imaging,prescription,insurance,consultation_note,discharge_summary,referral,consent_form,other',
                'tags' => 'nullable|string|max:500',
                'is_confidential' => 'boolean'
            ]);

            $document->update($validated);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document updated successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Document updated successfully!');

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
     * Remove the specified document
     */
    public function destroy($documentId)
    {
        try {
            $document = $this->findDocumentWithAuth($documentId, true);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found or access denied'
                ], 404);
            }

            $document->markAsDeleted();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'document_id' => $documentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document'
            ], 500);
        }
    }

    /**
     * Bulk upload documents
     */
    public function bulkUpload(Request $request)
    {
        try {
            $request->validate([
                'files' => 'required|array|max:10',
                'files.*' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt,csv,xlsx',
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

            $uploadedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($request->file('files') as $file) {
                try {
                    $fileContent = file_get_contents($file->getRealPath());
                    $hash = hash('sha256', $fileContent);

                    // Check for duplicates
                    if (Document::findByHash($hash, $patient->id)) {
                        $errors[] = "File {$file->getClientOriginalName()} already exists";
                        continue;
                    }

                    $document = new Document([
                        'patient_id' => $patient->id,
                        'uploaded_by' => auth()->id(),
                        'title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'file_data' => base64_encode($fileContent),
                        'file_hash' => $hash,
                        'category' => $request->category,
                        'tags' => $request->tags ? json_encode(
                            array_map('trim', explode(',', $request->tags))
                        ) : null,
                        'is_confidential' => $request->boolean('is_confidential', false),
                        'status' => 'active'
                    ]);

                    // Set uploader_type if column exists
                    if (Schema::hasColumn('documents', 'uploader_type')) {
                        $document->uploader_type = 'patient';
                    }

                    $document->save();
                    $uploadedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to upload {$file->getClientOriginalName()}: " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$uploadedCount documents uploaded successfully.",
                'uploaded_count' => $uploadedCount,
                'error_count' => count($errors),
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk upload: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Bulk upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Get doctor-shared documents for patient
     */
    public function getDoctorDocuments(Request $request)
    {
        try {
            $user = auth()->user();
            $patient = $user->patient;
            
            if (!$patient) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient profile not found'
                ], 404);
            }

            // Get documents uploaded by others (doctors) for this patient
            $documents = Document::where('patient_id', $patient->id)
                ->where('status', 'active')
                ->whereNotNull('uploaded_by')
                ->where('uploaded_by', '!=', $user->id)
                ->with(['uploadedBy'])
                ->orderBy('created_at', 'desc')
                ->get();

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
                    'doctor_name' => $doc->uploadedBy->name ?? 'Doctor'
                ];
            });

            return response()->json([
                'success' => true,
                'documents' => $formattedDocuments
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch doctor documents', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to load documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download doctor-shared document
     */
    public function downloadDoctorDocument($id)
    {
        try {
            $user = auth()->user();
            $patient = $user->patient;
            
            if (!$patient) {
                abort(404, 'Patient profile not found');
            }

            $document = Document::where('id', $id)
                ->where('patient_id', $patient->id)
                ->where('uploaded_by', '!=', $user->id)
                ->firstOrFail();

            return $this->download($id);

        } catch (\Exception $e) {
            Log::error('Failed to download doctor document', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);

            abort(404, 'Document not found or access denied');
        }
    }

    /**
     * Store document with ticket (guest upload)
     */
    public function storeWithTicket(Request $request)
    {
        try {
            $validated = $request->validate([
                'ticket_token' => 'required|string',
                'patient_id' => 'required|exists:patients,id',
                'title' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'file' => 'required|file|max:10240',
                'description' => 'nullable|string',
                'uploader_name' => 'nullable|string|max:255',
                'uploader_workplace' => 'nullable|string|max:255',
            ]);

            // Verify ticket is valid and not expired
            $ticketExpiry = cache()->get('ticket_' . $validated['ticket_token']);
            if (!$ticketExpiry || Carbon::now()->gt($ticketExpiry)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired ticket'
                ], 403);
            }

            $file = $request->file('file');
            $fileContent = file_get_contents($file->getRealPath());

            $document = Document::create([
                'id' => Str::uuid(),
                'patient_id' => $validated['patient_id'],
                'uploaded_by' => null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'file_data' => base64_encode($fileContent),
                'file_hash' => hash('sha256', $fileContent),
                'category' => $this->mapCategory($validated['category']),
                'uploader_type' => Schema::hasColumn('documents', 'uploader_type') ? 'guest_ticket' : null,
                'uploader_name' => Schema::hasColumn('documents', 'uploader_name') ? $validated['uploader_name'] : null,
                'uploader_workplace' => Schema::hasColumn('documents', 'uploader_workplace') ? $validated['uploader_workplace'] : null,
                'ticket_token' => Schema::hasColumn('documents', 'ticket_token') ? $validated['ticket_token'] : null,
                'ticket_expires_at' => Schema::hasColumn('documents', 'ticket_expires_at') ? $ticketExpiry : null,
                'status' => 'active'
            ]);

            // Remove ticket from cache (one-time use)
            cache()->forget('ticket_' . $validated['ticket_token']);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => [
                    'id' => $document->id,
                    'title' => $document->title,
                    'file_name' => $document->file_name,
                    'created_at' => $document->created_at
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Ticket document upload failed', [
                'error' => $e->getMessage(),
                'ticket' => $request->input('ticket_token')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed. Please try again.'
            ], 500);
        }
    }

    /**
     * Find document with proper authorization
     */
    private function findDocumentWithAuth($documentId, $requireOwnership = false)
    {
        $user = Auth::user();
        $document = Document::with(['patient.user', 'uploadedBy'])->find($documentId);
        
        if (!$document) {
            return null;
        }

        $hasAccess = false;

        if ($user->role === 'admin') {
            $hasAccess = true;
        } elseif ($user->role === 'patient' && $user->patient) {
            $hasAccess = $user->patient->id === $document->patient_id;
        } elseif ($user->role === 'doctor' && $user->doctor) {
            if ($requireOwnership) {
                $hasAccess = $document->uploaded_by === $user->id;
            } else {
                $hasAccess = $user->doctor->patients()->where('patients.id', $document->patient_id)->exists();
            }
        }

        return $hasAccess ? $document : null;
    }

    /**
     * Map category for storage
     */
    private function mapCategory($category)
    {
        $categoryMap = [
            'lab_report' => 'lab_report',
            'imaging' => 'imaging',
            'prescription' => 'prescription',
            'insurance' => 'insurance',
            'consultation_note' => 'consultation_note',
            'discharge_summary' => 'discharge_summary',
            'referral' => 'referral',
            'consent_form' => 'consent_form',
        ];

        return $categoryMap[$category] ?? 'other';
    }
}