@extends('doctor.layout')

@section('title', 'Dashboard')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                        Good {{ now()->format('A') === 'AM' ? 'Morning' : 'Afternoon' }}, Dr. {{ auth()->user()->name }}
                    </h1>
                    <p class="text-gray-600 text-lg">{{ now()->format('l, F j, Y') }} • Patient Overview Dashboard</p>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">
                    <button onclick="refreshDashboard()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    <a href="{{ route('doctor.patients.index') }}" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m-9 5.197v1a6 6 0 0010.967 0M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        View All Patients
                    </a>
                </div>
            </div>
        </div>

        <!-- Critical Alerts Section -->
        @if(isset($criticalPatients) && $criticalPatients->count() > 0)
        <div class="critical-alert medical-card p-8 border-red-300">
            <div class="flex items-center mb-6">
                <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h2 class="text-2xl font-bold text-white">🚨 Critical Patients Requiring Immediate Attention</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($criticalPatients as $patient)
                <div class="bg-white rounded-2xl p-6 border-2 border-red-300 hover:shadow-xl transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <span class="text-red-600 font-bold text-lg">{{ strtoupper(substr($patient->name, 0, 1)) }}</span>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $patient->name }}</h4>
                                <p class="text-sm text-gray-600">{{ $patient->patient->medical_record_number ?? 'No MRN' }}</p>
                            </div>
                        </div>
                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full pulse">CRITICAL</span>
                    </div>

                    <div class="space-y-2 mb-4">
                        <p class="text-sm text-red-700 font-medium">{{ $patient->latest_alert ?? 'Critical vital signs detected' }}</p>
                        <p class="text-xs text-gray-500">{{ $patient->alert_time ?? 'Recently updated' }}</p>
                    </div>

                    <div class="flex space-x-2">
                        <a href="{{ route('doctor.patients.monitor', $patient->id) }}"
                           class="flex-1 bg-red-600 text-white text-center py-2 px-4 rounded-xl hover:bg-red-700 transition-colors font-semibold text-sm">
                            View Now
                        </a>
                        <button onclick="callPatient({{ $patient->id }})"
                                class="bg-red-100 text-red-600 px-4 py-2 rounded-xl hover:bg-red-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Key Statistics -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m-9 5.197v1a6 6 0 0010.967 0M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="totalPatientsCount">{{ $totalPatients ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Total Patients</div>
                <div class="text-xs text-gray-400 mt-1">Under your care</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="activeTodayCount">{{ $activeToday ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Active Today</div>
                <div class="text-xs text-gray-400 mt-1">Reported vitals</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="criticalAlertsCount">{{ $criticalAlerts ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Critical Alerts</div>
                <div class="text-xs text-gray-400 mt-1">Require attention</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="pendingReviewsCount">{{ $pendingReviews ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Pending Reviews</div>
                <div class="text-xs text-gray-400 mt-1">Awaiting review</div>
            </div>
        </div>

        <!-- Main Dashboard Content -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Recent Patient Activity -->
            <div class="xl:col-span-2">
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Recent Patient Activity</h3>
                                <p class="text-gray-600">Latest updates from your patients</p>
                            </div>
                            <div class="flex items-center space-x-2 text-sm text-gray-500">
                                <div class="status-indicator status-normal"></div>
                                <span>Live Updates</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="space-y-6" id="recentActivitiesList">
                            @forelse($recentActivities ?? [] as $activity)
                            <div class="activity-item flex items-start space-x-4 p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-200 hover:shadow-md transition-all duration-200">
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                                        <span class="text-blue-600 font-bold">{{ strtoupper(substr($activity->patient_name ?? 'P', 0, 1)) }}</span>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="font-bold text-gray-900">{{ $activity->patient_name ?? 'Patient' }}</h4>
                                        <span class="text-xs text-gray-500">{{ $activity->time ?? 'Just now' }}</span>
                                    </div>

                                    <p class="text-sm text-gray-700 mb-2">{{ $activity->activity ?? 'Updated vital signs' }}</p>

                                    <div class="flex items-center justify-between">
                                        <span class="px-3 py-1 text-xs rounded-full font-semibold
                                            @if(($activity->status ?? 'normal') === 'normal') bg-green-100 text-green-800
                                            @elseif(($activity->status ?? 'normal') === 'warning') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst($activity->status ?? 'normal') }}
                                        </span>

                                        <div class="flex space-x-2">
                                            <button onclick="viewPatient({{ $activity->patient_id ?? 0 }})" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                                                View
                                            </button>
                                            <button onclick="respondToActivity({{ $activity->id ?? 0 }})" class="text-green-600 hover:text-green-700 text-xs font-medium">
                                                Respond
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12">
                                <div class="text-6xl mb-4">📊</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">No Recent Activity</h3>
                                <p class="text-gray-600">Patient updates will appear here as they come in</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

<!-- Quick Actions -->
                <<!-- Quick Actions -->
<div class="medical-card p-6 bg-white rounded-xl shadow-md">
    <div class="mb-6 border-b pb-4">
        <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
            ⚡ Quick Actions
        </h3>
        <p class="text-sm text-gray-500">Common tasks you can perform instantly</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
        <!-- Add New Patient -->
        <button onclick="addNewPatient()" class="flex items-center px-4 py-3 rounded-lg border bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 text-blue-700 hover:shadow hover:scale-[1.02] transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
            </svg>
            Add New Patient
        </button>

        <!-- View Analytics -->
        <a href="{{ route('doctor.analytics') }}" class="flex items-center px-4 py-3 rounded-lg border bg-gradient-to-r from-green-50 to-emerald-50 border-green-200 text-green-700 hover:shadow hover:scale-[1.02] transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
            </svg>
            View Analytics
        </a>

        <!-- Generate Report -->
        <button onclick="generateReport()" class="flex items-center px-4 py-3 rounded-lg border bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200 text-purple-700 hover:shadow hover:scale-[1.02] transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Generate Report
        </button>

        <!-- Emergency Protocol -->
        <button onclick="emergencyProtocol()" class="flex items-center px-4 py-3 rounded-lg border bg-gradient-to-r from-red-50 to-pink-50 border-red-200 text-red-700 hover:shadow hover:scale-[1.02] transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            Emergency Protocol
        </button>
    </div>
</div>


                <!-- Today's Schedule -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">📅 Today's Schedule</h3>
                        <p class="text-sm text-gray-600">{{ now()->format('M d, Y') }}</p>
                    </div>

                    <div class="space-y-4">
                        @forelse($todaySchedule ?? [] as $appointment)
                        <div class="schedule-item p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl border border-blue-100">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-semibold text-blue-900">{{ $appointment->time ?? '9:00 AM' }}</span>
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-medium">
                                    {{ $appointment->status ?? 'Scheduled' }}
                                </span>
                            </div>
                            <h4 class="font-medium text-gray-900 mb-1">{{ $appointment->patient_name ?? 'Patient Consultation' }}</h4>
                            <p class="text-sm text-gray-600">{{ $appointment->type ?? 'Regular checkup' }}</p>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="text-4xl mb-2">🗓️</div>
                            <p class="text-gray-500 text-sm">No appointments scheduled</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Stats Chart -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">📈 Patient Overview</h3>
                        <p class="text-sm text-gray-600">This week's summary</p>
                    </div>

                    <div class="relative h-48 mb-4">
                        <canvas id="weeklyStatsChart"></canvas>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Normal Status:</span>
                            <span class="font-semibold text-green-600">{{ $weeklyStats['normal'] ?? '85%' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Requires Attention:</span>
                            <span class="font-semibold text-yellow-600">{{ $weeklyStats['warning'] ?? '12%' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Critical:</span>
                            <span class="font-semibold text-red-600">{{ $weeklyStats['critical'] ?? '3%' }}</span>
                        </div>
                    </div>
                </div>


            </div>
        </div>

        <!-- Recent Documents and Communications -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Documents -->
            <!-- <div class="medical-card">


                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($recentDocuments ?? [] as $document)
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-2xl hover:bg-gray-100 transition-colors">
                            <div class="text-2xl">{{ $document->file_icon ?? '📄' }}</div>
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $document->title ?? 'Medical Report' }}</h4>
                                <p class="text-sm text-gray-600">{{ $document->patient_name ?? 'Patient Name' }}</p>
                                <p class="text-xs text-gray-500">{{ $document->created_at ?? 'Recently uploaded' }}</p>
                            </div>
                            <button onclick="viewDocument({{ $document->id ?? 0 }})" class="text-blue-600 hover:text-blue-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="text-4xl mb-2">📄</div>
                            <p class="text-gray-500 text-sm">No recent documents</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div> -->

            <!-- Communication Center -->
            <!-- <div class="medical-card">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900">💬 Communication Center</h3>
                        <button onclick="openCommunicationCenter()" class="text-blue-600 hover:text-blue-700 text-sm font-semibold">
                            Open →
                        </button>
                    </div>
                </div>

                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($recentMessages ?? [] as $message)
                        <div class="message-item p-4 bg-gray-50 rounded-2xl">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-bold text-sm">{{ strtoupper(substr($message->patient_name ?? 'P', 0, 1)) }}</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <span class="font-medium text-gray-900">{{ $message->patient_name ?? 'Patient' }}</span>
                                        <span class="text-xs text-gray-500">{{ $message->time ?? 'Now' }}</span>
                                    </div>
                                    <p class="text-sm text-gray-700">{{ $message->message ?? 'Patient message content here...' }}</p>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="text-4xl mb-2">💬</div>
                            <p class="text-gray-500 text-sm">No recent messages</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div> -->
        </div>
    </div>
</div>

<style>
.action-btn {
    @apply w-full flex items-center p-4 rounded-2xl border font-medium transition-all duration-200 hover:shadow-md cursor-pointer text-decoration-none;
}

.activity-item {
    @apply transition-transform duration-200;
}

.activity-item:hover {
    @apply transform scale-105;
}

.schedule-item:hover {
    @apply shadow-md;
}

.pulse {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}
</style>
@endsection

@push('scripts')
<script>
// Dashboard initialization
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupRealTimeUpdates();
    startActivityPolling();
});

// Initialize charts
function initializeCharts() {
    const ctx = document.getElementById('weeklyStatsChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Normal', 'Warning', 'Critical'],
                datasets: [{
                    data: [85, 12, 3],
                    backgroundColor: [
                        '#10b981',
                        '#f59e0b',
                        '#ef4444'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '60%'
            }
        });
    }
}

// Setup real-time updates
function setupRealTimeUpdates() {
    // WebSocket implementation would go here
    console.log('Real-time updates initialized');
}

// Start polling for activities (fallback for real-time)
function startActivityPolling() {
    setInterval(async () => {
        try {
            const response = await fetch('/doctor/dashboard/activities', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                if (data.newActivities && data.newActivities.length > 0) {
                    data.newActivities.forEach(activity => {
                        addRecentActivity(activity);
                    });
                }
            }
        } catch (error) {
            console.error('Error polling activities:', error);
        }
    }, 30000);
}

// Action functions
function refreshDashboard() {
    window.showToast('🔄 Refreshing dashboard...', 'info');
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

function viewPatient(patientId) {
    if (patientId) {
        window.location.href = `/doctor/patients/${patientId}/monitor`;
    }
}

function respondToActivity(activityId) {
    window.showToast('💬 Response feature coming soon!', 'info');
}

function callPatient(patientId) {
    window.showToast('📞 Calling patient...', 'info');
}

function addNewPatient() {
    window.showToast('👥 Add patient feature coming soon!', 'info');
}

function generateReport() {
    window.showToast('📊 Generating report...', 'info');
}

function emergencyProtocol() {
    const confirmed = confirm('⚠️ Are you sure you want to initiate emergency protocol?');
    if (confirmed) {
        window.showToast('🚨 Emergency protocol initiated!', 'error');
    }
}

function viewDocument(documentId) {
    if (documentId) {
        window.open(`/doctor/documents/${documentId}`, '_blank');
    }
}

function openCommunicationCenter() {
    window.showToast('💬 Communication center coming soon!', 'info');
}

function addRecentActivity(activity) {
    const activitiesList = document.getElementById('recentActivitiesList');
    if (!activitiesList) return;

    const activityHTML = `
        <div class="activity-item flex items-start space-x-4 p-6 bg-gradient-to-r from-gray-50 to-white rounded-2xl border border-gray-200 hover:shadow-md transition-all duration-200 opacity-0" style="animation: slideIn 0.5s ease-out forwards;">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                    <span class="text-blue-600 font-bold">${activity.patient_name ? activity.patient_name.charAt(0).toUpperCase() : 'P'}</span>
                </div>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-bold text-gray-900">${activity.patient_name || 'Patient'}</h4>
                    <span class="text-xs text-gray-500">Just now</span>
                </div>

                <p class="text-sm text-gray-700 mb-2">${activity.activity || 'Updated vital signs'}</p>

                <div class="flex items-center justify-between">
                    <span class="px-3 py-1 text-xs rounded-full font-semibold ${getStatusClass(activity.status)}">
                        ${activity.status ? activity.status.charAt(0).toUpperCase() + activity.status.slice(1) : 'Normal'}
                    </span>

                    <div class="flex space-x-2">
                        <button onclick="viewPatient(${activity.patient_id || 0})" class="text-blue-600 hover:text-blue-700 text-xs font-medium">
                            View
                        </button>
                        <button onclick="respondToActivity(${activity.id || 0})" class="text-green-600 hover:text-green-700 text-xs font-medium">
                            Respond
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    activitiesList.insertAdjacentHTML('afterbegin', activityHTML);

    // Remove oldest activity if more than 10
    const activities = activitiesList.querySelectorAll('.activity-item');
    if (activities.length > 10) {
        activities[activities.length - 1].remove();
    }
}

function getStatusClass(status) {
    switch (status) {
        case 'normal':
            return 'bg-green-100 text-green-800';
        case 'warning':
            return 'bg-yellow-100 text-yellow-800';
        case 'critical':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-green-100 text-green-800';
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
</script>
@endpush
