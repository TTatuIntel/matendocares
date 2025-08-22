<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorMed;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DoctorPatient;
use Illuminate\Support\Str;



class DoctorMedController extends Controller
{
    /**
     * Display a listing of medications for a patient
     */
    public function index(Request $request, $patientId)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Access denied to patient medications.'], 403);
        }

        $medications = DoctorMed::where('user_id', $patientId)
            ->where('doctor_id', $doctor->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['medications' => $medications]);
    }

    /**
     * Store a newly prescribed medication
     */
public function storeMedication(Request $request)
    {
        $user = auth()->user();

        if (!$user->doctor) {
            return response()->json(['error' => 'Only doctors can prescribe medications'], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'dosage' => 'required|string|max:255',
            'frequency' => 'required|string|max:255',
            'times' => 'required|array',
            'times.*' => 'string',
            'start_date' => 'required|date',
            'prescribed_by' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'refills' => 'required|integer|min:0',
            'status' => 'required|in:active,inactive,completed'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $medication = DoctorMed::create([
                'user_id' => $request->user_id,
                'doctor_id' => $user->doctor->id,
                'name' => $request->name,
                'generic_name' => $request->generic_name,
                'dosage' => $request->dosage,
                'frequency' => $request->frequency,
                'times' => json_encode($request->times),
                'start_date' => $request->start_date,
                'prescribed_by' => $request->prescribed_by ?? $user->name,
                'purpose' => $request->purpose,
                'instructions' => $request->instructions,
                'refills' => $request->refills,
                'status' => $request->status
            ]);

            return response()->json([
                'message' => 'Medication prescribed successfully',
                'medication' => $medication
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Medication Prescription Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error prescribing medication. Please try again.',
                'details' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Store health tips for a patient
     */
// In your DoctorMedController.php

public function storeHealthTips(Request $request)
{
    $user = auth()->user();

    if (!$user->doctor) {
        return response()->json(['error' => 'Only doctors can provide health tips'], 403);
    }

    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'health_tips' => 'required|string|max:2000'
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    try {
        $healthTip = DoctorMed::create([
            'user_id' => $request->user_id,
            'doctor_id' => $user->doctor->id,
            'name' => 'Health Tips',
            'health_tips' => $request->health_tips,
            'status' => 'active'
        ]);

        return response()->json([
            'message' => 'Health tips saved successfully',
            'health_tip' => $healthTip
        ], 201);

    } catch (\Exception $e) {
        \Log::error('Health Tips Error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Error saving health tips. Please try again.',
            'details' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

    /**
     * Update the specified medication
     */
    public function updateMedication(Request $request, $id)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'dosage' => 'sometimes|required|string|max:255',
            'frequency' => 'sometimes|required|string|max:255',
            'times' => 'sometimes|required|array',
            'start_date' => 'sometimes|required|date',
            'prescribed_by' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
            'instructions' => 'nullable|string',
            'refills' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|required|in:active,inactive,completed',
            'health_tips' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $medication = DoctorMed::findOrFail($id);

        // Verify doctor owns this medication record
        if ($medication->doctor_id !== $doctor->id) {
            return response()->json(['error' => 'Unauthorized to update this medication.'], 403);
        }

        try {
            $medication->update($request->all());

            return response()->json([
                'message' => 'Medication updated successfully',
                'medication' => $medication
            ]);

        } catch (\Exception $e) {
            \Log::error('Medication Update Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error updating medication. Please try again.'], 500);
        }
    }

    /**
     * Remove the specified medication
     */
    public function destroyMedication($id)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        $medication = DoctorMed::findOrFail($id);

        // Verify doctor owns this medication record
        if ($medication->doctor_id !== $doctor->id) {
            return response()->json(['error' => 'Unauthorized to delete this medication.'], 403);
        }

        try {
            $medication->delete();

            return response()->json(['message' => 'Medication deleted successfully']);

        } catch (\Exception $e) {
            \Log::error('Medication Deletion Error: ' . $e->getMessage());
            return response()->json(['error' => 'Error deleting medication. Please try again.'], 500);
        }
    }

    /**
     * Get all health tips for a patient
     */
    public function getHealthTips($patientId)
    {
        $user = auth()->user();
        $doctor = $user->doctor;

        // Verify doctor has access to this patient
        $hasAccess = DoctorPatient::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->exists();

        if (!$hasAccess) {
            return response()->json(['error' => 'Access denied to patient health tips.'], 403);
        }

        $healthTips = DoctorMed::where('user_id', $patientId)
            ->where('doctor_id', $doctor->id)
            ->whereNotNull('health_tips')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['health_tips' => $healthTips]);
    }
}
