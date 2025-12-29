<!-- General Settings Content -->
<form method="POST" action="{{ route('billing.finance.settings-hub.general') }}">
    @csrf
    <div class="space-y-6">
        <!-- Invoice Configuration -->
        <div>
            <h4 class="text-md font-medium text-gray-900 mb-3">Invoice Configuration</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" value="{{ setting('invoice_prefix', 'INV') }}" 
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="text-xs text-gray-500 mt-1">Example: INV-2025-001</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Starting Number</label>
                    <input type="number" name="invoice_start_number" value="{{ setting('invoice_start_number', 1) }}" min="1"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="text-xs text-gray-500 mt-1">Next invoice will use this number</p>
                </div>
            </div>
        </div>

        <!-- Payment Terms -->
        <div>
            <h4 class="text-md font-medium text-gray-900 mb-3">Payment Terms</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Default Payment Terms (Days)</label>
                    <select name="payment_terms" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="0" {{ setting('payment_terms') == 0 ? 'selected' : '' }}>Due on Receipt</option>
                        <option value="15" {{ setting('payment_terms') == 15 ? 'selected' : '' }}>Net 15</option>
                        <option value="30" {{ setting('payment_terms') == 30 ? 'selected' : '' }}>Net 30</option>
                        <option value="60" {{ setting('payment_terms') == 60 ? 'selected' : '' }}>Net 60</option>
                        <option value="90" {{ setting('payment_terms') == 90 ? 'selected' : '' }}>Net 90</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Late Fee Percentage</label>
                    <input type="number" name="late_fee_percentage" value="{{ setting('late_fee_percentage', 0) }}" min="0" max="100" step="0.1"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="text-xs text-gray-500 mt-1">Applied to overdue invoices</p>
                </div>
            </div>
        </div>

        <!-- Onboarding -->
        <div>
            <h4 class="text-md font-medium text-gray-900 mb-3">Onboarding</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Invitation Timeout (Hours)</label>
                    <input type="number" name="invitation_timeout" value="{{ setting('invitation_timeout', 48) }}" min="1"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                    <p class="text-xs text-gray-500 mt-1">How long an invitation link remains valid</p>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end pt-4 border-t border-gray-200">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Settings
            </button>
        </div>
    </div>
</form>
