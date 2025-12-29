<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quote #{{ $quote->quote_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .slide-up { animation: slideUp 0.4s ease-out; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="bg-white shadow-sm rounded-t-lg px-8 py-6 border-b border-gray-200">
                <div class="flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Quote #{{ $quote->quote_number }}</h1>
                        <p class="mt-1 text-sm text-gray-500">For {{ $quote->company->name }}</p>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Valid Until</div>
                        <div class="text-lg font-semibold text-gray-900">{{ $quote->valid_until->format('M d, Y') }}</div>
                    </div>
                </div>
            </div>

            <!-- Status Banner -->
            @if($quote->is_accepted)
                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                This quote was accepted on {{ $quote->accepted_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($quote->valid_until < now())
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                This quote expired on {{ $quote->valid_until->format('M d, Y') }}. Please contact us for an updated quote.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Line Items -->
            <div class="bg-white shadow-sm px-8 py-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Services</h2>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($quote->lineItems as $item)
                            <tr>
                                <td class="px-4 py-4 whitespace-normal text-sm text-gray-900">
                                    <div class="font-medium">{{ $item->description }}</div>
                                    @if($item->notes)
                                        <div class="text-gray-500 text-xs mt-1">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="px-4 py-4 text-right text-sm font-semibold text-gray-900">Total:</td>
                            <td class="px-4 py-4 text-right text-lg font-bold text-gray-900">${{ number_format($quote->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- Accept Section -->
            @if(!$quote->is_accepted && $quote->valid_until >= now())
                <div class="bg-white shadow-sm rounded-b-lg px-8 py-6 border-t border-gray-200 slide-up">
                    <form action="{{ route('billing.public.quote.accept', $quote->token) }}" method="POST" 
                          x-data="{ accepting: false, termsAccepted: false, formValid: false }" 
                          @submit="accepting = true"
                          x-init="$watch('termsAccepted', value => formValid = value)">
                        @csrf
                        
                        <div class="mb-6">
                            <label class="flex items-start cursor-pointer group">
                                <input type="checkbox" name="terms_accepted" required 
                                       x-model="termsAccepted"
                                       class="mt-1 h-4 w-4 text-primary-600 border-gray-300 rounded focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition-all">
                                <span class="ml-2 text-sm text-gray-700">
                                    I accept the terms and conditions outlined in this quote and authorize the commencement of services.
                                </span>
                            </label>
                        </div>

                        <div class="mb-6">
                            <label for="accepted_by_name" class="block text-sm font-medium text-gray-700 mb-2">Your Name *</label>
                            <input type="text" name="accepted_by_name" id="accepted_by_name" required 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                        </div>

                        <div class="mb-6">
                            <label for="accepted_by_email" class="block text-sm font-medium text-gray-700 mb-2">Your Email *</label>
                            <input type="email" name="accepted_by_email" id="accepted_by_email" required 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500">
                        </div>

                        <div class="mb-6">
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                            <textarea name="notes" id="notes" rows="3" 
                                   class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="Any additional comments or instructions..."></textarea>
                        </div>

                        <div class="flex gap-4">
                            <button type="submit" 
                                    @click="accepting = true"
                                    :disabled="accepting"
                                    class="flex-1 bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-3 px-6 rounded-lg transition-colors duration-150 flex items-center justify-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                <span x-show="!accepting">Accept Quote & Proceed</span>
                                <span x-show="accepting" x-cloak>Processing...</span>
                            </button>
                            
                            <button type="button"
                                    @click="$dispatch('open-reject-modal')"
                                    class="bg-red-100 hover:bg-red-200 text-red-700 font-semibold py-3 px-6 rounded-lg transition-colors duration-150 flex items-center justify-center">
                                <i class="fas fa-times-circle mr-2"></i>
                                Reject
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-white shadow-sm rounded-b-lg px-8 py-6 border-t border-gray-200 text-center text-gray-500">
                    <i class="fas fa-lock text-2xl mb-2"></i>
                    <p>This quote can no longer be accepted online. Please contact us for assistance.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-data="{ open: false }" 
         @open-reject-modal.window="open = true" 
         x-show="open" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="open = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('billing.public.quote.reject', $quote->token) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-exclamation-triangle text-red-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Reject Quote</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">
                                        Please let us know why you are rejecting this quote so we can improve our offer.
                                    </p>
                                    
                                    <div class="mb-4">
                                        <label for="rejected_by_name" class="block text-sm font-medium text-gray-700 mb-1">Your Name *</label>
                                        <input type="text" name="rejected_by_name" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="rejected_by_email" class="block text-sm font-medium text-gray-700 mb-1">Your Email *</label>
                                        <input type="email" name="rejected_by_email" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500">
                                    </div>
                                    
                                    <div class="mb-4">
                                        <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason for Rejection *</label>
                                        <textarea name="rejection_reason" required rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Reject Quote
                        </button>
                        <button type="button" @click="open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="//unpkg.com/alpinejs" defer></script>
</body>
</html>
