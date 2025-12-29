<!-- Xero Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.update') }}">
    @csrf
    <input type="hidden" name="integration" value="xero">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
            <input type="text" name="xero_client_id" value="{{ setting('xero_client_id') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
            <input type="password" name="xero_client_secret" value="{{ setting('xero_client_secret') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tenant ID</label>
            <input type="text" name="xero_tenant_id" value="{{ setting('xero_tenant_id') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="Optional - will be fetched automatically on connect">
        </div>

        <div class="bg-info-50 border border-info-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-info-500"></i>
                </div>
                <div class="ml-3">
                    <h5 class="text-sm font-medium text-info-800">Callback URL</h5>
                    <p class="text-sm text-info-700 mt-1">Add this to your Xero App configuration:</p>
                    <code class="block mt-2 text-xs bg-white px-2 py-1 rounded border border-info-200">
                        {{ route('billing.finance.xero.callback') }}
                    </code>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Xero Settings
            </button>
        </div>
    </div>
</form>