@extends('patient.layout')

@section('title', 'Appointments')
@section('content')
<div class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6">
    <div class="space-y-4">
        <!-- Quick Stats -->
<!-- Quick Stats -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <!-- Upcoming Appointments Card -->
    <div class="medical-card p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-2 sm:mb-3">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
            </svg>
        </div>
        <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $upcomingAppointments->count() }}
        </div>
        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 font-medium">Upcoming</div>
        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            @if($upcomingAppointments->count() > 0)
                Next on {{ $upcomingAppointments->first()->scheduled_at->format('M d') }}
            @else
                No upcoming
            @endif
        </div>
    </div>

    <!-- Completed Appointments Card -->
    <div class="medical-card p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-2 sm:mb-3">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $completedAppointments }}
        </div>
        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 font-medium">Completed</div>
        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            @if($lastCompletedAppointment)
                Last on {{ $lastCompletedAppointment->scheduled_at->format('M d') }}
            @else
                No history
            @endif
        </div>
    </div>

    <!-- Healthcare Providers Card -->
    <div class="medical-card p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-2 sm:mb-3">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
        </div>
        <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $uniqueDoctorsCount }}
        </div>
        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 font-medium">Healthcare Providers</div>
        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            @if($uniqueDoctorsCount > 0)
                {{ $uniqueDoctorsCount }} provider(s)
            @else
                Not specified
            @endif
        </div>
    </div>

    <!-- Days Until Next Appointment Card -->
    <div class="medical-card p-3 sm:p-4 text-center hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center mx-auto mb-2 sm:mb-3">
            <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1">
            @if($daysUntilNext !== null)
                {{ $daysUntilNext }}
            @else
                -
            @endif
        </div>
        <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 font-medium">Days Until Next</div>
        <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            @if($nextAppointmentDate)
                {{ $nextAppointmentDate }}
            @else
                No appointments
            @endif
        </div>
    </div>
</div>

        <!-- Quick Actions Bar -->
        <div class="medical-card p-4 sm:p-6 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-3 md:space-y-0">
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1 flex items-center">
                        <div class="w-2 h-2 sm:w-3 sm:h-3 bg-gradient-to-r from-blue-500 to-green-500 rounded-full mr-2 sm:mr-3"></div>
                        Appointment Center
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Manage all your healthcare appointments in one place</p>
                </div>
                <div class="flex flex-wrap gap-2 sm:gap-3">
                    <button onclick="openRequestModal()" class="btn-primary text-sm px-4 py-2">
                        <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Request Appointment
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Requests Section -->
        <div class="medical-card mb-6 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1 flex items-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full mr-2 sm:mr-3"></div>
                            Pending Requests
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Requests waiting for doctor's response</p>
                    </div>
                    <span class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400">
                        {{ count($pendingRequests ?? []) }} request(s)
                    </span>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <div class="space-y-4 sm:space-y-6">
                    @forelse($pendingRequests ?? [] as $request)
                    <div class="appointment-card pending relative group dark:bg-gray-700 dark:border-gray-600" data-appointment-id="{{ $request->id }}">
                        <!-- Status ribbon -->
                        <div class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 bg-yellow-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-md">
                            Pending
                        </div>

                        <div class="flex items-start space-x-3 sm:space-x-4 p-4 sm:p-6">
                            <div class="doctor-avatar blue flex-shrink-0">
                                <span class="text-lg sm:text-xl">👨‍⚕️</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3 mb-2 sm:mb-3">
                                    <div>
                                        <h4 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">
                                            Dr. {{ $request->doctor->name ?? 'Unknown Doctor' }}
                                        </h4>
                                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                                            {{ $request->doctor->specialty ?? 'General Practitioner' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Main appointment info -->
                                <div class="bg-gray-50 dark:bg-gray-600 rounded-xl p-3 sm:p-4 mb-3 sm:mb-4">
                                    <p class="text-xs sm:text-sm text-gray-700 dark:text-gray-200 mb-2 sm:mb-3">{{ $request->description }}</p>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                        <div class="space-y-2">
                                            <div class="appointment-detail">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                                                </svg>
                                                <div>
                                                    <span class="detail-label">Requested Date:</span>
                                                    <span class="detail-value">{{ $request->scheduled_at->format('M d, Y g:i A') }}</span>
                                                </div>
                                            </div>
                                            <div class="appointment-detail">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <div>
                                                    <span class="detail-label">Duration:</span>
                                                    <span class="detail-value">{{ $request->duration_minutes }} minutes</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <div class="appointment-detail">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                                </svg>
                                                <div>
                                                    <span class="detail-label">Type:</span>
                                                    <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $request->type)) }}</span>
                                                </div>
                                            </div>
                                            <div class="appointment-detail">
                                                <svg class="w-3 h-3 sm:w-4 sm:h-4 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                                <div>
                                                    <span class="detail-label">Priority:</span>
                                                    <span class="px-2 py-1 text-xs rounded-full font-semibold
                                                        @if($request->priority === 'urgent') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                        @elseif($request->priority === 'emergency') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200
                                                        @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 @endif">
                                                        {{ ucfirst($request->priority) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-600">
                                    <div class="flex items-center text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Requested {{ $request->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 sm:py-12 bg-gray-50 dark:bg-gray-700 rounded-xl">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 sm:h-12 sm:w-12 mx-auto text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h4 class="text-base sm:text-lg font-medium text-gray-900 dark:text-white mt-3 sm:mt-4">No pending requests</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Your appointment requests will appear here</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="medical-card mb-6 overflow-hidden dark:bg-gray-800 dark:border-gray-700">
            <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1 flex items-center">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-gradient-to-r from-green-500 to-blue-500 rounded-full mr-2 sm:mr-3 animate-pulse"></div>
                            Upcoming Appointments
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300">Your confirmed healthcare visits</p>
                    </div>
                    <span class="text-xs sm:text-sm font-medium text-gray-500 dark:text-gray-400 bg-blue-50 dark:bg-blue-900 px-2 sm:px-3 py-1 rounded-full">
                        {{ count($upcomingAppointments ?? []) }} upcoming
                    </span>
                </div>
            </div>

            <div class="p-4 sm:p-6">
                <div class="space-y-4 sm:space-y-6">
                    @forelse($upcomingAppointments ?? [] as $appointment)
                    <div class="appointment-card confirmed relative group hover:shadow-lg transition-all duration-300 dark:bg-gray-700 dark:border-gray-600" data-appointment-id="{{ $appointment->id }}">
                        <!-- Status ribbon -->
                        <div class="absolute -top-1 -right-1 sm:-top-2 sm:-right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-md flex items-center">
                            <svg class="w-2 h-2 sm:w-3 sm:h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Confirmed
                        </div>

                        <div class="flex items-start space-x-3 sm:space-x-4 p-4 sm:p-6">
                            <div class="doctor-avatar green flex-shrink-0">
                                <span class="text-lg sm:text-xl">👨‍⚕️</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center justify-between gap-2 sm:gap-3 mb-2 sm:mb-3">
                                    <div>
                                        <h4 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white">Dr. {{ $appointment->doctor->name ?? 'Unknown Doctor' }}</h4>
                                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">{{ $appointment->doctor->specialty ?? 'General Practice' }}</p>
                                    </div>
                                    @if($appointment->days_until <= 7)
                                        <span class="upcoming-badge animate-pulse text-xs px-2 py-1">
                                            <svg class="w-2 h-2 sm:w-3 sm:h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $appointment->days_until }} days
                                        </span>
                                    @endif
                                </div>

                                <!-- Appointment type badge -->
                                <div class="mb-3 sm:mb-4">
                                    <span class="inline-block bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-semibold px-2 py-1 rounded">
                                        {{ ucfirst(str_replace('_', ' ', $appointment->type)) }} Appointment
                                    </span>
                                </div>

                                <!-- Main appointment info -->
                                <div class="bg-gray-50 dark:bg-gray-600 rounded-xl p-3 sm:p-4 mb-3 sm:mb-4 grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                                    <div class="space-y-2 sm:space-y-3">
                                        <div class="appointment-detail">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                                            </svg>
                                            <div>
                                                <span class="detail-label">Date & Time:</span>
                                                <span class="detail-value">{{ $appointment->scheduled_at->format('M d, Y g:i A') }}</span>
                                            </div>
                                        </div>
                                        <div class="appointment-detail">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-purple-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <div>
                                                <span class="detail-label">Location:</span>
                                                <span class="detail-value">{{ $appointment->is_telemedicine ? 'Virtual' : 'Clinic' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2 sm:space-y-3">
                                        <div class="appointment-detail">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <div>
                                                <span class="detail-label">Duration:</span>
                                                <span class="detail-value">{{ $appointment->duration_minutes }} minutes</span>
                                            </div>
                                        </div>
                                        @if($appointment->priority)
                                        <div class="appointment-detail">
                                            <svg class="w-3 h-3 sm:w-4 sm:h-4 text-orange-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            <div>
                                                <span class="detail-label">Priority:</span>
                                                <span class="detail-value font-semibold @if($appointment->priority === 'urgent') text-orange-600 @elseif($appointment->priority === 'emergency') text-red-600 @else text-blue-600 @endif">
                                                    {{ ucfirst($appointment->priority) }}
                                                </span>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-between pt-3 border-t border-gray-100 dark:border-gray-600 gap-2 sm:gap-3">
                                    <div class="flex items-center text-xs sm:text-sm text-gray-500 dark:text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span>Scheduled {{ $appointment->created_at->diffForHumans() }}</span>
                                    </div>

                                    <div class="flex flex-wrap gap-2">
                                        <button onclick="cancelAppointment({{ $appointment->id }})"
                                                class="btn-secondary text-xs px-3 py-1 text-red-600 hover:bg-red-50 dark:hover:bg-red-900 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                            Cancel
                                        </button>
                                        @if($appointment->is_telemedicine && $appointment->meeting_link)
                                        <a href="{{ $appointment->meeting_link }}" target="_blank"
                                           class="btn-primary text-xs px-3 py-1 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                            </svg>
                                            Join Meeting
                                        </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 sm:py-12 bg-gray-50 dark:bg-gray-700 rounded-xl">
                        <div class="text-4xl sm:text-6xl mb-3 sm:mb-4">📅</div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-1 sm:mb-2">No Upcoming Appointments</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 sm:mb-6">Your confirmed appointments will appear here</p>
                        <button onclick="openRequestModal()" class="btn-primary text-sm px-4 py-2">
                            <svg class="w-4 h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Request Appointment
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Request Appointment Modal -->
<div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-xl w-full mx-4">
        <div class="p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white flex items-center">
                    <div class="w-2 h-2 sm:w-3 sm:h-3 bg-gradient-to-r from-blue-500 to-green-500 rounded-full mr-2 sm:mr-3"></div>
                    New Appointment Request
                </h3>
                <button onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <form id="requestForm" class="p-4 sm:p-6">
            @csrf
            <div class="grid grid-cols-1 gap-4 sm:gap-6">
                <div class="form-group">
                    <label class="form-label">Select Healthcare Provider</label>
                    <select name="user_id" class="form-input" required>
                        <option value="">Choose a doctor...</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor['id'] }}">
                                Dr. {{ $doctor['name'] }} - {{ $doctor['specialty'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Reason for Appointment</label>
                    <textarea name="description" rows="3" class="form-input resize-none"
                              placeholder="Describe your symptoms or reason for visit..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Urgency Level</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 sm:gap-3 urgency-levels">
                        <label class="urgency-level">
                            <input type="radio" name="priority" value="low" class="sr-only" required>
                            <div class="urgency-card routine">
                                <div class="text-base sm:text-lg">📅</div>
                                <div class="font-semibold text-xs sm:text-sm">Low</div>
                                <div class="text-xs">2-4 weeks</div>
                            </div>
                        </label>
                        <label class="urgency-level">
                            <input type="radio" name="priority" value="normal" class="sr-only">
                            <div class="urgency-card soon">
                                <div class="text-base sm:text-lg">⏰</div>
                                <div class="font-semibold text-xs sm:text-sm">Normal</div>
                                <div class="text-xs">1-2 weeks</div>
                            </div>
                        </label>
                        <label class="urgency-level">
                            <input type="radio" name="priority" value="high" class="sr-only">
                            <div class="urgency-card urgent">
                                <div class="text-base sm:text-lg">⚠️</div>
                                <div class="font-semibold text-xs sm:text-sm">High</div>
                                <div class="text-xs">Within 5 days</div>
                            </div>
                        </label>
                        <label class="urgency-level">
                            <input type="radio" name="priority" value="urgent" class="sr-only">
                            <div class="urgency-card emergency">
                                <div class="text-base sm:text-lg">🚨</div>
                                <div class="font-semibold text-xs sm:text-sm">Urgent</div>
                                <div class="text-xs">Within 24h</div>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4 pt-4 sm:pt-6 border-t border-gray-200 dark:border-gray-700 mt-4 sm:mt-6">
                <button type="button" onclick="closeRequestModal()" class="btn-secondary px-6 py-2 text-sm">Cancel</button>
                <button type="button" onclick="submitRequest()" class="btn-primary px-6 py-2 text-sm">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-label {
    @apply block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2;
}

.form-input {
    @apply w-full px-3 py-2 sm:px-4 sm:py-3 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200;
}

.urgency-levels {
    @apply select-none;
}

.urgency-level {
    @apply cursor-pointer;
}

.urgency-card {
    @apply p-3 border-2 border-gray-200 dark:border-gray-600 rounded-xl text-center cursor-pointer hover:border-blue-300 transition-all duration-200 h-full flex flex-col items-center justify-center;
}

.urgency-card.routine {
    @apply hover:border-green-300;
}

.urgency-card.soon {
    @apply hover:border-yellow-300;
}

.urgency-card.urgent {
    @apply hover:border-orange-300;
}

.urgency-card.emergency {
    @apply hover:border-red-300;
}

.urgency-level input:checked + .urgency-card.routine {
    @apply border-green-500 bg-green-50 dark:bg-green-900;
}

.urgency-level input:checked + .urgency-card.soon {
    @apply border-yellow-500 bg-yellow-50 dark:bg-yellow-900;
}

.urgency-level input:checked + .urgency-card.urgent {
    @apply border-orange-500 bg-orange-50 dark:bg-orange-900;
}

.urgency-level input:checked + .urgency-card.emergency {
    @apply border-red-500 bg-red-50 dark:bg-red-900;
}

.btn-primary {
    @apply bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-xl font-medium hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-md hover:shadow-lg;
}

.btn-secondary {
    @apply bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 px-4 py-2 rounded-xl font-medium hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-300;
}

.appointment-card {
    @apply bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.appointment-card.pending {
    @apply border-l-4 border-yellow-500;
}

.appointment-card.confirmed {
    @apply border-l-4 border-green-500;
}

.appointment-card:hover {
    @apply shadow-md;
    transform: translateY(-2px);
}

.doctor-avatar {
    @apply w-12 h-12 sm:w-14 sm:h-14 rounded-xl flex items-center justify-center shadow-sm;
}

.doctor-avatar.blue {
    @apply bg-gradient-to-br from-blue-100 to-blue-200 dark:from-blue-800 dark:to-blue-700 text-blue-600 dark:text-blue-300;
}

.doctor-avatar.green {
    @apply bg-gradient-to-br from-green-100 to-green-200 dark:from-green-800 dark:to-green-700 text-green-600 dark:text-green-300;
}

.upcoming-badge {
    @apply bg-gradient-to-r from-orange-400 to-red-500 text-white font-bold rounded-full flex items-center;
}

.appointment-detail {
    @apply flex items-start space-x-2 sm:space-x-3 text-xs sm:text-sm;
}

.detail-label {
    @apply text-gray-500 dark:text-gray-400 whitespace-nowrap;
}

.detail-value {
    @apply font-medium text-gray-900 dark:text-white;
}

.medical-card {
    @apply bg-white dark:bg-gray-800 rounded-2xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Make urgency level cards clickable and show selection
    const urgencyLevels = document.querySelectorAll('.urgency-level');

    urgencyLevels.forEach(level => {
        level.addEventListener('click', function() {
            // Remove selected state from all cards first
            document.querySelectorAll('.urgency-card').forEach(card => {
                card.classList.remove(
                    'border-green-500', 'bg-green-50', 'dark:bg-green-900',
                    'border-yellow-500', 'bg-yellow-50', 'dark:bg-yellow-900',
                    'border-orange-500', 'bg-orange-50', 'dark:bg-orange-900',
                    'border-red-500', 'bg-red-50', 'dark:bg-red-900'
                );
            });

            // Add selected state to clicked card
            const card = this.querySelector('.urgency-card');
            const input = this.querySelector('input[type="radio"]');

            if (input.value === 'low') {
                card.classList.add('border-green-500', 'bg-green-50', 'dark:bg-green-900');
            } else if (input.value === 'normal') {
                card.classList.add('border-yellow-500', 'bg-yellow-50', 'dark:bg-yellow-900');
            } else if (input.value === 'high') {
                card.classList.add('border-orange-500', 'bg-orange-50', 'dark:bg-orange-900');
            } else if (input.value === 'urgent') {
                card.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900');
            }

            // Actually select the radio button
            input.checked = true;
        });
    });
});
</script>

@push('scripts')
<script>
    // Request Modal Functions
    function openRequestModal() {
        document.getElementById('requestModal').classList.remove('hidden');
        document.getElementById('requestModal').classList.add('flex');
    }

    function closeRequestModal() {
        document.getElementById('requestModal').classList.add('hidden');
        document.getElementById('requestModal').classList.remove('flex');
    }

    // Form submission
    async function submitRequest() {
        const form = document.getElementById('requestForm');
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!form) {
            console.error('Error: Form element not found');
            alert('Form configuration error. Please refresh the page.');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner">⏳</span> Submitting...';
        }

        try {
            const formData = new FormData(form);
            const requestData = {
                user_id: formData.get('user_id'),
                description: formData.get('description'),
                priority: formData.get('priority')
            };

            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                throw new Error('Security token missing. Please refresh the page.');
            }

            const response = await fetch('/appointments/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(requestData)
            });

            const responseText = await response.text();
            let responseData;

            try {
                responseData = responseText ? JSON.parse(responseText) : {};
            } catch (e) {
                console.error('Failed to parse response:', e, '\nResponse text:', responseText);
                throw new Error('Invalid server response format');
            }

            if (!response.ok) {
                throw new Error(responseData.message || `Server returned status ${response.status}`);
            }

            if (!responseData.success) {
                throw new Error(responseData.message || 'Request was not successful');
            }

            showNotification('success', 'Request submitted successfully!');
            closeRequestModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } catch (error) {
            console.error('Submission error:', error);
            let errorMessage = 'An error occurred';
            if (error.message.includes('Failed to fetch')) {
                errorMessage = 'Network error. Please check your connection.';
            } else if (error.message) {
                errorMessage = error.message;
            }
            showNotification('error', `Error: ${errorMessage}`);
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            }
        }
    }

    // Cancel appointment function
    async function cancelAppointment(appointmentId) {
        const reason = prompt('Please provide a reason for cancellation (optional):');
        if (!confirm('Are you sure you want to cancel this appointment?')) {
            return;
        }

        try {
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            if (!csrfMeta) {
                throw new Error('Security token missing. Please refresh the page.');
            }

            const response = await fetch(`/patient/appointments/${appointmentId}/cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfMeta.content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ reason: reason || 'Patient cancelled' })
            });

            const responseText = await response.text();
            let data;

            try {
                data = responseText ? JSON.parse(responseText) : {};
            } catch (e) {
                console.error('Failed to parse response:', e);
                throw new Error('Invalid server response');
            }

            if (!response.ok) {
                throw new Error(data.message || 'Failed to cancel appointment');
            }

            if (data.success) {
                showNotification('success', 'Appointment cancelled successfully');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error(data.message || 'Failed to cancel appointment');
            }
        } catch (error) {
            console.error('Cancel appointment error:', error);
            showNotification('error', 'Error cancelling appointment: ' + error.message);
        }
    }

    // Helper function for notifications
    function showNotification(type, message) {
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(notification => notification.remove());

        const notification = document.createElement('div');
        notification.className = `notification fixed top-4 right-4 p-4 rounded-md shadow-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);

        notification.addEventListener('click', () => {
            if (notification.parentNode) {
                notification.remove();
            }
        });
    }

    // Initialize when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Appointments page loaded');

        // Animate cards on load
        const cards = document.querySelectorAll('.appointment-card');
        cards.forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
</script>
@endpush
@endsection
