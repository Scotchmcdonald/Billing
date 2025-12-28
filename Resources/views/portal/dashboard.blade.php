<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Client Portal') }} - {{ $company->name }}
            </h2>
            @if(auth()->user()->isAdmin() || auth()->user()->can('finance.admin'))
                <div class="flex items-center">
                    <span class="text-sm text-gray-500 dark:text-gray-400 mr-2">Viewing as Customer</span>
                    <a href="{{ route('billing.finance.portal-access') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 bg-primary-50 dark:bg-primary-900 px-3 py-1 rounded-lg border border-primary-200 dark:border-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-all duration-150">
                        {{ __('Switch Company') }}
                    </a>
                </div>
            @elseif(isset($hasMultipleCompanies) && $hasMultipleCompanies)
                <a href="{{ route('billing.portal.entry') }}" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-800 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-colors duration-150">
                    {{ __('Switch Company') }}
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-12" x-data="{ 
        activeTab: 'services',
        showPayModal: false,
        payInvoice: null,
        paymentMethod: 'cc',
        
        get processingFee() {
            if (this.paymentMethod === 'ach') return 0;
            if (!this.payInvoice) return 0;
            // 2.9% + 30c
            return (this.payInvoice.total * 0.029) + 0.30;
        },
        
        get totalToPay() {
            if (!this.payInvoice) return 0;
            return parseFloat(this.payInvoice.total) + this.processingFee;
        },
        
        openPayModal(invoice) {
            this.payInvoice = invoice;
            this.showPayModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Tabs -->
            <div class="border-b border-gray-200 dark:border-gray-700 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'services'" 
                        :class="activeTab === 'services' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150">
                        My Services
                    </button>
                    <button @click="activeTab = 'history'" 
                        :class="activeTab === 'history' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors duration-150">
                        Billing History
                    </button>
                    <button @click="activeTab = 'methods'" 
                        :class="activeTab === 'methods' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Payment Methods
                    </button>
                </nav>
            </div>

            <!-- My Services Tab -->
            <div x-show="activeTab === 'services'" class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    @forelse($subscriptions as $sub)
                    <li>
                        <div class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-indigo-600 truncate">
                                    {{ $sub->name }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $sub->active() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $sub->stripe_status }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-2 sm:flex sm:justify-between">
                                <div class="sm:flex">
                                    <p class="flex items-center text-sm text-gray-500">
                                        Quantity: {{ $sub->quantity }}
                                    </p>
                                </div>
                                <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                                    <p>
                                        Next Billing: {{ $sub->ends_at ? $sub->ends_at->format('M d, Y') : 'Auto-renew' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="px-4 py-4 sm:px-6 text-gray-500 text-sm">No active subscriptions.</li>
                    @endforelse
                </ul>
                <div class="bg-gray-50 px-4 py-4 sm:px-6">
                    <button class="text-sm text-indigo-600 font-medium hover:text-indigo-500">Request Change</button>
                </div>
            </div>

            <!-- Billing History Tab (Invoices & Payments) -->
            <div x-show="activeTab === 'history'" class="bg-white shadow overflow-hidden sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice / Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status / Payment</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $invoice->invoice_number }}</div>
                                <div class="text-sm text-gray-500">{{ $invoice->issue_date->toFormattedDateString() }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($invoice->total, 2) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <div>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->status == 'paid' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    </div>
                                    
                                    @php
                                        // Find payments for this invoice
                                        $invoicePayments = $payments->where('invoice_id', $invoice->id);
                                    @endphp

                                    @if($invoicePayments->isNotEmpty())
                                        <div class="mt-1 text-xs text-gray-500">
                                            @foreach($invoicePayments as $payment)
                                                <div>Paid {{ $payment->payment_date->format('M d') }} via {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</div>
                                            @endforeach
                                        </div>
                                    @elseif($invoice->status == 'open' || $invoice->status == 'sent')
                                        <div class="mt-1 text-xs text-red-500">
                                            Due {{ $invoice->due_date->format('M d, Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="#" class="text-gray-400 cursor-not-allowed mr-3" title="PDF Download Not Available">View PDF</a>
                                @if($invoice->status == 'sent' || $invoice->status == 'open')
                                <button @click="openPayModal({ id: '{{ $invoice->id }}', number: '{{ $invoice->invoice_number }}', total: {{ $invoice->total }} })" class="text-emerald-600 hover:text-emerald-900">Pay Now</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Payment Methods Tab -->
            <div x-show="activeTab === 'methods'" class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Saved Payment Methods</h3>
                <ul class="space-y-4">
                    @foreach($paymentMethods as $pm)
                    <li class="flex items-center justify-between border p-4 rounded-md">
                        <div class="flex items-center">
                            <svg class="h-8 w-8 text-gray-400" fill="currentColor" viewBox="0 0 24 24"><path d="M2 10h20v12H2V10zm0-2v-3h20v3H2zm0-5h20v1H2V3z"/></svg>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">•••• •••• •••• {{ $pm->card->last4 }}</p>
                                <p class="text-xs text-gray-500">Expires {{ $pm->card->exp_month }}/{{ $pm->card->exp_year }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">{{ ucfirst($pm->card->brand) }}</span>
                    </li>
                    @endforeach
                </ul>
                <div class="mt-6">
                    <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                        Add New Payment Method
                    </button>
                </div>
            </div>

            <!-- Pay Now Modal -->
            <div x-show="showPayModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showPayModal" class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showPayModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Pay Invoice <span x-text="payInvoice?.number"></span></h3>
                            
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b pb-2">
                                    <span class="text-gray-600">Invoice Amount</span>
                                    <span class="font-bold text-gray-900">$<span x-text="payInvoice?.total.toFixed(2)"></span></span>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center p-3 border rounded-md cursor-pointer" :class="paymentMethod === 'cc' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                            <input type="radio" name="paymentMethod" value="cc" x-model="paymentMethod" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-3 block text-sm font-medium text-gray-700">Credit Card</span>
                                        </label>
                                        <label class="flex items-center p-3 border rounded-md cursor-pointer" :class="paymentMethod === 'ach' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200'">
                                            <input type="radio" name="paymentMethod" value="ach" x-model="paymentMethod" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                                            <span class="ml-3 block text-sm font-medium text-gray-700">ACH Bank Transfer</span>
                                            <span class="ml-auto text-xs text-green-600 font-bold">Save Fees!</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="bg-gray-50 p-3 rounded-md">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-gray-500">Processing Fee</span>
                                        <span class="text-gray-900">$<span x-text="processingFee.toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between text-lg font-bold border-t pt-2 mt-2">
                                        <span class="text-gray-900">Total</span>
                                        <span class="text-indigo-600">$<span x-text="totalToPay.toFixed(2)"></span></span>
                                    </div>
                                    <p x-show="paymentMethod === 'ach'" class="text-xs text-green-600 mt-2 text-center">
                                        You are saving $<span x-text="((payInvoice?.total * 0.029) + 0.30).toFixed(2)"></span> by using ACH!
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" @click="showPayModal = false">
                                Complete Payment
                            </button>
                            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showPayModal = false">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
