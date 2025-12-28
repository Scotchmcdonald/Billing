@extends('layouts.app')

@section('content')
<div x-data="autoPay" x-init="init()" class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold leading-7 text-gray-900">
                Set Up Auto-Pay
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Never miss a payment • Secure & convenient • Cancel anytime
            </p>
        </div>

        <!-- Progress Stepper -->
        <div class="mb-8">
            <nav aria-label="Progress">
                <ol role="list" class="flex items-center">
                    <li class="relative pr-8 sm:pr-20" :class="{ 'flex-1': step < 3 }">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full" :class="step >= 2 ? 'bg-primary-600' : 'bg-gray-200'"></div>
                        </div>
                        <a href="#" @click.prevent="goToStep(1)" class="relative flex h-8 w-8 items-center justify-center rounded-full" :class="step >= 1 ? 'bg-primary-600' : 'bg-gray-300'">
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            <span class="sr-only">Step 1</span>
                        </a>
                        <p class="mt-2 text-xs font-medium text-gray-900">Payment Method</p>
                    </li>

                    <li class="relative pr-8 sm:pr-20" :class="{ 'flex-1': step < 3 }">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full" :class="step >= 3 ? 'bg-primary-600' : 'bg-gray-200'"></div>
                        </div>
                        <a href="#" @click.prevent="step >= 2 && goToStep(2)" class="relative flex h-8 w-8 items-center justify-center rounded-full" :class="step >= 2 ? 'bg-primary-600' : 'bg-gray-300'">
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            <span class="sr-only">Step 2</span>
                        </a>
                        <p class="mt-2 text-xs font-medium text-gray-900">Schedule</p>
                    </li>

                    <li class="relative">
                        <a href="#" @click.prevent="step >= 3 && goToStep(3)" class="relative flex h-8 w-8 items-center justify-center rounded-full" :class="step >= 3 ? 'bg-primary-600' : 'bg-gray-300'">
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                            <span class="sr-only">Step 3</span>
                        </a>
                        <p class="mt-2 text-xs font-medium text-gray-900">Confirm</p>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Step 1: Payment Method -->
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-10" x-transition:enter-end="opacity-100 transform translate-x-0">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Payment Method</h3>
                
                <!-- Existing Payment Methods -->
                @if(count($payment_methods) > 0)
                    <div class="space-y-3 mb-6">
                        @foreach($payment_methods as $method)
                            <label class="relative flex cursor-pointer rounded-lg border p-4 focus:outline-none" 
                                   :class="form.payment_method_id === {{ $method['id'] }} ? 'border-primary-600 ring-2 ring-primary-600 bg-primary-50' : 'border-gray-300'">
                                <input type="radio" 
                                       x-model="form.payment_method_id" 
                                       value="{{ $method['id'] }}" 
                                       class="sr-only">
                                <div class="flex flex-1 items-center">
                                    <div class="flex-1">
                                        <p class="font-medium text-gray-900">
                                            @if($method['type'] === 'card')
                                                <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                                </svg>
                                                •••• {{ $method['last4'] }}
                                            @else
                                                <svg class="inline-block h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                Bank •••• {{ $method['last4'] }}
                                            @endif
                                        </p>
                                        <p class="text-sm text-gray-500">{{ $method['brand'] ?? 'ACH' }}</p>
                                    </div>
                                    <svg x-show="form.payment_method_id === {{ $method['id'] }}" class="h-5 w-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif

                <!-- Add New Payment Method -->
                <button @click="addPaymentMethod()" type="button" class="w-full flex items-center justify-center px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:border-primary-500 hover:text-primary-600">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Add New Payment Method
                </button>
            </div>

            <div class="mt-6 flex justify-end">
                <button @click="nextStep()" type="button" :disabled="!form.payment_method_id" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Continue
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Schedule -->
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-10" x-transition:enter-end="opacity-100 transform translate-x-0">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configure Schedule</h3>
                
                <div class="space-y-6">
                    <!-- Grace Period -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Grace Period (days after due date)
                        </label>
                        <select x-model="form.grace_days" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="0">No grace period (pay on due date)</option>
                            <option value="3">3 days after due date</option>
                            <option value="5">5 days after due date</option>
                            <option value="7">7 days after due date</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            We'll attempt payment on the due date plus this grace period.
                        </p>
                    </div>

                    <!-- Retry Attempts -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Retry Failed Payments
                        </label>
                        <select x-model="form.retry_attempts" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="1">1 retry (3 days later)</option>
                            <option value="2">2 retries (3 and 6 days later)</option>
                            <option value="3">3 retries (3, 6, and 9 days later)</option>
                        </select>
                        <p class="mt-1 text-sm text-gray-500">
                            If a payment fails, we'll automatically retry.
                        </p>
                    </div>

                    <!-- Email Notifications -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Email Notifications</h4>
                        <div class="space-y-2">
                            <label class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" x-model="form.notify_before" class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-700">Before Payment</span>
                                    <p class="text-gray-500">Email 2 days before auto-payment</p>
                                </div>
                            </label>
                            <label class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" x-model="form.notify_success" checked disabled class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-700">Payment Success</span>
                                    <p class="text-gray-500">Receipt after successful payment (required)</p>
                                </div>
                            </label>
                            <label class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" x-model="form.notify_failure" checked disabled class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-700">Payment Failure</span>
                                    <p class="text-gray-500">Alert if payment fails (required)</p>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="previousStep()" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>
                <button @click="nextStep()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Continue
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Confirmation -->
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-10" x-transition:enter-end="opacity-100 transform translate-x-0">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Review & Confirm</h3>
                
                <div class="space-y-4 mb-6">
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Payment Method</span>
                        <span class="text-sm text-gray-900" x-text="selectedMethodDisplay"></span>
                    </div>
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Grace Period</span>
                        <span class="text-sm text-gray-900" x-text="form.grace_days + ' days'"></span>
                    </div>
                    <div class="flex justify-between py-3 border-b border-gray-200">
                        <span class="text-sm font-medium text-gray-700">Retry Attempts</span>
                        <span class="text-sm text-gray-900" x-text="form.retry_attempts"></span>
                    </div>
                </div>

                <!-- Warning Box -->
                <div class="bg-warning-50 border-l-4 border-warning-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-warning-800">
                                <strong>Important:</strong> By enabling auto-pay, you authorize us to automatically charge your payment method for invoice amounts on the scheduled dates. You can disable auto-pay at any time.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Email Confirmation -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" x-model="form.email_confirmed" class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                        <span class="ml-3 text-sm text-gray-700">
                            I confirm that I have read and understood the auto-pay terms. I will receive a confirmation email to <strong x-text="userEmail"></strong> to activate auto-pay.
                        </span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="previousStep()" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>
                <button @click="enableAutoPay()" type="button" :disabled="!form.email_confirmed || submitting" x-text="submitting ? 'Enabling...' : 'Enable Auto-Pay'" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-success-600 hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500 disabled:opacity-50 disabled:cursor-not-allowed">
                </button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('autoPay', () => ({
        step: 1,
        submitting: false,
        userEmail: '{{ auth()->user()->email }}',
        form: {
            payment_method_id: null,
            grace_days: 3,
            retry_attempts: 3,
            notify_before: true,
            notify_success: true,
            notify_failure: true,
            email_confirmed: false
        },
        
        get selectedMethodDisplay() {
            const method = @json($payment_methods).find(m => m.id === this.form.payment_method_id);
            if (!method) return '';
            return method.type === 'card' ? `•••• ${method.last4}` : `Bank •••• ${method.last4}`;
        },
        
        init() {
            // Auto-select first payment method if available
            const methods = @json($payment_methods);
            if (methods.length > 0) {
                this.form.payment_method_id = methods[0].id;
            }
        },
        
        nextStep() {
            if (this.step < 3) {
                this.step++;
            }
        },
        
        previousStep() {
            if (this.step > 1) {
                this.step--;
            }
        },
        
        goToStep(targetStep) {
            if (targetStep <= this.step) {
                this.step = targetStep;
            }
        },
        
        addPaymentMethod() {
            window.location.href = '{{ route("billing.portal.payment-methods.create") }}?return=auto-pay';
        },
        
        async enableAutoPay() {
            this.submitting = true;
            
            try {
                const response = await fetch('{{ route("billing.portal.auto-pay.enable") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.form)
                });
                
                if (response.ok) {
                    window.location.href = '{{ route("billing.portal.auto-pay.success") }}';
                } else {
                    const error = await response.json();
                    alert(error.message || 'Failed to enable auto-pay');
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
            } finally {
                this.submitting = false;
            }
        }
    }))
});
</script>
@endpush
@endsection
