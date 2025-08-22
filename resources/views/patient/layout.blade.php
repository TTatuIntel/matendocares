<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Patient Portal') - Medical Monitor</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
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

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(107, 114, 128, 0.4);
        }

        .form-input {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            transition: all 0.3s ease;
            width: 100%;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            ring: 2px;
            ring-color: rgba(59, 130, 246, 0.2);
            background: rgba(255, 255, 255, 1);
        }

        .status-normal {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .pulse-glow {
            animation: pulseGlow 2s infinite;
        }

        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.3); }
            50% { box-shadow: 0 0 30px rgba(59, 130, 246, 0.5); }
        }

        /* Loading spinner */
        .spinner {
            border: 2px solid #f3f4f6;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Toast notifications */
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

        .toast-info {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }

        .toast-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }

        /* Navigation active states */
        .nav-link {
            color: #6b7280;
            transition: all 0.3s ease;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 500;
        }

        .nav-link:hover {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
        }

        .nav-link.active {
            color: #3b82f6;
            background: rgba(59, 130, 246, 0.1);
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.2);
        }

        /* Mobile menu styles */
        .mobile-menu {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .mobile-menu.open {
            transform: translateX(0);
        }

        /* Stat card styles */
        .stat-card {
            padding: 1.5rem;
            border-radius: 1.5rem;
            border: 2px solid;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .vital-indicator {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

/* ---- Unified typography based on vitals ---- */
:root{
  --app-text-base: 15px;
  --app-text-base-sm: 14px;
  --app-text-base-xs: 13px;
  --app-radius: 0.75rem;
}
html { font-size: var(--app-text-base); }
@media (max-width: 640px){
  html { font-size: var(--app-text-base-sm); }
}
@media (max-width: 400px){
  html { font-size: var(--app-text-base-xs); }
}
/* scale headings slightly smaller than before for compactness */
h1 { font-weight: 800; font-size: 1.4rem; }
h2 { font-weight: 700; font-size: 1.15rem; }
h3 { font-weight: 700; font-size: 1rem; }
p, li, label, input, button, a, span, small { line-height: 1.35; }

/* compact cards like vitals */
.medical-card, .glass-card { border-radius: var(--app-radius); padding: 0.75rem; }
@media (min-width: 640px){ .medical-card, .glass-card { padding: 1rem; }}

/* buttons keep style but slightly smaller */
.btn-primary, .btn-secondary, .btn-danger, .btn-warning {
  padding: .5rem .9rem;
  border-radius: .65rem;
  font-size: .9rem;
}
.btn-primary .w-4, .btn-secondary .w-4 { width: 0.9rem; height: 0.9rem; }

/* inputs compact */
input[type="text"], input[type="email"], input[type="search"], input[type="date"], select, textarea {
  padding: .5rem .6rem;
  border-radius: .6rem;
  font-size: .95rem;
}

/* tables compact */
table { font-size: .95rem; }
table th, table td { padding: .5rem .6rem; }

/* reduce overall container width slightly for neatness */
.max-w-6xl { max-width: 64rem; } /* ~1024px */
@media (max-width: 1024px){ .max-w-6xl { max-width: 95vw; }}

/* responsive text helpers */
.text-base { font-size: 1rem; }
.text-sm { font-size: .9rem; }
.text-xs { font-size: .8rem; }

/* Dark mode base */
.dark body {
  background: linear-gradient(135deg, #0b1020 0%, #0d1324 50%, #0b1a1a 100%);
}
.dark .medical-card, .dark .glass-card {
  background: rgba(17, 24, 39, 0.85);
  border-color: rgba(255,255,255,.06);
}
/* Share link list compact */
.share-links { font-size: .85rem; }
.share-links input { font-size: .85rem; padding: .4rem .5rem; }

</style>

    @stack('styles')

<script>
// Respect OS setting on first load
(function(){
  const saved = localStorage.getItem('theme');
  const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  if(saved === 'dark' || (!saved && prefersDark)){
    document.documentElement.classList.add('dark');
  }
})();

function toggleDarkMode(){
  const el = document.documentElement;
  el.classList.toggle('dark');
  localStorage.setItem('theme', el.classList.contains('dark') ? 'dark' : 'light');
}
</script>

</head>
<body>
<button id="themeToggle" onclick="toggleDarkMode()" class="fixed bottom-4 right-4 z-50 px-3 py-2 rounded-lg bg-gray-900 text-white/90 dark:bg-gray-100 dark:text-gray-900 shadow">🌓</button>

    <!-- Navigation -->
    <nav class="medical-card border-b border-blue-100 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">

            <div class="flex items-center space-x-3">
                <img src="{{ asset('images/matelogo4.png') }}" alt="Mate Logo" class="h-10 w-auto">

            </div>


                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('patient.dashboard') }}"
                       class="nav-link {{ request()->routeIs('patient.dashboard') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2zm16 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V7z"></path>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('patient.vitals.index') }}"
                       class="nav-link {{ request()->routeIs('patient.vitals.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                        </svg>
                        Vitals
                    </a>
                    <a href="{{ route('patient.documents.index') }}"
                       class="nav-link {{ request()->routeIs('patient.documents.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Documents
                    </a>
                    <a href="{{ route('patient.appointments.index') }}"
                       class="nav-link {{ request()->routeIs('patient.appointments.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                        </svg>
                        Appointments
                    </a>
                    <a href="{{ route('patient.medications.index') }}"
                       class="nav-link {{ request()->routeIs('patient.medications.*') ? 'active' : '' }}">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                        </svg>
                        Medications
                    </a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
<!-- Notifications Button -->


<button class="text-gray-500 hover:text-gray-700 relative" onclick="toggleNotifications()" id="notificationsButton">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4.07 13H2.05L2 12l.05-1H4.07a8.003 8.003 0 010 2z"></path>
    </svg>
    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center animate-pulse" id="notificationCounter" style="display: none;">
        0
    </span>
</button>


                    <!-- User Profile -->
                    <div class="relative" id="userMenu">
                        <button class="flex items-center text-gray-700 hover:text-blue-600 font-medium" onclick="toggleUserMenu()">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center text-white font-bold mr-2">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <span class="hidden sm:inline">{{ auth()->user()->name ?? 'User' }}</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div id="userDropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg z-50 border border-gray-200">
                            <div class="py-2">
<a href="{{ route('patient.profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-t-xl">
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
                                    Account Settings
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

                    <!-- Mobile Menu Button -->
                    <button class="md:hidden text-gray-500 hover:text-gray-700" onclick="toggleMobileMenu()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobileMenu" class="md:hidden mobile-menu fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-xl font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent">
                        🏥 Medical Monitor
                    </div>
                    <button onclick="toggleMobileMenu()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <nav class="p-6 space-y-2">
                <a href="{{ route('patient.dashboard') }}" class="nav-link {{ request()->routeIs('patient.dashboard') ? 'active' : '' }} block">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v2zm16 0V9a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2h14a2 2 0 002-2V7z"></path>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('patient.vitals.index') }}" class="nav-link {{ request()->routeIs('patient.vitals.*') ? 'active' : '' }} block">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                    </svg>
                    Vitals
                </a>
                <a href="{{ route('patient.documents.index') }}" class="nav-link {{ request()->routeIs('patient.documents.*') ? 'active' : '' }} block">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Documents
                </a>
                <a href="{{ route('patient.appointments.index') }}" class="nav-link {{ request()->routeIs('patient.appointments.*') ? 'active' : '' }} block">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2-2v16a2 2 0 002 2z"></path>
                    </svg>
                    Appointments
                </a>
                <a href="{{ route('patient.medications.index') }}" class="nav-link {{ request()->routeIs('patient.medications.*') ? 'active' : '' }} block">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                    Medications
                </a>
            </nav>
        </div>

        <!-- Mobile Menu Overlay -->
        <div id="mobileMenuOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40" onclick="toggleMobileMenu()"></div>
    </nav>

    <!-- Main Content -->
    <main class="py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="medical-card border-t border-blue-100 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-600">
                <p>&copy; 2024 Medical Monitor. All rights reserved.</p>
                <p class="mt-2 text-sm">Your health data is secure and private.</p>
            </div>
        </div>
    </footer>

    <!-- Toast Container -->
    <div id="toast-container"></div>

<!-- Notifications Panel -->
<!-- Notifications Panel -->
<div id="notificationsPanel" class="hidden fixed top-16 right-4 w-80 bg-white rounded-xl shadow-lg z-50 border border-gray-200">
    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="font-bold text-gray-900">Notifications</h3>
        <button onclick="markAllAsReadVisually()" class="text-sm text-blue-600 hover:text-blue-800">Mark all as read</button>
    </div>
    <div class="max-h-96 overflow-y-auto" id="notificationsContainer">
        @forelse(auth()->user()->appointmentNotifications()->latest()->take(10)->get() as $notification)
        <div class="notification-item flex items-start space-x-3 p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer {{ is_null($notification->read_at) ? 'bg-blue-50 unread' : 'read' }}"
             data-notification-id="{{ $notification->id }}"
             onclick="handleNotificationClick(this, '{{ $notification->id }}')">
            <div class="notification-dot w-2 h-2 {{ is_null($notification->read_at) ? 'bg-blue-500' : 'bg-gray-300' }} rounded-full mt-2"></div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">
                    Appointment {{ ucfirst($notification->type) }}
                </p>
                <p class="text-xs text-gray-600">{{ $notification->message }}</p>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $notification->created_at->diffForHumans() }}
                    @if($notification->data['is_telemedicine'] ?? false)
                    • <span class="text-blue-600">Telemedicine</span>
                    @endif
                </p>
            </div>
        </div>
        @empty
        <div class="p-4 text-center text-gray-500" id="emptyNotifications">
            No notifications found
        </div>
        @endforelse
    </div>
    <div class="p-3 border-t border-gray-200 text-center">
        <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View all notifications</a>
    </div>
</div>

<script>
// Store read notifications in memory (avoiding localStorage)
let readNotifications = [];
let lastNotificationCheck = Date.now();
let notificationCheckInterval;

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    initializeNotificationSystem();
    startNotificationPolling();
});

function initializeNotificationSystem() {
    // Update counter on initial load
    updateNotificationCounter();
}

function startNotificationPolling() {
    // Check for new notifications every 30 seconds
    notificationCheckInterval = setInterval(checkForNewNotifications, 30000);
}

function checkForNewNotifications() {
    // Make an AJAX request to check for new notifications
    fetch('/api/check-new-notifications', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.hasNew) {
            // Update the notification counter
            updateNotificationCounterFromServer(data.unreadCount);

            // Optionally refresh the notifications panel if it's open
            if (!document.getElementById('notificationsPanel').classList.contains('hidden')) {
                refreshNotificationsPanel();
            }

            // Show a subtle notification indicator
            showNewNotificationIndicator();
        }
    })
    .catch(error => {
        console.error('Error checking notifications:', error);
    });
}

function showNewNotificationIndicator() {
    const counter = document.getElementById('notificationCounter');
    if (counter.style.display !== 'none') {
        // Add a subtle pulse animation to indicate new notifications
        counter.classList.add('animate-pulse');

        // Show a brief flash effect
        const button = document.getElementById('notificationsButton');
        button.classList.add('animate-bounce');
        setTimeout(() => {
            button.classList.remove('animate-bounce');
        }, 1000);
    }
}

function refreshNotificationsPanel() {
    // Make an AJAX request to refresh the notifications panel content
    fetch('/api/notifications-panel', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('notificationsContainer').innerHTML = html;
        updateNotificationCounter();
    })
    .catch(error => {
        console.error('Error refreshing notifications:', error);
    });
}

function updateNotificationCounterFromServer(count) {
    const counter = document.getElementById('notificationCounter');

    if (count > 0) {
        counter.textContent = count;
        counter.style.display = 'flex';
        counter.classList.add('animate-pulse');
    } else {
        counter.style.display = 'none';
        counter.classList.remove('animate-pulse');
    }
}

function handleNotificationClick(element, notificationId) {
    // Only process if notification is unread
    if (element.classList.contains('unread')) {
        // Mark as read in UI
        markAsReadInUI(element);

        // Save to memory
        addReadNotification(notificationId);

        // Send read status to server
        markNotificationAsRead(notificationId);
    }

    // Update counter after marking as read
    updateNotificationCounter();

    // Close panel
    document.getElementById('notificationsPanel').classList.add('hidden');

    // Redirect after slight delay
    setTimeout(() => {
        window.location.href = 'http://127.0.0.1:8000/patient/appointments';
    }, 150);
}

function markNotificationAsRead(notificationId) {
    fetch(`/api/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsReadVisually() {
    // Get all unread notifications
    const unreadNotifications = document.querySelectorAll('.notification-item.unread');

    // Mark each as read
    unreadNotifications.forEach(item => {
        const notificationId = item.dataset.notificationId;
        markAsReadInUI(item);
        addReadNotification(notificationId);
        markNotificationAsRead(notificationId);
    });

    // Force counter update after all are marked
    setTimeout(updateNotificationCounter, 100);
}

function markAsReadInUI(element) {
    // Only mark as read if it's currently unread
    if (element.classList.contains('unread')) {
        element.classList.remove('bg-blue-50', 'unread');
        element.classList.add('read');
        const dot = element.querySelector('.notification-dot');
        if (dot) {
            dot.classList.remove('bg-blue-500');
            dot.classList.add('bg-gray-300');
        }

        // Force counter update after UI change
        setTimeout(updateNotificationCounter, 50);
    }
}

function updateNotificationCounter() {
    const counter = document.getElementById('notificationCounter');
    const unreadCount = document.querySelectorAll('.notification-item.unread').length;

    console.log('Updating counter - Unread count:', unreadCount); // Debug log

    if (unreadCount > 0) {
        counter.textContent = unreadCount;
        counter.style.display = 'flex';
        counter.classList.add('animate-pulse');
    } else {
        counter.style.display = 'none';
        counter.classList.remove('animate-pulse');
        counter.textContent = '0';
    }
}

function toggleNotifications() {
    const panel = document.getElementById('notificationsPanel');
    panel.classList.toggle('hidden');
    if (!panel.classList.contains('hidden')) {
        updateNotificationCounter();
        // Remove pulse animation when panel is opened
        document.getElementById('notificationCounter').classList.remove('animate-pulse');
    }
}

// Memory storage helpers (replacing localStorage)
function addReadNotification(notificationId) {
    if (!readNotifications.includes(notificationId)) {
        readNotifications.push(notificationId);
    }
}

// Close panel when clicking outside
document.addEventListener('click', function(event) {
    const panel = document.getElementById('notificationsPanel');
    const button = document.getElementById('notificationsButton');

    if (!panel.contains(event.target) && !button.contains(event.target)) {
        panel.classList.add('hidden');
    }
});

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
    if (notificationCheckInterval) {
        clearInterval(notificationCheckInterval);
    }
});
</script>

    <!-- Global JavaScript -->
    <script>
        // CSRF Token Setup
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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

        // Loading State Helpers
        function showLoading(button) {
            if (!button) return;
            button.disabled = true;
            const originalContent = button.innerHTML;
            button.dataset.originalContent = originalContent;
            button.innerHTML = `
                <div class="spinner mr-2"></div>
                Loading...
            `;
        }

        function hideLoading(button) {
            if (!button) return;
            button.disabled = false;
            if (button.dataset.originalContent) {
                button.innerHTML = button.dataset.originalContent;
            }
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

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const overlay = document.getElementById('mobileMenuOverlay');

            menu.classList.toggle('open');
            overlay.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const userMenu = document.getElementById('userMenu');
            const notificationsPanel = document.getElementById('notificationsPanel');

            if (!userMenu.contains(event.target)) {
                document.getElementById('userDropdown').classList.add('hidden');
            }

            if (!event.target.closest('#notificationsPanel') && !event.target.closest('button[onclick="toggleNotifications()"]')) {
                notificationsPanel.classList.add('hidden');
            }
        });

        // Global Error Handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            showToast('An unexpected error occurred. Please try again.', 'error');
        });

        // AJAX Setup
        function setupAjax() {
            // Set up CSRF token for all AJAX requests
            if (window.jQuery) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': window.csrfToken
                    }
                });
            }

            // Fetch API wrapper with CSRF
            window.fetchWithCSRF = function(url, options = {}) {
                options.headers = {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    ...options.headers
                };
                return fetch(url, options);
            };
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            setupAjax();

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

        // Make functions globally available
        window.showToast = showToast;
        window.hideToast = hideToast;
        window.showLoading = showLoading;
        window.hideLoading = hideLoading;
    </script>

    @stack('scripts')
</body>
</html>
