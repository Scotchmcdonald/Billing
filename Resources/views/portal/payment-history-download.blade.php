<x-app-layout>
    <div x-data="paymentHistory()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Payment History</h1>
            <p class="mt-2 text-sm text-gray-600">Download and reconcile your complete payment records</p>
        </div>

        <!-- Quick Export Card -->
        <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-lg shadow-lg p-6 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold mb-2">Quick Export</h2>
                    <p class="text-primary-100 text-sm mb-4">Download all your payment history with one click</p>
                    <div class="flex items-center gap-3">
                        <button @click="quickExport('excel')" class="inline-flex items-center px-4 py-2 bg-white text-primary-700 rounded-md shadow-sm text-sm font-medium hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 2a2 2 0 00-2 2v8a2 2 0 002 2h6a2 2 0 002-2V6.414A2 2 0 0016.414 5L14 2.586A2 2 0 0012.586 2H9z"/>
                                <path d="M3 8a2 2 0 012-2v10h8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                            </svg>
                            Excel (.xlsx)
                        </button>
                        <button @click="quickExport('csv')" class="inline-flex items-center px-4 py-2 bg-white text-primary-700 rounded-md shadow-sm text-sm font-medium hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            CSV
                        </button>
                        <button @click="quickExport('pdf')" class="inline-flex items-center px-4 py-2 bg-white text-primary-700 rounded-md shadow-sm text-sm font-medium hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                            </svg>
                            PDF
                        </button>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-primary-100 mb-1">Total Paid</p>
                    <p class="text-4xl font-bold" x-text="'$' + stats.total_paid.toLocaleString()"></p>
                    <p class="text-sm text-primary-100 mt-1" x-text="stats.payment_count + ' payments'"></p>
                </div>
            </div>
        </div>

        <!-- Custom Export Builder -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Custom Export Builder</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                    <div class="space-y-2">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center">
                                <input type="radio" x-model="customExport.dateRange" value="all" class="mr-2 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">All Time</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" x-model="customExport.dateRange" value="ytd" class="mr-2 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">Year to Date</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" x-model="customExport.dateRange" value="custom" class="mr-2 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-700">Custom</span>
                            </label>
                        </div>
                        
                        <div x-show="customExport.dateRange === 'custom'" class="grid grid-cols-2 gap-4 pt-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">From Date</label>
                                <input type="date" x-model="customExport.startDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">To Date</label>
                                <input type="date" x-model="customExport.endDate" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Payment Methods</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.methods.all" @change="toggleAllMethods()" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700 font-medium">All Methods</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.methods.card" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Credit/Debit Card</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.methods.ach" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">ACH/Bank Transfer</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.methods.check" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Check</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.methods.other" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Other</span>
                        </label>
                    </div>
                </div>

                <!-- Fields to Include -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fields to Include</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.fields.transaction_id" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Transaction ID</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.fields.invoice_number" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Invoice Number</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.fields.amount" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Amount</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.fields.fee" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Processing Fee</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.fields.net_amount" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Net Amount</span>
                        </label>
                    </div>
                </div>

                <!-- Format & Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Export Format</label>
                    <select x-model="customExport.format" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 mb-4">
                        <option value="excel">Excel (.xlsx)</option>
                        <option value="csv">CSV (.csv)</option>
                        <option value="pdf">PDF Report</option>
                    </select>
                    
                    <label class="block text-sm font-medium text-gray-700 mb-2">Additional Options</label>
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.includeNotes" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Include Notes</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.groupByMonth" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Group by Month</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="customExport.includeRefunds" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                            <span class="text-sm text-gray-700">Include Refunds</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Export Button -->
            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    <span class="font-medium" x-text="estimatedRecords + ' records'"></span> will be included in this export
                </div>
                <button @click="generateCustomExport()" :disabled="!isValidExport()" :class="isValidExport() ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-300 cursor-not-allowed'" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg x-show="!exporting" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <svg x-show="exporting" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="exporting ? 'Generating...' : 'Generate Export'"></span>
                </button>
            </div>
        </div>

        <!-- Recent Payments Summary -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Payments</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="payment in recentPayments" :key="payment.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="payment.date"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-600" x-text="payment.invoice"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" x-text="payment.method"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900" x-text="'$' + payment.amount.toFixed(2)"></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-medium bg-success-100 text-success-800 rounded-full">Completed</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function paymentHistory() {
            return {
                stats: {
                    total_paid: 45237.89,
                    payment_count: 127
                },
                customExport: {
                    dateRange: 'all',
                    startDate: '',
                    endDate: '',
                    methods: {
                        all: true,
                        card: true,
                        ach: true,
                        check: true,
                        other: true
                    },
                    fields: {
                        transaction_id: true,
                        invoice_number: true,
                        amount: true,
                        fee: true,
                        net_amount: true
                    },
                    format: 'excel',
                    includeNotes: false,
                    groupByMonth: false,
                    includeRefunds: false
                },
                exporting: false,
                estimatedRecords: 127,
                recentPayments: [
                    { id: 1, date: '2024-12-15', invoice: 'INV-2024-0523', method: 'Credit Card', amount: 249.99 },
                    { id: 2, date: '2024-12-08', invoice: 'INV-2024-0489', method: 'ACH', amount: 1250.00 },
                    { id: 3, date: '2024-12-01', invoice: 'INV-2024-0467', method: 'Credit Card', amount: 375.50 },
                    { id: 4, date: '2024-11-24', invoice: 'INV-2024-0421', method: 'Check', amount: 850.00 },
                    { id: 5, date: '2024-11-15', invoice: 'INV-2024-0398', method: 'ACH', amount: 625.00 }
                ],
                
                toggleAllMethods() {
                    const allChecked = this.customExport.methods.all;
                    this.customExport.methods.card = allChecked;
                    this.customExport.methods.ach = allChecked;
                    this.customExport.methods.check = allChecked;
                    this.customExport.methods.other = allChecked;
                },
                
                isValidExport() {
                    return this.customExport.dateRange && this.customExport.format;
                },
                
                async quickExport(format) {
                    this.exporting = true;
                    // Simulate API call
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    window.location.href = `/billing/portal/payment-history/export?format=${format}&range=all`;
                    this.exporting = false;
                },
                
                async generateCustomExport() {
                    if (!this.isValidExport()) return;
                    
                    this.exporting = true;
                    // Build query string from custom export options
                    const params = new URLSearchParams({
                        format: this.customExport.format,
                        dateRange: this.customExport.dateRange,
                        startDate: this.customExport.startDate,
                        endDate: this.customExport.endDate,
                        includeNotes: this.customExport.includeNotes,
                        groupByMonth: this.customExport.groupByMonth,
                        includeRefunds: this.customExport.includeRefunds
                    });
                    
                    // Simulate API call
                    await new Promise(resolve => setTimeout(resolve, 2500));
                    window.location.href = `/billing/portal/payment-history/export?${params.toString()}`;
                    this.exporting = false;
                }
            }
        }
    </script>
</x-app-layout>
