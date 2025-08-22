<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Monitor - {{ $patient->user->name ?? 'Unknown Patient' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        .medical-card { background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,.08); transition: all .3s ease; }
        .medical-card:hover { box-shadow: 0 20px 40px rgba(0,0,0,.12); transform: translateY(-2px); }
        .btn-primary { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); border: none; color: white; border-radius: 12px; font-weight: 600; transition: all .3s ease; padding: 12px 24px; display: inline-flex; align-items: center; justify-content: center; }
        .btn-primary:hover { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); transform: translateY(-1px); box-shadow: 0 10px 25px rgba(59,130,246,.4); }
        .btn-secondary { background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); color: #374151; border: none; border-radius: 12px; font-weight: 600; transition: all .3s ease; padding: 12px 24px; display: inline-flex; align-items: center; justify-content: center; }
        .btn-secondary:hover { background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%); transform: translateY(-1px); }
        .status-badge { display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .025em; }
        .status-normal { background: #dcfce7; color: #166534; } .status-warning { background: #fef3c7; color: #92400e; } .status-critical { background: #fecaca; color: #991b1b; }
        .status-indicator { width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
        .status-indicator.status-normal { background: #22c55e; } .status-indicator.status-warning { background: #f59e0b; } .status-indicator.status-critical { background: #ef4444; }
        .temp_access-banner { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 16px 24px; border-radius: 16px; margin-bottom: 24px; }
        .chart-filter-btn.active { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; }
        .loading-spinner { border: 3px solid #f3f4f6; border-radius: 50%; border-top: 3px solid #3b82f6; width: 20px; height: 20px; animation: spin 1s linear infinite; }
        @keyframes spin { 0%{transform:rotate(0)}100%{transform:rotate(360deg)} }
        .blur-content { filter: blur(4px); pointer-events: none; user-select: none; }
        .verification-overlay { background: rgba(0,0,0,.75); backdrop-filter: blur(8px); }
        .form-input { border: 2px solid #e5e7eb; border-radius: 12px; padding: 12px 16px; transition: all .3s ease; width: 100%; }
        .form-input:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); outline: none; }
        .security-badge { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; }
        .verification-code-input { font-family: 'Courier New', monospace; letter-spacing: .2em; text-transform: uppercase; text-align: center; font-size: 18px; font-weight: bold; }
        .verification-code-display { background: #f0f9ff; border: 2px solid #0ea5e9; border-radius: 12px; padding: 12px; text-align: center; font-family: 'Courier New', monospace; font-size: 20px; font-weight: bold; color: #0284c7; letter-spacing: .3em; }
    </style>
</head>
<body class="bg-gray-50">
    {{-- Verification Modal (shown when doctor not verified) --}}
    @if(!$tempAccess->doctor_verified)
    <div id="verificationModal" class="fixed inset-0 verification-overlay z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-screen overflow-y-auto">
            <div class="p-8">
                <div class="text-center mb-8">
                    <div class="mx-auto h-16 w-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900">Doctor Verification Required</h2>
                    <p class="text-sm text-gray-600 mt-2">Verify your credentials to access {{ $patient->user->name ?? 'patient' }}'s data</p>
                    <div class="mt-4">
                        <span class="security-badge">Secure Access • Expires {{ $tempAccess->expires_at->diffForHumans() }}</span>
                    </div>
                </div>

                <div class="bg-blue-50 rounded-xl p-4 mb-6">
                    <div class="flex items-center space-x-3">
                        <div class="h-10 w-10 bg-gradient-to-br from-blue-100 to-indigo-200 rounded-xl flex items-center justify-center">
                            <span class="text-blue-600 font-bold">{{ strtoupper(substr($patient->user->name ?? 'P', 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="font-semibold text-blue-900">{{ $patient->user->name ?? 'Patient' }}</p>
                            <p class="text-sm text-blue-700">Generated by: {{ $tempAccess->generatedBy->name ?? 'Patient' }}</p>
                            <p class="text-xs text-blue-600">Access #{{ $tempAccess->access_count }}</p>
                        </div>
                    </div>
                </div>

                @if($tempAccess->verification_code)
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 mb-6 border border-blue-200">
                    <div class="text-center">
                        <h4 class="text-sm font-semibold text-blue-800 mb-2">Expected Verification Code</h4>
                        <div class="verification-code-display">
                            {{ $tempAccess->formatted_verification_code ?? $tempAccess->verification_code }}
                        </div>
                        <p class="text-xs text-blue-600 mt-2">This code was provided by the patient</p>
                    </div>
                </div>
                @endif

                <form id="verificationForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input id="doctor_name" name="doctor_name" type="text" required class="form-input" placeholder="Dr. John Smith">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medical Specialty *</label>
                        <select id="doctor_specialty" name="doctor_specialty" required class="form-input">
                            <option value="">Select Specialty</option>
                            <option>General Practice</option><option>Internal Medicine</option><option>Family Medicine</option>
                            <option>Emergency Medicine</option><option>Cardiology</option><option>Neurology</option>
                            <option>Orthopedics</option><option>Pediatrics</option><option>Gynecology</option>
                            <option>Psychiatry</option><option>Dermatology</option><option>Ophthalmology</option>
                            <option>ENT</option><option>Radiology</option><option>Anesthesiology</option><option>Surgery</option><option>Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Hospital/Clinic/Facility *</label>
                        <input id="doctor_facility" name="doctor_facility" type="text" required class="form-input" placeholder="General Hospital, City Medical Center">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                        <input id="doctor_phone" name="doctor_phone" type="tel" required class="form-input" placeholder="+1 5551234567">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Verification Code *</label>
                        <input id="verification_code" name="verification_code" type="text" required
                               class="form-input verification-code-input"
                               placeholder="XXXX-XXXX"
                               maxlength="24"
                               pattern="^[A-Za-z0-9\-\s]{8,24}$">
                        <p class="mt-1 text-xs text-gray-500">Enter the 8-character code (letters/numbers) with or without dashes/spaces.</p>
                        <div id="codeError" class="mt-1 text-xs text-red-600 hidden"></div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="ml-2">
                                <h4 class="text-sm font-medium text-blue-800">Access Permissions</h4>
                                <ul class="mt-1 text-xs text-blue-700 space-y-1">
                                    <li>• View patient vital signs and medical history</li>
                                    <li>• Upload medical documents and reports</li>
                                    <li>• Prescribe medications</li>
                                    <li>• Provide health tips and recommendations</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="verifyBtn" class="w-full btn-primary">
                        <span id="submitText">Verify & Access Patient Data</span>
                        <div id="loadingSpinner" class="loading-spinner ml-2 hidden"></div>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Dashboard Content --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 {{ !$tempAccess->doctor_verified ? 'blur-content' : '' }}">
        <div class="temp_access-banner">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <div>
                        <h3 class="font-bold">
                            @if($tempAccess->doctor_verified)
                                Temporary Access — Dr. {{ $tempAccess->doctor_name }}
                            @else
                                Temporary Access — Verification Required
                            @endif
                        </h3>
                        <p class="text-sm opacity-90">
                            @if($tempAccess->doctor_verified)
                                {{ $tempAccess->doctor_specialty }} at {{ $tempAccess->doctor_facility }} •
                                Expires {{ $tempAccess->expires_at->diffForHumans() }} •
                                Access #{{ $tempAccess->access_count }}
                            @else
                                Please complete verification to access patient data
                            @endif
                        </p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm opacity-90">Time Remaining</div>
                    <div class="font-bold text-lg">{{ $tempAccess->time_remaining }}</div>
                </div>
            </div>
        </div>

        @if($tempAccess->doctor_verified)
        {{-- Patient Header --}}
        <div class="medical-card p-8 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-3xl flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-2xl">{{ strtoupper(substr($patient->user->name ?? 'P', 0, 1)) }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $patient->user->name ?? 'Unknown Patient' }}</h1>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span>📋 {{ $patient->medical_record_number ?? 'No MRN' }}</span>
                            <span>🩸 {{ $patient->blood_type ?? 'Unknown' }}</span>
                            <span>📧 {{ $patient->user->email ?? 'No Email' }}</span>
                            @if(optional($patient->user)->phone)
                                <span>📞 {{ $patient->user->phone }}</span>
                            @endif
                        </div>
                        @if($latestVitals)
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="status-badge status-{{ $latestVitals->status ?? 'normal' }}">
                                <div class="status-indicator status-{{ $latestVitals->status ?? 'normal' }}"></div>
                                {{ ucfirst($latestVitals->status ?? 'normal') }}
                            </span>
                            <span class="text-xs text-gray-500">Last reading: {{ $latestVitals->measured_at->diffForHumans() }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">
                    <button onclick="generateReport()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate Report
                    </button>
                </div>
            </div>
        </div>

        {{-- Current Vitals --}}
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6 mb-8">
            <div class="medical-card p-6 text-center" id="bpCard">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">
                    {{ $latestVitals ? ($latestVitals->systolic_bp . '/' . $latestVitals->diastolic_bp) : '--/--' }}
                </div>
                <div class="text-sm text-gray-600 font-medium">Blood Pressure</div>
                <div class="text-xs text-gray-400 mt-1">mmHg</div>
                <div class="text-xs text-gray-500 mt-2">
                    {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                </div>
            </div>

            <div class="medical-card p-6 text-center" id="hrCard">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $latestVitals->heart_rate ?? '--' }}</div>
                <div class="text-sm text-gray-600 font-medium">Heart Rate</div>
                <div class="text-xs text-gray-400 mt-1">bpm</div>
                <div class="text-xs text-gray-500 mt-2">{{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}</div>
            </div>

            <div class="medical-card p-6 text-center" id="tempCard">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l3-3 3 3v13M9 19h6M9 19H7a2 2 0 01-2-2V9a2 2 0 012-2h2M15 19h2a2 2 0 002-2V9a2 2 0 00-2-2h-2"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $latestVitals->temperature ?? '--' }}°F</div>
                <div class="text-sm text-gray-600 font-medium">Temperature</div>
                <div class="text-xs text-gray-400 mt-1">Fahrenheit</div>
                <div class="text-xs text-gray-500 mt-2">{{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}</div>
            </div>

            <div class="medical-card p-6 text-center" id="oxygenCard">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $latestVitals->oxygen_saturation ?? '--' }}%</div>
                <div class="text-sm text-gray-600 font-medium">Oxygen Saturation</div>
                <div class="text-xs text-gray-400 mt-1">SpO2</div>
                <div class="text-xs text-gray-500 mt-2">{{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}</div>
            </div>
        </div>

        {{-- Trends --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-2 space-y-8">
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Vital Signs Trends</h3>
                                <p class="text-gray-600">Patient monitoring data</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="status-indicator status-normal"></div>
                                <span class="text-sm text-gray-500">Live Updates</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button onclick="updateChart('blood_pressure', event)" class="chart-filter-btn active bg-gradient-to-r from-red-100 to-red-200 text-red-700 rounded-xl px-4 py-2 text-sm font-semibold">Blood Pressure</button>
                            <button onclick="updateChart('heart_rate', event)" class="chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200">Heart Rate</button>
                            <button onclick="updateChart('temperature', event)" class="chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200">Temperature</button>
                            <button onclick="updateChart('oxygen_saturation', event)" class="chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200">Oxygen Saturation</button>
                        </div>

                        <div class="relative h-96 bg-gradient-to-br from-gray-50 to-white rounded-3xl p-6 border border-gray-100">
                            <canvas id="vitalsChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Recent Vitals History</h3>
                        <p class="text-gray-600">Latest patient vital signs</p>
                    </div>
                    <div class="p-8">
                        @forelse($recentVitals as $vital)
                            <div class="flex items-center justify-between py-4 border-b border-gray-100 last:border-b-0 hover:bg-gray-50 rounded-lg px-4 cursor-pointer" onclick="showVitalDetails({{ json_encode($vital) }})">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $vital->measured_at->format('M j, g:i A') }}</p>
                                    <div class="flex items-center mt-1 text-sm text-gray-500 space-x-4">
                                        <span>BP: {{ $vital->systolic_bp ?? '--' }}/{{ $vital->diastolic_bp ?? '--' }}</span>
                                        <span>HR: {{ $vital->heart_rate ?? '--' }} bpm</span>
                                        <span>Temp: {{ $vital->temperature ? $vital->temperature . '°F' : '--' }}</span>
                                        <span>O2: {{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '--' }}</span>
                                    </div>
                                </div>
                                <span class="status-badge status-{{ $vital->status ?? 'normal' }}">
                                    <div class="status-indicator status-{{ $vital->status ?? 'normal' }}"></div>
                                    {{ ucfirst($vital->status ?? 'normal') }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2z"></path>
                                </svg>
                                <h3 class="mt-4 text-lg font-medium text-gray-900">No Vitals History</h3>
                                <p class="mt-2 text-gray-500">Patient vitals will appear here once recorded</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <div class="medical-card p-6">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">Quick Actions</h3>
                        <p class="text-sm text-gray-500">External doctor tools</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <button onclick="openUploadModal()" class="btn-primary w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                            Upload Document
                        </button>

                        <button onclick="openPrescriptionModal()" class="btn-secondary w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                            Prescribe Medication
                        </button>

                        <button onclick="openHealthTipsModal()" class="btn-secondary w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Add Health Tips
                        </button>
                    </div>
                </div>

                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Patient Information</h3>
                        <p class="text-sm text-gray-600">Medical details</p>
                    </div>

                    <div class="space-y-3">
                        <div class="flex justify-between"><span class="text-sm text-gray-600">Blood Type:</span><span class="text-sm font-semibold text-red-600">{{ $patient->blood_type ?? 'Unknown' }}</span></div>
                        <div class="flex justify-between"><span class="text-sm text-gray-600">Height:</span><span class="text-sm font-semibold">{{ $patient->height ? $patient->height . ' cm' : 'N/A' }}</span></div>
                        <div class="flex justify-between"><span class="text-sm text-gray-600">Weight:</span><span class="text-sm font-semibold">{{ $patient->current_weight ? $patient->current_weight . ' kg' : 'N/A' }}</span></div>
                        <div class="flex justify-between"><span class="text-sm text-gray-600">Activity Level:</span><span class="text-sm font-semibold">{{ $patient->activity_level ? ucfirst(str_replace('_', ' ', $patient->activity_level)) : 'N/A' }}</span></div>
                        <div class="flex justify-between"><span class="text-sm text-gray-600">Smoker:</span><span class="text-sm font-semibold">{{ $patient->smoker ? 'Yes' : 'No' }}</span></div>
                    </div>

                    @if($patient->allergies)
                        <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <h4 class="text-sm font-semibold text-red-800 mb-1">Allergies</h4>
                            <p class="text-sm text-red-700">{{ $patient->allergies }}</p>
                        </div>
                    @endif

                    @if($patient->chronic_conditions)
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <h4 class="text-sm font-semibold text-yellow-800 mb-1">Chronic Conditions</h4>
                            <p class="text-sm text-yellow-700">{{ $patient->chronic_conditions }}</p>
                        </div>
                    @endif
                </div>

                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Current Medications</h3>
                        <p class="text-sm text-gray-600">Patient-reported medications</p>
                    </div>

                    @forelse($medications as $med)
                        <div class="border-b border-gray-100 py-3 last:border-b-0">
                            <p class="text-sm font-medium text-gray-900">{{ $med->name }}</p>
                            <p class="text-xs text-gray-500">{{ $med->dosage }} • {{ $med->frequency }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 italic">No medications recorded</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="medical-card mt-8">
            <div class="p-8 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Patient Documents</h3>
                        <p class="text-gray-600">Medical records and reports</p>
                    </div>
                    <button onclick="openUploadModal()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        Upload Document
                    </button>
                </div>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($documents as $document)
                        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex items-center">
                                    <span class="text-2xl mr-3">
                                        @if(str_contains($document->file_type, 'image')) 🖼️
                                        @elseif($document->file_type === 'application/pdf') 📄
                                        @elseif(str_contains($document->file_type, 'word')) 📝
                                        @else 📋 @endif
                                    </span>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-sm">{{ $document->title }}</h4>
                                        <p class="text-xs text-gray-500">{{ $document->category }}</p>
                                    </div>
                                </div>
                            </div>
                            @if($document->description)
                                <p class="text-sm text-gray-600 mb-3">{{ \Illuminate\Support\Str::limit($document->description, 60) }}</p>
                            @endif
                            <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                <span>{{ number_format($document->file_size / 1024, 1) }} KB</span>
                                <span>{{ $document->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('temp.access.document.download', [$tempAccess->token, $document->id]) }}"
                                   class="flex-1 text-center py-2 px-3 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                                    Download
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-12">
                            <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <h3 class="mt-4 text-lg font-medium text-gray-900">No Documents</h3>
                            <p class="mt-2 text-gray-500">Upload the first document for this patient</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        @else
        {{-- Placeholder content when not verified --}}
        <div class="text-center py-16">
            <div class="mx-auto h-24 w-24 bg-gradient-to-br from-gray-200 to-gray-300 rounded-3xl flex items-center justify-center mb-6">
                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Verification Required</h3>
            <p class="text-gray-600 mb-6">Please complete the verification process to access patient data</p>
        </div>
        @endif
    </div>

    {{-- Modals (only when verified) --}}
    @if($tempAccess->doctor_verified)
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Upload Document</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="uploadForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label><input type="text" name="title" required class="form-input"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category" required class="form-input">
                        <option value="">Select Category</option>
                        <option>Prescription</option><option>Lab Results</option><option>Medical Report</option><option>Imaging</option><option>Note</option><option>Referral</option><option>Other</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Description</label><textarea name="description" rows="3" class="form-input"></textarea></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File *</label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt" class="form-input">
                    <p class="text-xs text-gray-500 mt-1">Max 10MB • PDF, Word, Images, Text</p>
                </div>
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeUploadModal()" class="flex-1 btn-secondary">Cancel</button>
                    <button type="submit" id="uploadBtn" class="flex-1 btn-primary"><span id="uploadText">Upload</span><div id="uploadSpinner" class="loading-spinner ml-2 hidden"></div></button>
                </div>
            </form>
        </div>
    </div>

    <div id="prescriptionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-lg w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Prescribe Medication</h3>
                <button onclick="closePrescriptionModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="prescriptionForm" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Medication *</label><input type="text" name="name" required class="form-input"></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Generic Name</label><input type="text" name="generic_name" class="form-input"></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Dosage *</label><input type="text" name="dosage" required class="form-input" placeholder="e.g., 500mg"></div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label>
                        <select name="frequency" required class="form-input">
                            <option value="">Select</option><option>Once daily</option><option>Twice daily</option><option>Three times daily</option><option>Four times daily</option><option>As needed</option>
                        </select>
                    </div>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label><input type="date" name="start_date" required class="form-input"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label><input type="text" name="purpose" class="form-input" placeholder="e.g., Blood pressure control"></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label><textarea name="instructions" rows="3" class="form-input" placeholder="Take with food, avoid alcohol, etc."></textarea></div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Refills</label><input type="number" name="refills" min="0" max="12" value="0" class="form-input"></div>
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closePrescriptionModal()" class="flex-1 btn-secondary">Cancel</button>
                    <button type="submit" id="prescribeBtn" class="flex-1 btn-primary"><span id="prescribeText">Prescribe</span><div id="prescribeSpinner" class="loading-spinner ml-2 hidden"></div></button>
                </div>
            </form>
        </div>
    </div>

    <div id="healthTipsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Add Health Tips</h3>
                <button onclick="closeHealthTipsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form id="healthTipsForm" class="space-y-4">
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Title *</label><input type="text" name="title" required class="form-input" placeholder="e.g., Diet Recommendations"></div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" class="form-input">
                        <option value="general">General</option><option value="diet">Diet & Nutrition</option><option value="exercise">Exercise</option>
                        <option value="medication">Medication</option><option value="lifestyle">Lifestyle</option><option value="prevention">Prevention</option>
                    </select>
                </div>
                <div><label class="block text-sm font-medium text-gray-700 mb-1">Content *</label><textarea name="content" rows="6" required class="form-input" placeholder="Enter your health recommendations and tips..."></textarea></div>
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeHealthTipsModal()" class="flex-1 btn-secondary">Cancel</button>
                    <button type="submit" id="healthTipsBtn" class="flex-1 btn-primary"><span id="healthTipsText">Add Tips</span><div id="healthTipsSpinner" class="loading-spinner ml-2 hidden"></div></button>
                </div>
            </form>
        </div>
    </div>

    <div id="vitalDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Vital Signs Details</h3>
                <button onclick="closeVitalDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div id="vitalDetailsContent"></div>
        </div>
    </div>
    @endif

    <script>
        window.jsPDF = window.jspdf.jsPDF;
        const tempAccessToken = '{{ $tempAccess->token }}';
        const isVerified = {{ $tempAccess->doctor_verified ? 'true' : 'false' }};
        let vitalsChart;

        document.addEventListener('DOMContentLoaded', function() {
            @if(!$tempAccess->doctor_verified)
                setupVerificationForm();
            @else
                initializeDashboard();
            @endif
        });

        // Verification Form Setup (matches controller + model normalization)
        function setupVerificationForm() {
            const form = document.getElementById('verificationForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const submitBtn = document.getElementById('verifyBtn');
                const submitText = document.getElementById('submitText');
                const loadingSpinner = document.getElementById('loadingSpinner');
                const codeError = document.getElementById('codeError');
                codeError.classList.add('hidden'); codeError.textContent = '';
                submitBtn.disabled = true; submitText.textContent = 'Verifying...'; loadingSpinner.classList.remove('hidden');

                try {
                    const formData = new FormData(this);
                    let data = Object.fromEntries(formData.entries());

                    // Normalize and validate: exactly 8 alphanumerics after stripping
                    const raw = (data.verification_code || '').toUpperCase();
                    const cleaned = raw.replace(/[^A-Z0-9]/g, '');
                    if (cleaned.length !== 8) {
                        throw new Error('Verification code must be exactly 8 letters or digits (dashes/spaces allowed).');
                    }
                    data.verification_code = raw; // backend also normalizes

                    const response = await fetch(`/temp_access/verify/${tempAccessToken}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    });

                    const result = await response.json();
                    if (response.ok && result.success) {
                        showToast('Verification successful! Reloading dashboard...', 'success');
                        setTimeout(() => window.location.reload(), 1200);
                    } else {
                        throw new Error(result.error || 'Verification failed');
                    }
                } catch (error) {
                    if (error.message.toLowerCase().includes('verification code')) {
                        codeError.textContent = error.message;
                        codeError.classList.remove('hidden');
                    }
                    showToast(error.message || 'Verification failed. Please check your information.', 'error');
                    submitBtn.disabled = false; submitText.textContent = 'Verify & Access Patient Data'; loadingSpinner.classList.add('hidden');
                }
            });

            const phoneEl = document.getElementById('doctor_phone');
            if (phoneEl) {
                phoneEl.addEventListener('input', function(e) {
                    e.target.value = e.target.value.replace(/[^\d+]/g, '');
                });
            }

            const codeEl = document.getElementById('verification_code');
            if (codeEl) {
                codeEl.addEventListener('input', function(e) {
                    let value = e.target.value.toUpperCase().replace(/[^A-Z0-9\-\s]/g, '');
                    e.target.value = value;
                    document.getElementById('codeError')?.classList.add('hidden');
                });
                codeEl.addEventListener('paste', function(e) {
                    e.preventDefault();
                    let paste = (e.clipboardData || window.clipboardData).getData('text');
                    paste = paste.toUpperCase().replace(/[^A-Z0-9]/g, '');
                    if (paste.length === 8) paste = paste.slice(0,4) + '-' + paste.slice(4);
                    this.value = paste.slice(0, 24);
                });
            }
        }

        // Dashboard Initialization
        function initializeDashboard() {
            initializeChart();
            setupModals();
        }

        function initializeChart() {
            const ctx = document.getElementById('vitalsChart'); if (!ctx) return;
            const chartData = @json($chartData);

            vitalsChart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels || [],
                    datasets: [{
                        label: 'Systolic BP',
                        data: chartData.systolic || [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4, fill: true,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3, pointRadius: 6, borderWidth: 3
                    }, {
                        label: 'Diastolic BP',
                        data: chartData.diastolic || [],
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        tension: 0.4, fill: false,
                        pointBackgroundColor: '#dc2626',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3, pointRadius: 5, borderWidth: 2, borderDash: [5,5]
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, padding: 20, font: { size: 12, family: 'Inter', weight: '600' } } },
                        tooltip: { backgroundColor: 'rgba(255,255,255,.95)', titleColor: '#374151', bodyColor: '#6b7280', borderColor: '#e5e7eb', borderWidth: 1, cornerRadius: 12, padding: 12 }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } } },
                        y: { beginAtZero: false, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } } }
                    },
                    animation: { duration: 1000, easing: 'easeInOutQuart' }
                }
            });

            updateChart('blood_pressure'); // initial
        }

        function updateChart(vitalType, ev) {
            if (!vitalsChart) return;
            const buttons = Array.from(document.querySelectorAll('.chart-filter-btn'));
            if (ev && ev.target) {
                buttons.forEach(btn => btn.classList.remove('active'));
                ev.target.classList.add('active');
            }

            const chartData = @json($chartData);
            const datasets = vitalsChart.data.datasets;
            datasets[0].borderDash = []; datasets[0].fill = true;

            switch(vitalType) {
                case 'blood_pressure':
                    datasets[0].hidden = false; datasets[1].hidden = false;
                    datasets[0].label = 'Systolic BP'; datasets[1].label = 'Diastolic BP';
                    datasets[0].data = chartData.systolic || []; datasets[1].data = chartData.diastolic || [];
                    datasets[0].borderColor = '#ef4444'; datasets[0].backgroundColor = 'rgba(239,68,68,.1)';
                    datasets[1].borderColor = '#dc2626'; datasets[1].backgroundColor = 'rgba(220,38,38,.1)'; datasets[1].borderDash = [5,5]; datasets[1].fill = false;
                    break;
                case 'heart_rate':
                    datasets[0].hidden = false; datasets[1].hidden = true;
                    datasets[0].label = 'Heart Rate'; datasets[0].data = chartData.heart_rate || [];
                    datasets[0].borderColor = '#3b82f6'; datasets[0].backgroundColor = 'rgba(59,130,246,.1)';
                    break;
                case 'temperature':
                    datasets[0].hidden = false; datasets[1].hidden = true;
                    datasets[0].label = 'Temperature'; datasets[0].data = chartData.temperature || [];
                    datasets[0].borderColor = '#f59e0b'; datasets[0].backgroundColor = 'rgba(245,158,11,.1)';
                    break;
                case 'oxygen_saturation':
                    datasets[0].hidden = false; datasets[1].hidden = true;
                    datasets[0].label = 'Oxygen Saturation'; datasets[0].data = chartData.oxygen || [];
                    datasets[0].borderColor = '#10b981'; datasets[0].backgroundColor = 'rgba(16,185,129,.1)';
                    break;
            }
            vitalsChart.update('active');
        }

        function setupModals() { setupUploadModal(); setupPrescriptionModal(); setupHealthTipsModal(); }
        function setupUploadModal() {
            const form = document.getElementById('uploadForm'); if (!form) return;
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                await submitForm('upload', `/temp_access/${tempAccessToken}/upload-document`, new FormData(this));
            });
        }
        function setupPrescriptionModal() {
            const form = document.getElementById('prescriptionForm'); if (!form) return;
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(this).entries());
                await submitForm('prescribe', `/temp_access/${tempAccessToken}/prescribe-medication`, data);
            });
            const startDateInput = form.querySelector('input[name="start_date"]');
            if (startDateInput) startDateInput.value = new Date().toISOString().split('T')[0];
        }
        function setupHealthTipsModal() {
            const form = document.getElementById('healthTipsForm'); if (!form) return;
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const data = Object.fromEntries(new FormData(this).entries());
                await submitForm('healthTips', `/temp_access/${tempAccessToken}/add-health-tip`, data);
            });
        }

        async function submitForm(type, url, data) {
            const btn = document.getElementById(`${type}Btn`);
            const text = document.getElementById(`${type}Text`);
            const spinner = document.getElementById(`${type}Spinner`);
            const originalText = text.textContent;
            btn.disabled = true; text.textContent = 'Processing...'; spinner.classList.remove('hidden');

            try {
                const isFormData = data instanceof FormData;
                const options = { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } };
                if (isFormData) { options.body = data; } else { options.headers['Content-Type'] = 'application/json'; options.body = JSON.stringify(data); }
                const response = await fetch(url, options);
                const result = await response.json();
                if (response.ok && result.success) {
                    showToast(result.message || 'Success!', 'success');
                    closeModal(type);
                    if (type === 'upload') setTimeout(() => window.location.reload(), 1200);
                } else {
                    throw new Error(result.error || 'Operation failed');
                }
            } catch (error) {
                showToast(error.message || 'Operation failed', 'error');
            } finally {
                btn.disabled = false; text.textContent = originalText; spinner.classList.add('hidden');
            }
        }

        function openUploadModal() { const m = document.getElementById('uploadModal'); m.classList.remove('hidden'); m.classList.add('flex'); }
        function closeUploadModal() { closeModal('upload'); }
        function openPrescriptionModal() { const m = document.getElementById('prescriptionModal'); m.classList.remove('hidden'); m.classList.add('flex'); }
        function closePrescriptionModal() { closeModal('prescription'); }
        function openHealthTipsModal() { const m = document.getElementById('healthTipsModal'); m.classList.remove('hidden'); m.classList.add('flex'); }
        function closeHealthTipsModal() { closeModal('healthTips'); }
        function closeModal(type) {
            const map = { 'upload':'uploadModal', 'prescription':'prescriptionModal', 'healthTips':'healthTipsModal', 'vitalDetails':'vitalDetailsModal' };
            const modal = document.getElementById(map[type]); if (!modal) return;
            modal.classList.add('hidden'); modal.classList.remove('flex');
            const form = modal.querySelector('form'); if (form) form.reset();
        }

        function showVitalDetails(vital) {
            const modal = document.getElementById('vitalDetailsModal'); const content = document.getElementById('vitalDetailsContent');
            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-semibold text-blue-800 mb-2">Basic Information</h4>
                            <div class="space-y-2">
                                <div class="flex justify-between"><span class="text-sm text-gray-600">Date & Time:</span><span class="text-sm font-medium">${new Date(vital.measured_at).toLocaleString()}</span></div>
                                <div class="flex justify-between"><span class="text-sm text-gray-600">Status:</span>
                                    <span class="status-badge status-${vital.status || 'normal'}"><div class="status-indicator status-${vital.status || 'normal'}"></div>${vital.status ? vital.status.charAt(0).toUpperCase() + vital.status.slice(1) : 'Normal'}</span>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-3">Vital Measurements</h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="bg-gray-50 p-3 rounded-lg"><span class="text-xs text-gray-600">Blood Pressure</span><p class="text-lg font-bold">${vital.systolic_bp || '--'}/${vital.diastolic_bp || '--'} mmHg</p></div>
                                <div class="bg-gray-50 p-3 rounded-lg"><span class="text-xs text-gray-600">Heart Rate</span><p class="text-lg font-bold">${vital.heart_rate || '--'} bpm</p></div>
                                <div class="bg-gray-50 p-3 rounded-lg"><span class="text-xs text-gray-600">Temperature</span><p class="text-lg font-bold">${vital.temperature ? vital.temperature + '°F' : '--'}</p></div>
                                <div class="bg-gray-50 p-3 rounded-lg"><span class="text-xs text-gray-600">Oxygen Saturation</span><p class="text-lg font-bold">${vital.oxygen_saturation ? vital.oxygen_saturation + '%' : '--'}</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="space-y-4">
                        ${vital.notes ? `<div class="bg-white p-4 rounded-lg border border-gray-200"><h4 class="font-semibold text-gray-800 mb-2">Notes</h4><p class="text-sm text-gray-700">${vital.notes}</p></div>` : ''}
                        ${vital.symptoms ? `<div class="bg-white p-4 rounded-lg border border-gray-200"><h4 class="font-semibold text-gray-800 mb-2">Symptoms</h4><p class="text-sm text-gray-700">${vital.symptoms}</p></div>` : ''}
                        ${vital.weight ? `<div class="bg-white p-4 rounded-lg border border-gray-200"><h4 class="font-semibold text-gray-800 mb-2">Additional Measurements</h4><div class="space-y-2"><div class="flex justify-between"><span class="text-sm text-gray-600">Weight:</span><span class="text-sm font-medium">${vital.weight} lbs</span></div>${vital.blood_glucose ? `<div class="flex justify-between"><span class="text-sm text-gray-600">Blood Glucose:</span><span class="text-sm font-medium">${vital.blood_glucose} mg/dL</span></div>` : ''}</div></div>` : ''}
                    </div>
                </div>`;
            modal.classList.remove('hidden'); modal.classList.add('flex');
        }
        function closeVitalDetailsModal() { closeModal('vitalDetails'); }

        function generateReport() {
            try {
                const doc = new jsPDF();
                const patientName = '{{ $patient->user->name ?? "Unknown Patient" }}';
                const doctorName = '{{ $tempAccess->doctor_name ?? "External Doctor" }}';
                doc.setFontSize(20).setFont(undefined, 'bold').text('Patient Medical Report', 14, 20);
                doc.setFontSize(16).setFont(undefined, 'normal').text(`Patient: ${patientName}`, 14, 30);
                doc.setFontSize(12).text(`Prepared by: Dr. ${doctorName}`, 14, 40);
                doc.setFontSize(10).text(`Generated on: ${new Date().toLocaleString()}`, 14, 50);

                let currentY = 65;
                doc.setFontSize(14).setFont(undefined, 'bold').text('Patient Information:', 14, currentY); currentY += 10;
                const patientInfo = [
                    ['Medical Record:', '{{ $patient->medical_record_number ?? "N/A" }}'],
                    ['Blood Type:', '{{ $patient->blood_type ?? "Unknown" }}'],
                    ['Height:', '{{ $patient->height ? $patient->height . " cm" : "N/A" }}'],
                    ['Weight:', '{{ $patient->current_weight ? $patient->current_weight . " kg" : "N/A" }}'],
                    ['Activity Level:', '{{ $patient->activity_level ? ucfirst(str_replace("_", " ", $patient->activity_level)) : "N/A" }}'],
                    ['Smoker:', '{{ $patient->smoker ? "Yes" : "No" }}']
                ];
                doc.autoTable({ body: patientInfo, startY: currentY, styles: { fontSize: 10, cellPadding: 3 }, columnStyles: { 0: { cellWidth: 40, fontStyle: 'bold' }, 1: { cellWidth: 120 } } });
                currentY = doc.lastAutoTable.finalY + 15;

                doc.setFontSize(14).setFont(undefined, 'bold').text('Access Information:', 14, currentY); currentY += 10;
                const accessInfo = [
                    ['Doctor:', 'Dr. {{ $tempAccess->doctor_name ?? "External Doctor" }}'],
                    ['Specialty:', '{{ $tempAccess->doctor_specialty ?? "N/A" }}'],
                    ['Facility:', '{{ $tempAccess->doctor_facility ?? "N/A" }}'],
                    ['Access Date:', '{{ $tempAccess->verified_at ? $tempAccess->verified_at->format("Y-m-d H:i:s") : "N/A" }}'],
                    ['Access Count:', '{{ $tempAccess->access_count }}']
                ];
                doc.autoTable({ body: accessInfo, startY: currentY, styles: { fontSize: 10, cellPadding: 3 }, columnStyles: { 0: { cellWidth: 40, fontStyle: 'bold' }, 1: { cellWidth: 120 } } });

                const filename = `patient_report_${patientName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().slice(0,10)}.pdf`;
                doc.save(filename);
                showToast('Report generated successfully!', 'success');
            } catch (error) { showToast('Error generating report', 'error'); }
        }

        function showToast(message, type = 'info', duration = 4000) {
            document.querySelectorAll('.toast-notification').forEach(t => t.remove());
            const toast = document.createElement('div');
            toast.className = `toast-notification fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm ${
                type === 'success' ? 'bg-green-500 text-white' : type === 'error' ? 'bg-red-500 text-white' : type === 'warning' ? 'bg-yellow-500 text-white' : 'bg-blue-500 text-white'
            }`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${type === 'success'
                            ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                            : type === 'error'
                            ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>'
                            : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' }
                    </div>
                    <div class="ml-3"><p class="text-sm font-medium">${message}</p></div>
                    <div class="ml-4 flex-shrink-0">
                        <button onclick="this.closest('.toast-notification').remove()" class="text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>`;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), duration);
        }
    </script>
</body>
</html>
