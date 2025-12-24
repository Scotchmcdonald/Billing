@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12" 
     x-data="{ 
        step: 1,
        paymentMethod: 'ach',
        processing: false,
        completed: false,
        error: null,
        invoiceAmount: 1250.00,
        ccFeePercentage: 0.029,
        achFeeFlat: 0.00,
        
        get fee() {
            return this.paymentMethod === 'cc' ? (this.invoiceAmount * this.ccFeePercentage) : this.achFeeFlat;
        },
        
        get total() {
            return this.invoiceAmount + this.fee;
        },

        nextStep() {
            if (this.step < 3) {
                this.step++;
            } else {
                this.submitPayment();
            }
        },

        prevStep() {
            if (this.step > 1) this.step--;
        },

        submitPayment() {
            this.processing = true;
            this.error = null;
            
            // Simulate random failure for demonstration
            const shouldFail = Math.random() < 0.3; 

            setTimeout(() => {
                this.processing = false;
                if (shouldFail) {
                    this.error = {
                        title: 'Payment Failed',
                        message: 'The bank returned "Insufficient Funds".',
                        advice: 'Please try a different payment method or contact your bank.'
                    };
                } else {
                    this.completed = true;
                    this.step = 4;
                }
            }, 2000);
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
                            <button type="button" @click="error = null; step = 2" class="rounded-md bg-red-50 px-2 py-1.5 text-sm font-medium text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
                                Try Credit Card instead
                            </button>
                            <button type="button" @click="error = null" class="ml-3 rounded-md bg-red-50 px-2 py-1.5 text-sm font-medium text-red-800 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
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
                              :class="step >= 2 ? 'text-gray-900' : 'text-gray-500'">Payment Method</span>
                    </div>
                </li>

                <!-- Step 3 -->
                <li class="md:flex-1">
                    <div class="group flex flex-col border-l-4 py-2 pl-4 md:border-l-0 md:border-t-4 md:pl-0 md:pt-4 md:pb-0"
                         :class="step >= 3 ? 'border-primary-600' : 'border-gray-200'">
                        <span class="text-sm font-medium"
                              :class="step >= 3 ? 'text-primary-600' : 'text-gray-500'">Step 3</span>
                        <span class="text-sm font-medium"
                              :class="step >= 3 ? 'text-gray-900' : 'text-gray-500'">Confirmation</span>
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
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Review Invoice #INV-2024-001</h3>
                        <div class="mt-6 border-t border-gray-100">
                            <dl class="divide-y divide-gray-100">
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">Billing Period</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">Dec 1, 2024 - Dec 31, 2024</dd>
                                </div>
                                <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                                    <dt class="text-sm font-medium leading-6 text-gray-900">Line Items</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700 sm:col-span-2 sm:mt-0">
                                        <ul role="list" class="divide-y divide-gray-100 rounded-md border border-gray-200">
                                            <li class="flex items-center justify-between py-3 pl-3 pr-4 text-sm">
                                                <div class="flex w-0 flex-1 items-center">
                                                    <span class="truncate font-medium">Enterprise License (x5)</span>
                                                </div>
                                                <div class="ml-4 flex-shrink-0">
                                                    <span class="font-mono">$1,000.00</span>
                                                </div>
                                            </li>
                                            <li class="flex items-center justify-between py-3 pl-3 pr-4 text-sm">
                                                <div class="flex w-0 flex-1 items-center">
                                                    <span class="truncate font-medium">Support Plan</span>
                                                </div>
                                                <div class="ml-4 flex-shrink-0">
                                                    <span class="font-mono">$250.00</span>
                                                </div>
                                            </li>
                                        </ul>
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
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Select Payment Method</h3>
                        
                        <div class="mt-6 grid grid-cols-1 gap-y-6 sm:grid-cols-2 sm:gap-x-4">
                            <!-- ACH Option -->
                            <div @click="paymentMethod = 'ach'" 
                                class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none"
                                :class="paymentMethod === 'ach' ? 'border-primary-600 ring-2 ring-primary-600' : 'border-gray-300'">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Bank Account (ACH)</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">No processing fees</span>
                                    </span>
                                </span>
                                <svg x-show="paymentMethod === 'ach'" class="h-5 w-5 text-primary-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                            </div>

                            <!-- CC Option -->
                            <div @click="paymentMethod = 'cc'" 
                                class="relative flex cursor-pointer rounded-lg border p-4 shadow-sm focus:outline-none"
                                :class="paymentMethod === 'cc' ? 'border-primary-600 ring-2 ring-primary-600' : 'border-gray-300'">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900">Credit Card</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500">2.9% processing fee</span>
                                    </span>
                                </span>
                                <svg x-show="paymentMethod === 'cc'" class="h-5 w-5 text-primary-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>

                        <!-- Dynamic Fee Offset UI -->
                        <div x-show="paymentMethod === 'cc'" class="mt-6 rounded-md bg-blue-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1 md:flex md:justify-between">
                                    <p class="text-sm text-blue-700">Switch to Bank Account (ACH) to save <span class="font-bold" x-text="'$' + fee.toFixed(2)"></span> in fees.</p>
                                    <p class="mt-3 text-sm md:ml-6 md:mt-0">
                                        <a href="#" @click.prevent="paymentMethod = 'ach'" class="whitespace-nowrap font-medium text-blue-700 hover:text-blue-600">Switch to ACH <span aria-hidden="true">&rarr;</span></a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Element Placeholder -->
                        <div class="mt-8">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <span x-text="paymentMethod === 'ach' ? 'Bank Details' : 'Card Details'"></span>
                            </label>
                            
                            <!-- ACH Bank Connection Simulation -->
                            <div x-show="paymentMethod === 'ach'" x-transition>
                                <button type="button" class="flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2">
                                    <svg class="mr-2 h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                    </svg>
                                    Connect Bank Account via Plaid
                                </button>
                                <p class="mt-2 text-xs text-center text-gray-500">Secure connection. We do not store your login credentials.</p>
                            </div>

                            <!-- Visual Card Input Simulation -->
                            <div x-show="paymentMethod === 'cc'" x-transition class="rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus-within:border-primary-600 focus-within:ring-1 focus-within:ring-primary-600">
                                <div class="flex items-center justify-between mb-2">
                                    <label for="card-number" class="block text-xs font-medium text-gray-500">Card Number</label>
                                    <div class="flex space-x-1">
                                        <!-- Visa Icon -->
                                        <svg class="h-4 w-6 text-gray-400" viewBox="0 0 36 24" fill="currentColor"><path d="M13.169 2.686h-2.604c-.467 0-.85.268-1.03.64l-3.66 8.66-1.52-7.73C4.253 3.65 3.42 2.686 2.33 2.686H.11c-.29 0-.43.22-.33.53l.09.35c.5.13 1.07.33 1.42.53.42.24.54.45.7.83l2.37 9.06-2.42 5.65c-.11.26.09.56.4.56h2.6c.47 0 .85-.27 1.03-.64l6.89-16.28c.05-.18-.08-.59-.4-.59zm4.68 16.28h2.44c.4 0 .71-.24.88-.77l2.56-12.2c.05-.2-.08-.63-.4-.63h-2.44c-.4 0-.71.24-.88.77l-2.56 12.2c-.05.2.08.63.4.63zm6.84-12.6c-.56-.2-1.35-.42-2.37-.42-2.61 0-4.45 1.39-4.46 3.38-.02 1.47 1.31 2.29 2.31 2.78 1.02.5 1.37.82 1.37 1.27 0 .68-.82 1.01-1.58 1.01-1.06 0-1.63-.16-2.5-.55l-.35-.16-.37 2.3c.62.28 1.76.52 2.94.52 2.76 0 4.55-1.36 4.57-3.47.01-1.16-.69-2.04-2.21-2.76-.92-.46-1.49-.77-1.49-1.24 0-.43.48-.88 1.52-.88.88 0 1.52.15 2.01.36l.24.11.37-2.25z"/></svg>
                                    </div>
                                </div>
                                <input type="text" name="card-number" id="card-number" class="block w-full border-0 p-0 text-gray-900 placeholder-gray-400 focus:ring-0 sm:text-sm" placeholder="0000 0000 0000 0000">
                            </div>
                            
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div class="rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus-within:border-primary-600 focus-within:ring-1 focus-within:ring-primary-600">
                                    <label for="card-expiration-date" class="block text-xs font-medium text-gray-500">Expiration Date</label>
                                    <input type="text" name="card-expiration-date" id="card-expiration-date" class="block w-full border-0 p-0 text-gray-900 placeholder-gray-400 focus:ring-0 sm:text-sm" placeholder="MM / YY">
                                </div>
                                <div class="rounded-md border border-gray-300 bg-white px-3 py-2 shadow-sm focus-within:border-primary-600 focus-within:ring-1 focus-within:ring-primary-600">
                                    <label for="card-cvc" class="block text-xs font-medium text-gray-500">CVC</label>
                                    <input type="text" name="card-cvc" id="card-cvc" class="block w-full border-0 p-0 text-gray-900 placeholder-gray-400 focus:ring-0 sm:text-sm" placeholder="CVC">
                                </div>
                            </div>
                            
                            <div class="mt-4 flex items-center text-xs text-gray-500">
                                <svg class="h-4 w-4 mr-1 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Payments are secure and encrypted.
                            </div>
                        </div>

                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" @click="nextStep()" class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto">Review & Pay</button>
                        <button type="button" @click="prevStep()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Back</button>
                    </div>
                </div>

                <!-- Step 3: Confirmation / Processing -->
                <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0" style="display: none;">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium leading-6 text-gray-900">Confirm Payment</h3>
                        
                        <div class="mt-6 bg-gray-50 rounded-lg p-6">
                            <dl class="divide-y divide-gray-200">
                                <div class="py-4 flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Subtotal</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="'$' + invoiceAmount.toFixed(2)"></dd>
                                </div>
                                <div class="py-4 flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Processing Fee</dt>
                                    <dd class="text-sm font-medium text-gray-900" x-text="'$' + fee.toFixed(2)"></dd>
                                </div>
                                <div class="py-4 flex justify-between border-t border-gray-200">
                                    <dt class="text-base font-bold text-gray-900">Total to Pay</dt>
                                    <dd class="text-base font-bold text-primary-600" x-text="'$' + total.toFixed(2)"></dd>
                                </div>
                            </dl>
                        </div>

                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" @click="submitPayment()" :disabled="processing" class="inline-flex w-full justify-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 sm:ml-3 sm:w-auto disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!processing">Pay <span x-text="'$' + total.toFixed(2)"></span></span>
                            <span x-show="processing" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Processing...
                            </span>
                        </button>
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
