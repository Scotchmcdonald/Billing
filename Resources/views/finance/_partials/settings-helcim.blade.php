<!-- Helcim Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.helcim') }}">
    @csrf
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account ID</label>
            <input type="text" name="helcim_account_id" value="{{ setting('helcim_account_id') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="123456789">
            <p class="text-xs text-gray-500 mt-1">Your Helcim Merchant Account ID</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">API Key</label>
            <input type="password" name="helcim_api_key" value="{{ setting('helcim_api_key') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="Enter your API Key">
            <p class="text-xs text-gray-500 mt-1">Keep this secure - never share publicly</p>
        </div>

        <div class="bg-info-50 border border-info-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-info-500"></i>
                </div>
                <div class="ml-3">
                    <h5 class="text-sm font-medium text-info-800">Webhook Endpoint</h5>
                    <p class="text-sm text-info-700 mt-1">Configure this URL in your Helcim dashboard:</p>
                    <code class="block mt-2 text-xs bg-white px-2 py-1 rounded border border-info-200">
                        {{ route('billing.webhooks.helcim') }}
                    </code>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Helcim Settings
            </button>
        </div>
    </div>
</form>
