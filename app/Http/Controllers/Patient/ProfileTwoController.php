<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileTwoController extends Controller
{
    public function edit()
    {
        $patient = Auth::user()->patient;
        return view('patient.profile.edit', compact('patient'));
    }

    public function update(Request $request)
    {
        $patient = Auth::user()->patient;
        
        $validated = $request->validate([
            'blood_type' => ['nullable', 'string', 'max:255'],
            'height' => ['nullable', 'numeric', 'between:0,300'],
            'current_weight' => ['nullable', 'numeric', 'between:0,1000'],
            'allergies' => ['nullable', 'string'],
            'chronic_conditions' => ['nullable', 'string'],
            'current_medications' => ['nullable', 'string'],
            'insurance_provider' => ['nullable', 'string', 'max:255'],
            'insurance_policy_number' => ['nullable', 'string', 'max:255'],
            'family_medical_history' => ['nullable', 'string'],
            'activity_level' => ['nullable', Rule::in([
                'sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extremely_active'
            ])],
            'smoker' => ['nullable', 'boolean'],
            'alcohol_consumption' => ['nullable', 'integer', 'min:0', 'max:100'],
            'dietary_restrictions' => ['nullable', 'string'],
            'emergency_contacts' => ['nullable', 'string'],
            'baseline_heart_rate' => ['nullable', 'numeric', 'between:0,300'],
            'baseline_blood_pressure' => ['nullable', 'string', 'max:255'],
            'baseline_temperature' => ['nullable', 'numeric', 'between:0,120'],
        ]);

        // Handle checkbox input
        $validated['smoker'] = $request->has('smoker');

        try {
            $patient->update($validated);
            
            return redirect()->route('patient.profile.edit')
                ->with('success', 'Profile updated successfully!');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Error updating profile: ' . $e->getMessage());
        }
    }
}