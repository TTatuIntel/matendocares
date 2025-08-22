@extends('patient.layout')

@section('title', 'Profile Settings')
@section('page-title', 'Edit Profile')
@section('page-description', 'Update your personal and medical information')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-100 border-l-4 border-green-500 text-green-700">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-700">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <div class="medical-card p-8">
<form action="{{ route('patient.profile.update.edit') }}" method="POST">
            @csrf
  @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Blood Type -->
                        <div>
                            <label for="blood_type" class="block text-sm font-medium text-gray-700 mb-1">Blood Type</label>
                            <select id="blood_type" name="blood_type" class="form-input">
                                <option value="">Select blood type</option>
                                @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $type)
                                <option value="{{ $type }}" {{ old('blood_type', $patient->blood_type) == $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                                @endforeach
                            </select>
                            @error('blood_type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Height -->
                        <div>
                            <label for="height" class="block text-sm font-medium text-gray-700 mb-1">Height (cm)</label>
                            <input type="number" step="0.1" id="height" name="height"
                                   value="{{ old('height', $patient->height) }}"
                                   class="form-input" placeholder="e.g. 175.5">
                            @error('height')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Weight -->
                        <div>
                            <label for="current_weight" class="block text-sm font-medium text-gray-700 mb-1">Weight (kg)</label>
                            <input type="number" step="0.1" id="current_weight" name="current_weight"
                                   value="{{ old('current_weight', $patient->current_weight) }}"
                                   class="form-input" placeholder="e.g. 70.2">
                            @error('current_weight')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Activity Level -->
                        <div>
                            <label for="activity_level" class="block text-sm font-medium text-gray-700 mb-1">Activity Level</label>
                            <select id="activity_level" name="activity_level" class="form-input">
                                <option value="">Select activity level</option>
                                @foreach(['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extremely_active'] as $level)
                                <option value="{{ $level }}" {{ old('activity_level', $patient->activity_level) == $level ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $level)) }}
                                </option>
                                @endforeach
                            </select>
                            @error('activity_level')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Health Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Health Information</h3>
                    <div class="space-y-4">
                        <!-- Allergies -->
                        <div>
                            <label for="allergies" class="block text-sm font-medium text-gray-700 mb-1">Allergies</label>
                            <textarea id="allergies" name="allergies" rows="3" class="form-input">{{ old('allergies', $patient->allergies) }}</textarea>
                            @error('allergies')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Chronic Conditions -->
                        <div>
                            <label for="chronic_conditions" class="block text-sm font-medium text-gray-700 mb-1">Chronic Conditions</label>
                            <textarea id="chronic_conditions" name="chronic_conditions" rows="3" class="form-input">{{ old('chronic_conditions', $patient->chronic_conditions) }}</textarea>
                            @error('chronic_conditions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Current Medications -->
                        <div>
                            <label for="current_medications" class="block text-sm font-medium text-gray-700 mb-1">Current Medications</label>
                            <textarea id="current_medications" name="current_medications" rows="3" class="form-input">{{ old('current_medications', $patient->current_medications) }}</textarea>
                            @error('current_medications')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Lifestyle & Insurance -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Lifestyle & Insurance</h3>
                    <div class="space-y-4">
                        <!-- Smoker -->
                        <div class="flex items-center">
                            <input type="checkbox" id="smoker" name="smoker" value="1"
                                   {{ old('smoker', $patient->smoker) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="smoker" class="ml-2 block text-sm text-gray-700">Smoker</label>
                            @error('smoker')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alcohol Consumption -->
                        <div>
                            <label for="alcohol_consumption" class="block text-sm font-medium text-gray-700 mb-1">Alcohol Consumption (drinks/week)</label>
                            <input type="number" id="alcohol_consumption" name="alcohol_consumption"
                                   value="{{ old('alcohol_consumption', $patient->alcohol_consumption) }}"
                                   class="form-input" min="0" max="100">
                            @error('alcohol_consumption')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Dietary Restrictions -->
                        <div>
                            <label for="dietary_restrictions" class="block text-sm font-medium text-gray-700 mb-1">Dietary Restrictions</label>
                            <textarea id="dietary_restrictions" name="dietary_restrictions" rows="2" class="form-input">{{ old('dietary_restrictions', $patient->dietary_restrictions) }}</textarea>
                            @error('dietary_restrictions')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Insurance Provider -->
                        <div>
                            <label for="insurance_provider" class="block text-sm font-medium text-gray-700 mb-1">Insurance Provider</label>
                            <input type="text" id="insurance_provider" name="insurance_provider"
                                   value="{{ old('insurance_provider', $patient->insurance_provider) }}"
                                   class="form-input">
                            @error('insurance_provider')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Insurance Policy Number -->
                        <div>
                            <label for="insurance_policy_number" class="block text-sm font-medium text-gray-700 mb-1">Policy Number</label>
                            <input type="text" id="insurance_policy_number" name="insurance_policy_number"
                                   value="{{ old('insurance_policy_number', $patient->insurance_policy_number) }}"
                                   class="form-input">
                            @error('insurance_policy_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Emergency Contacts -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Emergency Contacts</h3>
                    <div class="space-y-4">
                        <textarea id="emergency_contacts" name="emergency_contacts" rows="4"
                                  class="form-input" placeholder="Enter emergency contacts as JSON or plain text">{{ old('emergency_contacts', $patient->emergency_contacts) }}</textarea>
                        <p class="text-sm text-gray-500">Format: Name, Relationship, Phone (one per line)</p>
                        @error('emergency_contacts')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Baseline Measurements -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Baseline Measurements</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Baseline Heart Rate -->
                        <div>
                            <label for="baseline_heart_rate" class="block text-sm font-medium text-gray-700 mb-1">Heart Rate (bpm)</label>
                            <input type="number" step="0.1" id="baseline_heart_rate" name="baseline_heart_rate"
                                   value="{{ old('baseline_heart_rate', $patient->baseline_heart_rate) }}"
                                   class="form-input">
                            @error('baseline_heart_rate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Baseline Blood Pressure -->
                        <div>
                            <label for="baseline_blood_pressure" class="block text-sm font-medium text-gray-700 mb-1">Blood Pressure</label>
                            <input type="text" id="baseline_blood_pressure" name="baseline_blood_pressure"
                                   value="{{ old('baseline_blood_pressure', $patient->baseline_blood_pressure) }}"
                                   class="form-input" placeholder="e.g. 120/80">
                            @error('baseline_blood_pressure')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Baseline Temperature -->
                        <div>
                            <label for="baseline_temperature" class="block text-sm font-medium text-gray-700 mb-1">Temperature (°F)</label>
                            <input type="number" step="0.1" id="baseline_temperature" name="baseline_temperature"
                                   value="{{ old('baseline_temperature', $patient->baseline_temperature) }}"
                                   class="form-input">
                            @error('baseline_temperature')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('patient.dashboard') }}" class="btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn-primary">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
