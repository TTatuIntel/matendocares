@extends('doctor.layout')

@section('title', 'Appointments')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="space-y-8">
        <!-- Header Section -->
        <div class="p-8 bg-white rounded-lg shadow">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">
                        Appointment Management
                    </h1>
                    <p class="text-gray-600">Welcome back, Dr. {{ Auth::user()->name }}</p>
                </div>
                <div class="mt-6 lg:mt-0">
                    <button onclick="openAppointmentModal()" class="btn-primary">
                        Schedule Appointment
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Requests -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Pending Requests</h3>
                <p class="text-gray-600">Appointment requests awaiting your response</p>
            </div>

            <div class="p-8">
                <div class="space-y-4">
                    @forelse($pendingRequests ?? [] as $request)
                    <div class="p-6 bg-white rounded-lg border border-gray-200 hover:shadow-md">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-lg font-bold text-gray-900">
                                        {{ $request->patient->user->name ?? 'Unknown Patient' }}
                                        <span class="ml-2 text-sm font-normal text-gray-600">
                                            ({{ $request->patient->age ?? 'N/A' }} years)
                                        </span>
                                    </h4>
                                    <span class="px-3 py-1 text-xs rounded-full font-semibold
                                        @if($request->priority === 'urgent') bg-orange-100 text-orange-800
                                        @elseif($request->priority === 'emergency') bg-red-100 text-red-800
                                        @else bg-blue-100 text-blue-800 @endif">
                                        {{ ucfirst($request->priority) }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Requested</p>
                                        <p class="font-medium">{{ $request->created_at->format('M d, Y g:i A') }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Reason</p>
                                        <p class="font-medium">{{ $request->description }}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Preferred Time</p>
                                        <p class="font-medium">{{ $request->scheduled_at->format('M d, Y g:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-3 pt-6 mt-4 border-t border-gray-200">
                            <button onclick="openAcceptModal('{{ $request->id }}')"
                                class="btn-primary text-sm px-4 py-2">
                                Accept
                            </button>
                            <button onclick="openAppointmentModal()"
                                class="btn-secondary text-sm px-4 py-2">
                                Reschedule
                            </button>
                            <button onclick="rejectRequest('{{ $request->id }}')"
                                class="btn-danger text-sm px-4 py-2 bg-red-100 text-red-700 hover:bg-red-200">
                                Reject
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h4 class="text-lg font-medium text-gray-900 mt-4">No pending requests</h4>
                        <p class="text-gray-500 mt-1">You have no appointment requests at this time</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Today's Schedule -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Today's Schedule</h3>
                <p class="text-gray-600">{{ now()->format('l, F j, Y') }}</p>
            </div>

            <div class="p-8">
                <div class="space-y-4">
                    @forelse($todaySchedule ?? [] as $appointment)
                    <div class="flex flex-col md:flex-row md:items-center justify-between p-6 bg-white rounded-lg border border-gray-200 hover:shadow-md">
                        <div class="flex-1 mb-4 md:mb-0">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-gray-900 flex items-center">
                                    {{ $appointment->patient->user->name ?? 'Unknown Patient' }}
                                    <span class="ml-3 text-sm font-normal text-gray-600">
                                        ({{ $appointment->patient->age ?? 'N/A' }} years)
                                    </span>
                                </h4>
                                <span class="text-xl font-bold text-blue-600">
                                    {{ $appointment->scheduled_at->format('g:i A') }}
                                </span>
                            </div>

                            <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-gray-500">Appointment Type</p>
                                    <p class="font-medium">{{ ucfirst($appointment->type) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Duration</p>
                                    <p class="font-medium">{{ $appointment->duration_minutes }} minutes</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Location</p>
                                    <p class="font-medium">{{ $appointment->is_telemedicine ? 'Virtual' : 'In-Person' }}</p>
                                    @if($appointment->is_telemedicine && $appointment->meeting_link)
                                    <a href="{{ $appointment->meeting_link }}" target="_blank" class="text-blue-600 hover:underline text-sm">Join Meeting</a>
                                    @endif
                                </div>
                            </div>

                            @if($appointment->notes)
                            <div class="mt-3 bg-gray-50 p-3 rounded">
                                <p class="text-sm font-semibold text-gray-700 mb-1">Notes:</p>
                                <p class="text-sm text-gray-700">{{ $appointment->notes }}</p>
                            </div>
                            @endif
                        </div>

                        <div class="flex flex-col md:items-end space-y-3">
                            <span class="px-3 py-1 text-sm rounded-full font-semibold
                                @switch($appointment->status)
                                    @case('confirmed') bg-green-100 text-green-800 @break
                                    @case('completed') bg-blue-100 text-blue-800 @break
                                    @case('cancelled') bg-red-100 text-red-800 @break
                                    @default bg-yellow-100 text-yellow-800
                                @endswitch">
                                {{ ucfirst($appointment->status) }}
                            </span>

                            <div class="flex space-x-2">
                                <button onclick="startAppointment({{ $appointment->id }})"
                                    class="btn-primary text-sm px-4 py-2">
                                    Start
                                </button>

                              <button onclick="openAppointmentModal()" class="btn-secondary text-sm px-4 py-2">
                            ReSchedule
                        </button>
                                <button onclick="showCancelModal('{{ $appointment->id }}')"
                                    class="btn-danger text-sm px-4 py-2 bg-red-100 text-red-700 hover:bg-red-200">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-12">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">No Appointments Scheduled Today</h3>
                        <p class="text-gray-600 mb-4">You have no appointments scheduled for today</p>
                        <button onclick="openAppointmentModal()" class="btn-primary">
                            Schedule Appointment
                        </button>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming Appointments -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-8 border-b border-gray-200">
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Upcoming Appointments</h3>
                <p class="text-gray-600">All confirmed future appointments</p>
            </div>

            <div class="p-8">
                @if(count($upcomingAppointments ?? []) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($upcomingAppointments ?? [] as $appointment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                            <span class="text-gray-600">{{ substr($appointment->patient->user->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $appointment->patient->user->name ?? 'Unknown' }}</div>
                                            <div class="text-sm text-gray-500">{{ $appointment->patient->age ?? 'N/A' }} years</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $appointment->scheduled_at->format('M d, Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $appointment->scheduled_at->format('g:i A') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full font-semibold bg-blue-100 text-blue-800">
                                        {{ ucfirst($appointment->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full font-semibold
                                        @switch($appointment->status)
                                            @case('confirmed') bg-green-100 text-green-800 @break
                                            @case('completed') bg-blue-100 text-blue-800 @break
                                            @case('cancelled') bg-red-100 text-red-800 @break
                                            @default bg-yellow-100 text-yellow-800
                                        @endswitch">
                                        {{ ucfirst($appointment->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <button onclick="viewPatient({{ $appointment->patient->id ?? 0 }})" class="text-blue-600 hover:text-blue-900 mr-3">View</button>
                                    <button onclick="openAppointmentModal('{{ $appointment->id }}', 'reschedule')" class="text-green-600 hover:text-green-900 mr-3">Reschedule</button>
                                    <button onclick="showCancelModal('{{ $appointment->id }}')" class="text-red-600 hover:text-red-900">Cancel</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">No Upcoming Appointments</h3>
                    <p class="text-gray-600 mb-4">You have no upcoming appointments scheduled</p>
                    <button onclick="openAppointmentModal()" class="btn-primary">
                        Schedule Appointment
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Unified Appointment Modal -->
<div id="appointmentModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 id="appointmentModalTitle" class="text-xl font-bold text-gray-900">Schedule Appointment</h3>
                <button onclick="closeAppointmentModal()" class="text-gray-400 hover:text-gray-600">
                    &times;
                </button>
            </div>
        </div>

        <form id="appointmentForm" class="p-6">
            @csrf
            <input type="hidden" name="appointment_id" id="appointment_id">
            <input type="hidden" name="action_type" id="action_type">

            <div class="space-y-6">
                <div class="form-group" id="patientSelectGroup">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Select Patient</label>
                    <select name="patient_id" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        <option value="">Choose a patient...</option>
                        @foreach($patients ?? [] as $patient)
                            <option value="{{ $patient->id }}">{{ $patient->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Date</label>
                        <input type="date" name="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Time</label>
                        <input type="time" name="time" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Appointment Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="consultation">Consultation</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="emergency">Emergency</option>
                            <option value="routine">Routine Checkup</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" min="15" max="120" value="30" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Appointment Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Enter any notes about this appointment..."></textarea>
                </div>

                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_telemedicine" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm font-medium text-gray-700">This is a virtual appointment</span>
                    </label>
                </div>

                <div class="form-group" id="meetingLinkGroup" style="display: none;">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Meeting Link</label>
                    <input type="url" name="meeting_link" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="https://meet.example.com/your-room">
                </div>

                <div id="acceptFields" style="display: none;">
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Initial Notes</label>
                        <textarea name="initial_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Enter any initial notes for the patient..."></textarea>
                    </div>
                </div>

                <div id="rescheduleFields" style="display: none;">
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Reason for Rescheduling</label>
                        <textarea name="reschedule_reason" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Enter reason for rescheduling..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                <button type="button" onclick="closeAppointmentModal()" class="btn-secondary px-6 py-2">Cancel</button>
                <button type="submit" id="submitButton" class="btn-primary px-6 py-2">Submit</button>
            </div>
        </form>
    </div>
</div>

<!-- Accept Request Modal -->
<div id="acceptModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg max-w-2xl w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Accept Appointment Request</h3>
                <button onclick="closeAcceptModal()" class="text-gray-400 hover:text-gray-600">
                    &times;
                </button>
            </div>
        </div>

        <form id="acceptForm" class="p-6">
            @csrf
            <input type="hidden" name="appointment_id" id="accept_appointment_id">

            <div class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Date</label>
                        <input type="date" name="date" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Confirm Time</label>
                        <input type="time" name="time" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Appointment Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="consultation">Consultation</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="emergency">Emergency</option>
                            <option value="routine">Routine Checkup</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Duration (minutes)</label>
                        <input type="number" name="duration_minutes" min="15" max="120" value="30" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Doctor's Notes</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Enter any notes for this appointment..."></textarea>
                </div>

                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_telemedicine" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm font-medium text-gray-700">This is a virtual appointment</span>
                    </label>
                </div>

                <div class="form-group" id="acceptMeetingLinkGroup" style="display: none;">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Meeting Link</label>
                    <input type="url" name="meeting_link" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="https://meet.example.com/your-room">
                </div>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                <button type="button" onclick="closeAcceptModal()" class="btn-secondary px-6 py-2">Cancel</button>
                <button type="submit" class="btn-primary px-6 py-2">Confirm Acceptance</button>
            </div>
        </form>
    </div>
</div>

<!-- Cancel Appointment Modal -->
<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-xl font-bold text-gray-900">Cancel Appointment</h3>
                <button onclick="closeCancelModal()" class="text-gray-400 hover:text-gray-600">
                    &times;
                </button>
            </div>
        </div>

        <form id="cancelForm" class="p-6">
            @csrf
            <input type="hidden" name="appointment_id" id="cancel_appointment_id">
            <div class="form-group">
                <label class="block text-sm font-bold text-gray-700 mb-2">Reason for Cancellation</label>
                <textarea name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" required></textarea>
            </div>

            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200 mt-6">
                <button type="button" onclick="closeCancelModal()" class="btn-secondary px-6 py-2">Cancel</button>
                <button type="submit" class="btn-danger px-6 py-2 bg-red-600 text-white hover:bg-red-700">Confirm Cancellation</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Unified Appointment Modal Functions
    function openAppointmentModal(appointmentId = null, actionType = 'schedule') {
        const modal = document.getElementById('appointmentModal');
        const title = document.getElementById('appointmentModalTitle');
        const form = document.getElementById('appointmentForm');
        const submitBtn = document.getElementById('submitButton');
        const patientSelectGroup = document.getElementById('patientSelectGroup');
        const acceptFields = document.getElementById('acceptFields');
        const rescheduleFields = document.getElementById('rescheduleFields');

        // Reset form
        form.reset();

        // Set action type
        document.getElementById('action_type').value = actionType;
        document.getElementById('appointment_id').value = appointmentId || '';

        // Configure modal based on action type
        if (actionType === 'schedule') {
            title.textContent = 'Schedule New Appointment';
            submitBtn.textContent = 'Schedule Appointment';
            patientSelectGroup.style.display = 'block';
            acceptFields.style.display = 'none';
            rescheduleFields.style.display = 'none';
        } else if (actionType === 'reschedule') {
            title.textContent = 'Reschedule Appointment';
            submitBtn.textContent = 'Reschedule';
            patientSelectGroup.style.display = 'none';
            acceptFields.style.display = 'none';
            rescheduleFields.style.display = 'block';

            // Pre-fill with existing appointment data (you would fetch this via AJAX)
            // This is a placeholder - you'd need to implement the actual data fetch
            if (appointmentId) {
                // fetchAppointmentData(appointmentId);
            }
        }

        modal.classList.remove('hidden');
    }



    function closeAppointmentModal() {
        document.getElementById('appointmentModal').classList.add('hidden');
    }

    // Accept Request Modal Functions
    function openAcceptModal(appointmentId) {
        const modal = document.getElementById('acceptModal');
        document.getElementById('accept_appointment_id').value = appointmentId;

        // Pre-fill with existing appointment data (you would fetch this via AJAX)
        // This is a placeholder - you'd need to implement the actual data fetch
        if (appointmentId) {
            // fetchAppointmentDataForAccept(appointmentId);
        }

        modal.classList.remove('hidden');
    }

    function closeAcceptModal() {
        document.getElementById('acceptModal').classList.add('hidden');
    }

    // Cancel Modal Functions
    function showCancelModal(appointmentId) {
        document.getElementById('cancel_appointment_id').value = appointmentId;
        document.getElementById('cancelModal').classList.remove('hidden');
    }

    function closeCancelModal() {
        document.getElementById('cancelModal').classList.add('hidden');
    }

    // Toggle meeting link field based on telemedicine checkbox
    document.querySelector('input[name="is_telemedicine"]').addEventListener('change', function() {
        document.getElementById('meetingLinkGroup').style.display = this.checked ? 'block' : 'none';
    });

    document.querySelector('#acceptForm input[name="is_telemedicine"]').addEventListener('change', function() {
        document.getElementById('acceptMeetingLinkGroup').style.display = this.checked ? 'block' : 'none';
    });

    // Form submissions
    // In your appointment form submission handler
document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    // Convert checkbox value to boolean
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    // Properly handle checkbox value
    data.is_telemedicine = data.is_telemedicine === 'on' ? true : false;

    // Clear meeting link if not telemedicine
    if (!data.is_telemedicine) {
        data.meeting_link = null;
    }

    try {
        const response = await fetch('/doctor/appointments', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

        if (!response.ok) {
            throw new Error(responseData.message || 'Failed to process request');
        }

        showNotification('success', responseData.message || 'Appointment scheduled successfully');
        closeAppointmentModal();
        setTimeout(() => {
            window.location.reload();
        }, 1500);

    } catch (error) {
        console.error('Error:', error);
        showNotification('error', error.message || 'An error occurred while scheduling the appointment');
    }
});



    document.getElementById('acceptForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());

    // Convert checkbox value to boolean
    data.is_telemedicine = data.is_telemedicine === 'on' ? true : false;

    // Clear meeting link if not telemedicine
    if (!data.is_telemedicine) {
        data.meeting_link = null;
    }

    const appointmentId = document.getElementById('accept_appointment_id').value;

    try {
        const response = await fetch(`/doctor/appointments/${appointmentId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        });

        const responseData = await response.json();

        if (!response.ok) {
            throw new Error(responseData.message || 'Failed to accept appointment');
        }

        showNotification('success', responseData.message || 'Appointment accepted successfully');
        closeAcceptModal();
        setTimeout(() => {
            window.location.reload();
        }, 1500);

    } catch (error) {
        console.error('Error:', error);
        showNotification('error', error.message || 'An error occurred while accepting the appointment');
    }
});

    document.getElementById('cancelForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const appointmentId = document.getElementById('cancel_appointment_id').value;
        await submitForm(`/doctor/appointments/${appointmentId}/cancel`, this);
    });

    async function submitForm(url, form) {
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const submitBtn = form.querySelector('button[type="submit"]');

        try {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner">Processing...</span>';
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': data._token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const responseData = await response.json();

            if (!response.ok) {
                throw new Error(responseData.message || 'Failed to process request');
            }

            showNotification('success', responseData.message || 'Action completed successfully');
            setTimeout(() => {
                window.location.reload();
            }, 1500);

        } catch (error) {
            console.error('Error:', error);
            showNotification('error', error.message || 'An error occurred');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = submitBtn.dataset.originalText || 'Submit';
            }
        }
    }

    // Appointment actions
    async function rejectRequest(appointmentId) {
        if (!confirm('Are you sure you want to reject this appointment request?')) {
            return;
        }

        try {
            const response = await fetch(`/doctor/appointments/${appointmentId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                showNotification('success', 'Appointment request rejected');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                throw new Error('Failed to reject appointment');
            }
        } catch (error) {
            console.error('Error:', error);
            showNotification('error', error.message || 'An error occurred while rejecting the appointment');
        }
    }

    function startAppointment(appointmentId) {
        window.location.href = `/doctor/appointments/${appointmentId}/start`;
    }

    function viewPatient(patientId) {
        window.location.href = `/doctor/patients/${patientId}`;
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
        // Set original button text for all submit buttons
        document.querySelectorAll('button[type="submit"]').forEach(btn => {
            btn.dataset.originalText = btn.textContent;
        });
    });
</script>
@endpush
