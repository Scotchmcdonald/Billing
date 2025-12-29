<!-- Twilio SMS Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.update') }}">
    @csrf
    <input type="hidden" name="integration" value="twilio">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Account SID</label>
            <input type="text" name="twilio_account_sid" value="{{ setting('twilio_account_sid') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Auth Token</label>
            <input type="password" name="twilio_auth_token" value="{{ setting('twilio_auth_token') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Number</label>
            <input type="text" name="twilio_from_number" value="{{ setting('twilio_from_number') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="+1234567890">
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Twilio Settings
            </button>
        </div>
    </div>
</form>