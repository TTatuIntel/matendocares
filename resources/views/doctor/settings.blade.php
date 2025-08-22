@extends('doctor.layout')

@section('title', 'Profile Settings')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                        👤 Profile Settings
                    </h1>
                    <p class="text-gray-600 text-lg">Manage your professional profile and account settings</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-3xl flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-xl">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">👨‍⚕️ Professional Information</h3>
                <p class="text-gray-600">Update your professional details and credentials</p>
            </div>

            <div class="p-8">
                <form id="profileForm" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" id="name" name="name" value="{{ auth()->user()->name }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                            <input type="email" id="email" name="email" value="{{ auth()->user()->email }}" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="{{ auth()->user()->phone ?? '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="license_number" class="block text-sm font-medium text-gray-700 mb-2">Medical License Number</label>
                            <input type="text" id="license_number" name="license_number" value="{{ auth()->user()->doctor->license_number ?? '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="specialization" class="block text-sm font-medium text-gray-700 mb-2">Specialization</label>
                            <select id="specialization" name="specialization"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Specialization</option>
                                <option value="general_medicine" {{ (auth()->user()->doctor->specialization ?? '') === 'general_medicine' ? 'selected' : '' }}>General Medicine</option>
                                <option value="cardiology" {{ (auth()->user()->doctor->specialization ?? '') === 'cardiology' ? 'selected' : '' }}>Cardiology</option>
                                <option value="neurology" {{ (auth()->user()->doctor->specialization ?? '') === 'neurology' ? 'selected' : '' }}>Neurology</option>
                                <option value="orthopedics" {{ (auth()->user()->doctor->specialization ?? '') === 'orthopedics' ? 'selected' : '' }}>Orthopedics</option>
                                <option value="pediatrics" {{ (auth()->user()->doctor->specialization ?? '') === 'pediatrics' ? 'selected' : '' }}>Pediatrics</option>
                                <option value="psychiatry" {{ (auth()->user()->doctor->specialization ?? '') === 'psychiatry' ? 'selected' : '' }}>Psychiatry</option>
                                <option value="emergency_medicine" {{ (auth()->user()->doctor->specialization ?? '') === 'emergency_medicine' ? 'selected' : '' }}>Emergency Medicine</option>
                                <option value="internal_medicine" {{ (auth()->user()->doctor->specialization ?? '') === 'internal_medicine' ? 'selected' : '' }}>Internal Medicine</option>
                                <option value="family_medicine" {{ (auth()->user()->doctor->specialization ?? '') === 'family_medicine' ? 'selected' : '' }}>Family Medicine</option>
                                <option value="other" {{ (auth()->user()->doctor->specialization ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="years_experience" class="block text-sm font-medium text-gray-700 mb-2">Years of Experience</label>
                            <input type="number" id="years_experience" name="years_experience" min="0" max="60" value="{{ auth()->user()->doctor->years_experience ?? '' }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div>
                        <label for="qualifications" class="block text-sm font-medium text-gray-700 mb-2">Qualifications & Certifications</label>
                        <textarea id="qualifications" name="qualifications" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="List your medical degrees, certifications, and other qualifications">{{ auth()->user()->doctor->qualifications ?? '' }}</textarea>
                    </div>
                    
                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Professional Bio</label>
                        <textarea id="bio" name="bio" rows="4"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Brief professional biography and areas of expertise">{{ auth()->user()->doctor->bio ?? '' }}</textarea>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Security -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">🔒 Account Security</h3>
                <p class="text-gray-600">Manage your password and security settings</p>
            </div>

            <div class="p-8">
                <form id="passwordForm" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current Password *</label>
                        <input type="password" id="current_password" name="current_password" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="new_password" class="block text-sm font-medium text-gray-700 mb-2">New Password *</label>
                            <input type="password" id="new_password" name="new_password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <div class="mt-2 text-sm text-gray-600">
                                Password must be at least 8 characters long and contain uppercase, lowercase, numbers, and special characters.
                            </div>
                        </div>
                        
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">🔔 Notification Preferences</h3>
                <p class="text-gray-600">Configure how you receive alerts and notifications</p>
            </div>

            <div class="p-8">
                <form id="notificationForm" class="space-y-6">
                    @csrf
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h4 class="font-semibold text-gray-900">Critical Patient Alerts</h4>
                                <p class="text-sm text-gray-600">Receive immediate notifications for critical patient conditions</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="critical_alerts" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h4 class="font-semibold text-gray-900">Daily Summary Reports</h4>
                                <p class="text-sm text-gray-600">Get daily summaries of patient activities and health updates</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="daily_summaries" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h4 class="font-semibold text-gray-900">Appointment Reminders</h4>
                                <p class="text-sm text-gray-600">Receive reminders for upcoming appointments</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="appointment_reminders" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h4 class="font-semibold text-gray-900">Patient Updates</h4>
                                <p class="text-sm text-gray-600">Get notified when patients submit new vital signs or documents</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="patient_updates" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div>
                                <h4 class="font-semibold text-gray-900">System Maintenance</h4>
                                <p class="text-sm text-gray-600">Receive notifications about system updates and maintenance</p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="system_notifications">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="font-semibold text-gray-900 mb-4">Notification Methods</h4>
                        <div class="space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="notification_methods[]" value="email" class="mr-3" checked>
                                <span class="text-gray-700">Email notifications</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notification_methods[]" value="sms" class="mr-3">
                                <span class="text-gray-700">SMS notifications (for critical alerts)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="notification_methods[]" value="push" class="mr-3" checked>
                                <span class="text-gray-700">Browser push notifications</span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Preferences
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Working Hours -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">⏰ Working Hours</h3>
                <p class="text-gray-600">Set your availability for appointments and consultations</p>
            </div>

            <div class="p-8">
                <form id="workingHoursForm" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 gap-4">
                        @php
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                        @endphp
                        
                        @foreach($days as $day)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="working_days[]" value="{{ strtolower($day) }}" 
                                           class="mr-3" {{ in_array(strtolower($day), ['monday', 'tuesday', 'wednesday', 'thursday', 'friday']) ? 'checked' : '' }}>
                                    <span class="font-medium text-gray-900 w-20">{{ $day }}</span>
                                </label>
                            </div>
                            
                            <div class="flex items-center space-x-4">
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600">Start:</label>
                                    <input type="time" name="start_time[{{ strtolower($day) }}]" value="09:00"
                                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <label class="text-sm text-gray-600">End:</label>
                                    <input type="time" name="end_time[{{ strtolower($day) }}]" value="17:00"
                                           class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <div class="border-t border-gray-200 pt-6">
                        <h4 class="font-semibold text-gray-900 mb-4">Break Times</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lunch Break Start</label>
                                <input type="time" name="lunch_start" value="12:00"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lunch Break End</label>
                                <input type="time" name="lunch_end" value="13:00"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save Working Hours
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">📊 Account Statistics</h3>
                <p class="text-gray-600">Overview of your account activity and performance</p>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="stat-card bg-blue-50 border border-blue-200 p-6 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-2xl font-bold text-blue-600">{{ $totalPatients ?? 0 }}</h4>
                                <p class="text-blue-800 font-medium">Total Patients</p>
                            </div>
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m-9 5.197v1a6 6 0 0010.967 0M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-green-50 border border-green-200 p-6 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-2xl font-bold text-green-600">{{ $totalAppointments ?? 0 }}</h4>
                                <p class="text-green-800 font-medium">Appointments This Month</p>
                            </div>
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="stat-card bg-purple-50 border border-purple-200 p-6 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-2xl font-bold text-purple-600">{{ $avgResponseTime ?? '4.2' }}min</h4>
                                <p class="text-purple-800 font-medium">Avg Response Time</p>
                            </div>
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <h5 class="font-semibold text-gray-900 mb-2">Account Created</h5>
                        <p class="text-gray-600">{{ auth()->user()->created_at->format('F j, Y') }}</p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded-xl">
                        <h5 class="font-semibold text-gray-900 mb-2">Last Login</h5>
                        <p class="text-gray-600">{{ auth()->user()->last_activity ? auth()->user()->last_activity->diffForHumans() : 'Recently' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.toggle-switch {
    @apply relative inline-block w-12 h-6;
}

.toggle-switch input {
    @apply opacity-0 w-0 h-0;
}

.toggle-slider {
    @apply absolute cursor-pointer top-0 left-0 right-0 bottom-0 bg-gray-300 rounded-full transition-all duration-300;
}

.toggle-slider:before {
    @apply absolute content-[''] h-5 w-5 left-0.5 bottom-0.5 bg-white rounded-full transition-all duration-300;
}

.toggle-switch input:checked + .toggle-slider {
    @apply bg-blue-500;
}

.toggle-switch input:checked + .toggle-slider:before {
    @apply transform translate-x-6;
}

.stat-card {
    @apply transition-all duration-200 hover:shadow-md;
}
</style>
@endsection

@push('scripts')
<script>
// Profile settings initialization
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    setupValidation();
});

// Setup event listeners
function setupEventListeners() {
    // Profile form submission
    document.getElementById('profileForm').addEventListener('submit', handleProfileUpdate);
    
    // Password form submission
    document.getElementById('passwordForm').addEventListener('submit', handlePasswordUpdate);
    
    // Notification form submission
    document.getElementById('notificationForm').addEventListener('submit', handleNotificationUpdate);
    
    // Working hours form submission
    document.getElementById('workingHoursForm').addEventListener('submit', handleWorkingHoursUpdate);
    
    // Password validation
    document.getElementById('confirm_password').addEventListener('input', validatePasswordMatch);
    document.getElementById('new_password').addEventListener('input', validatePasswordStrength);
}

// Setup form validation
function setupValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

function validateField(field) {
    const value = field.value.trim();
    const isValid = value !== '';
    
    if (!isValid) {
        showFieldError(field, 'This field is required');
    } else {
        clearFieldError(field);
    }
    
    return isValid;
}

function validatePasswordMatch() {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (confirmPassword && newPassword !== confirmPassword) {
        showFieldError(document.getElementById('confirm_password'), 'Passwords do not match');
        return false;
    } else {
        clearFieldError(document.getElementById('confirm_password'));
        return true;
    }
}

function validatePasswordStrength() {
    const password = document.getElementById('new_password').value;
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        numbers: /\d/.test(password),
        special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
    };
    
    const allMet = Object.values(requirements).every(req => req);
    
    if (password && !allMet) {
        let message = 'Password must contain: ';
        const missing = [];
        if (!requirements.length) missing.push('8+ characters');
        if (!requirements.uppercase) missing.push('uppercase letter');
        if (!requirements.lowercase) missing.push('lowercase letter');
        if (!requirements.numbers) missing.push('number');
        if (!requirements.special) missing.push('special character');
        
        showFieldError(document.getElementById('new_password'), message + missing.join(', '));
        return false;
    } else {
        clearFieldError(document.getElementById('new_password'));
        return true;
    }
}

function showFieldError(field, message) {
    clearFieldError(field);
    
    field.classList.add('border-red-500');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'text-red-500 text-sm mt-1 field-error';
    errorDiv.textContent = message;
    field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
    field.classList.remove('border-red-500');
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

// Form submission handlers
async function handleProfileUpdate(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Updating...
    `;
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('/doctor/profile/update', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Profile updated successfully!', 'success');
        } else {
            window.showToast('❌ Failed to update profile: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Profile update error:', error);
        window.showToast('❌ Error updating profile', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

async function handlePasswordUpdate(event) {
    event.preventDefault();
    
    if (!validatePasswordStrength() || !validatePasswordMatch()) {
        return;
    }
    
    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Updating...
    `;
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('/doctor/profile/update-password', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Password updated successfully!', 'success');
            event.target.reset();
        } else {
            window.showToast('❌ Failed to update password: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Password update error:', error);
        window.showToast('❌ Error updating password', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

async function handleNotificationUpdate(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Saving...
    `;
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('/doctor/profile/update-notifications', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Notification preferences saved!', 'success');
        } else {
            window.showToast('❌ Failed to save preferences: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Notification update error:', error);
        window.showToast('❌ Error saving preferences', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

async function handleWorkingHoursUpdate(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Saving...
    `;
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('/doctor/profile/update-working-hours', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Working hours updated successfully!', 'success');
        } else {
            window.showToast('❌ Failed to update working hours: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Working hours update error:', error);
        window.showToast('❌ Error updating working hours', 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}
</script>
@endpush