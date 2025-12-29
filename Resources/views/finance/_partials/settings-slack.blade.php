<!-- Slack Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.update') }}">
    @csrf
    <input type="hidden" name="integration" value="slack">
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook URL</label>
            <input type="text" name="slack_webhook_url" value="{{ setting('slack_webhook_url') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="https://hooks.slack.com/services/...">
            <p class="text-xs text-gray-500 mt-1">Incoming Webhook URL for your Slack workspace</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Default Channel</label>
            <input type="text" name="slack_channel" value="{{ setting('slack_channel', '#billing') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                   placeholder="#billing">
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="slack_notify_payment_failed" id="slack_notify_payment_failed" value="1" 
                   {{ setting('slack_notify_payment_failed') ? 'checked' : '' }}
                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            <label for="slack_notify_payment_failed" class="ml-2 block text-sm text-gray-900">
                Notify on failed payments
            </label>
        </div>

        <div class="flex items-center">
            <input type="checkbox" name="slack_notify_invoice_paid" id="slack_notify_invoice_paid" value="1" 
                   {{ setting('slack_notify_invoice_paid') ? 'checked' : '' }}
                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-500 focus:ring-primary-500">
            <label for="slack_notify_invoice_paid" class="ml-2 block text-sm text-gray-900">
                Notify on successful payments
            </label>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Slack Settings
            </button>
        </div>
    </div>
</form>