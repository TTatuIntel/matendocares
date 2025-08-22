@extends('patient.layout')

@section('title', 'Dashboard')
@section('page-title', 'Health Dashboard')
@section('page-description', 'Monitor your health metrics and stay on top of your wellness journey')

@section('content')
<div class="max-w-6xl mx-auto px-2 sm:px-4 lg:px-6">
    <div class="space-y-4 sm:space-y-6">

        <!-- Enhanced Health Status Overview Card -->
        <div class="medical-card p-4 sm:p-6 pulse-glow dark:bg-gray-800 dark:border-gray-700">
            <!-- Header Section with Better Organization -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
                <div class="flex-1 mb-4 lg:mb-0">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white">Health Status Overview</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-300 font-medium flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                                </svg>
                                {{ now()->format('l, F j, Y') }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Share Access Section - Streamlined -->
                <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4 lg:mb-0 w-full lg:w-auto">
                    <!-- Generate Link Button -->
                    <button
                        id="tempLinkBtn"
                        onclick="generateTempLink(this)"
                        class="btn-primary !py-1.5 !px-3 text-xs flex items-center justify-center"
                        type="button"
                    >
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2l-4.686-4.686a6 6 0 017.743-7.743L15 7z"></path>
                        </svg>
                        Doctor Access
                    </button>

                    <!-- Share Components (Initially Hidden) -->
                    <div id="shareComponents" class="hidden lg:flex lg:flex-row flex-col gap-2 w-full lg:w-auto">
                        <!-- Link Section -->
                        <div class="relative">
                            <input
                                type="text"
                                id="shortLinkInput"
                                readonly
                                onclick="copyShortLink()"
                                class="w-full lg:w-48 text-xs font-mono bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg px-2 py-1 text-blue-800 dark:text-blue-200 cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-800 transition-colors"
                                placeholder="Click to copy link..."
                                title="Click to copy access link"
                            />
                            <div class="absolute right-1 top-1 text-blue-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Code Section -->
                        <div class="relative">
                            <input
                                type="text"
                                id="codeInput"
                                readonly
                                onclick="copyCode()"
                                class="w-full lg:w-24 text-xs font-mono font-bold bg-purple-50 dark:bg-purple-900 border border-purple-200 dark:border-purple-700 rounded-lg px-2 py-1 text-purple-800 dark:text-purple-200 tracking-wider text-center cursor-pointer hover:bg-purple-100 dark:hover:bg-purple-800 transition-colors"
                                placeholder="CODE"
                                title="Click to copy verification code"
                            />
                            <div class="absolute right-1 top-1 text-purple-500">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="flex gap-1">
                            <button
                                id="shareAllBtn"
                                onclick="shareAll()"
                                disabled
                                class="btn-primary !py-1 !px-2 text-xs flex items-center disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Share both link and code"
                            >
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                </svg>
                                Share
                            </button>

                            <button
                                id="revokeBtn"
                                onclick="revokeAccess()"
                                disabled
                                class="btn-secondary !py-1 !px-2 text-xs flex items-center text-red-600 hover:bg-red-50 dark:hover:bg-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                title="Revoke access"
                            >
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Health Score Section -->
                <div class="flex items-center justify-center lg:justify-end space-x-4 sm:space-x-6">
                    <div class="text-center">
                        <div class="text-3xl sm:text-4xl font-bold bg-gradient-to-r from-emerald-600 to-blue-600 bg-clip-text text-transparent mb-2">
                            {{ $healthScore }}%
                        </div>
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 font-semibold">Health Score</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            @if($healthScore >= 90)
                                Excellent
                            @elseif($healthScore >= 80)
                                Very Good
                            @elseif($healthScore >= 70)
                                Good
                            @elseif($healthScore >= 60)
                                Fair
                            @else
                                Needs Attention
                            @endif
                        </div>
                    </div>
                    <div class="relative w-16 h-16 sm:w-20 sm:h-20">
                        <svg class="w-16 h-16 sm:w-20 sm:h-20 transform -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="16" fill="none" stroke="#e5e7eb" stroke-width="2"/>
                            <circle cx="18" cy="18" r="16" fill="none" stroke="url(#healthGradient)" stroke-width="2.5"
                                    stroke-dasharray="{{ $healthScore }}, 100"
                                    stroke-linecap="round">
                                <animate attributeName="stroke-dasharray" dur="2s" from="0,100" to="{{ $healthScore }},100"/>
                            </circle>
                            <defs>
                                <linearGradient id="healthGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#10b981"/>
                                    <stop offset="100%" stop-color="#3b82f6"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-xl sm:text-2xl animate-pulse">
                                @if($healthScore >= 80)
                                    💚
                                @elseif($healthScore >= 60)
                                    💛
                                @else
                                    ❤️
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Health Indicators -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-green-50 to-emerald-100 dark:from-green-900 dark:to-emerald-800 rounded-2xl border border-green-200 dark:border-green-700 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                        <span class="text-xs font-semibold text-green-800 dark:text-green-200">Blood Pressure</span>
                    </div>
                    <div class="text-base sm:text-lg font-bold text-green-800 dark:text-green-200 mb-2">
                        @if($latestVitals && $latestVitals->systolic_bp && $latestVitals->diastolic_bp)
                            @if($latestVitals->systolic_bp < 120 && $latestVitals->diastolic_bp < 80)
                                Normal
                            @elseif($latestVitals->systolic_bp < 140 && $latestVitals->diastolic_bp < 90)
                                Elevated
                            @else
                                High
                            @endif
                        @else
                            --
                        @endif
                    </div>
                    <div class="w-full bg-green-200 dark:bg-green-800 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-green-400 to-green-500 h-2 rounded-full transition-all duration-2000 ease-out"
                             style="width: {{ $latestVitals && $latestVitals->systolic_bp ? (($latestVitals->systolic_bp < 120) ? 85 : (($latestVitals->systolic_bp < 140) ? 65 : 40)) : 0 }}%"></div>
                    </div>
                    <div class="text-xs text-green-700 dark:text-green-300 font-medium">
                        {{ $latestVitals ? ($latestVitals->blood_pressure ?? '--') : '--' }}
                    </div>
                </div>

                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-blue-900 dark:to-indigo-800 rounded-2xl border border-blue-200 dark:border-blue-700 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></div>
                        <span class="text-xs font-semibold text-blue-800 dark:text-blue-200">Heart Rate</span>
                    </div>
                    <div class="text-base sm:text-lg font-bold text-blue-800 dark:text-blue-200 mb-2">
                        @if($latestVitals && $latestVitals->heart_rate)
                            @if($latestVitals->heart_rate >= 60 && $latestVitals->heart_rate <= 100)
                                Good
                            @elseif($latestVitals->heart_rate > 100)
                                High
                            @else
                                Low
                            @endif
                        @else
                            --
                        @endif
                    </div>
                    <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-2 rounded-full transition-all duration-2000 ease-out"
                             style="width: {{ $latestVitals && $latestVitals->heart_rate ? (($latestVitals->heart_rate >= 60 && $latestVitals->heart_rate <= 100) ? 85 : 60) : 0 }}%"></div>
                    </div>
                    <div class="text-xs text-blue-700 dark:text-blue-300 font-medium">
                        {{ $latestVitals ? ($latestVitals->heart_rate ? $latestVitals->heart_rate . ' bpm' : '--') : '--' }}
                    </div>
                </div>

                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 rounded-2xl border border-purple-200 dark:border-purple-700 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-2 h-2 bg-purple-500 rounded-full mr-2 animate-pulse"></div>
                        <span class="text-xs font-semibold text-purple-800 dark:text-purple-200">Weight</span>
                    </div>
                    <div class="text-base sm:text-lg font-bold text-purple-800 dark:text-purple-200 mb-2">
                        @if($latestVitals && $latestVitals->weight)
                            Stable
                        @else
                            --
                        @endif
                    </div>
                    <div class="w-full bg-purple-200 dark:bg-purple-800 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-purple-400 to-purple-500 h-2 rounded-full transition-all duration-2000 ease-out"
                             style="width: {{ $latestVitals && $latestVitals->weight ? 80 : 0 }}%"></div>
                    </div>
                    <div class="text-xs text-purple-700 dark:text-purple-300 font-medium">
                        {{ $latestVitals ? ($latestVitals->weight ? $latestVitals->weight . ' lbs' : '--') : '--' }}
                    </div>
                </div>

                <div class="text-center p-3 sm:p-4 bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900 dark:to-orange-800 rounded-2xl border border-orange-200 dark:border-orange-700 hover:shadow-lg transition-all duration-300">
                    <div class="flex items-center justify-center mb-2">
                        <div class="w-2 h-2 bg-orange-500 rounded-full mr-2 animate-pulse"></div>
                        <span class="text-xs font-semibold text-orange-800 dark:text-orange-200">Blood Glucose</span>
                    </div>
                    <div class="text-base sm:text-lg font-bold text-orange-800 dark:text-orange-200 mb-2">
                        @if($latestVitals && $latestVitals->blood_glucose !== null)
                            @if($latestVitals->blood_glucose < 70)
                                Low
                            @elseif($latestVitals->blood_glucose >= 70 && $latestVitals->blood_glucose <= 140)
                                Normal
                            @else
                                High
                            @endif
                        @else
                            --
                        @endif
                    </div>
                    <div class="w-full bg-orange-200 dark:bg-orange-800 rounded-full h-2 mb-2">
                        <div class="bg-gradient-to-r from-orange-400 to-orange-500 h-2 rounded-full transition-all duration-2000 ease-out"
                             style="width: {{ $latestVitals && $latestVitals->blood_glucose !== null ? (($latestVitals->blood_glucose >= 70 && $latestVitals->blood_glucose <= 140) ? 90 : 60) : 0 }}%">
                        </div>
                    </div>
                    <div class="text-xs text-orange-700 dark:text-orange-300 font-medium">
                        {{ $latestVitals && $latestVitals->blood_glucose !== null ? number_format($latestVitals->blood_glucose, 2) . ' mg/dL' : '--' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Access Status Alert (if active) -->
        <div id="accessStatusAlert" class="hidden">
            <!-- This will be populated by JavaScript -->
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">

            <!-- Left Column - Charts and Analytics -->
            <div class="xl:col-span-2 space-y-4 sm:space-y-6">
                <!-- Quick Actions Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="medical-card p-4 sm:p-6 hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-gray-700">
                        <div class="text-center">
                            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
                                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-2">Record Vitals</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Track your daily health measurements</p>
                            <a href="{{ route('patient.vitals.index') }}" class="btn-primary w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Start Recording
                            </a>
                        </div>
                    </div>

                    <div class="medical-card p-4 sm:p-6 hover:shadow-lg transition-all duration-300 dark:bg-gray-800 dark:border-gray-700">
                        <div class="text-center">
                            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-3 sm:mb-4">
                                <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-2">Book Appointment</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Schedule with your healthcare provider</p>
                            <a href="{{ route('patient.appointments.index') }}" class="btn-primary w-full">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Schedule Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Health Trends Chart -->
                <div class="medical-card p-4 sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 sm:mb-6">
                        <div>
                            <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Health Trends</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300">Your health metrics over time</p>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-3 sm:mt-0">
                            <button onclick="updateChart(event, 'blood_pressure')"
                                    class="chart-filter-btn active px-3 py-2 text-xs font-semibold bg-gradient-to-r from-red-100 to-red-200 dark:from-red-800 dark:to-red-700 text-red-700 dark:text-red-200 rounded-xl hover:from-red-200 hover:to-red-300 transition-all duration-200">
                                Blood Pressure
                            </button>
                            <button onclick="updateChart(event, 'heart_rate')"
                                    class="chart-filter-btn px-3 py-2 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                                Heart Rate
                            </button>
                            <button onclick="updateChart(event, 'weight')"
                                    class="chart-filter-btn px-3 py-2 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                                Weight
                            </button>
                            <button onclick="updateChart(event, 'temperature')"
                                    class="chart-filter-btn px-3 py-2 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200">
                                Temperature
                            </button>
                        </div>
                    </div>
                    <div class="relative h-64 sm:h-80 bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-700 rounded-2xl p-4 border border-gray-100 dark:border-gray-600">
                        <canvas id="healthTrendsChart"></canvas>
                    </div>

                    <!-- Chart Insights -->
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-800 p-3 rounded-2xl border border-blue-200 dark:border-blue-700">
                            <div class="text-xs font-semibold text-blue-800 dark:text-blue-200 mb-1">7-Day Average</div>
                            <div class="text-base font-bold text-blue-900 dark:text-blue-100" id="weeklyAverage">--</div>
                        </div>
                        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-800 p-3 rounded-2xl border border-green-200 dark:border-green-700">
                            <div class="text-xs font-semibold text-green-800 dark:text-green-200 mb-1">Trend</div>
                            <div class="text-base font-bold text-green-900 dark:text-green-100" id="trendDirection">--</div>
                        </div>
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 dark:from-purple-900 dark:to-purple-800 p-3 rounded-2xl border border-purple-200 dark:border-purple-700">
                            <div class="text-xs font-semibold text-purple-800 dark:text-purple-200 mb-1">Status</div>
                            <div class="text-base font-bold text-purple-900 dark:text-purple-100" id="goalProgress">--</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Today's Summary and Actions -->
            <div class="space-y-4">
                <!-- Today's Medications -->
                <div class="medical-card p-4 sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-1">Today's Medications</h3>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">{{ $medicationReminders->count() }} scheduled</p>
                        </div>
                        <a href="{{ route('patient.medications.index') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-xs sm:text-sm font-semibold transition-colors">
                            View All →
                        </a>
                    </div>

                    <div class="space-y-3 max-h-64 overflow-y-auto custom-scrollbar">
                        @forelse($medicationReminders as $reminder)
                        <div class="medication-item flex items-center space-x-3 p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-800 rounded-2xl border border-blue-100 dark:border-blue-700 hover:shadow-md transition-all duration-200">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">{{ $reminder->medication }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-300">{{ $reminder->dosage }}</p>
                                <p class="text-xs {{ $reminder->is_due ? 'text-red-600 font-semibold' : 'text-blue-600 font-semibold' }} mt-1">
                                    {{ $reminder->time }}
                                    @if($reminder->is_due)
                                        (Due Now)
                                    @endif
                                </p>
                            </div>
                            <button onclick="markMedicationTaken('{{ $reminder->id }}')"
                                    class="btn-primary text-xs py-1 px-3 hover:scale-105 transition-transform duration-200">
                                Take
                            </button>
                        </div>
                        @empty
                        <div class="text-center py-6">
                            <div class="text-3xl mb-3">💊</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">No medications scheduled for today</p>
                            <a href="{{ route('patient.medications.index') }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 text-sm font-semibold">
                                Manage Medications →
                            </a>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="medical-card p-4 sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-1">Upcoming Appointments</h3>
                            <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">Next healthcare visits</p>
                        </div>
                        <a href="{{ route('patient.appointments.index') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-xs sm:text-sm font-semibold transition-colors">
                            View All →
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($upcomingAppointments as $appointment)
                        <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-800 rounded-2xl border border-green-100 dark:border-green-700 hover:shadow-md transition-all duration-200">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center">
                                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs sm:text-sm font-bold text-gray-900 dark:text-white">{{ $appointment->doctor_name }}</p>
                                <p class="text-xs text-gray-600 dark:text-gray-300">{{ ucfirst(str_replace('_', ' ', $appointment->type)) }}</p>
                                <p class="text-xs text-green-600 dark:text-green-400 font-semibold mt-1">
                                    {{ $appointment->date }} at {{ $appointment->time }}
                                </p>
                            </div>
                            @if($appointment->is_today)
                            <span class="bg-gradient-to-r from-red-400 to-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">Today</span>
                            @elseif($appointment->is_urgent)
                            <span class="bg-gradient-to-r from-orange-400 to-orange-500 text-white text-xs font-bold px-2 py-1 rounded-full">Urgent</span>
                            @else
                            <span class="bg-gradient-to-r from-blue-400 to-blue-500 text-white text-xs font-bold px-2 py-1 rounded-full">Upcoming</span>
                            @endif
                        </div>
                        @empty
                        <div class="text-center py-6">
                            <div class="text-3xl mb-3">📅</div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">No upcoming appointments</p>
                            <a href="{{ route('patient.appointments.index') }}" class="btn-secondary text-xs py-2 px-3">
                                Schedule Appointment
                            </a>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Health Tips -->
                <div class="medical-card p-4 sm:p-6 dark:bg-gray-800 dark:border-gray-700">
                    <div class="mb-4">
                        <h3 class="text-base sm:text-lg font-bold text-gray-900 dark:text-white mb-1">💡 Doctor's Health Tips</h3>
                        <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-300">Personalized recommendations from your doctor</p>
                    </div>
                    <div class="space-y-3">
                        @forelse(($doctorMeds ?? collect())->whereNotNull('health_tips') as $med)
                        <div class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-800 rounded-2xl border border-blue-200 dark:border-blue-700">
                            <div class="flex items-start space-x-2">
                                <div class="text-base">💡</div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-xs mb-1">
                                        Health Tip for {{ $med->name }}
                                    </h4>
                                    <p class="text-xs text-gray-700 dark:text-gray-300">{{ $med->health_tips }}</p>
                                    @if($med->relationLoaded('doctor') && $med->doctor)
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">- Dr. {{ $med->doctor->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900 dark:to-indigo-800 rounded-2xl border border-blue-200 dark:border-blue-700">
                            <p class="text-xs text-blue-800 dark:text-blue-200 font-medium">No health tips available from your doctor yet.</p>
                        </div>
                        <div class="p-3 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-800 rounded-2xl border border-green-200 dark:border-green-700">
                            <p class="text-xs text-green-800 dark:text-green-200 font-medium">💧 Remember to stay hydrated throughout the day.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Timeline -->
        <div class="medical-card p-4 sm:p-6 dark:bg-gray-800 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4 sm:mb-6">
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">Recent Health Activity</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Your latest health updates and milestones</p>
                </div>
                <a href="{{ route('patient.vitals.index') }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 text-xs sm:text-sm font-semibold transition-colors">
                    View All Activity →
                </a>
            </div>

            <div class="space-y-4">
                @forelse($recentUpdates as $update)
                <div class="flex items-start space-x-3 p-4 bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-600 rounded-2xl border border-gray-200 dark:border-gray-600 hover:shadow-md transition-all duration-200">
                    <div class="flex-shrink-0 mt-1">
                        <div class="w-8 h-8 rounded-2xl flex items-center justify-center {{ $update->status === 'critical' ? 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400' : ($update->status === 'warning' ? 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400' : 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400') }}">
                            @if($update->status === 'critical')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            @elseif($update->status === 'warning')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-gray-900 dark:text-white mb-1">{{ $update->type }}</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300 mb-2">{{ $update->description }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $update->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <div class="text-center py-8 sm:py-12">
                    <div class="text-4xl sm:text-6xl mb-4">📊</div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-white mb-2">No Recent Activity</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mb-4 sm:mb-6">Start recording your health data to see activity here</p>
                    <a href="{{ route('patient.vitals.index') }}" class="btn-primary">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Record Health Data
                    </a>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentAccessData = null;

// Utility functions
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm max-w-sm ${
        type === 'success' ? 'bg-green-600' :
        type === 'error' ? 'bg-red-600' :
        type === 'warning' ? 'bg-yellow-600' : 'bg-blue-600'
    }`;

    toast.innerHTML = `
        <div class="flex items-center">
            <div class="flex-shrink-0 mr-2">
                ${type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️'}
            </div>
            <div class="flex-1">${message}</div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 5000);
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '{{ csrf_token() }}';
}

// Enhanced temp link generation
async function generateTempLink(btn) {
    const original = btn.innerHTML;
    const shareComponents = document.getElementById('shareComponents');
    const shortInput = document.getElementById('shortLinkInput');
    const codeInput = document.getElementById('codeInput');
    const shareAllBtn = document.getElementById('shareAllBtn');
    const revokeBtn = document.getElementById('revokeBtn');

    btn.disabled = true;
    btn.innerHTML = `
        <svg class="animate-spin w-4 h-4 mr-2 inline" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.37 0 0 5.37 0 12h4z"></path>
        </svg>
        Generating...
    `;

    try {
        const response = await fetch(`{{ route('patient.temp_access.generate') }}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                access_reason: 'External doctor consultation and medical review',
                duration_days: 3,
                notes: 'Generated from patient dashboard for external medical consultation'
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || data.message || 'Failed to generate access link');
        }

        // Store access data globally
        currentAccessData = data;

        // Populate the UI
        shortInput.value = data.short_url || '';
        codeInput.value = data.verification_code || '';

        // Show share components with animation
        shareComponents.classList.remove('hidden');
        shareComponents.style.animation = 'slideDown 0.5s ease-out';
        shareAllBtn.disabled = false;
        revokeBtn.disabled = false;

        // Update button text
        btn.innerHTML = '✅ Access Generated Successfully';
        btn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';

        // Show success message
        showToast(`✅ Access link generated successfully! Share both the link and verification code with your doctor.`, 'success');

        // Show access status alert
        showAccessStatusAlert(data);

    } catch (error) {
        console.error('Error generating temp link:', error);
        showToast(`❌ ${error.message}`, 'error');
    } finally {
        btn.disabled = false;
        setTimeout(() => {
            btn.innerHTML = original;
            btn.style.background = '';
        }, 5000);
    }
}

// Copy functions
function copyShortLink() {
    const input = document.getElementById('shortLinkInput');
    if (!input.value) {
        showToast('❌ No link to copy', 'error');
        return;
    }

    input.select();
    document.execCommand('copy');
    showToast('🔗 Link copied to clipboard!', 'success');
}

function copyCode() {
    const input = document.getElementById('codeInput');
    if (!input.value) {
        showToast('❌ No code to copy', 'error');
        return;
    }

    input.select();
    document.execCommand('copy');
    showToast('🔑 Verification code copied!', 'success');
}

// Enhanced share function
async function shareAll() {
    const link = document.getElementById('shortLinkInput').value;
    const code = document.getElementById('codeInput').value;

    if (!link || !code) {
        showToast('❌ Please generate a link first', 'error');
        return;
    }

    const shareText = `🏥 Medical Record Access

Access Link: ${link}
Verification Code: ${code}

Instructions for Doctor:
1. Click the link above
2. Fill in your medical credentials
3. Enter the verification code exactly as shown
4. You'll then have secure access to my medical data

This link expires in 3 days for security.`;

    // Try native sharing first
    if (navigator.share) {
        try {
            await navigator.share({
                title: 'Medical Record Access',
                text: shareText
            });
            showToast('✅ Successfully shared!', 'success');
            return;
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.log('Native share failed, falling back to clipboard');
            }
        }
    }

    // Fallback to clipboard
    try {
        await navigator.clipboard.writeText(shareText);
        showToast('📋 Link and code copied to clipboard!', 'success');
    } catch (error) {
        // Final fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = shareText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('📋 Link and code copied!', 'success');
    }
}

// Revoke access
async function revokeAccess() {
    if (!currentAccessData) {
        showToast('❌ No active access to revoke', 'error');
        return;
    }

    if (!confirm('Are you sure you want to revoke doctor access? This will immediately disable the link.')) {
        return;
    }

    try {
        const response = await fetch(`{{ route('patient.temp_access.revoke') }}`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            },
            body: JSON.stringify({
                patient_id: '{{ $patient->id ?? "" }}',
                reason: 'Revoked by patient from dashboard'
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.error || 'Failed to revoke access');
        }

        // Reset UI
        document.getElementById('shareComponents').classList.add('hidden');
        document.getElementById('shortLinkInput').value = '';
        document.getElementById('codeInput').value = '';
        document.getElementById('shareAllBtn').disabled = true;
        document.getElementById('revokeBtn').disabled = true;

        // Reset generate button
        const generateBtn = document.getElementById('tempLinkBtn');
        generateBtn.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2l-4.686-4.686a6 6 0 017.743-7.743L15 7z"></path>
            </svg>
            Generate Doctor Access
        `;
        generateBtn.style.background = '';

        hideAccessStatusAlert();
        currentAccessData = null;

        showToast('✅ Doctor access revoked successfully', 'success');

    } catch (error) {
        console.error('Error revoking access:', error);
        showToast(`❌ ${error.message}`, 'error');
    }
}

// Show access status alert
function showAccessStatusAlert(data) {
    const alertContainer = document.getElementById('accessStatusAlert');
    if (!alertContainer) return;

    alertContainer.innerHTML = `
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900 dark:to-emerald-800 border border-green-200 dark:border-green-700 rounded-2xl p-4">
            <div class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-green-900 dark:text-green-100 text-sm">Doctor Access Active</h4>
                    <p class="text-green-700 dark:text-green-200 text-sm mt-1">
                        External doctors can now access your medical data using the generated link and verification code.
                    </p>
                    <div class="mt-2 text-xs text-green-600 dark:text-green-300">
                        <div>📅 Expires: ${data.expires_human || 'in 3 days'}</div>
                        <div>🔑 Code: ${data.formatted_code || data.verification_code}</div>
                    </div>
                </div>
                <button onclick="hideAccessStatusAlert()" class="text-green-400 hover:text-green-600 dark:text-green-300 dark:hover:text-green-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    `;

    alertContainer.classList.remove('hidden');
}

// Hide access status alert
function hideAccessStatusAlert() {
    const alertContainer = document.getElementById('accessStatusAlert');
    if (alertContainer) {
        alertContainer.classList.add('hidden');
        alertContainer.innerHTML = '';
    }
}

// Check for existing access on page load
async function checkExistingAccess() {
    try {
        const response = await fetch(`{{ route('patient.temp_access.status') }}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken()
            }
        });

        const data = await response.json();

        if (data.success && data.has_active_access) {
            currentAccessData = data.access;

            // Show existing access info if available
            if (data.access.doctor_verified) {
                showAccessStatusAlert({
                    expires_human: data.access.expires_human,
                    formatted_code: data.access.verification_code
                });
            }
        }
    } catch (error) {
        console.log('Could not check existing access:', error);
    }
}

// Medication functions
async function markMedicationTaken(reminderId) {
    const button = event.target;
    const originalContent = button.innerHTML;
    const medicationItem = button.closest('.medication-item');

    button.disabled = true;
    button.innerHTML = `
        <svg class="animate-spin w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Taking...
    `;

    try {
        const response = await fetch('{{ route("patient.medications.mark-taken") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'Accept': 'application/json'
            },
            body: JSON.stringify({ reminder_id: reminderId })
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Failed to mark medication as taken');
        }

        // Update UI to show success
        button.innerHTML = `
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Taken
        `;
        button.className = 'bg-gradient-to-r from-green-500 to-green-600 text-white text-xs py-1 px-3 rounded-xl font-semibold cursor-default';
        medicationItem.style.opacity = '0.7';
        medicationItem.style.transform = 'scale(0.98)';

        showToast('💊 Medication marked as taken successfully!', 'success');

    } catch (error) {
        console.error('Error marking medication:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showToast(`❌ ${error.message}`, 'error');
    }
}

// Chart functionality
function initDashboardCharts() {
    const trendsData = @json($trendsData);
    const canvas = document.getElementById('healthTrendsChart');
    if (!canvas || typeof Chart === 'undefined') return;

    const ctx = canvas.getContext('2d');
    const healthChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendsData.labels || [],
            datasets: [
                {
                    label: 'Systolic BP',
                    data: trendsData.systolic || [],
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#ef4444',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    borderWidth: 2
                },
                {
                    label: 'Diastolic BP',
                    data: trendsData.diastolic || [],
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    tension: 0.4,
                    fill: false,
                    pointBackgroundColor: '#dc2626',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 3,
                    borderWidth: 2,
                    borderDash: [5, 5]
                },
                {
                    label: 'Heart Rate',
                    data: trendsData.heart_rate || [],
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: false,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    borderWidth: 2,
                    hidden: true
                },
                {
                    label: 'Weight',
                    data: trendsData.weight || [],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1',
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    borderWidth: 2,
                    hidden: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { usePointStyle: true, padding: 15, font: { size: 10, family: 'Inter', weight: '600' } }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#374151',
                    bodyColor: '#6b7280',
                    borderColor: '#e5e7eb',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 8
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: '#9ca3af', font: { size: 10, family: 'Inter' } } },
                y: { beginAtZero: false, grid: { color: '#f3f4f6' }, ticks: { color: '#9ca3af', font: { size: 10, family: 'Inter' } } },
                y1: { type: 'linear', display: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#9ca3af', font: { size: 10, family: 'Inter' } } }
            },
            animation: { duration: 1500, easing: 'easeInOutQuart' }
        }
    });

    window.updateChart = function(ev, type) {
        document.querySelectorAll('.chart-filter-btn').forEach(btn => {
            btn.className = 'chart-filter-btn px-3 py-2 text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200';
        });

        const activeClassMap = {
            blood_pressure: 'chart-filter-btn active px-3 py-2 text-xs font-semibold bg-gradient-to-r from-red-100 to-red-200 dark:from-red-800 dark:to-red-700 text-red-700 dark:text-red-200 rounded-xl',
            heart_rate: 'chart-filter-btn active px-3 py-2 text-xs font-semibold bg-gradient-to-r from-blue-100 to-blue-200 dark:from-blue-800 dark:to-blue-700 text-blue-700 dark:text-blue-200 rounded-xl',
            weight: 'chart-filter-btn active px-3 py-2 text-xs font-semibold bg-gradient-to-r from-purple-100 to-purple-200 dark:from-purple-800 dark:to-purple-700 text-purple-700 dark:text-purple-200 rounded-xl',
            temperature: 'chart-filter-btn active px-3 py-2 text-xs font-semibold bg-gradient-to-r from-orange-100 to-orange-200 dark:from-orange-800 dark:to-orange-700 text-orange-700 dark:text-orange-200 rounded-xl'
        };

        if (ev?.target) {
            ev.target.className = activeClassMap[type] || ev.target.className;
        }

        const ds = healthChart.data.datasets;
        if (type === 'blood_pressure') {
            ds[0].hidden = false; ds[1].hidden = false; ds[2].hidden = true; ds[3].hidden = true;
            const sAvg = (trendsData.systolic || []).filter(x => x).reduce((a, b) => a + b, 0) / ((trendsData.systolic || []).filter(x => x).length || 1);
            const dAvg = (trendsData.diastolic || []).filter(x => x).reduce((a, b) => a + b, 0) / ((trendsData.diastolic || []).filter(x => x).length || 1);
            document.getElementById('weeklyAverage').textContent = `${Math.round(sAvg)}/${Math.round(dAvg)} mmHg`;
            document.getElementById('trendDirection').textContent = '➡️ Stable';
            document.getElementById('goalProgress').textContent = 'Normal';
        } else if (type === 'heart_rate') {
            ds[0].hidden = true; ds[1].hidden = true; ds[2].hidden = false; ds[3].hidden = true;
            const hrAvg = (trendsData.heart_rate || []).filter(x => x).reduce((a, b) => a + b, 0) / ((trendsData.heart_rate || []).filter(x => x).length || 1);
            document.getElementById('weeklyAverage').textContent = `${Math.round(hrAvg)} bpm`;
            document.getElementById('trendDirection').textContent = '➡️ Stable';
            document.getElementById('goalProgress').textContent = 'Good';
        } else if (type === 'weight') {
            ds[0].hidden = true; ds[1].hidden = true; ds[2].hidden = true; ds[3].hidden = false;
            const wAvg = (trendsData.weight || []).filter(x => x).reduce((a, b) => a + b, 0) / ((trendsData.weight || []).filter(x => x).length || 1);
            document.getElementById('weeklyAverage').textContent = `${Math.round(wAvg * 10) / 10} lbs`;
            document.getElementById('trendDirection').textContent = '➡️ Stable';
            document.getElementById('goalProgress').textContent = 'On Track';
        }

        healthChart.update('active');
    };

    // Initialize insights
    if ((trendsData.systolic || []).length) {
        const sVals = (trendsData.systolic || []).filter(x => x);
        const dVals = (trendsData.diastolic || []).filter(x => x);
        const sAvg = sVals.reduce((a, b) => a + b, 0) / (sVals.length || 1);
        const dAvg = dVals.reduce((a, b) => a + b, 0) / (dVals.length || 1);
        document.getElementById('weeklyAverage').textContent = `${Math.round(sAvg)}/${Math.round(dAvg)} mmHg`;
        document.getElementById('trendDirection').textContent = '➡️ Stable';
        document.getElementById('goalProgress').textContent = 'Normal';
    }
}

// Initialize everything when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Check for existing access
    checkExistingAccess();

    // Load Chart.js and initialize charts
    if (typeof Chart === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
        script.onload = initDashboardCharts;
        document.head.appendChild(script);
    } else {
        initDashboardCharts();
    }

    // Add slide down animation styles
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
});

// Expose functions globally
window.generateTempLink = generateTempLink;
window.copyShortLink = copyShortLink;
window.copyCode = copyCode;
window.shareAll = shareAll;
window.revokeAccess = revokeAccess;
window.markMedicationTaken = markMedicationTaken;
</script>
@endpush