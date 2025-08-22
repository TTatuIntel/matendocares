@extends('doctor.layout')

@section('title', 'Profile Settings')
@section('page-title', 'Edit Profile')
@section('page-description', 'Update your professional information')

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
        <form action="{{ route('doctor.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Professional Information -->
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Professional Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- License Number -->
                        <div>
                            <label for="license_number" class="block text-sm font-medium text-gray-700 mb-1">License Number</label>
                            <input type="text" id="license_number" name="license_number"
                                   value="{{ old('license_number') }}"
                                   class="form-input" placeholder="Medical license number" required>
                            @error('license_number')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Specialization -->
                        <div>
                            <label for="specialization" class="block text-sm font-medium text-gray-700 mb-1">Specialization</label>
                            <input type="text" id="specialization" name="specialization"
                                   value="{{ old('specialization') }}"
                                   class="form-input" placeholder="e.g. Cardiology" required>
                            @error('specialization')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Years of Experience -->
                        <div>
                            <label for="years_experience" class="block text-sm font-medium text-gray-700 mb-1">Years of Experience</label>
                            <input type="number" id="years_experience" name="years_experience"
                                   value="{{ old('years_experience') }}"
                                   class="form-input" min="0" max="100" required>
                            @error('years_experience')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Consultation Fee -->
                        <div>
                            <label for="consultation_fee" class="block text-sm font-medium text-gray-700 mb-1">Consultation Fee</label>
                            <input type="number" step="0.01" id="consultation_fee" name="consultation_fee"
                                   value="{{ old('consultation_fee') }}"
                                   class="form-input" placeholder="e.g. 150.00" required>
                            @error('consultation_fee')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Qualifications & Bio -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Qualifications</h3>
                    <div class="space-y-4">
                        <!-- Qualifications -->
                        <div>
                            <label for="qualifications" class="block text-sm font-medium text-gray-700 mb-1">Qualifications</label>
                            <textarea id="qualifications" name="qualifications" rows="4" class="form-input">{{ old('qualifications') }}</textarea>
                            @error('qualifications')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Bio -->
                        <div>
                            <label for="bio" class="block text-sm font-medium text-gray-700 mb-1">Professional Bio</label>
                            <textarea id="bio" name="bio" rows="4" class="form-input">{{ old('bio') }}</textarea>
                            @error('bio')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Hospital & Availability -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Hospital & Availability</h3>
                    <div class="space-y-4">
                        <!-- Hospital Affiliation -->
                        <div>
                            <label for="hospital_affiliation" class="block text-sm font-medium text-gray-700 mb-1">Hospital Affiliation</label>
                            <input type="text" id="hospital_affiliation" name="hospital_affiliation"
                                   value="{{ old('hospital_affiliation') }}"
                                   class="form-input" placeholder="Primary hospital" required>
                            @error('hospital_affiliation')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Availability Status</label>
                            <select id="status" name="status" class="form-input" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="on_leave" {{ old('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                                <option value="not_available" {{ old('status') == 'not_available' ? 'selected' : '' }}>Not Available</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Accepts Emergency Calls -->
                        <div class="flex items-center">
                            <input type="checkbox" id="accepts_emergency_calls" name="accepts_emergency_calls" value="1"
                                   {{ old('accepts_emergency_calls') ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <label for="accepts_emergency_calls" class="ml-2 block text-sm text-gray-700">Accept Emergency Calls</label>
                            @error('accepts_emergency_calls')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Available Hours -->
                        <div>
                            <label for="available_hours" class="block text-sm font-medium text-gray-700 mb-1">Available Hours</label>
                            <textarea id="available_hours" name="available_hours" rows="3" class="form-input" placeholder="Enter your weekly schedule in JSON format">{{ old('available_hours') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Example: {"monday": ["9:00-17:00"], "tuesday": ["8:00-12:00", "13:00-17:00"]}</p>
                            @error('available_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('doctor.dashboard') }}" class="btn-secondary">
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
