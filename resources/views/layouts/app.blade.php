<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
       
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
       
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
       
        <!-- Enhanced Dashboard Styles -->
        <style>
            /* Smooth transitions for all elements */
            .smooth-transition {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            /* Card hover effects */
            .card-hover:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            }
            
            /* Premium button styles */
            .btn-primary {
                background: linear-gradient(135deg, #059669, #0d9488);
                border: 2px solid #065f46;
                color: white;
                transition: all 0.3s ease;
            }
            .btn-primary:hover {
                background: linear-gradient(135deg, #047857, #0f766e);
                border-color: #064e3b;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(5, 150, 105, 0.4);
            }
            
            .btn-secondary {
                background: linear-gradient(135deg, #1e40af, #3730a3);
                border: 2px solid #1e3a8a;
                color: white;
                transition: all 0.3s ease;
            }
            .btn-secondary:hover {
                background: linear-gradient(135deg, #1d4ed8, #4338ca);
                border-color: #1e3a8a;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
            }
            
            .btn-danger {
                background: linear-gradient(135deg, #dc2626, #b91c1c);
                border: 2px solid #991b1b;
                color: white;
                transition: all 0.3s ease;
            }
            .btn-danger:hover {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                border-color: #7f1d1d;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
            }
            
            .btn-ghost {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 2px solid rgba(255, 255, 255, 0.2);
                color: #374151;
                transition: all 0.3s ease;
            }
            .btn-ghost:hover {
                background: rgba(255, 255, 255, 0.2);
                border-color: rgba(255, 255, 255, 0.3);
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            }
            
            /* Enhanced table rows */
            .table-row:hover {
                background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
                transform: scale(1.001);
            }
            
            /* Modal backdrop with blur */
            .modal-backdrop {
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(4px);
            }
            
            /* Dashboard background gradient */
            .bg-dashboard {
                background: linear-gradient(135deg, #f8fafc 0%, #f0fdf4 25%, #eff6ff 75%, #f1f5f9 100%);
                min-height: 100vh;
            }
            
            /* Enhanced navigation styles */
            .nav-item {
                transition: all 0.3s ease;
                border-radius: 0.75rem;
                padding: 0.5rem 1rem;
                margin: 0.25rem 0;
            }
            .nav-item:hover {
                background: rgba(255, 255, 255, 0.1);
                transform: translateX(4px);
            }
            
            /* Card styles with glassmorphism */
            .glass-card {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.2);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            }
            
            /* Content sections with decorative elements */
            .section-header {
                position: relative;
                display: flex;
                align-items: center;
                margin-bottom: 2rem;
            }
            .section-header::before {
                content: '';
                width: 0.5rem;
                height: 2rem;
                background: linear-gradient(180deg, #059669, #1e40af);
                border-radius: 0.25rem;
                margin-right: 1rem;
            }
            
            /* Animated gradient borders */
            .gradient-border {
                position: relative;
                background: linear-gradient(135deg, #f8fafc, #ffffff);
                border-radius: 1rem;
                padding: 2px;
            }
            .gradient-border::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(135deg, #059669, #1e40af, #7c3aed);
                border-radius: 1rem;
                z-index: -1;
                animation: gradient-rotate 3s linear infinite;
            }
            
            @keyframes gradient-rotate {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            /* Status badges with improved styling */
            .status-badge {
                display: inline-flex;
                align-items: center;
                padding: 0.5rem 1rem;
                border-radius: 9999px;
                font-weight: 600;
                font-size: 0.875rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .status-pending {
                background: linear-gradient(135deg, #fbbf24, #f59e0b);
                color: #92400e;
            }
            
            .status-approved {
                background: linear-gradient(135deg, #10b981, #059669);
                color: #065f46;
            }
            
            .status-rejected {
                background: linear-gradient(135deg, #ef4444, #dc2626);
                color: #991b1b;
            }
            
            /* Enhanced form styling */
            .form-input {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(8px);
                border: 2px solid rgba(209, 213, 219, 0.3);
                border-radius: 0.75rem;
                padding: 0.75rem 1rem;
                transition: all 0.3s ease;
            }
            .form-input:focus {
                outline: none;
                border-color: #059669;
                box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
                background: rgba(255, 255, 255, 1);
            }
            
            /* Micro-animations for interactive elements */
            .bounce-in {
                animation: bounceIn 0.6s ease-out;
            }
            
            @keyframes bounceIn {
                0% { transform: scale(0.3); opacity: 0; }
                50% { transform: scale(1.05); }
                70% { transform: scale(0.9); }
                100% { transform: scale(1); opacity: 1; }
            }
            
            .fade-in {
                animation: fadeIn 0.8s ease-out;
            }
            
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            
            /* Enhanced scrollbar styling */
            ::-webkit-scrollbar {
                width: 8px;
            }
            ::-webkit-scrollbar-track {
                background: rgba(243, 244, 246, 0.5);
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb {
                background: linear-gradient(135deg, #059669, #1e40af);
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: linear-gradient(135deg, #047857, #1d4ed8);
            }
        </style>
       
        <!-- Additional page-specific styles -->
        @stack('styles')
    </head>
    <body class="font-sans antialiased bg-dashboard">
        <div class="min-h-screen">
            @include('layouts.navigation')
           
            <!-- Enhanced Page Heading -->
            @isset($header)
                <header class="glass-card shadow-lg border-b border-gray-200/50 smooth-transition">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div class="section-header fade-in">
                            {{ $header }}
                        </div>
                    </div>
                </header>
            @endisset
           
            <!-- Enhanced Page Content -->
            <main class="pb-12">
                <div class="fade-in">
                    {{ $slot }}
                </div>
            </main>
        </div>
       
        <!-- Enhanced JavaScript for interactions -->
        <script>
            // Add smooth scrolling behavior
            document.documentElement.style.scrollBehavior = 'smooth';
            
            // Add intersection observer for fade-in animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, observerOptions);
            
            // Observe all elements with animation classes
            document.addEventListener('DOMContentLoaded', function() {
                const animatedElements = document.querySelectorAll('.card-hover, .glass-card');
                animatedElements.forEach(el => observer.observe(el));
                
                // Add bounce-in animation to buttons on page load
                const buttons = document.querySelectorAll('button, .btn-primary, .btn-secondary, .btn-danger');
                buttons.forEach((btn, index) => {
                    setTimeout(() => {
                        btn.classList.add('bounce-in');
                    }, index * 100);
                });
            });
            
            // Enhanced form interactions
            document.addEventListener('DOMContentLoaded', function() {
                const inputs = document.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.classList.add('form-input');
                    
                    input.addEventListener('focus', function() {
                        this.parentElement?.classList.add('ring-2', 'ring-emerald-500/20');
                    });
                    
                    input.addEventListener('blur', function() {
                        this.parentElement?.classList.remove('ring-2', 'ring-emerald-500/20');
                    });
                });
            });
            
            // Add click ripple effect to buttons
            function addRippleEffect(button) {
                button.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.4s ease-out;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => ripple.remove(), 400);
                });
            }
            
            // Apply ripple effect to all buttons
            document.addEventListener('DOMContentLoaded', function() {
                const buttons = document.querySelectorAll('button');
                buttons.forEach(addRippleEffect);
            });
            
            // CSS for ripple animation
            const rippleCSS = `
                @keyframes ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            const style = document.createElement('style');
            style.textContent = rippleCSS;
            document.head.appendChild(style);
        </script>
       
        <!-- Additional page-specific scripts -->
        @stack('scripts')
    </body>
</html>