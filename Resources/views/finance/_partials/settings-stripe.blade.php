<!-- Stripe Integration Settings -->
<form method="POST" action="{{ route('billing.finance.settings-hub.stripe') }}">
    @csrf
    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Publishable Key</label>
            <input type="text" name="stripe_publishable_key" value="{{ setting('stripe_publishable_key') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="pk_live_...">
            <p class="text-xs text-gray-500 mt-1">Used for client-side payment forms</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
            <input type="password" name="stripe_secret_key" value="{{ setting('stripe_secret_key') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="sk_live_...">
            <p class="text-xs text-gray-500 mt-1">Keep this secure - never share publicly</p>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Secret</label>
            <input type="password" name="stripe_webhook_secret" value="{{ setting('stripe_webhook_secret') }}" 
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 font-mono text-sm"
                   placeholder="whsec_...">
            <p class="text-xs text-gray-500 mt-1">Used to verify webhook signatures</p>
        </div>

        <div class="bg-info-50 border border-info-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-info-500"></i>
                </div>
                <div class="ml-3">
                    <h5 class="text-sm font-medium text-info-800">Webhook Endpoint</h5>
                    <p class="text-sm text-info-700 mt-1">Configure this URL in your Stripe dashboard:</p>
                    <code class="block mt-2 text-xs bg-white px-2 py-1 rounded border border-info-200">
                        {{ route('billing.stripe.webhook') }}
                    </code>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                <i class="fas fa-save mr-2"></i>
                Save Stripe Settings
            </button>
        </div>
    </div>
</form>
