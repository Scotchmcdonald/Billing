@extends('layouts.app')

@section('content')
<div class="py-6" x-data="billingSettings()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Mission Control
                </h2>
                <p class="mt-1 text-sm text-gray-500">Global Finance & Inventory Configuration</p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <button type="button" @click="submitForm" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="saving ? 'Saving...' : 'Save Configuration'"></span>
                </button>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md bg-green-50 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <form id="settings-form" action="{{ route('billing.admin.settings.update') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 gap-x-8 gap-y-8 md:grid-cols-3">
                
                <!-- Stripe API Management (Hazard Zone) -->
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-base font-semibold leading-7 text-gray-900">Stripe API Configuration</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Manage your Stripe API keys and Webhook secrets. <span class="text-red-600 font-bold">Handle with care.</span></p>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2 overflow-hidden">
                    <!-- Hazard Stripes Header -->
                    <div class="h-2 w-full" style="background: repeating-linear-gradient(45deg, #fef3c7, #fef3c7 10px, #fcd34d 10px, #fcd34d 20px);"></div>
                    
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            
                            <div class="sm:col-span-4">
                                <label for="stripe_key" class="block text-sm font-medium leading-6 text-gray-900">Publishable Key</label>
                                <div class="mt-2">
                                    <input type="text" name="stripe_key" id="stripe_key" value="{{ $settings['stripe']->firstWhere('key', 'stripe_key')->value ?? '' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 font-mono">
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="stripe_secret" class="block text-sm font-medium leading-6 text-gray-900">Secret Key</label>
                                <div class="mt-2">
                                    <input type="password" name="stripe_secret" id="stripe_secret" placeholder="sk_live_..." class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 font-mono">
                                    <p class="mt-1 text-xs text-gray-500">Leave blank to keep existing secret.</p>
                                </div>
                            </div>

                            <div class="sm:col-span-4">
                                <label for="stripe_webhook_secret" class="block text-sm font-medium leading-6 text-gray-900">Webhook Secret</label>
                                <div class="mt-2">
                                    <input type="password" name="stripe_webhook_secret" id="stripe_webhook_secret" placeholder="whsec_..." class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6 font-mono">
                                    <p class="mt-1 text-xs text-gray-500">Leave blank to keep existing secret.</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <div class="flex items-center justify-between bg-gray-50 p-4 rounded-md border border-gray-200">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">Webhook Status</h4>
                                        <p class="text-xs text-gray-500 mt-1">Check if your server can communicate with Stripe.</p>
                                    </div>
                                    <button type="button" @click="testConnection" class="inline-flex items-center rounded-md bg-white px-2.5 py-1.5 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        <span x-show="!testing">Test Connection</span>
                                        <span x-show="testing">Testing...</span>
                                    </button>
                                </div>
                                <div x-show="testResult" class="mt-2 text-sm" :class="testSuccess ? 'text-green-600' : 'text-red-600'" x-text="testResult"></div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Fee & Surcharge Configuration -->
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-base font-semibold leading-7 text-gray-900">Fees & Surcharges</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Configure global credit card offset fees and surcharges.</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            
                            <div class="sm:col-span-6">
                                <div class="relative flex items-start">
                                    <div class="flex h-6 items-center">
                                        <input type="hidden" name="enable_offset_fee" value="0">
                                        <input id="enable_offset_fee" name="enable_offset_fee" type="checkbox" value="1" {{ ($settings['fees']->firstWhere('key', 'enable_offset_fee')->value ?? false) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600">
                                    </div>
                                    <div class="ml-3 text-sm leading-6">
                                        <label for="enable_offset_fee" class="font-medium text-gray-900">Enable Credit Card Offset Fee</label>
                                        <p class="text-gray-500">Automatically add a surcharge to credit card transactions to cover processing fees.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="offset_fee_percentage" class="block text-sm font-medium leading-6 text-gray-900">Percentage (%)</label>
                                <div class="mt-2">
                                    <input type="number" step="0.01" name="offset_fee_percentage" id="offset_fee_percentage" value="{{ $settings['fees']->firstWhere('key', 'offset_fee_percentage')->value ?? '2.9' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            <div class="sm:col-span-3">
                                <label for="offset_fee_flat" class="block text-sm font-medium leading-6 text-gray-900">Flat Fee ($)</label>
                                <div class="mt-2">
                                    <input type="number" step="0.01" name="offset_fee_flat" id="offset_fee_flat" value="{{ $settings['fees']->firstWhere('key', 'offset_fee_flat')->value ?? '0.30' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Financial Safety Valves -->
                <div class="md:col-span-1">
                    <div class="px-4 sm:px-0">
                        <h3 class="text-base font-semibold leading-7 text-gray-900">Safety Valves</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Set limits and notifications to prevent high-value errors.</p>
                    </div>
                </div>

                <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            
                            <div class="sm:col-span-4">
                                <label for="max_transaction_limit" class="block text-sm font-medium leading-6 text-gray-900">Maximum Transaction Limit ($)</label>
                                <div class="mt-2">
                                    <input type="number" name="max_transaction_limit" id="max_transaction_limit" value="{{ $settings['limits']->firstWhere('key', 'max_transaction_limit')->value ?? '10000' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6">
                                    <p class="mt-1 text-xs text-gray-500">Transactions above this amount will require ACH or manual override.</p>
                                </div>
                            </div>

                            <div class="sm:col-span-6">
                                <label for="tax_provider" class="block text-sm font-medium leading-6 text-gray-900">Tax Provider</label>
                                <div class="mt-2">
                                    <select id="tax_provider" name="tax_provider" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:max-w-xs sm:text-sm sm:leading-6">
                                        <option value="manual" {{ ($settings['tax']->firstWhere('key', 'tax_provider')->value ?? 'manual') == 'manual' ? 'selected' : '' }}>Manual / None</option>
                                        <option value="stripe_tax" {{ ($settings['tax']->firstWhere('key', 'tax_provider')->value ?? '') == 'stripe_tax' ? 'selected' : '' }}>Stripe Tax</option>
                                        <option value="taxjar" {{ ($settings['tax']->firstWhere('key', 'tax_provider')->value ?? '') == 'taxjar' ? 'selected' : '' }}>TaxJar</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>
function billingSettings() {
    return {
        saving: false,
        testing: false,
        testResult: '',
        testSuccess: false,
        submitForm() {
            this.saving = true;
            document.getElementById('settings-form').submit();
        },
        testConnection() {
            this.testing = true;
            this.testResult = '';
            
            fetch('{{ route("billing.admin.settings.test-stripe") }}')
                .then(response => response.json())
                .then(data => {
                    this.testing = false;
                    this.testSuccess = data.success;
                    this.testResult = data.message;
                })
                .catch(error => {
                    this.testing = false;
                    this.testSuccess = false;
                    this.testResult = 'Error connecting to server.';
                });
        }
    }
}
</script>
@endsection
