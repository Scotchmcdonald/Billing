<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Issue Credit Note</h2>

            @if(session('success'))
                <div class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded">
                    <p class="font-semibold">Please correct the following errors:</p>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('billing.finance.credit-notes.store', $invoice) }}">
                @csrf

                <!-- Invoice Information -->
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <span class="font-semibold text-gray-700">Invoice:</span>
                            <span class="text-gray-900">#{{ $invoice->invoice_number }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700">Company:</span>
                            <span class="text-gray-900">{{ $invoice->company->name }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-gray-700">Balance:</span>
                            <span class="text-gray-900 font-bold">${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label for="amount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Credit Amount <span class="text-danger-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                        <input 
                            type="number" 
                            name="amount" 
                            id="amount"
                            step="0.01"
                            min="0.01"
                            max="{{ number_format($invoice->total_amount - $invoice->paid_amount, 2, '.', '') }}"
                            value="{{ old('amount') }}"
                            class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            required
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500">
                        Maximum credit amount: ${{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}
                    </p>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-semibold text-gray-700 mb-2">
                        Reason <span class="text-danger-600">*</span>
                    </label>
                    <select 
                        name="reason" 
                        id="reason"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        required
                    >
                        <option value="">Select a reason...</option>
                        <option value="billing_error" {{ old('reason') == 'billing_error' ? 'selected' : '' }}>Billing Error</option>
                        <option value="service_issue" {{ old('reason') == 'service_issue' ? 'selected' : '' }}>Service Issue</option>
                        <option value="customer_satisfaction" {{ old('reason') == 'customer_satisfaction' ? 'selected' : '' }}>Customer Satisfaction</option>
                        <option value="overpayment" {{ old('reason') == 'overpayment' ? 'selected' : '' }}>Overpayment</option>
                        <option value="cancellation" {{ old('reason') == 'cancellation' ? 'selected' : '' }}>Service Cancellation</option>
                        <option value="other" {{ old('reason') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Notes -->
                <div class="mb-6">
                    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Internal Notes
                    </label>
                    <textarea 
                        name="notes" 
                        id="notes"
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        placeholder="Add any internal notes about this credit note..."
                    >{{ old('notes') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">These notes are for internal use only and will not be visible to the client.</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a 
                        href="{{ route('billing.finance.credit-notes.index') }}" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-gray-500"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500"
                    >
                        Issue Credit Note
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
