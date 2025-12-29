<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pre-Flight Billing Review') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        selectedInvoices: [], 
        showDetailModal: false,
        activeInvoice: null,
        toggleSelection(id) {
            if (this.selectedInvoices.includes(id)) {
                this.selectedInvoices = this.selectedInvoices.filter(i => i !== id);
            } else {
                this.selectedInvoices.push(id);
            }
        },
        openDetail(invoice) {
            this.activeInvoice = invoice;
            this.showDetailModal = true;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('billing::finance._partials.nav')

            <!-- Bulk Actions -->
            <div class="mb-4 flex justify-between items-center">
                <div class="flex space-x-2">
                    <button 
                        :disabled="selectedInvoices.length === 0"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                        Approve Selected (<span x-text="selectedInvoices.length"></span>)
                    </button>
                    <button class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                        Export Review Report
                    </button>
                </div>
                <div>
                    <button class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none">
                        Approve All Clean (Score < 20)
                    </button>
                </div>
            </div>

            <!-- Data Table -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                <input type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Company</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Variance</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Anomaly Score</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Items</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($invoices as $invoice)
                        <tr class="{{ $invoice['anomaly_score'] > 80 ? 'bg-rose-50 dark:bg-rose-900/20' : ($invoice['anomaly_score'] > 50 ? 'bg-amber-50 dark:bg-amber-900/20' : 'dark:bg-gray-800') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" @click="toggleSelection({{ $invoice['id'] }})" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 cursor-pointer">{{ $invoice['company_name'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 dark:text-gray-100">${{ number_format($invoice['total'], 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice['variance'] > 0 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $invoice['variance'] > 0 ? '+' : '' }}{{ $invoice['variance'] }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-sm text-gray-900 dark:text-gray-100 mr-2">{{ $invoice['anomaly_score'] }}</span>
                                    <div class="w-16 bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                        <div class="h-2.5 rounded-full {{ $invoice['anomaly_score'] > 50 ? 'bg-rose-600' : 'bg-emerald-600' }}" style="width: {{ $invoice['anomaly_score'] }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $invoice['line_items_count'] }}
                                @if($invoice['unbilled_items'])
                                    <span class="ml-1 text-xs text-amber-600 dark:text-amber-400" title="Includes unbilled items">⚠️</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click="openDetail({{ json_encode($invoice) }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 mr-3">Review</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Invoice Detail Modal -->
            <div x-show="showDetailModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showDetailModal" class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showDetailModal" class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                                        Invoice Detail: <span x-text="activeInvoice?.company_name"></span>
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Total: $<span x-text="activeInvoice?.total"></span>
                                        </p>
                                        <!-- Line items -->
                                        <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Line Items</h4>
                                            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                                <template x-for="item in activeInvoice?.line_items" :key="item.description">
                                                    <li class="py-2 flex justify-between">
                                                        <span class="text-sm text-gray-600 dark:text-gray-400" x-text="item.description"></span>
                                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="'$' + Number(item.amount).toFixed(2)"></span>
                                                    </li>
                                                </template>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" @click="showDetailModal = false">
                                Approve
                            </button>
                            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-600 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showDetailModal = false">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
