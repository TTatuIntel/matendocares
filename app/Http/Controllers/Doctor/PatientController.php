<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class PatientController extends Controller
{
    //
public function index()
    {
        $patients = User::where('role', 'patient')
                      ->whereNull('deleted_at')
                      ->paginate(10);

        return view('doctor.patient', compact('patients'));
    }


public function show($patientId)
    {
        // Get the patient with all related data
        $patient = User::with(['patient.vitalSigns' => function($query) {
            $query->orderBy('measured_at', 'desc');
        }])->findOrFail($patientId);

        // Get latest vital signs
        $latestVitals = optional($patient->patient)->vitalSigns->first();

        return view('doctor.patient-monitor', compact('patient', 'latestVitals'));
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

