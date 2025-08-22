<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Error - Medical Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .medical-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            border: none;
            color: white;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 12px 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: #374151;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            padding: 12px 24px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-secondary:hover {
            background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%);
            transform: translateY(-1px);
        }
        .error-animation {
            animation: shake 0.5s ease-in-out;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 to-red-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 bg-gradient-to-br from-red-500 to-red-600 rounded-3xl flex items-center justify-center mb-8 error-animation">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">Access Error</h2>
                <p class="text-lg text-gray-600 mb-6">
                    {{ $message ?? 'An error occurred while trying to access patient data.' }}
                </p>
            </div>

            <div class="medical-card p-8">
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">What can you do?</h3>
                    <div class="text-left space-y-4 text-gray-700">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium">Try Again</p>
                                <p class="text-xs text-gray-600">Refresh the page or try accessing the link again</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium">Check Your Link</p>
                                <p class="text-xs text-gray-600">Ensure you're using the complete link provided by the patient</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-purple-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium">Contact Patient</p>
                                <p class="text-xs text-gray-600">Ask the patient to verify the link and verification code</p>
                            </div>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-orange-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-sm font-medium">Verify Access Code</p>
                                <p class="text-xs text-gray-600">Ensure you have the correct 6-character verification code</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="medical-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Common Issues</h3>
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        <div>
                            <p class="font-medium text-red-700">Invalid Access Link</p>
                            <p>The link provided may be incorrect or incomplete</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        <div>
                            <p class="font-medium text-red-700">Expired Access</p>
                            <p>The temporary access link may have expired (7-day limit)</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        <div>
                            <p class="font-medium text-red-700">Revoked Access</p>
                            <p>The patient may have revoked access permissions</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        <div>
                            <p class="font-medium text-red-700">Verification Code Issue</p>
                            <p>The verification code might be incorrect or missing</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="w-2 h-2 bg-red-400 rounded-full mt-2 mr-3 flex-shrink-0"></span>
                        <div>
                            <p class="font-medium text-red-700">System Issue</p>
                            <p>There might be a temporary system issue</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="medical-card p-6 bg-blue-50 border border-blue-200">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-800">Need Help?</h4>
                        <p class="text-xs text-blue-700 mt-1">
                            Contact the patient who provided this link for assistance with accessing their medical data.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex space-x-4">
                <button onclick="window.location.reload()" 
                        class="flex-1 btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Try Again
                </button>
                <button onclick="window.history.back()" 
                        class="flex-1 btn-secondary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Go Back
                </button>
            </div>

            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Medical Monitor - Secure Patient Data Access<br>
                    All access attempts are logged and monitored for security
                </p>
            </div>
        </div>
    </div>

    <script>
        // Add some interactive feedback
        document.addEventListener('DOMContentLoaded', function() {
            // Add pulse animation to error icon after initial load
            setTimeout(() => {
                const errorIcon = document.querySelector('.error-animation');
                if (errorIcon) {
                    errorIcon.style.animation = 'pulse 2s infinite';
                }
            }, 1000);

            // Add click tracking for debugging (optional)
            document.querySelectorAll('button').forEach(button => {
                button.addEventListener('click', function() {
                    console.log('Button clicked:', this.textContent.trim());
                });
            });
        });
    </script>
</body>
</html>