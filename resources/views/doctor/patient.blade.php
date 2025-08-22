@extends('doctor.layout')

@section('title', 'Patients')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                        👥 Patient Management
                    </h1>
                    <p class="text-gray-600 text-lg">Monitor and manage all your patients from one central location</p>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">
                    <button onclick="exportPatients()" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Export List
                    </button>
                    <button onclick="addNewPatient()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Add New Patient
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Statistics -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m-9 5.197v1a6 6 0 0010.967 0M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $totalPatients ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Total Patients</div>
                <div class="text-xs text-gray-400 mt-1">Under your care</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $activePatients ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Active Today</div>
                <div class="text-xs text-gray-400 mt-1">Reported vitals</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $criticalPatients ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">Need Attention</div>
                <div class="text-xs text-gray-400 mt-1">Critical status</div>
            </div>

            <div class="medical-card p-6 text-center hover:shadow-2xl transition-all duration-300">
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900 mb-2">{{ $newPatients ?? 0 }}</div>
                <div class="text-sm text-gray-600 font-medium">New This Week</div>
                <div class="text-xs text-gray-400 mt-1">Recent additions</div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="medical-card p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="flex-1">
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Search patients by name, email, or medical record number..."
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                        <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <select id="statusFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                        <option value="">All Status</option>
                        <option value="normal">Normal</option>
                        <option value="warning">Warning</option>
                        <option value="critical">Critical</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select id="activityFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                        <option value="">All Activity</option>
                        <option value="today">Active Today</option>
                        <option value="week">Active This Week</option>
                        <option value="month">Active This Month</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <select id="sortFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                        <option value="name">Sort by Name</option>
                        <option value="status">Sort by Status</option>
                        <option value="activity">Sort by Activity</option>
                        <option value="added">Sort by Date Added</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Patients List -->
        <div class="medical-card">
            <div class="p-8 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Patient List</h3>
                        <p class="text-gray-600">Comprehensive overview of all your patients</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-sm text-gray-500">
                            <span id="patientCount">{{ $patients->count() ?? 0 }}</span> patients
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="toggleViewMode('grid')" id="gridViewBtn" class="p-2 rounded-lg bg-blue-100 text-blue-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                                </svg>
                            </button>
                            <button onclick="toggleViewMode('list')" id="listViewBtn" class="p-2 rounded-lg bg-gray-100 text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <!-- Grid View -->
                <div id="gridView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($patients ?? [] as $patient)
                    <div class="patient-card" data-patient-id="{{ $patient->id }}" data-status="{{ $patient->status ?? 'normal' }}" data-name="{{ strtolower($patient->name) }}" data-email="{{ strtolower($patient->email) }}" data-medical-record="{{ $patient->patient->medical_record_number ?? '' }}">
                        <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300 cursor-pointer" onclick="viewPatientDetails({{ $patient->id }})">
                            <!-- Patient Header -->
                            <div class="flex items-start justify-between mb-6">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-3xl flex items-center justify-center">
                                        <span class="text-blue-600 font-bold text-xl">{{ strtoupper(substr($patient->name, 0, 1)) }}</span>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-gray-900">{{ $patient->name }}</h4>
                                        <p class="text-sm text-gray-600">{{ $patient->patient->medical_record_number ?? 'No MRN' }}</p>
                                        <p class="text-xs text-gray-500">{{ $patient->email }}</p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col items-end space-y-2">
                                    <span class="status-badge {{ $patient->status ?? 'normal' }}">
                                        <div class="status-indicator status-{{ $patient->status ?? 'normal' }}"></div>
                                        {{ ucfirst($patient->status ?? 'normal') }}
                                    </span>
                                    @if($patient->last_activity)
                                    <span class="text-xs text-gray-500">{{ $patient->last_activity->diffForHumans() }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Latest Vitals -->
                            @if($patient->patient->latestVitals)
                            <div class="bg-gray-50 rounded-2xl p-4 mb-4">
                                <h5 class="font-semibold text-gray-800 mb-3 text-sm">Latest Vitals</h5>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <span class="text-gray-600">BP:</span>
                                        <span class="font-semibold text-gray-900">{{ $patient->patient->latestVitals->blood_pressure ?? '--' }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">HR:</span>
                                        <span class="font-semibold text-gray-900">{{ $patient->patient->latestVitals->heart_rate ?? '--' }} bpm</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">Temp:</span>
                                        <span class="font-semibold text-gray-900">{{ $patient->patient->latestVitals->temperature ?? '--' }}°F</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-600">O2:</span>
                                        <span class="font-semibold text-gray-900">{{ $patient->patient->latestVitals->oxygen_saturation ?? '--' }}%</span>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <!-- Patient Info -->
                            <div class="space-y-2 mb-6">
                                @if($patient->patient->blood_type)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Blood Type:</span>
                                    <span class="font-semibold text-red-600">{{ $patient->patient->blood_type }}</span>
                                </div>
                                @endif
                                @if($patient->patient->chronic_conditions)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Conditions:</span>
                                    <span class="font-semibold text-gray-900">{{ Str::limit($patient->patient->chronic_conditions, 20) }}</span>
                                </div>
                                @endif
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Joined:</span>
                                    <span class="font-semibold text-gray-900">{{ $patient->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <a href="{{ route('doctor.patients.monitor', $patient->id) }}" 
                                   class="flex-1 btn-primary text-center py-2 text-sm"
                                   onclick="event.stopPropagation()">
                                    Monitor
                                </a>
                                <button onclick="messagePatient({{ $patient->id }}); event.stopPropagation();" 
                                        class="btn-secondary px-4 py-2 text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </button>
                                <div class="relative">
                                    <button onclick="togglePatientMenu({{ $patient->id }}); event.stopPropagation();" 
                                            class="btn-secondary px-4 py-2 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"></path>
                                        </svg>
                                    </button>
                                    <div id="patient-menu-{{ $patient->id }}" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg z-20 border border-gray-200">
                                        <button onclick="editPatient({{ $patient->id }})" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-t-xl">
                                            Edit Details
                                        </button>
                                        <button onclick="viewHistory({{ $patient->id }})" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                            View History
                                        </button>
                                        <button onclick="generateReport({{ $patient->id }})" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50">
                                            Generate Report
                                        </button>
                                        <button onclick="shareAccess({{ $patient->id }})" class="block w-full text-left px-4 py-3 text-sm text-blue-600 hover:bg-blue-50 border-t border-gray-100">
                                            Share Access
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-span-3 text-center py-12">
                        <div class="text-6xl mb-4">👥</div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No Patients Found</h3>
                        <p class="text-gray-600 mb-6">Start by adding your first patient to monitor their health</p>
                        <button onclick="addNewPatient()" class="btn-primary">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Add First Patient
                        </button>
                    </div>
                    @endforelse
                </div>

                <!-- List View -->
                <div id="listView" class="hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Latest Vitals</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Activity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($patients ?? [] as $patient)
                                <tr class="hover:bg-gray-50 patient-row" data-patient-id="{{ $patient->id }}" data-status="{{ $patient->status ?? 'normal' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mr-4">
                                                <span class="text-blue-600 font-bold">{{ strtoupper(substr($patient->name, 0, 1)) }}</span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $patient->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $patient->patient->medical_record_number ?? 'No MRN' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $patient->email }}</div>
                                        <div class="text-sm text-gray-500">{{ $patient->phone ?? 'No phone' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($patient->patient->latestVitals)
                                        <div class="text-sm text-gray-900">
                                            BP: {{ $patient->patient->latestVitals->blood_pressure ?? '--' }}<br>
                                            HR: {{ $patient->patient->latestVitals->heart_rate ?? '--' }} bpm
                                        </div>
                                        @else
                                        <div class="text-sm text-gray-500">No vitals</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge {{ $patient->status ?? 'normal' }}">
                                            <div class="status-indicator status-{{ $patient->status ?? 'normal' }}"></div>
                                            {{ ucfirst($patient->status ?? 'normal') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $patient->last_activity ? $patient->last_activity->diffForHumans() : 'No activity' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('doctor.patients.monitor', $patient->id) }}" class="text-blue-600 hover:text-blue-900">Monitor</a>
                                            <button onclick="messagePatient({{ $patient->id }})" class="text-green-600 hover:text-green-900">Message</button>
                                            <button onclick="shareAccess({{ $patient->id }})" class="text-purple-600 hover:text-purple-900">Share</button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            @if(isset($patients) && method_exists($patients, 'links'))
            <div class="px-8 py-6 border-t border-gray-200">
                {{ $patients->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.status-badge {
    @apply px-3 py-1 rounded-full text-xs font-semibold flex items-center space-x-1;
}

.status-badge.normal {
    @apply bg-green-100 text-green-800;
}

.status-badge.warning {
    @apply bg-yellow-100 text-yellow-800;
}

.status-badge.critical {
    @apply bg-red-100 text-red-800;
}

.status-badge.inactive {
    @apply bg-gray-100 text-gray-800;
}

.patient-card {
    @apply transition-all duration-300;
}

.patient-card:hover {
    @apply transform scale-105;
}

.action-btn {
    @apply w-full flex items-center p-4 rounded-2xl border font-medium transition-all duration-200 hover:shadow-md cursor-pointer text-decoration-none;
}
</style>
@endsection

@push('scripts')
<script>
// Initialize patients management
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    initializeFilters();
});

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    document.getElementById('searchInput').addEventListener('input', filterPatients);
    
    // Filter functionality
    document.getElementById('statusFilter').addEventListener('change', filterPatients);
    document.getElementById('activityFilter').addEventListener('change', filterPatients);
    document.getElementById('sortFilter').addEventListener('change', sortPatients);
    
    // Close patient menus when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[onclick^="togglePatientMenu"]')) {
            document.querySelectorAll('[id^="patient-menu-"]').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    });
}

// Initialize filters
function initializeFilters() {
    const urlParams = new URLSearchParams(window.location.search);
    
    if (urlParams.get('search')) {
        document.getElementById('searchInput').value = urlParams.get('search');
    }
    if (urlParams.get('status')) {
        document.getElementById('statusFilter').value = urlParams.get('status');
    }
    if (urlParams.get('activity')) {
        document.getElementById('activityFilter').value = urlParams.get('activity');
    }
    
    filterPatients();
}

// Filter patients based on search and filters
function filterPatients() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const activityFilter = document.getElementById('activityFilter').value;
    
    const patientCards = document.querySelectorAll('.patient-card');
    const patientRows = document.querySelectorAll('.patient-row');
    
    let visibleCount = 0;
    
    // Filter grid view
    patientCards.forEach(card => {
        const name = card.dataset.name || '';
        const email = card.dataset.email || '';
        const medicalRecord = card.dataset.medicalRecord || '';
        const status = card.dataset.status || '';
        
        const matchesSearch = !searchTerm || 
            name.includes(searchTerm) || 
            email.includes(searchTerm) || 
            medicalRecord.includes(searchTerm);
            
        const matchesStatus = !statusFilter || status === statusFilter;
        
        // Activity filter logic would need server-side data
        const matchesActivity = !activityFilter; // Simplified for now
        
        if (matchesSearch && matchesStatus && matchesActivity) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Filter list view
    patientRows.forEach(row => {
        const patientId = row.dataset.patientId;
        const correspondingCard = document.querySelector(`.patient-card[data-patient-id="${patientId}"]`);
        
        if (correspondingCard && correspondingCard.style.display !== 'none') {
            row.style.display = 'table-row';
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update patient count
    document.getElementById('patientCount').textContent = visibleCount;
}

// Sort patients
function sortPatients() {
    const sortBy = document.getElementById('sortFilter').value;
    const container = document.getElementById('gridView');
    const cards = Array.from(container.querySelectorAll('.patient-card'));
    
    cards.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'status':
                const statusOrder = { 'critical': 0, 'warning': 1, 'normal': 2, 'inactive': 3 };
                return statusOrder[a.dataset.status] - statusOrder[b.dataset.status];
            case 'activity':
                // Would need actual activity timestamps
                return 0;
            case 'added':
                // Would need creation timestamps
                return 0;
            default:
                return 0;
        }
    });
    
    // Reorder cards in container
    cards.forEach(card => container.appendChild(card));
}

// Toggle view mode
function toggleViewMode(mode) {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    
    if (mode === 'grid') {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        gridBtn.className = 'p-2 rounded-lg bg-blue-100 text-blue-600';
        listBtn.className = 'p-2 rounded-lg bg-gray-100 text-gray-600';
    } else {
        gridView.classList.add('hidden');
        listView.classList.remove('hidden');
        gridBtn.className = 'p-2 rounded-lg bg-gray-100 text-gray-600';
        listBtn.className = 'p-2 rounded-lg bg-blue-100 text-blue-600';
    }
}

// Patient actions
function viewPatientDetails(patientId) {
    window.location.href = `/doctor/patients/${patientId}/monitor`;
}

function togglePatientMenu(patientId) {
    const menu = document.getElementById(`patient-menu-${patientId}`);
    
    // Close all other menus
    document.querySelectorAll('[id^="patient-menu-"]').forEach(m => {
        if (m.id !== `patient-menu-${patientId}`) {
            m.classList.add('hidden');
        }
    });
    
    menu.classList.toggle('hidden');
}

function messagePatient(patientId) {
    showToast('💬 Messaging feature coming soon!', 'info');
}

function editPatient(patientId) {
    showToast('✏️ Edit patient feature coming soon!', 'info');
}

function viewHistory(patientId) {
    window.location.href = `/doctor/patients/${patientId}/history`;
}

function generateReport(patientId) {
    showToast('📊 Generating patient report...', 'info');
}

function shareAccess(patientId) {
    if (confirm('Generate temporary access link for this patient?')) {
        generateTempAccess(patientId);
    }
}

async function generateTempAccess(patientId) {
    try {
        const response = await fetch('/doctor/patients/generate-temp-access', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                patient_id: patientId,
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

function addNewPatient() {
    showToast('👥 Add patient feature coming soon!', 'info');
}

function exportPatients() {
    showToast('📊 Exporting patient list...', 'info');
    
    // Create CSV data
    const csvData = [
        ['Name', 'Email', 'Medical Record', 'Status', 'Blood Type', 'Last Activity']
    ];
    
    // Add patient data (this would normally come from the server)
    document.querySelectorAll('.patient-card:not([style*="display: none"])').forEach(card => {
        const name = card.querySelector('.text-lg.font-bold').textContent;
        const email = card.querySelector('.text-xs.text-gray-500').textContent;
        const mrn = card.querySelector('.text-sm.text-gray-600').textContent;
        const status = card.dataset.status;
        
        csvData.push([name, email, mrn, status, '', '']);
    });
    
    // Convert to CSV string
    const csvString = csvData.map(row => 
        row.map(cell => `"${cell}"`).join(',')
    ).join('\n');
    
    // Download file
    const blob = new Blob([csvString], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `patients-${new Date().toISOString().split('T')[0]}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    
    showToast('📊 Patient list exported successfully!', 'success');
}

// Real-time updates
if (window.pusher) {
    const doctorChannel = pusher.subscribe('doctor.{{ auth()->id() }}');
    
    doctorChannel.bind('patient-status-update', function(data) {
        updatePatientStatus(data.patient_id, data.status);
    });
    
    doctorChannel.bind('patient-activity', function(data) {
        updatePatientActivity(data.patient_id, data.activity);
    });
}

function updatePatientStatus(patientId, newStatus) {
    const card = document.querySelector(`.patient-card[data-patient-id="${patientId}"]`);
    const row = document.querySelector(`.patient-row[data-patient-id="${patientId}"]`);
    
    if (card) {
        card.dataset.status = newStatus;
        const statusBadge = card.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.className = `status-badge ${newStatus}`;
            statusBadge.innerHTML = `
                <div class="status-indicator status-${newStatus}"></div>
                ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}
            `;
        }
    }
    
    if (row) {
        row.dataset.status = newStatus;
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.className = `status-badge ${newStatus}`;
            statusBadge.innerHTML = `
                <div class="status-indicator status-${newStatus}"></div>
                ${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}
            `;
        }
    }
}

function updatePatientActivity(patientId, activity) {
    // Update last activity timestamp
    const elements = document.querySelectorAll(`[data-patient-id="${patientId}"] .text-xs.text-gray-500`);
    elements.forEach(el => {
        if (el.textContent.includes('ago') || el.textContent.includes('activity')) {
            el.textContent = 'Just now';
        }
    });
}
</script>
@endpush