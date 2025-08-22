{{-- view/expired.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Expired - Medical Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
            <!-- Icon -->
            <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <!-- Content -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Access Link Expired</h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message ?? 'This temporary access link has expired or is no longer valid. Please request a new link from the patient.' }}
            </p>

            <!-- Security Notice -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-left">
                        <h3 class="font-semibold text-blue-800 text-sm">Security Information</h3>
                        <p class="text-blue-700 text-sm mt-1">
                            Temporary access links expire automatically for security purposes. Each link can only be used for a limited time.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button onclick="window.close()" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                    Close Window
                </button>
                
                <p class="text-sm text-gray-500">
                    To get a new access link, please contact the patient directly.
                </p>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-400">
                    🏥 Medical Monitor - Secure Health Data Access
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-close after 10 seconds if opened in popup
        if (window.opener) {
            setTimeout(() => {
                window.close();
            }, 10000);
        }
    </script>
</body>
</html>

{{-- temp-access/error.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Error - Medical Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8 text-center">
            <!-- Icon -->
            <div class="w-20 h-20 bg-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0l-7.918 8.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>

            <!-- Content -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Access Error</h1>
            
            <p class="text-gray-600 mb-6">
                {{ $message ?? 'There was an error accessing the health data. This could be due to a network issue or the link may no longer be valid.' }}
            </p>

            <!-- Troubleshooting -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 text-left">
                <h3 class="font-semibold text-gray-800 text-sm mb-2">Troubleshooting Steps:</h3>
                <ul class="text-sm text-gray-600 space-y-1">
                    <li>• Check your internet connection</li>
                    <li>• Verify the link was copied correctly</li>
                    <li>• Ensure the link hasn't expired</li>
                    <li>• Try refreshing the page</li>
                </ul>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                <button onclick="window.location.reload()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Try Again
                </button>
                
                <button onclick="window.close()" class="w-full bg-gray-600 hover:bg-gray-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                    Close Window
                </button>
            </div>

            <!-- Contact Info -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>Still having issues?</strong><br>
                    Please contact the patient for a new access link.
                </p>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-400">
                    🏥 Medical Monitor - Secure Health Data Access
                </p>
            </div>
        </div>
    </div>
</body>
</html>