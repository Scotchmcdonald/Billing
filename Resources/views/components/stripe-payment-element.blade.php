@props(['intent', 'returnUrl', 'company'])

<div x-data="stripePaymentElement" class="w-full max-w-md mx-auto">
    <!-- Troubleshooting Card -->
    <div x-show="errorMessage" x-cloak class="mb-6 transition-all duration-300 ease-in-out">
        <x-billing::troubleshooting-card 
            title="Payment Setup Failed" 
            :steps="['Check your bank credentials.', 'Ensure sufficient funds.', 'Contact support if the issue persists.']" 
        />
        <p x-text="errorMessage" class="mt-2 text-sm text-red-600 px-4 font-medium"></p>
    </div>

    <form id="payment-form" class="space-y-6" @submit.prevent="handleSubmit">
        <div id="payment-element" class="min-h-[200px]">
            <!-- Stripe Elements will create form elements here -->
        </div>

        <button type="submit" :disabled="loading" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
            <svg x-show="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span x-text="loading ? 'Processing...' : 'Save Payment Method'"></span>
        </button>
        
        <div class="text-center mt-4">
             <p class="text-xs text-gray-500">
                <span class="font-semibold text-emerald-600">Pro Tip:</span> Use your Bank Account (ACH) to avoid processing fees.
            </p>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('stripePaymentElement', () => ({
            stripe: null,
            elements: null,
            clientSecret: '{{ $intent->client_secret }}',
            errorMessage: null,
            loading: false,
            
            init() {
                this.stripe = Stripe('{{ config('cashier.key') }}');
                
                const appearance = {
                    theme: 'stripe',
                    variables: {
                        colorPrimary: '#4f46e5', // Indigo-600
                        colorText: '#1f2937', // Gray-800
                    },
                };
                
                const options = {
                    clientSecret: this.clientSecret,
                    appearance: appearance,
                    mode: 'setup',
                    currency: 'usd',
                    paymentMethodCreation: 'manual',
                    // Prioritize ACH
                    paymentMethodTypes: ['us_bank_account', 'card'],
                };

                this.elements = this.stripe.elements(options);
                const paymentElement = this.elements.create('payment', {
                    layout: 'tabs',
                    defaultValues: {
                        billingDetails: {
                            name: '{{ $company->name }}',
                            email: '{{ $company->email }}'
                        }
                    }
                });
                paymentElement.mount('#payment-element');
            },

            async handleSubmit() {
                this.loading = true;
                this.errorMessage = null;

                const { error } = await this.stripe.confirmSetup({
                    elements: this.elements,
                    confirmParams: {
                        return_url: '{{ $returnUrl }}',
                    },
                });

                if (error) {
                    this.errorMessage = error.message;
                    this.loading = false;
                } else {
                    // Your customer will be redirected to your `return_url`.
                }
            }
        }));
    });
</script>
@endpush
