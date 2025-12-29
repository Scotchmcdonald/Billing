<!-- GoCardless Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.update') }}">
    @csrf
    <input type="hidden" name="integration" value="gocardless">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Access Token</label>
            <input type="password" name="gocardless_access_token" value="{{ setting('gocardless_access_token') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
            <input type="password" name="gocardless_webhook_secret" value="{{ setting('gocardless_webhook_secret') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Environment</label>
            <select name="gocardless_environment" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                <option value="sandbox" {{ setting('gocardless_environment') === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                <option value="live" {{ setting('gocardless_environment') === 'live' ? 'selected' : '' }}>Live</option>
            </select>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save GoCardless Settings
            </button>
        </div>
    </div>
</form>