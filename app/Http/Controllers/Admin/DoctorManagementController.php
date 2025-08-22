<?php

// app/Http/Controllers/Admin/DoctorManagementController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\DoctorPatient;
use Illuminate\Http\Request;

class DoctorManagementController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('user')->paginate(20);
        return view('admin.doctors', compact('doctors'));
    }

    public function verify(Doctor $doctor)
    {
        $doctor->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id()
        ]);

        return response()->json(['success' => true, 'message' => 'Doctor verified successfully']);
    }

    public function assignPatients(Request $request, Doctor $doctor)
    {
        $request->validate([
            'patient_ids' => 'required|array',
            'patient_ids.*' => 'exists:patients,id'
        ]);

        foreach ($request->patient_ids as $patientId) {
            DoctorPatient::firstOrCreate([
                'doctor_id' => $doctor->id,
                'patient_id' => $patientId,
                'relationship_type' => 'primary'
            ], [
                'id' => \Str::uuid(),
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
                'status' => 'active'
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Patients assigned successfully']);
    }
}