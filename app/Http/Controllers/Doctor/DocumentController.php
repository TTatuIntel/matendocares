<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\Patient;
use App\Models\DoctorPatient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display all documents accessible to the doctor
     */
    public function index(Request $request)
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

        $query = Document::whereIn('patient_id', $patientIds)
            ->with(['patient.user']);

        // Filter by document type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by patient
        if ($request->has('patient_id') && $request->patient_id !== 'all') {
            $query->where('patient_id', $request->patient_id);
        }

        // Search by filename or description
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('uploaded_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('uploaded_at', '<=', $request->date_to);
        }

        $documents = $query->orderBy('uploaded_at', 'desc')->paginate(20);

        // Get patients for filter dropdown
        $patients = Patient::whereIn('id', $patientIds)
            ->with('user')
            ->get()
            ->map(function($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->user->name ?? 'Unknown Patient'
                ];
            });

        // Get document statistics
        $stats = [
            'total' => Document::whereIn('patient_id', $patientIds)->count(),
            'this_month' => Document::whereIn('patient_id', $patientIds)
                ->whereMonth('uploaded_at', now()->month)->count(),
            'pending_review' => Document::whereIn('patient_id', $patientIds)
                ->where('reviewed_by_doctor', false)->count(),
            'types' => Document::whereIn('patient_id', $patientIds)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray()
        ];

        return view('doctor.documents.index', compact('documents', 'patients', 'stats'));
    }

    /**
     * Display documents for a specific patient
     */
    public function patientDocuments(Patient $patient)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('doctor.documents.index')
                ->with('error', 'Access denied to patient documents.');
        }

        $documents = Document::where('patient_id', $patient->id)
            ->orderBy('uploaded_at', 'desc')
            ->paginate(15);

        $patient->load('user');

        // Document statistics for this patient
        $stats = [
            'total' => $documents->total(),
            'by_type' => Document::where('patient_id', $patient->id)
                ->selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->pluck('count', 'type')
                ->toArray(),
            'pending_review' => Document::where('patient_id', $patient->id)
                ->where('reviewed_by_doctor', false)->count(),
            'recent' => Document::where('patient_id', $patient->id)
                ->where('uploaded_at', '>=', now()->subDays(30))->count()
        ];

        return view('doctor.documents.patient', compact('patient', 'documents', 'stats'));
    }

    /**
     * Upload a new document
     */
    public function upload(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            'type' => 'required|in:medical_record,lab_result,prescription,imaging,report,other',
            'description' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $request->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->back()
                ->with('error', 'Access denied to upload documents for this patient.')
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileType = $file->getMimeType();

            // Read file content and encode as base64
            $fileContent = file_get_contents($file->getRealPath());
            $fileData = base64_encode($fileContent);

            $document = Document::create([
                'patient_id' => $request->patient_id,
                'uploaded_by' => $user->id,
                'uploaded_by_type' => 'doctor',
                'file_name' => $fileName,
                'file_size' => $fileSize,
                'file_type' => $fileType,
                'file_data' => $fileData,
                'type' => $request->type,
                'description' => $request->description,
                'category' => $request->category,
                'uploaded_at' => now(),
                'reviewed_by_doctor' => true, // Since doctor is uploading
                'reviewed_at' => now(),
                'reviewed_by' => $user->id
            ]);

            return redirect()->back()
                ->with('success', 'Document uploaded successfully.');

        } catch (\Exception $e) {
            \Log::error('Document Upload Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error uploading document. Please try again.')
                ->withInput();
        }
    }

    /**
     * Show document details
     */
    public function show(Document $document)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this document's patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $document->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('doctor.documents.index')
                ->with('error', 'Access denied to this document.');
        }

        $document->load(['patient.user', 'uploadedBy']);

        return view('doctor.documents.show', compact('document'));
    }

    /**
     * Review a document
     */
    public function review(Request $request, Document $document)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this document's patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $document->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('doctor.documents.index')
                ->with('error', 'Access denied to this document.');
        }

        $validator = Validator::make($request->all(), [
            'review_notes' => 'nullable|string|max:1000',
            'review_status' => 'required|in:approved,requires_attention,rejected',
            'follow_up_required' => 'nullable|boolean',
            'follow_up_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $document->update([
                'reviewed_by_doctor' => true,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
                'review_notes' => $request->review_notes,
                'review_status' => $request->review_status,
                'follow_up_required' => $request->follow_up_required ?? false,
                'follow_up_notes' => $request->follow_up_notes
            ]);

            // Create follow-up task if required
            if ($request->follow_up_required) {
                $this->createFollowUpTask($document, $request->follow_up_notes);
            }

            $statusMessage = match($request->review_status) {
                'approved' => 'Document approved successfully.',
                'requires_attention' => 'Document marked as requiring attention.',
                'rejected' => 'Document rejected.',
                default => 'Document reviewed successfully.'
            };

            return redirect()->back()->with('success', $statusMessage);

        } catch (\Exception $e) {
            \Log::error('Document Review Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error reviewing document. Please try again.')
                ->withInput();
        }
    }

    /**
     * Delete a document
     */
    public function destroy(Document $document)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this document's patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $document->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->route('doctor.documents.index')
                ->with('error', 'Access denied to delete this document.');
        }

        try {
            $document->delete();

            return redirect()->back()
                ->with('success', 'Document deleted successfully.');

        } catch (\Exception $e) {
            \Log::error('Document Deletion Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting document. Please try again.');
        }
    }

    /**
     * Download a document
     */
    public function download(Document $document)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this document's patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $document->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Access denied to download this document.');
        }

        try {
            // Decode base64 file data
            $fileData = base64_decode($document->file_data);

            return response($fileData)
                ->header('Content-Type', $document->file_type)
                ->header('Content-Disposition', 'attachment; filename="' . $document->file_name . '"')
                ->header('Content-Length', strlen($fileData));

        } catch (\Exception $e) {
            \Log::error('Document Download Error: ' . $e->getMessage());
            abort(500, 'Error downloading document.');
        }
    }

    /**
     * View a document inline
     */
    public function view(Document $document)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this document's patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $document->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            abort(403, 'Access denied to view this document.');
        }

        try {
            // Decode base64 file data
            $fileData = base64_decode($document->file_data);

            return response($fileData)
                ->header('Content-Type', $document->file_type)
                ->header('Content-Disposition', 'inline; filename="' . $document->file_name . '"');

        } catch (\Exception $e) {
            \Log::error('Document View Error: ' . $e->getMessage());
            abort(500, 'Error viewing document.');
        }
    }

    /**
     * Bulk upload documents
     */
    public function bulkUpload(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt',
            'type' => 'required|in:medical_record,lab_result,prescription,imaging,report,other',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $request->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->back()
                ->with('error', 'Access denied to upload documents for this patient.')
                ->withInput();
        }

        try {
            $uploadedCount = 0;
            $errors = [];

            foreach ($request->file('files') as $file) {
                try {
                    $fileName = $file->getClientOriginalName();
                    $fileSize = $file->getSize();
                    $fileType = $file->getMimeType();

                    // Read file content and encode as base64
                    $fileContent = file_get_contents($file->getRealPath());
                    $fileData = base64_encode($fileContent);

                    Document::create([
                        'patient_id' => $request->patient_id,
                        'uploaded_by' => $user->id,
                        'uploaded_by_type' => 'doctor',
                        'file_name' => $fileName,
                        'file_size' => $fileSize,
                        'file_type' => $fileType,
                        'file_data' => $fileData,
                        'type' => $request->type,
                        'description' => $request->description,
                        'category' => $request->category,
                        'uploaded_at' => now(),
                        'reviewed_by_doctor' => true,
                        'reviewed_at' => now(),
                        'reviewed_by' => $user->id
                    ]);

                    $uploadedCount++;

                } catch (\Exception $e) {
                    $errors[] = "Failed to upload {$fileName}: " . $e->getMessage();
                }
            }

            $message = "Successfully uploaded {$uploadedCount} document(s).";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', $errors);
            }

            return redirect()->back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Bulk Document Upload Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error uploading documents. Please try again.')
                ->withInput();
        }
    }

    /**
     * Search documents across all accessible patients
     */
    public function search(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255',
            'type' => 'nullable|in:medical_record,lab_result,prescription,imaging,report,other',
            'patient_id' => 'nullable|exists:patients,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => 'Invalid search parameters'], 400);
        }

        try {
            // Get patient IDs that this doctor has access to
            $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
                ->where('status', 'active')
                ->pluck('patient_id');

            $query = Document::whereIn('patient_id', $patientIds)
                ->with(['patient.user']);

            // Search in filename, description, and category
            $searchTerm = $request->query;
            $query->where(function($q) use ($searchTerm) {
                $q->where('file_name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });

            // Apply filters
            if ($request->type) {
                $query->where('type', $request->type);
            }

            if ($request->patient_id) {
                $query->where('patient_id', $request->patient_id);
            }

            $documents = $query->orderBy('uploaded_at', 'desc')
                ->limit(20)
                ->get()
                ->map(function($document) {
                    return [
                        'id' => $document->id,
                        'file_name' => $document->file_name,
                        'type' => ucfirst(str_replace('_', ' ', $document->type)),
                        'patient_name' => $document->patient->user->name ?? 'Unknown',
                        'uploaded_at' => $document->uploaded_at->format('M d, Y'),
                        'url' => route('doctor.documents.show', $document)
                    ];
                });

            return response()->json(['documents' => $documents]);

        } catch (\Exception $e) {
            \Log::error('Document Search Error: ' . $e->getMessage());
            return response()->json(['error' => 'Search error occurred'], 500);
        }
    }

    /**
     * Get document statistics for dashboard
     */
    public function getStats()
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        try {
            // Get patient IDs that this doctor has access to
            $patientIds = DoctorPatient::where('doctor_id', $doctor->id)
                ->where('status', 'active')
                ->pluck('patient_id');

            $stats = [
                'total_documents' => Document::whereIn('patient_id', $patientIds)->count(),
                'pending_review' => Document::whereIn('patient_id', $patientIds)
                    ->where('reviewed_by_doctor', false)->count(),
                'reviewed_today' => Document::whereIn('patient_id', $patientIds)
                    ->whereDate('reviewed_at', today())->count(),
                'uploaded_this_week' => Document::whereIn('patient_id', $patientIds)
                    ->where('uploaded_at', '>=', now()->startOfWeek())->count(),
                'by_type' => Document::whereIn('patient_id', $patientIds)
                    ->selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->pluck('count', 'type')
                    ->toArray()
            ];

            return response()->json($stats);

        } catch (\Exception $e) {
            \Log::error('Document Stats Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error fetching statistics'], 500);
        }
    }

    /**
     * Generate document report for patient
     */
    public function generateReport(Request $request)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'document_types' => 'nullable|array',
            'format' => 'required|in:pdf,csv'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $request->patient_id)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return redirect()->back()
                ->with('error', 'Access denied to generate report for this patient.');
        }

        try {
            $query = Document::where('patient_id', $request->patient_id);

            // Apply date filters
            if ($request->date_from) {
                $query->whereDate('uploaded_at', '>=', $request->date_from);
            }
            if ($request->date_to) {
                $query->whereDate('uploaded_at', '<=', $request->date_to);
            }

            // Apply document type filters
            if ($request->document_types && !empty($request->document_types)) {
                $query->whereIn('type', $request->document_types);
            }

            $documents = $query->with(['patient.user', 'uploadedBy'])
                ->orderBy('uploaded_at', 'desc')
                ->get();

            if ($request->format === 'csv') {
                return $this->generateCsvReport($documents, $request->patient_id);
            } else {
                return $this->generatePdfReport($documents, $request->patient_id);
            }

        } catch (\Exception $e) {
            \Log::error('Document Report Generation Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error generating document report.');
        }
    }

    /**
     * Get allowed file types
     */
    public function getAllowedFileTypes()
    {
        return response()->json([
            'types' => [
                'pdf' => 'PDF Documents',
                'doc' => 'Word Documents',
                'docx' => 'Word Documents',
                'jpg' => 'JPEG Images',
                'jpeg' => 'JPEG Images',
                'png' => 'PNG Images',
                'txt' => 'Text Files'
            ],
            'max_size' => '10MB',
            'max_files' => 10
        ]);
    }

    /**
     * Validate file before upload
     */
    public function validateFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,txt'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'errors' => $validator->errors()->all()
            ]);
        }

        $file = $request->file('file');

        return response()->json([
            'valid' => true,
            'file_info' => [
                'name' => $file->getClientOriginalName(),
                'size' => $this->formatFileSize($file->getSize()),
                'type' => $file->getMimeType()
            ]
        ]);
    }

    // Private helper methods

    /**
     * Create follow-up task for document
     */
    private function createFollowUpTask($document, $notes)
    {
        // Implement follow-up task creation logic
        // This could integrate with a task management system
        \Log::info("Follow-up task created for document {$document->id}: {$notes}");
    }

    /**
     * Generate CSV report
     */
    private function generateCsvReport($documents, $patientId)
    {
        $patient = Patient::with('user')->findOrFail($patientId);
        $filename = 'documents_' . Str::slug($patient->user->name ?? 'patient') . '_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($documents) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'File Name', 'Type', 'Category', 'Description', 'Uploaded Date',
                'Uploaded By', 'File Size', 'Reviewed', 'Review Status', 'Review Notes'
            ]);

            // CSV Data
            foreach ($documents as $document) {
                fputcsv($file, [
                    $document->file_name,
                    ucfirst(str_replace('_', ' ', $document->type)),
                    $document->category ?? '',
                    $document->description ?? '',
                    $document->uploaded_at->format('Y-m-d H:i'),
                    $document->uploadedBy->name ?? 'Unknown',
                    $this->formatFileSize($document->file_size),
                    $document->reviewed_by_doctor ? 'Yes' : 'No',
                    $document->review_status ? ucfirst($document->review_status) : '',
                    $document->review_notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate PDF report
     */
    private function generatePdfReport($documents, $patientId)
    {
        // Implement PDF generation using a library like DomPDF
        // This is a placeholder implementation
        return redirect()->back()->with('info', 'PDF report generation feature coming soon.');
    }

    /**
     * Format file size for display
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
