@extends('doctor.layout')

@section('title', 'Reports & Analytics')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                        📊 Reports & Analytics
                    </h1>
                    <p class="text-gray-600 text-lg">Generate comprehensive reports and analyze patient data</p>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">
                    <button onclick="scheduleReport()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Schedule Report
                    </button>
                    <button onclick="generateCustomReport()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Generate Custom Report
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Report Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="report-card medical-card p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer" onclick="generatePatientSummary()">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m-9 5.197v1a6 6 0 0010.967 0M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-semibold">Quick Generate</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Patient Summary</h3>
                <p class="text-gray-600 text-sm mb-4">Comprehensive overview of all patients under your care</p>
                <div class="text-2xl font-bold text-blue-600">{{ $totalPatients ?? 0 }} Patients</div>
            </div>

            <div class="report-card medical-card p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer" onclick="generateVitalsReport()">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-semibold">Trending</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Vitals Analysis</h3>
                <p class="text-gray-600 text-sm mb-4">Detailed analysis of patient vital signs and trends</p>
                <div class="text-2xl font-bold text-green-600">{{ $vitalRecords ?? 0 }} Records</div>
            </div>

            <div class="report-card medical-card p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer" onclick="generateAppointmentReport()">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <span class="text-xs bg-purple-100 text-purple-800 px-2 py-1 rounded-full font-semibold">Monthly</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Appointments</h3>
                <p class="text-gray-600 text-sm mb-4">Appointment statistics and scheduling patterns</p>
                <div class="text-2xl font-bold text-purple-600">{{ $monthlyAppointments ?? 0 }} This Month</div>
            </div>

            <div class="report-card medical-card p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer" onclick="generatePerformanceReport()">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-2xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <span class="text-xs bg-orange-100 text-orange-800 px-2 py-1 rounded-full font-semibold">Performance</span>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Performance</h3>
                <p class="text-gray-600 text-sm mb-4">Your practice performance metrics and KPIs</p>
                <div class="text-2xl font-bold text-orange-600">{{ $satisfactionScore ?? 95 }}% Rating</div>
            </div>
        </div>

        <!-- Report Generation Tools -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Custom Report Builder -->
            <div class="xl:col-span-2">
                <div class="medical-card">
                    <div class="p-8 border-b border-gray-200">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">🛠️ Custom Report Builder</h3>
                        <p class="text-gray-600">Create tailored reports with specific parameters and data points</p>
                    </div>

                    <div class="p-8">
                        <form id="customReportForm" class="space-y-6">
                            @csrf
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="reportType" class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
                                    <select id="reportType" name="report_type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Select Report Type</option>
                                        <option value="patient_overview">Patient Overview</option>
                                        <option value="vital_signs_analysis">Vital Signs Analysis</option>
                                        <option value="appointment_summary">Appointment Summary</option>
                                        <option value="medication_adherence">Medication Adherence</option>
                                        <option value="health_trends">Health Trends</option>
                                        <option value="emergency_response">Emergency Response</option>
                                        <option value="custom">Custom Analysis</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="dateRange" class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                                    <select id="dateRange" name="date_range" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="last_7_days">Last 7 days</option>
                                        <option value="last_30_days" selected>Last 30 days</option>
                                        <option value="last_90_days">Last 90 days</option>
                                        <option value="last_6_months">Last 6 months</option>
                                        <option value="last_year">Last year</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                            </div>

                            <div id="customDateRange" class="grid grid-cols-1 md:grid-cols-2 gap-6 hidden">
                                <div>
                                    <label for="startDate" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                    <input type="date" id="startDate" name="start_date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                
                                <div>
                                    <label for="endDate" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                    <input type="date" id="endDate" name="end_date" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>

                            <div>
                                <label for="patientFilter" class="block text-sm font-medium text-gray-700 mb-2">Patient Filter</label>
                                <select id="patientFilter" name="patient_filter" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="all">All Patients</option>
                                    <option value="active">Active Patients Only</option>
                                    <option value="critical">Critical Patients</option>
                                    <option value="specific">Specific Patients</option>
                                </select>
                            </div>

                            <div id="specificPatients" class="hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Specific Patients</label>
                                <div class="max-h-40 overflow-y-auto border border-gray-300 rounded-xl p-4 space-y-2">
                                    @foreach($patients ?? [] as $patient)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="selected_patients[]" value="{{ $patient->id }}" class="mr-3">
                                        <span class="text-gray-700">{{ $patient->name }} ({{ $patient->patient->medical_record_number ?? 'No MRN' }})</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Include Data Points</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="data_points[]" value="vital_signs" class="mr-2" checked>
                                        <span class="text-gray-700">Vital Signs</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="data_points[]" value="medications" class="mr-2" checked>
                                        <span class="text-gray-700">Medications</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="data_points[]" value="appointments" class="mr-2" checked>
                                        <span class="text-gray-700">Appointments</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="data_points[]" value="documents" class="mr-2">
                                        <span class="text-gray-700">Documents</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="data_points[]" value="alerts" class="mr-2">
                                        <span class="text-gray-700">Alerts</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="data_points[]" value="trends" class="mr-2">
                                        <span class="text-gray-700">Health Trends</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label for="reportFormat" class="block text-sm font-medium text-gray-700 mb-2">Output Format</label>
                                <div class="flex space-x-4">
                                    <label class="flex items-center">
                                        <input type="radio" name="report_format" value="pdf" class="mr-2" checked>
                                        <span class="text-gray-700">PDF Report</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="report_format" value="excel" class="mr-2">
                                        <span class="text-gray-700">Excel Spreadsheet</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio" name="report_format" value="csv" class="mr-2">
                                        <span class="text-gray-700">CSV Data</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-4">
                                <button type="button" onclick="previewReport()" class="btn-secondary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Preview
                                </button>
                                <button type="submit" class="btn-primary">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Generate Report
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Recent Reports & Sidebar -->
            <div class="space-y-6">
                <!-- Recent Reports -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">📄 Recent Reports</h3>
                        <p class="text-sm text-gray-600">Previously generated reports</p>
                    </div>
                    
                    <div class="space-y-4">
                        @forelse($recentReports ?? [] as $report)
                        <div class="report-item p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer" onclick="downloadReport({{ $report->id }})">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 text-sm">{{ $report->title ?? 'Report' }}</h4>
                                    <p class="text-xs text-gray-600 mt-1">{{ $report->type ?? 'Custom Report' }}</p>
                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                        <span>{{ $report->created_at->format('M d, Y') ?? 'Recent' }}</span>
                                        <span>{{ $report->format ?? 'PDF' }}</span>
                                        <span>{{ $report->size ?? '2.3' }}MB</span>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <button onclick="downloadReport({{ $report->id }}); event.stopPropagation();" class="text-blue-600 hover:text-blue-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                    </button>
                                    <button onclick="shareReport({{ $report->id }}); event.stopPropagation();" class="text-green-600 hover:text-green-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-8">
                            <div class="text-4xl mb-2">📄</div>
                            <p class="text-gray-500 text-sm">No reports generated yet</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">⚡ Quick Actions</h3>
                        <p class="text-sm text-gray-600">Common report types</p>
                    </div>
                    
                    <div class="space-y-3">
                        <button onclick="generateWeeklyReport()" class="action-btn bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-200 text-blue-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Weekly Summary
                        </button>
                        
                        <button onclick="generateMonthlyReport()" class="action-btn bg-gradient-to-r from-green-50 to-emerald-50 border-green-200 text-green-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                            </svg>
                            Monthly Report
                        </button>
                        
                        <button onclick="generateCriticalPatientsReport()" class="action-btn bg-gradient-to-r from-red-50 to-pink-50 border-red-200 text-red-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            Critical Patients
                        </button>
                        
                        <button onclick="generateComplianceReport()" class="action-btn bg-gradient-to-r from-purple-50 to-purple-100 border-purple-200 text-purple-700">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            Compliance Report
                        </button>
                    </div>
                </div>

                <!-- Report Statistics -->
                <div class="medical-card p-6">
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 mb-1">📈 Report Statistics</h3>
                        <p class="text-sm text-gray-600">Usage and insights</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Reports Generated</span>
                            <span class="font-bold text-gray-900">{{ $totalReports ?? 12 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">This Month</span>
                            <span class="font-bold text-blue-600">{{ $monthlyReports ?? 4 }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Most Used Format</span>
                            <span class="font-bold text-green-600">PDF ({{ $pdfPercentage ?? 75 }}%)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Avg Generation Time</span>
                            <span class="font-bold text-purple-600">{{ $avgGenerationTime ?? 2.3 }}s</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.report-card {
    @apply transition-all duration-300;
}

.report-card:hover {
    @apply transform scale-105;
}

.action-btn {
    @apply w-full flex items-center p-4 rounded-2xl border font-medium transition-all duration-200 hover:shadow-md cursor-pointer;
}

.report-item {
    @apply transition-all duration-200;
}
</style>
@endsection

@push('scripts')
<script>
// Reports initialization
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    initializeFormLogic();
});

// Setup event listeners
function setupEventListeners() {
    // Custom report form submission
    document.getElementById('customReportForm').addEventListener('submit', handleCustomReportGeneration);
    
    // Date range change handler
    document.getElementById('dateRange').addEventListener('change', handleDateRangeChange);
    
    // Patient filter change handler
    document.getElementById('patientFilter').addEventListener('change', handlePatientFilterChange);
}

// Initialize form logic
function initializeFormLogic() {
    // Set default dates
    const today = new Date();
    const thirtyDaysAgo = new Date(today.getTime() - (30 * 24 * 60 * 60 * 1000));
    
    document.getElementById('endDate').value = today.toISOString().split('T')[0];
    document.getElementById('startDate').value = thirtyDaysAgo.toISOString().split('T')[0];
}

// Handle date range change
function handleDateRangeChange() {
    const dateRange = document.getElementById('dateRange').value;
    const customDateRange = document.getElementById('customDateRange');
    
    if (dateRange === 'custom') {
        customDateRange.classList.remove('hidden');
    } else {
        customDateRange.classList.add('hidden');
    }
}

// Handle patient filter change
function handlePatientFilterChange() {
    const patientFilter = document.getElementById('patientFilter').value;
    const specificPatients = document.getElementById('specificPatients');
    
    if (patientFilter === 'specific') {
        specificPatients.classList.remove('hidden');
    } else {
        specificPatients.classList.add('hidden');
    }
}

// Custom report generation
async function handleCustomReportGeneration(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const submitBtn = event.target.querySelector('button[type="submit"]');
    
    // Validate form
    if (!validateReportForm(formData)) {
        return;
    }
    
    // Show loading state
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = `
        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Generating Report...
    `;
    submitBtn.disabled = true;
    
    try {
        const response = await fetch('/doctor/reports/generate', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Report generated successfully!', 'success');
            
            // Download the report
            if (data.download_url) {
                window.open(data.download_url, '_blank');
            }
            
            // Refresh the recent reports list
            setTimeout(() => window.location.reload(), 1000);
        } else {
            window.showToast('❌ Failed to generate report: ' + (data.message || 'Unknown error'), 'error');
        }
    } catch (error) {
        console.error('Report generation error:', error);
        window.showToast('❌ Error generating report: ' + error.message, 'error');
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// Validate report form
function validateReportForm(formData) {
    const reportType = formData.get('report_type');
    const dateRange = formData.get('date_range');
    
    if (!reportType) {
        window.showToast('❌ Please select a report type', 'error');
        return false;
    }
    
    if (dateRange === 'custom') {
        const startDate = formData.get('start_date');
        const endDate = formData.get('end_date');
        
        if (!startDate || !endDate) {
            window.showToast('❌ Please select custom date range', 'error');
            return false;
        }
        
        if (new Date(startDate) > new Date(endDate)) {
            window.showToast('❌ Start date must be before end date', 'error');
            return false;
        }
    }
    
    return true;
}

// Preview report functionality
function previewReport() {
    const formData = new FormData(document.getElementById('customReportForm'));
    
    if (!validateReportForm(formData)) {
        return;
    }
    
    window.showToast('👀 Report preview feature coming soon!', 'info');
}

// Quick report generation functions
async function generateQuickReport(reportType, additionalParams = {}) {
    window.showToast(`📊 Generating ${reportType} report...`, 'info');
    
    try {
        const params = {
            report_type: reportType,
            date_range: 'last_30_days',
            report_format: 'pdf',
            ...additionalParams
        };
        
        const response = await fetch('/doctor/reports/generate-quick', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: JSON.stringify(params)
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.showToast('✅ Report generated successfully!', 'success');
            if (data.download_url) {
                window.open(data.download_url, '_blank');
            }
        } else {
            window.showToast('❌ Failed to generate report', 'error');
        }
    } catch (error) {
        console.error('Quick report error:', error);
        window.showToast('❌ Error generating report', 'error');
    }
}

// Quick report functions
function generatePatientSummary() {
    generateQuickReport('patient_overview');
}

function generateVitalsReport() {
    generateQuickReport('vital_signs_analysis');
}

function generateAppointmentReport() {
    generateQuickReport('appointment_summary');
}

function generatePerformanceReport() {
    generateQuickReport('performance_metrics');
}

function generateWeeklyReport() {
    generateQuickReport('weekly_summary', { date_range: 'last_7_days' });
}

function generateMonthlyReport() {
    generateQuickReport('monthly_summary', { date_range: 'last_30_days' });
}

function generateCriticalPatientsReport() {
    generateQuickReport('critical_patients', { patient_filter: 'critical' });
}

function generateComplianceReport() {
    generateQuickReport('compliance_report');
}

function generateCustomReport() {
    document.getElementById('customReportForm').scrollIntoView({ behavior: 'smooth' });
    window.showToast('📝 Use the custom report builder below to create your report', 'info');
}

// Report management functions
function downloadReport(reportId) {
    window.location.href = `/doctor/reports/${reportId}/download`;
    window.showToast('📄 Starting download...', 'info');
}

function shareReport(reportId) {
    if (navigator.share) {
        navigator.share({
            title: 'Medical Report',
            text: 'Check out this medical report',
            url: `/doctor/reports/${reportId}/share`
        });
    } else {
        // Fallback: copy link to clipboard
        const shareUrl = `${window.location.origin}/doctor/reports/${reportId}/share`;
        navigator.clipboard.writeText(shareUrl).then(() => {
            window.showToast('🔗 Share link copied to clipboard!', 'success');
        });
    }
}

function scheduleReport() {
    window.showToast('⏰ Scheduled reports feature coming soon!', 'info');
}

// Real-time report generation progress (if implemented)
if (window.pusher) {
    const doctorChannel = pusher.subscribe('doctor.{{ auth()->id() }}');
    
    doctorChannel.bind('report-generated', function(data) {
        window.showToast('✅ Your report has been generated and is ready for download!', 'success');
        
        if (data.download_url) {
            setTimeout(() => {
                window.open(data.download_url, '_blank');
            }, 1000);
        }
    });
    
    doctorChannel.bind('report-generation-failed', function(data) {
        window.showToast('❌ Report generation failed: ' + (data.error || 'Unknown error'), 'error');
    });
}
</script>
@endpush