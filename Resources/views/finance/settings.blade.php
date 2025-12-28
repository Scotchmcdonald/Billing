<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Stripe Configuration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Stripe API Configuration</h3>
                    
                    <form method="POST" action="{{ route('billing.finance.settings.stripe') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <x-input-label for="stripe_key" :value="__('Stripe Publishable Key')" />
                            <x-text-input id="stripe_key" class="block mt-1 w-full" type="text" name="stripe_key" :value="$settings['stripe_key']->value ?? ''" placeholder="pk_test_..." />
                            <p class="mt-1 text-sm text-gray-500">Your Stripe publishable key (starts with pk_)</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="stripe_secret" :value="__('Stripe Secret Key')" />
                            <x-text-input id="stripe_secret" class="block mt-1 w-full" type="password" name="stripe_secret" :value="$settings['stripe_secret']->value ?? ''" placeholder="sk_test_..." />
                            <p class="mt-1 text-sm text-gray-500">Your Stripe secret key (starts with sk_)</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="stripe_webhook_secret" :value="__('Stripe Webhook Secret')" />
                            <x-text-input id="stripe_webhook_secret" class="block mt-1 w-full" type="password" name="stripe_webhook_secret" :value="$settings['stripe_webhook_secret']->value ?? ''" placeholder="whsec_..." />
                            <p class="mt-1 text-sm text-gray-500">Your Stripe webhook signing secret (optional)</p>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Save Stripe Settings') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- QuickBooks Integration -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">QuickBooks Integration</h3>
                    
                    <form method="POST" action="{{ route('billing.finance.settings.quickbooks') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="quickbooks_enabled" class="inline-flex items-center">
                                <input id="quickbooks_enabled" type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-2 focus:ring-primary-500 dark:bg-gray-700 transition-colors duration-150" name="quickbooks_enabled" {{ ($settings['quickbooks_enabled']->value ?? false) ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Enable QuickBooks Sync') }}</span>
                            </label>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="quickbooks_client_id" :value="__('Client ID')" />
                            <x-text-input id="quickbooks_client_id" class="block mt-1 w-full" type="text" name="quickbooks_client_id" :value="$settings['quickbooks_client_id']->value ?? ''" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="quickbooks_client_secret" :value="__('Client Secret')" />
                            <x-text-input id="quickbooks_client_secret" class="block mt-1 w-full" type="password" name="quickbooks_client_secret" :value="$settings['quickbooks_client_secret']->value ?? ''" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="quickbooks_realm_id" :value="__('Realm ID (Company ID)')" />
                            <x-text-input id="quickbooks_realm_id" class="block mt-1 w-full" type="text" name="quickbooks_realm_id" :value="$settings['quickbooks_realm_id']->value ?? ''" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button>
                                {{ __('Save QuickBooks Settings') }}
                            </x-primary-button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
