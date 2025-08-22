@extends('doctor.layout')

@section('title', 'Analytics')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                        📊 Analytics Dashboard
                    </h1>
                    <p class="text-gray-600 text-lg">Comprehensive insights into your patients' health trends and outcomes</p>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">
                    <select id="timeRangeFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 bg-white shadow-sm">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last year</option>
                    </select>
                    <button onclick="exportAnalytics()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export Report
                    </button>
                    <button onclick="generateInsights()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Generate Insights
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Performance Indicators -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="healthyPatientsCount">{{ $analytics['healthy_patients'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Healthy Patients</div>
                <div class="text-xs text-gray-400 mt-1">
                    <span class="text-green-600 font-semibold">{{ $analytics['healthy_percentage'] ?? 0 }}%</span> of total
                </div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="atRiskPatientsCount">{{ $analytics['at_risk_patients'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">At Risk</div>
                <div class="text-xs text-gray-400 mt-1">Require monitoring</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="criticalPatientsCount">{{ $analytics['critical_patients'] ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Critical</div>
                <div class="text-xs text-gray-400 mt-1">Need immediate care</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2" id="improvementRate">{{ $analytics['improvement_rate'] ?? 0 }}%</div>
                <div class="text-sm text-gray-600 font-medium">Improvement Rate</div>
                <div class="text-xs text-gray-400 mt-1">This month</div>
            </div>
        </div>

        <!-- Main Analytics Content -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Charts Section -->
            <div class="xl:col-span-2 space-y-8">
                <!-- Patient Health Distribution -->
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Patient Health Distribution</h3>
                                <p class="text-gray-600">Overview of patient health status categories</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="toggleChartType('doughnut')" id="doughnutBtn" class="chart-type-btn active">
                                    Pie Chart
                                </button>
                                <button onclick="toggleChartType('bar')" id="barBtn" class="chart-type-btn">
                                    Bar Chart
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="relative h-96">
                            <canvas id="healthDistributionChart"></canvas>
                        </div>
                        
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                            <div class="text-center p-4 bg-green-50 rounded-2xl border border-green-200">
                                <div class="text-2xl font-bold text-green-700 mb-1">{{ $analytics['healthy_patients'] ?? 0 }}</div>
                                <div class="text-sm text-green-600 font-medium">Healthy</div>
                            </div>
                            <div class="text-center p-4 bg-yellow-50 rounded-2xl border border-yellow-200">
                                <div class="text-2xl font-bold text-yellow-700 mb-1">{{ $analytics['at_risk_patients'] ?? 0 }}</div>
                                <div class="text-sm text-yellow-600 font-medium">At Risk</div>
                            </div>
                            <div class="text-center p-4 bg-red-50 rounded-2xl border border-red-200">
                                <div class="text-2xl font-bold text-red-700 mb-1">{{ $analytics['critical_patients'] ?? 0 }}</div>
                                <div class="text-sm text-red-600 font-medium">Critical</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-2xl border border-gray-200">
                                <div class="text-2xl font-bold text-gray-700 mb-1">{{ $analytics['inactive_patients'] ?? 0 }}</div>
                                <div class="text-sm text-gray-600 font-medium">Inactive</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Trends Over Time -->
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-2">Health Trends Over Time</h3>
                                <p class="text-gray-600">Patient health metrics progression</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button onclick="updateTrendsChart('blood_pressure')" class="trend-filter-btn active">
                                    Blood Pressure
                                </button>
                                <button onclick="updateTrendsChart('heart_rate')" class="trend-filter-btn">
                                    Heart Rate
                                </button>
                                <button onclick="updateTrendsChart('bmi')" class="trend-filter-btn">
                                    BMI
                                </button>
                                <button onclick="updateTrendsChart('adherence')" class="trend-filter-btn">
                                    Adherence
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="relative h-96">
                            <canvas id="trendsChart"></canvas>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-2xl border border-blue-200">
                                <div class="text-sm font-semibold text-blue-800 mb-1">Average This Period</div>
                                <div class="text-xl font-bold text-blue-900" id="avgValue">--</div>
                            </div>
                            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-2xl border border-green-200">
                                <div class="text-sm font-semibold text-green-800 mb-1">Trend Direction</div>
                                <div class="text-xl font-bold text-green-900" id="trendDir">--</div>
                            </div>
                            <div class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-2xl border border-purple-200">
                                <div class="text-sm font-semibold text-purple-800 mb-1">Patients Improving</div>
                                <div class="text-xl font-bold text-purple-900" id="improvingCount">--</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Risk Factors Analysis -->
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Risk Factors Analysis</h3>
                        <p class="text-gray-600">Common risk factors across your patient population</p>
                    </div>

                    <div class="p-8">
                        <div class="relative h-80">
                            <canvas id="riskFactorsChart"></canvas>
                        </div>
                        
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-3">High Risk Conditions</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                                        <span class="text-sm font-medium text-red-800">Hypertension</span>
                                        <span class="text-sm font-bold text-red-600">{{ $analytics['hypertension_patients'] ?? 0 }} patients</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg border border-orange-200">
                                        <span class="text-sm font-medium text-orange-800">Diabetes</span>
                                        <span class="text-sm font-bold text-orange-600">{{ $analytics['diabetes_patients'] ?? 0 }} patients</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                        <span class="text-sm font-medium text-yellow-800">High Cholesterol</span>
                                        <span class="text-sm font-bold text-yellow-600">{{ $analytics['cholesterol_patients'] ?? 0 }} patients</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h4 class="font-semibold text-gray-800 mb-3">Lifestyle Factors</h4>
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <span class="text-sm font-medium text-gray-800">Smoking</span>
                                        <span class="text-sm font-bold text-gray-600">{{ $analytics['smoking_patients'] ?? 0 }} patients</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                        <span class="text-sm font-medium text-blue-800">Low Activity</span>
                                        <span class="text-sm font-bold text-blue-600">{{ $analytics['low_activity_patients'] ?? 0 }} patients</span>
                                    </div>
                                    <div class="flex items-center justify-between p-3 bg-purple-50 rounded-lg border border-purple-200">
                                        <span class="text-sm font-medium text-purple-800">Poor Sleep</span>
                                        <span class="text-sm font-bold text-purple-600">{{ $analytics['poor_sleep_patients'] ?? 0 }} patients</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Performance Metrics -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">📈 Performance Metrics</h3>
                        <p class="text-sm text-gray-600">Your practice statistics</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="metric-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Patient Satisfaction</span>
                                <span class="text-sm font-bold text-green-600">{{ $analytics['satisfaction_score'] ?? 95 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-green-400 to-green-500 h-2 rounded-full transition-all duration-1000" 
                                     style="width: {{ $analytics['satisfaction_score'] ?? 95 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="metric-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Treatment Adherence</span>
                                <span class="text-sm font-bold text-blue-600">{{ $analytics['adherence_rate'] ?? 87 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-2 rounded-full transition-all duration-1000" 
                                     style="width: {{ $analytics['adherence_rate'] ?? 87 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="metric-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Readmission Rate</span>
                                <span class="text-sm font-bold text-red-600">{{ $analytics['readmission_rate'] ?? 3 }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-red-400 to-red-500 h-2 rounded-full transition-all duration-1000" 
                                     style="width: {{ $analytics['readmission_rate'] ?? 3 }}%"></div>
                            </div>
                        </div>
                        
                        <div class="metric-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Response Time</span>
                                <span class="text-sm font-bold text-purple-600">{{ $analytics['response_time'] ?? 2.5 }}h avg</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-gradient-to-r from-purple-400 to-purple-500 h-2 rounded-full transition-all duration-1000" 
                                     style="width: 75%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Patients -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">🏆 Top Performing Patients</h3>
                        <p class="text-sm text-gray-600">Best adherence this month</p>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($topPatients ?? [] as $index => $patient)
                        <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-100">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ $index + 1 }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $patient['name'] ?? 'Patient ' . ($index + 1) }}</p>
                                <p class="text-xs text-green-600 font-medium">{{ $patient['adherence'] ?? '95' }}% adherence</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">{{ $patient['improvement'] ?? '+5%' }}</div>
                            </div>
                        </div>
                        @empty
                        @for($i = 1; $i <= 5; $i++)
                        <div class="flex items-center space-x-3 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-2xl border border-green-100">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ $i }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">Patient {{ chr(64 + $i) }}</p>
                                <p class="text-xs text-green-600 font-medium">{{ 100 - ($i * 2) }}% adherence</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">+{{ 8 - $i }}%</div>
                            </div>
                        </div>
                        @endfor
                        @endforelse
                    </div>
                </div>

                <!-- Alerts & Reminders -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">🔔 Alerts & Reminders</h3>
                        <p class="text-sm text-gray-600">Important notifications</p>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="alert-item p-3 bg-red-50 rounded-2xl border border-red-200">
                            <div class="flex items-start space-x-2">
                                <div class="status-indicator status-critical mt-1"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-red-800">2 Critical Patients</p>
                                    <p class="text-xs text-red-600">Require immediate attention</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert-item p-3 bg-yellow-50 rounded-2xl border border-yellow-200">
                            <div class="flex items-start space-x-2">
                                <div class="status-indicator status-warning mt-1"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-yellow-800">5 Overdue Reviews</p>
                                    <p class="text-xs text-yellow-600">Pending your review</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert-item p-3 bg-blue-50 rounded-2xl border border-blue-200">
                            <div class="flex items-start space-x-2">
                                <div class="status-indicator status-normal mt-1"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-blue-800">Weekly Report Due</p>
                                    <p class="text-xs text-blue-600">Generate analytics report</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">⚡ Quick Actions</h3>
                        <p class="text-sm text-gray-600">Analytics tools</p>
                    </div>
                    
                    <div class="space-y-3">
                        <button onclick="generateWeeklyReport()" class="action-btn bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 text-blue-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Weekly Report
                        </button>
                        
                        <button onclick="compareMetrics()" class="action-btn bg-gradient-to-r from-green-50 to-emerald-50 border-green-200 text-green-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                            </svg>
                            Compare Metrics
                        </button>
                        
                        <button onclick="predictiveAnalysis()" class="action-btn bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200 text-purple-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            Predictive Analysis
                        </button>
                        
                        <button onclick="benchmarkAnalysis()" class="action-btn bg-gradient-to-r from-orange-50 to-red-50 border-orange-200 text-orange-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                            Benchmark Analysis
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Patient Outcomes Table -->
            <div class="medical-card">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Patient Outcomes Summary</h3>
                    <p class="text-gray-600">Key metrics for each patient</p>
                </div>
                
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Adherence</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($patientOutcomes ?? [] as $outcome)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $outcome['name'] ?? 'Patient' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="status-badge {{ $outcome['status'] ?? 'normal' }}">
                                            <div class="status-indicator status-{{ $outcome['status'] ?? 'normal' }}"></div>
                                            {{ ucfirst($outcome['status'] ?? 'normal') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $outcome['adherence'] ?? '95' }}%</td>
                                    <td class="px-4 py-3 text-sm {{ ($outcome['trend'] ?? 'up') === 'up' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ ($outcome['trend'] ?? 'up') === 'up' ? '↗️ Improving' : '↘️ Declining' }}
                                    </td>
                                </tr>
                                @empty
                                @for($i = 1; $i <= 8; $i++)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">Patient {{ chr(64 + $i) }}</td>
                                    <td class="px-4 py-3">
                                        <span class="status-badge {{ $i <= 6 ? 'normal' : ($i == 7 ? 'warning' : 'critical') }}">
                                            <div class="status-indicator status-{{ $i <= 6 ? 'normal' : ($i == 7 ? 'warning' : 'critical') }}"></div>
                                            {{ $i <= 6 ? 'Normal' : ($i == 7 ? 'Warning' : 'Critical') }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ 100 - $i }}%</td>
                                    <td class="px-4 py-3 text-sm {{ $i <= 6 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $i <= 6 ? '↗️ Improving' : '↘️ Declining' }}
                                    </td>
                                </tr>
                                @endfor
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Monthly Progress -->
            <div class="medical-card">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Monthly Progress</h3>
                    <p class="text-gray-600">Month-over-month improvements</p>
                </div>
                
                <div class="p-6">
                    <div class="space-y-6">
                        <div class="progress-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Overall Health Score</span>
                                <span class="text-sm font-bold text-green-600">+5.2%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-green-400 to-green-500 h-3 rounded-full transition-all duration-2000" 
                                     style="width: 78%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0</span>
                                <span>100</span>
                            </div>
                        </div>
                        
                        <div class="progress-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Treatment Adherence</span>
                                <span class="text-sm font-bold text-blue-600">+3.1%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-blue-400 to-blue-500 h-3 rounded-full transition-all duration-2000" 
                                     style="width: 87%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        
                        <div class="progress-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Patient Satisfaction</span>
                                <span class="text-sm font-bold text-purple-600">+2.8%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-purple-400 to-purple-500 h-3 rounded-full transition-all duration-2000" 
                                     style="width: 95%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0%</span>
                                <span>100%</span>
                            </div>
                        </div>
                        
                        <div class="progress-item">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700">Response Time</span>
                                <span class="text-sm font-bold text-orange-600">-12.5%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-orange-400 to-orange-500 h-3 rounded-full transition-all duration-2000" 
                                     style="width: 25%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>0h</span>
                                <span>24h</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chart-type-btn {
    @apply px-4 py-2 text-sm font-medium rounded-lg border transition-all duration-200;
}

.chart-type-btn.active {
    @apply bg-blue-100 text-blue-700 border-blue-300;
}

.chart-type-btn:not(.active) {
    @apply bg-gray-100 text-gray-700 border-gray-300 hover:bg-gray-200;
}

.trend-filter-btn {
    @apply px-3 py-2 text-xs font-semibold rounded-lg transition-all duration-200;
}

.trend-filter-btn.active {
    @apply bg-gradient-to-r from-blue-100 to-blue-200 text-blue-700;
}

.trend-filter-btn:not(.active) {
    @apply bg-gray-100 text-gray-700 hover:bg-gray-200;
}

.metric-item {
    @apply p-3 bg-gray-50 rounded-xl;
}

.alert-item {
    @apply transition-all duration-200 hover:shadow-md;
}

.progress-item {
    @apply p-4 bg-gray-50 rounded-xl;
}

.action-btn {
    @apply w-full flex items-center p-4 rounded-2xl border font-medium transition-all duration-200 hover:shadow-md cursor-pointer text-decoration-none;
}
</style>
@endsection

@push('scripts')
<script>
// Analytics data
let analyticsData = @json($analytics ?? []);

let healthDistributionChart;
let trendsChart;
let riskFactorsChart;

// Initialize analytics dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    setupEventListeners();
    animateMetrics();
});

// Initialize all charts
function initializeCharts() {
    initializeHealthDistributionChart();
    initializeTrendsChart();
    initializeRiskFactorsChart();
}

// Initialize health distribution chart
function initializeHealthDistributionChart() {
    const ctx = document.getElementById('healthDistributionChart');
    if (!ctx) return;

    healthDistributionChart = new Chart(ctx.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Healthy', 'At Risk', 'Critical', 'Inactive'],
            datasets: [{
                data: [
                    analyticsData.healthy_patients || 45,
                    analyticsData.at_risk_patients || 12,
                    analyticsData.critical_patients || 3,
                    analyticsData.inactive_patients || 8
                ],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#6b7280'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
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
            cutout: '60%',
            animation: {
                animateRotate: true,
                duration: 2000
            }
        }
    });
}

// Initialize trends chart
function initializeTrendsChart() {
    const ctx = document.getElementById('trendsChart');
    if (!ctx) return;

    trendsChart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Average Blood Pressure',
                data: [125, 123, 121, 120],
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true,
                pointBackgroundColor: '#ef4444',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 3,
                pointRadius: 6,
                borderWidth: 3
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
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    // Initialize with blood pressure data
    updateTrendsChart('blood_pressure');
}

// Initialize risk factors chart
function initializeRiskFactorsChart() {
    const ctx = document.getElementById('riskFactorsChart');
    if (!ctx) return;

    riskFactorsChart = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Hypertension', 'Diabetes', 'High Cholesterol', 'Smoking', 'Low Activity', 'Poor Sleep'],
            datasets: [{
                label: 'Number of Patients',
                data: [15, 8, 12, 6, 18, 10],
                backgroundColor: [
                    '#ef4444',
                    '#f59e0b',
                    '#f97316',
                    '#6b7280',
                    '#3b82f6',
                    '#8b5cf6'
                ],
                borderRadius: 8,
                borderSkipped: false
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
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: '#9ca3af', 
                        font: { size: 10, family: 'Inter' },
                        maxRotation: 45
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f3f4f6' },
                    ticks: { color: '#9ca3af', font: { size: 11, family: 'Inter' } }
                }
            },
            animation: {
                duration: 1500,
                easing: 'easeInOutQuart'
            }
        }
    });
}

// Setup event listeners
function setupEventListeners() {
    document.getElementById('timeRangeFilter').addEventListener('change', function() {
        updateChartsForTimeRange(this.value);
    });
}

// Update charts for time range
function updateChartsForTimeRange(days) {
    showToast(`📊 Updating charts for last ${days} days...`, 'info');
    
    // Simulate data update
    setTimeout(() => {
        // Update chart data based on time range
        if (healthDistributionChart) {
            // Simulate different data for different time ranges
            const multiplier = days / 30;
            healthDistributionChart.data.datasets[0].data = [
                Math.round(45 * multiplier),
                Math.round(12 * multiplier),
                Math.round(3 * multiplier),
                Math.round(8 * multiplier)
            ];
            healthDistributionChart.update();
        }
        
        showToast('✅ Charts updated successfully!', 'success');
    }, 1000);
}

// Toggle chart type
function toggleChartType(type) {
    if (!healthDistributionChart) return;

    // Update button states
    document.querySelectorAll('.chart-type-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.getElementById(type + 'Btn').classList.add('active');

    // Destroy and recreate chart with new type
    healthDistributionChart.destroy();
    
    const ctx = document.getElementById('healthDistributionChart');
    healthDistributionChart = new Chart(ctx.getContext('2d'), {
        type: type,
        data: {
            labels: ['Healthy', 'At Risk', 'Critical', 'Inactive'],
            datasets: [{
                data: [
                    analyticsData.healthy_patients || 45,
                    analyticsData.at_risk_patients || 12,
                    analyticsData.critical_patients || 3,
                    analyticsData.inactive_patients || 8
                ],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#ef4444',
                    '#6b7280'
                ],
                borderWidth: 0,
                borderRadius: type === 'bar' ? 8 : 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12, family: 'Inter', weight: '600' }
                    }
                }
            },
            cutout: type === 'doughnut' ? '60%' : 0,
            scales: type === 'bar' ? {
                x: { grid: { display: false } },
                y: { beginAtZero: true, grid: { color: '#f3f4f6' } }
            } : {}
        }
    });
}

// Update trends chart
function updateTrendsChart(vitalType) {
    if (!trendsChart) return;

    // Update button states
    document.querySelectorAll('.trend-filter-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    const datasets = trendsChart.data.datasets[0];
    
    switch(vitalType) {
        case 'blood_pressure':
            datasets.label = 'Average Blood Pressure (Systolic)';
            datasets.data = [125, 123, 121, 120];
            datasets.borderColor = '#ef4444';
            datasets.backgroundColor = 'rgba(239, 68, 68, 0.1)';
            updateTrendsInsights('122 mmHg', '↘️ Decreasing', '8 patients');
            break;
            
        case 'heart_rate':
            datasets.label = 'Average Heart Rate';
            datasets.data = [75, 73, 72, 71];
            datasets.borderColor = '#3b82f6';
            datasets.backgroundColor = 'rgba(59, 130, 246, 0.1)';
            updateTrendsInsights('73 bpm', '↘️ Decreasing', '12 patients');
            break;
            
        case 'bmi':
            datasets.label = 'Average BMI';
            datasets.data = [26.2, 26.0, 25.8, 25.6];
            datasets.borderColor = '#8b5cf6';
            datasets.backgroundColor = 'rgba(139, 92, 246, 0.1)';
            updateTrendsInsights('25.9', '↘️ Decreasing', '15 patients');
            break;
            
        case 'adherence':
            datasets.label = 'Treatment Adherence';
            datasets.data = [82, 85, 87, 89];
            datasets.borderColor = '#10b981';
            datasets.backgroundColor = 'rgba(16, 185, 129, 0.1)';
            updateTrendsInsights('86%', '↗️ Increasing', '18 patients');
            break;
    }
    
    trendsChart.update('active');
}

// Update trends insights
function updateTrendsInsights(average, trend, improving) {
    document.getElementById('avgValue').textContent = average;
    document.getElementById('trendDir').textContent = trend;
    document.getElementById('improvingCount').textContent = improving;
}

// Animate metrics on page load
function animateMetrics() {
    setTimeout(() => {
        document.querySelectorAll('.metric-item .bg-gradient-to-r, .progress-item .bg-gradient-to-r').forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 500);
        });
    }, 100);
}

// Action functions
function exportAnalytics() {
    showToast('📊 Generating analytics report...', 'info');
    
    setTimeout(() => {
        // Create comprehensive CSV data
        const csvData = [
            ['Metric', 'Value', 'Previous Period', 'Change'],
            ['Total Patients', analyticsData.total_patients || 68, 65, '+3'],
            ['Healthy Patients', analyticsData.healthy_patients || 45, 42, '+3'],
            ['At Risk Patients', analyticsData.at_risk_patients || 12, 14, '-2'],
            ['Critical Patients', analyticsData.critical_patients || 3, 5, '-2'],
            ['Satisfaction Score', (analyticsData.satisfaction_score || 95) + '%', '92%', '+3%'],
            ['Adherence Rate', (analyticsData.adherence_rate || 87) + '%', '84%', '+3%'],
            ['Readmission Rate', (analyticsData.readmission_rate || 3) + '%', '5%', '-2%']
        ];
        
        const csvString = csvData.map(row => 
            row.map(cell => `"${cell}"`).join(',')
        ).join('\n');
        
        const blob = new Blob([csvString], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `analytics-report-${new Date().toISOString().split('T')[0]}.csv`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(url);
        
        showToast('📊 Analytics report exported successfully!', 'success');
    }, 2000);
}

function generateInsights() {
    showToast('🧠 Generating AI insights...', 'info');
    
    setTimeout(() => {
        const insights = [
            "Patient adherence has improved by 3% this month",
            "Blood pressure control is trending positively across 85% of patients",
            "Consider increasing follow-up frequency for at-risk patients",
            "Medication adherence correlates strongly with improved outcomes"
        ];
        
        let insightText = "Key Insights Generated:\n\n";
        insights.forEach((insight, index) => {
            insightText += `${index + 1}. ${insight}\n`;
        });
        
        alert(insightText);
        showToast('🧠 AI insights generated successfully!', 'success');
    }, 3000);
}

function generateWeeklyReport() {
    showToast('📋 Generating weekly report...', 'info');
}

function compareMetrics() {
    showToast('📊 Opening metrics comparison...', 'info');
}

function predictiveAnalysis() {
    showToast('🔮 Running predictive analysis...', 'info');
}

function benchmarkAnalysis() {
    showToast('📈 Comparing against benchmarks...', 'info');
}
</script>
@endpush