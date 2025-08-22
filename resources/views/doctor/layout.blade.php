<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Doctor Portal') - Medical Monitor</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Pusher for Real-time -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #ffffff 50%, #f0fdf4 100%);
            min-height: 100vh;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .medical-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(255, 255, 255, 0.9) 100%);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(59, 130, 246, 0.1);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border-radius: 1.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            border: none;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(59, 130, 246, 0.4);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #374151 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
            border: none;
            cursor: pointer;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .notification-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .critical-alert {
            animation: criticalPulse 1s infinite;
        }

        @keyframes criticalPulse {
            0%, 100% {
                background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                box-shadow: 0 0 20px rgba(239, 68, 68, 0.5);
            }
            50% {
                background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
                box-shadow: 0 0 30px rgba(239, 68, 68, 0.8);
            }
        }

        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }

        .status-normal { background: #10b981; }
        .status-warning { background: #f59e0b; }
        .status-critical { background: #ef4444; animation: pulse 1s infinite; }

        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            min-width: 300px;
            padding: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            transform: translateX(400px);
            transition: transform 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .toast-error {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        .toast-info {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
    </style>

    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="medical-card border-b border-blue-100 sticky top-0 z-40 bg-gradient-to-r from-green-600 to-indigo-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
<div class="flex items-center space-x-3">
    <img src="{{ asset('images/matelogo4.png') }}" alt="Mate Logo" class="h-10 w-auto">

</div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('doctor.dashboard') }}"
                       class="nav-link {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2zm16 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V7z"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('doctor.patient.index') }}"
                       class="nav-link {{ request()->routeIs('doctor.patients.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m-9 5.197v1a6 6 0 0010.967 0M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        Patients
                    </a>
                    <!-- <a href="{{ route('doctor.analytics') }}"
                       class="nav-link {{ request()->routeIs('doctor.analytics') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                        </svg>
                        Analytics
                    </a> -->

                    <a href="{{ route('doctor.appointments.index') }}"
                       class="nav-link {{ request()->routeIs('doctor.documents.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Appointments
                    </a>

<!-- Notification Dropdown Container -->
<div class="relative">
    <button onclick="toggleNotifications()" class="nav-link {{ request()->routeIs('doctor.notifications.*') ? 'active' : '' }} flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.07 13H2.05L2 12l.05-1H4.07a8.003 8.003 0 010 2z"></path>
        </svg>
        Notifications
        <!-- Unread Count Badge -->
        <span id="notificationCounter" class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold text-white bg-red-500 rounded-full">
            {{ auth()->user()->unreadNotifications()->count() }}
        </span>
    </button>

    <!-- Notification Dropdown -->
    <div id="notificationDropdown" class="hidden absolute right-0 mt-2 w-72 bg-white rounded-md shadow-lg z-50 border border-gray-200">
        <div class="p-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="font-medium">Notifications</h3>
            <button onclick="markAllAsRead()" class="text-sm text-blue-600 hover:text-blue-800">Mark all as read</button>
        </div>
        <div id="notificationList" class="max-h-80 overflow-y-auto">
            @foreach(auth()->user()->unreadNotifications()->take(5)->get() as $notification)
                <div class="p-3 border-b border-gray-100 hover:bg-gray-50" data-notification-id="{{ $notification->id }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 pt-0.5">
                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $notification->data['patient_name'] ?? 'System' }}</p>
                            <p class="text-sm text-gray-500">{{ $notification->data['message'] }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="p-3 text-center border-t border-gray-200">
            <a href="{{ route('doctor.notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
        </div>
    </div>
</div>
                </div>

                <!-- Notifications and User Menu -->
                <div class="flex items-center space-x-4">



                    <!-- User Profile -->
                    <div class="relative" id="userMenu">
                        <button class="flex items-center text-white hover:text-gray-200 font-medium" onclick="toggleUserMenu()">
                            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-blue-500 rounded-full flex items-center justify-center text-white font-bold mr-2">
                                {{ substr(auth()->user()->name ?? 'D', 0, 1) }}
                            </div>
                            <span class="hidden sm:inline">Dr. {{ auth()->user()->name ?? 'Doctor' }}</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg z-50 border border-gray-200">
                            <div class="py-2">
<a href="{{ route('doctor.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-xl">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Profile Settings
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    Settings
                                </a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Help & Support
                                </a>
                                <div class="border-t border-gray-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-b-xl">
                                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                        </svg>
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Critical Alerts Panel -->
    <div id="criticalAlertsPanel" class="hidden fixed top-16 right-4 w-96 bg-white rounded-xl shadow-xl z-50 border-2 border-red-200">
        <div class="p-4 border-b border-red-200 bg-red-50">
            <h3 class="font-bold text-red-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Critical Alerts
            </h3>
        </div>
        <div id="criticalAlertsList" class="max-h-96 overflow-y-auto">
            <!-- Critical alerts will be loaded here -->
        </div>
    </div>

    <!-- Notifications Panel -->
<!-- Notifications Panel -->
<div id="notificationsPanel" class="hidden fixed top-16 right-4 w-80 bg-white rounded-xl shadow-lg z-50 border border-gray-200">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-bold text-gray-900">Notifications</h3>
        <button onclick="markAllAsRead()" class="text-blue-600 text-sm hover:text-blue-800">
            Mark all as read
        </button>
    </div>
    <div id="notificationsList" class="max-h-96 overflow-y-auto">
        <!-- Notifications will be loaded here via JavaScript -->
        <div class="p-4 text-center text-gray-500">
            Loading notifications...
        </div>
    </div>
    <div class="p-3 border-t border-gray-200 text-center">
        <a href="{{ route('doctor.notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            View all notifications
        </a>
    </div>
</div>

    <!-- Main Content -->
    <main class="py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="medical-card border-t border-blue-100 mt-12 bg-gradient-to-r from-gray-50 to-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; 2024 Medical Monitor - Doctor Portal. All rights reserved.</p>
                <p class="mt-2 text-sm">Secure patient monitoring and care management platform.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- Global JavaScript -->
    <script>
        // CSRF Token Setup
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Real-time connection setup
        const pusher = new Pusher('{{ env("PUSHER_APP_KEY", "your-pusher-key") }}', {
            cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}'
        });

        // Subscribe to doctor's channel
        const doctorChannel = pusher.subscribe('doctor.{{ auth()->id() }}');

        // Listen for critical alerts
        doctorChannel.bind('critical-alert', function(data) {
            handleCriticalAlert(data);
        });

        // Listen for regular notifications
        doctorChannel.bind('notification', function(data) {
            handleNotification(data);
        });

        // Toast Notification System
        function showToast(message, type = 'info', duration = 5000) {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="hideToast(this)" class="ml-4 text-white hover:text-gray-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            `;

            document.getElementById('toast-container').appendChild(toast);

            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);

            // Auto hide
            setTimeout(() => hideToast(toast), duration);
        }

        function hideToast(element) {
            const toast = element.classList ? element : element.closest('.toast');
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }

        // Handle critical alerts
        function handleCriticalAlert(data) {
            // Show browser notification
            if (Notification.permission === 'granted') {
                new Notification('🚨 Critical Alert', {
                    body: data.message,
                    icon: '/favicon.ico',
                    tag: 'critical-' + data.id
                });
            }

            // Add to critical alerts panel
            addCriticalAlert(data);
            updateCriticalCount();

            // Show toast
            showToast(`🚨 Critical Alert: ${data.message}`, 'error', 10000);

            // Play sound alert
            playAlertSound();
        }

        // Handle regular notifications
        function handleNotification(data) {
            addNotification(data);
            updateNotificationCount();
            showToast(data.message, 'info');
        }

        // Add critical alert to panel
        function addCriticalAlert(data) {
            const alertsList = document.getElementById('criticalAlertsList');
            const alert = document.createElement('div');
            alert.className = 'p-4 border-b border-red-200 bg-red-50 hover:bg-red-100 cursor-pointer';
            alert.onclick = () => handleAlertClick(data);
            alert.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="status-critical"></div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-red-800">${data.title || 'Critical Alert'}</p>
                        <p class="text-xs text-red-600">${data.message}</p>
                        <p class="text-xs text-red-500 mt-1">${new Date().toLocaleTimeString()}</p>
                    </div>
                </div>
            `;
            alertsList.insertBefore(alert, alertsList.firstChild);
        }

        // Add notification to panel
        function addNotification(data) {
            const notificationsList = document.getElementById('notificationsList');
            const notification = document.createElement('div');
            notification.className = 'p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer';
            notification.onclick = () => handleNotificationClick(data);
            notification.innerHTML = `
                <div class="flex items-start space-x-3">
                    <div class="status-${data.type || 'normal'}"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">${data.title || 'Notification'}</p>
                        <p class="text-xs text-gray-600">${data.message}</p>
                        <p class="text-xs text-gray-500 mt-1">${new Date().toLocaleTimeString()}</p>
                    </div>
                </div>
            `;
            notificationsList.insertBefore(notification, notificationsList.firstChild);
        }

        // Update notification counts
        function updateCriticalCount() {
            const countEl = document.getElementById('criticalCount');
            const currentCount = parseInt(countEl.textContent) || 0;
            countEl.textContent = currentCount + 1;
            countEl.classList.remove('hidden');
        }

        function updateNotificationCount() {
            const countEl = document.getElementById('notificationCount');
            const currentCount = parseInt(countEl.textContent) || 0;
            countEl.textContent = currentCount + 1;
            countEl.classList.remove('hidden');
        }

        // Play alert sound
        function playAlertSound() {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSaB0PPJdycFJHfF8N+OQgoTXrXo66hVFAlHnn/ytmMcBjiR1fLNeSsFJHfH8N6PQAoTXrTr66hVFANGnt/xvmwhBTiB0fLKeSc');
            audio.play().catch(() => {});
        }

        // Navigation Functions
        function toggleUserMenu() {
            const dropdown = document.getElementById('userDropdown');
            dropdown.classList.toggle('hidden');
        }

        function toggleNotifications() {
            const panel = document.getElementById('notificationsPanel');
            panel.classList.toggle('hidden');
        }

        function toggleCriticalAlerts() {
            const panel = document.getElementById('criticalAlertsPanel');
            panel.classList.toggle('hidden');
        }

        // Handle alert/notification clicks
        function handleAlertClick(data) {
            if (data.patient_id) {
                window.location.href = `/doctor/patients/${data.patient_id}/monitor`;
            }
        }

        function handleNotificationClick(data) {
            if (data.url) {
                window.location.href = data.url;
            }
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const notificationsPanel = document.getElementById('notificationsPanel');
            const criticalAlertsPanel = document.getElementById('criticalAlertsPanel');

            if (!userMenu.contains(event.target)) {
                document.getElementById('userDropdown').classList.add('hidden');
            }

            if (!event.target.closest('#notificationsPanel') && !event.target.closest('#notificationBtn')) {
                notificationsPanel.classList.add('hidden');
            }

            if (!event.target.closest('#criticalAlertsPanel') && !event.target.closest('#criticalAlertsBtn')) {
                criticalAlertsPanel.classList.add('hidden');
            }
        });

        // Request notification permission
        if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
            Notification.requestPermission();
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Load existing notifications
            loadNotifications();

            // Show any flash messages
            @if(session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif

            @if(session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif

            @if(session('info'))
                showToast('{{ session('info') }}', 'info');
            @endif

            @if(session('warning'))
                showToast('{{ session('warning') }}', 'warning');
            @endif
        });


            try {
                const response = await fetch('/doctor/notifications', {
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    // Process notifications
                    if (data.critical && data.critical.length > 0) {
                        document.getElementById('criticalCount').textContent = data.critical.length;
                        document.getElementById('criticalCount').classList.remove('hidden');
                    }

                    if (data.regular && data.regular.length > 0) {
                        document.getElementById('notificationCount').textContent = data.regular.length;
                        document.getElementById('notificationCount').classList.remove('hidden');
                    }
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        }

        // Make functions globally available
        window.showToast = showToast;
        window.hideToast = hideToast;



// Add to your existing JavaScript
// Load notifications when panel opens
function loadNotifications() {
    fetch('/doctor/notifications')
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('notificationsList');
            list.innerHTML = '';

            if (data.length === 0) {
                list.innerHTML = '<div class="p-4 text-center text-gray-500">No new notifications</div>';
                return;
            }

            data.forEach(notification => {
                const notifElement = document.createElement('div');
                notifElement.className = `p-3 border-b border-gray-200 hover:bg-gray-50 cursor-pointer ${notification.read_at ? '' : 'bg-blue-50'}`;
                notifElement.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <div class="status-${notification.data.status || 'normal'}"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${notification.data.patient_name || 'Patient'}</p>
                            <p class="text-xs text-gray-600">${notification.data.message}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                ${new Date(notification.created_at).toLocaleString()}
                                ${notification.read_at ? '' : '<span class="ml-2 inline-block w-2 h-2 rounded-full bg-blue-500"></span>'}
                            </p>
                        </div>
                    </div>
                `;
                notifElement.onclick = () => {
                    markAsRead(notification.id);
                    if (notification.data.url) {
                        window.location.href = notification.data.url;
                    }
                };
                list.appendChild(notifElement);
            });
        });
}

// Mark notification as read
function markAsRead(notificationId) {
    fetch(`/doctor/notifications/${notificationId}/mark-as-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json'
        }
    }).then(() => {
        updateNotificationCount();
    });
}

// Mark all as read
function markAllAsRead() {
    fetch('/doctor/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': window.csrfToken,
            'Content-Type': 'application/json'
        }
    }).then(() => {
        loadNotifications();
        updateNotificationCount();
    });
}

// Update notification count badge
function updateNotificationCount() {
    fetch('/doctor/notifications/unread-count')
        .then(response => response.json())
        .then(count => {
            const badge = document.getElementById('notificationCount');
            if (count > 0) {
                badge.textContent = count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        });
}

// Call this when notifications panel is opened
function toggleNotifications() {
    const panel = document.getElementById('notificationsPanel');
    panel.classList.toggle('hidden');

    if (!panel.classList.contains('hidden')) {
        loadNotifications();
    }
}
// Call this on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
});
    </script>

    @stack('scripts')
</body>

<script>
    // Toggle notification dropdown
    function toggleNotifications() {
        const dropdown = document.getElementById('notificationDropdown');
        dropdown.classList.toggle('hidden');

        // Load notifications if dropdown is shown
        if (!dropdown.classList.contains('hidden')) {
            loadNotifications();
        }
    }

    // Load notifications via AJAX
    function loadNotifications() {
        fetch('/doctor/notifications/latest')
            .then(response => response.json())
            .then(data => {
                const list = document.getElementById('notificationList');
                list.innerHTML = '';

                data.forEach(notification => {
                    list.innerHTML += `
                        <div class="p-3 border-b border-gray-100 hover:bg-gray-50" data-notification-id="${notification.id}">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 pt-0.5">
                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">${notification.data.patient_name || 'System'}</p>
                                    <p class="text-sm text-gray-500">${notification.data.message}</p>
                                    <p class="text-xs text-gray-400 mt-1">${new Date(notification.created_at).toLocaleTimeString()}</p>
                                </div>
                            </div>
                        </div>
                    `;
                });
            });
    }

    // Mark all as read
    function markAllAsRead() {
        fetch('/doctor/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        }).then(() => {
            updateNotificationCount();
            loadNotifications();
        });
    }

    // Update notification counter
    function updateNotificationCount() {
        fetch('/doctor/notifications/unread-count')
            .then(response => response.json())
            .then(count => {
                const counter = document.getElementById('notificationCounter');
                counter.textContent = count;
                counter.classList.toggle('hidden', count === 0);
            });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const dropdown = document.getElementById('notificationDropdown');
        const button = document.querySelector('[onclick="toggleNotifications()"]');

        if (!dropdown.contains(event.target) && !button.contains(event.target)) {
            dropdown.classList.add('hidden');
        }
    });

    // Initialize notification count on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateNotificationCount();

        // Enable Pusher for real-time updates (if configured)
        @if(config('broadcasting.default') === 'pusher')
            Echo.private('user.{{ auth()->id() }}')
                .notification((notification) => {
                    updateNotificationCount();
                    // Optionally prepend new notification to list
                });
        @endif
    });

// Toggle user dropdown
function toggleUserMenu() {
    const dropdown = document.getElementById('userDropdown');
    dropdown.classList.toggle('hidden');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const userMenu = document.getElementById('userMenu');
    const userDropdown = document.getElementById('userDropdown');
    const userButton = userMenu.querySelector('button');

    // Close if clicking outside both the button and dropdown
    if (!userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
        userDropdown.classList.add('hidden');
    }
});
</script>
</html>
