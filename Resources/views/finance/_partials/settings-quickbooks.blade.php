<!-- QuickBooks Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.quickbooks') }}">
    @csrf
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
            <input type="text" name="qbo_client_id" value="{{ setting('qbo_client_id') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
            <p class="text-xs text-gray-500 mt-1">From QuickBooks Developer Portal</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
            <input type="password" name="qbo_client_secret" value="{{ setting('qbo_client_secret') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
            <p class="text-xs text-gray-500 mt-1">Keep this secure</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Realm ID (Company ID)</label>
            <input type="text" name="qbo_realm_id" value="{{ setting('qbo_realm_id') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
            <p class="text-xs text-gray-500 mt-1">Your QuickBooks company identifier</p>
        </div>

        @if(setting('qbo_access_token'))
        <div class="bg-success-50 border border-success-200 rounded-md p-4">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-success-500 mr-3"></i>
                <div>
                    <h5 class="text-sm font-medium text-success-800">Connection Active</h5>
                    <p class="text-sm text-success-700 mt-1">Last synced: {{ now()->format('M d, Y g:i A') }}</p>
                </div>
                <button type="button" class="ml-auto text-sm text-success-700 hover:text-success-900">
                    Reconnect
                </button>
            </div>
        </div>
        @else
        <div class="bg-warning-50 border border-warning-200 rounded-md p-4">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-warning-500 mr-3"></i>
                <div class="flex-1">
                    <h5 class="text-sm font-medium text-warning-800">Not Connected</h5>
                    <p class="text-sm text-warning-700 mt-1">Click "Connect to QuickBooks" to authorize access</p>
                </div>
                <button type="button" class="ml-auto px-4 py-2 bg-warning-600 text-white rounded-md hover:bg-warning-700 text-sm font-medium">
                    Connect to QuickBooks
                </button>
            </div>
        </div>
        @endif

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save QuickBooks Settings
            </button>
        </div>
    </div>
</form>
