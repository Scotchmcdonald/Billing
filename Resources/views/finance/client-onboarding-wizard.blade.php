{{-- Client Onboarding Wizard - Guided Journey Pattern --}}
<div x-data="onboardingWizard()" x-init="init()" class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Progress Stepper --}}
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <nav aria-label="Progress">
                <ol class="flex items-center">
                    <template x-for="(stepItem, index) in steps" :key="index">
                        <li class="relative flex-1" :class="index < steps.length - 1 ? 'pr-8 sm:pr-20' : ''">
                            {{-- Connector Line --}}
                            <div x-show="index < steps.length - 1" class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="h-0.5 w-full" :class="step > index + 1 ? 'bg-primary-600' : 'bg-gray-200'"></div>
                            </div>
                            
                            <div class="relative flex flex-col items-center group">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full border-2 bg-white" 
                                      :class="step > index + 1 ? 'border-primary-600 bg-primary-600' : step === index + 1 ? 'border-primary-600' : 'border-gray-300'">
                                    <span x-show="step > index + 1" class="text-white">✓</span>
                                    <span x-show="step <= index + 1" :class="step === index + 1 ? 'text-primary-600 font-semibold' : 'text-gray-500'" x-text="index + 1"></span>
                                </span>
                                <span class="mt-2 text-xs font-medium text-center" :class="step === index + 1 ? 'text-primary-600' : 'text-gray-500'" x-text="stepItem.title"></span>
                            </div>
                        </li>
                    </template>
                </ol>
            </nav>
        </div>

        {{-- Step 1: Company Information --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Company Information</h2>
            <p class="text-sm text-gray-600 mb-6">Tell us about your new client</p>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company Name *</label>
                    <input type="text" x-model="formData.company_name" required
                           class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                           placeholder="ABC Corporation">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Industry</label>
                        <select x-model="formData.industry" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select industry...</option>
                            <option value="technology">Technology</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="finance">Finance</option>
                            <option value="retail">Retail</option>
                            <option value="manufacturing">Manufacturing</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Company Size</label>
                        <select x-model="formData.company_size" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Select size...</option>
                            <option value="1-10">1-10 employees</option>
                            <option value="11-50">11-50 employees</option>
                            <option value="51-200">51-200 employees</option>
                            <option value="201-500">201-500 employees</option>
                            <option value="500+">500+ employees</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                    <input type="url" x-model="formData.website"
                           class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                           placeholder="https://example.com">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                    <textarea x-model="formData.address" rows="2"
                              class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                              placeholder="Street address, City, State, ZIP"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button @click="nextStep()" :disabled="!formData.company_name"
                        class="px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next: Primary Contact →
                </button>
            </div>
        </div>

        {{-- Step 2: Primary Contact --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Primary Contact</h2>
            <p class="text-sm text-gray-600 mb-6">Who should we contact about billing?</p>

            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">First Name *</label>
                        <input type="text" x-model="formData.contact_first_name" required
                               class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Last Name *</label>
                        <input type="text" x-model="formData.contact_last_name" required
                               class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input type="email" x-model="formData.contact_email" required
                           class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                           placeholder="contact@example.com">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                        <input type="tel" x-model="formData.contact_phone"
                               class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                               placeholder="(555) 123-4567">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                        <input type="text" x-model="formData.contact_title"
                               class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                               placeholder="CEO, CFO, etc.">
                    </div>
                </div>

                <div class="bg-info-50 border border-info-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-info-700 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm text-info-700">This contact will receive invoice notifications and billing updates.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="prevStep()"
                        class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    ← Back
                </button>
                <button @click="nextStep()" :disabled="!formData.contact_email"
                        class="px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Next: Billing Setup →
                </button>
            </div>
        </div>

        {{-- Step 3: Billing Setup --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Billing Configuration</h2>
            <p class="text-sm text-gray-600 mb-6">Set up pricing and billing terms</p>

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Billing Model *</label>
                    <div class="grid grid-cols-2 gap-4">
                        <label class="relative flex cursor-pointer rounded-lg border p-4" :class="formData.billing_model === 'hourly' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                            <input type="radio" x-model="formData.billing_model" value="hourly" class="sr-only">
                            <div class="flex-1">
                                <span class="block text-sm font-medium" :class="formData.billing_model === 'hourly' ? 'text-primary-900' : 'text-gray-900'">Hourly</span>
                                <span class="mt-1 flex items-center text-xs" :class="formData.billing_model === 'hourly' ? 'text-primary-700' : 'text-gray-500'">Bill based on time tracked</span>
                            </div>
                        </label>
                        <label class="relative flex cursor-pointer rounded-lg border p-4" :class="formData.billing_model === 'fixed' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                            <input type="radio" x-model="formData.billing_model" value="fixed" class="sr-only">
                            <div class="flex-1">
                                <span class="block text-sm font-medium" :class="formData.billing_model === 'fixed' ? 'text-primary-900' : 'text-gray-900'">Fixed/Retainer</span>
                                <span class="mt-1 flex items-center text-xs" :class="formData.billing_model === 'fixed' ? 'text-primary-700' : 'text-gray-500'">Monthly fixed amount</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div x-show="formData.billing_model === 'hourly'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Hourly Rate *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">$</span>
                        </div>
                        <input type="number" x-model="formData.hourly_rate" step="5" min="0"
                               class="w-full pl-7 py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                               placeholder="150.00">
                    </div>
                </div>

                <div x-show="formData.billing_model === 'fixed'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Retainer Amount *</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500">$</span>
                        </div>
                        <input type="number" x-model="formData.monthly_amount" step="100" min="0"
                               class="w-full pl-7 py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                               placeholder="5000.00">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Terms</label>
                    <select x-model="formData.payment_terms" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                        <option value="net_15">Net 15</option>
                        <option value="net_30" selected>Net 30</option>
                        <option value="net_45">Net 45</option>
                        <option value="due_on_receipt">Due on Receipt</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Billing Frequency</label>
                    <select x-model="formData.billing_frequency" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                        <option value="monthly">Monthly</option>
                        <option value="biweekly">Bi-weekly</option>
                        <option value="weekly">Weekly</option>
                        <option value="quarterly">Quarterly</option>
                    </select>
                </div>

                <div>
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" x-model="formData.auto_invoice"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Enable automatic invoice generation</span>
                    </label>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="prevStep()"
                        class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    ← Back
                </button>
                <button @click="nextStep()"
                        class="px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Next: Services & Review →
                </button>
            </div>
        </div>

        {{-- Step 4: Review & Submit --}}
        <div x-show="step === 4" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0"
             class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Review & Submit</h2>
            <p class="text-sm text-gray-600 mb-6">Verify all information before creating the client</p>

            <div class="space-y-6">
                {{-- Company Info Summary --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Company Information</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Company Name</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.company_name || 'Not provided'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Industry</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.industry || 'Not specified'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Company Size</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.company_size || 'Not specified'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Website</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.website || 'Not provided'"></dd>
                        </div>
                    </dl>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Primary Contact</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Name</dt>
                            <dd class="font-medium text-gray-900" x-text="(formData.contact_first_name || '') + ' ' + (formData.contact_last_name || '')"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Email</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.contact_email || 'Not provided'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Phone</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.contact_phone || 'Not provided'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Title</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.contact_title || 'Not specified'"></dd>
                        </div>
                    </dl>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Billing Configuration</h3>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Billing Model</dt>
                            <dd class="font-medium text-gray-900 capitalize" x-text="formData.billing_model || 'Not set'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Rate/Amount</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.billing_model === 'hourly' ? '$' + (formData.hourly_rate || '0') + '/hr' : '$' + (formData.monthly_amount || '0') + '/mo'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Payment Terms</dt>
                            <dd class="font-medium text-gray-900" x-text="formData.payment_terms?.replace('_', ' ') || 'Not set'"></dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Billing Frequency</dt>
                            <dd class="font-medium text-gray-900 capitalize" x-text="formData.billing_frequency || 'Not set'"></dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-success-50 border border-success-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-success-700 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-success-800">Ready to create</h3>
                            <p class="mt-1 text-sm text-success-700">All required information has been provided. Click Submit to create the client account.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="prevStep()"
                        class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    ← Back
                </button>
                <button @click="submitOnboarding()" :disabled="isProcessing"
                        class="px-6 py-3 bg-success-600 text-white font-medium rounded-lg hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!isProcessing">Create Client ✓</span>
                    <span x-show="isProcessing" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creating...
                    </span>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
function onboardingWizard() {
    return {
        step: 1,
        isProcessing: false,
        steps: [
            { title: 'Company' },
            { title: 'Contact' },
            { title: 'Billing' },
            { title: 'Review' }
        ],
        formData: {
            company_name: '',
            industry: '',
            company_size: '',
            website: '',
            address: '',
            contact_first_name: '',
            contact_last_name: '',
            contact_email: '',
            contact_phone: '',
            contact_title: '',
            billing_model: 'hourly',
            hourly_rate: '',
            monthly_amount: '',
            payment_terms: 'net_30',
            billing_frequency: 'monthly',
            auto_invoice: true
        },

        init() {
            // Initialize form
        },

        nextStep() {
            if (this.step < this.steps.length) {
                this.step++;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
            }
        },

        async submitOnboarding() {
            this.isProcessing = true;
            // API call to create client
            setTimeout(() => {
                this.isProcessing = false;
                alert('Client created successfully!');
                // Redirect to client profile
            }, 2000);
        }
    }
}
</script>
