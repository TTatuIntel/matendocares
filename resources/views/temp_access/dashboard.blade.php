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
        .medical-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }
        .medical-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(-4px);
        }
        .vital-card {
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            border: 2px solid transparent;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .vital-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
            opacity: 0.7;
        }
        .vital-card:hover {
            border-color: var(--accent-color);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            transform: translateY(-2px);
        }
        .vital-card.bp-card { --accent-color: #ef4444; }
        .vital-card.hr-card { --accent-color: #3b82f6; }
        .vital-card.temp-card { --accent-color: #f59e0b; }
        .vital-card.oxygen-card { --accent-color: #10b981; }
        .vital-card.glucose-card { --accent-color: #8b5cf6; }
        .vital-card.weight-card { --accent-color: #06b6d4; }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 14px 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            color: #475569;
            border: 2px solid #e2e8f0;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 14px 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
            border-color: #cbd5e1;
            transform: translateY(-2px);
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .status-normal {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            color: #166534;
            border: 1px solid #22c55e;
        }
        .status-warning {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
            border: 1px solid #f59e0b;
        }
        .status-critical {
            background: linear-gradient(135deg, #fecaca 0%, #fca5a5 100%);
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }
        .status-indicator.status-normal { background: #22c55e; }
        .status-indicator.status-warning { background: #f59e0b; }
        .status-indicator.status-critical { background: #ef4444; }

        .temp_access-banner {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px 28px;
            border-radius: 20px;
            margin-bottom: 32px;
            box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);
        }
        .chart-filter-btn {
            padding: 12px 20px;
            border-radius: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .chart-filter-btn.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.4);
        }
        .chart-filter-btn:not(.active) {
            background: #f8fafc;
            color: #64748b;
            border-color: #e2e8f0;
        }
        .chart-filter-btn:not(.active):hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }

        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-radius: 50%;
            border-top: 3px solid #3b82f6;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .blur-content {
            filter: blur(4px);
            pointer-events: none;
            user-select: none;
        }
        .verification-overlay {
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(12px);
        }
        .form-input {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px 18px;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 14px;
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            outline: none;
        }
        .critical-alert {
            background: linear-gradient(135deg, #fecaca 0%, #ef4444 100%);
            color: white;
            animation: flashAlert 3s ease-in-out;
        }
        @keyframes flashAlert {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .action-btn {
            transition: all 0.3s ease;
            border-radius: 16px;
            font-weight: 600;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        .info-item:last-child {
            border-bottom: none;
        }
        .info-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1f2937;
        }
        .vital-icon {
            width: 60px;
            height: 60px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
        }
        .chart-container {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            border-radius: 24px;
            padding: 24px;
            border: 1px solid #e2e8f0;
        }
        @keyframes shake {
            0%, 20%, 40%, 60%, 80%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        }
        .shake { animation: shake 0.6s ease-in-out; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
    <!-- Ultra Compact Verification Modal -->
    @if(!$tempAccess->doctor_verified)
    <div id="verificationModal" class="fixed inset-0 verification-overlay z-50 flex items-center justify-center p-2">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-2" style="max-height: 90vh;">
            <div class="p-4">
                <!-- Minimal Header -->
                <div class="text-center mb-3">
                    <div class="mx-auto h-10 w-10 bg-blue-600 rounded-xl flex items-center justify-center mb-2">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                        </svg>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">Doctor Verification</h2>
                    <p class="text-xs text-gray-600">Access {{ Str::limit($patient->user->name ?? 'patient', 15) }}'s data</p>
                </div>

                <!-- Ultra Compact Form -->
                <form id="verificationForm" class="space-y-2">
                    <input id="doctor_name" name="doctor_name" type="text" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           placeholder="Your full name">

                    <input id="doctor_phone" name="doctor_phone" type="tel" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           placeholder="Phone number">

                    <select id="doctor_specialty" name="doctor_specialty" required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <option value="">Medical specialty</option>
                        <option value="General Practice">General Practice</option>
                        <option value="Internal Medicine">Internal Medicine</option>
                        <option value="Emergency Medicine">Emergency Medicine</option>
                        <option value="Cardiology">Cardiology</option>
                        <option value="Pediatrics">Pediatrics</option>
                        <option value="Other">Other</option>
                    </select>

                    <input id="doctor_facility" name="doctor_facility" type="text" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                           placeholder="Hospital/Clinic name">

                    <input id="verification_code" name="verification_code" type="text" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-center font-mono tracking-wider"
                           placeholder="XXXX-XXXX" maxlength="9">

                    <button type="submit" id="verifyBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg transition-colors mt-3">
                        <span id="submitText">Verify Access</span>
                        <div id="loadingSpinner" class="loading-spinner ml-2 hidden"></div>
                    </button>
                </form>

                <p class="text-xs text-gray-500 text-center mt-2">Expires {{ $tempAccess->expires_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 {{ !$tempAccess->doctor_verified ? 'blur-content' : '' }}">

        <!-- Enhanced Access Banner -->
        <div class="temp_access-banner mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2l-4.686-4.686a6 6 0 017.743-7.743L15 7z"></path>
                    </svg>
                    <div>
                        <h3 class="font-bold text-xl">
                            @if($tempAccess->doctor_verified)
                                Dr. {{ $tempAccess->doctor_name }}
                            @else
                                Verification Required
                            @endif
                        </h3>
                        <div class="text-sm opacity-90">
                            @if($tempAccess->doctor_verified)
                                {{ $tempAccess->doctor_specialty }} • {{ $tempAccess->doctor_facility }}
                            @else
                                Complete verification to access data
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs opacity-90">Access Expires</div>
                    <div class="font-bold">{{ $tempAccess->time_remaining }}</div>
                    <div class="text-xs opacity-75">Views: {{ $tempAccess->access_count }}</div>
                </div>
            </div>
        </div>

        @if($tempAccess->doctor_verified)
        <!-- Enhanced Patient Header -->
        <div class="medical-card p-8 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-3xl flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-2xl">{{ strtoupper(substr($patient->user->name ?? 'P', 0, 1)) }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $patient->user->name ?? 'Unknown Patient' }}</h1>
                        <div class="flex items-center space-x-4 text-sm text-gray-600 mb-2">
                            <span>📋 {{ $patient->medical_record_number ?? 'No MRN' }}</span>
                            <span>🩸 {{ $patient->blood_type ?? 'Unknown' }}</span>
                            <span>📧 {{ $patient->user->email ?? 'N/A' }}</span>
                            @if($patient->user->phone)
                            <span>📞 {{ $patient->user->phone }}</span>
                            @endif
                        </div>
                        @if($latestVitals)
                        <div class="flex items-center space-x-2">
                            <span class="status-badge status-{{ $latestVitals->status ?? 'normal' }}">
                                <div class="status-indicator status-{{ $latestVitals->status ?? 'normal' }}"></div>
                                {{ ucfirst($latestVitals->status ?? 'normal') }}
                            </span>
                            <span class="text-xs text-gray-500">Last vitals: {{ $latestVitals->measured_at->diffForHumans() }}</span>
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
                    <button onclick="refreshData()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Enhanced Vitals Cards Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-6 gap-8 mb-8">
            <div class="xl:col-span-6 grid grid-cols-2 lg:grid-cols-6 gap-6">
                <!-- Blood Pressure Card -->
                <div class="vital-card bp-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="bpCard">
                    <div class="vital-icon bg-gradient-to-br from-red-500 to-pink-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2" id="currentBP">
                        {{ $latestVitals ? ($latestVitals->systolic_bp . '/' . $latestVitals->diastolic_bp) : '--/--' }}
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Blood Pressure</div>
                    <div class="text-xs text-gray-400 mt-1">mmHg</div>
                    <div class="text-xs text-gray-500 mt-2" id="bpTime">
                        {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                    </div>
                </div>

                <!-- Heart Rate Card -->
                <div class="vital-card hr-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="hrCard">
                    <div class="vital-icon bg-gradient-to-br from-blue-500 to-indigo-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2" id="currentHR">
                        {{ $latestVitals->heart_rate ?? '--' }}
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Heart Rate</div>
                    <div class="text-xs text-gray-400 mt-1">bpm</div>
                    <div class="text-xs text-gray-500 mt-2" id="hrTime">
                        {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                    </div>
                </div>

                <!-- Temperature Card -->
                <div class="vital-card temp-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="tempCard">
                    <div class="vital-icon bg-gradient-to-br from-orange-500 to-red-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l3-3 3 3v13M9 19h6M9 19H7a2 2 0 01-2-2V9a2 2 0 012-2h2M15 19h2a2 2 0 002-2V9a2 2 0 00-2-2h-2"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2" id="currentTemp">
                        {{ $latestVitals->temperature ?? '--' }}°F
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Temperature</div>
                    <div class="text-xs text-gray-400 mt-1">Fahrenheit</div>
                    <div class="text-xs text-gray-500 mt-2" id="tempTime">
                        {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                    </div>
                </div>

                <!-- Oxygen Saturation Card -->
                <div class="vital-card oxygen-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="oxygenCard">
                    <div class="vital-icon bg-gradient-to-br from-green-500 to-emerald-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2" id="currentO2">
                        {{ $latestVitals->oxygen_saturation ?? '--' }}%
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Oxygen Saturation</div>
                    <div class="text-xs text-gray-400 mt-1">SpO2</div>
                    <div class="text-xs text-gray-500 mt-2" id="o2Time">
                        {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                    </div>
                </div>

                <!-- Blood Glucose Card -->
                <div class="vital-card glucose-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="glucoseCard">
                    <div class="vital-icon bg-gradient-to-br from-purple-500 to-indigo-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2" id="currentGlucose">
                        {{ $latestVitals && $latestVitals->blood_glucose !== null ? number_format($latestVitals->blood_glucose, 2) : '--' }} mg/dL
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Blood Glucose</div>
                    <div class="text-xs text-gray-400 mt-1">mg/dL</div>
                    <div class="text-xs text-gray-500 mt-2" id="glucoseTime">
                        {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                    </div>
                </div>

                <!-- Weight Card -->
                <div class="vital-card weight-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="weightCard">
                    <div class="vital-icon bg-gradient-to-br from-cyan-500 to-blue-600">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2" id="currentWeight">
                        {{ $latestVitals && $latestVitals->weight !== null ? number_format($latestVitals->weight, 2) : '--' }} kg
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Weight</div>
                    <div class="text-xs text-gray-400 mt-1">Kilograms</div>
                    <div class="text-xs text-gray-500 mt-2" id="weightTime">
                        {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Charts and Trends -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Enhanced Vital Signs Chart -->
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Vital Signs Trends</h3>
                                <p class="text-gray-600">Real-time monitoring and historical data</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="status-indicator status-normal"></div>
                                <span class="text-sm text-gray-500">Live Updates</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <!-- Enhanced Chart Filter Buttons -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <button onclick="updateChart('blood_pressure')"
                                    class="chart-filter-btn active bg-gradient-to-r from-red-100 to-red-200 text-red-700 rounded-xl px-4 py-2 text-sm font-semibold hover:from-red-200 hover:to-red-300 transition-all duration-200">
                                Blood Pressure
                            </button>
                            <button onclick="updateChart('heart_rate')"
                                    class="chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition-all duration-200">
                                Heart Rate
                            </button>
                            <button onclick="updateChart('temperature')"
                                    class="chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition-all duration-200">
                                Temperature
                            </button>
                            <button onclick="updateChart('oxygen_saturation')"
                                    class="chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition-all duration-200">
                                Oxygen Saturation
                            </button>
                            <button onclick="updateChart('blood_glucose')"
                                    class="chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition-all duration-200">
                                Blood Glucose
                            </button>
                        </div>

                        <!-- Enhanced Chart Container -->
                        <div class="relative h-96 bg-gradient-to-br from-gray-50 to-white rounded-3xl p-6 border border-gray-100">
                            <canvas id="vitalsChart"></canvas>
                        </div>

                        <!-- Enhanced Chart Insights -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-2xl border border-blue-200">
                                <div class="text-sm font-semibold text-blue-800 mb-1">24h Average</div>
                                <div class="text-xl font-bold text-blue-900" id="averageValue">--</div>
                            </div>
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-2xl border border-green-200">
                                <div class="text-sm font-semibold text-green-800 mb-1">Trend</div>
                                <div class="text-xl font-bold text-green-900" id="trendDirection">--</div>
                            </div>
                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-2xl border border-purple-200">
                                <div class="text-sm font-semibold text-purple-800 mb-1">Status</div>
                                <div class="text-xl font-bold text-purple-900" id="vitalStatus">--</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Recent Activity Timeline -->
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Recent Vitals History</h3>
                        <p class="text-gray-600">Latest measurements and readings</p>
                    </div>

                    <div class="p-8">
                        <div class="space-y-6" id="vitalsTimeline">
                            @forelse($recentVitals->take(8) as $vital)
                            <div class="flex items-start space-x-4 p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-200 hover:shadow-lg transition-all duration-200 cursor-pointer" onclick="showVitalDetails({{ json_encode($vital) }})">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center {{ $vital->status === 'critical' ? 'bg-red-100 text-red-600' : ($vital->status === 'warning' ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }}">
                                        @if($vital->status === 'critical')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        @elseif($vital->status === 'warning')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-900 mb-1">{{ $vital->measured_at->format('M d, Y g:i A') }}</p>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs text-gray-600 mb-2">
                                        <span>BP: {{ $vital->systolic_bp ?? '--' }}/{{ $vital->diastolic_bp ?? '--' }}</span>
                                        <span>HR: {{ $vital->heart_rate ?? '--' }}bpm</span>
                                        <span>Temp: {{ $vital->temperature ? $vital->temperature . '°F' : '--' }}</span>
                                        <span>O2: {{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '--' }}</span>
                                    </div>
                                    @if($vital->blood_glucose)
                                    <div class="text-xs text-purple-600">
                                        <span>Glucose: {{ $vital->blood_glucose }} mg/dL</span>
                                    </div>
                                    @endif
                                    <div class="flex items-center justify-between mt-2">
                                        <p class="text-xs text-gray-500">{{ $vital->measured_at->diffForHumans() }}</p>
                                        <span class="status-badge status-{{ $vital->status ?? 'normal' }} text-xs px-2 py-1">
                                            {{ ucfirst($vital->status ?? 'normal') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12">
                                <div class="text-6xl mb-4">📊</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">No Vitals History</h3>
                                <p class="text-gray-600">Patient vitals will appear here once recorded</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Sidebar -->
            <div class="space-y-6">
                <!-- Enhanced Quick Actions -->
                <div class="medical-card p-6 bg-white rounded-lg shadow-md">
                    <div class="mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">⚡ Quick Actions</h3>
                        <p class="text-sm text-gray-500">Healthcare Management Tools</p>
                    </div>

                    <div class="grid grid-cols-1 gap-3">
                        <button onclick="openUploadModal()" class="action-btn bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 text-blue-700 hover:from-blue-100 hover:to-blue-200 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            Upload Document
                        </button>

                        <button onclick="openPrescriptionModal()" class="action-btn bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 hover:from-green-100 hover:to-emerald-100 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                            Prescribe Medication
                        </button>

                        <button onclick="openHealthTipsModal()" class="action-btn bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 text-purple-700 hover:from-purple-100 hover:to-purple-200 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Add Health Tips
                        </button>

                        <button onclick="exportVitals()" class="action-btn bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 text-orange-700 hover:from-orange-100 hover:to-red-100 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Vitals
                        </button>

                        <button onclick="refreshData()" class="action-btn bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 text-gray-700 hover:from-gray-100 hover:to-gray-200 transition-all duration-200">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Refresh Data
                        </button>
                    </div>
                </div>

                <!-- Enhanced Patient Information -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">👤 Patient Information</h3>
                        <p class="text-sm text-gray-600">Medical details and demographics</p>
                    </div>

                    <div class="space-y-4">
                        <div class="info-item">
                            <span class="info-label">Medical Record:</span>
                            <span class="info-value">{{ $patient->medical_record_number ?? 'Not assigned' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Blood Type:</span>
                            <span class="info-value text-red-600 font-semibold">{{ $patient->blood_type ?? 'Unknown' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age:</span>
                            <span class="info-value">{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : 'Unknown' }} years</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender:</span>
                            <span class="info-value">{{ ucfirst($patient->gender ?? 'Not specified') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Height:</span>
                            <span class="info-value">{{ $patient->height ? $patient->height . ' cm' : 'Not recorded' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Activity Level:</span>
                            <span class="info-value">{{ ucfirst(str_replace('_', ' ', $patient->activity_level ?? 'unknown')) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Smoker:</span>
                            <span class="info-value {{ $patient->smoker ? 'text-red-600' : 'text-green-600' }}">{{ $patient->smoker ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Medical History -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">🏥 Medical History</h3>
                        <p class="text-sm text-gray-600">Conditions and health background</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800 text-sm mb-2">Chronic Conditions</h4>
                            @if($patient->chronic_conditions)
                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $patient->chronic_conditions }}</p>
                            @else
                            <p class="text-sm text-gray-500 italic">None recorded</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-800 text-sm mb-2">Allergies</h4>
                            @if($patient->allergies)
                            <p class="text-sm text-red-700 bg-red-50 p-3 rounded-lg border border-red-200">{{ $patient->allergies }}</p>
                            @else
                            <p class="text-sm text-gray-500 italic">None recorded</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-800 text-sm mb-2">Current Medications</h4>
                            @if($patient->current_medications)
                            <p class="text-sm text-gray-700 bg-blue-50 p-3 rounded-lg border border-blue-200">{{ $patient->current_medications }}</p>
                            @else
                            <p class="text-sm text-gray-500 italic">None recorded</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Enhanced Access Information -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">🔐 Access Details</h3>
                        <p class="text-sm text-gray-600">Temporary access information</p>
                    </div>

                    <div class="space-y-3">
                        <div class="info-item">
                            <span class="info-label">Doctor:</span>
                            <span class="info-value">Dr. {{ $tempAccess->doctor_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Specialty:</span>
                            <span class="info-value">{{ $tempAccess->doctor_specialty }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Facility:</span>
                            <span class="info-value">{{ $tempAccess->doctor_facility }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Generated:</span>
                            <span class="info-value">{{ $tempAccess->created_at->format('M d, Y g:i A') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Expires:</span>
                            <span class="info-value text-orange-600">{{ $tempAccess->expires_at->format('M d, Y g:i A') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Access Count:</span>
                            <span class="info-value">{{ $tempAccess->access_count }} times</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Status:</span>
                            <span class="info-value text-green-600 font-semibold">Verified ✓</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Documents Section -->
        <div class="medical-card mt-8">
            <div class="p-8 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-6 lg:mb-0">
                        <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent mb-2">
                            📄 Patient Documents
                        </h2>
                        <p class="text-gray-600">Medical records, reports, and healthcare documents</p>
                    </div>
                    <button onclick="openUploadModal()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload Document
                    </button>
                </div>
            </div>

            <!-- Enhanced Documents Grid -->
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse($documents as $document)
                    <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center">
                                <span class="text-3xl mr-3">
                                    @if(str_contains($document->file_type, 'image'))
                                        🖼️
                                    @elseif($document->file_type === 'application/pdf')
                                        📄
                                    @elseif(str_contains($document->file_type, 'word'))
                                        📝
                                    @else
                                        📋
                                    @endif
                                </span>
                                <div>
                                    <h3 class="font-bold text-gray-900 text-sm mb-1 line-clamp-2">{{ $document->title }}</h3>
                                    <p class="text-xs text-gray-500 font-medium">{{ $document->category }}</p>
                                </div>
                            </div>
                            @if($document->is_confidential ?? false)
                                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-semibold">🔒 Confidential</span>
                            @endif
                        </div>

                        @if($document->description)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $document->description }}</p>
                        @endif

                        <div class="flex items-center justify-between text-xs text-gray-500 mb-4 font-medium">
                            <span>{{ number_format($document->file_size / 1024, 1) }} KB</span>
                            <span>{{ $document->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="flex space-x-2">
                            <a href="{{ route('temp.access.document.download', [$tempAccess->token, $document->id]) }}"
                               class="flex-1 btn-primary text-center py-2.5 rounded-xl text-sm font-semibold">
                               👁️ View
                            </a>
                            <a href="{{ route('temp.access.document.download', [$tempAccess->token, $document->id]) }}"
                               class="flex-1 btn-secondary text-center py-2.5 rounded-xl text-sm font-semibold">
                                ⬇️ Download
                            </a>
                        </div>
                    </div>
                    @empty
                    <!-- Enhanced Empty State -->
                    <div class="col-span-full">
                        <div class="text-center py-16">
                            <div class="text-8xl mb-6">📄</div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">No Documents Found</h3>
                            <p class="text-gray-600 mb-8 text-lg">Upload the first document to start building the medical record.</p>
                            <button onclick="openUploadModal()" class="btn-primary px-8 py-4 text-lg font-semibold">
                                <svg class="w-6 h-6 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                Upload First Document
                            </button>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>


<!-- 🧪 Prescribed Medications Section -->
<div class="p-8 border-t border-gray-200 mt-12">
    <h2 class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
        💊 Prescribed Medications
    </h2>
    <p class="text-gray-600 mb-6">Doctor-prescribed treatments and medication plans</p>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($medications as $med)
        <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300">
            <div class="mb-4">
                <h3 class="font-bold text-gray-900 text-sm mb-1 line-clamp-2">{{ $med->name }}</h3>
                <p class="text-xs text-gray-500 font-medium">{{ $med->generic_name ?? '—' }}</p>
            </div>

            <ul class="text-sm text-gray-600 mb-4 space-y-1">
                <li><strong>Dosage:</strong> {{ $med->dosage ?? '—' }}</li>
                <li><strong>Frequency:</strong> {{ $med->frequency ?? '—' }}</li>
                <li><strong>Start Date:</strong> {{ \Carbon\Carbon::parse($med->start_date)->format('M d, Y') }}</li>
                <li><strong>Refills:</strong> {{ $med->refills }}</li>
                <li><strong>Purpose:</strong> {{ $med->purpose ?? '—' }}</li>
            </ul>

            @if($med->instructions)
                <p class="text-xs text-gray-500 italic mb-2">📝 {{ $med->instructions }}</p>
            @endif

            @if($med->health_tips)
                <p class="text-xs text-green-600 italic">💡 {{ $med->health_tips }}</p>
            @endif
        </div>
        @empty
        <div class="col-span-full text-center py-12">
            <div class="text-6xl mb-4">💊</div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No Medications Found</h3>
            <p class="text-gray-600">No prescriptions have been recorded for this patient yet.</p>
        </div>
        @endforelse
    </div>
</div>
<!-- Doctor's Prescriptions Section -->
<div class="medical-card mt-8">
    <div class="p-8 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Doctor's Prescribed Medications</h3>
                <p class="text-gray-600">Medications prescribed by your healthcare providers</p>
            </div>
            <button onclick="openDoctorsMedsModal()" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                View All
            </button>
        </div>
    </div>

    <div class="p-8">
        @forelse($doctorMeds->take(2) as $med)
        <div class="medication-detail-card mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                    <div class="medication-avatar bg-gradient-to-br from-blue-100 to-indigo-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $med->name }}</h4>
                        <p class="text-sm text-gray-600 mb-2">Prescribed by {{ $med->doctor->name ?? 'Doctor' }}</p>
                        <div class="flex space-x-2">
                            <span class="med-detail-badge">{{ $med->dosage }}</span>
                            <span class="med-detail-badge">{{ $med->frequency }}</span>
                            <span class="status-badge {{ $med->status === 'active' ? 'active' : 'inactive' }}">
                                {{ ucfirst($med->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <button onclick="addToMyMeds('{{ $med->id }}')" class="btn-primary px-4 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add to My Meds
                </button>
            </div>

            @if($med->health_tips)
            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                <p class="text-sm font-medium text-blue-800">💡 Doctor's Health Tips:</p>
                <p class="text-sm text-blue-700 mt-1">{{ $med->health_tips }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No doctor prescriptions</h3>
            <p class="mt-1 text-gray-500">Your doctor's prescribed medications will appear here</p>
        </div>
        @endforelse
    </div>
</div>
<!-- Doctor's Prescriptions Section -->
<div class="medical-card mt-8">
    <div class="p-8 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Doctor's Prescribed Medications</h3>
                <p class="text-gray-600">Medications prescribed by your healthcare providers</p>
            </div>
            <button onclick="openDoctorsMedsModal()" class="btn-secondary">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                View All
            </button>
        </div>
    </div>

    <div class="p-8">
        @forelse($doctorMeds->take(2) as $med)
        <div class="medication-detail-card mb-6">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                    <div class="medication-avatar bg-gradient-to-br from-blue-100 to-indigo-100">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $med->name }}</h4>
                        <p class="text-sm text-gray-600 mb-2">Prescribed by {{ $med->prescribed_by ?? 'Doctor' }}</p>
                        <div class="flex space-x-2">
                            <span class="med-detail-badge">{{ $med->dosage }}</span>
                            <span class="med-detail-badge">{{ $med->frequency }}</span>
                            <span class="status-badge {{ $med->status === 'active' ? 'active' : 'inactive' }}">
                                {{ ucfirst($med->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <button onclick="addToMyMeds('{{ $med->id }}')" class="btn-primary px-4 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add to My Meds
                </button>
            </div>

            @if($med->health_tips)
            <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                <p class="text-sm font-medium text-blue-800">💡 Doctor's Health Tips:</p>
                <p class="text-sm text-blue-700 mt-1">{{ $med->health_tips }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No doctor prescriptions</h3>
            <p class="mt-1 text-gray-500">Your doctor's prescribed medications will appear here</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Doctor's Medications Modal -->
<div id="doctorsMedsModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[90vh] my-8 flex flex-col shadow-xl">

            <!-- Header (sticky) -->
            <div class="p-8 border-b border-gray-200 flex items-center justify-between sticky top-0 bg-white z-10 rounded-t-3xl">
                <h3 class="text-2xl font-bold text-gray-900">All Doctor's Prescriptions</h3>
                <button onclick="closeDoctorsMedsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content (scrollable) -->
            <div class="p-8 overflow-y-auto flex-1">
                @forelse($doctorMeds as $med)
                <div class="medication-detail-card mb-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-start space-x-4">
                            <div class="medication-avatar bg-gradient-to-br from-blue-100 to-indigo-100">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">{{ $med->name }}</h4>
                                <p class="text-sm text-gray-600">
                                    Prescribed by {{ $med->prescribed_by ?? 'Doctor' }}
                                    on {{ $med->created_at->format('m/d/Y') }}
                                </p>
                            </div>
                        </div>
                        <button onclick="addToMyMeds('{{ $med->id }}')" class="btn-primary px-4 py-2">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add to My Meds
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <p class="text-sm text-gray-600"><span class="font-medium">Dosage:</span> {{ $med->dosage }}</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Frequency:</span> {{ $med->frequency }}</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Schedule:</span> {{ $med->times ?? 'Not specified' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600"><span class="font-medium">Start Date:</span> {{ $med->start_date->format('M d, Y') }}</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Purpose:</span> {{ $med->purpose ?? 'Not specified' }}</p>
                            <p class="text-sm text-gray-600"><span class="font-medium">Status:</span>
                                <span class="status-badge {{ $med->status === 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($med->status) }}
                                </span>
                            </p>
                        </div>
                    </div>

                    @if($med->health_tips)
                    <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-sm font-medium text-blue-800">💡 Doctor's Health Tips:</p>
                        <p class="text-sm text-blue-700 mt-1">{{ $med->health_tips }}</p>
                    </div>
                    @endif

                    @if($med->instructions)
                    <div class="mt-3">
                        <p class="text-sm font-medium text-gray-700">Special Instructions:</p>
                        <p class="text-sm text-gray-600 mt-1">{{ $med->instructions }}</p>
                    </div>
                    @endif
                </div>
                @empty
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No doctor prescriptions</h3>
                    <p class="mt-1 text-gray-500">Your doctor's prescribed medications will appear here</p>
                </div>
                @endforelse
            </div>

            <!-- Footer (sticky) -->
            <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-3xl sticky bottom-0">
                <div class="flex justify-end">
                    <button onclick="closeDoctorsMedsModal()" class="btn-secondary px-8">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Script -->
<script>
    function openDoctorsMedsModal() {
        const modal = document.getElementById('doctorsMedsModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Prevent background scroll
    }

    function closeDoctorsMedsModal() {
        const modal = document.getElementById('doctorsMedsModal');
        modal.classList.add('hidden');
        document.body.style.overflow = ''; // Restore scroll
    }

    // Close modal when clicking outside
    document.getElementById('doctorsMedsModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDoctorsMedsModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDoctorsMedsModal();
        }
    });
</script>



        @else
        <!-- Enhanced Placeholder when not verified -->
        <div class="text-center py-20">
            <div class="mx-auto h-32 w-32 bg-gradient-to-br from-gray-200 to-gray-300 rounded-3xl flex items-center justify-center mb-8">
                <svg class="h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-4">Verification Required</h3>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                Please complete the verification process above to securely access <strong>{{ $patient->user->name ?? 'patient' }}'s</strong> comprehensive medical data
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 max-w-lg mx-auto">
                <h4 class="font-semibold text-blue-900 mb-2">Security Notice</h4>
                <p class="text-blue-800 text-sm">
                    This temporary access link provides secure, time-limited access to patient medical data.
                    All access attempts are logged and monitored for HIPAA compliance.
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Enhanced Modals (only show when verified) -->
    @if($tempAccess->doctor_verified)

    <!-- Enhanced Document Upload Modal -->
    <div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-lg w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Upload Medical Document</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="uploadForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Document Title *</label>
                    <input type="text" name="title" required class="form-input" placeholder="e.g., Lab Results - CBC Panel">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="category" required class="form-input">
                        <option value="">Select Category</option>
                        <option value="Lab Results">Lab Results</option>
                        <option value="Imaging">Imaging (X-ray, MRI, CT)</option>
                        <option value="prescription">Prescription</option>
                        <option value="Medical Report">Medical Report</option>
                        <option value="Consultation Note">Consultation Note</option>
                        <option value="Discharge Summary">Discharge Summary</option>
                        <option value="Referral">Referral Letter</option>
                        <option value="Insurance">Insurance Document</option>
                        <option value="Consent Form">Consent Form</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="form-input" placeholder="Brief description of the document contents and findings..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tags (Optional)</label>
                    <input type="text" name="tags" class="form-input" placeholder="e.g., urgent, follow-up, diabetes, cardiology">
                    <p class="text-xs text-gray-500 mt-1">Separate tags with commas for easy searching</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">File *</label>
                    <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt,.xlsx,.xls" class="form-input">
                    <p class="text-xs text-gray-500 mt-1">Max 10MB • PDF, Word, Images, Excel, Text files supported</p>
                </div>
                <div class="flex items-center">
                    <input type="checkbox" name="is_confidential" id="is_confidential" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                    <label for="is_confidential" class="ml-2 text-sm text-gray-700">Mark as confidential document</label>
                </div>
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeUploadModal()" class="flex-1 btn-secondary">Cancel</button>
                    <button type="submit" id="uploadBtn" class="flex-1 btn-primary">
                        <span id="uploadText">Upload Document</span>
                        <div id="uploadSpinner" class="loading-spinner ml-2 hidden"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Prescription Modal -->
    <div id="prescriptionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Prescribe Medication</h3>
                <button onclick="closePrescriptionModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="prescriptionForm" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Medication Name *</label>
                        <input type="text" name="name" required class="form-input" placeholder="e.g., Lisinopril">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Generic Name</label>
                        <input type="text" name="generic_name" class="form-input" placeholder="e.g., ACE Inhibitor">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosage *</label>
                        <input type="text" name="dosage" required class="form-input" placeholder="e.g., 10mg, 5ml, 2 tablets">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Frequency *</label>
                        <select name="frequency" required class="form-input">
                            <option value="">Select Frequency</option>
                            <option value="Once daily">Once daily (QD)</option>
                            <option value="Twice daily">Twice daily (BID)</option>
                            <option value="Three times daily">Three times daily (TID)</option>
                            <option value="Four times daily">Four times daily (QID)</option>
                            <option value="Every 4 hours">Every 4 hours</option>
                            <option value="Every 6 hours">Every 6 hours</option>
                            <option value="Every 8 hours">Every 8 hours</option>
                            <option value="Every 12 hours">Every 12 hours</option>
                            <option value="As needed">As needed (PRN)</option>
                            <option value="Weekly">Weekly</option>
                            <option value="Monthly">Monthly</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date *</label>
                        <input type="date" name="start_date" required class="form-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration</label>
                        <select name="duration" class="form-input">
                            <option value="">Select Duration</option>
                            <option value="7 days">7 days</option>
                            <option value="14 days">14 days</option>
                            <option value="30 days">30 days</option>
                            <option value="60 days">60 days</option>
                            <option value="90 days">90 days</option>
                            <option value="6 months">6 months</option>
                            <option value="Ongoing">Ongoing</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose/Indication *</label>
                    <input type="text" name="purpose" required class="form-input" placeholder="e.g., Blood pressure control, Pain management, Infection treatment">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instructions *</label>
                    <textarea name="instructions" rows="3" required class="form-input" placeholder="Detailed instructions: Take with food, avoid alcohol, monitor blood pressure, watch for side effects, etc."></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Refills Authorized</label>
                        <select name="refills" class="form-input">
                            <option value="0">No refills</option>
                            <option value="1">1 refill</option>
                            <option value="2">2 refills</option>
                            <option value="3">3 refills</option>
                            <option value="6">6 refills</option>
                            <option value="12">12 refills</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="form-input">
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="hold">On Hold</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="form-input" placeholder="Additional notes, warnings, or special considerations..."></textarea>
                </div>
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closePrescriptionModal()" class="flex-1 btn-secondary">Cancel</button>
                    <button type="submit" id="prescribeBtn" class="flex-1 btn-primary">
                        <span id="prescribeText">Prescribe Medication</span>
                        <div id="prescribeSpinner" class="loading-spinner ml-2 hidden"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Health Tips Modal -->
    <div id="healthTipsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Add Health Tips & Recommendations</h3>
                <button onclick="closeHealthTipsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="healthTipsForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
                    <input type="text" name="title" required class="form-input" placeholder="e.g., Dietary Recommendations for Diabetes Management">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                    <select name="category" required>
                    <option value="lab_report">Lab Report</option>
                    <option value="imaging">Imaging</option>
                    <option value="prescription">Prescription</option>
                    <option value="insurance">Insurance</option>
                    <option value="consultation_note">Consultation Note</option>
                    <option value="discharge_summary">Discharge Summary</option>
                    <option value="referral">Referral</option>
                    <option value="consent_form">Consent Form</option>
                    <option value="other">Other</option>
                </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority Level</label>
                    <select name="priority" class="form-input">
                        <option value="low">Low - General guidance</option>
                        <option value="medium">Medium - Important recommendations</option>
                        <option value="high">High - Critical health advice</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Health Tips & Recommendations *</label>
                    <textarea name="content" rows="8" required class="form-input" placeholder="Enter detailed health recommendations, tips, and advice for the patient. Include specific actions, timelines, and any warnings or precautions..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Follow-up Required</label>
                    <select name="follow_up_required" class="form-input">
                        <option value="no">No follow-up needed</option>
                        <option value="yes">Follow-up recommended</option>
                        <option value="urgent">Urgent follow-up required</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Implementation Timeline</label>
                    <input type="text" name="timeline" class="form-input" placeholder="e.g., Start immediately, Within 1 week, Gradual implementation over 30 days">
                </div>
                <div class="flex space-x-3 pt-4">
                    <button type="button" onclick="closeHealthTipsModal()" class="flex-1 btn-secondary">Cancel</button>
                    <button type="submit" id="healthTipsBtn" class="flex-1 btn-primary">
                        <span id="healthTipsText">Add Health Tips</span>
                        <div id="healthTipsSpinner" class="loading-spinner ml-2 hidden"></div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Enhanced Vital Details Modal -->
    <div id="vitalDetailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-40">
        <div class="bg-white rounded-2xl p-8 max-w-4xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">Comprehensive Vital Signs Analysis</h3>
                <button onclick="closeVitalDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="vitalDetailsContent">
                <!-- Content populated by JavaScript -->
            </div>
        </div>
    </div>

    @endif

    <script>



        window.jsPDF = window.jspdf.jsPDF;
        const tempAccessToken = '{{ $tempAccess->token }}';
        const isVerified = {{ $tempAccess->doctor_verified ? 'true' : 'false' }};
        const patientData = {
            patient_id: "{{ $patient->id }}",
            patient_name: "{{ $patient->user->name ?? 'Unknown Patient' }}",
            patient_email: "{{ $patient->user->email ?? 'N/A' }}",
            patient_phone: "{{ $patient->user->phone ?? 'N/A' }}",
            medical_record_number: "{{ $patient->medical_record_number ?? 'N/A' }}",
            blood_type: "{{ $patient->blood_type ?? 'Unknown' }}",
            realTimeEnabled: true
        };
        let vitalsChart;

        // Initialize chart data safely
        const defaultChartData = {
            labels: [],
            systolic: [],
            diastolic: [],
            heart_rate: [],
            temperature: [],
            oxygen: [],
            glucose: []
        };

        @php
        $chartDataJson = json_encode($chartData ?? $defaultChartData);
        @endphp

        const chartData = {!! $chartDataJson !!};

        document.addEventListener('DOMContentLoaded', function() {
            setupModals();
            @if(!$tempAccess->doctor_verified)
                setupVerificationForm();
            @else
                initializeDashboard();
            @endif
        });

        // Enhanced Verification Form Setup with smooth transitions
        function setupVerificationForm() {
            const form = document.getElementById('verificationForm');
            const modal = document.getElementById('verificationModal');
            if (!form || !modal) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('verifyBtn');
                const submitText = document.getElementById('submitText');
                const loadingSpinner = document.getElementById('loadingSpinner');

                // Disable form and show loading
                submitBtn.disabled = true;
                submitText.textContent = 'Verifying...';
                loadingSpinner.classList.remove('hidden');
                submitBtn.classList.add('opacity-75');

                try {
                    const formData = new FormData(this);
                    const data = Object.fromEntries(formData.entries());

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
                        // Success animation
                        submitText.textContent = 'Verified!';
                        loadingSpinner.classList.add('hidden');
                        submitBtn.classList.remove('opacity-75');
                        submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');

                        // Add success icon
                        submitText.innerHTML = '<svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Access Granted!';

                        // Show success toast
                        showToast('Verification successful! Loading dashboard...', 'success');

                        // Smooth fade out animation
                        setTimeout(() => {
                            modal.style.transition = 'all 0.5s ease-out';
                            modal.style.opacity = '0';
                            modal.style.transform = 'scale(0.95)';

                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }, 1500);

                    } else {
                        throw new Error(result.error || 'Verification failed');
                    }

                } catch (error) {
                    console.error('Verification error:', error);

                    // Error state
                    submitBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'shake');
                    submitText.textContent = 'Verification Failed';
                    loadingSpinner.classList.add('hidden');

                    // Shake animation
                    setTimeout(() => {
                        submitBtn.classList.remove('shake', 'bg-red-600', 'hover:bg-red-700');
                        submitBtn.classList.remove('opacity-75');
                    }, 600);

                    showToast(error.message, 'error');

                    // Reset button after error
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitText.textContent = 'Verify Access';
                    }, 2000);
                }
            });

            // Auto-format phone input
            document.getElementById('doctor_phone').addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 10) {
                    if (value.startsWith('1')) {
                        value = value.replace(/(\d{1})(\d{3})(\d{3})(\d{4})/, '+$1 ($2) $3-$4');
                    } else {
                        value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
                    }
                }
                e.target.value = value;
            });

            // Auto-format verification code
            document.getElementById('verification_code').addEventListener('input', function(e) {
                let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (value.length > 4 && value.length <= 8) {
                    value = value.substring(0, 4) + '-' + value.substring(4);
                }
                e.target.value = value;

                // Visual feedback for complete code
                if (value.length >= 8) {
                    e.target.classList.add('border-green-500', 'bg-green-50');
                } else {
                    e.target.classList.remove('border-green-500', 'bg-green-50');
                }
            });

            // Real-time validation feedback
            const inputs = form.querySelectorAll('input[required], select[required]');
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.value.trim()) {
                        this.classList.add('border-green-500');
                        this.classList.remove('border-red-500');
                    } else {
                        this.classList.add('border-red-500');
                        this.classList.remove('border-green-500');
                    }
                });

                input.addEventListener('input', function() {
                    this.classList.remove('border-red-500');
                });
            });
        }

        // Dashboard Initialization
        function initializeDashboard() {
            initializeChart();
            setupModals();
            setInterval(refreshData, 300000);
            startRealTimeUpdates();
        }

        // Enhanced Chart Functions
        function initializeChart() {
            const ctx = document.getElementById('vitalsChart');
            if (!ctx) return;

            vitalsChart = new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels || [],
                    datasets: [{
                        label: 'Systolic BP',
                        data: chartData.systolic || [],
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        borderWidth: 3
                    }, {
                        label: 'Diastolic BP',
                        data: chartData.diastolic || [],
                        borderColor: '#dc2626',
                        backgroundColor: 'rgba(220, 38, 38, 0.1)',
                        tension: 0.4,
                        fill: false,
                        pointBackgroundColor: '#dc2626',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 5,
                        borderWidth: 2,
                        borderDash: [5, 5]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: { usePointStyle: true, padding: 20, font: { size: 12, family: 'Inter', weight: '600' } }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#374151',
                            bodyColor: '#6b7280',
                            borderColor: '#e5e7eb',
                            borderWidth: 1,
                            cornerRadius: 12,
                            padding: 12
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } }
                        },
                        y: {
                            beginAtZero: false,
                            grid: { color: '#f3f4f6' },
                            ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } }
                        }
                    },
                    animation: {
                        duration: 1000,
                        easing: 'easeInOutQuart'
                    }
                }
            });

            updateChart('blood_pressure');
        }

        function updateChart(vitalType) {
            if (!vitalsChart) return;

            // Update button states
            document.querySelectorAll('.chart-filter-btn').forEach(btn => {
                btn.classList.remove('active');
                btn.className = 'chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition-all duration-200';
            });

            event.target.classList.add('active');
            event.target.className = 'chart-filter-btn active bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 rounded-xl px-4 py-2 text-sm font-semibold';

            const datasets = vitalsChart.data.datasets;

            switch(vitalType) {
                case 'blood_pressure':
                    datasets[0].hidden = false;
                    datasets[1].hidden = false;
                    datasets[0].label = 'Systolic BP';
                    datasets[1].label = 'Diastolic BP';
                    datasets[0].data = chartData.systolic || [];
                    datasets[1].data = chartData.diastolic || [];
                    datasets[0].borderColor = '#ef4444';
                    datasets[0].backgroundColor = 'rgba(239, 68, 68, 0.1)';
                    updateInsights('120/80 mmHg', '→ Stable', 'Normal');
                    break;
                case 'heart_rate':
                    datasets[0].hidden = false;
                    datasets[1].hidden = true;
                    datasets[0].label = 'Heart Rate';
                    datasets[0].data = chartData.heart_rate || [];
                    datasets[0].borderColor = '#3b82f6';
                    datasets[0].backgroundColor = 'rgba(59, 130, 246, 0.1)';
                    updateInsights('72 bpm', '↗ Increasing', 'Good');
                    break;
                case 'temperature':
                    datasets[0].hidden = false;
                    datasets[1].hidden = true;
                    datasets[0].label = 'Temperature';
                    datasets[0].data = chartData.temperature || [];
                    datasets[0].borderColor = '#f59e0b';
                    datasets[0].backgroundColor = 'rgba(245, 158, 11, 0.1)';
                    updateInsights('98.6°F', '→ Stable', 'Normal');
                    break;
                case 'oxygen_saturation':
                    datasets[0].hidden = false;
                    datasets[1].hidden = true;
                    datasets[0].label = 'Oxygen Saturation';
                    datasets[0].data = chartData.oxygen || [];
                    datasets[0].borderColor = '#10b981';
                    datasets[0].backgroundColor = 'rgba(16, 185, 129, 0.1)';
                    updateInsights('98%', '→ Stable', 'Excellent');
                    break;
                case 'blood_glucose':
                    datasets[0].hidden = false;
                    datasets[1].hidden = true;
                    datasets[0].label = 'Blood Glucose';
                    datasets[0].data = chartData.glucose || [];
                    datasets[0].borderColor = '#8b5cf6';
                    datasets[0].backgroundColor = 'rgba(139, 92, 246, 0.1)';
                    updateInsights('105 mg/dL', '→ Stable', 'Normal');
                    break;
            }

            vitalsChart.update('active');
        }

        // Update chart insights
        function updateInsights(average, trend, status) {
            document.getElementById('averageValue').textContent = average;
            document.getElementById('trendDirection').textContent = trend;
            document.getElementById('vitalStatus').textContent = status;
        }

        // Start real-time updates
        function startRealTimeUpdates() {
            // Simulate real-time updates every 30 seconds
            setInterval(async () => {
                try {
                    const response = await fetch(`/temp_access/${tempAccessToken}/latest-vitals`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        if (data.updated) {
                            updateRealTimeVitals(data.vitals);
                        }
                    }
                } catch (error) {
                    console.error('Error fetching latest vitals:', error);
                }
            }, 30000);
        }

        // Update real-time vitals display
        function updateRealTimeVitals(data) {
            if (data.blood_pressure) {
                document.getElementById('currentBP').textContent = data.blood_pressure;
                document.getElementById('bpTime').textContent = new Date().toLocaleTimeString();
            }

            if (data.heart_rate) {
                document.getElementById('currentHR').textContent = data.heart_rate;
                document.getElementById('hrTime').textContent = new Date().toLocaleTimeString();
            }

            if (data.temperature) {
                document.getElementById('currentTemp').textContent = data.temperature + '°F';
                document.getElementById('tempTime').textContent = new Date().toLocaleTimeString();
            }

            if (data.oxygen_saturation) {
                document.getElementById('currentO2').textContent = data.oxygen_saturation + '%';
                document.getElementById('o2Time').textContent = new Date().toLocaleTimeString();
            }

            if (data.blood_glucose) {
                document.getElementById('currentGlucose').textContent = data.blood_glucose + ' mg/dL';
                document.getElementById('glucoseTime').textContent = new Date().toLocaleTimeString();
            }

            if (data.weight) {
                document.getElementById('currentWeight').textContent = data.weight + ' kg';
                document.getElementById('weightTime').textContent = new Date().toLocaleTimeString();
            }

            showToast('New vital signs received', 'info');
        }

        // Modal Functions
        function setupModals() {
            setupUploadModal();
            setupPrescriptionModal();
            setupHealthTipsModal();
        }

        function setupUploadModal() {
            const form = document.getElementById('uploadForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                await submitForm('upload', `/temp_access/${tempAccessToken}/upload-document`, new FormData(this));
            });
        }

        function setupPrescriptionModal() {
            const form = document.getElementById('prescriptionForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                await submitForm('prescribe', `/temp_access/${tempAccessToken}/prescribe-medication`, data);
            });

            const startDateInput = form.querySelector('input[name="start_date"]');
            if (startDateInput) {
                startDateInput.value = new Date().toISOString().split('T')[0];
            }
        }

        function setupHealthTipsModal() {
            const form = document.getElementById('healthTipsForm');
            if (!form) return;

            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const data = Object.fromEntries(formData.entries());
                await submitForm('healthTips', `/temp_access/${tempAccessToken}/add-health-tip`, data);
            });
        }

        // Generic form submission
        async function submitForm(type, url, data) {
            const btn = document.getElementById(`${type}Btn`);
            const text = document.getElementById(`${type}Text`);
            const spinner = document.getElementById(`${type}Spinner`);
            const originalText = text.textContent;

            btn.disabled = true;
            text.textContent = 'Processing...';
            spinner.classList.remove('hidden');

            try {
                const isFormData = data instanceof FormData;
                const options = {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin'
                };

                if (isFormData) {
                    options.body = data;
                } else {
                    options.headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(data);
                }

                const response = await fetch(url, options);
                const responseText = await response.text();

                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON response:', e);
                    throw new Error('Server returned an invalid response.');
                }

                if (response.ok) {
                    if (result.success) {
                        showToast(result.message || 'Operation completed successfully!', 'success');
                        closeModal(type);
                        if (type === 'upload') {
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    } else {
                        throw new Error(result.error || result.message || 'Operation failed.');
                    }
                } else {
                    throw new Error(result.error || result.message || `Server error: ${response.status}`);
                }

            } catch (error) {
                console.error(`${type} error:`, error);
                showToast(error.message || 'Operation failed. Please try again.', 'error');
            } finally {
                btn.disabled = false;
                text.textContent = originalText;
                spinner.classList.add('hidden');
            }
        }

        // Modal Management
        function openUploadModal() {
            document.getElementById('uploadModal').classList.remove('hidden');
            document.getElementById('uploadModal').classList.add('flex');
        }

        function closeUploadModal() {
            closeModal('upload');
        }

        function openPrescriptionModal() {
            document.getElementById('prescriptionModal').classList.remove('hidden');
            document.getElementById('prescriptionModal').classList.add('flex');
        }

        function closePrescriptionModal() {
            closeModal('prescription');
        }

        function openHealthTipsModal() {
            document.getElementById('healthTipsModal').classList.remove('hidden');
            document.getElementById('healthTipsModal').classList.add('flex');
        }

        function closeHealthTipsModal() {
            closeModal('healthTips');
        }

        function closeModal(type) {
            const modalMap = {
                'upload': 'uploadModal',
                'prescription': 'prescriptionModal',
                'healthTips': 'healthTipsModal',
                'vitalDetails': 'vitalDetailsModal'
            };

            const modal = document.getElementById(modalMap[type]);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');

                const form = modal.querySelector('form');
                if (form) form.reset();
            }
        }

        // Enhanced Vital Details
        function showVitalDetails(vital) {
            const modal = document.getElementById('vitalDetailsModal');
            const content = document.getElementById('vitalDetailsContent');

            content.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-blue-50 p-6 rounded-xl border border-blue-200">
                            <h4 class="font-semibold text-blue-800 mb-4 text-lg">Basic Information</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-sm text-gray-600">Date & Time:</span>
                                    <p class="font-medium text-gray-900">${new Date(vital.measured_at).toLocaleString()}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Status:</span>
                                    <p class="font-medium">
                                        <span class="status-badge status-${vital.status || 'normal'}">
                                            <div class="status-indicator status-${vital.status || 'normal'}"></div>
                                            ${vital.status ? vital.status.charAt(0).toUpperCase() + vital.status.slice(1) : 'Normal'}
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Recorded By:</span>
                                    <p class="font-medium text-gray-900">${vital.recorded_by || 'Patient Self-Report'}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Device:</span>
                                    <p class="font-medium text-gray-900">${vital.device || 'Manual Entry'}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-xl border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4 text-lg">Vital Measurements</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                                    <span class="text-sm text-red-600 font-medium">Blood Pressure</span>
                                    <p class="text-2xl font-bold text-red-800">${vital.systolic_bp || '--'}/${vital.diastolic_bp || '--'} mmHg</p>
                                    ${vital.systolic_bp ? `
                                    <span class="text-xs ${vital.systolic_bp >= 140 || vital.diastolic_bp >= 90 ? 'text-red-600' : vital.systolic_bp >= 130 || vital.diastolic_bp >= 80 ? 'text-yellow-600' : 'text-green-600'}">
                                        ${vital.systolic_bp >= 140 || vital.diastolic_bp >= 90 ? 'High' : vital.systolic_bp >= 130 || vital.diastolic_bp >= 80 ? 'Elevated' : 'Normal'}
                                    </span>
                                    ` : ''}
                                </div>
                                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                    <span class="text-sm text-blue-600 font-medium">Heart Rate</span>
                                    <p class="text-2xl font-bold text-blue-800">${vital.heart_rate || '--'} bpm</p>
                                    ${vital.heart_rate ? `
                                    <span class="text-xs ${vital.heart_rate > 100 ? 'text-red-600' : vital.heart_rate < 60 ? 'text-yellow-600' : 'text-green-600'}">
                                        ${vital.heart_rate > 100 ? 'High' : vital.heart_rate < 60 ? 'Low' : 'Normal'}
                                    </span>
                                    ` : ''}
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg border border-orange-200">
                                    <span class="text-sm text-orange-600 font-medium">Temperature</span>
                                    <p class="text-2xl font-bold text-orange-800">${vital.temperature ? vital.temperature + '°F' : '--'}</p>
                                    ${vital.temperature ? `
                                    <span class="text-xs ${vital.temperature >= 100.4 ? 'text-red-600' : vital.temperature <= 97.0 ? 'text-blue-600' : 'text-green-600'}">
                                        ${vital.temperature >= 100.4 ? 'Fever' : vital.temperature <= 97.0 ? 'Low' : 'Normal'}
                                    </span>
                                    ` : ''}
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                                    <span class="text-sm text-green-600 font-medium">Oxygen Saturation</span>
                                    <p class="text-2xl font-bold text-green-800">${vital.oxygen_saturation ? vital.oxygen_saturation + '%' : '--'}</p>
                                    ${vital.oxygen_saturation ? `
                                    <span class="text-xs ${vital.oxygen_saturation < 95 ? 'text-red-600' : vital.oxygen_saturation < 98 ? 'text-yellow-600' : 'text-green-600'}">
                                        ${vital.oxygen_saturation < 95 ? 'Low' : vital.oxygen_saturation < 98 ? 'Fair' : 'Good'}
                                    </span>
                                    ` : ''}
                                </div>
                                ${vital.blood_glucose ? `
                                <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                                    <span class="text-sm text-purple-600 font-medium">Blood Glucose</span>
                                    <p class="text-2xl font-bold text-purple-800">${vital.blood_glucose} mg/dL</p>
                                    <span class="text-xs ${vital.blood_glucose > 180 ? 'text-red-600' : vital.blood_glucose < 70 ? 'text-red-600' : vital.blood_glucose > 140 ? 'text-yellow-600' : 'text-green-600'}">
                                        ${vital.blood_glucose > 180 ? 'High (>180)' : vital.blood_glucose < 70 ? 'Low (<70)' : vital.blood_glucose > 140 ? 'Elevated (140-180)' : 'Normal (70-140)'}
                                    </span>
                                </div>
                                ` : ''}
                                ${vital.weight ? `
                                <div class="bg-cyan-50 p-4 rounded-lg border border-cyan-200">
                                    <span class="text-sm text-cyan-600 font-medium">Weight</span>
                                    <p class="text-2xl font-bold text-cyan-800">${vital.weight} kg</p>
                                    <span class="text-xs text-cyan-600">Weight Measurement</span>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        ${vital.notes ? `
                        <div class="bg-white p-6 rounded-xl border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-3">Patient Notes</h4>
                            <p class="text-gray-700 leading-relaxed">${vital.notes}</p>
                        </div>
                        ` : ''}

                        ${vital.symptoms ? `
                        <div class="bg-yellow-50 p-6 rounded-xl border border-yellow-200">
                            <h4 class="font-semibold text-yellow-800 mb-3">Reported Symptoms</h4>
                            <p class="text-yellow-700 leading-relaxed">${vital.symptoms}</p>
                        </div>
                        ` : ''}

                        <div class="bg-purple-50 p-6 rounded-xl border border-purple-200">
                            <h4 class="font-semibold text-purple-800 mb-4">Clinical Assessment</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Overall Status:</span>
                                    <span class="font-medium ${vital.status === 'critical' ? 'text-red-600' : vital.status === 'warning' ? 'text-yellow-600' : 'text-green-600'}">
                                        ${vital.status ? vital.status.charAt(0).toUpperCase() + vital.status.slice(1) : 'Normal'}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Reading Time:</span>
                                    <span class="font-medium text-gray-900">${new Date(vital.measured_at).toLocaleString()}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Data Age:</span>
                                    <span class="font-medium text-gray-900">${new Date(vital.measured_at).toLocaleDateString()}</span>
                                </div>
                            </div>
                        </div>

                        ${vital.blood_glucose ? `
                        <div class="bg-indigo-50 p-6 rounded-xl border border-indigo-200">
                            <h4 class="font-semibold text-indigo-800 mb-4">Glucose Analysis</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Current Level:</span>
                                    <span class="font-medium ${vital.blood_glucose > 180 ? 'text-red-600' : vital.blood_glucose < 70 ? 'text-red-600' : vital.blood_glucose > 140 ? 'text-yellow-600' : 'text-green-600'}">${vital.blood_glucose} mg/dL</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Category:</span>
                                    <span class="font-medium ${vital.blood_glucose > 180 ? 'text-red-600' : vital.blood_glucose < 70 ? 'text-red-600' : vital.blood_glucose > 140 ? 'text-yellow-600' : 'text-green-600'}">
                                        ${vital.blood_glucose > 180 ? 'Hyperglycemic' : vital.blood_glucose < 70 ? 'Hypoglycemic' : vital.blood_glucose > 140 ? 'Elevated' : 'Normal Range'}
                                    </span>
                                </div>
                                <div class="text-sm text-indigo-700 mt-3 p-3 bg-indigo-100 rounded-lg">
                                    <strong>Clinical Note:</strong>
                                    ${vital.blood_glucose > 180 ? 'Consider immediate intervention for hyperglycemia. Monitor for symptoms and review medication compliance.' :
                                      vital.blood_glucose < 70 ? 'Monitor for hypoglycemia symptoms. Ensure patient safety and consider glucose administration if symptomatic.' :
                                      vital.blood_glucose > 140 ? 'Monitor trend and consider dietary counseling. Review recent meals and medication timing.' :
                                      'Glucose level within normal range. Continue current management plan.'}
                                </div>
                            </div>
                        </div>
                        ` : ''}

                        <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                            <h4 class="font-semibold text-gray-800 mb-4">Measurement Context</h4>
                            <div class="space-y-3">
                                ${vital.activity_level ? `
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Activity Level:</span>
                                    <span class="font-medium text-gray-900">${vital.activity_level.charAt(0).toUpperCase() + vital.activity_level.slice(1)}</span>
                                </div>
                                ` : ''}
                                ${vital.pain_level ? `
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Pain Level:</span>
                                    <span class="font-medium text-gray-900">${vital.pain_level}/10</span>
                                </div>
                                ` : ''}
                                ${vital.sleep_hours ? `
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Sleep (Last Night):</span>
                                    <span class="font-medium text-gray-900">${vital.sleep_hours} hours</span>
                                </div>
                                ` : ''}
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Data Source:</span>
                                    <span class="font-medium text-gray-900">${vital.device || 'Manual Entry'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeVitalDetailsModal() {
            closeModal('vitalDetails');
        }

        // Enhanced Export Functions
        function exportVitals() {
            try {
                const doc = new jsPDF();
                const patientName = '{{ $patient->user->name ?? "Unknown Patient" }}';
                const doctorName = '{{ $tempAccess->doctor_name ?? "External Doctor" }}';

                // Add title and header
                doc.setFontSize(20).setFont(undefined, 'bold').text('Patient Vitals Export', 14, 20);
                doc.setFontSize(16).setFont(undefined, 'normal').text(`Patient: ${patientName}`, 14, 30);
                doc.setFontSize(12).text(`Exported by: Dr. ${doctorName}`, 14, 40);
                doc.setFontSize(10).text(`Generated on: ${new Date().toLocaleString()}`, 14, 50);

                // Add vitals data
                let currentY = 65;
                doc.setFontSize(14).setFont(undefined, 'bold').text('Latest Vital Signs:', 14, currentY);
                currentY += 10;

                const latestVitals = [
                    ['Measurement', 'Value', 'Status', 'Recorded'],
                    ['Blood Pressure', '{{ $latestVitals ? ($latestVitals->systolic_bp . "/" . $latestVitals->diastolic_bp . " mmHg") : "N/A" }}', '{{ $latestVitals->status ?? "Normal" }}', '{{ $latestVitals ? $latestVitals->measured_at->format("Y-m-d H:i") : "N/A" }}'],
                    ['Heart Rate', '{{ $latestVitals ? ($latestVitals->heart_rate . " bpm") : "N/A" }}', 'Normal', '{{ $latestVitals ? $latestVitals->measured_at->format("Y-m-d H:i") : "N/A" }}'],
                    ['Temperature', '{{ $latestVitals ? ($latestVitals->temperature . "°F") : "N/A" }}', 'Normal', '{{ $latestVitals ? $latestVitals->measured_at->format("Y-m-d H:i") : "N/A" }}'],
                    ['Oxygen Saturation', '{{ $latestVitals ? ($latestVitals->oxygen_saturation . "%") : "N/A" }}', 'Good', '{{ $latestVitals ? $latestVitals->measured_at->format("Y-m-d H:i") : "N/A" }}'],
                    ['Blood Glucose', '{{ $latestVitals ? ($latestVitals->blood_glucose . " mg/dL") : "N/A" }}', 'Normal', '{{ $latestVitals ? $latestVitals->measured_at->format("Y-m-d H:i") : "N/A" }}']
                ];

                doc.autoTable({
                    head: [latestVitals[0]],
                    body: latestVitals.slice(1),
                    startY: currentY,
                    styles: { fontSize: 10, cellPadding: 3 },
                    headStyles: { fillColor: [59, 130, 246], textColor: 255, fontStyle: 'bold' },
                    columnStyles: {
                        0: { cellWidth: 40, fontStyle: 'bold' },
                        1: { cellWidth: 40 },
                        2: { cellWidth: 30 },
                        3: { cellWidth: 40 }
                    }
                });

                // Save the PDF
                const filename = `vitals_export_${patientName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().slice(0, 10)}.pdf`;
                doc.save(filename);

                showToast('Vitals exported successfully!', 'success');

            } catch (error) {
                console.error('Export error:', error);
                showToast('Error exporting vitals', 'error');
            }
        }

        function generateReport() {
            try {
                const doc = new jsPDF();
                const patientName = '{{ $patient->user->name ?? "Unknown Patient" }}';
                const doctorName = '{{ $tempAccess->doctor_name ?? "External Doctor" }}';

                // Add comprehensive report
                doc.setFontSize(20).setFont(undefined, 'bold').text('Comprehensive Patient Report', 14, 20);
                doc.setFontSize(16).setFont(undefined, 'normal').text(`Patient: ${patientName}`, 14, 30);
                doc.setFontSize(12).text(`Prepared by: Dr. ${doctorName}`, 14, 40);
                doc.setFontSize(10).text(`Generated on: ${new Date().toLocaleString()}`, 14, 50);

                // Add patient info
                let currentY = 65;
                doc.setFontSize(14).setFont(undefined, 'bold').text('Patient Information:', 14, currentY);
                currentY += 10;

                const patientInfo = [
                    ['Field', 'Value'],
                    ['Medical Record:', '{{ $patient->medical_record_number ?? "N/A" }}'],
                    ['Blood Type:', '{{ $patient->blood_type ?? "Unknown" }}'],
                    ['Age:', '{{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : "Unknown" }} years'],
                    ['Gender:', '{{ ucfirst($patient->gender ?? "Not specified") }}'],
                    ['Height:', '{{ $patient->height ? $patient->height . " cm" : "N/A" }}'],
                    ['Activity Level:', '{{ ucfirst(str_replace("_", " ", $patient->activity_level ?? "unknown")) }}'],
                    ['Smoker:', '{{ $patient->smoker ? "Yes" : "No" }}']
                ];

                doc.autoTable({
                    head: [patientInfo[0]],
                    body: patientInfo.slice(1),
                    startY: currentY,
                    styles: { fontSize: 10, cellPadding: 3 },
                    headStyles: { fillColor: [59, 130, 246], textColor: 255, fontStyle: 'bold' },
                    columnStyles: {
                        0: { cellWidth: 60, fontStyle: 'bold' },
                        1: { cellWidth: 120 }
                    }
                });

                currentY = doc.lastAutoTable.finalY + 15;

                // Add access information
                doc.setFontSize(14).setFont(undefined, 'bold').text('Access Information:', 14, currentY);
                currentY += 10;

                const accessInfo = [
                    ['Field', 'Value'],
                    ['Doctor:', 'Dr. {{ $tempAccess->doctor_name ?? "External Doctor" }}'],
                    ['Specialty:', '{{ $tempAccess->doctor_specialty ?? "N/A" }}'],
                    ['Facility:', '{{ $tempAccess->doctor_facility ?? "N/A" }}'],
                    ['Access Date:', '{{ $tempAccess->verified_at ? $tempAccess->verified_at->format("Y-m-d H:i:s") : "N/A" }}'],
                    ['Access Count:', '{{ $tempAccess->access_count }}'],
                    ['Expires:', '{{ $tempAccess->expires_at->format("Y-m-d H:i:s") }}']
                ];

                doc.autoTable({
                    head: [accessInfo[0]],
                    body: accessInfo.slice(1),
                    startY: currentY,
                    styles: { fontSize: 10, cellPadding: 3 },
                    headStyles: { fillColor: [59, 130, 246], textColor: 255, fontStyle: 'bold' },
                    columnStyles: {
                        0: { cellWidth: 60, fontStyle: 'bold' },
                        1: { cellWidth: 120 }
                    }
                });

                // Save the PDF
                const filename = `patient_report_${patientName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().toISOString().slice(0, 10)}.pdf`;
                doc.save(filename);

                showToast('Report generated successfully!', 'success');

            } catch (error) {
                console.error('Report generation error:', error);
                showToast('Error generating report', 'error');
            }
        }

        function refreshData() {
            showToast('Refreshing patient data...', 'info');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        }

        function showToast(message, type = 'info', duration = 4000) {
            // Remove any existing toasts
            const existingToasts = document.querySelectorAll('.toast-notification');
            existingToasts.forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = `toast-notification fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 max-w-sm ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-white' :
                'bg-blue-500 text-white'
            }`;

            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${type === 'success' ?
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>' :
                            type === 'error' ?
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>' :
                            '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'
                        }
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">${message}</p>
                    </div>
                    <div class="ml-4 flex-shrink-0">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(toast);

            // Auto remove after duration
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, duration);
        }

    </script>


</body>
</html>
