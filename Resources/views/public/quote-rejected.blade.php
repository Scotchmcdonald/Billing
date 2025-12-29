<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote Rejected</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <div class="bg-white shadow-lg rounded-lg overflow-hidden">
                <!-- Rejected Icon -->
                <div class="bg-red-500 px-6 py-8 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-full mb-4">
                        <i class="fas fa-times text-red-500 text-3xl"></i>
                    </div>
                    <h1 class="text-2xl font-bold text-white">Quote Rejected</h1>
                </div>

                <!-- Content -->
                <div class="px-6 py-8">
                    <div class="text-center mb-6">
                        <p class="text-lg text-gray-700 mb-2">You have rejected this quote.</p>
                        <p class="text-gray-600">Quote #{{ $quote->quote_number }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-4 mb-6">
                        <p class="text-sm text-gray-600 mb-2">
                            Thank you for your feedback. We have received your rejection reason:
                        </p>
                        <blockquote class="italic text-gray-700 border-l-4 border-red-300 pl-3 py-1 my-2">
                            "{{ $quote->notes ? explode('Rejection Reason: ', $quote->notes)[1] ?? 'No reason provided' : 'No reason provided' }}"
                        </blockquote>
                        <p class="text-sm text-gray-600 mt-4">
                            A representative will be in touch shortly to discuss how we can better meet your needs.
                        </p>
                    </div>

                    <div class="text-center">
                        <a href="mailto:{{ config('mail.from.address') }}" class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
