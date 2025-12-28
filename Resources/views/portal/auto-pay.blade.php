<x-app-layout>
    <div class="container mx-auto px-4 py-6 max-w-2xl">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Auto-Pay Settings</h2>

            @if(session('success'))
                <div class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg">
                    <p class="font-semibold">✓ Settings Updated</p>
                    <p class="text-sm mt-1">{{ session('success') }}</p>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
                    <p class="font-semibold">Unable to Update Settings</p>
                    <ul class="list-disc list-inside mt-2 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Current Status -->
            <div class="mb-6 p-4 rounded-lg {{ $company->auto_pay_enabled ? 'bg-success-50 border border-success-200' : 'bg-gray-50 border border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">Auto-Pay Status</p>
                        <p class="text-sm text-gray-600 mt-1">
                            @if($company->auto_pay_enabled)
                                Automatic payments are <span class="text-success-700 font-semibold">ENABLED</span>
                            @else
                                Automatic payments are <span class="text-gray-700 font-semibold">DISABLED</span>
                            @endif
                        </p>
                    </div>
                    @if($company->auto_pay_enabled)
                        <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    @endif
                </div>
            </div>

            <!-- Auto-Pay Toggle Form -->
            <form method="POST" action="{{ route('billing.portal.auto-pay.toggle', $company) }}" x-data="{ enabled: {{ $company->auto_pay_enabled ? 'true' : 'false' }}, showConfirmation: false }">
                @csrf

                <!-- Enable/Disable Toggle -->
                <div class="mb-6">
                    <label class="flex items-center justify-between p-4 border border-gray-300 rounded-lg cursor-pointer hover:bg-gray-50">
                        <div>
                            <span class="font-semibold text-gray-900">Enable Automatic Payments</span>
                            <p class="text-sm text-gray-600 mt-1">
                                Automatically charge your default payment method when invoices are due
                            </p>
                        </div>
                        <div class="relative">
                            <input 
                                type="checkbox" 
                                name="auto_pay_enabled" 
                                id="auto_pay_enabled"
                                x-model="enabled"
                                @change="if(enabled && !{{ $company->auto_pay_enabled ? 'true' : 'false' }}) showConfirmation = true"
                                class="sr-only peer"
                                {{ $company->auto_pay_enabled ? 'checked' : '' }}
                            >
                            <div class="w-14 h-8 bg-gray-200 peer-focus:ring-4 peer-focus:ring-primary-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-primary-600"></div>
                        </div>
                    </label>
                </div>

                <!-- Payment Method Selection (shown when enabled) -->
                <div x-show="enabled" x-transition class="mb-6">
                    <label for="payment_method_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Default Payment Method <span class="text-danger-600">*</span>
                    </label>
                    
                    @if($paymentMethods->count() > 0)
                        <select 
                            name="payment_method_id" 
                            id="payment_method_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            {{ !$company->auto_pay_enabled ? 'required' : '' }}
                        >
                            <option value="">Select a payment method...</option>
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method->id }}" {{ $company->default_payment_method_id == $method->id ? 'selected' : '' }}>
                                    {{ $method->type === 'card' ? '•••• •••• •••• ' . $method->last4 : 'Bank Account ••••' . $method->last4 }}
                                    @if($method->is_default) (Current Default) @endif
                                </option>
                            @endforeach
                        </select>
                    @else
                        <div class="bg-warning-50 border border-warning-200 text-warning-700 px-4 py-3 rounded-lg">
                            <p class="font-semibold">⚠️ No Payment Methods</p>
                            <p class="text-sm mt-1">You need to add a payment method before enabling auto-pay.</p>
                            <a href="{{ route('billing.portal.payment-methods.create', $company) }}" class="text-sm font-semibold text-warning-700 underline mt-2 inline-block">
                                Add Payment Method →
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Next Charge Info (shown when enabled) -->
                <div x-show="enabled" x-transition class="mb-6">
                    @if($nextInvoice)
                        <div class="bg-primary-50 border border-primary-200 rounded-lg p-4">
                            <p class="text-sm font-semibold text-gray-900 mb-2">Next Scheduled Charge</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Invoice #{{ $nextInvoice->invoice_number }}</p>
                                    <p class="text-xs text-gray-500">Due: {{ $nextInvoice->due_date->format('M d, Y') }}</p>
                                </div>
                                <p class="text-2xl font-bold text-primary-600">${{ number_format($nextInvoice->total_amount / 100, 2) }}</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <p class="text-sm text-gray-600">No upcoming invoices</p>
                        </div>
                    @endif
                </div>

                <!-- Confirmation Modal (Alpine.js) -->
                <div x-show="showConfirmation" 
                     x-transition
                     class="fixed inset-0 z-50 overflow-y-auto" 
                     style="display: none;"
                     @keydown.escape.window="showConfirmation = false">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showConfirmation = false"></div>
                    
                    <!-- Modal -->
                    <div class="relative min-h-screen flex items-center justify-center p-4">
                        <div class="relative bg-white rounded-lg max-w-md w-full p-6 shadow-xl">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Confirm Auto-Pay Activation</h3>
                            
                            <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-4">
                                <p class="text-sm text-warning-700">
                                    <span class="font-semibold">Please Confirm:</span> Your default payment method will be automatically charged when invoices become due.
                                </p>
                            </div>

                            <ul class="text-sm text-gray-600 space-y-2 mb-6">
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-success-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>Charges occur automatically on invoice due dates</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-success-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>You'll receive email confirmations for each payment</span>
                                </li>
                                <li class="flex items-start">
                                    <svg class="w-5 h-5 text-success-600 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>You can disable auto-pay at any time</span>
                                </li>
                            </ul>

                            <div class="flex items-center justify-end space-x-3">
                                <button 
                                    type="button" 
                                    @click="showConfirmation = false; enabled = false"
                                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-gray-500"
                                >
                                    Cancel
                                </button>
                                <button 
                                    type="button" 
                                    @click="showConfirmation = false"
                                    class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500"
                                >
                                    I Understand, Enable Auto-Pay
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a 
                        href="{{ route('billing.portal.dashboard', $company) }}" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-gray-500"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500"
                        :disabled="enabled && {{ $paymentMethods->count() }} === 0"
                    >
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
