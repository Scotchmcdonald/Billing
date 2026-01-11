<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('New Client Onboarding') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="onboardingWizard()">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Progress Steps -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <div class="mb-4 flex items-center justify-between">
                        <div class="text-sm font-medium text-gray-700">Step <span x-text="step"></span> of 4</div>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" 
                                     :style="`width: ${(step / 4) * 100}%`"></div>
                            </div>
                            <span class="text-sm font-semibold text-primary-600" x-text="Math.round((step / 4) * 100) + '%'"></span>
                        </div>
                    </div>
                    <nav class="flex items-center justify-between">
                        <template x-for="(stepData, index) in steps" :key="index">
                            <div class="flex items-center" :class="index < steps.length - 1 ? 'flex-1' : ''">
                                <div class="flex flex-col items-center">
                                    <div 
                                        class="w-12 h-12 rounded-full flex items-center justify-center font-bold transition-colors duration-200"
                                        :class="{
                                            'bg-primary-600 text-white': index + 1 === step,
                                            'bg-green-600 text-white': index + 1 < step,
                                            'bg-gray-200 text-gray-600': index + 1 > step
                                        }">
                                        <span x-show="index + 1 > step" x-text="index + 1"></span>
                                        <i x-show="index + 1 < step" class="fas fa-check"></i>
                                        <span x-show="index + 1 === step" x-text="index + 1"></span>
                                    </div>
                                    <div class="text-xs mt-2 font-medium text-center" 
                                         :class="index + 1 === step ? 'text-primary-600' : 'text-gray-600'"
                                         x-text="stepData.title">
                                    </div>
                                </div>
                                <div x-show="index < steps.length - 1" 
                                     class="h-1 flex-1 mx-4 rounded"
                                     :class="index + 1 < step ? 'bg-green-600' : 'bg-gray-200'">
                                </div>
                            </div>
                        </template>
                    </nav>
                </div>
            </div>

            <!-- Step Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form @submit.prevent="submitForm">
                    
                    <!-- Step 1: Company Information -->
                    <div x-show="step === 1" class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Company Information</h3>
                        <p class="text-sm text-gray-600 mb-6">Let's start with the basics about your company.</p>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Name <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    x-model="formData.company_name"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="Acme Corporation">
                            </div>

                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Industry
                                    </label>
                                    <select 
                                        x-model="formData.industry"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Select industry</option>
                                        <option value="technology">Technology</option>
                                        <option value="healthcare">Healthcare</option>
                                        <option value="manufacturing">Manufacturing</option>
                                        <option value="retail">Retail</option>
                                        <option value="financial">Financial Services</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Company Size
                                    </label>
                                    <select 
                                        x-model="formData.company_size"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Select size</option>
                                        <option value="1-10">1-10 employees</option>
                                        <option value="11-50">11-50 employees</option>
                                        <option value="51-200">51-200 employees</option>
                                        <option value="201-500">201-500 employees</option>
                                        <option value="500+">500+ employees</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    x-model="formData.address"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="123 Main Street">
                            </div>

                            <div class="grid grid-cols-3 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        City <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="formData.city"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        State <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="formData.state"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        ZIP Code <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="formData.zip"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Billing Contact -->
                    <div x-show="step === 2" class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Billing Contact</h3>
                        <p class="text-sm text-gray-600 mb-6">Who should we contact for billing matters?</p>

                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        First Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="formData.billing_first_name"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Last Name <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        type="text" 
                                        x-model="formData.billing_last_name"
                                        required
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    x-model="formData.billing_email"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone Number <span class="text-red-500">*</span>
                                </label>
                                <input 
                                    type="tel" 
                                    x-model="formData.billing_phone"
                                    required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Job Title
                                </label>
                                <input 
                                    type="text" 
                                    x-model="formData.billing_title"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    placeholder="e.g., Finance Manager">
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Payment Method -->
                    <div x-show="step === 3" class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Payment Method</h3>
                        <p class="text-sm text-gray-600 mb-6">How would you like to be billed?</p>

                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Payment Type <span class="text-red-500">*</span>
                                </label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                                           :class="formData.payment_method === 'card' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.payment_method" value="card" class="sr-only">
                                        <div class="flex items-center">
                                            <i class="fas fa-credit-card text-2xl mr-3" 
                                               :class="formData.payment_method === 'card' ? 'text-primary-600' : 'text-gray-400'"></i>
                                            <div>
                                                <div class="font-semibold">Credit Card</div>
                                                <div class="text-xs text-gray-500">Auto-pay via Helcim</div>
                                            </div>
                                        </div>
                                    </label>
                                    <label class="relative flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
                                           :class="formData.payment_method === 'invoice' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.payment_method" value="invoice" class="sr-only">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-invoice text-2xl mr-3" 
                                               :class="formData.payment_method === 'invoice' ? 'text-primary-600' : 'text-gray-400'"></i>
                                            <div>
                                                <div class="font-semibold">Invoice</div>
                                                <div class="text-xs text-gray-500">Net 30 terms</div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Credit Card Form (shown only if card selected) -->
                            <div x-show="formData.payment_method === 'card'" class="space-y-4">
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start">
                                    <i class="fas fa-info-circle text-blue-600 mr-3 mt-0.5"></i>
                                    <div class="text-sm text-blue-800">
                                        You'll be redirected to Helcim to securely enter your payment details after completing this wizard.
                                    </div>
                                </div>
                            </div>

                            <!-- Invoice Terms (shown only if invoice selected) -->
                            <div x-show="formData.payment_method === 'invoice'" class="space-y-4">
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <div class="text-sm font-medium text-yellow-800 mb-2">Payment Terms</div>
                                    <ul class="text-sm text-yellow-700 space-y-1">
                                        <li>• Invoices due within 30 days</li>
                                        <li>• 1.5% monthly late fee after due date</li>
                                        <li>• Payment by ACH, check, or wire transfer</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Subscription Tier -->
                    <div x-show="step === 4" class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-2">Choose Your Plan</h3>
                        <p class="text-sm text-gray-600 mb-6">Select the service tier that fits your needs.</p>

                        <div class="grid grid-cols-3 gap-6 mb-6">
                            <label class="relative border-2 rounded-lg p-6 cursor-pointer transition-all"
                                   :class="formData.subscription_tier === 'basic' ? 'border-primary-600 bg-primary-50 shadow-lg' : 'border-gray-300'">
                                <input type="radio" x-model="formData.subscription_tier" value="basic" class="sr-only">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900 mb-2">Basic</div>
                                    <div class="text-3xl font-bold text-primary-600 mb-4">$99<span class="text-lg text-gray-500">/mo</span></div>
                                    <ul class="text-sm text-gray-600 space-y-2 text-left">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Up to 25 devices</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Basic monitoring</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Email support</span>
                                        </li>
                                    </ul>
                                </div>
                            </label>

                            <label class="relative border-2 rounded-lg p-6 cursor-pointer transition-all"
                                   :class="formData.subscription_tier === 'professional' ? 'border-primary-600 bg-primary-50 shadow-lg' : 'border-gray-300'">
                                <input type="radio" x-model="formData.subscription_tier" value="professional" class="sr-only">
                                <div class="absolute top-0 right-0 bg-primary-600 text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-lg">
                                    POPULAR
                                </div>
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900 mb-2">Professional</div>
                                    <div class="text-3xl font-bold text-primary-600 mb-4">$299<span class="text-lg text-gray-500">/mo</span></div>
                                    <ul class="text-sm text-gray-600 space-y-2 text-left">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Up to 100 devices</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Advanced monitoring</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Priority support</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Monthly reports</span>
                                        </li>
                                    </ul>
                                </div>
                            </label>

                            <label class="relative border-2 rounded-lg p-6 cursor-pointer transition-all"
                                   :class="formData.subscription_tier === 'enterprise' ? 'border-primary-600 bg-primary-50 shadow-lg' : 'border-gray-300'">
                                <input type="radio" x-model="formData.subscription_tier" value="enterprise" class="sr-only">
                                <div class="text-center">
                                    <div class="text-lg font-bold text-gray-900 mb-2">Enterprise</div>
                                    <div class="text-3xl font-bold text-primary-600 mb-4">$799<span class="text-lg text-gray-500">/mo</span></div>
                                    <ul class="text-sm text-gray-600 space-y-2 text-left">
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Unlimited devices</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Full-stack monitoring</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>24/7 dedicated support</span>
                                        </li>
                                        <li class="flex items-start">
                                            <i class="fas fa-check text-green-500 mr-2 mt-0.5"></i>
                                            <span>Custom integrations</span>
                                        </li>
                                    </ul>
                                </div>
                            </label>
                        </div>

                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-semibold text-gray-900">Need a custom plan?</div>
                                    <div class="text-sm text-gray-600">Contact us for tailored pricing and features</div>
                                </div>
                                <button type="button" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                    Contact Sales
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <button 
                            type="button" 
                            @click="previousStep"
                            x-show="step > 1"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Back
                        </button>
                        <div x-show="step === 1"></div>
                        
                        <button 
                            type="button" 
                            @click="nextStep"
                            x-show="step < 4"
                            class="px-6 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700 transition-colors">
                            Continue
                            <i class="fas fa-arrow-right ml-2"></i>
                        </button>

                        <button 
                            type="submit"
                            x-show="step === 4"
                            :disabled="submitting"
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed">
                            <span x-show="!submitting">
                                <i class="fas fa-check mr-2"></i>
                                Complete Onboarding
                            </span>
                            <span x-show="submitting">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                Processing...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function onboardingWizard() {
            return {
                step: 1,
                submitting: false,
                steps: [
                    { title: 'Company Info' },
                    { title: 'Billing Contact' },
                    { title: 'Payment' },
                    { title: 'Subscription' }
                ],
                formData: {
                    company_name: '',
                    industry: '',
                    company_size: '',
                    address: '',
                    city: '',
                    state: '',
                    zip: '',
                    billing_first_name: '',
                    billing_last_name: '',
                    billing_email: '',
                    billing_phone: '',
                    billing_title: '',
                    payment_method: 'card',
                    subscription_tier: 'professional'
                },

                nextStep() {
                    if (this.step < 4) {
                        this.step++;
                    }
                },

                previousStep() {
                    if (this.step > 1) {
                        this.step--;
                    }
                },

                async submitForm() {
                    this.submitting = true;

                    try {
                        const response = await fetch('/billing/onboarding/submit', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        if (response.ok) {
                            const data = await response.json();
                            
                            if (this.formData.payment_method === 'card') {
                                // Redirect to Helcim checkout
                                window.location.href = data.helcim_url;
                            } else {
                                // Redirect to dashboard
                                window.location.href = data.redirect_url;
                            }
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.submitting = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
