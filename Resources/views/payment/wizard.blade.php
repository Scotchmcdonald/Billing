@extends('layouts.app')

@section('content')
<script type="text/javascript" src="https://js.helcim.com/v1/helcim-pay.js"></script>
<div class="min-h-screen bg-gray-50 py-12" 
     x-data="{ 
        step: 1,
        processing: false,
        completed: {{ isset($isPaid) && $isPaid ? 'true' : 'false' }},
        error: null,

        nextStep() {
            if (this.step === 1) {
                this.step = 2;
            }
        },

        pay() {
            this.processing = true;
            this.error = null;
            
            helcimPay.checkout('{{ $helcimToken }}')
                .then((data) => {
                    if (data.status === 'APPROVED' || data.status === 'SUCCESS') {
                        this.processing = false;
                        this.completed = true;
                        this.step = 3;
                    } else {
                        throw new Error(data.responseMessage || 'Payment failed');
                    }
                })
                .catch((error) => {
                    console.error(error);
                    this.processing = false;
                    this.error = {
                        title: 'Payment Failed',
                        message: error.message || 'An error occurred.',
                        advice: 'Please try again.'
                    };
                });
        }
     }">

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Troubleshooting Card (Error State) -->
        <div x-show="error" x-transition class="mb-6 rounded-md bg-red-50 p-4 border border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800" x-text="error.title"></h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p x-text="error.message"></p>
                    </div>
                    <div class="mt-4">
                        <div class="-mx-2 -my-1.5 flex">
                            <button type="button" @click="error = null" class="rounded-md bg-red-50 px-2 py-1.5 text-sm font-medium text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
                                Dismiss
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wizard Progress -->
        <nav aria-label="Progress" class="mb-12">
            <ol role="list" class="space-y-4 md:flex md:space-y-0 md:space-x-8">
                <!-- Step 1 -->
                <li class="md:flex-1">
                    <div class="group flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pl-0 md:pt-4 md:pb-0"
                         :class="step >= 1 ? 'border-primary-600' : 'border-gray-200'">
                        <span class="text-sm font-medium" 
                              :class="step >= 1 ? 'text-primary-600' : 'text-gray-500'">Step 1</span>
                        <span class="text-sm font-medium"
                              :class="step >= 1 ? 'text-gray-900' : 'text-gray-500'">Review Invoice</span>
                    </div>
                </li>

                <!-- Step 2 -->
                <li class="md:flex-1">
                    <div class="group flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pl-0 md:pt-4 md:pb-0"
                         :class="step >= 2 ? 'border-primary-600' : 'border-gray-200'">
                        <span class="text-sm font-medium"
                              :class="step >= 2 ? 'text-primary-600' : 'text-gray-500'">Step 2</span>
                        <span class="text-sm font-medium"
                              :class="step >= 2 ? 'text-gray-900' : 'text-gray-500'">Payment</span>
                    </div>
                </li>

                <!-- Step 3 -->
                <li class="md:flex-1">
                    <div class="group flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pl-0 md:pt-4 md:pb-0"
                         :class="step >= 3 ? 'border-primary-600' : 'border-gray-200'">
                        <span class="text-sm font-medium"
                              :class="step >= 3 ? 'text-primary-600' : 'text-gray-500'">Step 3</span>
                        <span class="text-sm font-medium"
                              :class="step >= 3 ? 'text-gray-900' : 'text-gray-500'">Complete</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Content Area -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
            
            <!-- Main Wizard Column -->
            <div class="lg:col-span-8 bg-white shadow sm:rounded-lg overflow-hidden">
                
                <!-- Step 1: Review -->
                <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Review Invoice #{{ $invoice->invoice_number }}</h3>
                        <div class="mt-6 border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">Issue Date</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">{{ $invoice->issue_date->format('M d, Y') }}</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">Total Amount</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        <span class="font-mono">${{ number_format($invoice->total, 2) }}</span>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" @click="nextStep()" class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">Proceed to Payment</button>
                    </div>
                </div>

                <!-- Step 2: Method -->
                <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Payment</h3>
                        
                        <div class="mt-6">
                            <p class="text-sm text-gray-500 mb-4">Click the button below to securely pay via Helcim.</p>
                            
                            <button type="button" @click="pay()" :disabled="processing" class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!processing">Pay ${{ number_format($invoice->total, 2) }}</span>
                                <span x-show="processing" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" @click="prevStep()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Back</button>
                    </div>
                </div>

                <!-- Step 3: Success -->
                <div x-show="step === 3 || completed" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                    <div class="px-4 py-5 sm:p-6 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-lg font-medium leading-6 text-gray-900">Payment Successful</h3>
                        <p class="mt-2 text-sm text-gray-500">Thank you! Your payment has been processed successfully.</p>
                        
                        <div class="mt-6">
                            <a href="/" class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:w-auto">Return to Dashboard</a>
                        </div>
                    </div>
                </div>
                        <button type="button" @click="prevStep()" :disabled="processing" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto disabled:opacity-50">Back</button>
                    </div>
                </div>

                <!-- Step 4: Success -->
                <div x-show="step === 4" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" style="display: none;">
                    <div class="px-4 py-12 sm:p-6 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-100">
                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <h3 class="mt-3 text-lg font-semibold leading-6 text-gray-900">Payment Successful</h3>
                        <p class="mt-2 text-sm text-gray-500">Thank you! Your payment of <span class="font-bold" x-text="'$' + total.toFixed(2)"></span> has been processed.</p>
                        
                        <div class="mt-6">
                            <button type="button" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd" />
                                </svg>
                                Download Receipt PDF
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sticky Summary Column -->
            <div class="lg:col-span-4 mt-8 lg:mt-0">
                <div class="bg-white shadow sm:rounded-lg sticky top-6">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Order Summary</h3>
                        <dl class="mt-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <dt class="text-sm text-gray-600">Subtotal</dt>
                                <dd class="text-sm font-medium text-gray-900" x-text="'$' + invoiceAmount.toFixed(2)"></dd>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                                <dt class="flex items-center text-sm text-gray-600">
                                    <span>Processing Fee</span>
                                    <svg x-show="paymentMethod === 'ach'" class="ml-2 h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </dt>
                                <dd class="text-sm font-medium text-gray-900" :class="{'text-green-600': paymentMethod === 'ach'}" x-text="paymentMethod === 'ach' ? 'Free' : '$' + fee.toFixed(2)"></dd>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                                <dt class="text-base font-medium text-gray-900">Total</dt>
                                <dd class="text-base font-medium text-gray-900" x-text="'$' + total.toFixed(2)"></dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Stripe initialization logic would go here
    console.log('Stripe JS loaded');
</script>
@endpush
@endsection
