<x-guest-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="text-center mb-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900">Quote Request Received!</h1>
                        <p class="mt-2 text-gray-600">Reference Number: #{{ $quote->id }}</p>
                    </div>

                    <div class="max-w-3xl mx-auto">
                        <div class="bg-gray-50 rounded-lg p-8 mb-8">
                            <h2 class="text-xl font-semibold text-gray-800 mb-6">Quote Details</h2>
                            
                            <div class="space-y-4 mb-8">
                                @foreach($quote->lineItems as $item)
                                    <div class="flex justify-between items-center border-b border-gray-200 pb-4 last:border-0 last:pb-0">
                                        <div>
                                            <h3 class="font-medium text-gray-900">{{ $item->product->name }}</h3>
                                            <p class="text-sm text-gray-500">Quantity: {{ $item->quantity }}</p>
                                        </div>
                                        <div class="text-gray-900 font-medium">
                                            ${{ number_format($item->unit_price * $item->quantity, 2) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="flex justify-between items-center pt-6 border-t-2 border-gray-200">
                                <span class="text-lg font-bold text-gray-900">Total Estimated Monthly Cost</span>
                                <span class="text-2xl font-bold text-indigo-600">${{ number_format($quote->total, 2) }}</span>
                            </div>
                        </div>

                        <div class="text-center space-y-4">
                            <p class="text-gray-600">
                                Thank you, <strong>{{ $quote->prospect_name }}</strong>. We have sent a confirmation email to <strong>{{ $quote->prospect_email }}</strong>.
                            </p>
                            <p class="text-gray-600">
                                One of our MSP specialists will review your requirements and contact you within 24 hours to finalize your plan.
                            </p>
                            
                            <div class="pt-8">
                                <a href="{{ route('billing.public.quote.index') }}" class="text-indigo-600 hover:text-indigo-800 font-medium">
                                    &larr; Build Another Quote
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
