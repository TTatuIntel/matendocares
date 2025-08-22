@extends('doctor.layout')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="medical-card p-6 rounded-xl">
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.07 13H2.05L2 12l.05-1H4.07a8.003 8.003 0 010 2z"></path>
                </svg>
                Your Notifications
            </h1>

            <div class="flex space-x-2">
                <button onclick="markAllAsRead()" class="btn-secondary py-2 px-4 text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Mark All as Read
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="space-y-3">
            @forelse($notifications as $notification)
                <div class="notification-item p-4 border border-gray-200 rounded-lg hover:shadow-md transition-all duration-200 cursor-pointer
                    {{ $notification->unread() ? 'bg-blue-50 border-blue-200' : 'bg-white' }}"
                    onclick="openNotificationOverlay('{{ $notification->id }}', '{{ $notification->data['patient_id'] ?? '' }}', '{{ $notification->data['patient_user_id'] ?? '' }}')"
                    data-notification="{{ $notification->id }}"
                    data-patient-user-id="{{ $notification->data['patient_user_id'] ?? '' }}">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                @if(isset($notification->data['is_critical']) && $notification->data['is_critical'])
                                    <span class="status-critical bg-red-500 rounded-full w-3 h-3"></span>
                                @else
                                    <span class="status-normal bg-blue-500 rounded-full w-3 h-3"></span>
                                @endif

                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ $notification->data['patient_name'] ?? 'Patient' }}
                                        @if($notification->unread())
                                            <span class="notification-dot ml-2 inline-block h-2 w-2 rounded-full bg-blue-600"></span>
                                        @endif
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">{{ $notification->data['message'] }}</p>
                                    <p class="text-xs text-gray-500 mt-2">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="text-blue-600 hover:text-blue-800 text-sm whitespace-nowrap ml-4">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">No notifications</h3>
                    <p class="mt-1 text-gray-500">You don't have any notifications yet.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Notification Overlay -->
<div id="notificationOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800" id="overlayPatientName"></h3>
                <button onclick="closeNotificationOverlay()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-4">
                <div class="flex items-start">
                    <div class="flex-1">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-gray-800" id="overlayNotificationMessage"></p>
                            <p class="text-xs text-gray-500 mt-2" id="overlayNotificationTime"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h4 class="font-medium text-gray-900 mb-3">Patient Information</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500">Age</p>
                            <p class="text-gray-900" id="overlayPatientAge"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Gender</p>
                            <p class="text-gray-900" id="overlayPatientGender"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Condition</p>
                            <p class="text-gray-900" id="overlayPatientCondition"></p>
                        </div>
                        <div>
                            <p class="text-gray-500">Last Checked</p>
                            <p class="text-gray-900" id="overlayLastChecked"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button onclick="closeNotificationOverlay()" class="btn-secondary px-4 py-2">
                    Close
                </button>
                <button id="viewPatientBtn" class="btn-primary px-4 py-2">
                    View Patient Monitor
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentNotificationId = null;
    let currentPatientId = null;
    let currentPatientUserId = null;

    // Open notification overlay
    function openNotificationOverlay(notificationId, patientId, patientUserId) {
        currentNotificationId = notificationId;
        currentPatientId = patientId;
        currentPatientUserId = patientUserId;

        // Mark as read when opening
        markNotificationAsRead(notificationId);

        // Fetch notification details
        const notificationItem = document.querySelector(`[data-notification="${notificationId}"]`);
        const patientName = notificationItem.querySelector('.font-medium').textContent.trim().replace('●', '').trim();
        const message = notificationItem.querySelector('.text-sm').textContent;
        const time = notificationItem.querySelector('.text-xs').textContent;

        // Set overlay content
        document.getElementById('overlayPatientName').textContent = patientName;
        document.getElementById('overlayNotificationMessage').textContent = message;
        document.getElementById('overlayNotificationTime').textContent = time;

        // Fetch real patient data from the server
        fetch(`/api/patients/${patientUserId}/details`)
            .then(response => response.json())
            .then(patientData => {
                document.getElementById('overlayPatientAge').textContent = patientData.age || 'Not specified';
                document.getElementById('overlayPatientGender').textContent = patientData.gender || 'Not specified';
                document.getElementById('overlayPatientCondition').textContent = patientData.condition || 'Not specified';
                document.getElementById('overlayLastChecked').textContent = patientData.last_checked || 'Not available';
            })
            .catch(error => {
                console.error('Error fetching patient data:', error);
                // Fallback to placeholder data if API fails
                document.getElementById('overlayPatientAge').textContent = 'Not available';
                document.getElementById('overlayPatientGender').textContent = 'Not available';
                document.getElementById('overlayPatientCondition').textContent = 'Not available';
                document.getElementById('overlayLastChecked').textContent = 'Not available';
            });

        // Set up the view patient button
        const viewPatientBtn = document.getElementById('viewPatientBtn');
        viewPatientBtn.onclick = function() {
            // Use patient_user_id to navigate to the patient monitor page
            window.location.href = `/doctor/patient`;
        };

        // Show overlay
        document.getElementById('notificationOverlay').classList.remove('hidden');
    }

    // Close notification overlay
    function closeNotificationOverlay() {
        document.getElementById('notificationOverlay').classList.add('hidden');
        currentNotificationId = null;
        currentPatientId = null;
        currentPatientUserId = null;
    }

    // Mark single notification as read
    function markNotificationAsRead(notificationId) {
        fetch(`/doctor/notifications/${notificationId}/mark-as-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            // Update the UI
            const item = document.querySelector(`[data-notification="${notificationId}"]`);
            if (item) {
                item.classList.remove('bg-blue-50', 'border-blue-200');
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.remove();
            }

            // Update the notification count badge globally
            if (window.updateNotificationCount) {
                updateNotificationCount();
            }
        });
    }

    // Mark all notifications as read
    function markAllAsRead() {
        fetch('/doctor/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            // Update all UI elements
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('bg-blue-50', 'border-blue-200');
                const dot = item.querySelector('.notification-dot');
                if (dot) dot.remove();
            });

            // Update the notification count badge globally
            if (window.updateNotificationCount) {
                updateNotificationCount();
            }
        });
    }
</script>
@endpush

<style>
    .notification-item {
        transition: all 0.2s ease;
    }

    .notification-item:hover {
        transform: translateY(-1px);
    }

    #notificationOverlay {
        transition: opacity 0.3s ease;
    }

    .btn-primary {
        background-color: #3b82f6;
        color: white;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    .btn-primary:hover {
        background-color: #2563eb;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        border-radius: 0.375rem;
        padding: 0.5rem 1rem;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }
</style>
@endsection
