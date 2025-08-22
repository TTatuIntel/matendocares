@extends('patient.layout')

@section('title', 'Vital Signs')
@section('page-title', 'Health Vitals Management')
@section('page-description', 'Record and monitor your health measurements')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-6">
        <!-- Compact Stats Overview -->
        <div class="grid grid-cols-4 gap-4">
            <div class="medical-card p-4 text-center">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                    </svg>
                </div>
                <div class="text-xl font-bold text-gray-900">{{ $totalRecords ?? 47 }}</div>
                <div class="text-xs text-gray-600">Total Records</div>
            </div>

            <div class="medical-card p-4 text-center">
                <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-xl font-bold text-gray-900">{{ $weeklyRecords ?? 12 }}</div>
                <div class="text-xs text-gray-600">This Week</div>
            </div>

            <div class="medical-card p-4 text-center">
                <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div class="text-xl font-bold text-gray-900">{{ $avgBP ?? '121/79' }}</div>
                <div class="text-xs text-gray-600">Avg BP</div>
            </div>

            <div class="medical-card p-4 text-center">
                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="text-xl font-bold text-gray-900">{{ $avgHR ?? '74' }}</div>
                <div class="text-xs text-gray-600">Avg HR</div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Main Form Section (3/4 width) -->
            <div class="lg:col-span-3">
                <!-- Latest Measurements Display -->
                <div class="medical-card p-6 mb-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Latest Measurements</h3>
                        <div class="text-sm text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ isset($recentVitals) && $recentVitals->first() ? $recentVitals->first()->measured_at->format('M d, Y g:i A') : 'No recent data' }}
                        </div>
                    </div>

                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                        <div class="stat-card bg-gradient-to-br from-red-50 to-red-100 border-red-200 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                                @php
                                    $bpStatus = 'Normal';
                                    $bpColor = 'green';
                                    if (isset($recentVitals) && $recentVitals->first()) {
                                        $bp = $recentVitals->first()->blood_pressure;
                                        if ($bp) {
                                            $parts = explode('/', $bp);
                                            if (count($parts) == 2) {
                                                $systolic = (int)$parts[0];
                                                $diastolic = (int)$parts[1];
                                                if ($systolic >= 140 || $diastolic >= 90) {
                                                    $bpStatus = 'High Risk';
                                                    $bpColor = 'red';
                                                } elseif ($systolic >= 120 || $diastolic >= 80) {
                                                    $bpStatus = 'Borderline';
                                                    $bpColor = 'yellow';
                                                }
                                            }
                                        }
                                    }
                                @endphp
                                <span class="text-xs bg-{{ $bpColor }}-100 text-{{ $bpColor }}-700 px-2 py-1 rounded-full">{{ $bpStatus }}</span>
                            </div>
                            <div class="text-lg font-bold text-red-700">{{ isset($recentVitals) && $recentVitals->first() ? $recentVitals->first()->blood_pressure : '120/80' }}</div>
                            <div class="text-xs text-red-600">Blood Pressure</div>
                        </div>

                        <div class="stat-card bg-gradient-to-br from-purple-50 to-purple-100 border-purple-200 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                </div>
                                @php
                                    $hrStatus = 'Normal';
                                    $hrColor = 'green';
                                    if (isset($recentVitals) && $recentVitals->first() && $recentVitals->first()->heart_rate) {
                                        $hr = (int)$recentVitals->first()->heart_rate;
                                        if ($hr < 40 || $hr > 140) {
                                            $hrStatus = 'Critical';
                                            $hrColor = 'red';
                                        } elseif (($hr >= 40 && $hr <= 50) || ($hr >= 120 && $hr <= 139)) {
                                            $hrStatus = 'High Risk';
                                            $hrColor = 'orange';
                                        } elseif ($hr >= 100 && $hr <= 119) {
                                            $hrStatus = 'Borderline';
                                            $hrColor = 'yellow';
                                        }
                                    }
                                @endphp
                                <span class="text-xs bg-{{ $hrColor }}-100 text-{{ $hrColor }}-700 px-2 py-1 rounded-full">{{ $hrStatus }}</span>
                            </div>
                            <div class="text-lg font-bold text-purple-700">{{ isset($recentVitals) && $recentVitals->first() ? $recentVitals->first()->heart_rate : '72' }}</div>
                            <div class="text-xs text-purple-600">Heart Rate</div>
                        </div>

                        <div class="stat-card bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l3-3 3 3v13M9 19h6M9 19H7a2 2 0 01-2-2V9a2 2 0 012-2h2M15 19h2a2 2 0 002-2V9a2 2 0 00-2-2h-2"></path>
                                    </svg>
                                </div>
                                @php
                                    $tempStatus = 'Normal';
                                    $tempColor = 'green';
                                    if (isset($recentVitals) && $recentVitals->first() && $recentVitals->first()->temperature) {
                                        $temp = (float)$recentVitals->first()->temperature;
                                        $tempC = ($temp - 32) * 5/9;
                                        if ($tempC < 32.0 || $tempC >= 40.0) {
                                            $tempStatus = 'Critical';
                                            $tempColor = 'red';
                                        } elseif (($tempC >= 32.0 && $tempC < 35.0) || ($tempC >= 39.0 && $tempC < 40.0)) {
                                            $tempStatus = 'High Risk';
                                            $tempColor = 'orange';
                                        } elseif ($tempC >= 38.0 && $tempC < 39.0) {
                                            $tempStatus = 'Borderline';
                                            $tempColor = 'yellow';
                                        }
                                    }
                                @endphp
                                <span class="text-xs bg-{{ $tempColor }}-100 text-{{ $tempColor }}-700 px-2 py-1 rounded-full">{{ $tempStatus }}</span>
                            </div>
                            <div class="text-lg font-bold text-orange-700">{{ isset($recentVitals) && $recentVitals->first() ? $recentVitals->first()->temperature : '98.6' }}°F</div>
                            <div class="text-xs text-orange-600">Temperature</div>
                        </div>

                        <div class="stat-card bg-gradient-to-br from-blue-50 to-blue-100 border-blue-200 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16l3-1m-3 1l-3-1"></path>
                                    </svg>
                                </div>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">Stable</span>
                            </div>
                            <div class="text-lg font-bold text-blue-700">{{ isset($recentVitals) && $recentVitals->first() ? $recentVitals->first()->weight : '150' }}</div>
                            <div class="text-xs text-blue-600">Weight</div>
                        </div>

                        <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 border-green-200 p-3">
                            <div class="flex items-center justify-between mb-2">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                </div>
                                @php
                                    $glucoseStatus = 'Normal';
                                    $glucoseColor = 'green';
                                    if (isset($recentVitals) && $recentVitals->first() && $recentVitals->first()->blood_glucose) {
                                        $glucose = (float)$recentVitals->first()->blood_glucose;
                                        if ($glucose < 50 || $glucose > 600) {
                                            $glucoseStatus = 'Critical';
                                            $glucoseColor = 'red';
                                        } elseif (($glucose >= 50 && $glucose <= 70) || ($glucose >= 300 && $glucose <= 599)) {
                                            $glucoseStatus = 'High Risk';
                                            $glucoseColor = 'orange';
                                        } elseif ($glucose >= 250 && $glucose <= 299) {
                                            $glucoseStatus = 'Borderline';
                                            $glucoseColor = 'yellow';
                                        }
                                    }
                                @endphp
                                <span class="text-xs bg-{{ $glucoseColor }}-100 text-{{ $glucoseColor }}-700 px-2 py-1 rounded-full">{{ $glucoseStatus }}</span>
                            </div>
                            <div class="text-lg font-bold text-green-700">{{ isset($recentVitals) && $recentVitals->first() ? $recentVitals->first()->blood_glucose : '95' }}</div>
                            <div class="text-xs text-green-600">Blood Glucose</div>
                        </div>
                    </div>
                </div>

                <!-- Comprehensive Vitals Form -->
                <div class="medical-card" id="vitals-form">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Record Your Vital Signs</h3>
                        <p class="text-sm text-gray-600">Enter your current health measurements</p>
                    </div>

                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-200 bg-gray-50">
                        <nav class="flex overflow-x-auto px-6" aria-label="Tabs">
                            <button type="button" class="vitals-tab active py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap border-b-2 border-transparent hover:border-gray-300" data-tab="primary">
                                Primary Vitals
                            </button>
                            <button type="button" class="vitals-tab py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap border-b-2 border-transparent hover:border-gray-300" data-tab="physical">
                                Physical Measurements
                            </button>
                            <button type="button" class="vitals-tab py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap border-b-2 border-transparent hover:border-gray-300" data-tab="wellness">
                                Wellness Assessment
                            </button>
                            <button type="button" class="vitals-tab py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap border-b-2 border-transparent hover:border-gray-300" data-tab="symptoms">
                                Current Symptoms
                            </button>
                            <button type="button" class="vitals-tab py-3 px-4 text-sm font-medium text-gray-500 hover:text-gray-700 whitespace-nowrap border-b-2 border-transparent hover:border-gray-300" data-tab="notes">
                                Additional Notes
                            </button>
                        </nav>
                    </div>

                    <form id="vitalsForm" class="p-6" action="{{ route('patient.vitals.store') }}" method="POST">
                        @csrf

                        <!-- Primary Vitals Tab -->
                        <div class="tab-content active" id="primary-tab">
                            <div class="bg-gradient-to-br from-red-50 to-pink-50 p-6 rounded-2xl border-l-4 border-red-500">
                                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                    Primary Vitals
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Blood Pressure -->
                                    <div class="form-group">
                                        <label class="form-label">Blood Pressure</label>
                                        <div class="relative">
                                            <input type="text" name="blood_pressure" placeholder="120/80"
                                                   class="form-input pr-12" pattern="[0-9]{2,3}/[0-9]{2,3}"
                                                   data-validation="blood_pressure" value="{{ old('blood_pressure') }}">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500">mmHg</span>
                                            </div>
                                        </div>
                                        <div class="validation-message hidden"></div>
                                        <div class="text-xs text-gray-500 mt-1">Normal: < 120/80, High Risk: ≥ 140/90</div>
                                        @error('blood_pressure')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Heart Rate -->
                                    <div class="form-group">
                                        <label class="form-label">Heart Rate</label>
                                        <div class="relative">
                                            <input type="number" name="heart_rate" placeholder="72" min="30" max="300"
                                                   class="form-input pr-12" data-validation="heart_rate" value="{{ old('heart_rate') }}">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500">bpm</span>
                                            </div>
                                        </div>
                                        <div class="validation-message hidden"></div>
                                        <div class="text-xs text-gray-500 mt-1">Normal: 60-99, Critical: < 40 or > 140</div>
                                        @error('heart_rate')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Blood Glucose -->
                                    <div class="form-group">
                                        <label class="form-label">Blood Glucose <span class="text-gray-400">(Optional)</span></label>
                                        <div class="space-y-3">
                                            <!-- Glucose Type Selection -->
                                            <div class="flex items-center space-x-6 mb-2">
                                                <label class="flex items-center">
                                                    <input type="radio" name="glucose_type" value="fasting" class="form-radio text-red-500" checked onchange="updateGlucoseType()">
                                                    <span class="ml-2 text-sm text-gray-700">Fasting</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="radio" name="glucose_type" value="random" class="form-radio text-red-500" onchange="updateGlucoseType()">
                                                    <span class="ml-2 text-sm text-gray-700">Random/Post-meal</span>
                                                </label>
                                            </div>
                                            
                                            <!-- Glucose Input -->
                                            <div class="relative">
                                                <input type="number" name="blood_glucose" placeholder="100" id="glucose-input"
                                                       min="15" max="600" step="0.1" class="form-input pr-20" value="{{ old('blood_glucose') }}"
                                                       oninput="updateGlucoseConversion()" data-validation="glucose">
                                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                    <span class="text-xs text-gray-500" id="glucose-unit">mg/dL</span>
                                                </div>
                                            </div>
                                            
                                            <!-- Unit Selection -->
                                            <div class="flex items-center space-x-4">
                                                <label class="flex items-center">
                                                    <input type="radio" name="glucose_unit" value="mg/dl" class="form-radio text-red-500" checked onchange="switchGlucoseUnit()">
                                                    <span class="ml-2 text-sm text-gray-700">mg/dL</span>
                                                </label>
                                                <label class="flex items-center">
                                                    <input type="radio" name="glucose_unit" value="mmol/l" class="form-radio text-red-500" onchange="switchGlucoseUnit()">
                                                    <span class="ml-2 text-sm text-gray-700">mmol/L</span>
                                                </label>
                                            </div>
                                            
                                            <!-- Conversion Display -->
                                            <div class="text-xs text-gray-500" id="glucose-conversion" style="display: none;">
                                                <span id="converted-value"></span>
                                            </div>
                                            
                                            <!-- Dynamic Range Information -->
                                            <div class="text-xs text-gray-500" id="glucose-ranges">
                                                <div id="fasting-ranges">Normal (fasting): 72-140 mg/dL, Critical: <50 or >600</div>
                                                <div id="random-ranges" style="display: none;">Normal (post-meal): 72-180 mg/dL, Critical: <50 or >600</div>
                                            </div>
                                            
                                            <div class="validation-message hidden"></div>
                                        </div>
                                        <input type="hidden" name="glucose_unit_selected" value="mg/dl" id="glucose-unit-hidden">
                                        <input type="hidden" name="glucose_type_selected" value="fasting" id="glucose-type-hidden">
                                        @error('blood_glucose')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Physical Measurements Tab -->
                        <div class="tab-content hidden" id="physical-tab">
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border-l-4 border-blue-500">
                                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                    Physical Measurements
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Temperature -->
                                    <div class="form-group">
                                        <label class="form-label">Temperature</label>
                                        <div class="relative">
                                            <input type="number" name="temperature" step="0.1" placeholder="98.6"
                                                   min="90" max="110" class="form-input pr-12" data-validation="temperature" value="{{ old('temperature') }}">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500">°F</span>
                                            </div>
                                        </div>
                                        <div class="validation-message hidden"></div>
                                        <div class="text-xs text-gray-500 mt-1">Normal: 97.0-99.5°F (36.1-37.9°C)</div>
                                        @error('temperature')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Weight -->
                                    <div class="form-group">
                                        <label class="form-label">Weight</label>
                                        <div class="relative">
                                            <input type="number" name="weight" step="0.1" placeholder="150"
                                                   min="50" max="1000" class="form-input pr-12" data-validation="weight" value="{{ old('weight') }}">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500">lbs</span>
                                            </div>
                                        </div>
                                        <div class="validation-message hidden"></div>
                                        <div class="text-xs text-gray-500 mt-1">Monitor for stability within 2%</div>
                                        @error('weight')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Oxygen Saturation -->
                                    <div class="form-group">
                                        <label class="form-label">Oxygen Saturation</label>
                                        <div class="relative">
                                            <input type="number" name="oxygen_saturation" placeholder="98"
                                                   min="70" max="100" class="form-input pr-12" data-validation="oxygen" value="{{ old('oxygen_saturation') }}">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500">%</span>
                                            </div>
                                        </div>
                                        <div class="validation-message hidden"></div>
                                        <div class="text-xs text-gray-500 mt-1">Normal: 94-100%, Critical: < 85%</div>
                                        @error('oxygen_saturation')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Second row for Steps and Sleep -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                    <!-- Steps Today -->
                                    <div class="form-group">
                                        <label class="form-label">Steps Today <span class="text-gray-400">(Optional)</span></label>
                                        <input type="number" name="steps" placeholder="8000" min="0" class="form-input" value="{{ old('steps') }}">
                                        @error('steps')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Sleep Hours -->
                                    <div class="form-group">
                                        <label class="form-label">Sleep Hours <span class="text-gray-400">(Optional)</span></label>
                                        <div class="relative">
                                            <input type="number" name="sleep_hours" step="0.5" placeholder="8"
                                                   min="0" max="24" class="form-input pr-12" value="{{ old('sleep_hours') }}">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                                <span class="text-xs text-gray-500">hrs</span>
                                            </div>
                                        </div>
                                        @error('sleep_hours')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Wellness Assessment Tab -->
                        <div class="tab-content hidden" id="wellness-tab">
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-2xl border-l-4 border-purple-500">
                                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-3 h-3 bg-purple-500 rounded-full mr-3"></div>
                                    Wellness Assessment
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <!-- Energy Level -->
                                    <div class="wellness-slider">
                                        <label class="form-label">Energy Level</label>
                                        <div class="space-y-3">
                                            <input type="range" name="energy_level" min="1" max="10" value="{{ old('energy_level', 5) }}"
                                                   class="wellness-range" data-target="energy"
                                                   oninput="updateWellnessSlider(this, 'energy')">
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-500">Low</span>
                                                <div class="text-center">
                                                    <div id="energy-emoji" class="text-xl">😐</div>
                                                    <div id="energy-value" class="font-bold text-purple-600">5</div>
                                                </div>
                                                <span class="text-gray-500">High</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pain Level -->
                                    <div class="wellness-slider">
                                        <label class="form-label">Pain Level</label>
                                        <div class="space-y-3">
                                            <input type="range" name="pain_level" min="0" max="10" value="{{ old('pain_level', 0) }}"
                                                   class="wellness-range pain-range" data-target="pain"
                                                   oninput="updateWellnessSlider(this, 'pain')">
                                            <div class="flex justify-between items-center text-xs">
                                                <span class="text-gray-500">None</span>
                                                <div class="text-center">
                                                    <div id="pain-emoji" class="text-xl">😊</div>
                                                    <div id="pain-value" class="font-bold text-purple-600">0</div>
                                                </div>
                                                <span class="text-gray-500">Severe</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Overall Mood -->
                                    <div class="form-group">
                                        <label class="form-label">Overall Mood</label>
                                        <select name="mood" class="form-input">
                                            <option value="">Select mood</option>
                                            <option value="excellent" {{ old('mood') == 'excellent' ? 'selected' : '' }}>😊 Excellent</option>
                                            <option value="good" {{ old('mood') == 'good' ? 'selected' : '' }}>🙂 Good</option>
                                            <option value="fair" {{ old('mood') == 'fair' ? 'selected' : '' }}>😐 Fair</option>
                                            <option value="poor" {{ old('mood') == 'poor' ? 'selected' : '' }}>😞 Poor</option>
                                            <option value="very_poor" {{ old('mood') == 'very_poor' ? 'selected' : '' }}>😷 Very Poor</option>
                                        </select>
                                        @error('mood')
                                            <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Current Symptoms Tab -->
                        <div class="tab-content hidden" id="symptoms-tab">
                            <div class="bg-gradient-to-br from-orange-50 to-red-50 p-6 rounded-2xl border-l-4 border-orange-500">
                                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-3 h-3 bg-orange-500 rounded-full mr-3"></div>
                                    Current Symptoms
                                </h4>
                                <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                                    @php
                                        $symptoms = [
                                            'headache' => ['🤕', 'Headache'],
                                            'fever' => ['🤒', 'Fever'],
                                            'fatigue' => ['😴', 'Fatigue'],
                                            'nausea' => ['🤢', 'Nausea'],
                                            'dizziness' => ['😵', 'Dizziness'],
                                            'chest_pain' => ['💔', 'Chest Pain'],
                                            'shortness_of_breath' => ['😤', 'Short Breath'],
                                            'muscle_pain' => ['💪', 'Muscle Pain'],
                                            'cough' => ['😷', 'Cough'],
                                            'sore_throat' => ['🗣️', 'Sore Throat'],
                                            'runny_nose' => ['👃', 'Runny Nose'],
                                            'stomach_pain' => ['🤰', 'Stomach Pain'],
                                        ];
                                        $oldSymptoms = old('symptoms', []);
                                    @endphp

                                    @foreach($symptoms as $value => $symptom)
                                    <label class="symptom-checkbox flex flex-col items-center p-3 border-2 border-gray-200 rounded-xl hover:border-orange-300 cursor-pointer transition-all duration-200">
                                        <input type="checkbox" name="symptoms[]" value="{{ $value }}" class="sr-only" {{ in_array($value, $oldSymptoms) ? 'checked' : '' }}>
                                        <span class="text-xl mb-1">{{ $symptom[0] }}</span>
                                        <span class="text-xs font-medium text-gray-700 text-center">{{ $symptom[1] }}</span>
                                        <div class="checkmark opacity-0 transition-opacity duration-200 mt-1">
                                            <svg class="w-4 h-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    </label>
                                    @endforeach
                                </div>
                                @error('symptoms')
                                    <div class="text-xs text-red-600 mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Notes Tab -->
                        <div class="tab-content hidden" id="notes-tab">
                            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 p-6 rounded-2xl border-l-4 border-yellow-500">
                                <h4 class="text-md font-semibold text-gray-900 mb-4 flex items-center">
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                    Additional Notes
                                </h4>
                                <div class="space-y-4">
                                    <textarea name="notes" rows="5"
                                              placeholder="Any observations, concerns, medications taken today, or other notes..."
                                              class="form-input resize-none">{{ old('notes') }}</textarea>

                                    <!-- Automatic Notes Addition -->
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                        <h5 class="text-sm font-semibold text-yellow-800 mb-2">📝 Auto-Generated Risk Assessment</h5>
                                        <div id="auto-notes" class="text-sm text-yellow-700">
                                            <p class="italic">Risk assessment will be automatically generated based on your vitals and symptoms when you submit.</p>
                                        </div>
                                    </div>
                                </div>
                                @error('notes')
                                    <div class="text-xs text-red-600 mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Submit Section -->
                        <div class="flex flex-col sm:flex-row sm:justify-end space-y-3 sm:space-y-0 sm:space-x-4 pt-6 mt-6 border-t border-gray-200">
                            <button type="button" onclick="fillSampleData()" class="btn-secondary px-6 py-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Sample Data
                            </button>
                            <button type="button" onclick="clearForm()" class="btn-secondary px-6 py-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Clear
                            </button>
                            <button type="submit" class="btn-primary px-8 py-2" id="submitBtn">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Submit Update
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Recent Vitals History -->
                @if(isset($recentVitals) && $recentVitals->count() > 0)
                <div class="medical-card p-6 mt-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mr-3"></div>
                            Recent Vital Signs
                        </h3>
                        <div class="flex items-center space-x-4">
                            <div class="text-sm text-gray-500">Showing {{ $recentVitals->take(10)->count() }} of {{ $recentVitals->count() }} entries</div>
                            @if($recentVitals->count() > 10)
                                <a href="{{ route('patient.vitals.history') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center">
                                    View All History
                                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b-2 border-gray-200">
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-sm">Date & Time</th>
                                    <th class="text-left py-3 px-4 font-semibold text-red-600 text-sm">Blood Pressure</th>
                                    <th class="text-left py-3 px-4 font-semibold text-purple-600 text-sm">Heart Rate</th>
                                    <th class="text-left py-3 px-4 font-semibold text-orange-600 text-sm">Temperature</th>
                                    <th class="text-left py-3 px-4 font-semibold text-blue-600 text-sm">Weight</th>
                                    <th class="text-left py-3 px-4 font-semibold text-gray-700 text-sm">Risk Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentVitals->take(10) as $vital)
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors duration-150 cursor-pointer"
                                    onclick="showVitalDetails({{ json_encode($vital) }})">
                                    <td class="py-3 px-4 text-sm">
                                        <div class="font-medium text-gray-900">{{ $vital->measured_at->format('M d') }}</div>
                                        <div class="text-xs text-gray-500">{{ $vital->measured_at->format('g:i A') }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($vital->blood_pressure)
                                            <div class="font-medium text-red-700 text-sm">{{ $vital->blood_pressure }}</div>
                                            <div class="text-xs text-red-500">mmHg</div>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($vital->heart_rate)
                                            <div class="font-medium text-purple-700 text-sm">{{ $vital->heart_rate }}</div>
                                            <div class="text-xs text-purple-500">bpm</div>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($vital->temperature)
                                            <div class="font-medium text-orange-700 text-sm">{{ $vital->temperature }}</div>
                                            <div class="text-xs text-orange-500">°F</div>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @if($vital->weight)
                                            <div class="font-medium text-blue-700 text-sm">{{ $vital->weight }}</div>
                                            <div class="text-xs text-blue-500">lbs</div>
                                        @else
                                            <span class="text-gray-400 text-sm">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        @php
                                            $riskLevel = 'normal';
                                            $riskClass = 'bg-green-100 text-green-700';
                                            
                                            // Check for critical values using PDF guidelines
                                            if ($vital->temperature) {
                                                $tempC = ($vital->temperature - 32) * 5/9;
                                                if ($tempC < 32.0 || $tempC >= 40.0) {
                                                    $riskLevel = 'critical';
                                                    $riskClass = 'bg-red-100 text-red-700';
                                                }
                                            }
                                            
                                            if ($vital->heart_rate && ($vital->heart_rate < 40 || $vital->heart_rate > 140)) {
                                                $riskLevel = 'critical';
                                                $riskClass = 'bg-red-100 text-red-700';
                                            }
                                            
                                            if ($vital->blood_glucose && ($vital->blood_glucose < 50 || $vital->blood_glucose > 600)) {
                                                $riskLevel = 'critical';
                                                $riskClass = 'bg-red-100 text-red-700';
                                            }
                                            
                                            if ($vital->oxygen_saturation && $vital->oxygen_saturation < 85) {
                                                $riskLevel = 'critical';
                                                $riskClass = 'bg-red-100 text-red-700';
                                            }
                                            
                                            // Check for high risk values
                                            if ($riskLevel === 'normal' && $vital->blood_pressure) {
                                                $parts = explode('/', $vital->blood_pressure);
                                                if (count($parts) == 2) {
                                                    $systolic = (int)$parts[0];
                                                    $diastolic = (int)$parts[1];
                                                    if ($systolic >= 140 || $diastolic >= 90) {
                                                        $riskLevel = 'high risk';
                                                        $riskClass = 'bg-orange-100 text-orange-700';
                                                    }
                                                }
                                            }
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $riskClass }}">
                                            {{ ucfirst($riskLevel) }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>

            <!-- Compact Sidebar (1/4 width) -->
            <div class="space-y-4">
                <!-- Today's Summary -->
                <div class="medical-card p-4">
                    <h3 class="text-md font-bold text-gray-900 mb-3 flex items-center">
                        <div class="w-3 h-3 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full mr-3"></div>
                        Today's Summary
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Records Today</span>
                            <span class="text-lg font-bold text-blue-600">{{ $todayRecords ?? 2 }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Last Record</span>
                            <span class="text-sm font-bold text-green-600">{{ isset($recentVitals) && $recentVitals->first() ? $recentVitals->first()->measured_at->format('g:i A') : '8:00 AM' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Risk Status</span>
                            @php
                                $overallRisk = 'normal';
                                $overallRiskClass = 'bg-green-100 text-green-700';
                                
                                if (isset($recentVitals) && $recentVitals->first()) {
                                    $latest = $recentVitals->first();
                                    
                                    // Check for any critical values
                                    if (($latest->temperature && (($latest->temperature - 32) * 5/9 < 32.0 || ($latest->temperature - 32) * 5/9 >= 40.0)) ||
                                        ($latest->heart_rate && ($latest->heart_rate < 40 || $latest->heart_rate > 140)) ||
                                        ($latest->blood_glucose && ($latest->blood_glucose < 50 || $latest->blood_glucose > 600)) ||
                                        ($latest->oxygen_saturation && $latest->oxygen_saturation < 85)) {
                                        $overallRisk = 'critical';
                                        $overallRiskClass = 'bg-red-100 text-red-700';
                                    }
                                    // Check for high risk
                                    elseif ($latest->blood_pressure) {
                                        $parts = explode('/', $latest->blood_pressure);
                                        if (count($parts) == 2) {
                                            $systolic = (int)$parts[0];
                                            $diastolic = (int)$parts[1];
                                            if ($systolic >= 140 || $diastolic >= 90) {
                                                $overallRisk = 'monitor';
                                                $overallRiskClass = 'bg-yellow-100 text-yellow-700';
                                            }
                                        }
                                    }
                                }
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $overallRiskClass }}">
                                {{ ucfirst($overallRisk) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Updated Risk Stratification Ranges based on PDF -->
                <div class="medical-card p-4">
                    <h3 class="text-md font-bold text-gray-900 mb-3 flex items-center">
                        <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full mr-3"></div>
                        📊 Risk Stratification Ranges
                    </h3>
                    <div class="space-y-3">
                        <!-- Blood Pressure -->
                        <div class="border-b border-gray-200 pb-2">
                            <div class="text-xs font-semibold text-gray-700 mb-1">Blood Pressure (mmHg)</div>
                            <div class="text-xs text-green-600">Normal: < 120/80</div>
                            <div class="text-xs text-yellow-600">Borderline: 120-139/80-89</div>
                            <div class="text-xs text-red-600">High Risk: ≥ 140/90</div>
                        </div>
                        
                        <!-- Heart Rate - Updated based on PDF (removed low risk) -->
                        <div class="border-b border-gray-200 pb-2">
                            <div class="text-xs font-semibold text-gray-700 mb-1">Heart Rate (bpm)</div>
                            <div class="text-xs text-green-600">Normal: 60-99</div>
                            <div class="text-xs text-yellow-600">Borderline: 100-119</div>
                            <div class="text-xs text-orange-600">High Risk: 40-50 or 120-139</div>
                            <div class="text-xs text-red-600">Critical: < 40 or > 140</div>
                        </div>
                        
                        <!-- Blood Glucose - Updated with fasting vs random -->
                        <div class="border-b border-gray-200 pb-2">
                            <div class="text-xs font-semibold text-gray-700 mb-1">Blood Glucose</div>
                            <div class="text-xs text-green-600 mb-1"><strong>mg/dL:</strong></div>
                            <div class="text-xs text-green-600">Normal (fasting): 72-140</div>
                            <div class="text-xs text-green-600">Normal (post-meal): 72-180</div>
                            <div class="text-xs text-yellow-600">Borderline: 250-299</div>
                            <div class="text-xs text-orange-600">High Risk: 50-70 or 300-599</div>
                            <div class="text-xs text-red-600">Critical: < 50 or > 600</div>
                            
                            <div class="text-xs text-green-600 mb-1 mt-2"><strong>mmol/L:</strong></div>
                            <div class="text-xs text-green-600">Normal (fasting): 4.0-7.8</div>
                            <div class="text-xs text-green-600">Normal (post-meal): 4.0-10.0</div>
                            <div class="text-xs text-yellow-600">Borderline: 14.0-16.6</div>
                            <div class="text-xs text-orange-600">High Risk: 2.8-3.9 or 16.7-33.3</div>
                            <div class="text-xs text-red-600">Critical: < 2.8 or > 33.3</div>
                        </div>
                        
                        <!-- Temperature -->
                        <div class="border-b border-gray-200 pb-2">
                            <div class="text-xs font-semibold text-gray-700 mb-1">Temperature (°C)</div>
                            <div class="text-xs text-green-600">Normal: 36.1-37.9</div>
                            <div class="text-xs text-yellow-600">Borderline: 38.0-38.9</div>
                            <div class="text-xs text-orange-600">High Risk: 32.0-35.0 or 39.0-39.9</div>
                            <div class="text-xs text-red-600">Critical: < 32.0 or ≥ 40.0</div>
                        </div>
                        
                        <!-- Oxygen Saturation -->
                        <div>
                            <div class="text-xs font-semibold text-gray-700 mb-1">O2 Saturation (%)</div>
                            <div class="text-xs text-green-600">Normal: 94-100</div>
                            <div class="text-xs text-yellow-600">Borderline: 90-93</div>
                            <div class="text-xs text-orange-600">High Risk: 85-89</div>
                            <div class="text-xs text-red-600">Critical: < 85</div>
                        </div>
                    </div>
                </div>

                <!-- Recording Tips -->
                <div class="medical-card p-4">
                    <h3 class="text-md font-bold text-gray-900 mb-3 flex items-center">
                        <div class="w-3 h-3 bg-gradient-to-r from-green-500 to-blue-500 rounded-full mr-3"></div>
                        💡 Recording Tips
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-white text-xs font-bold">✓</span>
                            </div>
                            <p class="text-sm text-gray-700">Take measurements when relaxed</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-white text-xs font-bold">⏰</span>
                            </div>
                            <p class="text-sm text-gray-700">Record at same time daily</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-white text-xs font-bold">📝</span>
                            </div>
                            <p class="text-sm text-gray-700">Include symptoms and notes</p>
                        </div>
                        <div class="flex items-start space-x-3">
                            <div class="w-6 h-6 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                <span class="text-white text-xs font-bold">🚨</span>
                            </div>
                            <p class="text-sm text-gray-700">Seek immediate care for critical values</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Vital Details Modal -->
<div id="vitalDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Vital Signs Details</h3>
            <button onclick="closeVitalDetails()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="p-6 space-y-4" id="vitalDetailsContent">
            <!-- Content will be inserted here by JavaScript -->
        </div>

        <div class="p-4 border-t border-gray-200 flex justify-end">
            <button onclick="closeVitalDetails()" class="btn-secondary px-6">Close</button>
        </div>
    </div>
</div>

<style>
.form-label {
    @apply block text-sm font-semibold text-gray-700 mb-2;
}

.form-group {
    @apply space-y-1;
}

.validation-message {
    @apply text-xs text-red-600 mt-1 font-medium;
}

.validation-message.success {
    @apply text-green-600;
}

.validation-message.warning {
    @apply text-yellow-600;
}

.validation-message.critical {
    @apply text-red-600 font-bold;
}

.wellness-range {
    @apply w-full h-2 bg-gradient-to-r from-red-200 via-yellow-200 to-green-200 rounded-lg appearance-none cursor-pointer;
}

.wellness-range::-webkit-slider-thumb {
    @apply appearance-none h-4 w-4 bg-white rounded-full shadow-lg cursor-pointer border-2 border-purple-500;
}

.pain-range {
    @apply bg-gradient-to-r from-green-200 via-yellow-200 to-red-200;
}

.symptom-checkbox input:checked + span + span + .checkmark {
    @apply opacity-100;
}

.symptom-checkbox:has(input:checked) {
    @apply border-orange-500 bg-orange-50;
}

.stat-card {
    @apply rounded-2xl border transition-all duration-300;
}

.stat-card:hover {
    @apply transform -translate-y-1 shadow-lg;
}

/* Tab Styles */
.vitals-tab {
    @apply transition-colors duration-200;
}

.vitals-tab.active {
    @apply text-blue-600 border-blue-500;
}

.tab-content {
    @apply transition-all duration-300;
}

.tab-content.hidden {
    @apply opacity-0 transform translate-y-4;
}

.tab-content.active {
    @apply opacity-100 transform translate-y-0;
}

/* Enhanced border colors for vitals */
.border-l-4 {
    border-left-width: 4px;
}

/* Table hover effects */
tbody tr:hover {
    @apply bg-gradient-to-r from-gray-50 to-blue-50;
}

/* Radio button styles */
.form-radio {
    @apply w-4 h-4 border-2 border-gray-300 rounded-full focus:ring-2 focus:ring-red-500;
}

.form-radio:checked {
    @apply bg-red-500 border-red-500;
}

/* Modal animations */
#vitalDetailsModal {
    transition: opacity 0.3s ease;
    opacity: 0;
    pointer-events: none;
}

#vitalDetailsModal:not(.hidden) {
    opacity: 1;
    pointer-events: auto;
}

/* Critical value highlights */
.critical-value {
    @apply bg-red-100 border-red-500 text-red-900;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}
</style>

@endsection

@push('scripts')
<script>
// Updated validation rules based on PDF risk stratification - PRODUCTION READY
const validationRules = {
    blood_pressure: {
        pattern: /^\d{2,3}\/\d{2,3}$/,
        message: 'Format: 120/80',
        validate: (value) => {
            if (!value) return { valid: true, message: '' };
            const match = value.match(/^(\d{2,3})\/(\d{2,3})$/);
            if (!match) return { valid: false, message: 'Invalid format. Use 120/80', level: 'error' };

            const systolic = parseInt(match[1]);
            const diastolic = parseInt(match[2]);

            if (systolic < 40 || systolic > 250) return { valid: false, message: 'Systolic should be 40-250', level: 'error' };
            if (diastolic < 20 || diastolic > 150) return { valid: false, message: 'Diastolic should be 20-150', level: 'error' };
            if (systolic <= diastolic) return { valid: false, message: 'Systolic should be higher than diastolic', level: 'error' };

            // Risk stratification based on PDF
            if (systolic >= 140 || diastolic >= 90) {
                return { valid: true, message: '🚨 HIGH RISK - Hypertension detected', level: 'critical' };
            } else if (systolic >= 120 || diastolic >= 80) {
                return { valid: true, message: '⚠️ BORDERLINE - Elevated blood pressure', level: 'warning' };
            } else {
                return { valid: true, message: '✅ NORMAL - Healthy blood pressure', level: 'success' };
            }
        }
    },
    heart_rate: {
        min: 30,
        max: 300,
        validate: (value) => {
            if (!value) return { valid: true, message: '' };
            const hr = parseInt(value);
            if (hr < 30) return { valid: false, message: 'Too low (minimum 30 bpm)', level: 'error' };
            if (hr > 300) return { valid: false, message: 'Too high (maximum 300 bpm)', level: 'error' };
            
            // Risk stratification based on PDF (removed low risk range as requested)
            if (hr < 40 || hr > 140) {
                return { valid: true, message: '🚨 CRITICAL - Seek immediate medical attention', level: 'critical' };
            } else if ((hr >= 40 && hr <= 50) || (hr >= 120 && hr <= 139)) {
                return { valid: true, message: '⚠️ HIGH RISK - Monitor cardiac function closely', level: 'warning' };
            } else if (hr >= 100 && hr <= 119) {
                return { valid: true, message: '⚠️ BORDERLINE - Slightly elevated', level: 'warning' };
            } else if (hr >= 60 && hr <= 99) {
                return { valid: true, message: '✅ NORMAL - Healthy heart rate', level: 'success' };
            } else {
                return { valid: true, message: '⚠️ BORDERLINE - Monitor closely', level: 'warning' };
            }
        }
    },
    temperature: {
        validate: (value) => {
            if (!value) return { valid: true, message: '' };
            const temp = parseFloat(value);
            if (temp < 90) return { valid: false, message: 'Too low (minimum 90°F)', level: 'error' };
            if (temp > 115) return { valid: false, message: 'Too high (maximum 115°F)', level: 'error' };
            
            // Convert to Celsius for risk stratification
            const tempC = (temp - 32) * 5/9;
            
            // Risk stratification based on PDF
            if (tempC < 32.0 || tempC >= 40.0) {
                return { valid: true, message: '🚨 CRITICAL - Life-threatening temperature', level: 'critical' };
            } else if ((tempC >= 32.0 && tempC < 35.0) || (tempC >= 39.0 && tempC < 40.0)) {
                return { valid: true, message: '⚠️ HIGH RISK - Dangerous temperature', level: 'warning' };
            } else if (tempC >= 38.0 && tempC < 39.0) {
                return { valid: true, message: '⚠️ BORDERLINE - Mild fever', level: 'warning' };
            } else if (tempC >= 36.1 && tempC <= 37.9) {
                return { valid: true, message: '✅ NORMAL - Healthy temperature', level: 'success' };
            } else {
                return { valid: true, message: '⚠️ BORDERLINE - Slightly abnormal', level: 'warning' };
            }
        }
    },
    weight: {
        validate: (value) => {
            if (!value) return { valid: true, message: '' };
            const weight = parseFloat(value);
            if (weight < 50) return { valid: false, message: 'Too low (minimum 50 lbs)', level: 'error' };
            if (weight > 1000) return { valid: false, message: 'Too high (maximum 1000 lbs)', level: 'error' };
            return { valid: true, message: '✅ Valid weight - Monitor for stability within 2%', level: 'success' };
        }
    },
    oxygen: {
        validate: (value) => {
            if (!value) return { valid: true, message: '' };
            const o2 = parseInt(value);
            if (o2 < 70) return { valid: false, message: 'Invalid (minimum 70%)', level: 'error' };
            if (o2 > 100) return { valid: false, message: 'Invalid (maximum 100%)', level: 'error' };
            
            // Risk stratification based on PDF
            if (o2 < 85) {
                return { valid: true, message: '🚨 CRITICAL - Severe hypoxemia', level: 'critical' };
            } else if (o2 >= 85 && o2 <= 89) {
                return { valid: true, message: '⚠️ HIGH RISK - Low oxygen saturation', level: 'warning' };
            } else if (o2 >= 90 && o2 <= 93) {
                return { valid: true, message: '⚠️ BORDERLINE - Monitor oxygen levels', level: 'warning' };
            } else {
                return { valid: true, message: '✅ NORMAL - Healthy oxygen saturation', level: 'success' };
            }
        }
    },
    glucose: {
        validate: (value) => {
            if (!value) return { valid: true, message: '' };
            const glucose = parseFloat(value);
            const unit = document.getElementById('glucose-unit').textContent;
            const glucoseType = document.querySelector('input[name="glucose_type"]:checked')?.value || 'fasting';
            
            let normalMin, normalMax, borderlineMin, borderlineMax, highRiskMin, highRiskMax, criticalMin, criticalMax;
            
            if (unit === 'mg/dL') {
                // Based on PDF values for mg/dL
                criticalMin = 50; 
                criticalMax = 600;
                highRiskMin = 50; 
                highRiskMax = 599;
                borderlineMin = 250; 
                borderlineMax = 299;
                
                if (glucoseType === 'fasting') {
                    normalMin = 72; 
                    normalMax = 140;
                } else {
                    normalMin = 72; 
                    normalMax = 180; // post-meal
                }
            } else {
                // Based on PDF values for mmol/L
                criticalMin = 2.8; 
                criticalMax = 33.3;
                highRiskMin = 2.8; 
                highRiskMax = 33.3;
                borderlineMin = 14.0; 
                borderlineMax = 16.6;
                
                if (glucoseType === 'fasting') {
                    normalMin = 4.0; 
                    normalMax = 7.8;
                } else {
                    normalMin = 4.0; 
                    normalMax = 10.0; // post-meal
                }
            }
            
            // Validate minimum value (15 for mg/dL, equivalent for mmol/L)
            const minAllowed = unit === 'mg/dL' ? 15 : 0.8;
            if (glucose < minAllowed) {
                return { valid: false, message: `Too low (minimum ${minAllowed} ${unit})`, level: 'error' };
            }
            
            // Risk stratification based on PDF
            if (glucose < criticalMin || glucose > criticalMax) {
                return { valid: true, message: '🚨 CRITICAL - Dangerous glucose level', level: 'critical' };
            } else if ((glucose >= 50 && glucose <= 70) || (glucose >= 300 && glucose <= 599)) {
                return { valid: true, message: '⚠️ HIGH RISK - Abnormal glucose level', level: 'warning' };
            } else if (glucose >= borderlineMin && glucose <= borderlineMax) {
                return { valid: true, message: '⚠️ BORDERLINE - Monitor glucose levels', level: 'warning' };
            } else if (glucose >= normalMin && glucose <= normalMax) {
                const typeText = glucoseType === 'fasting' ? 'fasting' : 'post-meal';
                return { valid: true, message: `✅ NORMAL - Healthy ${typeText} glucose level`, level: 'success' };
            } else {
                return { valid: true, message: '⚠️ BORDERLINE - Outside normal range', level: 'warning' };
            }
        }
    }
};

// Tab functionality
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.vitals-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const targetTab = this.dataset.tab;

            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));

            // Add active class to clicked tab
            this.classList.add('active');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
                content.classList.add('hidden');
            });

            // Show target tab content
            const targetContent = document.getElementById(targetTab + '-tab');
            if (targetContent) {
                targetContent.classList.remove('hidden');
                setTimeout(() => {
                    targetContent.classList.add('active');
                }, 10);
            }
        });
    });

    // Initialize validation
    const inputs = document.querySelectorAll('[data-validation]');
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            validateField(this);
            generateAutoNotes();
        });
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });

    // Initialize wellness sliders
    resetWellnessSliders();
    generateAutoNotes();
});

function validateField(input) {
    const validationType = input.dataset.validation;
    const validation = validationRules[validationType];
    const messageElement = input.closest('.form-group').querySelector('.validation-message');

    if (!validation || !messageElement) return;

    const result = validation.validate(input.value);

    messageElement.classList.remove('hidden', 'success', 'warning', 'critical');
    messageElement.textContent = result.message;

    // Remove all validation classes
    input.classList.remove('border-red-300', 'border-yellow-300', 'border-green-300', 
                          'focus:border-red-500', 'focus:border-yellow-500', 'focus:border-green-500',
                          'focus:ring-red-500', 'focus:ring-yellow-500', 'focus:ring-green-500',
                          'critical-value');

    if (result.valid) {
        if (result.level === 'critical') {
            input.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500', 'critical-value');
            messageElement.classList.add('critical');
        } else if (result.level === 'warning') {
            input.classList.add('border-yellow-300', 'focus:border-yellow-500', 'focus:ring-yellow-500');
            messageElement.classList.add('warning');
        } else if (result.level === 'success') {
            input.classList.add('border-green-300', 'focus:border-green-500', 'focus:ring-green-500');
            messageElement.classList.add('success');
        }
        
        if (result.message) {
            messageElement.classList.remove('hidden');
        } else {
            messageElement.classList.add('hidden');
        }
    } else {
        input.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-500');
        messageElement.classList.remove('hidden');
    }
}

// Function to update glucose type and ranges
function updateGlucoseType() {
    const fastingRadio = document.querySelector('input[name="glucose_type"][value="fasting"]');
    const randomRadio = document.querySelector('input[name="glucose_type"][value="random"]');
    const fastingRanges = document.getElementById('fasting-ranges');
    const randomRanges = document.getElementById('random-ranges');
    const glucoseTypeHidden = document.getElementById('glucose-type-hidden');
    
    if (fastingRadio.checked) {
        fastingRanges.style.display = 'block';
        randomRanges.style.display = 'none';
        glucoseTypeHidden.value = 'fasting';
    } else {
        fastingRanges.style.display = 'none';
        randomRanges.style.display = 'block';
        glucoseTypeHidden.value = 'random';
    }
    
    // Re-validate glucose if there's a value
    const glucoseInput = document.getElementById('glucose-input');
    if (glucoseInput.value) {
        validateField(glucoseInput);
    }
    generateAutoNotes();
}

// Enhanced glucose unit conversion functions
function switchGlucoseUnit() {
    const mgdlRadio = document.querySelector('input[name="glucose_unit"][value="mg/dl"]');
    const mmollRadio = document.querySelector('input[name="glucose_unit"][value="mmol/l"]');
    const glucoseInput = document.getElementById('glucose-input');
    const glucoseUnit = document.getElementById('glucose-unit');
    const glucoseUnitHidden = document.getElementById('glucose-unit-hidden');
    const currentValue = parseFloat(glucoseInput.value);

    if (mmollRadio.checked && glucoseUnit.textContent === 'mg/dL') {
        // Convert from mg/dL to mmol/L
        if (currentValue) {
            const mmolValue = (currentValue / 18).toFixed(1);
            glucoseInput.value = mmolValue;
        }
        glucoseInput.placeholder = "5.5";
        glucoseInput.min = "0.8";
        glucoseInput.max = "35";
        glucoseUnit.textContent = 'mmol/L';
        glucoseUnitHidden.value = 'mmol/l';
    } else if (mgdlRadio.checked && glucoseUnit.textContent === 'mmol/L') {
        // Convert from mmol/L to mg/dL
        if (currentValue) {
            const mgdlValue = Math.round(currentValue * 18);
            glucoseInput.value = mgdlValue;
        }
        glucoseInput.placeholder = "100";
        glucoseInput.min = "15";
        glucoseInput.max = "600";
        glucoseUnit.textContent = 'mg/dL';
        glucoseUnitHidden.value = 'mg/dl';
    }

    updateGlucoseConversion();
    // Re-validate after unit change
    const glucoseInputElement = document.getElementById('glucose-input');
    if (glucoseInputElement.value) {
        validateField(glucoseInputElement);
    }
    generateAutoNotes();
}

function updateGlucoseConversion() {
    const glucoseInput = document.getElementById('glucose-input');
    const glucoseUnit = document.getElementById('glucose-unit');
    const conversionDiv = document.getElementById('glucose-conversion');
    const convertedValue = document.getElementById('converted-value');
    const currentValue = parseFloat(glucoseInput.value);

    if (currentValue) {
        let converted, convertedUnit;

        if (glucoseUnit.textContent === 'mg/dL') {
            converted = (currentValue / 18).toFixed(1);
            convertedUnit = 'mmol/L';
        } else {
            converted = Math.round(currentValue * 18);
            convertedUnit = 'mg/dL';
        }

        convertedValue.textContent = `≈ ${converted} ${convertedUnit}`;
        conversionDiv.style.display = 'block';
    } else {
        conversionDiv.style.display = 'none';
    }
}

// Enhanced auto-generate notes based on updated risk stratification
function generateAutoNotes() {
    const form = document.getElementById('vitalsForm');
    const bp = form.querySelector('input[name="blood_pressure"]').value;
    const hr = form.querySelector('input[name="heart_rate"]').value;
    const temp = form.querySelector('input[name="temperature"]').value;
    const weight = form.querySelector('input[name="weight"]').value;
    const glucose = form.querySelector('input[name="blood_glucose"]').value;
    const oxygen = form.querySelector('input[name="oxygen_saturation"]').value;
    const glucoseUnit = document.getElementById('glucose-unit').textContent;
    const glucoseType = document.querySelector('input[name="glucose_type"]:checked')?.value || 'fasting';
    const mood = form.querySelector('select[name="mood"]').value;
    const symptoms = Array.from(form.querySelectorAll('input[name="symptoms[]"]:checked')).map(cb => cb.value);

    let autoNotes = [];
    let riskLevel = 'Normal';
    let criticalFindings = [];
    let highRiskFindings = [];
    let borderlineFindings = [];

    // Analyze vitals using updated risk stratification from PDF
    if (bp) {
        const [systolic, diastolic] = bp.split('/').map(n => parseInt(n));
        if (systolic >= 140 || diastolic >= 90) {
            highRiskFindings.push("🚨 HYPERTENSION: Blood pressure indicates cardiovascular risk");
            riskLevel = 'High Risk';
        } else if (systolic >= 120 || diastolic >= 80) {
            borderlineFindings.push("⚠️ ELEVATED BP: Blood pressure approaching hypertensive range");
            if (riskLevel === 'Normal') riskLevel = 'Borderline';
        } else {
            autoNotes.push("✅ Blood pressure within normal range");
        }
    }

    if (hr) {
        const heartRate = parseInt(hr);
        if (heartRate < 40 || heartRate > 140) {
            criticalFindings.push("🚨 CRITICAL HEART RATE: Immediate medical evaluation required");
            riskLevel = 'Critical';
        } else if ((heartRate >= 40 && heartRate <= 50) || (heartRate >= 120 && heartRate <= 139)) {
            highRiskFindings.push("⚠️ HIGH RISK HEART RATE: Monitor cardiac function closely");
            if (riskLevel !== 'Critical') riskLevel = 'High Risk';
        } else if (heartRate >= 100 && heartRate <= 119) {
            borderlineFindings.push("⚠️ BORDERLINE HEART RATE: Slightly elevated");
            if (riskLevel === 'Normal') riskLevel = 'Borderline';
        } else if (heartRate >= 60 && heartRate <= 99) {
            autoNotes.push("✅ Heart rate within normal range");
        }
    }

    if (temp) {
        const temperature = parseFloat(temp);
        const tempC = (temperature - 32) * 5/9;
        
        if (tempC < 32.0 || tempC >= 40.0) {
            criticalFindings.push("🚨 EXTREME TEMPERATURE: Life-threatening condition");
            riskLevel = 'Critical';
        } else if ((tempC >= 32.0 && tempC < 35.0) || (tempC >= 39.0 && tempC < 40.0)) {
            highRiskFindings.push("⚠️ DANGEROUS TEMPERATURE: Requires immediate medical attention");
            if (riskLevel !== 'Critical') riskLevel = 'High Risk';
        } else if (tempC >= 38.0 && tempC < 39.0) {
            borderlineFindings.push("⚠️ MILD FEVER: Monitor for progression");
            if (riskLevel === 'Normal') riskLevel = 'Borderline';
        } else if (tempC >= 36.1 && tempC <= 37.9) {
            autoNotes.push("✅ Temperature within normal range");
        }
    }

    if (glucose) {
        const bloodGlucose = parseFloat(glucose);
        let isCritical, isHighRisk, isBorderline, isNormal;

        if (glucoseUnit === 'mg/dL') {
            isCritical = bloodGlucose < 50 || bloodGlucose > 600;
            isHighRisk = (bloodGlucose >= 50 && bloodGlucose <= 70) || (bloodGlucose >= 300 && bloodGlucose <= 599);
            isBorderline = bloodGlucose >= 250 && bloodGlucose <= 299;
            
            if (glucoseType === 'fasting') {
                isNormal = bloodGlucose >= 72 && bloodGlucose <= 140;
            } else {
                isNormal = bloodGlucose >= 72 && bloodGlucose <= 180;
            }
        } else {
            isCritical = bloodGlucose < 2.8 || bloodGlucose > 33.3;
            isHighRisk = (bloodGlucose >= 2.8 && bloodGlucose <= 3.9) || (bloodGlucose >= 16.7 && bloodGlucose <= 33.3);
            isBorderline = bloodGlucose >= 14.0 && bloodGlucose <= 16.6;
            
            if (glucoseType === 'fasting') {
                isNormal = bloodGlucose >= 4.0 && bloodGlucose <= 7.8;
            } else {
                isNormal = bloodGlucose >= 4.0 && bloodGlucose <= 10.0;
            }
        }

        const typeText = glucoseType === 'fasting' ? 'fasting' : 'post-meal';
        
        if (isCritical) {
            criticalFindings.push(`🚨 CRITICAL GLUCOSE: Severe ${bloodGlucose < (glucoseUnit === 'mg/dL' ? 50 : 2.8) ? 'hypoglycemia' : 'hyperglycemia'}`);
            riskLevel = 'Critical';
        } else if (isHighRisk) {
            highRiskFindings.push(`⚠️ HIGH RISK GLUCOSE: Abnormal ${typeText} level (${glucose} ${glucoseUnit})`);
            if (riskLevel !== 'Critical') riskLevel = 'High Risk';
        } else if (isBorderline) {
            borderlineFindings.push(`⚠️ BORDERLINE GLUCOSE: Monitor ${typeText} levels (${glucose} ${glucoseUnit})`);
            if (riskLevel === 'Normal') riskLevel = 'Borderline';
        } else if (isNormal) {
            autoNotes.push(`✅ Blood glucose within normal ${typeText} range (${glucose} ${glucoseUnit})`);
        }
    }

    if (oxygen) {
        const o2Sat = parseInt(oxygen);
        if (o2Sat < 85) {
            criticalFindings.push("🚨 SEVERE HYPOXEMIA: Immediate oxygen therapy required");
            riskLevel = 'Critical';
        } else if (o2Sat >= 85 && o2Sat <= 89) {
            highRiskFindings.push("⚠️ HIGH RISK HYPOXEMIA: Significant oxygen deficiency");
            if (riskLevel !== 'Critical') riskLevel = 'High Risk';
        } else if (o2Sat >= 90 && o2Sat <= 93) {
            borderlineFindings.push("⚠️ BORDERLINE OXYGEN: Monitor respiratory status");
            if (riskLevel === 'Normal') riskLevel = 'Borderline';
        } else if (o2Sat >= 94) {
            autoNotes.push("✅ Oxygen saturation within normal range");
        }
    }

    // Analyze symptoms for additional risk factors
    if (symptoms.length > 0) {
        const symptomsText = symptoms.map(s => s.replace('_', ' ')).join(', ');
        autoNotes.push(`📝 Symptoms reported: ${symptomsText}`);

        // Critical symptom combinations
        if (symptoms.includes('chest_pain') && symptoms.includes('shortness_of_breath')) {
            criticalFindings.push("🚨 CARDIAC/RESPIRATORY EMERGENCY: Chest pain + breathing difficulty");
            riskLevel = 'Critical';
        }
        if (symptoms.includes('fever') && symptoms.includes('shortness_of_breath')) {
            highRiskFindings.push("⚠️ INFECTIOUS PROCESS: Fever with respiratory symptoms");
            if (riskLevel !== 'Critical') riskLevel = 'High Risk';
        }
    }

    // Add mood context
    if (mood) {
        const moodDescriptions = {
            'excellent': '😊 Patient reports excellent mood and well-being',
            'good': '🙂 Patient in good spirits',
            'fair': '😐 Patient mood is fair - monitor emotional health',
            'poor': '😞 Patient reports poor mood - consider psychological support',
            'very_poor': '😷 Patient feeling very unwell - comprehensive evaluation needed'
        };
        if (moodDescriptions[mood]) {
            autoNotes.push(moodDescriptions[mood]);
        }
    }

    // Compile final assessment
    let finalNotes = [];
    
    // Add risk level assessment
    finalNotes.push(`🏥 OVERALL RISK ASSESSMENT: ${riskLevel.toUpperCase()}`);
    
    // Add critical findings first
    if (criticalFindings.length > 0) {
        finalNotes.push('');
        finalNotes.push('🚨 CRITICAL FINDINGS:');
        finalNotes = finalNotes.concat(criticalFindings);
        finalNotes.push('⚠️ RECOMMENDATION: Seek immediate emergency medical attention');
    }
    
    // Add high risk findings
    if (highRiskFindings.length > 0) {
        finalNotes.push('');
        finalNotes.push('⚠️ HIGH RISK FINDINGS:');
        finalNotes = finalNotes.concat(highRiskFindings);
        if (criticalFindings.length === 0) {
            finalNotes.push('📞 RECOMMENDATION: Contact healthcare provider urgently for evaluation');
        }
    }
    
    // Add borderline findings
    if (borderlineFindings.length > 0) {
        finalNotes.push('');
        finalNotes.push('⚠️ BORDERLINE FINDINGS:');
        finalNotes = finalNotes.concat(borderlineFindings);
        if (criticalFindings.length === 0 && highRiskFindings.length === 0) {
            finalNotes.push('📋 RECOMMENDATION: Monitor closely and consider healthcare consultation');
        }
    }
    
    // Add normal findings
    if (autoNotes.length > 0) {
        finalNotes.push('');
        finalNotes.push('✅ NORMAL FINDINGS:');
        finalNotes = finalNotes.concat(autoNotes);
    }

    // Add timestamp
    finalNotes.push('');
    finalNotes.push(`📅 Assessment generated: ${new Date().toLocaleString()}`);

    // Display auto-notes
    const autoNotesElement = document.getElementById('auto-notes');
    if (finalNotes.length > 1) {
        autoNotesElement.innerHTML = finalNotes.map(note => `<p class="mb-1">${note}</p>`).join('');
    } else {
        autoNotesElement.innerHTML = '<p class="italic">Risk assessment will be automatically generated based on your vitals and symptoms when you submit.</p>';
    }
}

// Wellness slider updates
function updateWellnessSlider(slider, type) {
    const value = parseInt(slider.value);
    document.getElementById(`${type}-value`).textContent = value;

    if (type === 'energy') {
        const energyEmojis = ['😴', '😪', '😕', '😐', '🙂', '😊', '😃', '😄', '🤩', '⚡'];
        document.getElementById('energy-emoji').textContent = energyEmojis[value - 1] || '😐';
    } else if (type === 'pain') {
        const painEmojis = ['😊', '🙂', '😐', '😕', '😣', '😖', '😰', '😫', '😱', '😭', '💀'];
        document.getElementById('pain-emoji').textContent = painEmojis[value] || '😊';
    }

    generateAutoNotes();
}

// Form submission with enhanced validation
document.getElementById('vitalsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const submitBtn = document.getElementById('submitBtn');
    const originalContent = submitBtn.innerHTML;

    // Validate all fields
    const inputs = document.querySelectorAll('[data-validation]');
    let isValid = true;
    let hasCritical = false;

    inputs.forEach(input => {
        validateField(input);
        if (input.classList.contains('border-red-300') && !input.classList.contains('critical-value')) {
            isValid = false;
        }
        if (input.classList.contains('critical-value')) {
            hasCritical = true;
        }
    });

    if (!isValid) {
        showToast('❌ Please fix the validation errors before submitting', 'error');
        return;
    }

    // Warning for critical values
    if (hasCritical) {
        const confirmSubmit = confirm('⚠️ CRITICAL VALUES DETECTED!\n\nYour vitals show values that may require immediate medical attention. Do you want to continue submitting these measurements?\n\nConsider seeking medical evaluation if you haven\'t already.');
        if (!confirmSubmit) {
            return;
        }
    }

    // Show loading state
    showLoading(submitBtn);

    try {
        const formData = new FormData(this);

        // Add auto-generated notes to the submission
        const autoNotesElement = document.getElementById('auto-notes');
        const autoNotesText = autoNotesElement.textContent.replace(/Risk assessment will be automatically generated.*/, '').trim();

        if (autoNotesText) {
            const existingNotes = formData.get('notes') || '';
            const enhancedNotes = existingNotes + (existingNotes ? '\n\n' : '') + 'AUTOMATED RISK ASSESSMENT:\n' + autoNotesText;
            formData.set('notes', enhancedNotes);
        }

        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (result.success) {
            // Show success overlay with risk assessment
            showSuccessOverlay(result.message);

            // Redirect after 3 seconds
            setTimeout(() => {
                window.location.href = result.redirect_url || '/patient/vitals';
            }, 3000);
        } else {
            showToast(result.message || 'Failed to save vitals', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('An error occurred while saving vitals', 'error');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalContent;
        submitBtn.disabled = false;
    }
});

// Show loading state
function showLoading(button) {
    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Saving...
    `;
}

// Show success overlay
function showSuccessOverlay(message) {
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-70 flex items-center justify-center z-50 transition-opacity duration-300 opacity-0';
    overlay.id = 'successOverlay';

    const messageBox = document.createElement('div');
    messageBox.className = 'bg-white p-8 rounded-lg text-center max-w-md mx-4 shadow-xl';
    messageBox.innerHTML = `
        <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
        <h3 class="text-xl font-bold mb-2">Vitals Recorded Successfully!</h3>
        <p class="mb-4">${message}</p>
        <p class="text-sm text-gray-500">Redirecting in 3 seconds...</p>
    `;

    overlay.appendChild(messageBox);
    document.body.appendChild(overlay);

    setTimeout(() => {
        overlay.classList.remove('opacity-0');
        overlay.classList.add('opacity-100');
    }, 10);
}

// Production-ready sample data generator with random realistic values
function fillSampleData() {
    const form = document.getElementById('vitalsForm');
    
    // Generate realistic sample data with different risk levels
    const sampleDataSets = [
        // Normal healthy values
        {
            blood_pressure: '115/75',
            heart_rate: 72,
            temperature: 98.2,
            weight: 155.0,
            oxygen_saturation: 98,
            blood_glucose: 95,
            steps: 8500,
            sleep_hours: 7.5,
            mood: 'good',
            energy_level: 7,
            pain_level: 0,
            glucose_type: 'fasting',
            notes: 'Feeling well today. Regular exercise and good sleep.'
        },
        // Borderline values
        {
            blood_pressure: '125/82',
            heart_rate: 105,
            temperature: 99.1,
            weight: 148.5,
            oxygen_saturation: 92,
            blood_glucose: 155,
            steps: 4200,
            sleep_hours: 5.5,
            mood: 'fair',
            energy_level: 4,
            pain_level: 3,
            glucose_type: 'random',
            notes: 'Feeling slightly tired. Had a stressful week at work.'
        },
        // High risk values
        {
            blood_pressure: '145/95',
            heart_rate: 125,
            temperature: 101.2,
            weight: 162.0,
            oxygen_saturation: 88,
            blood_glucose: 320,
            steps: 1800,
            sleep_hours: 4.0,
            mood: 'poor',
            energy_level: 2,
            pain_level: 6,
            glucose_type: 'random',
            notes: 'Not feeling well. Experiencing fatigue and elevated readings.'
        },
        // Critical values
        {
            blood_pressure: '180/110',
            heart_rate: 35,
            temperature: 103.5,
            weight: 145.0,
            oxygen_saturation: 82,
            blood_glucose: 45,
            steps: 500,
            sleep_hours: 2.0,
            mood: 'very_poor',
            energy_level: 1,
            pain_level: 8,
            glucose_type: 'fasting',
            notes: 'Feeling very unwell. Experiencing severe symptoms. May need medical attention.'
        }
    ];

    // Randomly select a sample data set
    const randomIndex = Math.floor(Math.random() * sampleDataSets.length);
    const sampleData = sampleDataSets[randomIndex];

    // Fill the form with selected sample data
    form.querySelector('input[name="blood_pressure"]').value = sampleData.blood_pressure;
    form.querySelector('input[name="heart_rate"]').value = sampleData.heart_rate;
    form.querySelector('input[name="temperature"]').value = sampleData.temperature;
    form.querySelector('input[name="weight"]').value = sampleData.weight;
    form.querySelector('input[name="oxygen_saturation"]').value = sampleData.oxygen_saturation;
    form.querySelector('input[name="blood_glucose"]').value = sampleData.blood_glucose;
    form.querySelector('input[name="steps"]').value = sampleData.steps;
    form.querySelector('input[name="sleep_hours"]').value = sampleData.sleep_hours;
    form.querySelector('select[name="mood"]').value = sampleData.mood;
    form.querySelector('textarea[name="notes"]').value = sampleData.notes;

    // Set glucose type
    form.querySelector(`input[name="glucose_type"][value="${sampleData.glucose_type}"]`).checked = true;
    updateGlucoseType();

    // Set glucose unit to mg/dL and update display
    form.querySelector('input[name="glucose_unit"][value="mg/dl"]').checked = true;
    document.getElementById('glucose-unit').textContent = 'mg/dL';
    document.getElementById('glucose-unit-hidden').value = 'mg/dl';
    updateGlucoseConversion();

    // Set wellness sliders
    const energySlider = form.querySelector('input[name="energy_level"]');
    energySlider.value = sampleData.energy_level;
    updateWellnessSlider(energySlider, 'energy');

    const painSlider = form.querySelector('input[name="pain_level"]');
    painSlider.value = sampleData.pain_level;
    updateWellnessSlider(painSlider, 'pain');

    // Randomly add some symptoms for higher risk samples
    if (randomIndex >= 2) {
        const possibleSymptoms = ['headache', 'fatigue', 'dizziness', 'nausea'];
        const numSymptoms = Math.floor(Math.random() * 3) + 1;
        const selectedSymptoms = possibleSymptoms.slice(0, numSymptoms);
        
        selectedSymptoms.forEach(symptom => {
            const checkbox = form.querySelector(`input[name="symptoms[]"][value="${symptom}"]`);
            if (checkbox) {
                checkbox.checked = true;
                checkbox.closest('.symptom-checkbox').classList.add('border-orange-500', 'bg-orange-50');
            }
        });
    }

    // Generate auto-notes for sample data
    generateAutoNotes();

    // Show appropriate message based on sample type
    const riskLevels = ['Normal', 'Borderline', 'High Risk', 'Critical'];
    showToast(`📊 ${riskLevels[randomIndex]} sample data loaded successfully!`, randomIndex >= 2 ? 'error' : 'info');
}

function clearForm() {
    const form = document.getElementById('vitalsForm');
    form.reset();
    resetWellnessSliders();
    resetSymptoms();

    // Reset glucose unit to default
    document.getElementById('glucose-unit').textContent = 'mg/dL';
    document.getElementById('glucose-unit-hidden').value = 'mg/dl';
    document.getElementById('glucose-type-hidden').value = 'fasting';
    document.getElementById('glucose-conversion').style.display = 'none';
    
    // Reset glucose ranges display
    document.getElementById('fasting-ranges').style.display = 'block';
    document.getElementById('random-ranges').style.display = 'none';

    // Clear validation states
    const inputs = document.querySelectorAll('[data-validation]');
    inputs.forEach(input => {
        input.classList.remove('border-red-300', 'border-yellow-300', 'border-green-300', 
                              'focus:border-red-500', 'focus:border-yellow-500', 'focus:border-green-500',
                              'focus:ring-red-500', 'focus:ring-yellow-500', 'focus:ring-green-500',
                              'critical-value');
        const messageElement = input.closest('.form-group').querySelector('.validation-message');
        if (messageElement) {
            messageElement.classList.add('hidden');
        }
    });

    const autoNotesElement = document.getElementById('auto-notes');
    autoNotesElement.innerHTML = '<p class="italic">Risk assessment will be automatically generated based on your vitals and symptoms when you submit.</p>';

    showToast('🗑️ Form cleared', 'info');
}

function resetWellnessSliders() {
    const energySlider = document.querySelector('input[name="energy_level"]');
    const painSlider = document.querySelector('input[name="pain_level"]');

    if (energySlider) {
        energySlider.value = 5;
        updateWellnessSlider(energySlider, 'energy');
    }

    if (painSlider) {
        painSlider.value = 0;
        updateWellnessSlider(painSlider, 'pain');
    }
}

function resetSymptoms() {
    document.querySelectorAll('.symptom-checkbox input').forEach(checkbox => {
        checkbox.checked = false;
        checkbox.closest('.symptom-checkbox').classList.remove('border-orange-500', 'bg-orange-50');
    });
}

// Vital details modal functions
function showVitalDetails(vital) {
    const date = new Date(vital.measured_at);
    const formattedDate = date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    // Risk assessment using PDF guidelines
    let riskLevel = 'normal';
    let riskClass = 'bg-green-100 text-green-700';
    let riskDetails = [];

    // Assess each vital sign using PDF criteria
    if (vital.blood_pressure) {
        const parts = vital.blood_pressure.split('/');
        if (parts.length === 2) {
            const systolic = parseInt(parts[0]);
            const diastolic = parseInt(parts[1]);
            if (systolic >= 140 || diastolic >= 90) {
                riskLevel = 'high risk';
                riskClass = 'bg-red-100 text-red-700';
                riskDetails.push('⚠️ Hypertensive blood pressure');
            } else if (systolic >= 120 || diastolic >= 80) {
                if (riskLevel === 'normal') {
                    riskLevel = 'borderline';
                    riskClass = 'bg-yellow-100 text-yellow-700';
                }
                riskDetails.push('⚠️ Elevated blood pressure');
            }
        }
    }

    if (vital.heart_rate) {
        const hr = parseInt(vital.heart_rate);
        if (hr < 40 || hr > 140) {
            riskLevel = 'critical';
            riskClass = 'bg-red-100 text-red-700';
            riskDetails.push('🚨 Critical heart rate');
        } else if ((hr >= 40 && hr <= 50) || (hr >= 120 && hr <= 139)) {
            if (riskLevel !== 'critical') {
                riskLevel = 'high risk';
                riskClass = 'bg-orange-100 text-orange-700';
            }
            riskDetails.push('⚠️ High risk heart rate');
        } else if (hr >= 100 && hr <= 119) {
            if (riskLevel === 'normal') {
                riskLevel = 'borderline';
                riskClass = 'bg-yellow-100 text-yellow-700';
            }
            riskDetails.push('⚠️ Borderline heart rate');
        }
    }

    if (vital.temperature) {
        const temp = parseFloat(vital.temperature);
        const tempC = (temp - 32) * 5/9;
        if (tempC < 32.0 || tempC >= 40.0) {
            riskLevel = 'critical';
            riskClass = 'bg-red-100 text-red-700';
            riskDetails.push('🚨 Extreme temperature');
        } else if ((tempC >= 32.0 && tempC < 35.0) || (tempC >= 39.0 && tempC < 40.0)) {
            if (riskLevel !== 'critical') {
                riskLevel = 'high risk';
                riskClass = 'bg-orange-100 text-orange-700';
            }
            riskDetails.push('⚠️ Dangerous temperature');
        } else if (tempC >= 38.0 && tempC < 39.0) {
            if (riskLevel === 'normal') {
                riskLevel = 'borderline';
                riskClass = 'bg-yellow-100 text-yellow-700';
            }
            riskDetails.push('⚠️ Mild fever');
        }
    }

    if (vital.oxygen_saturation) {
        const o2 = parseInt(vital.oxygen_saturation);
        if (o2 < 85) {
            riskLevel = 'critical';
            riskClass = 'bg-red-100 text-red-700';
            riskDetails.push('🚨 Severe hypoxemia');
        } else if (o2 >= 85 && o2 <= 89) {
            if (riskLevel !== 'critical') {
                riskLevel = 'high risk';
                riskClass = 'bg-orange-100 text-orange-700';
            }
            riskDetails.push('⚠️ High risk hypoxemia');
        } else if (o2 >= 90 && o2 <= 93) {
            if (riskLevel === 'normal') {
                riskLevel = 'borderline';
                riskClass = 'bg-yellow-100 text-yellow-700';
            }
            riskDetails.push('⚠️ Low oxygen saturation');
        }
    }

    if (vital.blood_glucose) {
        const glucose = parseFloat(vital.blood_glucose);
        if (glucose < 50 || glucose > 600) {
            riskLevel = 'critical';
            riskClass = 'bg-red-100 text-red-700';
            riskDetails.push('🚨 Critical glucose level');
        } else if ((glucose >= 50 && glucose <= 70) || (glucose >= 300 && glucose <= 599)) {
            if (riskLevel !== 'critical') {
                riskLevel = 'high risk';
                riskClass = 'bg-orange-100 text-orange-700';
            }
            riskDetails.push('⚠️ High risk glucose level');
        } else if (glucose >= 250 && glucose <= 299) {
            if (riskLevel === 'normal') {
                riskLevel = 'borderline';
                riskClass = 'bg-yellow-100 text-yellow-700';
            }
            riskDetails.push('⚠️ Borderline glucose level');
        }
    }

    const moods = {
        'excellent': ['😊', 'Excellent'],
        'good': ['🙂', 'Good'],
        'fair': ['😐', 'Fair'],
        'poor': ['😞', 'Poor'],
        'very_poor': ['😷', 'Very Poor']
    };
    const moodData = vital.mood ? moods[vital.mood] || ['😐', 'Unknown'] : null;

    let content = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Recorded At</h4>
                    <p class="mt-1 text-sm text-gray-900">${formattedDate}</p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Blood Pressure</h4>
                    <p class="mt-1 text-sm text-gray-900">${vital.blood_pressure || '-'} <span class="text-gray-500">mmHg</span></p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Heart Rate</h4>
                    <p class="mt-1 text-sm text-gray-900">${vital.heart_rate || '-'} <span class="text-gray-500">bpm</span></p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Temperature</h4>
                    <p class="mt-1 text-sm text-gray-900">${vital.temperature || '-'} <span class="text-gray-500">°F</span></p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-500">Weight</h4>
                    <p class="mt-1 text-sm text-gray-900">${vital.weight || '-'} <span class="text-gray-500">lbs</span></p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Blood Glucose</h4>
                    <p class="mt-1 text-sm text-gray-900">${vital.blood_glucose || '-'} <span class="text-gray-500">mg/dL</span></p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Oxygen Saturation</h4>
                    <p class="mt-1 text-sm text-gray-900">${vital.oxygen_saturation || '-'} <span class="text-gray-500">%</span></p>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-500">Mood</h4>
                    <p class="mt-1 text-sm text-gray-900">${moodData ? moodData[0] + ' ' + moodData[1] : '-'}</p>
                </div>
            </div>
        </div>

        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-500">Notes</h4>
            <p class="mt-1 text-sm text-gray-900">${vital.notes || 'No notes recorded'}</p>
        </div>

        <div class="mt-6">
            <h4 class="text-sm font-medium text-gray-500">Risk Assessment</h4>
            <div class="mt-2">
                <span class="px-3 py-1 text-sm font-medium rounded-full ${riskClass}">
                    ${riskLevel.charAt(0).toUpperCase() + riskLevel.slice(1)} Risk
                </span>
                ${riskDetails.length > 0 ? `
                    <div class="mt-2 space-y-1">
                        ${riskDetails.map(detail => `<p class="text-xs text-gray-600">${detail}</p>`).join('')}
                    </div>
                ` : ''}
            </div>
        </div>
    `;

    document.getElementById('vitalDetailsContent').innerHTML = content;
    document.getElementById('vitalDetailsModal').classList.remove('hidden');
}

function closeVitalDetails() {
    document.getElementById('vitalDetailsModal').classList.add('hidden');
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full ${
        type === 'error' ? 'bg-red-500 text-white' :
        type === 'success' ? 'bg-green-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    toast.textContent = message;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.remove('translate-x-full');
        toast.classList.add('translate-x-0');
    }, 100);

    setTimeout(() => {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (document.body.contains(toast)) {
                document.body.removeChild(toast);
            }
        }, 300);
    }, 3000);
}

// Close modal on outside click
document.getElementById('vitalDetailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVitalDetails();
    }
});

// Add event listeners for all interactive elements
document.addEventListener('DOMContentLoaded', function() {
    const symptomCheckboxes = document.querySelectorAll('input[name="symptoms[]"]');
    symptomCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', generateAutoNotes);
    });

    const moodSelect = document.querySelector('select[name="mood"]');
    if (moodSelect) {
        moodSelect.addEventListener('change', generateAutoNotes);
    }

    const glucoseInput = document.querySelector('input[name="blood_glucose"]');
    if (glucoseInput) {
        glucoseInput.addEventListener('input', function() {
            updateGlucoseConversion();
            generateAutoNotes();
        });
    }

    const glucoseUnitRadios = document.querySelectorAll('input[name="glucose_unit"]');
    glucoseUnitRadios.forEach(radio => {
        radio.addEventListener('change', generateAutoNotes);
    });

    const glucoseTypeRadios = document.querySelectorAll('input[name="glucose_type"]');
    glucoseTypeRadios.forEach(radio => {
        radio.addEventListener('change', generateAutoNotes);
    });

    // Initialize wellness sliders with current values
    const energySlider = document.querySelector('input[name="energy_level"]');
    const painSlider = document.querySelector('input[name="pain_level"]');

    if (energySlider) {
        updateWellnessSlider(energySlider, 'energy');
    }

    if (painSlider) {
        updateWellnessSlider(painSlider, 'pain');
    }

    generateAutoNotes();
});
</script>
@endpush