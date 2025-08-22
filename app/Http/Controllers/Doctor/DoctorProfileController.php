<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DoctorProfileController extends Controller
{
    public function edit()
    {
        $doctor = Auth::user()->doctor;
        return view('doctor.profile.edit', compact('doctor'));
    }

    public function update(Request $request)
    {
        $doctor = Auth::user()->doctor;

        $validated = $request->validate([
            'license_number' => ['nullable', 'string', 'max:255'],
            'specialization' => ['required', 'string', 'max:255'],
            'qualifications' => ['nullable', 'string'],
            'hospital_affiliation' => ['nullable', 'string', 'max:255'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:100'],
            'bio' => ['nullable', 'string'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0'],
            'accepts_emergency_calls' => ['boolean'],
            'available_hours' => ['nullable', 'json'],
            'status' => ['required', Rule::in(['active', 'on_leave', 'not_available'])],
        ]);

        // Handle checkbox input
        $validated['accepts_emergency_calls'] = $request->has('accepts_emergency_calls');

    // Create or update the doctor profile
    $doctor = auth()->user()->doctor()->updateOrCreate([], $validated);

    return redirect()->back()->with('success', 'Profile updated successfully');

        // try {
        //     $doctor->update($validated);

        //     return redirect()->route('doctor.profile.edit')
        //         ->with('success', 'Profile updated successfully!');

        // } catch (\Exception $e) {
        //     return back()->withInput()
        //         ->with('error', 'Error updating profile: ' . $e->getMessage());
        // }
    }
}
