@extends('doctor.layout')

@section('title', 'Calendar')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="medical-card p-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-2">
                        📅 Appointment Calendar
                    </h1>
                    <p class="text-gray-600 text-lg">Visual overview of your appointment schedule</p>
                </div>
                <div class="mt-6 lg:mt-0 flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('doctor.appointments.index') }}" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                        List View
                    </a>
                    <button onclick="scheduleAppointment()" class="btn-primary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Schedule Appointment
                    </button>
                </div>
            </div>
        </div>

        <!-- Calendar Navigation -->
        <div class="medical-card p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex items-center space-x-4">
                    <button onclick="previousMonth()" class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </button>
                    <h2 class="text-2xl font-bold text-gray-900" id="currentMonth">{{ now()->format('F Y') }}</h2>
                    <button onclick="nextMonth()" class="p-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                    <button onclick="goToToday()" class="btn-secondary text-sm">Today</button>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 text-sm">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span>Confirmed</span>
                        <div class="w-3 h-3 bg-yellow-500 rounded-full ml-4"></div>
                        <span>Scheduled</span>
                        <div class="w-3 h-3 bg-blue-500 rounded-full ml-4"></div>
                        <span>Completed</span>
                        <div class="w-3 h-3 bg-red-500 rounded-full ml-4"></div>
                        <span>Cancelled</span>
                    </div>
                    
                    <select id="viewMode" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="month">Month View</option>
                        <option value="week">Week View</option>
                        <option value="day">Day View</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="medical-card">
            <!-- Month View -->
            <div id="monthView" class="p-8">
                <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden">
                    <!-- Day Headers -->
                    <div class="bg-gray-50 p-4 text-center font-semibold text-gray-700">Sun</div>
                    <div class="bg-gray-50 p-4 text-center font-semibold text-gray-700">Mon</div>
                    <div class="bg-gray-50 p-4 text-center font-semibold text-gray-700">Tue</div>
                    <div class="bg-gray-50 p-4 text-center font-semibold text-gray-700">Wed</div>
                    <div class="bg-gray-50 p-4 text-center font-semibold text-gray-700">Thu</div>
                    <div class="bg-gray-50 p-4 text-center font-semibold text-gray-700">Fri</div>
                    <div class="bg-gray-50 p-4 text-center font-semibold text-gray-700">Sat</div>
                    
                    <!-- Calendar Days -->
                    <div id="calendarDays">
                        <!-- Days will be populated by JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Week View -->
            <div id="weekView" class="p-8 hidden">
                <div class="overflow-x-auto">
                    <div class="min-w-full">
                        <!-- Time slots and appointments for week view -->
                        <div id="weekCalendar">
                            <!-- Week calendar will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Day View -->
            <div id="dayView" class="p-8 hidden">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4" id="selectedDate">{{ now()->format('l, F j, Y') }}</h3>
                        <div id="dayAppointments">
                            <!-- Day appointments will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Appointments Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Mini Calendar -->
            <div class="medical-card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Navigation</h3>
                <div class="mini-calendar">
                    <div class="grid grid-cols-7 gap-1 text-xs">
                        <div class="text-center font-semibold text-gray-500 p-1">S</div>
                        <div class="text-center font-semibold text-gray-500 p-1">M</div>
                        <div class="text-center font-semibold text-gray-500 p-1">T</div>
                        <div class="text-center font-semibold text-gray-500 p-1">W</div>
                        <div class="text-center font-semibold text-gray-500 p-1">T</div>
                        <div class="text-center font-semibold text-gray-500 p-1">F</div>
                        <div class="text-center font-semibold text-gray-500 p-1">S</div>
                        <!-- Mini calendar days will be populated by JavaScript -->
                        <div id="miniCalendarDays"></div>
                    </div>
                </div>
            </div>

            <!-- Today's Summary -->
            <div class="medical-card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Today's Summary</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <span class="text-sm font-medium text-green-800">Completed</span>
                        <span class="text-lg font-bold text-green-600" id="todayCompleted">{{ $todayStats['completed'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                        <span class="text-sm font-medium text-blue-800">Scheduled</span>
                        <span class="text-lg font-bold text-blue-600" id="todayScheduled">{{ $todayStats['scheduled'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <span class="text-sm font-medium text-yellow-800">Pending</span>
                        <span class="text-lg font-bold text-yellow-600" id="todayPending">{{ $todayStats['pending'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <span class="text-sm font-medium text-red-800">Cancelled</span>
                        <span class="text-lg font-bold text-red-600" id="todayCancelled">{{ $todayStats['cancelled'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments -->
            <div class="medical-card p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">⏰ Next Appointments</h3>
                <div class="space-y-4" id="upcomingAppointments">
                    @forelse($upcomingAppointments ?? [] as $appointment)
                    <div class="appointment-item p-3 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-semibold text-gray-900">{{ $appointment->patient->name ?? 'Unknown' }}</span>
                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('M d') }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-600">{{ ucfirst(str_replace('_', ' ', $appointment->type ?? 'consultation')) }}</span>
                            <span class="text-sm font-bold text-blue-600">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <div class="text-4xl mb-2">📅</div>
                        <p class="text-gray-500 text-sm">No upcoming appointments</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appointment Details Modal -->
<div id="appointmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900">Appointment Details</h3>
                <button onclick="closeAppointmentModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="appointmentDetails" class="space-y-4">
                <!-- Appointment details will be populated here -->
            </div>
            
            <div class="flex justify-end space-x-4 pt-6">
                <button onclick="editCurrentAppointment()" class="btn-secondary">Edit</button>
                <button onclick="viewPatientFromAppointment()" class="btn-primary">View Patient</button>
            </div>
        </div>
    </div>
</div>

<style>
.calendar-day {
    @apply bg-white p-2 min-h-32 border border-gray-100 relative;
}

.calendar-day.other-month {
    @apply bg-gray-50 text-gray-400;
}

.calendar-day.today {
    @apply bg-blue-50 border-blue-200;
}

.calendar-day.selected {
    @apply bg-blue-100 border-blue-300;
}

.appointment-block {
    @apply text-xs p-1 mb-1 rounded cursor-pointer truncate;
}

.appointment-confirmed {
    @apply bg-green-200 text-green-800;
}

.appointment-scheduled {
    @apply bg-yellow-200 text-yellow-800;
}

.appointment-completed {
    @apply bg-blue-200 text-blue-800;
}

.appointment-cancelled {
    @apply bg-red-200 text-red-800;
}

.mini-calendar-day {
    @apply text-center p-1 text-xs cursor-pointer rounded;
}

.mini-calendar-day:hover {
    @apply bg-blue-100;
}

.mini-calendar-day.today {
    @apply bg-blue-500 text-white;
}

.mini-calendar-day.has-appointments {
    @apply bg-green-100 text-green-800 font-semibold;
}
</style>
@endsection

@push('scripts')
<script>
// Calendar data and state
let currentDate = new Date();
let selectedDate = new Date();
let appointments = @json($appointments ?? []);
let currentView = 'month';

// Initialize calendar
document.addEventListener('DOMContentLoaded', function() {
    initializeCalendar();
    setupEventListeners();
    renderCalendar();
});

// Setup event listeners
function setupEventListeners() {
    document.getElementById('viewMode').addEventListener('change', function() {
        switchView(this.value);
    });
}

// Initialize calendar with current data
function initializeCalendar() {
    renderCalendar();
    renderMiniCalendar();
    updateCurrentMonthDisplay();
}

// Switch between calendar views
function switchView(view) {
    currentView = view;
    
    // Hide all views
    document.getElementById('monthView').classList.add('hidden');
    document.getElementById('weekView').classList.add('hidden');
    document.getElementById('dayView').classList.add('hidden');
    
    // Show selected view
    switch(view) {
        case 'month':
            document.getElementById('monthView').classList.remove('hidden');
            renderCalendar();
            break;
        case 'week':
            document.getElementById('weekView').classList.remove('hidden');
            renderWeekView();
            break;
        case 'day':
            document.getElementById('dayView').classList.remove('hidden');
            renderDayView();
            break;
    }
}

// Render month calendar
function renderCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const today = new Date();
    
    // Get first day of month and number of days
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());
    
    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';
    
    // Generate 42 days (6 weeks)
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        // Add classes for styling
        if (date.getMonth() !== month) {
            dayElement.classList.add('other-month');
        }
        
        if (date.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }
        
        if (date.toDateString() === selectedDate.toDateString()) {
            dayElement.classList.add('selected');
        }
        
        // Day number
        const dayNumber = document.createElement('div');
        dayNumber.className = 'text-sm font-semibold mb-1';
        dayNumber.textContent = date.getDate();
        dayElement.appendChild(dayNumber);
        
        // Add appointments for this day
        const dayAppointments = getAppointmentsForDate(date);
        dayAppointments.forEach(appointment => {
            const appointmentElement = document.createElement('div');
            appointmentElement.className = `appointment-block appointment-${appointment.status || 'scheduled'}`;
            appointmentElement.textContent = `${formatTime(appointment.appointment_time)} ${appointment.patient_name || 'Unknown'}`;
            appointmentElement.onclick = () => showAppointmentDetails(appointment);
            dayElement.appendChild(appointmentElement);
        });
        
        // Click handler for day selection
        dayElement.onclick = (e) => {
            if (e.target === dayElement || e.target === dayNumber) {
                selectDate(date);
            }
        };
        
        calendarDays.appendChild(dayElement);
    }
}

// Render week view
function renderWeekView() {
    const weekCalendar = document.getElementById('weekCalendar');
    weekCalendar.innerHTML = '';
    
    // Get start of week
    const startOfWeek = new Date(currentDate);
    startOfWeek.setDate(currentDate.getDate() - currentDate.getDay());
    
    // Create time slots
    const timeSlots = [];
    for (let hour = 8; hour < 18; hour++) {
        timeSlots.push(`${hour}:00`);
        timeSlots.push(`${hour}:30`);
    }
    
    // Create week grid
    const weekGrid = document.createElement('div');
    weekGrid.className = 'grid grid-cols-8 gap-px bg-gray-200 rounded-lg overflow-hidden';
    
    // Header row
    const headerRow = document.createElement('div');
    headerRow.className = 'col-span-8 grid grid-cols-8 gap-px';
    
    // Time column header
    const timeHeader = document.createElement('div');
    timeHeader.className = 'bg-gray-50 p-2 text-center font-semibold text-gray-700';
    timeHeader.textContent = 'Time';
    headerRow.appendChild(timeHeader);
    
    // Day headers
    for (let i = 0; i < 7; i++) {
        const date = new Date(startOfWeek);
        date.setDate(startOfWeek.getDate() + i);
        
        const dayHeader = document.createElement('div');
        dayHeader.className = 'bg-gray-50 p-2 text-center font-semibold text-gray-700';
        dayHeader.textContent = date.toLocaleDateString('en-US', { weekday: 'short', month: 'numeric', day: 'numeric' });
        headerRow.appendChild(dayHeader);
    }
    
    weekGrid.appendChild(headerRow);
    
    // Time slots
    timeSlots.forEach(time => {
        const timeRow = document.createElement('div');
        timeRow.className = 'col-span-8 grid grid-cols-8 gap-px';
        
        // Time label
        const timeLabel = document.createElement('div');
        timeLabel.className = 'bg-gray-50 p-2 text-center text-sm text-gray-600';
        timeLabel.textContent = time;
        timeRow.appendChild(timeLabel);
        
        // Day columns
        for (let i = 0; i < 7; i++) {
            const date = new Date(startOfWeek);
            date.setDate(startOfWeek.getDate() + i);
            
            const dayCell = document.createElement('div');
            dayCell.className = 'bg-white p-2 min-h-12 border-r border-gray-100';
            
            // Add appointments for this time slot
            const timeAppointments = getAppointmentsForDateTime(date, time);
            timeAppointments.forEach(appointment => {
                const appointmentElement = document.createElement('div');
                appointmentElement.className = `appointment-block appointment-${appointment.status || 'scheduled'}`;
                appointmentElement.textContent = appointment.patient_name || 'Unknown';
                appointmentElement.onclick = () => showAppointmentDetails(appointment);
                dayCell.appendChild(appointmentElement);
            });
            
            timeRow.appendChild(dayCell);
        }
        
        weekGrid.appendChild(timeRow);
    });
    
    weekCalendar.appendChild(weekGrid);
}

// Render day view
function renderDayView() {
    const dayAppointments = document.getElementById('dayAppointments');
    dayAppointments.innerHTML = '';
    
    const appointments = getAppointmentsForDate(selectedDate);
    
    if (appointments.length === 0) {
        dayAppointments.innerHTML = `
            <div class="text-center py-12">
                <div class="text-6xl mb-4">📅</div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Appointments</h3>
                <p class="text-gray-600 mb-6">No appointments scheduled for this day</p>
                <button onclick="scheduleAppointment()" class="btn-primary">
                    Schedule Appointment
                </button>
            </div>
        `;
        return;
    }
    
    // Sort appointments by time
    appointments.sort((a, b) => new Date(a.appointment_time) - new Date(b.appointment_time));
    
    appointments.forEach(appointment => {
        const appointmentElement = document.createElement('div');
        appointmentElement.className = 'appointment-card p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer';
        appointmentElement.onclick = () => showAppointmentDetails(appointment);
        
        appointmentElement.innerHTML = `
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-lg font-bold text-gray-900">${appointment.patient_name || 'Unknown Patient'}</h4>
                <span class="text-lg font-bold text-blue-600">${formatTime(appointment.appointment_time)}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600">${ucfirst(appointment.type || 'consultation')}</span>
                <span class="px-2 py-1 text-xs rounded-full font-semibold appointment-${appointment.status || 'scheduled'}">
                    ${ucfirst(appointment.status || 'scheduled')}
                </span>
            </div>
            ${appointment.notes ? `<p class="mt-2 text-sm text-gray-700">${appointment.notes}</p>` : ''}
        `;
        
        dayAppointments.appendChild(appointmentElement);
    });
}

// Render mini calendar
function renderMiniCalendar() {
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const today = new Date();
    
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay());
    
    const miniCalendarDays = document.getElementById('miniCalendarDays');
    miniCalendarDays.innerHTML = '';
    
    for (let i = 0; i < 42; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        
        const dayElement = document.createElement('div');
        dayElement.className = 'mini-calendar-day';
        dayElement.textContent = date.getDate();
        
        if (date.getMonth() !== month) {
            dayElement.classList.add('text-gray-300');
        }
        
        if (date.toDateString() === today.toDateString()) {
            dayElement.classList.add('today');
        }
        
        if (getAppointmentsForDate(date).length > 0) {
            dayElement.classList.add('has-appointments');
        }
        
        dayElement.onclick = () => {
            selectDate(date);
            switchView('day');
            document.getElementById('viewMode').value = 'day';
        };
        
        miniCalendarDays.appendChild(dayElement);
    }
}

// Navigation functions
function previousMonth() {
    currentDate.setMonth(currentDate.getMonth() - 1);
    updateCurrentMonthDisplay();
    renderCalendar();
    renderMiniCalendar();
}

function nextMonth() {
    currentDate.setMonth(currentDate.getMonth() + 1);
    updateCurrentMonthDisplay();
    renderCalendar();
    renderMiniCalendar();
}

function goToToday() {
    currentDate = new Date();
    selectedDate = new Date();
    updateCurrentMonthDisplay();
    renderCalendar();
    renderMiniCalendar();
}

function selectDate(date) {
    selectedDate = new Date(date);
    renderCalendar();
    updateSelectedDateDisplay();
}

function updateCurrentMonthDisplay() {
    document.getElementById('currentMonth').textContent = currentDate.toLocaleDateString('en-US', { 
        month: 'long', 
        year: 'numeric' 
    });
}

function updateSelectedDateDisplay() {
    document.getElementById('selectedDate').textContent = selectedDate.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
}

// Helper functions
function getAppointmentsForDate(date) {
    return appointments.filter(appointment => {
        const appointmentDate = new Date(appointment.appointment_time);
        return appointmentDate.toDateString() === date.toDateString();
    });
}

function getAppointmentsForDateTime(date, timeSlot) {
    const [hour, minute] = timeSlot.split(':');
    const slotTime = new Date(date);
    slotTime.setHours(parseInt(hour), parseInt(minute), 0, 0);
    
    return appointments.filter(appointment => {
        const appointmentTime = new Date(appointment.appointment_time);
        const appointmentHour = appointmentTime.getHours();
        const appointmentMinute = appointmentTime.getMinutes();
        
        return appointmentTime.toDateString() === date.toDateString() &&
               appointmentHour === parseInt(hour) &&
               Math.abs(appointmentMinute - parseInt(minute)) < 30;
    });
}

function formatTime(dateTimeString) {
    return new Date(dateTimeString).toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit',
        hour12: true 
    });
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1).replace('_', ' ');
}

// Appointment modal functions
function showAppointmentDetails(appointment) {
    const modal = document.getElementById('appointmentModal');
    const details = document.getElementById('appointmentDetails');
    
    details.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Patient</label>
                <p class="text-lg font-semibold text-gray-900">${appointment.patient_name || 'Unknown'}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Type</label>
                <p class="text-lg text-gray-900">${ucfirst(appointment.type || 'consultation')}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Date & Time</label>
                <p class="text-lg text-gray-900">${new Date(appointment.appointment_time).toLocaleDateString()} at ${formatTime(appointment.appointment_time)}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Duration</label>
                <p class="text-lg text-gray-900">${appointment.duration || 30} minutes</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <span class="px-3 py-1 text-sm rounded-full font-semibold appointment-${appointment.status || 'scheduled'}">
                    ${ucfirst(appointment.status || 'scheduled')}
                </span>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Contact</label>
                <p class="text-lg text-gray-900">${appointment.patient_phone || 'No phone'}</p>
            </div>
        </div>
        ${appointment.notes ? `
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Notes</label>
                <p class="text-gray-900 bg-gray-50 p-3 rounded-lg">${appointment.notes}</p>
            </div>
        ` : ''}
    `;
    
    // Store current appointment for actions
    modal.dataset.appointmentId = appointment.id;
    modal.classList.remove('hidden');
}

function closeAppointmentModal() {
    document.getElementById('appointmentModal').classList.add('hidden');
}

function editCurrentAppointment() {
    const appointmentId = document.getElementById('appointmentModal').dataset.appointmentId;
    window.showToast('✏️ Edit appointment feature coming soon!', 'info');
    closeAppointmentModal();
}

function viewPatientFromAppointment() {
    const appointmentId = document.getElementById('appointmentModal').dataset.appointmentId;
    const appointment = appointments.find(a => a.id == appointmentId);
    if (appointment && appointment.patient_id) {
        window.location.href = `/doctor/patients/${appointment.patient_id}/monitor`;
    }
    closeAppointmentModal();
}

// Schedule appointment function (to be implemented)
function scheduleAppointment() {
    window.showToast('📅 Schedule appointment feature coming soon!', 'info');
}

// Real-time updates (if using WebSockets)
if (window.pusher) {
    const doctorChannel = pusher.subscribe('doctor.{{ auth()->id() }}');
    
    doctorChannel.bind('appointment-update', function(data) {
        // Update appointment in local array
        const index = appointments.findIndex(a => a.id === data.appointment.id);
        if (index !== -1) {
            appointments[index] = data.appointment;
        } else {
            appointments.push(data.appointment);
        }
        
        // Re-render current view
        switch(currentView) {
            case 'month':
                renderCalendar();
                break;
            case 'week':
                renderWeekView();
                break;
            case 'day':
                renderDayView();
                break;
        }
        
        renderMiniCalendar();
        window.showToast('📅 Calendar updated with new appointment', 'info');
    });
}
</script>
@endpush