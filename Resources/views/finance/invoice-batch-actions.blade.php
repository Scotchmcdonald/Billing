@extends('layouts.app')

@section('content')
<div x-data="invoiceBatchActions()" x-init="init()" class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold leading-7 text-gray-900">
                Invoice Batch Actions
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Perform bulk operations on multiple invoices simultaneously
            </p>
        </div>

        <!-- Selection Summary Bar -->
        <div x-show="selectedInvoices.length > 0" x-transition class="mb-6 bg-primary-50 border-l-4 border-primary-600 p-4 rounded-r-lg">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="h-6 w-6 text-primary-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-sm font-medium text-primary-800">
                        <span x-text="selectedInvoices.length"></span> invoices selected
                    </p>
                </div>
                
                <div class="flex items-center space-x-3">
                    <!-- Batch Action Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Batch Action
                            <svg class="ml-2 -mr-0.5 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        
                        <div x-show="open" x-transition class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a @click.prevent="openBatchModal('mark_paid')" href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="mr-3 h-5 w-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Mark as Paid
                                </a>
                                <a @click.prevent="openBatchModal('send_reminder')" href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="mr-3 h-5 w-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    Send Payment Reminder
                                </a>
                                <a @click.prevent="openBatchModal('void')" href="#" class="flex items-center px-4 py-2 text-sm text-danger-700 hover:bg-gray-50">
                                    <svg class="mr-3 h-5 w-5 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Void Invoices
                                </a>
                                <a @click.prevent="openBatchModal('export')" href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <svg class="mr-3 h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Export Selected
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <button @click="clearSelection()" type="button" class="inline-flex items-center px-3 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Clear Selection
                    </button>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="filters.status" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="viewed">Viewed</option>
                        <option value="paid">Paid</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Date Range</label>
                    <select x-model="filters.dateRange" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">All Time</option>
                        <option value="this_month">This Month</option>
                        <option value="last_month">Last Month</option>
                        <option value="this_quarter">This Quarter</option>
                        <option value="overdue">Overdue Only</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Client</label>
                    <input type="text" x-model="filters.client" placeholder="Search clients..." class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500">
                </div>
                
                <div class="flex items-end">
                    <button @click="applyFilters()" type="button" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700">
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-3 py-3 w-12">
                            <input type="checkbox" @change="toggleSelectAll()" :checked="isAllSelected()" class="rounded text-primary-600 focus:ring-primary-500">
                        </th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="invoice in invoices" :key="invoice.id">
                        <tr class="hover:bg-gray-50" :class="{'bg-primary-50': isSelected(invoice.id)}">
                            <td class="px-3 py-4 whitespace-nowrap">
                                <input type="checkbox" :checked="isSelected(invoice.id)" @change="toggleInvoice(invoice.id)" class="rounded text-primary-600 focus:ring-primary-500">
                            </td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-primary-600">
                                <a :href="`/billing/invoices/${invoice.id}`" x-text="invoice.number"></a>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-900" x-text="invoice.client_name"></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500" x-text="invoice.date"></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900" x-text="'$' + (invoice.amount / 100).toLocaleString('en-US', {minimumFractionDigits: 2})"></td>
                            <td class="px-3 py-4 whitespace-nowrap text-center text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                      :class="{
                                          'bg-success-100 text-success-800': invoice.status === 'paid',
                                          'bg-warning-100 text-warning-800': invoice.status === 'sent' || invoice.status === 'viewed',
                                          'bg-danger-100 text-danger-800': invoice.status === 'overdue',
                                          'bg-gray-100 text-gray-800': invoice.status === 'draft'
                                      }"
                                      x-text="invoice.status.charAt(0).toUpperCase() + invoice.status.slice(1)">
                                </span>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Showing <span class="font-medium">1</span> to <span class="font-medium">{{ $invoices->count() }}</span> of <span class="font-medium">{{ $invoices->total() }}</span> invoices
            </div>
            <div>
                {{ $invoices->links() }}
            </div>
        </div>

        <!-- Batch Action Modal -->
        <div x-show="showModal" x-transition class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full" 
                             :class="{
                                 'bg-success-100': modalAction === 'mark_paid',
                                 'bg-primary-100': modalAction === 'send_reminder' || modalAction === 'export',
                                 'bg-danger-100': modalAction === 'void'
                             }">
                            <svg class="h-6 w-6" 
                                 :class="{
                                     'text-success-600': modalAction === 'mark_paid',
                                     'text-primary-600': modalAction === 'send_reminder' || modalAction === 'export',
                                     'text-danger-600': modalAction === 'void'
                                 }"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-5">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="getModalTitle()"></h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" x-text="getModalDescription()"></p>
                            </div>
                            
                            <!-- Mark as Paid Options -->
                            <div x-show="modalAction === 'mark_paid'" class="mt-4 space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 text-left mb-1">Payment Method</label>
                                    <select x-model="batchPaymentMethod" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="check">Check</option>
                                        <option value="cash">Cash</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 text-left mb-1">Payment Date</label>
                                    <input type="date" x-model="batchPaymentDate" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                </div>
                            </div>
                            
                            <!-- Void Confirmation -->
                            <div x-show="modalAction === 'void'" class="mt-4">
                                <label class="block text-sm font-medium text-gray-700 text-left mb-2">
                                    Type <code class="bg-danger-100 px-2 py-1 rounded font-mono text-danger-800">VOID</code> to confirm:
                                </label>
                                <input type="text" x-model="voidConfirmation" class="block w-full rounded-md border-danger-300 shadow-sm focus:border-danger-500 focus:ring-danger-500" placeholder="Type here...">
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                        <button @click="executeBatchAction()" :disabled="!canExecute()" type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 sm:col-start-2 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{
                                    'bg-success-600 hover:bg-success-700 focus:ring-success-500': modalAction === 'mark_paid',
                                    'bg-primary-600 hover:bg-primary-700 focus:ring-primary-500': modalAction === 'send_reminder' || modalAction === 'export',
                                    'bg-danger-600 hover:bg-danger-700 focus:ring-danger-500': modalAction === 'void'
                                }">
                            <span x-show="!processing">Confirm</span>
                            <span x-show="processing">Processing...</span>
                        </button>
                        <button @click="closeModal()" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function invoiceBatchActions() {
    return {
        selectedInvoices: [],
        invoices: @json($invoices->items()),
        filters: {
            status: '',
            dateRange: 'all',
            client: ''
        },
        showModal: false,
        modalAction: '',
        processing: false,
        batchPaymentMethod: 'check',
        batchPaymentDate: new Date().toISOString().split('T')[0],
        voidConfirmation: '',
        
        init() {
            // Initialize
        },
        
        toggleInvoice(invoiceId) {
            const index = this.selectedInvoices.indexOf(invoiceId);
            if (index > -1) {
                this.selectedInvoices.splice(index, 1);
            } else {
                this.selectedInvoices.push(invoiceId);
            }
        },
        
        isSelected(invoiceId) {
            return this.selectedInvoices.includes(invoiceId);
        },
        
        toggleSelectAll() {
            if (this.isAllSelected()) {
                this.selectedInvoices = [];
            } else {
                this.selectedInvoices = this.invoices.map(inv => inv.id);
            }
        },
        
        isAllSelected() {
            return this.invoices.length > 0 && this.selectedInvoices.length === this.invoices.length;
        },
        
        clearSelection() {
            this.selectedInvoices = [];
        },
        
        openBatchModal(action) {
            this.modalAction = action;
            this.showModal = true;
        },
        
        closeModal() {
            this.showModal = false;
            this.voidConfirmation = '';
            this.processing = false;
        },
        
        getModalTitle() {
            const titles = {
                'mark_paid': 'Mark Invoices as Paid',
                'send_reminder': 'Send Payment Reminders',
                'void': 'Void Selected Invoices',
                'export': 'Export Selected Invoices'
            };
            return titles[this.modalAction] || 'Batch Action';
        },
        
        getModalDescription() {
            const descriptions = {
                'mark_paid': `You are about to mark ${this.selectedInvoices.length} invoice(s) as paid. Please provide payment details.`,
                'send_reminder': `Send payment reminder emails to ${this.selectedInvoices.length} client(s) with outstanding invoices.`,
                'void': `This will permanently void ${this.selectedInvoices.length} invoice(s). This action cannot be undone.`,
                'export': `Export ${this.selectedInvoices.length} invoice(s) to Excel.`
            };
            return descriptions[this.modalAction] || '';
        },
        
        canExecute() {
            if (this.processing) return false;
            if (this.modalAction === 'void') {
                return this.voidConfirmation === 'VOID';
            }
            return true;
        },
        
        async executeBatchAction() {
            this.processing = true;
            
            try {
                const response = await fetch('{{ route("billing.finance.invoices.batch-action") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        action: this.modalAction,
                        invoice_ids: this.selectedInvoices,
                        payment_method: this.batchPaymentMethod,
                        payment_date: this.batchPaymentDate
                    })
                });
                
                if (response.ok) {
                    const result = await response.json();
                    alert(`Success: ${result.message}`);
                    window.location.reload();
                } else {
                    alert('Failed to execute batch action');
                }
            } catch (error) {
                alert('An error occurred');
            } finally {
                this.processing = false;
                this.closeModal();
            }
        },
        
        applyFilters() {
            // Apply filters logic
            window.location.href = window.location.pathname + '?' + new URLSearchParams(this.filters).toString();
        }
    }
}
</script>
@endpush
@endsection
