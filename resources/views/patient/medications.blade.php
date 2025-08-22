@extends('patient.layout')

@section('title', 'Medications')
@section('page-title', 'Medication Management')
@section('page-description', 'Track your medications, set reminders, and monitor adherence')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Quick Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $stats['activeMedications'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Active Medications</div>
                <div class="text-xs text-gray-400 mt-1">Currently prescribed</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $stats['todayTaken'] ?? 0 }}/{{ $stats['todayScheduled'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Today's Progress</div>
                <div class="text-xs text-gray-400 mt-1">Medications taken</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $stats['adherenceRate'] ?? 0 }}%</div>
                <div class="text-sm text-gray-600 font-medium">Adherence Rate</div>
                <div class="text-xs text-gray-400 mt-1">Last 30 days</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.07 13H2.05L2 12l.05-1H4.07a8.003 8.003 0 010 2z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $stats['upcomingReminders'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Upcoming</div>
                <div class="text-xs text-gray-400 mt-1">Next 2 hours</div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="medical-card p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Today's Medication Schedule</h3>
                    <p class="text-gray-600">{{ \Carbon\Carbon::now()->format('l, F j, Y') }}</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-sm text-gray-500">
                        Progress: {{ $stats['todayTaken'] ?? 0 }}/{{ $stats['todayScheduled'] ?? 0 }} taken
                    </div>
                    <div class="w-32 bg-gray-200 rounded-full h-3">
                        <div class="bg-gradient-to-r from-green-400 to-green-500 h-3 rounded-full transition-all duration-1000"
                             style="width: {{ $stats['todayScheduled'] > 0 ? round(($stats['todayTaken'] / $stats['todayScheduled']) * 100) : 0 }}%"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($todayMedications as $med)
                <div class="medication-card {{ $med['status'] ?? 'scheduled' }}" data-medication-id="{{ $med['id'] }}">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            <div class="medication-icon {{ $med['color'] ?? 'blue' }}">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-900">{{ $med['name'] }}</h4>
                                <p class="text-sm text-gray-600">{{ $med['dosage'] }}</p>
                            </div>
                        </div>

                        <div class="status-badge {{ $med['status'] ?? 'scheduled' }}">
                            @if(($med['status'] ?? '') === 'taken')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600">Scheduled Time:</span>
                            <span class="font-semibold text-gray-900">{{ $med['time'] ?? 'Not scheduled' }}</span>
                        </div>

                        @if(($med['status'] ?? '') === 'taken')
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Taken At:</span>
                                <span class="font-semibold text-green-600">{{ $med['taken_at'] ?? 'Just now' }}</span>
                            </div>
                        @endif

                        <div class="pt-3 border-t border-gray-200">
                            @if(($med['status'] ?? '') === 'taken')
                                <button disabled class="btn-taken w-full">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Taken
                                </button>
                            @else
                                <button onclick="takeMedication({{ $med['id'] ?? 0 }})" class="btn-primary w-full">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Mark as Taken
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-3 text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No medications scheduled</h3>
                    <p class="mt-1 text-gray-500">Add your first medication to get started</p>
                    <div class="mt-6">
                        <button onclick="openAddMedicationModal()" class="btn-primary">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Medication
                        </button>
                    </div>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- All Medications List -->
            <div class="xl:col-span-2">
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">All Medications</h3>
                                <p class="text-gray-600">Manage your complete medication list</p>
                            </div>
                            <button onclick="openAddMedicationModal()" class="btn-primary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Medication
                            </button>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="space-y-6">
                            @forelse($allMedications as $medication)
                            <div class="medication-detail-card">
                                <div class="flex items-start justify-between mb-6">
                                    <div class="flex items-start space-x-4">
                                        <div class="medication-avatar">
                                            <span class="text-xl">💊</span>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-bold text-gray-900 mb-1">{{ $medication['name'] }}</h4>
                                            <p class="text-sm text-gray-600 mb-2">{{ $medication['generic_name'] ?? 'No generic name' }}</p>
                                            <div class="flex items-center space-x-4 text-sm">
                                                <span class="med-detail-badge">{{ $medication['dosage'] }}</span>
                                                <span class="med-detail-badge">{{ $medication['frequency'] }}</span>
                                                <span class="status-badge active">Active</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button onclick="editMedication({{ $medication['id'] }})" class="btn-icon">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button onclick="deleteMedication({{ $medication['id'] }})" class="btn-icon text-red-600 hover:bg-red-50">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-3">
                                        <div class="detail-row">
                                            <span class="detail-label">Schedule:</span>
                                            <span class="detail-value">{{ implode(', ', $medication['times']) }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Prescribed by:</span>
                                            <span class="detail-value">{{ $medication['prescribed_by'] ?? 'Not specified' }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Start Date:</span>
                                            <span class="detail-value">{{ \Carbon\Carbon::parse($medication['start_date'])->format('M d, Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="detail-row">
                                            <span class="detail-label">Purpose:</span>
                                            <span class="detail-value">{{ $medication['purpose'] ?? 'Not specified' }}</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Refills:</span>
                                            <span class="detail-value">{{ $medication['refills'] ?? 0 }} remaining</span>
                                        </div>
                                        <div class="detail-row">
                                            <span class="detail-label">Adherence:</span>
                                            <span class="detail-value text-green-600 font-semibold">{{ $medication['adherence'] ?? '100%' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                </svg>
                                <h3 class="mt-2 text-lg font-medium text-gray-900">No medications found</h3>
                                <p class="mt-1 text-gray-500">Get started by adding your first medication</p>
                                <div class="mt-6">
                                    <button onclick="openAddMedicationModal()" class="btn-primary">
                                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Medication
                                    </button>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Quick Actions</h3>
                        <p class="text-sm text-gray-600">Medication management</p>
                    </div>
                    <div class="space-y-3">
                        <button onclick="openAddMedicationModal()" class="action-btn bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 text-blue-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Medication
                        </button>
                        <button onclick="setReminder()" class="action-btn bg-gradient-to-r from-green-50 to-emerald-50 border-green-200 text-green-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.07 13H2.05L2 12l.05-1H4.07a8.003 8.003 0 010 2z"></path>
                            </svg>
                            Set Reminder
                        </button>
                        <button onclick="exportData()" class="action-btn bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200 text-purple-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Export Data
                        </button>
                        <button onclick="reportSideEffect()" class="action-btn bg-gradient-to-r from-orange-50 to-red-50 border-orange-200 text-orange-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Report Side Effect
                        </button>
                    </div>
                </div>

                <!-- Adherence Chart -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">Weekly Adherence</h3>
                        <p class="text-sm text-gray-600">Last 7 days performance</p>
                    </div>
                    <div class="space-y-4">
                        @foreach($weeklyAdherence as $day)
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 w-8">{{ $day['day'] }}</span>
                            <div class="flex-1 mx-3">
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-green-400 to-green-500 h-2 rounded-full transition-all duration-1000"
                                         style="width: {{ $day['percentage'] }}%"></div>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900 w-8">{{ $day['percentage'] }}%</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-200">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-700 mb-1">{{ array_sum(array_column($weeklyAdherence, 'percentage')) / count($weeklyAdherence) }}%</div>
                            <div class="text-sm text-green-600 font-medium">Average Adherence</div>
                            <div class="text-xs text-green-500 mt-1">Last 7 days</div>
                        </div>
                    </div>
                </div>

                <!-- Medication Reminders -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">⏰ Reminders</h3>
                        <p class="text-sm text-gray-600">Notification preferences</p>
                    </div>
                    <div class="space-y-4">
                        <div class="reminder-setting">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Push Notifications</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="reminder-setting">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Email Reminders</span>
                                <label class="toggle-switch">
                                    <input type="checkbox">
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="reminder-setting">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">SMS Alerts</span>
                                <label class="toggle-switch">
                                    <input type="checkbox" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="reminder-setting">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700">Remind Before</span>
                                <select class="text-sm border border-gray-300 rounded-lg px-2 py-1 bg-white">
                                    <option>15 minutes</option>
                                    <option>30 minutes</option>
                                    <option>1 hour</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<!-- Doctor's Medications Section (Placeholder) -->
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-gray-900">{{ $med->name }}</h4>
                        <p class="text-sm text-gray-600 mb-2">Prescribed by {{ $med->doctor->name ?? 'Doctor' }}</p>
                        <div class="flex space-x-2">
                            <span class="med-detail-badge">{{ $med->dosage }}</span>
                            <span class="med-detail-badge">{{ $med->frequency }}</span>
                            <span class="status-badge {{ $med->status === 'active' ? 'active' : 'inactive' }}">{{ ucfirst($med->status) }}</span>
                        </div>
                    </div>
                </div>
                <button onclick="addToMyMeds('{{ $med->id }}')" class="btn-primary px-4 py-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">No doctor prescriptions</h3>
            <p class="mt-1 text-gray-500">Your doctor's prescribed medications will appear here</p>
        </div>
        @endforelse
    </div>
</div>

<!-- Doctor's Medications Modal -->
<div id="doctorsMedsModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white rounded-3xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-8 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">All Doctor's Prescriptions</h3>
                <button onclick="closeDoctorsMedsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <div class="p-8">
            @forelse($doctorMeds as $med)
            <div class="medication-detail-card mb-6">
                <div class="flex items-start justify-between mb-4">
                    <div class="flex items-start space-x-4">
                        <div class="medication-avatar bg-gradient-to-br from-blue-100 to-indigo-100">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-lg font-bold text-gray-900">{{ $med->name }}</h4>
                            <p class="text-sm text-gray-600">Prescribed by {{ $med->doctor->name ?? 'Doctor' }} on {{ $med->created_at->format('m/d/Y') }}</p>
                        </div>
                    </div>
                    <button onclick="addToMyMeds('{{ $med->id }}')" class="btn-primary px-4 py-2">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add to My Meds
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <p class="text-sm text-gray-600"><span class="font-medium">Dosage:</span> {{ $med->dosage }}</p>
                        <p class="text-sm text-gray-600"><span class="font-medium">Frequency:</span> {{ $med->frequency }}</p>
                        <p class="text-sm text-gray-600"><span class="font-medium">Schedule:</span> {{ $med->times }}</p>
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                <h3 class="mt-2 text-lg font-medium text-gray-900">No doctor prescriptions</h3>
                <p class="mt-1 text-gray-500">Your doctor's prescribed medications will appear here</p>
            </div>
            @endforelse
        </div>

        <div class="p-6 border-t border-gray-200 bg-gray-50 rounded-b-3xl">
            <div class="flex justify-end">
                <button onclick="closeDoctorsMedsModal()" class="btn-secondary px-8">Close</button>
            </div>
        </div>
    </div>
</div>



<!-- Add Medication Modal -->
<div id="addMedicationModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white rounded-3xl max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
        <div class="p-8 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-2xl font-bold text-gray-900">Add New Medication</h3>
                <button onclick="closeAddMedicationModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

<form id="addMedicationForm" class="p-8 space-y-6" onsubmit="event.preventDefault(); submitMedication();">
        <!-- <div id="addMedicationForm" class="p-8 space-y-6"> -->
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="form-label">Medication Name *</label>
                    <input type="text" name="medication_name" placeholder="e.g., Vitamin D3" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Generic Name</label>
                    <input type="text" name="generic_name" placeholder="e.g., Cholecalciferol" class="form-input">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="form-label">Dosage *</label>
                    <input type="text" name="dosage" placeholder="e.g., 1000 IU" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Frequency *</label>
                    <select name="frequency" class="form-input" required>
                        <option value="">Select frequency</option>
                        <option value="once_daily">Once daily</option>
                        <option value="twice_daily">Twice daily</option>
                        <option value="three_times_daily">Three times daily</option>
                        <option value="four_times_daily">Four times daily</option>
                        <option value="as_needed">As needed</option>
                        <option value="custom">Custom schedule</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Schedule Times *</label>
                <div id="scheduleTimes" class="space-y-2">
                    <input type="time" name="times[]" class="form-input" required>
                </div>
                <button type="button" onclick="addTimeSlot()" class="mt-2 text-sm text-blue-600 hover:text-blue-700">+ Add another time</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="form-group">
                    <label class="form-label">Start Date *</label>
                    <input type="date" name="start_date" class="form-input" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Prescribed By</label>
                    <input type="text" name="prescribed_by" placeholder="e.g., Dr. Johnson" class="form-input">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Purpose/Condition</label>
                <input type="text" name="purpose" placeholder="e.g., Bone health, Heart health" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Special Instructions</label>
                <textarea name="instructions" rows="3" placeholder="e.g., Take with food, Avoid dairy products" class="form-input resize-none"></textarea>
            </div>

            <!-- <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <button type="button" onclick="closeAddMedicationModal()" class="btn-secondary px-8">Cancel</button>
                <button type="button" onclick="submitMedication()" class="btn-primary px-8" id="submitMedicationBtn">Add Medication</button>
            </div> -->
<div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
        <button type="button" onclick="closeAddMedicationModal()" class="btn-secondary px-8">Cancel</button>
        <button type="submit" class="btn-primary px-8" id="submitMedicationBtn">Add Medication</button>
    </div>
        </div>
    </div>
</div>
</form>

<style>
.medical-card {
    @apply bg-white rounded-3xl shadow-lg border border-gray-200 hover:shadow-xl transition-all duration-300;
}

.medication-card {
    @apply bg-white p-6 rounded-3xl border-2 border-gray-200 hover:shadow-lg transition-all duration-300;
}

.medication-card.taken {
    @apply border-green-200 bg-green-50;
}

.medication-card.upcoming {
    @apply border-blue-200 bg-blue-50 ring-2 ring-blue-500 ring-opacity-20;
}

.medication-card.scheduled {
    @apply border-gray-200 bg-white;
}

.medication-icon {
    @apply w-12 h-12 rounded-2xl flex items-center justify-center;
}

.medication-icon.yellow {
    @apply bg-gradient-to-br from-yellow-500 to-orange-500;
}

.medication-icon.blue {
    @apply bg-gradient-to-br from-blue-500 to-indigo-600;
}

.medication-icon.green {
    @apply bg-gradient-to-br from-green-500 to-emerald-600;
}

.medication-icon.purple {
    @apply bg-gradient-to-br from-purple-500 to-purple-600;
}

.status-badge {
    @apply px-3 py-1 rounded-full text-xs font-semibold flex items-center space-x-1;
}

.status-badge.taken {
    @apply bg-green-100 text-green-800;
}

.status-badge.upcoming {
    @apply bg-blue-100 text-blue-800;
}

.status-badge.scheduled {
    @apply bg-gray-100 text-gray-800;
}

.status-badge.active {
    @apply bg-green-100 text-green-800;
}

.btn-primary {
    @apply bg-gradient-to-r from-blue-500 to-indigo-600 text-white py-3 px-6 rounded-xl font-semibold hover:from-blue-600 hover:to-indigo-700 transition-all duration-200 flex items-center justify-center;
}

.btn-secondary {
    @apply bg-gray-100 text-gray-700 py-3 px-6 rounded-xl font-semibold hover:bg-gray-200 transition-all duration-200 flex items-center justify-center;
}

.btn-taken {
    @apply bg-green-100 text-green-800 py-2 px-4 rounded-xl font-semibold cursor-not-allowed flex items-center justify-center;
}

.medication-detail-card {
    @apply bg-gradient-to-br from-gray-50 to-white p-6 rounded-3xl border border-gray-200 hover:shadow-lg transition-all duration-300;
}

.medication-avatar {
    @apply w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl flex items-center justify-center;
}

.med-detail-badge {
    @apply bg-gray-100 text-gray-700 px-2 py-1 rounded-lg text-xs font-medium;
}

.detail-row {
    @apply flex justify-between items-center;
}

.detail-label {
    @apply text-sm text-gray-600;
}

.detail-value {
    @apply text-sm font-semibold text-gray-900;
}

.btn-icon {
    @apply p-2 rounded-xl bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors;
}

.action-btn {
    @apply w-full flex items-center p-4 rounded-2xl border font-medium transition-all duration-200 hover:shadow-md;
}

.reminder-setting {
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

.form-label {
    @apply block text-sm font-bold text-gray-700 mb-2;
}

.form-group {
    @apply space-y-2;
}

.form-input {
    @apply w-full p-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors;
}

/* Toast notification styles */
.toast {
    @apply fixed top-4 right-4 z-50 p-4 rounded-xl shadow-lg max-w-sm transform transition-all duration-300;
}

.toast.success {
    @apply bg-green-100 text-green-800 border border-green-200;
}

.toast.error {
    @apply bg-red-100 text-red-800 border border-red-200;
}

.toast.info {
    @apply bg-blue-100 text-blue-800 border border-blue-200;
}

.toast.warning {
    @apply bg-yellow-100 text-yellow-800 border border-yellow-200;
}

/* Loading spinner */
.loading-spinner {
    @apply animate-spin h-4 w-4 border-2 border-current border-t-transparent rounded-full;
}
</style>


@endsection

@push('scripts')
<script>
// Global variables
let isSubmitting = false;

// Toast notification system
function showToast(message, type = 'info', duration = 5000) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="flex items-center space-x-2">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-current opacity-70 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(toast);

    // Auto remove after duration
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, duration);
}

// Loading state helpers
function showLoading(button) {
    const originalContent = button.innerHTML;
    button.dataset.originalContent = originalContent;
    button.innerHTML = `
        <div class="loading-spinner mr-2"></div>
        Processing...
    `;
    button.disabled = true;
}

function hideLoading(button) {
    const originalContent = button.dataset.originalContent;
    if (originalContent) {
        button.innerHTML = originalContent;
    }
    button.disabled = false;
}

// Take medication functionality
async function takeMedication(medicationId) {
    const card = document.querySelector(`[data-medication-id="${medicationId}"]`);
    if (!card) return;

    const button = card.querySelector('button');
    if (!button || button.disabled) return;

    showLoading(button);

    try {
        const response = await fetch('{{ route("patient.medications.mark-taken") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                medication_id: medicationId
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to mark medication');
        }

        // Update UI
        card.classList.remove('upcoming', 'scheduled');
        card.classList.add('taken');

        // Update status badge
        const statusBadge = card.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.className = 'status-badge taken';
            statusBadge.innerHTML = `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
        }

        // Add taken time
        const timeContainer = card.querySelector('.space-y-3');
        if (timeContainer && !timeContainer.querySelector('.text-green-600')) {
            const takenTimeDiv = document.createElement('div');
            takenTimeDiv.className = 'flex items-center justify-between text-sm';
            takenTimeDiv.innerHTML = `
                <span class="text-gray-600">Taken At:</span>
                <span class="font-semibold text-green-600">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
            `;
            timeContainer.insertBefore(takenTimeDiv, timeContainer.lastElementChild);
        }

        // Update button
        button.className = 'btn-taken w-full';
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Taken
        `;
        button.disabled = true;

        showToast('💊 Medication marked as taken successfully!', 'success');
        updateProgress();

    } catch (error) {
        hideLoading(button);
        showToast(`❌ ${error.message}`, 'error');
        console.error('Error taking medication:', error);
    }
}

// Update progress bar
function updateProgress() {
    try {
        const takenCount = document.querySelectorAll('.medication-card.taken').length;
        const totalCount = document.querySelectorAll('.medication-card').length;

        if (totalCount === 0) return;

        const percentage = Math.round((takenCount / totalCount) * 100);

        // Update progress bar
        const progressBar = document.querySelector('.bg-gradient-to-r.from-green-400');
        if (progressBar) {
            progressBar.style.width = `${percentage}%`;
        }

        // Update text
        const progressText = document.querySelector('.text-sm.text-gray-500');
        if (progressText) {
            progressText.textContent = `Progress: ${takenCount}/${totalCount} taken`;
        }
    } catch (error) {
        console.error('Error updating progress:', error);
    }
}

// Modal functions
function openAddMedicationModal() {
    const modal = document.getElementById('addMedicationModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Focus first input
        const firstInput = modal.querySelector('input[name="medication_name"]');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }
    }
}

function closeAddMedicationModal() {
    const modal = document.getElementById('addMedicationModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        resetForm();
    }
}

function resetForm() {
    const form = document.getElementById('addMedicationForm');
    if (!form) return;

    // Reset all inputs
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        if (input.type === 'checkbox' || input.type === 'radio') {
            input.checked = false;
        } else if (input.name === 'start_date') {
            input.value = new Date().toISOString().split('T')[0];
        } else {
            input.value = '';
        }
        input.classList.remove('border-red-300');
    });

    // Reset time slots to just one
    const timesContainer = document.getElementById('scheduleTimes');
    if (timesContainer) {
        timesContainer.innerHTML = '<input type="time" name="times[]" class="form-input" required>';
    }

    isSubmitting = false;
}

// Add time slot for medication schedule
function addTimeSlot() {
    const container = document.getElementById('scheduleTimes');
    if (!container) return;

    const newInput = document.createElement('div');
    newInput.className = 'flex items-center space-x-2';
    newInput.innerHTML = `
        <input type="time" name="times[]" class="form-input flex-1" required>
        <button type="button" onclick="removeTimeSlot(this)" class="text-red-600 hover:text-red-700 p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
            </svg>
        </button>
    `;
    container.appendChild(newInput);
}

function removeTimeSlot(button) {
    const container = document.getElementById('scheduleTimes');
    if (container && container.children.length > 1) {
        button.parentElement.remove();
    }
}

// Handle frequency change
document.addEventListener('change', function(e) {
    if (e.target.name === 'frequency') {
        const timesContainer = document.getElementById('scheduleTimes');
        if (!timesContainer) return;

        timesContainer.innerHTML = '';
        const frequency = e.target.value;
        let timeSlots = 1;

        switch (frequency) {
            case 'once_daily': timeSlots = 1; break;
            case 'twice_daily': timeSlots = 2; break;
            case 'three_times_daily': timeSlots = 3; break;
            case 'four_times_daily': timeSlots = 4; break;
            default: timeSlots = 1;
        }

        for (let i = 0; i < timeSlots; i++) {
            if (i === 0) {
                timesContainer.innerHTML = '<input type="time" name="times[]" class="form-input" required>';
            } else {
                addTimeSlot();
            }
        }
    }
});

// Form submission
// async function submitMedication() {
//     if (isSubmitting) return;

//     const submitBtn = document.getElementById('submitMedicationBtn');
//     if (!submitBtn) return;

//     const form = document.getElementById('addMedicationForm');
//     if (!form) return;

//     // Basic validation
//     const requiredFields = form.querySelectorAll('[required]');
//     let isValid = true;

//     requiredFields.forEach(field => {
//         if (!field.value.trim()) {
//             field.classList.add('border-red-300');
//             isValid = false;
//         } else {
//             field.classList.remove('border-red-300');
//         }
//     });

//     if (!isValid) {
//         showToast('❌ Please fill in all required fields', 'error');
//         return;
//     }

//     isSubmitting = true;
//     showLoading(submitBtn);

//     try {
//         // Collect form data
//         const formData = {
//             medication_name: form.querySelector('[name="medication_name"]').value,
//             generic_name: form.querySelector('[name="generic_name"]').value,
//             dosage: form.querySelector('[name="dosage"]').value,
//             frequency: form.querySelector('[name="frequency"]').value,
//             times: Array.from(form.querySelectorAll('[name="times[]"]')).map(input => input.value),
//             start_date: form.querySelector('[name="start_date"]').value,
//             prescribed_by: form.querySelector('[name="prescribed_by"]').value,
//             purpose: form.querySelector('[name="purpose"]').value,
//             instructions: form.querySelector('[name="instructions"]').value
//         };

//         // Send to server
//         const response = await fetch('{{ route("patient.medications.store") }}', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-TOKEN': '{{ csrf_token() }}',
//                 'Accept': 'application/json'
//             },
//             body: JSON.stringify(formData)
//         });

//         const data = await response.json();

//         if (!response.ok) {
//             throw new Error(data.message || 'Failed to add medication');
//         }

//         showToast('✅ Medication added successfully!', 'success');
//         closeAddMedicationModal();

//         // Reload the page to show new medication
//         window.location.reload();

//     } catch (error) {
//         showToast(`❌ ${error.message}`, 'error');
//         console.error('Error submitting medication:', error);
//     } finally {
//         isSubmitting = false;
//         hideLoading(submitBtn);
//     }
// }



async function submitMedication() {
// At the start of your submitMedication function
console.log('Starting form submission');
console.log('Form element:', document.getElementById('addMedicationForm'));
    if (isSubmitting) return;

    const submitBtn = document.getElementById('submitMedicationBtn');
    if (!submitBtn) return;

    const form = document.getElementById('addMedicationForm');
    if (!form) return;

    // Basic validation
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('border-red-300');
            isValid = false;
        } else {
            field.classList.remove('border-red-300');
        }
    });

    if (!isValid) {
        showToast('❌ Please fill in all required fields', 'error');
        return;
    }

    isSubmitting = true;
    showLoading(submitBtn);

    try {
        // Create FormData object
        const formData = new FormData(form);

        // Convert FormData to JSON
        const jsonData = {};
        formData.forEach((value, key) => {
            if (key === 'times[]') {
                if (!jsonData.times) jsonData.times = [];
                jsonData.times.push(value);
            } else {
                jsonData[key] = value;
            }
        });

        // Send to server
        const response = await fetch('{{ route("patient.medications.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(jsonData)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to add medication');
        }

        showToast('✅ Medication added successfully!', 'success');
        closeAddMedicationModal();

        // Reload the page to show new medication
        window.location.reload();

    } catch (error) {
        showToast(`❌ ${error.message}`, 'error');
        console.error('Error submitting medication:', error);
    } finally {
        isSubmitting = false;
        hideLoading(submitBtn);
    }
}

// Other action functions
function editMedication(id) {
    showToast('✏️ Edit functionality coming soon!', 'info');
}

function deleteMedication(id) {
    if (confirm('Are you sure you want to delete this medication?')) {
        showToast('🗑️ Delete functionality coming soon!', 'info');
    }
}

function setReminder() {
    showToast('⏰ Reminder settings updated!', 'info');
}

function exportData() {
    showToast('📊 Export functionality coming soon!', 'info');
}

function reportSideEffect() {
    showToast('⚠️ Side effect reporting coming soon!', 'info');
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('Medications page loaded');

    // Animate progress bars on load
    setTimeout(() => {
        const progressBars = document.querySelectorAll('.bg-gradient-to-r.from-green-400');
        progressBars.forEach(bar => {
            const width = bar.style.width || '0%';
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 500);
        });
    }, 100);

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddMedicationModal();
        }
    });

    // Close modal on backdrop click
    const modal = document.getElementById('addMedicationModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeAddMedicationModal();
            }
        });
    }
});

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    showToast('❌ An unexpected error occurred. Please refresh the page.', 'error');
});
</script>

<script>
// Open doctor's medications modal
function openDoctorsMedsModal() {
    const modal = document.getElementById('doctorsMedsModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

// Close doctor's medications modal
function closeDoctorsMedsModal() {
    const modal = document.getElementById('doctorsMedsModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Add doctor's medication to user's medications
async function addToMyMeds(doctorMedId) {
    const button = document.querySelector(`[onclick="addToMyMeds('${doctorMedId}')"]`);
    if (!button || button.disabled) return;

    const originalText = button.innerHTML;
    button.innerHTML = `
        <div class="loading-spinner mr-2"></div>
        Adding...
    `;
    button.disabled = true;

    try {
        const response = await fetch('{{ route("patient.medications.add-from-doctor") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                doctor_med_id: doctorMedId
            })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Failed to add medication');
        }

        showToast('✅ Medication added to your list successfully!', 'success');

        // Update the button to show it's been added
        button.innerHTML = `
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Added
        `;
        button.className = 'btn-taken px-4 py-2';
        button.onclick = null;

    } catch (error) {
        button.innerHTML = originalText;
        button.disabled = false;
        showToast(`❌ ${error.message}`, 'error');
        console.error('Error adding medication:', error);
    }
}
</script>

@endpush
