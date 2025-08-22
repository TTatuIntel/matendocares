<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use App\Models\Document;
use App\Models\Patient;
use App\Models\VitalSign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DoctorDocumentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Document::with(['patient.user:id,name'])
                ->where('uploaded_by', auth()->id());

            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->patient_id);
            }

            $documents = $query->orderBy('created_at', 'desc')
                ->get([
                    'id',
                    'patient_id',
                    'uploaded_by',
                    'title',
                    'category',
                    'file_name',
                    'file_type',
                    'file_size',
                    'description',
                    'created_at'
                ]);

            $documents->transform(function ($document) {
                return [
                    'id' => $document->id,
                    'doctor_id' => $document->uploaded_by,
                    'patient_id' => $document->patient_id,
                    'title' => $document->title,
                    'category' => $document->category,
                    'file_name' => $document->file_name,
                    'file_type' => $document->file_type,
                    'file_size' => $document->file_size,
                    'description' => $document->description,
                    'created_at' => $document->created_at,
                    'patient_name' => $document->patient->user->name ?? 'Unknown'
                ];
            });

            return response()->json([
                'success' => true,
                'documents' => $documents
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to fetch documents', [
                'error' => $e->getMessage(),
                'doctor_id' => auth()->id(),
                'patient_id' => $request->input('patient_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load documents'
            ], 500);
        }
    }

    public function upload(Request $request)
{
    try {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string'
        ]);

        // Get the patient_id from the user_id
        $patient = Patient::where('user_id', $validated['user_id'])->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient record not found for this user'
            ], 404);
        }

        $file = $request->file('file');
        $fileContent = file_get_contents($file->getRealPath());
        $fileHash = hash('sha256', $fileContent);

        // Check if file already exists for this patient
        $existingDocument = Document::where('file_hash', $fileHash)
            ->where('patient_id', $patient->id)
            ->first();

        if ($existingDocument) {
            return response()->json([
                'success' => false,
                'message' => 'This document already exists for the patient'
            ], 409);
        }

        $document = Document::create([
            'id' => Str::uuid(),
            'patient_id' => $patient->id,
            'uploaded_by' => Auth::id(),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'file_data' => base64_encode($fileContent),
            'file_hash' => $fileHash,
            'description' => $validated['description'] ?? null,
            'status' => 'active'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully',
            'document' => $document->only([
                'id',
                'title',
                'category',
                'file_name',
                'file_type',
                'file_size',
                'description',
                'created_at'
            ])
        ]);

    } catch (ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        \Log::error('Document upload failed', [
            'error' => $e->getMessage(),
            'doctor_id' => Auth::id(),
            'user_id' => $request->input('user_id')
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Upload failed. Please try again.'
        ], 500);
    }
}

    public function download($id)
    {
        try {
            $document = Document::where('id', $id)
                ->where(function($query) {
                    $query->where('uploaded_by', Auth::id())
                          ->orWhereHas('patient', function($q) {
                              $q->where('user_id', Auth::id());
                          });
                })
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
                    'Content-Type' => $document->file_type,
                    'Content-Disposition' => 'attachment; filename="' . $document->file_name . '"',
                    'Content-Length' => strlen($fileContent),
                ]
            );

        } catch (\Exception $e) {
            \Log::error('Document download failed', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to download document'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $document = Document::where('id', $id)
                ->where('uploaded_by', Auth::id())
                ->firstOrFail();

            $document->update(['status' => 'deleted']);
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Document deletion failed', [
                'document_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document'
            ], 500);
        }
    }
}
