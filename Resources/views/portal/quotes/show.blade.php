<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <a href="{{ route('billing.portal.dashboard', $company->id) }}" class="text-gray-500 hover:text-gray-700">
                    {{ __('Client Portal') }}
                </a>
                <span class="text-gray-400 mx-2">/</span>
                {{ __('Quote #') }}{{ $quote->quote_number }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Banner -->
            @if($quote->is_accepted)
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                This quote was accepted on {{ $quote->accepted_at->format('M d, Y \a\t g:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($quote->status === 'rejected')
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                This quote was rejected.
                            </p>
                        </div>
                    </div>
                </div>
            @elseif($quote->valid_until < now())
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-red-700">
                                This quote expired on {{ $quote->valid_until->format('M d, Y') }}.
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <div class="bg-white shadow overflow-hidden sm:rounded-lg" x-data="{ 
                billingFrequency: '{{ $quote->billing_frequency }}',
                items: {{ json_encode($quote->lineItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'quantity' => $item->quantity,
                        'unit_price_monthly' => $item->unit_price_monthly ?? $item->unit_price,
                        'unit_price_annually' => $item->unit_price_annually ?? ($item->unit_price * 12),
                    ];
                })) }}
            }">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-start">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Quote Details
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Valid until {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'N/A' }}
                        </p>
                    </div>
                    <div class="text-right">
                        <!-- Billing Frequency Toggle -->
                        @if($quote->status === 'sent')
                        <div class="mb-2 inline-flex rounded-md shadow-sm" role="group">
                            <button type="button" 
                                @click="billingFrequency = 'monthly'"
                                :class="{'bg-indigo-600 text-white': billingFrequency === 'monthly', 'bg-white text-gray-700 hover:bg-gray-50': billingFrequency !== 'monthly'}"
                                class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-l-lg focus:z-10 focus:ring-2 focus:ring-indigo-500 focus:text-indigo-700">
                                Monthly
                            </button>
                            <button type="button" 
                                @click="billingFrequency = 'annually'"
                                :class="{'bg-indigo-600 text-white': billingFrequency === 'annually', 'bg-white text-gray-700 hover:bg-gray-50': billingFrequency !== 'annually'}"
                                class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-r-lg focus:z-10 focus:ring-2 focus:ring-indigo-500 focus:text-indigo-700">
                                Annually
                            </button>
                        </div>
                        @endif
                        
                        <div class="text-2xl font-bold text-gray-900" x-text="'$' + items.reduce((acc, item) => {
                            let price = billingFrequency === 'monthly' ? item.unit_price_monthly : item.unit_price_annually;
                            return acc + (price * item.quantity);
                        }, 0).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                            ${{ number_format($quote->total, 2) }}
                        </div>
                        <div class="text-sm text-gray-500" x-text="billingFrequency === 'monthly' ? 'per month' : 'per year'">
                            {{ $quote->billing_frequency === 'monthly' ? 'per month' : 'per year' }}
                        </div>
                    </div>
                </div>
                
                <div class="border-t border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Qty
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Unit Price
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($quote->lineItems as $index => $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-normal text-sm font-medium text-gray-900">
                                    {{ $item->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right" x-text="'$' + (billingFrequency === 'monthly' ? {{ $item->unit_price_monthly ?? $item->unit_price }} : {{ $item->unit_price_annually ?? ($item->unit_price * 12) }}).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                    ${{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium" x-text="'$' + ((billingFrequency === 'monthly' ? {{ $item->unit_price_monthly ?? $item->unit_price }} : {{ $item->unit_price_annually ?? ($item->unit_price * 12) }}) * {{ $item->quantity }}).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                    ${{ number_format($item->subtotal, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($quote->notes)
                <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Notes</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $quote->notes }}</dd>
                </div>
                @endif
            </div>

            <!-- Actions -->
            @if($quote->status === 'sent' && $quote->valid_until >= now())
                <div class="mt-8 bg-white shadow sm:rounded-lg p-6" x-data="{ showReject: false }">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Action Required</h3>
                    
                    <div x-show="!showReject">
                        <form action="{{ route('billing.portal.quotes.accept', ['company' => $company->id, 'id' => $quote->id]) }}" method="POST">
                            @csrf
                            <!-- Hidden input for billing frequency -->
                            <input type="hidden" name="billing_frequency" :value="billingFrequency">
                            
                            <div class="mb-4">
                                <label class="flex items-start cursor-pointer">
                                    <input type="checkbox" name="terms_accepted" required class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">
                                        I accept the terms and conditions outlined in this quote and authorize the commencement of services.
                                    </span>
                                </label>
                            </div>
                            
                            <div class="mb-4">
                                <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes (Optional)</label>
                                <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                            </div>

                            <div class="flex gap-4">
                                <button type="submit" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    Accept Quote
                                </button>
                                <button type="button" @click="showReject = true" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Reject
                                </button>
                            </div>
                        </form>
                    </div>

                    <div x-show="showReject" x-cloak>
                        <form action="{{ route('billing.portal.quotes.reject', ['company' => $company->id, 'id' => $quote->id]) }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for Rejection</label>
                                <textarea name="rejection_reason" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Please let us know why..."></textarea>
                            </div>

                            <div class="flex gap-4">
                                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                    Confirm Rejection
                                </button>
                                <button type="button" @click="showReject = false" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
