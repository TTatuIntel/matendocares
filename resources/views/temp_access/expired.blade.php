<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Expired - Medical Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .medical-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-red-50 to-orange-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-24 w-24 bg-gradient-to-br from-red-500 to-orange-600 rounded-3xl flex items-center justify-center mb-8">
                    <svg class="h-12 w-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h2 class="text-3xl font-extrabold text-gray-900 mb-4">Access Expired</h2>
                <p class="text-lg text-gray-600 mb-6">
                    This temporary access link has expired or is no longer valid.
                </p>
            </div>

            <div class="medical-card p-8">
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">What happened?</h3>
                    <div class="text-left space-y-3 text-gray-700">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">The temporary access link has reached its expiration time (7 days maximum)</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636"></path>
                            </svg>
                            <p class="text-sm">The patient may have revoked access to their medical data</p>
                        </div>
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">The link may have been used beyond its intended purpose</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="medical-card p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Need Access?</h3>
                <div class="space-y-3 text-sm text-gray-600">
                    <p>If you need to access this patient's medical data:</p>
                    <ul class="list-disc list-inside space-y-1 ml-4">
                        <li>Contact the patient directly to request a new access link</li>
                        <li>Ask the patient to generate a fresh temporary access link</li>
                        <li>Ensure you have the correct verification code from the patient</li>
                    </ul>
                </div>
            </div>

            <div class="medical-card p-6 bg-blue-50 border border-blue-200">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-800">Security Notice</h4>
                        <p class="text-xs text-blue-700 mt-1">
                            Temporary access links expire automatically to protect patient privacy and medical data security.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <p class="text-xs text-gray-500">
                    Medical Monitor - Secure Patient Data Access<br>
                    All access attempts are logged for security purposes
                </p>
            </div>
        </div>
    </div>
</body>
</html>