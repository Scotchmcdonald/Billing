<!-- PayPal Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.update') }}">
    @csrf
    <input type="hidden" name="integration" value="paypal">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
            <input type="text" name="paypal_client_id" value="{{ setting('paypal_client_id') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="Client ID from PayPal Developer Dashboard">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
            <input type="password" name="paypal_client_secret" value="{{ setting('paypal_client_secret') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="Secret Key">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
            <select name="paypal_mode" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="sandbox" {{ setting('paypal_mode') === 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                <option value="live" {{ setting('paypal_mode') === 'live' ? 'selected' : '' }}>Live (Production)</option>
            </select>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save PayPal Settings
            </button>
        </div>
    </div>
</form>