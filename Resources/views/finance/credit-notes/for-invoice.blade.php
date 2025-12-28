<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Credit Note') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Create Credit Note</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Issue a credit note for Invoice #{{ $invoice->id ?? 'N/A' }}
            </p>
        </div>

        <!-- State Indicator -->
        <x-billing::state-indicator state="idle" />

        <!-- Invoice Details -->
        @if(isset($invoice))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Invoice Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Customer</div>
                    <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $invoice->company->name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Invoice Date</div>
                    <div class="text-gray-900 dark:text-gray-100">{{ $invoice->created_at?->format('M d, Y') ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Invoice Amount</div>
                    <div class="text-gray-900 dark:text-gray-100 font-medium">${{ number_format($invoice->total / 100, 2) }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Amount Paid</div>
                    <div class="text-gray-900 dark:text-gray-100">${{ number_format($invoice->amount_paid / 100, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Credit Note Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form method="POST" action="{{ route('billing.finance.credit-notes.store', $invoice) }}">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Credit Amount *
                        </label>
                        <div class="mt-1 relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">$</span>
                            </div>
                            <input 
                                type="number" 
                                name="amount" 
                                id="amount" 
                                step="0.01"
                                max="{{ $invoice->total / 100 }}"
                                required
                                class="focus:ring-2 focus:ring-primary-500 focus:border-primary-500 block w-full pl-7 pr-12 py-3 text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg transition-colors duration-150"
                                placeholder="0.00"
                                value="{{ old('amount') }}">
                        </div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Maximum: ${{ number_format($invoice->total / 100, 2) }}
                        </p>
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reason *
                        </label>
                        <select 
                            name="reason" 
                            id="reason" 
                            required
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                            <option value="">Select a reason...</option>
                            <option value="billing_error" {{ old('reason') == 'billing_error' ? 'selected' : '' }}>Billing Error</option>
                            <option value="service_issue" {{ old('reason') == 'service_issue' ? 'selected' : '' }}>Service Issue</option>
                            <option value="customer_complaint" {{ old('reason') == 'customer_complaint' ? 'selected' : '' }}>Customer Complaint</option>
                            <option value="refund_request" {{ old('reason') == 'refund_request' ? 'selected' : '' }}>Refund Request</option>
                            <option value="duplicate_charge" {{ old('reason') == 'duplicate_charge' ? 'selected' : '' }}>Duplicate Charge</option>
                            <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('reason')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Internal Notes
                        </label>
                        <textarea 
                            name="notes" 
                            id="notes" 
                            rows="4"
                            class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md"
                            placeholder="Add any additional details...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('billing.finance.credit-notes.index') }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Issue Credit Note
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-4">
            <p class="text-yellow-800 dark:text-yellow-200">No invoice specified. Please select an invoice first.</p>
        </div>
        @endif
    </div>
</x-app-layout>
