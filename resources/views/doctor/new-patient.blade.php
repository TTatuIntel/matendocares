@extends('doctor.layout')

@section('title', 'Add New Patient')
@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                        👥 Add New Patient
                    </h1>
                    <p class="text-gray-600 text-lg">Create a new patient profile for monitoring and care</p>
                </div>
                <a href="{{ route('doctor.patients.index') }}" class="btn-secondary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                    </svg>
                    Back to Patients
                </a>
            </div>
        </div>

        <!-- Patient Registration Form -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Patient Information</h3>
                <p class="text-gray-600">Please fill in all required information to create the patient profile</p>
            </div>

            <div class="p-8">
                <form id="addPatientForm" class="space-y-8">
                    @csrf
                    
                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" id="step1">
                        <div class="mb-6">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">📋 Basic Information</h4>
                            <p class="text-gray-600">Personal details and contact information</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="firstName" class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                                <input type="text" id="firstName" name="first_name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="lastName" class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                                <input type="text" id="lastName" name="last_name" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                <input type="tel" id="phone" name="phone"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="dateOfBirth" class="block text-sm font-medium text-gray-700 mb-2">Date of Birth *</label>
                                <input type="date" id="dateOfBirth" name="date_of_birth" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                                <select id="gender" name="gender" required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                    <option value="prefer_not_to_say">Prefer not to say</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea id="address" name="address" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Full address including city, state, and zip code"></textarea>
                        </div>
                    </div>

                    <!-- Step 2: Medical Information -->
                    <div class="form-step hidden" id="step2">
                        <div class="mb-6">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">🏥 Medical Information</h4>
                            <p class="text-gray-600">Medical history and health details</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="medicalRecordNumber" class="block text-sm font-medium text-gray-700 mb-2">Medical Record Number</label>
                                <input type="text" id="medicalRecordNumber" name="medical_record_number"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Auto-generated if left empty">
                            </div>
                            
                            <div>
                                <label for="bloodType" class="block text-sm font-medium text-gray-700 mb-2">Blood Type</label>
                                <select id="bloodType" name="blood_type"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Blood Type</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                    <option value="unknown">Unknown</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="height" class="block text-sm font-medium text-gray-700 mb-2">Height (inches)</label>
                                <input type="number" id="height" name="height" step="0.1" min="0" max="120"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="weight" class="block text-sm font-medium text-gray-700 mb-2">Weight (lbs)</label>
                                <input type="number" id="weight" name="weight" step="0.1" min="0" max="1000"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div>
                                <label for="activityLevel" class="block text-sm font-medium text-gray-700 mb-2">Activity Level</label>
                                <select id="activityLevel" name="activity_level"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="">Select Activity Level</option>
                                    <option value="sedentary">Sedentary</option>
                                    <option value="lightly_active">Lightly Active</option>
                                    <option value="moderately_active">Moderately Active</option>
                                    <option value="very_active">Very Active</option>
                                    <option value="extremely_active">Extremely Active</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Smoking Status</label>
                                <div class="flex items-center space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="smoker" value="1" class="mr-2">
                                        <span>Yes</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="smoker" value="0" class="mr-2">
                                        <span>No</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="chronicConditions" class="block text-sm font-medium text-gray-700 mb-2">Chronic Conditions</label>
                            <textarea id="chronicConditions" name="chronic_conditions" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="List any chronic conditions, separated by commas"></textarea>
                        </div>

                        <div class="mt-6">
                            <label for="allergies" class="block text-sm font-medium text-gray-700 mb-2">Allergies</label>
                            <textarea id="allergies" name="allergies" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="List any known allergies, including medications, foods, and environmental"></textarea>
                        </div>

                        <div class="mt-6">
                            <label for="currentMedications" class="block text-sm font-medium text-gray-700 mb-2">Current Medications</label>
                            <textarea id="currentMedications" name="current_medications" rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="List current medications with dosages"></textarea>
                        </div>
                    </div>

                    <!-- Step 3: Emergency Contacts -->
                    <div class="form-step hidden" id="step3">
                        <div class="mb-6">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">🚨 Emergency Contacts</h4>
                            <p class="text-gray-600">People to contact in case of emergency</p>
                        </div>

                        <div id="emergencyContacts">
                            <!-- Emergency Contact 1 -->
                            <div class="emergency-contact-section bg-gray-50 p-6 rounded-xl mb-4">
                                <h5 class="font-semibold text-gray-900 mb-4">Primary Emergency Contact</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                        <input type="text" name="emergency_contacts[0][name]"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                        <select name="emergency_contacts[0][relationship]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select Relationship</option>
                                            <option value="spouse">Spouse</option>
                                            <option value="parent">Parent</option>
                                            <option value="child">Child</option>
                                            <option value="sibling">Sibling</option>
                                            <option value="friend">Friend</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                        <input type="tel" name="emergency_contacts[0][phone]"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="emergency_contacts[0][email]"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>

                            <!-- Emergency Contact 2 -->
                            <div class="emergency-contact-section bg-gray-50 p-6 rounded-xl mb-4">
                                <h5 class="font-semibold text-gray-900 mb-4">Secondary Emergency Contact</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                        <input type="text" name="emergency_contacts[1][name]"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Relationship</label>
                                        <select name="emergency_contacts[1][relationship]"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                            <option value="">Select Relationship</option>
                                            <option value="spouse">Spouse</option>
                                            <option value="parent">Parent</option>
                                            <option value="child">Child</option>
                                            <option value="sibling">Sibling</option>
                                            <option value="friend">Friend</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                                        <input type="tel" name="emergency_contacts[1][phone]"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                        <input type="email" name="emergency_contacts[1][email]"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Review and Confirmation -->
                    <div class="form-step hidden" id="step4">
                        <div class="mb-6">
                            <h4 class="text-xl font-bold text-gray-900 mb-2">✅ Review and Confirm</h4>
                            <p class="text-gray-600">Please review all information before creating the patient profile</p>
                        </div>

                        <div id="reviewSection" class="space-y-6">
                            <!-- Review content will be populated by JavaScript -->
                        </div>

                        <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h5 class="font-semibold text-blue-900">Account Creation</h5>
                                    <p class="text-blue-800 text-sm">A patient account will be created automatically with a temporary password. The patient will receive login instructions via email.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="flex justify-between pt-8 border-t border-gray-200">
                        <button type="button" id="prevBtn" onclick="previousStep()" class="btn-secondary hidden">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                            </svg>
                            Previous
                        </button>
                        
                        <div class="flex space-x-4">
                            <button type="button" id="nextBtn" onclick="nextStep()" class="btn-primary">
                                Next
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </button>
                            
                            <button type="submit" id="submitBtn" class="btn-primary hidden">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Patient
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="medical-card p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="step-indicator active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Medical Info</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Emergency Contacts</div>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" data-step="4">
                        <div class="step-number">4</div>
                        <div class="step-label">Review</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-step {
    @apply transition-all duration-300;
}

.step-indicator {
    @apply flex flex-col items-center space-y-2 transition-all duration-200;
}

.step-indicator.active .step-number {
    @apply bg-blue-600 text-white;
}

.step-indicator.completed .step-number {
    @apply bg-green-600 text-white;
}

.step-number {
    @apply w-10 h-10 rounded-full bg-gray-300 text-gray-600 flex items-center justify-center font-bold text-sm;
}

.step-label {
    @apply text-sm font-medium text-gray-600;
}

.step-indicator.active .step-label {
    @apply text-blue-600;
}

.step-indicator.completed .step-label {
    @apply text-green-600;
}

.step-line {
    @apply w-16 h-1 bg-gray-300 mx-4;
}

.emergency-contact-section {
    @apply transition-all duration-200 hover:shadow-md;
}
</style>
@endsection

@push('scripts')
<script>
let currentStep = 1;
const totalSteps = 4;

// Initialize form
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    updateStepIndicator();
    calculateAge();
});

// Setup event listeners
function setupEventListeners() {
    // Form submission
    document.getElementById('addPatientForm').addEventListener('submit', handleFormSubmission);
    
    // Age calculation on date change
    document.getElementById('dateOfBirth').addEventListener('change', calculateAge);
    
    // Real-time validation
    setupValidation();
}

// Step navigation
function nextStep() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            hideStep(currentStep);
            currentStep++;
            showStep(currentStep);
            updateStepIndicator();
            updateNavigationButtons();
            
            if (currentStep === 4) {
                populateReviewSection();
            }
        }
    }
}

function previousStep() {
    if (currentStep > 1) {
        hideStep(currentStep);
        currentStep--;
        showStep(currentStep);
        updateStepIndicator();
        updateNavigationButtons();
    }
}

function hideStep(step) {
    document.getElementById(`step${step}`).classList.add('hidden');
}

function showStep(step) {
    document.getElementById(`step${step}`).classList.remove('hidden');
}

function updateStepIndicator() {
    const indicators = document.querySelectorAll('.step-indicator');
    
    indicators.forEach((indicator, index) => {
        const stepNumber = index + 1;
        indicator.classList.remove('active', 'completed');
        
        if (stepNumber === currentStep) {
            indicator.classList.add('active');
        } else if (stepNumber < currentStep) {
            indicator.classList.add('completed');
        }
    });
}

function updateNavigationButtons() {
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    // Show/hide previous button
    if (currentStep > 1) {
        prevBtn.classList.remove('hidden');
    } else {
        prevBtn.classList.add('hidden');
    }
    
    // Show/hide next/submit buttons
    if (currentStep === totalSteps) {
        nextBtn.classList.add('hidden');
        submitBtn.classList.remove('hidden');
    } else {
        nextBtn.classList.remove('hidden');
        submitBtn.classList.add('hidden');
    }
}

// Validation
function validateCurrentStep() {
    const currentStepElement = document.getElementById(`step${currentStep}`);
    const requiredFields = currentStepElement.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            showFieldError(field, 'This field is required');
            isValid = false;
        } else {
            clearFieldError(field);
        }
    });
    
    // Additional validation for specific steps
    if (currentStep === 1) {
        isValid = validateStep1() && isValid;
    } else if (currentStep === 2) {
        isValid = validateStep2() && isValid;
    }
    
    return isValid;
}

function validateStep1() {
    const email = document.getElementById('email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && !emailRegex.test(email)) {
        showFieldError(document.getElementById('email'), 'Please enter a valid email address');
        return false;
    }
    
    const dateOfBirth = document.getElementById('dateOfBirth').value;
    if (dateOfBirth) {
        const birthDate = new Date(dateOfBirth);
        const today = new Date();
        if (birthDate > today) {
            showFieldError(document.getElementById('dateOfBirth'), 'Date of birth cannot be in the future');
            return false;
        }
    }
    
    return true;
}

function validateStep2() {
    const height = document.getElementById('height').value;
    const weight = document.getElementById('weight').value;
    
    if (height && (height < 12 || height > 120)) {
        showFieldError(document.getElementById('height'), 'Please enter a valid height (12-120 inches)');
        return false;
    }
    
    if (weight && (weight < 1 || weight > 1000)) {
        showFieldError(document.getElementById('weight'), 'Please enter a valid weight (1-1000 lbs)');
        return false;
    }
    
    return true;
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

function setupValidation() {
    const fields = document.querySelectorAll('input, select, textarea');
    fields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                showFieldError(this, 'This field is required');
            } else {
                clearFieldError(this);
            }
        });
        
        field.addEventListener('input', function() {
            clearFieldError(this);
        });
    });
}

// Utility functions
function calculateAge() {
    const dateOfBirth = document.getElementById('dateOfBirth').value;
    if (dateOfBirth) {
        const birthDate = new Date(dateOfBirth);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        // You can display the age somewhere if needed
        console.log('Patient age:', age);
    }
}

function populateReviewSection() {
    const reviewSection = document.getElementById('reviewSection');
    const formData = new FormData(document.getElementById('addPatientForm'));
    
    const basicInfo = {
        'First Name': formData.get('first_name'),
        'Last Name': formData.get('last_name'),
        'Email': formData.get('email'),
        'Phone': formData.get('phone'),
        'Date of Birth': formData.get('date_of_birth'),
        'Gender': formData.get('gender'),
        'Address': formData.get('address')
    };
    
    const medicalInfo = {
        'Medical Record Number': formData.get('medical_record_number') || 'Auto-generated',
        'Blood Type': formData.get('blood_type'),
        'Height': formData.get('height') ? formData.get('height') + ' inches' : '',
        'Weight': formData.get('weight') ? formData.get('weight') + ' lbs' : '',
        'Activity Level': formData.get('activity_level'),
        'Smoker': formData.get('smoker') === '1' ? 'Yes' : 'No',
        'Chronic Conditions': formData.get('chronic_conditions'),
        'Allergies': formData.get('allergies'),
        'Current Medications': formData.get('current_medications')
    };
    
    const emergencyContacts = [];
    for (let i = 0; i < 2; i++) {
        const name = formData.get(`emergency_contacts[${i}][name]`);
        if (name) {
            emergencyContacts.push({
                'Name': name,
                'Relationship': formData.get(`emergency_contacts[${i}][relationship]`),
                'Phone': formData.get(`emergency_contacts[${i}][phone]`),
                'Email': formData.get(`emergency_contacts[${i}][email]`)
            });
        }
    }
    
    let reviewHTML = `
        <div class="space-y-6">
            <div class="bg-blue-50 p-6 rounded-xl">
                <h5 class="font-bold text-blue-900 mb-4">📋 Basic Information</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    ${Object.entries(basicInfo).map(([key, value]) => 
                        value ? `<div><span class="font-medium text-gray-700">${key}:</span> <span class="text-gray-900">${value}</span></div>` : ''
                    ).join('')}
                </div>
            </div>
            
            <div class="bg-green-50 p-6 rounded-xl">
                <h5 class="font-bold text-green-900 mb-4">🏥 Medical Information</h5>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    ${Object.entries(medicalInfo).map(([key, value]) => 
                        value ? `<div><span class="font-medium text-gray-700">${key}:</span> <span class="text-gray-900">${value}</span></div>` : ''
                    ).join('')}
                </div>
            </div>
    `;
    
    if (emergencyContacts.length > 0) {
        reviewHTML += `
            <div class="bg-red-50 p-6 rounded-xl">
                <h5 class="font-bold text-red-900 mb-4">🚨 Emergency Contacts</h5>
                ${emergencyContacts.map((contact, index) => `
                    <div class="mb-4 ${index > 0 ? 'border-t border-red-200 pt-4' : ''}">
                        <h6 class="font-semibold text-red-800 mb-2">${index === 0 ? 'Primary' : 'Secondary'} Contact</h6>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            ${Object.entries(contact).map(([key, value]) => 
                                value ? `<div><span class="font-medium text-gray-700">${key}:</span> <span class="text-gray-900">${value}</span></div>` : ''
                            ).join('')}
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }
    
    reviewHTML += '</div>';
    reviewSection.innerHTML = reviewHTML;
}

// Form submission
async function handleFormSubmission(event) {
    event.preventDefault();
    
    if (!validateCurrentStep()) {
        return;
    }
    
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn.innerHTML;
    
    // Show loading state
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Creating Patient...
    `;
    submitBtn.disabled = true;
    
    try {
        const formData = new FormData(event.target);
        
        // Convert FormData to JSON
        const jsonData = {};
        const emergencyContacts = [];
        
        formData.forEach((value, key) => {
            if (key.startsWith('emergency_contacts[')) {
                const match = key.match(/emergency_contacts\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    const index = parseInt(match[1]);
                    const field = match[2];
                    
                    if (!emergencyContacts[index]) {
                        emergencyContacts[index] = {};
                    }
                    emergencyContacts[index][field] = value;
                }
            } else {
                jsonData[key] = value;
            }
        });
        
        jsonData.emergency_contacts = emergencyContacts.filter(contact => contact.name);
        
        const response = await fetch('{{ route("doctor.store-patient") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(jsonData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Patient created successfully!', 'success');
            setTimeout(() => {
                window.location.href = `/doctor/patients/${data.patient.id}/monitor`;
            }, 1000);
        } else {
            window.showToast('❌ Failed to create patient: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Form submission error:', error);
        window.showToast('❌ Error creating patient: ' + error.message, 'error');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}
</script>
@endpush