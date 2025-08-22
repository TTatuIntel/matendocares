@extends('doctor.layout')

@section('title', 'Emergency Management')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent mb-2">
                        🚨 Emergency Management Center
                    </h1>
                    <p class="text-gray-600 text-lg">Monitor critical alerts and manage emergency protocols</p>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">
                    <button onclick="viewProtocols()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Emergency Protocols
                    </button>
                    <button onclick="triggerEmergencyAlert()" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl transition-colors duration-200 flex items-center shadow-lg">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Emergency Alert
                    </button>
                </div>
            </div>
        </div>

        <!-- Emergency Status Banner -->
        @if(isset($activeEmergencies) && $activeEmergencies->count() > 0)
        <div class="critical-alert medical-card p-6 border-red-300 animate-pulse">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-8 h-8 text-white mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <div>
                        <h2 class="text-xl font-bold text-white">🚨 ACTIVE EMERGENCY SITUATION</h2>
                        <p class="text-red-100">{{ $activeEmergencies->count() }} patient(s) require immediate attention</p>
                    </div>
                </div>
                <button onclick="viewActiveEmergencies()" class="bg-white text-red-600 font-bold py-2 px-4 rounded-lg hover:bg-red-50 transition-colors">
                    View Details
                </button>
            </div>
        </div>
        @endif

        <!-- Quick Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300 border-l-4 border-red-500">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="criticalAlertsCount">{{ $criticalAlerts ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Critical Alerts</div>
                <div class="text-xs text-gray-400 mt-1">Last 24 hours</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300 border-l-4 border-yellow-500">
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="responseTime">{{ $avgResponseTime ?? '5.2' }}</div>
                <div class="text-sm text-gray-600 font-medium">Avg Response</div>
                <div class="text-xs text-gray-400 mt-1">Minutes</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300 border-l-4 border-green-500">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="resolvedToday">{{ $resolvedToday ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Resolved Today</div>
                <div class="text-xs text-gray-400 mt-1">Emergency cases</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300 border-l-4 border-blue-500">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="onCallStaff">{{ $onCallStaff ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">On-Call Staff</div>
                <div class="text-xs text-gray-400 mt-1">Available now</div>
            </div>
        </div>

        <!-- Active Alerts and Emergency Dashboard -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Active Critical Alerts -->
            <div class="xl:col-span-2">
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">🚨 Active Critical Alerts</h3>
                                <p class="text-gray-600">Patients requiring immediate attention</p>
                            </div>
                            <div class="flex items-center space-x-2 text-sm text-red-600">
                                <div class="w-3 h-3 bg-red-500 rounded-full animate-ping"></div>
                                <span>Live Updates</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="space-y-6" id="criticalAlertsList">
                            @forelse($criticalAlertsList ?? [] as $alert)
                            <div class="alert-card border-l-4 border-red-500 bg-red-50 p-6 rounded-2xl hover:shadow-lg transition-all duration-300">
                                <div class="flex items-start justify-between">
                                    <div class="flex items-start space-x-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-16 h-16 bg-red-100 rounded-3xl flex items-center justify-center">
                                                <span class="text-red-600 font-bold text-xl">{{ strtoupper(substr($alert->patient_name ?? 'P', 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-2">
                                                <h4 class="text-lg font-bold text-red-900">{{ $alert->patient_name ?? 'Unknown Patient' }}</h4>
                                                <span class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded-full animate-pulse">CRITICAL</span>
                                            </div>
                                            
                                            <p class="text-red-800 font-medium mb-2">{{ $alert->alert_message ?? 'Critical vital signs detected' }}</p>
                                            
                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                <div>
                                                    <span class="text-red-700 font-medium">Alert Type:</span>
                                                    <span class="text-red-900">{{ ucfirst($alert->alert_type ?? 'vital_signs') }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-red-700 font-medium">Time:</span>
                                                    <span class="text-red-900">{{ $alert->created_at->diffForHumans() ?? 'Just now' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-red-700 font-medium">Severity:</span>
                                                    <span class="text-red-900 font-bold">{{ ucfirst($alert->severity ?? 'high') }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-red-700 font-medium">Status:</span>
                                                    <span class="text-red-900">{{ ucfirst($alert->status ?? 'active') }}</span>
                                                </div>
                                            </div>
                                            
                                            @if($alert->vital_signs)
                                            <div class="mt-3 p-3 bg-white rounded-lg border border-red-200">
                                                <h5 class="font-semibold text-red-800 text-sm mb-2">Critical Vitals</h5>
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-xs">
                                                    @if($alert->vital_signs->blood_pressure)
                                                    <div>
                                                        <span class="text-red-600">BP:</span>
                                                        <span class="font-bold text-red-800">{{ $alert->vital_signs->blood_pressure }}</span>
                                                    </div>
                                                    @endif
                                                    @if($alert->vital_signs->heart_rate)
                                                    <div>
                                                        <span class="text-red-600">HR:</span>
                                                        <span class="font-bold text-red-800">{{ $alert->vital_signs->heart_rate }} bpm</span>
                                                    </div>
                                                    @endif
                                                    @if($alert->vital_signs->temperature)
                                                    <div>
                                                        <span class="text-red-600">Temp:</span>
                                                        <span class="font-bold text-red-800">{{ $alert->vital_signs->temperature }}°F</span>
                                                    </div>
                                                    @endif
                                                    @if($alert->vital_signs->oxygen_saturation)
                                                    <div>
                                                        <span class="text-red-600">O2:</span>
                                                        <span class="font-bold text-red-800">{{ $alert->vital_signs->oxygen_saturation }}%</span>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="flex flex-col space-y-2">
                                        <button onclick="respondToAlert({{ $alert->id }})" class="bg-red-600 text-white px-4 py-2 rounded-xl hover:bg-red-700 transition-colors font-semibold text-sm">
                                            Respond Now
                                        </button>
                                        <button onclick="viewPatientMonitor({{ $alert->patient_id }})" class="bg-white text-red-600 border border-red-600 px-4 py-2 rounded-xl hover:bg-red-50 transition-colors font-semibold text-sm">
                                            View Patient
                                        </button>
                                        <button onclick="callEmergencyContact({{ $alert->patient_id }})" class="bg-orange-100 text-orange-700 px-4 py-2 rounded-xl hover:bg-orange-200 transition-colors font-semibold text-sm">
                                            Call Contact
                                        </button>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-12">
                                <div class="text-6xl mb-4">✅</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">No Critical Alerts</h3>
                                <p class="text-gray-600">All patients are stable. Great work!</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Sidebar -->
            <div class="space-y-6">
                <!-- Emergency Contacts -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">🚑 Emergency Contacts</h3>
                        <p class="text-sm text-gray-600">Quick access contacts</p>
                    </div>
                    
                    <div class="space-y-3">
                        <button onclick="call911()" class="w-full emergency-contact-btn bg-red-600 text-white">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                <div class="text-left">
                                    <div class="font-bold">Emergency Services</div>
                                    <div class="text-sm opacity-90">911</div>
                                </div>
                            </div>
                        </button>
                        
                        <button onclick="callHospital()" class="w-full emergency-contact-btn bg-blue-600 text-white">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H9m0 0H5m0 0h2M7 7h10M7 11h6m1 4h-7"></path>
                                </svg>
                                <div class="text-left">
                                    <div class="font-bold">Hospital Emergency</div>
                                    <div class="text-sm opacity-90">(555) 123-4567</div>
                                </div>
                            </div>
                        </button>
                        
                        <button onclick="callPoison()" class="w-full emergency-contact-btn bg-green-600 text-white">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <div class="text-left">
                                    <div class="font-bold">Poison Control</div>
                                    <div class="text-sm opacity-90">1-800-222-1222</div>
                                </div>
                            </div>
                        </button>
                        
                        <button onclick="callSupervisor()" class="w-full emergency-contact-btn bg-purple-600 text-white">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <div class="text-left">
                                    <div class="font-bold">Supervisor</div>
                                    <div class="text-sm opacity-90">(555) 987-6543</div>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">⚡ Quick Actions</h3>
                        <p class="text-sm text-gray-600">Emergency protocols</p>
                    </div>
                    
                    <div class="space-y-3">
                        <button onclick="activateCodeBlue()" class="action-btn bg-gradient-to-r from-red-50 to-pink-50 border-red-200 text-red-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Code Blue
                        </button>
                        
                        <button onclick="activateCodeRed()" class="action-btn bg-gradient-to-r from-orange-50 to-red-50 border-orange-200 text-orange-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"></path>
                            </svg>
                            Code Red (Fire)
                        </button>
                        
                        <button onclick="notifyAllStaff()" class="action-btn bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 text-blue-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Notify All Staff
                        </button>
                        
                        <button onclick="evacuationProcedure()" class="action-btn bg-gradient-to-r from-yellow-50 to-orange-50 border-yellow-200 text-yellow-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Evacuation
                        </button>
                    </div>
                </div>

                <!-- Response Log -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">📋 Recent Responses</h3>
                        <p class="text-sm text-gray-600">Last 5 emergency responses</p>
                    </div>
                    
                    <div class="space-y-3">
                        @forelse($recentResponses ?? [] as $response)
                        <div class="response-item p-3 bg-gray-50 rounded-lg border-l-4 
                            @switch($response->severity ?? 'medium')
                                @case('high') border-red-500 @break
                                @case('medium') border-yellow-500 @break
                                @default border-green-500
                            @endswitch">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-semibold text-gray-900">{{ $response->patient_name ?? 'Patient' }}</span>
                                <span class="text-xs text-gray-500">{{ $response->created_at->format('H:i') ?? 'Now' }}</span>
                            </div>
                            <p class="text-xs text-gray-600">{{ $response->action_taken ?? 'Emergency response completed' }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs px-2 py-1 rounded-full font-semibold
                                    @switch($response->status ?? 'resolved')
                                        @case('resolved') bg-green-100 text-green-800 @break
                                        @case('ongoing') bg-yellow-100 text-yellow-800 @break
                                        @default bg-gray-100 text-gray-800
                                    @endswitch">
                                    {{ ucfirst($response->status ?? 'resolved') }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $response->response_time ?? '3' }}min</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-6">
                            <div class="text-2xl mb-2">📋</div>
                            <p class="text-gray-500 text-xs">No recent responses</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Protocol Reference -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">📚 Emergency Protocol Quick Reference</h3>
                <p class="text-gray-600">Essential emergency procedures and protocols</p>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="protocol-card p-6 bg-red-50 border border-red-200 rounded-2xl hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-2xl">💓</span>
                            </div>
                            <h4 class="text-lg font-bold text-red-800">Cardiac Arrest</h4>
                        </div>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li>• Check responsiveness and breathing</li>
                            <li>• Call for help immediately</li>
                            <li>• Start CPR (30:2 ratio)</li>
                            <li>• Use AED if available</li>
                            <li>• Continue until help arrives</li>
                        </ul>
                        <button onclick="viewFullProtocol('cardiac')" class="mt-4 text-red-600 hover:text-red-800 text-sm font-semibold">
                            View Full Protocol →
                        </button>
                    </div>

                    <div class="protocol-card p-6 bg-blue-50 border border-blue-200 rounded-2xl hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-2xl">🫁</span>
                            </div>
                            <h4 class="text-lg font-bold text-blue-800">Respiratory Distress</h4>
                        </div>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li>• Assess airway and breathing</li>
                            <li>• Position for optimal breathing</li>
                            <li>• Administer oxygen if available</li>
                            <li>• Monitor vital signs closely</li>
                            <li>• Prepare for intubation</li>
                        </ul>
                        <button onclick="viewFullProtocol('respiratory')" class="mt-4 text-blue-600 hover:text-blue-800 text-sm font-semibold">
                            View Full Protocol →
                        </button>
                    </div>

                    <div class="protocol-card p-6 bg-yellow-50 border border-yellow-200 rounded-2xl hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-2xl">🩸</span>
                            </div>
                            <h4 class="text-lg font-bold text-yellow-800">Severe Bleeding</h4>
                        </div>
                        <ul class="text-sm text-yellow-700 space-y-1">
                            <li>• Apply direct pressure</li>
                            <li>• Elevate if possible</li>
                            <li>• Use pressure points</li>
                            <li>• Apply tourniquet if needed</li>
                            <li>• Monitor for shock</li>
                        </ul>
                        <button onclick="viewFullProtocol('bleeding')" class="mt-4 text-yellow-600 hover:text-yellow-800 text-sm font-semibold">
                            View Full Protocol →
                        </button>
                    </div>

                    <div class="protocol-card p-6 bg-green-50 border border-green-200 rounded-2xl hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-2xl">⚡</span>
                            </div>
                            <h4 class="text-lg font-bold text-green-800">Seizure</h4>
                        </div>
                        <ul class="text-sm text-green-700 space-y-1">
                            <li>• Ensure safety (clear area)</li>
                            <li>• Do not restrain patient</li>
                            <li>• Time the seizure</li>
                            <li>• Position safely afterward</li>
                            <li>• Monitor vital signs</li>
                        </ul>
                        <button onclick="viewFullProtocol('seizure')" class="mt-4 text-green-600 hover:text-green-800 text-sm font-semibold">
                            View Full Protocol →
                        </button>
                    </div>

                    <div class="protocol-card p-6 bg-purple-50 border border-purple-200 rounded-2xl hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-2xl">🧠</span>
                            </div>
                            <h4 class="text-lg font-bold text-purple-800">Stroke/CVA</h4>
                        </div>
                        <ul class="text-sm text-purple-700 space-y-1">
                            <li>• Assess using FAST protocol</li>
                            <li>• Note time of onset</li>
                            <li>• Keep patient calm</li>
                            <li>• Monitor neurological status</li>
                            <li>• Prepare for rapid transport</li>
                        </ul>
                        <button onclick="viewFullProtocol('stroke')" class="mt-4 text-purple-600 hover:text-purple-800 text-sm font-semibold">
                            View Full Protocol →
                        </button>
                    </div>

                    <div class="protocol-card p-6 bg-orange-50 border border-orange-200 rounded-2xl hover:shadow-lg transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center mr-4">
                                <span class="text-2xl">💊</span>
                            </div>
                            <h4 class="text-lg font-bold text-orange-800">Overdose/Poisoning</h4>
                        </div>
                        <ul class="text-sm text-orange-700 space-y-1">
                            <li>• Identify substance if possible</li>
                            <li>• Contact poison control</li>
                            <li>• Monitor airway and breathing</li>
                            <li>• Administer antidote if known</li>
                            <li>• Prevent further absorption</li>
                        </ul>
                        <button onclick="viewFullProtocol('overdose')" class="mt-4 text-orange-600 hover:text-orange-800 text-sm font-semibold">
                            View Full Protocol →
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Emergency Alert Modal -->
<div id="emergencyAlertModal" class="hidden fixed inset-0 bg-red-900 bg-opacity-75 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white border-4 border-red-500">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-red-900 flex items-center">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    Trigger Emergency Alert
                </h3>
                <button onclick="closeEmergencyModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="emergencyAlertForm" class="space-y-6">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-red-800 font-medium">⚠️ This will trigger an immediate emergency response protocol and notify all relevant staff.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Type</label>
                    <select id="emergencyType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500">
                        <option value="medical">Medical Emergency</option>
                        <option value="cardiac">Cardiac Arrest</option>
                        <option value="respiratory">Respiratory Distress</option>
                        <option value="trauma">Trauma/Injury</option>
                        <option value="fire">Fire Emergency</option>
                        <option value="security">Security Threat</option>
                        <option value="evacuation">Evacuation Required</option>
                        <option value="other">Other Emergency</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Patient (if applicable)</label>
                    <select id="emergencyPatient" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500">
                        <option value="">Select Patient (Optional)</option>
                        @foreach($patients ?? [] as $patient)
                        <option value="{{ $patient->id }}">{{ $patient->name }} - {{ $patient->patient->medical_record_number ?? 'No MRN' }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <input type="text" id="emergencyLocation" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500" placeholder="Specific location or room number">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Description</label>
                    <textarea id="emergencyDescription" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-red-500 focus:border-red-500" placeholder="Detailed description of the emergency situation" required></textarea>
                </div>
                
                <div class="flex justify-end space-x-4 pt-6">
                    <button type="button" onclick="closeEmergencyModal()" class="btn-secondary">Cancel</button>
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-xl transition-colors duration-200">
                        🚨 TRIGGER EMERGENCY ALERT
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.emergency-contact-btn {
    @apply p-4 rounded-2xl font-medium transition-all duration-200 hover:shadow-md cursor-pointer;
}

.action-btn {
    @apply w-full flex items-center p-4 rounded-2xl border font-medium transition-all duration-200 hover:shadow-md cursor-pointer;
}

.protocol-card:hover {
    @apply transform scale-105;
}

.alert-card {
    @apply transition-all duration-300;
}

.alert-card:hover {
    @apply transform scale-102;
}
</style>
@endsection

@push('scripts')
<script>
// Emergency management initialization
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    startRealTimeMonitoring();
    setupEmergencyAlerts();
});

// Setup event listeners
function setupEventListeners() {
    // Emergency alert form submission
    document.getElementById('emergencyAlertForm').addEventListener('submit', handleEmergencyAlert);
    
    // Real-time alert polling
    setInterval(checkForNewAlerts, 10000); // Check every 10 seconds
}

// Real-time monitoring setup
function startRealTimeMonitoring() {
    if (window.pusher) {
        const doctorChannel = pusher.subscribe('doctor.{{ auth()->id() }}');
        
        doctorChannel.bind('critical-alert', function(data) {
            handleNewCriticalAlert(data);
        });
        
        doctorChannel.bind('emergency-declared', function(data) {
            handleEmergencyDeclaration(data);
        });
    }
}

// Setup emergency alert sounds and notifications
function setupEmergencyAlerts() {
    // Request notification permission
    if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
        Notification.requestPermission();
    }
}

// Handle new critical alerts
function handleNewCriticalAlert(data) {
    // Add alert to the list
    addAlertToList(data.alert);
    
    // Show browser notification
    if (Notification.permission === 'granted') {
        new Notification('🚨 CRITICAL ALERT', {
            body: `${data.alert.patient_name}: ${data.alert.message}`,
            icon: '/favicon.ico',
            tag: 'critical-' + data.alert.id,
            requireInteraction: true
        });
    }
    
    // Play alert sound
    playEmergencySound();
    
    // Show toast notification
    window.showToast(`🚨 CRITICAL: ${data.alert.patient_name} - ${data.alert.message}`, 'error', 0);
    
    // Update statistics
    updateEmergencyStats();
}

// Add new alert to the list
function addAlertToList(alert) {
    const alertsList = document.getElementById('criticalAlertsList');
    if (!alertsList) return;

    const alertHTML = `
        <div class="alert-card border-l-4 border-red-500 bg-red-50 p-6 rounded-2xl hover:shadow-lg transition-all duration-300 opacity-0" style="animation: slideIn 0.5s ease-out forwards;">
            <div class="flex items-start justify-between">
                <div class="flex items-start space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 bg-red-100 rounded-3xl flex items-center justify-center">
                            <span class="text-red-600 font-bold text-xl">${alert.patient_name ? alert.patient_name.charAt(0).toUpperCase() : 'P'}</span>
                        </div>
                    </div>
                    
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <h4 class="text-lg font-bold text-red-900">${alert.patient_name || 'Unknown Patient'}</h4>
                            <span class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded-full animate-pulse">CRITICAL</span>
                        </div>
                        
                        <p class="text-red-800 font-medium mb-2">${alert.message || 'Critical situation detected'}</p>
                        
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-red-700 font-medium">Alert Type:</span>
                                <span class="text-red-900">${alert.type || 'vital_signs'}</span>
                            </div>
                            <div>
                                <span class="text-red-700 font-medium">Time:</span>
                                <span class="text-red-900">Just now</span>
                            </div>
                            <div>
                                <span class="text-red-700 font-medium">Severity:</span>
                                <span class="text-red-900 font-bold">${alert.severity || 'High'}</span>
                            </div>
                            <div>
                                <span class="text-red-700 font-medium">Status:</span>
                                <span class="text-red-900">Active</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="flex flex-col space-y-2">
                    <button onclick="respondToAlert(${alert.id})" class="bg-red-600 text-white px-4 py-2 rounded-xl hover:bg-red-700 transition-colors font-semibold text-sm">
                        Respond Now
                    </button>
                    <button onclick="viewPatientMonitor(${alert.patient_id})" class="bg-white text-red-600 border border-red-600 px-4 py-2 rounded-xl hover:bg-red-50 transition-colors font-semibold text-sm">
                        View Patient
                    </button>
                </div>
            </div>
        </div>
    `;

    alertsList.insertAdjacentHTML('afterbegin', alertHTML);
}

// Play emergency alert sound
function playEmergencySound() {
    // Create audio element for emergency sound
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBTiB0fLKeSc');
    audio.volume = 0.8;
    audio.play().catch(() => {
        console.log('Audio play failed - user interaction required');
    });
}

// Emergency action functions
function respondToAlert(alertId) {
    if (confirm('Confirm that you are responding to this critical alert?')) {
        respondToEmergencyAlert(alertId);
    }
}

async function respondToEmergencyAlert(alertId) {
    try {
        const response = await fetch(`/doctor/emergency/respond-alert/${alertId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                response_time: new Date().toISOString(),
                action_taken: 'Doctor responding to alert'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Response logged - proceeding to patient', 'success');
            // Redirect to patient monitor
            if (data.patient_id) {
                window.location.href = `/doctor/patients/${data.patient_id}/monitor`;
            }
        } else {
            window.showToast('❌ Failed to log response', 'error');
        }
    } catch (error) {
        console.error('Response error:', error);
        window.showToast('❌ Error logging response', 'error');
    }
}

function viewPatientMonitor(patientId) {
    window.location.href = `/doctor/patients/${patientId}/monitor`;
}

function callEmergencyContact(patientId) {
    window.showToast('📞 Calling emergency contact...', 'info');
    // Implementation would initiate call to patient's emergency contact
}

function viewActiveEmergencies() {
    window.showToast('🚨 Showing active emergencies...', 'info');
}

// Emergency contact functions
function call911() {
    if (confirm('This will initiate a call to Emergency Services (911). Continue?')) {
        window.location.href = 'tel:911';
        window.showToast('📞 Calling 911...', 'error');
    }
}

function callHospital() {
    if (confirm('Call Hospital Emergency Department?')) {
        window.location.href = 'tel:+15551234567';
        window.showToast('📞 Calling Hospital Emergency...', 'warning');
    }
}

function callPoison() {
    if (confirm('Call Poison Control Center?')) {
        window.location.href = 'tel:+18002221222';
        window.showToast('📞 Calling Poison Control...', 'warning');
    }
}

function callSupervisor() {
    if (confirm('Call Supervisor?')) {
        window.location.href = 'tel:+15559876543';
        window.showToast('📞 Calling Supervisor...', 'info');
    }
}

// Emergency protocol functions
function activateCodeBlue() {
    if (confirm('🚨 ACTIVATE CODE BLUE?\n\nThis will alert all staff of a cardiac/respiratory emergency.')) {
        triggerEmergencyCode('blue');
    }
}

function activateCodeRed() {
    if (confirm('🔥 ACTIVATE CODE RED?\n\nThis will alert all staff of a fire emergency.')) {
        triggerEmergencyCode('red');
    }
}

function notifyAllStaff() {
    if (confirm('📢 Notify all available staff of emergency situation?')) {
        notifyStaff();
    }
}

function evacuationProcedure() {
    if (confirm('🚪 INITIATE EVACUATION PROCEDURE?\n\nThis will begin emergency evacuation protocol.')) {
        initiateEvacuation();
    }
}

async function triggerEmergencyCode(codeType) {
    try {
        const response = await fetch('/doctor/emergency/trigger-code', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                code_type: codeType,
                location: 'Doctor Station',
                triggered_by: '{{ auth()->user()->name }}'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast(`🚨 CODE ${codeType.toUpperCase()} ACTIVATED - All staff notified`, 'error', 0);
        } else {
            window.showToast('❌ Failed to activate emergency code', 'error');
        }
    } catch (error) {
        console.error('Emergency code error:', error);
        window.showToast('❌ Error activating emergency code', 'error');
    }
}

// Emergency alert modal functions
function triggerEmergencyAlert() {
    document.getElementById('emergencyAlertModal').classList.remove('hidden');
}

function closeEmergencyModal() {
    document.getElementById('emergencyAlertModal').classList.add('hidden');
    document.getElementById('emergencyAlertForm').reset();
}

async function handleEmergencyAlert(event) {
    event.preventDefault();
    
    const emergencyType = document.getElementById('emergencyType').value;
    const patientId = document.getElementById('emergencyPatient').value;
    const location = document.getElementById('emergencyLocation').value;
    const description = document.getElementById('emergencyDescription').value;
    
    if (!description) {
        window.showToast('❌ Please provide emergency description', 'error');
        return;
    }
    
    if (confirm('🚨 CONFIRM EMERGENCY ALERT?\n\nThis will immediately notify all relevant staff and trigger emergency protocols.')) {
        try {
            const response = await fetch('/doctor/emergency/trigger-alert', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    type: emergencyType,
                    patient_id: patientId || null,
                    location: location,
                    description: description,
                    severity: 'high',
                    triggered_by: '{{ auth()->id() }}'
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.showToast('🚨 EMERGENCY ALERT TRIGGERED - All staff notified', 'error', 0);
                closeEmergencyModal();
                setTimeout(() => window.location.reload(), 2000);
            } else {
                window.showToast('❌ Failed to trigger emergency alert', 'error');
            }
        } catch (error) {
            console.error('Emergency alert error:', error);
            window.showToast('❌ Error triggering emergency alert', 'error');
        }
    }
}

// Protocol reference functions
function viewProtocols() {
    window.showToast('📚 Opening emergency protocols...', 'info');
}

function viewFullProtocol(protocolType) {
    window.showToast(`📋 Opening ${protocolType} protocol...`, 'info');
    // Implementation would open detailed protocol view
}

// Utility functions
function updateEmergencyStats() {
    // Update the statistics counters
    const criticalCount = document.getElementById('criticalAlertsCount');
    if (criticalCount) {
        criticalCount.textContent = parseInt(criticalCount.textContent) + 1;
    }
}

function checkForNewAlerts() {
    // Fallback polling for new alerts
    fetch('/doctor/emergency/check-alerts', {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.new_alerts && data.new_alerts.length > 0) {
            data.new_alerts.forEach(alert => {
                handleNewCriticalAlert({ alert: alert });
            });
        }
    })
    .catch(error => {
        console.error('Alert check error:', error);
    });
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