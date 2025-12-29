<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Accepted - Thank You!</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Success Icon -->
                <div class="bg-green-500 px-6 py-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                        <i class="fas fa-check text-green-500 text-3xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Quote Accepted!</h1>
                </div>

                <!-- Content -->
                <div class="px-6 py-8">
                    <div class="text-center mb-6">
                        <p class="text-lg text-gray-700 mb-2">Thank you for accepting our quote.</p>
                        <p class="text-gray-600">Quote #{{ $quote->quote_number }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <h2 class="font-semibold text-gray-900 mb-3">What happens next?</h2>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                                <span>Our team has been notified of your acceptance</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                                <span>You'll receive a confirmation email shortly</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                                <span>We'll begin preparing your services</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-0.5 mr-2"></i>
                                <span>An invoice will be sent within 1 business day</span>
                            </li>
                        </ul>
                    </div>

                    <div class="border-t border-gray-200 pt-4">
                        <p class="text-sm text-gray-600 text-center">
                            Questions? Contact us at 
                            <a href="mailto:{{ config('mail.from.address') }}" class="text-primary-600 hover:text-primary-700">
                                {{ config('mail.from.address') }}
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
