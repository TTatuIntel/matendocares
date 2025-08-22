@extends('doctor.layout')

@section('title', 'Patient Monitor - ' . $patient->name)
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Patient Header -->
        <div class="medical-card p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center space-x-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-3xl flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-2xl">{{ strtoupper(substr($patient->name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $patient->name }}</h1>
                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                            <span>📋 {{ $patient->patient->medical_record_number ?? 'No MRN' }}</span>
                            <span>🩸 {{ $patient->patient->blood_type ?? 'Unknown' }}</span>
                            <span>📧 {{ $patient->email }}</span>
                            @if($patient->phone)
                            <span>📞 {{ $patient->phone }}</span>
                            @endif
                        </div>
                        <div class="flex items-center space-x-2 mt-2">
                            <span class="status-badge {{ $patient->status ?? 'normal' }}">
                                <div class="status-indicator status-{{ $patient->status ?? 'normal' }}"></div>
                                {{ ucfirst($patient->status ?? 'normal') }}
                            </span>
                            @if($patient->last_activity)
                            <span class="text-xs text-gray-500">Last active: {{ $patient->last_activity->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">

                    <button onclick="openExportModal()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate Report
                    </button>
                    <a href="{{ route('doctor.patients.index') }}" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"></path>
                        </svg>
                        Back to Patients
                    </a>
                </div>
            </div>
        </div>


        <!-- Critical Alerts -->
        @if(isset($criticalAlerts) && $criticalAlerts->count() > 0)
        <div class="critical-alert medical-card p-6 border-red-300">
            <div class="flex items-center mb-4">
                <svg class="w-6 h-6 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h2 class="text-xl font-bold text-white">🚨 Critical Alerts</h2>
            </div>
            <div class="space-y-3">
                @foreach($criticalAlerts as $alert)
                <div class="bg-white rounded-lg p-4 border border-red-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-semibold text-red-800">{{ $alert->title ?? 'Critical Vital Signs' }}</p>
                            <p class="text-sm text-red-600">{{ $alert->message ?? 'Patient requires immediate attention' }}</p>
                            <p class="text-xs text-red-500 mt-1">{{ $alert->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                        <button onclick="acknowledgeAlert({{ $alert->id }})" class="btn-primary text-sm px-4 py-2">
                            Acknowledge
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Real-time Vitals Dashboard -->
        <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
            <!-- Current Vitals Cards -->
            <div class="xl:col-span-4 grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="bpCard">
                    <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-2" id="currentBP">
                        {{ $latestVitals->blood_pressure ?? '--/--' }}
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Blood Pressure</div>
                    <div class="text-xs text-gray-400 mt-1">mmHg</div>
                    <div class="text-xs text-gray-500 mt-2" id="bpTime">
                        {{ $latestVitals ? $latestVitals->measured_at->format('g:i A') : '--' }}
                    </div>
                </div>

                <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="hrCard">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
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

                <!-- Blood Glucose Card -->
<div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="glucoseCard">
    <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
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
<div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300" id="weightCard">
    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
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

        <!-- Main Monitoring Content -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Charts and Trends -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Vital Signs Trends Chart -->
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
                        <!-- Chart Filter Buttons -->
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
                        </div>

                        <!-- Chart Container -->
                        <div class="relative h-96 bg-gradient-to-br from-gray-50 to-white rounded-3xl p-6 border border-gray-100">
                            <canvas id="vitalsChart"></canvas>
                        </div>

                        <!-- Chart Insights -->
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

                <!-- Recent Activity Timeline -->
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Recent Activity Timeline</h3>
                        <p class="text-gray-600">Patient's latest health updates and interactions</p>
                    </div>

                    <div class="p-8">
                        <div class="space-y-6" id="activityTimeline">
                            @forelse($recentActivities ?? [] as $activity)
                            <div class="activity-item flex items-start space-x-4 p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-200">
                                <div class="flex-shrink-0 mt-1">
                                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center {{ $activity->status === 'critical' ? 'bg-red-100 text-red-600' : ($activity->status === 'warning' ? 'bg-yellow-100 text-yellow-600' : 'bg-green-100 text-green-600') }}">
                                        @if($activity->status === 'critical')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                        @elseif($activity->status === 'warning')
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
                                    <p class="text-sm font-bold text-gray-900 mb-1">{{ $activity->type ?? 'Vital Signs Update' }}</p>
                                    <p class="text-sm text-gray-700 mb-2">{{ $activity->description ?? 'Patient reported new vital signs' }}</p>
                                    <div class="flex items-center justify-between">
                                        <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                                        <button onclick="viewActivityDetails({{ $activity->id }})" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                            View Details
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12">
                                <div class="text-6xl mb-4">📊</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">No Recent Activity</h3>
                                <p class="text-gray-600">Patient activity will appear here as it happens</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Patient Information -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">👤 Patient Information</h3>
                        <p class="text-sm text-gray-600">Medical details and history</p>
                    </div>

                    <div class="space-y-4">
                        <div class="info-item">
                            <span class="info-label">Medical Record:</span>
                            <span class="info-value">{{ $patient->patient->medical_record_number ?? 'Not assigned' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Blood Type:</span>
                            <span class="info-value text-red-600 font-semibold">{{ $patient->patient->blood_type ?? 'Unknown' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Age:</span>
                            <span class="info-value">{{ $patient->patient->date_of_birth ? \Carbon\Carbon::parse($patient->patient->date_of_birth)->age : 'Unknown' }} years</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Gender:</span>
                            <span class="info-value">{{ ucfirst($patient->patient->gender ?? 'Not specified') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Activity Level:</span>
                            <span class="info-value">{{ ucfirst(str_replace('_', ' ', $patient->patient->activity_level ?? 'unknown')) }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Smoker:</span>
                            <span class="info-value">{{ $patient->patient->smoker ? 'Yes' : 'No' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Medical History -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">🏥 Medical History</h3>
                        <p class="text-sm text-gray-600">Conditions and allergies</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h4 class="font-semibold text-gray-800 text-sm mb-2">Chronic Conditions</h4>
                            @if($patient->patient->chronic_conditions)
                            <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">{{ $patient->patient->chronic_conditions }}</p>
                            @else
                            <p class="text-sm text-gray-500 italic">None recorded</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-800 text-sm mb-2">Allergies</h4>
                            @if($patient->patient->allergies)
                            <p class="text-sm text-red-700 bg-red-50 p-3 rounded-lg border border-red-200">{{ $patient->patient->allergies }}</p>
                            @else
                            <p class="text-sm text-gray-500 italic">None recorded</p>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-semibold text-gray-800 text-sm mb-2">Current Medications</h4>
                            @if($patient->patient->current_medications)
                            <p class="text-sm text-gray-700 bg-blue-50 p-3 rounded-lg border border-blue-200">{{ $patient->patient->current_medications }}</p>
                            @else
                            <p class="text-sm text-gray-500 italic">None recorded</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
<div class="medical-card p-6 bg-white rounded-lg shadow-md">
    <div class="mb-6">
        <h3 class="text-xl font-bold text-gray-900 mb-2">⚡ Quick Actions</h3>
        <p class="text-sm text-gray-500">Streamlined Patient Management</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <button onclick="openHealthTipsModal()" class="action-btn bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 text-purple-700 hover:from-purple-100 hover:to-purple-200 transition-all duration-200 flex items-center justify-center p-3 rounded-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Add Health Tips
        </button>

        <button onclick="openPrescribeMedicationModal()" class="action-btn bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 text-green-700 hover:from-green-100 hover:to-emerald-100 transition-all duration-200 flex items-center justify-center p-3 rounded-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
            </svg>
            Prescribe Medication
        </button>

        <a href="{{ route('doctor.appointments.index') }}" class="action-btn bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 text-purple-700 hover:from-purple-100 hover:to-purple-200 transition-all duration-200 flex items-center justify-center p-3 rounded-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Schedule Appointment
        </a>

        <button onclick="sendMessage()" class="action-btn bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 text-orange-700 hover:from-orange-100 hover:to-red-100 transition-all duration-200 flex items-center justify-center p-3 rounded-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            Send Message
        </button>

        <button data-open-doctor-documents class="action-btn bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 text-blue-700 hover:from-blue-100 hover:to-blue-200 transition-all duration-200 flex items-center justify-center p-3 rounded-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            My Documents
        </button>

        <button onclick="openDocumentUploadModal()" class="action-btn bg-gradient-to-r from-indigo-50 to-indigo-100 border border-indigo-200 text-indigo-700 hover:from-indigo-100 hover:to-indigo-200 transition-all duration-200 flex items-center justify-center p-3 rounded-lg">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Upload Document
        </button>
    </div>
</div>

                <!-- Monitoring Settings -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">⚙️ Monitoring Settings</h3>
                        <p class="text-sm text-gray-600">Alert preferences</p>
                    </div>

                    <div class="space-y-4">
                        <div class="setting-item">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Real-time Alerts</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Critical Notifications</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Daily Summary</span>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>

                        <div class="setting-item">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Share with Team</span>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medications Section -->
        <div class="medical-card p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Medications</h2>
            </div>

            @if($patient->patient->meds->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($patient->patient->meds as $med)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $med->name }}
                                @if($med->generic_name)
                                <div class="text-xs text-gray-400">({{ $med->generic_name }})</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $med->dosage }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $med->frequency }}
                                @if($med->times)
                                <div class="text-xs text-gray-400">
                                    Times: {{ $med->times_display }}
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $med->start_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    @if($med->status === 'active') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ ucfirst($med->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="showMedDetails(this)"
                                        class="text-blue-600 hover:text-blue-900 mr-2"
                                        data-med='@json($med)'>
                                    Details
                                </button>
                                @if($med->prescribed_by)
                                <span class="text-xs text-gray-500">Prescribed by: {{ $med->prescribed_by }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
                <p class="text-gray-500">No medications recorded.</p>
            @endif
        </div>

        <!-- Documents Section -->
        <!-- Enhanced Documents Section -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="mb-6 lg:mb-0">
                        <h2 class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent mb-2">
                            📄 Patient Documents
                        </h2>
                        <p class="text-gray-600">Securely manage patient healthcare documents and records</p>
                    </div>
                    <button onclick="openDocumentUploadModal()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload Document
                    </button>
                </div>
            </div>
            <!-- Search and Filter -->
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" id="searchDocuments" placeholder="Search documents by title, category, or tags..."
                                   class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                            <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <select id="categoryDocumentFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                            <option value="">All Categories</option>
                            <option value="Prescription">Prescription</option>
                            <option value="Lab Results">Lab Results</option>
                            <option value="Medical Report">Medical Report</option>
                            <option value="Imaging">Imaging</option>
                            <option value="Note">Clinical Note</option>
                            <option value="Referral">Referral</option>
                            <option value="Other">Other</option>
                        </select>
                        <select id="sortDocumentFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                            <option value="name">Name A-Z</option>
                            <option value="size">File Size</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Documents Grid -->
            <div class="p-8">
                <div id="documentsGridContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @forelse($patient->patient->documents as $document)
                    <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300 document-card"
                         data-category="{{ $document->category }}"
                         data-title="{{ strtolower($document->title) }}"
                         data-tags="{{ strtolower($document->tags ?? '') }}">
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

                        @if($document->tags)
                            <div class="mb-4">
                                @foreach(array_slice(explode(',', $document->tags), 0, 3) as $tag)
                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1 mb-1 font-medium">{{ trim($tag) }}</span>
                                @endforeach
                                @if(count(explode(',', $document->tags)) > 3)
                                    <span class="text-xs text-gray-500">+{{ count(explode(',', $document->tags)) - 3 }} more</span>
                                @endif
                            </div>
                        @endif

                        <div class="flex space-x-2">


<a href="{{ route('doctor.patients.documents.download', [$patient->id, $document->id]) }}"
   class="flex-1 btn-primary text-center py-2.5 rounded-xl text-sm font-semibold">
   👁️ View
</a>


                            <a href="{{ route('doctor.patients.documents.download', [$patient->id, $document->id]) }}"
                               class="flex-1 btn-secondary text-center py-2.5 rounded-xl text-sm font-semibold">
                                ⬇️ Download
                            </a>
                            <div class="relative">
                                <button onclick="toggleDocumentDropdown('{{ $document->id }}')" class="p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"></path>
                                    </svg>
                                </button>
                                <div id="dropdown-document-{{ $document->id }}" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg z-20 hidden border border-gray-200">
                                    <button onclick="editDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-t-xl transition-colors">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Edit Details
                                    </button>
                                    <button onclick="shareDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                        </svg>
                                        Share with Patient
                                    </button>
                                    <button onclick="archiveDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6m0 0l6-6m-6 6V3"></path>
                                        </svg>
                                        Archive
                                    </button>
                                    <button onclick="deleteDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors border-t border-gray-100 rounded-b-xl">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <!-- Empty State -->
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <div class="text-8xl mb-6">📄</div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-4">No Documents Found</h3>
                            <p class="text-gray-600 mb-8 text-lg">Upload the first document for this patient to start building their medical record.</p>
                            <button onclick="openDocumentUploadModal()" class="btn-primary px-8 py-4 text-lg font-semibold">
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




<!-- Documents Display Modal -->
<!-- Documents Display Modal -->
<div id="doctorDocumentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl leading-6 font-medium text-gray-900">My Documents</h3>
                    <p class="text-sm text-gray-600 mt-1">View all your uploaded documents</p>
                </div>
                <button type="button" onclick="closeDoctorDocumentsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Loading State -->
            <div id="doctorDocumentsLoading" class="flex justify-center items-center py-12">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-gray-600">Loading documents...</span>
            </div>

            <!-- Empty State -->
            <div id="doctorDocumentsEmpty" class="text-center py-12 hidden">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No documents found</h3>
                <p class="mt-2 text-gray-600">You haven't uploaded any documents yet.</p>
                <button onclick="openUploadDocumentModal()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    Upload Document
                </button>
            </div>

            <!-- Documents Grid -->
            <div id="doctorDocumentsGrid" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="doctorDocumentsContainer">
                    <!-- Documents will be populated here -->
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span id="doctorDocumentsCount">0 documents</span>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="refreshDoctorDocuments()" class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    <button type="button" onclick="closeDoctorDocumentsModal()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Close
                    </button>
                    <button onclick="openUploadDocumentModal()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Upload New
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Details Modal -->
<div id="doctorDocumentDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Document Details</h3>
                <button type="button" onclick="closeDoctorDocumentDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="doctorDocumentDetailsContent">
                <!-- Details will be populated here -->
            </div>
        </div>
    </div>
</div>
            <!-- Document Upload Modal -->
<div id="documentUploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Upload Document</h3>
                <button type="button" onclick="closeDocumentUploadModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form with novalidate to prevent browser validation conflicts -->
            <form id="documentUploadForm" method="POST" action="/doctor/documents/upload" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="_token" id="csrf_token">

<input type="hidden" name="user_id" value="{{ request()->route('patient') }}">

                <div class="space-y-4">
                    <div>
                        <label for="document_title" class="block text-sm font-medium text-gray-700 mb-1">Title*</label>
                        <input type="text" id="document_title" name="title" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="document_category" class="block text-sm font-medium text-gray-700 mb-1">Category*</label>
                        <select id="document_category" name="category" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select a category</option>
                            <option value="Prescription">Prescription</option>
                            <option value="Lab Results">Lab Results</option>
                            <option value="Medical Report">Medical Report</option>
                            <option value="Imaging">Imaging (X-ray, MRI, etc.)</option>
                            <option value="Note">Clinical Note</option>
                            <option value="Referral">Referral</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label for="document_file" class="block text-sm font-medium text-gray-700 mb-1">File*</label>
                        <input type="file" id="document_file" name="file" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt">
                        <p class="mt-1 text-xs text-gray-500">PDF, Word, Images, or Text files (Max: 10MB)</p>
                    </div>

                    <div>
                        <label for="document_description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="document_description" name="description" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeDocumentUploadModal()" class="btn-secondary px-4 py-2">
                        Cancel
                    </button>
                    <!-- Removed onclick handler to prevent conflicts -->
                    <button type="submit" class="btn-primary px-4 py-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        Upload Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Prescribe Medication Modal -->
<div id="prescribeMedicationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Prescribe Medication</h3>
                <button type="button" onclick="closePrescribeMedicationModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="prescribeMedicationForm" method="POST" action="/doctor/medications" novalidate>
<!-- <form id="prescribeMedicationForm" action="{{ url('/doctor/medications') }}" method="POST"> -->

                @csrf
                <!-- <input type="hidden" name="user_id" value="{{ request()->route(param: 'patient') }}"> -->
    <input type="hidden" name="user_id" id="patientIdInput">



                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="medication_name" class="block text-sm font-medium text-gray-700 mb-1">Medication Name*</label>
                            <input type="text" id="medication_name" name="name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="generic_name" class="block text-sm font-medium text-gray-700 mb-1">Generic Name</label>
                            <input type="text" id="generic_name" name="generic_name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="dosage" class="block text-sm font-medium text-gray-700 mb-1">Dosage*</label>
                            <input type="text" id="dosage" name="dosage" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="frequency" class="block text-sm font-medium text-gray-700 mb-1">Frequency*</label>
                            <select id="frequency" name="frequency" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select frequency</option>
                                <option value="Once daily">Once daily</option>
                                <option value="Twice daily">Twice daily</option>
                                <option value="Three times daily">Three times daily</option>
                                <option value="Four times daily">Four times daily</option>
                                <option value="Every 4 hours">Every 4 hours</option>
                                <option value="Every 6 hours">Every 6 hours</option>
                                <option value="Every 8 hours">Every 8 hours</option>
                                <option value="Every 12 hours">Every 12 hours</option>
                                <option value="As needed">As needed</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div id="timesContainer" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Specific Times*</label>
                        <div class="flex flex-wrap gap-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="times[]" value="Morning" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2">Morning</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="times[]" value="Noon" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2">Noon</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="times[]" value="Evening" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2">Evening</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="times[]" value="Night" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2">Night</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="times[]" value="Before meals" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2">Before meals</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="times[]" value="After meals" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                <span class="ml-2">After meals</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date*</label>
                            <input type="date" id="start_date" name="start_date" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label for="refills" class="block text-sm font-medium text-gray-700 mb-1">Refills*</label>
                            <input type="number" id="refills" name="refills" min="0" value="0" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>

                    <div>
                        <label for="prescribed_by" class="block text-sm font-medium text-gray-700 mb-1">Prescribed By</label>
                        <input type="text" id="prescribed_by" name="prescribed_by"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="purpose" class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                        <input type="text" id="purpose" name="purpose"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="instructions" class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                        <textarea id="instructions" name="instructions" rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status*</label>
                        <select id="status" name="status" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closePrescribeMedicationModal()" class="btn-secondary px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary px-4 py-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a4 4 0 008 0V7M8 7h8m-8 0H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2h-4"></path>
                        </svg>
                        Prescribe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Health Tips Modal -->
<div id="healthTipsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Add Health Tips</h3>
                <button type="button" onclick="closeHealthTipsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="healthTipsForm" method="POST" action="/doctor/health-tips">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="health_tips" class="block text-sm font-medium text-gray-700 mb-1">Health Tips*</label>
                        <textarea id="health_tips" name="health_tips" rows="6" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter health tips for the patient..."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeHealthTipsModal()" class="btn-secondary px-4 py-2">
                        Cancel
                    </button>
                    <button type="submit" class="btn-primary px-4 py-2">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Save Tips
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

        <!-- Vitals History Table -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Vital Signs History</h3>
                        <p class="text-gray-600">Complete record of patient's vital signs</p>
                    </div>
                    <button onclick="exportVitals()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Data
                    </button>
                </div>
            </div>

            <div class="p-8">
                @if($patient->patient->vitalSigns->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Blood Pressure</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Heart Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Temperature</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">O2 Saturation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Weight</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($patient->patient->vitalSigns->take(20) as $vital)
                        <tr class="hover:bg-gray-50 cursor-pointer"
                            onclick="showVitalDetails(this)"
                            data-vital='@json($vital)'>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $vital->measured_at->format('M d, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $vital->blood_pressure ?? '--' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $vital->heart_rate ? $vital->heart_rate . ' bpm' : '--' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $vital->temperature ? $vital->temperature . '°F' : '--' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $vital->oxygen_saturation ? $vital->oxygen_saturation . '%' : '--' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $vital->weight ? $vital->weight . ' lbs' : '--' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="status-badge {{ $vital->status ?? 'normal' }}">
                                        <div class="status-indicator status-{{ $vital->status ?? 'normal' }}"></div>
                                        {{ ucfirst($vital->status ?? 'normal') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $vital->notes ?? '--' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <div class="text-6xl mb-4">📊</div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No Vital Signs History</h3>
                    <p class="text-gray-600">Patient vital signs will appear here once they start reporting</p>
                </div>
                @endif
            </div>
        </div>



    </div>
</div>

<!-- Vital Signs Details Modal -->
<!-- Vital Signs Details Modal -->
<div id="vitalDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Vital Signs Details</h3>
                <button onclick="closeVitalDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6" id="vitalDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>


<!-- Export Data Modal -->
<div id="exportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Download Patient Data</h3>
            <div class="mt-2 px-7 py-3">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="dateRange">
                        Date Range (optional)
                    </label>
                    <div class="flex space-x-2">
                        <input type="date" id="startDate" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <span class="self-center">to</span>
                        <input type="date" id="endDate" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>
            </div>
            <div class="items-center px-4 py-3">
                <button onclick="generatePDF()" class="px-4 py-2 bg-blue-600 text-white text-base font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Download PDF
                </button>
                <button onclick="closeExportModal()" class="ml-3 px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Medication Details Modal -->
<div id="medDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Medication Details</h3>
                <button onclick="closeMedDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="medDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Document Details Modal -->
<div id="documentDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Document Details</h3>
                <button onclick="closeDocumentDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="documentDetailsContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="mt-4 flex justify-end">
                <a id="documentDownloadBtn" href="#"
                   class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                    Download Document
                </a>
            </div>
        </div>
    </div>



</div>



<style>
.info-item {
    @apply flex justify-between items-center py-2 border-b border-gray-100 last:border-b-0;
}

.info-label {
    @apply text-sm text-gray-600 font-medium;
}

.info-value {
    @apply text-sm font-semibold text-gray-900;
}

.setting-item {
    @apply p-3 bg-gray-50 rounded-xl;
}

.toggle-switch {
    @apply relative inline-block w-11 h-6;
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
    @apply transform translate-x-5;
}

.chart-filter-btn.active {
    @apply bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700;
}

.action-btn {
    @apply w-full flex items-center p-4 rounded-2xl border font-medium transition-all duration-200 hover:shadow-md cursor-pointer text-decoration-none;
}
</style>



@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
<script>



window.jsPDF = window.jspdf.jsPDF;

// Patient monitoring data
let patientData = {
    patient_id: "{{ $patient->id }}",
    patient_name: "{{ $patient->name }}",
    patient_email: "{{ $patient->email }}",
    patient_phone: "{{ $patient->phone ?? 'N/A' }}",
    medical_record_number: "{{ $patient->patient->medical_record_number ?? 'N/A' }}",
    blood_type: "{{ $patient->patient->blood_type ?? 'Unknown' }}",
    vitalsData: @json($vitalsData ?? []),
    realTimeEnabled: true
};
let vitalsChart;

// Initialize patient monitoring
document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    setupRealTimeMonitoring();
    startVitalsPolling();
});

// Initialize the vitals chart
function initializeChart() {
    const ctx = document.getElementById('vitalsChart');
    if (!ctx) return;

    vitalsChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: patientData.vitalsData.labels || [],
            datasets: [{
                label: 'Systolic BP',
                data: patientData.vitalsData.systolic || [],
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
                data: patientData.vitalsData.diastolic || [],
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
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12, family: 'Inter', weight: '600' }
                    }
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

    // Initialize with blood pressure view
    updateChart('blood_pressure');
}

// Update chart based on vital type
function updateChart(vitalType) {
    if (!vitalsChart) return;

    // Update button states
    document.querySelectorAll('.chart-filter-btn').forEach(btn => {
        btn.classList.remove('active');
        btn.className = 'chart-filter-btn bg-gray-100 text-gray-700 rounded-xl px-4 py-2 text-sm font-semibold hover:bg-gray-200 transition-all duration-200';
    });

    event.target.classList.add('active');
    event.target.className = 'chart-filter-btn active bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700 rounded-xl px-4 py-2 text-sm font-semibold';

    // Update chart data based on vital type
    const datasets = vitalsChart.data.datasets;

    switch(vitalType) {
        case 'blood_pressure':
            datasets[0].hidden = false; // Systolic
            datasets[1].hidden = false; // Diastolic
            datasets[0].label = 'Systolic BP';
            datasets[1].label = 'Diastolic BP';
            datasets[0].data = patientData.vitalsData.systolic || [];
            datasets[1].data = patientData.vitalsData.diastolic || [];
            updateInsights('120/80 mmHg', '➡️ Stable', 'Normal');
            break;

        case 'heart_rate':
            datasets[0].hidden = false;
            datasets[1].hidden = true;
            datasets[0].label = 'Heart Rate';
            datasets[0].data = patientData.vitalsData.heart_rate || [];
            datasets[0].borderColor = '#3b82f6';
            datasets[0].backgroundColor = 'rgba(59, 130, 246, 0.1)';
            updateInsights('72 bpm', '↗️ Increasing', 'Good');
            break;

        case 'temperature':
            datasets[0].hidden = false;
            datasets[1].hidden = true;
            datasets[0].label = 'Temperature';
            datasets[0].data = patientData.vitalsData.temperature || [];
            datasets[0].borderColor = '#f59e0b';
            datasets[0].backgroundColor = 'rgba(245, 158, 11, 0.1)';
            updateInsights('98.6°F', '➡️ Stable', 'Normal');
            break;

        case 'oxygen_saturation':
            datasets[0].hidden = false;
            datasets[1].hidden = true;
            datasets[0].label = 'Oxygen Saturation';
            datasets[0].data = patientData.vitalsData.oxygen || [];
            datasets[0].borderColor = '#10b981';
            datasets[0].backgroundColor = 'rgba(16, 185, 129, 0.1)';
            updateInsights('98%', '➡️ Stable', 'Excellent');
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

// Setup real-time monitoring
function setupRealTimeMonitoring() {
    if (window.pusher) {
        const patientChannel = pusher.subscribe(`patient.${patientData.patient_id}`);

        patientChannel.bind('vitals-update', function(data) {
            updateRealTimeVitals(data);
        });

        patientChannel.bind('critical-alert', function(data) {
            handleCriticalAlert(data);
        });
    }
}

// Update real-time vitals display
function updateRealTimeVitals(data) {
    // Update vital cards
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

    // Update chart with new data point
    if (vitalsChart) {
        vitalsChart.data.labels.push(new Date().toLocaleTimeString());

        // Add new data points and keep only last 20
        if (vitalsChart.data.labels.length > 20) {
            vitalsChart.data.labels.shift();
            vitalsChart.data.datasets.forEach(dataset => {
                if (dataset.data.length > 0) dataset.data.shift();
            });
        }

        vitalsChart.update('none');
    }

    // Add to activity timeline
    addActivityToTimeline({
        type: 'Vitals Update',
        description: 'New vital signs reported',
        status: data.status || 'normal',
        time: 'Just now'
    });

    showToast('📊 New vital signs received', 'info');
}

// Handle critical alerts
function handleCriticalAlert(data) {
    // Flash the relevant vital card
    const cardId = getVitalCardId(data.vital_type);
    if (cardId) {
        flashCard(cardId);
    }

    // Add critical alert to timeline
    addActivityToTimeline({
        type: 'Critical Alert',
        description: data.message,
        status: 'critical',
        time: 'Just now'
    });

    showToast(`🚨 Critical Alert: ${data.message}`, 'error', 10000);
}

// Get vital card ID based on type
function getVitalCardId(vitalType) {
    const mapping = {
        'blood_pressure': 'bpCard',
        'heart_rate': 'hrCard',
        'temperature': 'tempCard',
        'oxygen_saturation': 'oxygenCard'
    };
    return mapping[vitalType];
}

// Flash card for alerts
function flashCard(cardId) {
    const card = document.getElementById(cardId);
    if (card) {
        card.classList.add('critical-alert');
        setTimeout(() => {
            card.classList.remove('critical-alert');
        }, 3000);
    }
}

// Add activity to timeline
function addActivityToTimeline(activity) {
    const timeline = document.getElementById('activityTimeline');
    if (!timeline) return;

    const activityHTML = `
        <div class="activity-item flex items-start space-x-4 p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-200 opacity-0" style="animation: slideIn 0.5s ease-out forwards;">
            <div class="flex-shrink-0 mt-1">
                <div class="w-10 h-10 rounded-2xl flex items-center justify-center ${activity.status === 'critical' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-600'}">
                    ${activity.status === 'critical' ?
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>' :
                        '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
                    }
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 mb-1">${activity.type}</p>
                <p class="text-sm text-gray-700 mb-2">${activity.description}</p>
                <p class="text-xs text-gray-500">${activity.time}</p>
            </div>
        </div>
    `;

    timeline.insertAdjacentHTML('afterbegin', activityHTML);

    // Remove oldest activities if more than 10
    const activities = timeline.querySelectorAll('.activity-item');
    if (activities.length > 10) {
        activities[activities.length - 1].remove();
    }
}

// Start vitals polling (fallback for real-time)
function startVitalsPolling() {
    setInterval(async () => {
        try {
            const response = await fetch(`/doctor/patients/${patientData.patient_id}/vitals/latest`, {
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.updated) {
                    updateRealTimeVitals(data.vitals);
                }
            }
        } catch (error) {
            console.error('Error polling vitals:', error);
        }
    }, 30000); // Poll every 30 seconds
}

// Action functions
function acknowledgeAlert(alertId) {
    showToast('✅ Alert acknowledged', 'success');
    // Remove alert from DOM
    setTimeout(() => {
        const alertElement = document.querySelector(`[data-alert-id="${alertId}"]`);
        if (alertElement) alertElement.remove();
    }, 1000);
}

function shareAccess() {
    if (confirm('Generate temporary access link for this patient?')) {
        generateTempAccess();
    }
}

async function generateTempAccess() {
    try {
        const response = await fetch('/doctor/patients/generate-temp-access', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                patient_id: patientData.patient_id,
                expires_hours: 24
            })
        });

        const data = await response.json();

        if (data.success) {
            navigator.clipboard.writeText(data.temp_url);
            showToast('🔗 Temporary access link copied to clipboard!', 'success');
        } else {
            showToast('❌ Failed to generate access link', 'error');
        }
    } catch (error) {
        console.error('Error generating temp access:', error);
        showToast('❌ Error generating access link', 'error');
    }
}

function openExportModal() {
    document.getElementById('exportModal').classList.remove('hidden');
}

function closeExportModal() {
    document.getElementById('exportModal').classList.add('hidden');
}

function generatePDF() {
    try {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Get date range inputs
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;

        // Filter data based on date range
        let filteredData = [...patientData.vitalsData];
        if (startDate) filteredData = filteredData.filter(item => item.date >= startDate);
        if (endDate) filteredData = filteredData.filter(item => item.date <= endDate);

        // Add title and patient info
        doc.setFontSize(20).setFont(undefined, 'bold').text('Patient Vitals Report', 14, 20);
        doc.setFontSize(16).setFont(undefined, 'normal').text(`Patient: ${patientData.patient_name}`, 14, 30);
        doc.setFontSize(11).text(`Generated on: ${new Date().toLocaleString()}`, 14, 40);

        let currentY = 50;

        // Add date range info if filters are applied
        if (startDate || endDate) {
            doc.setFontSize(10);
            let dateRangeText = 'Filtered data: ';
            if (startDate && endDate) dateRangeText += `From ${startDate} to ${endDate}`;
            else if (startDate) dateRangeText += `From ${startDate}`;
            else if (endDate) dateRangeText += `Until ${endDate}`;
            doc.text(dateRangeText, 14, currentY);
            currentY += 10;
        }

        // Add summary table
        doc.setFontSize(14).setFont(undefined, 'bold')
           .text(`Vital Signs Summary (${filteredData.length} records)`, 14, currentY);
        currentY += 10;

        // Create and add summary table
        doc.autoTable({
            head: [['Date', 'BP', 'HR', 'Temp', 'O2 Sat', 'BMI', 'Glucose', 'Status']],
            body: filteredData.map(item => [
                item.datetime, item.bp, item.heartRate,
                item.temperature, item.oxygenSat, item.bmi,
                item.glucose, item.status
            ]),
            startY: currentY,
            styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak' },
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: 'bold' },
            columnStyles: {
                0: { cellWidth: 30 }, 1: { cellWidth: 20 }, 2: { cellWidth: 15 },
                3: { cellWidth: 20 }, 4: { cellWidth: 15 }, 5: { cellWidth: 15 },
                6: { cellWidth: 20 }, 7: { cellWidth: 15 }
            }
        });

        currentY = doc.lastAutoTable.finalY + 20;

        // Add detailed records
        doc.setFontSize(14).setFont(undefined, 'bold')
           .text('Detailed Vital Signs Records', 14, currentY);
        currentY += 15;

        filteredData.forEach((vital, index) => {
            if (currentY > 250) { doc.addPage(); currentY = 20; }

            // Record header
            doc.setFontSize(12).setFont(undefined, 'bold')
               .text(`Record ${index + 1}: ${vital.datetime}`, 14, currentY);
            currentY += 8;
            doc.setFontSize(10).setFont(undefined, 'normal')
               .text(`Status: ${vital.status}`, 14, currentY);
            currentY += 6;

            // Detailed table for this record
            doc.autoTable({
                body: [
                    ['Vital Sign', 'Value', 'Additional Info', 'Notes'],
                    ['Blood Pressure', vital.bp, `Resting HR: ${vital.restingHR} bpm`, ''],
                    ['Heart Rate', `${vital.heartRate} bpm`, '', ''],
                    ['Temperature', vital.temperature, '', ''],
                    ['Oxygen Saturation', vital.oxygenSat, '', ''],
                    ['BMI', vital.bmi, `Weight: ${vital.weight}, Height: ${vital.height}`, ''],
                    ['Blood Glucose', vital.glucose, '', ''],
                    ['Respiratory Rate', `${vital.respiratoryRate} breaths/min`, '', ''],
                    ['Activity Data', `Steps: ${vital.steps}`, `Sleep: ${vital.sleepHours} hours`, ''],
                    ['Wellness', `Mood: ${vital.mood}`, `Energy Level: ${vital.energyLevel}/10`, ''],
                    ['Entry Info', `Method: ${vital.entryMethod}`, `Device: ${vital.deviceType}`, ''],
                    ['Symptoms', vital.symptoms, '', ''],
                    ['Notes', vital.notes, '', '']
                ],
                startY: currentY,
                styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak' },
                headStyles: { fillColor: [52, 152, 219], textColor: 255, fontStyle: 'bold' },
                columnStyles: {
                    0: { cellWidth: 35, fontStyle: 'bold', fillColor: [240, 248, 255] },
                    1: { cellWidth: 40 }, 2: { cellWidth: 50 }, 3: { cellWidth: 55 }
                },
                alternateRowStyles: { fillColor: [248, 249, 250] }
            });

            currentY = doc.lastAutoTable.finalY + 15;
        });

        // Add patient information on new page
        doc.addPage();
        currentY = 20;
        doc.setFontSize(14).setFont(undefined, 'bold')
           .text('Patient Information', 14, currentY);
        currentY += 15;

        doc.autoTable({
            body: [
                ['Field', 'Value'],
                ['Name', patientData.patient_name],
                ['Email', patientData.patient_email],
                ['Phone', patientData.patient_phone],
                ['Medical Record #', patientData.medical_record_number],
                ['Blood Type', patientData.blood_type]
            ],
            startY: currentY,
            styles: { fontSize: 11, cellPadding: 5 },
            headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: 'bold' },
            columnStyles: {
                0: { cellWidth: 60, fontStyle: 'bold', fillColor: [240, 248, 255] },
                1: { cellWidth: 120 }
            }
        });

        // Save the PDF
        const timestamp = new Date().toISOString().slice(0, 10);
        const filename = `patient_vitals_${patientData.patient_name.replace(/[^a-zA-Z0-9]/g, '_')}_${timestamp}.pdf`;
        doc.save(filename);

        closeExportModal();
        setTimeout(() => showToast(`PDF downloaded successfully with ${filteredData.length} records.`, 'success'), 500);

    } catch (error) {
        console.error('PDF generation error:', error);
        showToast(`Error generating PDF: ${error.message}`, 'error');
    }
}

// function generatePDF() {
//     try {
//         const { jsPDF } = window.jspdf;
//         if (!jsPDF) {
//             throw new Error('PDF library not loaded');
//         }

//         const doc = new jsPDF();

//         // Get date range inputs
//         const startDate = document.getElementById('startDate').value;
//         const endDate = document.getElementById('endDate').value;

//         // Filter data based on date range
//         let filteredData = patientData.vitalsData.map(item => ({
//             ...item,
//             date: item.date || new Date().toISOString().split('T')[0],
//             datetime: item.datetime || new Date().toLocaleString(),
//             bp: item.bp || '--/--',
//             heartRate: item.heartRate || '--',
//             temperature: item.temperature || '--',
//             oxygenSat: item.oxygenSat || '--',
//             bmi: item.bmi || '--',
//             glucose: item.glucose || '--',
//             status: item.status || 'normal',
//             restingHR: item.restingHR || '--',
//             weight: item.weight || '--',
//             height: item.height || '--',
//             respiratoryRate: item.respiratoryRate || '--',
//             steps: item.steps || '--',
//             sleepHours: item.sleepHours || '--',
//             mood: item.mood || '--',
//             energyLevel: item.energyLevel || '--',
//             symptoms: item.symptoms || 'None reported',
//             notes: item.notes || 'No notes',
//             entryMethod: item.entryMethod || 'N/A',
//             deviceType: item.deviceType || 'N/A'
//         }));

//         if (startDate) {
//             filteredData = filteredData.filter(item => item.date >= startDate);
//         }
//         if (endDate) {
//             filteredData = filteredData.filter(item => item.date <= endDate);
//         }

//         // Add title and patient info
//         doc.setFontSize(20).setFont(undefined, 'bold').text('Patient Vitals Report', 14, 20);
//         doc.setFontSize(16).setFont(undefined, 'normal').text(`Patient: ${patientData.patient_name || 'Unknown Patient'}`, 14, 30);
//         doc.setFontSize(11).text(`Generated on: ${new Date().toLocaleString()}`, 14, 40);

//         let currentY = 50;

//         // Add date range info if filters are applied
//         if (startDate || endDate) {
//             doc.setFontSize(10);
//             let dateRangeText = 'Filtered data: ';
//             if (startDate && endDate) dateRangeText += `From ${startDate} to ${endDate}`;
//             else if (startDate) dateRangeText += `From ${startDate}`;
//             else if (endDate) dateRangeText += `Until ${endDate}`;
//             doc.text(dateRangeText, 14, currentY);
//             currentY += 10;
//         }

//         // Add summary table
//         doc.setFontSize(14).setFont(undefined, 'bold')
//            .text(`Vital Signs Summary (${filteredData.length} records)`, 14, currentY);
//         currentY += 10;

//         // Create and add summary table
//         doc.autoTable({
//             head: [['Date', 'BP', 'HR', 'Temp', 'O2 Sat', 'BMI', 'Glucose', 'Status']],
//             body: filteredData.map(item => [
//                 item.datetime,
//                 item.bp,
//                 item.heartRate,
//                 item.temperature,
//                 item.oxygenSat,
//                 item.bmi,
//                 item.glucose,
//                 item.status.charAt(0).toUpperCase() + item.status.slice(1)
//             ]),
//             startY: currentY,
//             styles: { fontSize: 8, cellPadding: 2, overflow: 'linebreak' },
//             headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: 'bold' },
//             columnStyles: {
//                 0: { cellWidth: 30 }, 1: { cellWidth: 20 }, 2: { cellWidth: 15 },
//                 3: { cellWidth: 20 }, 4: { cellWidth: 15 }, 5: { cellWidth: 15 },
//                 6: { cellWidth: 20 }, 7: { cellWidth: 15 }
//             }
//         });

//         currentY = doc.lastAutoTable.finalY + 20;

//         // Add detailed records
//         doc.setFontSize(14).setFont(undefined, 'bold')
//            .text('Detailed Vital Signs Records', 14, currentY);
//         currentY += 15;

//         filteredData.forEach((vital, index) => {
//             if (currentY > 250) { doc.addPage(); currentY = 20; }

//             // Record header
//             doc.setFontSize(12).setFont(undefined, 'bold')
//                .text(`Record ${index + 1}: ${vital.datetime}`, 14, currentY);
//             currentY += 8;
//             doc.setFontSize(10).setFont(undefined, 'normal')
//                .text(`Status: ${vital.status.charAt(0).toUpperCase() + vital.status.slice(1)}`, 14, currentY);
//             currentY += 6;

//             // Detailed table for this record
//             doc.autoTable({
//                 body: [
//                     ['Vital Sign', 'Value', 'Additional Info', 'Notes'],
//                     ['Blood Pressure', vital.bp, `Resting HR: ${vital.restingHR} bpm`, ''],
//                     ['Heart Rate', `${vital.heartRate} bpm`, '', ''],
//                     ['Temperature', vital.temperature, '', ''],
//                     ['Oxygen Saturation', vital.oxygenSat, '', ''],
//                     ['BMI', vital.bmi, `Weight: ${vital.weight}, Height: ${vital.height}`, ''],
//                     ['Blood Glucose', vital.glucose, '', ''],
//                     ['Respiratory Rate', `${vital.respiratoryRate} breaths/min`, '', ''],
//                     ['Activity Data', `Steps: ${vital.steps}`, `Sleep: ${vital.sleepHours} hours`, ''],
//                     ['Wellness', `Mood: ${vital.mood}`, `Energy Level: ${vital.energyLevel}/10`, ''],
//                     ['Entry Info', `Method: ${vital.entryMethod}`, `Device: ${vital.deviceType}`, ''],
//                     ['Symptoms', vital.symptoms, '', ''],
//                     ['Notes', vital.notes, '', '']
//                 ],
//                 startY: currentY,
//                 styles: { fontSize: 8, cellPadding: 3, overflow: 'linebreak' },
//                 headStyles: { fillColor: [52, 152, 219], textColor: 255, fontStyle: 'bold' },
//                 columnStyles: {
//                     0: { cellWidth: 35, fontStyle: 'bold', fillColor: [240, 248, 255] },
//                     1: { cellWidth: 40 }, 2: { cellWidth: 50 }, 3: { cellWidth: 55 }
//                 },
//                 alternateRowStyles: { fillColor: [248, 249, 250] }
//             });

//             currentY = doc.lastAutoTable.finalY + 15;
//         });

//         // Add patient information on new page
//         doc.addPage();
//         currentY = 20;
//         doc.setFontSize(14).setFont(undefined, 'bold')
//            .text('Patient Information', 14, currentY);
//         currentY += 15;

//         doc.autoTable({
//             body: [
//                 ['Field', 'Value'],
//                 ['Name', patientData.patient_name || 'Unknown'],
//                 ['Email', patientData.patient_email || 'N/A'],
//                 ['Phone', patientData.patient_phone || 'N/A'],
//                 ['Medical Record #', patientData.medical_record_number || 'N/A'],
//                 ['Blood Type', patientData.blood_type || 'Unknown']
//             ],
//             startY: currentY,
//             styles: { fontSize: 11, cellPadding: 5 },
//             headStyles: { fillColor: [41, 128, 185], textColor: 255, fontStyle: 'bold' },
//             columnStyles: {
//                 0: { cellWidth: 60, fontStyle: 'bold', fillColor: [240, 248, 255] },
//                 1: { cellWidth: 120 }
//             }
//         });

//         // Save the PDF
//         const timestamp = new Date().toISOString().slice(0, 10);
//         const safeName = (patientData.patient_name || 'patient').replace(/[^a-zA-Z0-9]/g, '_');
//         const filename = `patient_vitals_${safeName}_${timestamp}.pdf`;
//         doc.save(filename);

//         closeExportModal();
//         setTimeout(() => showToast(`PDF downloaded successfully with ${filteredData.length} records.`, 'success'), 500);

//     } catch (error) {
//         console.error('PDF generation error:', error);
//         showToast(`Error generating PDF: ${error.message}`, 'error');
//     }
// }

function showMedDetails(button) {
    const medData = JSON.parse(button.getAttribute('data-med'));
    const modalContent = document.getElementById('medDetailsContent');

    modalContent.innerHTML = `
        <div class="space-y-4">
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Medication Information</h4>
                <p><span class="font-medium">Name:</span> ${medData.name}</p>
                ${medData.generic_name ? `<p><span class="font-medium">Generic Name:</span> ${medData.generic_name}</p>` : ''}
                <p><span class="font-medium">Dosage:</span> ${medData.dosage}</p>
                <p><span class="font-medium">Frequency:</span> ${medData.frequency}</p>
                ${medData.times ? `<p><span class="font-medium">Times:</span> ${medData.times_display}</p>` : ''}
                <p><span class="font-medium">Start Date:</span> ${new Date(medData.start_date).toLocaleDateString()}</p>
                <p><span class="font-medium">Status:</span>
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${medData.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                        ${medData.status.charAt(0).toUpperCase() + medData.status.slice(1)}
                    </span>
                </p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Prescription Details</h4>
                ${medData.prescribed_by ? `<p><span class="font-medium">Prescribed By:</span> ${medData.prescribed_by}</p>` : ''}
                ${medData.prescription_date ? `<p><span class="font-medium">Prescription Date:</span> ${new Date(medData.prescription_date).toLocaleDateString()}</p>` : ''}
                ${medData.refills ? `<p><span class="font-medium">Refills:</span> ${medData.refills}</p>` : ''}
            </div>
        </div>

        <div class="space-y-4">
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Instructions</h4>
                ${medData.instructions ? `<p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">${medData.instructions}</p>` : '<p class="text-sm text-gray-500 italic">No special instructions</p>'}
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Notes</h4>
                ${medData.notes ? `<p class="text-sm text-gray-700 bg-blue-50 p-3 rounded-lg border border-blue-200">${medData.notes}</p>` : '<p class="text-sm text-gray-500 italic">No additional notes</p>'}
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Side Effects</h4>
                ${medData.side_effects ? `<p class="text-sm text-red-700 bg-red-50 p-3 rounded-lg border border-red-200">${medData.side_effects}</p>` : '<p class="text-sm text-gray-500 italic">No side effects reported</p>'}
            </div>
        </div>
    `;

    document.getElementById('medDetailsModal').classList.remove('hidden');
}

function closeMedDetailsModal() {
    document.getElementById('medDetailsModal').classList.add('hidden');
}

function showDocumentDetails(button) {
    const docData = JSON.parse(button.getAttribute('data-document'));
    const modalContent = document.getElementById('documentDetailsContent');
    const downloadBtn = document.getElementById('documentDownloadBtn');

    modalContent.innerHTML = `
        <div class="space-y-4">
            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Document Information</h4>
                <p><span class="font-medium">Title:</span> ${docData.title}</p>
                <p><span class="font-medium">Category:</span> ${docData.category}</p>
                <p><span class="font-medium">Uploaded:</span> ${new Date(docData.created_at).toLocaleString()}</p>
                <p><span class="font-medium">File:</span> ${docData.file_name} (${docData.file_size_human})</p>
                <p><span class="font-medium">Type:</span> ${docData.file_type}</p>
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Description</h4>
                ${docData.description ? `<p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-lg">${docData.description}</p>` : '<p class="text-sm text-gray-500 italic">No description provided</p>'}
            </div>

            <div>
                <h4 class="font-semibold text-gray-800 mb-2">Preview</h4>
                ${docData.file_type.includes('image') ?
                    `<img src="/storage/${docData.file_path}" alt="${docData.title}" class="max-w-full h-auto rounded-lg border border-gray-200">` :
                    `<div class="bg-gray-100 p-8 rounded-lg text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600">Preview not available for this file type</p>
                    </div>`
                }
            </div>
        </div>
    `;

    downloadBtn.href = `/doctor/patients/${patientData.patient_id}/documents/${docData.id}/download`;
    document.getElementById('documentDetailsModal').classList.remove('hidden');
}

function closeDocumentDetailsModal() {
    document.getElementById('documentDetailsModal').classList.add('hidden');
}

function addNote() {
    showToast('📝 Clinical notes feature coming soon!', 'info');
}

function prescribeMedication() {
    showToast('💊 Prescription feature coming soon!', 'info');
}

function scheduleAppointment() {
    showToast('📅 Appointment scheduling coming soon!', 'info');
}

function sendMessage() {
    showToast('💬 Messaging feature coming soon!', 'info');
}

function viewActivityDetails(activityId) {
    showToast('📋 Activity details coming soon!', 'info');
}

// function exportVitals() {
//     showToast('📊 Exporting vitals data...', 'info');

//     // Create CSV data
//     const csvData = [
//         ['Date', 'Time', 'Blood Pressure', 'Heart Rate', 'Temperature', 'Oxygen Saturation', 'Weight', 'Status', 'Notes']
//     ];

//     // Add sample data (in real app, this would come from the server)
//     csvData.push([
//         new Date().toLocaleDateString(),
//         new Date().toLocaleTimeString(),
//         '120/80',
//         '72',
//         '98.6',
//         '98',
//         '150',
//         'Normal',
//         'Patient feeling well'
//     ]);

//     // Convert to CSV string
//     const csvString = csvData.map(row =>
//         row.map(cell => `"${cell}"`).join(',')
//     ).join('\n');

//     // Download file
//     const blob = new Blob([csvString], { type: 'text/csv' });
//     const url = window.URL.createObjectURL(blob);
//     const a = document.createElement('a');
//     a.href = url;
//     a.download = `${patientData.patient_id}-vitals-${new Date().toISOString().split('T')[0]}.csv`;
//     document.body.appendChild(a);
//     a.click();
//     document.body.removeChild(a);
//     window.URL.revokeObjectURL(url);

//     showToast('📊 Vitals data exported successfully!', 'success');
//}

function exportVitals() {
    try {
        // Check if jsPDF is loaded
        if (typeof jsPDF === 'undefined') {
            throw new Error('PDF library not loaded. Please refresh the page and try again.');
        }

        const doc = new jsPDF();

        // Safely get patient name
        const patientNameElement = document.querySelector('.medical-card h1');
        if (!patientNameElement) {
            throw new Error('Could not find patient information on the page');
        }
        const patientName = patientNameElement.textContent || 'Unknown Patient';

        // Get all vitals rows from the table
        const vitalsTable = document.querySelector('.medical-card table');
        if (!vitalsTable) {
            throw new Error('No vitals data table found on the page');
        }

        const vitalsRows = vitalsTable.querySelectorAll('tbody tr');
        if (vitalsRows.length === 0) {
            showToast('No vitals data available to export', 'warning');
            return;
        }

        // Add title and patient info
        doc.setFontSize(20).setFont(undefined, 'bold').text('Patient Vitals Report', 14, 20);
        doc.setFontSize(16).setFont(undefined, 'normal').text(`Patient: ${patientName}`, 14, 30);
        doc.setFontSize(11).text(`Generated on: ${new Date().toLocaleString()}`, 14, 40);

        let currentY = 50;

        // Prepare table data
        const tableData = [];

        // Get headers from the table
        const headerCells = vitalsTable.querySelectorAll('thead th');
        const headers = Array.from(headerCells).map(header => header.textContent.trim());

        // Process each row
        vitalsRows.forEach(row => {
            const rowData = [];
            const cells = row.querySelectorAll('td');

            cells.forEach((cell, index) => {
                // For status cells, get the text without the status indicator
                if (index === 6) { // Status column
                    const statusText = cell.querySelector('.status-badge')?.textContent.trim() || '--';
                    rowData.push(statusText);
                } else {
                    rowData.push(cell.textContent.trim());
                }
            });

            tableData.push(rowData);
        });

        // Add vitals table
        doc.autoTable({
            head: [headers],
            body: tableData,
            startY: currentY,
            styles: {
                fontSize: 8,
                cellPadding: 2,
                overflow: 'linebreak',
                valign: 'middle'
            },
            headStyles: {
                fillColor: [41, 128, 185],
                textColor: 255,
                fontStyle: 'bold',
                valign: 'middle'
            },
            columnStyles: {
                0: { cellWidth: 25 }, // Date
                1: { cellWidth: 20 }, // BP
                2: { cellWidth: 15 }, // HR
                3: { cellWidth: 15 }, // Temp
                4: { cellWidth: 15 }, // O2 Sat
                5: { cellWidth: 15 }, // Weight
                6: { cellWidth: 15 }, // Status
                7: { cellWidth: 30 }  // Notes
            },
            margin: { top: 10 },
            didDrawPage: function (data) {
                // Add footer
                doc.setFontSize(10);
                doc.setTextColor(150);
                doc.text(`Page ${data.pageCount}`, data.settings.margin.left, doc.internal.pageSize.height - 10);
            }
        });

        // Save the PDF
        const timestamp = new Date().toISOString().slice(0, 10);
        const safeName = patientName.replace(/[^a-zA-Z0-9]/g, '_');
        const filename = `patient_vitals_${safeName}_${timestamp}.pdf`;
        doc.save(filename);

        showToast('PDF report generated successfully', 'success');
    } catch (error) {
        console.error('PDF generation error:', error);
        showToast(`Error generating PDF: ${error.message}`, 'error');
    }
}

// CSS Animation
const style = document.createElement('style');
style.textContent = `
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
`;
document.head.appendChild(style);

function showVitalDetails(rowElement) {
    // Get the vital data from the data attribute
    const vitalData = JSON.parse(rowElement.getAttribute('data-vital'));

    // Format the modal content
    const modalContent = document.getElementById('vitalDetailsContent');
    modalContent.innerHTML = `
        <div class="space-y-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h4 class="font-semibold text-blue-800 mb-2">Basic Information</h4>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-sm text-gray-600">Date & Time:</span>
                        <p class="font-medium">${new Date(vitalData.measured_at).toLocaleString()}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Status:</span>
                        <p class="font-medium">
                            <span class="status-badge ${vitalData.status || 'normal'}">
                                <div class="status-indicator status-${vitalData.status || 'normal'}"></div>
                                ${vitalData.status ? vitalData.status.charAt(0).toUpperCase() + vitalData.status.slice(1) : 'Normal'}
                            </span>
                        </p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Recorded By:</span>
                        <p class="font-medium">${vitalData.recorded_by || 'Patient'}</p>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600">Device:</span>
                        <p class="font-medium">${vitalData.device || 'Unknown'}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-2">Vital Measurements</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">Blood Pressure:</span>
                        <p class="text-xl font-bold">${vitalData.blood_pressure || '--/--'} mmHg</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">Heart Rate:</span>
                        <p class="text-xl font-bold">${vitalData.heart_rate || '--'} bpm</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">Temperature:</span>
                        <p class="text-xl font-bold">${vitalData.temperature || '--'}°F</p>
                    </div>
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">Oxygen Saturation:</span>
                        <p class="text-xl font-bold">${vitalData.oxygen_saturation || '--'}%</p>
                    </div>
                    ${vitalData.respiratory_rate ? `
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">Respiratory Rate:</span>
                        <p class="text-xl font-bold">${vitalData.respiratory_rate} breaths/min</p>
                    </div>
                    ` : ''}
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">Weight:</span>
                        <p class="text-xl font-bold">${vitalData.weight || '--'} lbs</p>
                    </div>
                    ${vitalData.height ? `
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">Height:</span>
                        <p class="text-xl font-bold">${vitalData.height} in</p>
                    </div>
                    ` : ''}
                    ${vitalData.bmi ? `
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <span class="text-sm text-gray-600">BMI:</span>
                        <p class="text-xl font-bold">${vitalData.bmi}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-2">Additional Information</h4>
                <div class="grid grid-cols-2 gap-4">
                    ${vitalData.blood_glucose ? `
                    <div>
                        <span class="text-sm text-gray-600">Blood Glucose:</span>
                        <p class="font-medium">${vitalData.blood_glucose} mg/dL</p>
                    </div>
                    ` : ''}
                    ${vitalData.pain_level ? `
                    <div>
                        <span class="text-sm text-gray-600">Pain Level:</span>
                        <p class="font-medium">${vitalData.pain_level}/10</p>
                    </div>
                    ` : ''}
                    ${vitalData.activity_level ? `
                    <div>
                        <span class="text-sm text-gray-600">Activity Level:</span>
                        <p class="font-medium">${vitalData.activity_level.charAt(0).toUpperCase() + vitalData.activity_level.slice(1)}</p>
                    </div>
                    ` : ''}
                    ${vitalData.sleep_hours ? `
                    <div>
                        <span class="text-sm text-gray-600">Sleep Hours:</span>
                        <p class="font-medium">${vitalData.sleep_hours} hours</p>
                    </div>
                    ` : ''}
                </div>
            </div>

            ${vitalData.symptoms ? `
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-2">Symptoms</h4>
                <p class="text-sm text-gray-700">${vitalData.symptoms}</p>
            </div>
            ` : ''}

            ${vitalData.notes ? `
            <div class="bg-white p-4 rounded-lg border border-gray-200">
                <h4 class="font-semibold text-gray-800 mb-2">Notes</h4>
                <p class="text-sm text-gray-700">${vitalData.notes}</p>
            </div>
            ` : ''}
        </div>
    `;

    document.getElementById('vitalDetailsModal').classList.remove('hidden');
}


function closeVitalDetailsModal() {
    document.getElementById('vitalDetailsModal').classList.add('hidden');
}



// Document Upload Form Handling
// Document Upload Form Handling
document.getElementById('documentUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;

    try {
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = `Loading...`;

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to upload document');
        }

        showToast('✅ Document uploaded successfully!', 'success');
        form.reset();
        closeDocumentUploadModal();

        // Refresh page after 1.5 seconds
        setTimeout(() => window.location.reload(), 1500);

    } catch (error) {
        console.error('Upload error:', error);
        showToast(`❌ Error: ${error.message}`, 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
});

// Toast Notification Function
function showToast(message, type = 'info', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-6 py-4 rounded-md shadow-lg text-white ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        type === 'warning' ? 'bg-yellow-500' :
        'bg-blue-500'
    }`;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        setTimeout(() => toast.remove(), 500);
    }, duration);
}

</script>

<script>
// Set CSRF token when page loads
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const tokenField = document.getElementById('csrf_token');
        if (tokenField) {
            tokenField.value = csrfToken;
        }
    }
});

// Open document upload modal
function openDocumentUploadModal() {
    document.getElementById('documentUploadModal').classList.remove('hidden');
}

// Close document upload modal
function closeDocumentUploadModal() {
    document.getElementById('documentUploadModal').classList.add('hidden');
    // Reset form
    const form = document.getElementById('documentUploadForm');
    if (form) {
        form.reset();
        // Re-enable submit button if it was disabled
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = `
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload Document
            `;
        }
    }
}

// Flag to prevent multiple submissions
let isSubmitting = false;

// Handle document upload form submission
async function handleDocumentUpload(formData) {
    try {
        // Log form data for debugging
        for (let [key, value] of formData.entries()) {
            console.log(`${key}:`, value);
        }

        const response = await fetch('/doctor/documents/upload', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (!response.ok) {
            console.error('Validation errors:', data.errors);
            throw new Error('Validation error');
        }

        console.log('Upload successful:', data);
        // Handle success (e.g., update UI)
    } catch (error) {
        console.error('Upload error:', error);
    }
}

// Attach event listener only once when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('documentUploadForm');
    if (form) {
        // Remove any existing listeners first
        form.removeEventListener('submit', handleDocumentUpload);
        // Add the listener
        form.addEventListener('submit', handleDocumentUpload);
    }
});

// Toast notification function
function showToast(message, type = 'info') {
    // Remove any existing toasts first
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification fixed top-4 right-4 p-4 rounded-md shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    toast.textContent = message;

    document.body.appendChild(toast);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);

    // Allow manual removal by clicking
    toast.addEventListener('click', () => {
        if (toast.parentNode) {
            toast.remove();
        }
    });
}
</script>



<script>
// Global variables
let doctorDocumentsData = [];

// Open documents modal
function openDoctorDocumentsModal() {
    document.getElementById('doctorDocumentsModal').classList.remove('hidden');
    loadDoctorDocuments();
}

// Close documents modal
function closeDoctorDocumentsModal() {
    document.getElementById('doctorDocumentsModal').classList.add('hidden');
    doctorDocumentsData = [];
}

// Load documents from server
async function loadDoctorDocuments() {
    showDoctorDocumentsLoading();

    try {
        const response = await fetch('{{ route("doctor.documents.index") }}?user_id={{ $patient->id ?? '' }}', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        doctorDocumentsData = data.documents || [];
        displayDoctorDocuments();

    } catch (error) {
        console.error('Error loading documents:', error);
        showDoctorDocumentsError('Failed to load documents. Please try again.');
        showDoctorDocumentsEmpty();
    }
}

// Display documents in grid
function displayDoctorDocuments() {
    hideDoctorDocumentsLoading();

    if (doctorDocumentsData.length === 0) {
        showDoctorDocumentsEmpty();
        return;
    }

    showDoctorDocumentsGrid();
    const container = document.getElementById('doctorDocumentsContainer');
    container.innerHTML = '';

    doctorDocumentsData.forEach(doc => {
        const card = createDoctorDocumentCard(doc);
        container.appendChild(card);
    });

    updateDoctorDocumentsCount();
}

// Create a single document card
function createDoctorDocumentCard(doc) {
    const card = document.createElement('div');
    card.className = 'border rounded-lg p-4 hover:shadow-md transition-shadow';

    const fileSize = formatFileSize(doc.file_size);
    const uploadDate = new Date(doc.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const fileIcon = getFileIcon(doc.file_type);

    card.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                ${fileIcon}
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-sm font-medium text-gray-900">${escapeHtml(doc.title)}</h3>
                <p class="text-xs text-gray-500">${escapeHtml(doc.category)}</p>
                <p class="text-xs text-gray-400">${escapeHtml(doc.file_name)} (${fileSize})</p>
                <p class="text-xs text-gray-400 mt-1">${uploadDate}</p>
                ${doc.description ? `<p class="text-xs text-gray-600 mt-1">${escapeHtml(doc.description.substring(0, 60))}${doc.description.length > 60 ? '...' : ''}</p>` : ''}
            </div>
        </div>
        <div class="mt-3 flex justify-end space-x-2">
            <button onclick="downloadDoctorDocument('${doc.id}')" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Download
            </button>
            <button onclick="showDoctorDocumentDetails('${doc.id}')" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                Details
            </button>
            <button onclick="deleteDoctorDocument('${doc.id}')" class="text-red-600 hover:text-red-800 text-sm font-medium">
                Delete
            </button>
        </div>
    `;

    return card;
}

// Get file icon based on type
function getFileIcon(fileType) {
    if (fileType && fileType.includes('image')) {
        return `<svg class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>`;
    } else if (fileType === 'application/pdf') {
        return `<svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
        </svg>`;
    } else {
        return `<svg class="h-10 w-10 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>`;
    }
}

// Utility functions
function formatFileSize(bytes) {
    if (!bytes) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function updateDoctorDocumentsCount() {
    const count = doctorDocumentsData.length;
    const countElement = document.getElementById('doctorDocumentsCount');
    countElement.textContent = `${count} document${count !== 1 ? 's' : ''}`;
}

// State management
function showDoctorDocumentsLoading() {
    document.getElementById('doctorDocumentsLoading').classList.remove('hidden');
    document.getElementById('doctorDocumentsEmpty').classList.add('hidden');
    document.getElementById('doctorDocumentsGrid').classList.add('hidden');
}

function hideDoctorDocumentsLoading() {
    document.getElementById('doctorDocumentsLoading').classList.add('hidden');
}

function showDoctorDocumentsEmpty() {
    document.getElementById('doctorDocumentsEmpty').classList.remove('hidden');
    document.getElementById('doctorDocumentsGrid').classList.add('hidden');
}

function showDoctorDocumentsGrid() {
    document.getElementById('doctorDocumentsGrid').classList.remove('hidden');
    document.getElementById('doctorDocumentsEmpty').classList.add('hidden');
}

function showDoctorDocumentsError(message) {
    hideDoctorDocumentsLoading();
    alert(message);
    showDoctorDocumentsEmpty();
}

// Action functions
function downloadDoctorDocument(docId) {
    const doc = doctorDocumentsData.find(d => d.id === docId);
    if (!doc) return;

    window.open(`/doctor/documents/${docId}/download`, '_blank');
}

function showDoctorDocumentDetails(docId) {
    const doc = doctorDocumentsData.find(d => d.id === docId);
    if (!doc) return;

    const modal = document.getElementById('doctorDocumentDetailsModal');
    const content = document.getElementById('doctorDocumentDetailsContent');

    const uploadDate = new Date(doc.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    content.innerHTML = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.title)}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.category)}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">File Name</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.file_name)}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">File Type</label>
                    <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.file_type)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">File Size</label>
                    <p class="mt-1 text-sm text-gray-900">${formatFileSize(doc.file_size)}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Upload Date</label>
                <p class="mt-1 text-sm text-gray-900">${uploadDate}</p>
            </div>
            ${doc.description ? `
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.description)}</p>
            </div>
            ` : ''}
            <div class="flex justify-end space-x-2 pt-4">
                <button onclick="downloadDoctorDocument('${doc.id}')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Download
                </button>
                <button onclick="closeDoctorDocumentDetailsModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
}

function closeDoctorDocumentDetailsModal() {
    document.getElementById('doctorDocumentDetailsModal').classList.add('hidden');
}

async function deleteDoctorDocument(docId) {
    if (!confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/doctor/documents/${docId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to delete document');
        }

        doctorDocumentsData = doctorDocumentsData.filter(doc => doc.id !== docId);
        displayDoctorDocuments();

    } catch (error) {
        console.error('Error deleting document:', error);
        alert('Failed to delete document. Please try again.');
    }
}

function refreshDoctorDocuments() {
    loadDoctorDocuments();
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to any button that should open the modal
    document.querySelectorAll('[data-open-doctor-documents]').forEach(button => {
        button.addEventListener('click', openDoctorDocumentsModal);
    });
});
</script>

<script>
console.log("Patient User ID:", "{{ $patient->user_id }}");
console.log("Patient User ID Type:", typeof "{{ $patient->user_id }}");
</script>

<script>
// Medication Modal Functions
function openPrescribeMedicationModal() {
    // Get patient ID from URL (format: /doctor/patients/{id}/monitor)
    const pathParts = window.location.pathname.split('/');
    const patientId = pathParts[3]; // Gets the ID part

    // Set the patient ID in the form
    document.getElementById('patientIdInput').value = patientId;

    // Show modal
    document.getElementById('prescribeMedicationModal').classList.remove('hidden');
}

function closePrescribeMedicationModal() {
    document.getElementById('prescribeMedicationModal').classList.add('hidden');
    document.getElementById('prescribeMedicationForm').reset();
}

// Health Tips Modal Functions
function openHealthTipsModal() {
    document.getElementById('healthTipsModal').classList.remove('hidden');
}

function closeHealthTipsModal() {
    document.getElementById('healthTipsModal').classList.add('hidden');
    document.getElementById('healthTipsForm').reset();
}

// Show/hide times checkboxes based on frequency selection
document.getElementById('frequency').addEventListener('change', function() {
    const timesContainer = document.getElementById('timesContainer');
    if (this.value === 'Once daily' || this.value === 'Twice daily' ||
        this.value === 'Three times daily' || this.value === 'Four times daily') {
        timesContainer.classList.remove('hidden');
    } else {
        timesContainer.classList.add('hidden');
    }
});

// Handle medication form submission
document.getElementById('prescribeMedicationForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;

    try {
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Prescribing...
        `;

        const formData = new FormData(form);
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            // Log the full error response
            console.error('Server Error Response:', data);
            let errorMessage = data.message || 'Failed to prescribe medication';

            // Include validation errors if they exist
            if (data.errors) {
                errorMessage += ': ' + Object.values(data.errors).join(', ');
            }

            throw new Error(errorMessage);
        }

        showToast('✅ Medication prescribed successfully!', 'success');
        closePrescribeMedicationModal();

        // Refresh medications list
        loadPatientMedications();

    } catch (error) {
        console.error('Error:', error);
        showToast(`❌ Error: ${error.message}`, 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
});
// Handle health tips form submission
document.getElementById('healthTipsForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton.innerHTML;

    try {
        // Show loading state
        submitButton.disabled = true;
        submitButton.innerHTML = `
            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving...
        `;

        // Get patient ID from URL
        const pathParts = window.location.pathname.split('/');
        const patientId = pathParts[3]; // Gets the ID part

        // Create form data
        const formData = new FormData(form);
        formData.append('user_id', patientId);

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            let errorMessage = data.message || 'Failed to save health tips';
            if (data.errors) {
                errorMessage += ': ' + Object.values(data.errors).join(', ');
            }
            throw new Error(errorMessage);
        }

        showToast('✅ Health tips saved successfully!', 'success');
        closeHealthTipsModal();

        // Refresh health tips list
        loadPatientHealthTips();

    } catch (error) {
        console.error('Error:', error);
        showToast(`❌ Error: ${error.message}`, 'error');
    } finally {
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
    }
});

// Load patient medications
async function loadPatientMedications() {
    try {
        const patientId = document.querySelector('input[name="user_id"]').value;
        const response = await fetch(`/doctor/patients/${patientId}/medications`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load medications');
        }

        const data = await response.json();
        // Update your medications display here
        console.log('Medications loaded:', data.medications);

    } catch (error) {
        console.error('Error loading medications:', error);
    }
}

// Load patient health tips
async function loadPatientHealthTips() {
    try {
        const patientId = document.querySelector('input[name="user_id"]').value;
        const response = await fetch(`/doctor/patients/${patientId}/health-tips`, {
            headers: {
                'Accept': 'application/json'
            }
        });

        if (!response.ok) {
            throw new Error('Failed to load health tips');
        }

        const data = await response.json();
        // Update your health tips display here
        console.log('Health tips loaded:', data.health_tips);

    } catch (error) {
        console.error('Error loading health tips:', error);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Load initial data
    loadPatientMedications();
    loadPatientHealthTips();

    // Add event listeners to buttons that open modals
    document.querySelectorAll('[data-prescribe-medication]').forEach(button => {
        button.addEventListener('click', openPrescribeMedicationModal);
    });

    document.querySelectorAll('[data-add-health-tips]').forEach(button => {
        button.addEventListener('click', openHealthTipsModal);
    });
});
</script>
@endpush
