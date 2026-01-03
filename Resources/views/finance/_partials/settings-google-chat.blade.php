<!-- Google Chat Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.google-chat') }}">
    @csrf
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
            <input type="text" name="google_chat_webhook_url" value="{{ setting('google_chat_webhook_url') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="https://chat.googleapis.com/v1/spaces/...">
            <p class="text-xs text-gray-500 mt-1">Incoming Webhook URL for the Google Chat space</p>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Google Chat Settings
            </button>
        </div>
    </div>
</form>
