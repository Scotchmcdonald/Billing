<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Pre-Flight Invoice Review') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="preFlightReview()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Success Message -->
            <div x-show="successMessage" x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3 text-lg"></i>
                    <p class="text-sm font-medium text-green-800" x-text="successMessage"></p>
                    <button @click="successMessage = ''" class="ml-auto text-green-600 hover:text-green-800">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">Total Invoices</div>
                    <div class="text-3xl font-bold text-gray-900">{{ $invoices->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">Clean (Score {{ $invoices->count() }}</div>
                    <div class="text-3xl font-bold text-green-600">{{ $invoices->where('anomaly_score', '<', 30)->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">Review Needed</div>
                    <div class="text-3xl font-bold text-yellow-600">{{ $invoices->whereBetween('anomaly_score', [30, 70])->count() }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">High Risk</div>
                    <div class="text-3xl font-bold text-red-600">{{ $invoices->where('anomaly_score', '>', 70)->count() }}</div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Bulk Actions</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            <span x-text="selectedCount" class="font-semibold text-primary-600"></span> 
                            <span>invoice(s) selected</span>
                            <span x-show="selectedCount === 0" class="ml-2 text-gray-400">← Select invoices to enable actions</span>
                        </p>
                    </div>
                    <div class="flex gap-3">
                        <button @click="bulkApprove" 
                                :disabled="selectedCount === 0"
                                :class="selectedCount > 0 ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-300 cursor-not-allowed'"
                                class="px-4 py-2 text-white rounded-lg transition-colors duration-150 flex items-center">
                            <i class="fas fa-check mr-2"></i>
                            Approve Selected
                        </button>
                        <button @click="bulkApproveAndSend" 
                                :disabled="selectedCount === 0"
                                :class="selectedCount > 0 ? 'bg-primary-600 hover:bg-primary-700' : 'bg-gray-300 cursor-not-allowed'"
                                class="px-4 py-2 text-white rounded-lg transition-colors duration-150 flex items-center">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Approve & Send Selected
                        </button>
                        <button @click="approveAllClean" 
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-150 flex items-center">
                            <i class="fas fa-check-double mr-2"></i>
                            Approve All Clean ({{ $invoices->where('anomaly_score', '<', 30)->count() }})
                        </button>
                    </div>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">
                                    <input type="checkbox" 
                                           @change="toggleAll"
                                           :checked="allSelected"
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Invoice #
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Risk Score
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Issues
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($invoices as $invoice)
                                <tr class="{{ $invoice->anomaly_score > 70 ? 'bg-red-50' : ($invoice->anomaly_score > 30 ? 'bg-yellow-50' : '') }}">
                                    <td class="px-6 py-4">
                                        <input type="checkbox" 
                                               x-model="selected"
                                               value="{{ $invoice->id }}"
                                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $invoice->invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $invoice->company->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ${{ number_format($invoice->total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($invoice->anomaly_score < 30)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ $invoice->anomaly_score }} - Clean
                                            </span>
                                        @elseif($invoice->anomaly_score < 70)
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ $invoice->anomaly_score }} - Review
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                {{ $invoice->anomaly_score }} - High Risk
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">
                                        @if($invoice->anomaly_reasons)
                                            <ul class="list-disc list-inside text-xs">
                                                @foreach(json_decode($invoice->anomaly_reasons, true) as $reason)
                                                    <li>{{ $reason }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <span class="text-green-600">No issues detected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($invoice->status === 'approved')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                Approved
                                            </span>
                                        @elseif($invoice->status === 'sent')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                Sent
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                                Draft
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button @click="viewInvoice({{ $invoice->id }})" 
                                                class="text-primary-600 hover:text-primary-900">
                                            View
                                        </button>
                                        @if($invoice->status === 'draft')
                                            <button @click="approveInvoice({{ $invoice->id }})" 
                                                    class="text-green-600 hover:text-green-900">
                                                Approve
                                            </button>
                                            <button @click="approveAndSendInvoice({{ $invoice->id }})" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                Approve & Send
                                            </button>
                                        @elseif($invoice->status === 'approved')
                                            <button @click="sendInvoice({{ $invoice->id }})" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                Send Now
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function preFlightReview() {
            return {
                selected: [],
                successMessage: '',

                get selectedCount() {
                    return this.selected.length;
                },

                get allSelected() {
                    return this.selected.length === {{ $invoices->count() }};
                },

                toggleAll(event) {
                    if (event.target.checked) {
                        this.selected = @json($invoices->pluck('id'));
                    } else {
                        this.selected = [];
                    }
                },

                async approveInvoice(id) {
                    if (!confirm('Approve this invoice? It will remain in draft status until you explicitly send it.')) return;
                    
                    const response = await fetch(`/billing/finance/pre-flight/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (response.ok) {
                        this.showSuccess('Invoice approved successfully');
                        setTimeout(() => window.location.reload(), 1500);
                    }
                },

                async approveAndSendInvoice(id) {
                    if (!confirm('Approve and immediately send this invoice to the client?')) return;
                    
                    const response = await fetch(`/billing/finance/pre-flight/${id}/approve-and-send`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (response.ok) {
                        this.showSuccess('Invoice approved and sent to client');
                        setTimeout(() => window.location.reload(), 1500);
                    }
                },

                async sendInvoice(id) {
                    if (!confirm('Send this approved invoice to the client now?')) return;
                    
                    const response = await fetch(`/billing/finance/pre-flight/${id}/send`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (response.ok) {
                        this.showSuccess('Invoice sent to client');
                        setTimeout(() => window.location.reload(), 1500);
                    }
                },

                async bulkApprove() {
                    if (!confirm(`Approve ${this.selectedCount} invoice(s)? They will remain in draft status.`)) return;
                    
                    const response = await fetch('/billing/finance/pre-flight/bulk-approve', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: this.selected })
                    });

                    if (response.ok) {
                        this.showSuccess(`${this.selectedCount} invoice(s) approved successfully`);
                        setTimeout(() => window.location.reload(), 1500);
                    }
                },

                async bulkApproveAndSend() {
                    if (!confirm(`Approve and send ${this.selectedCount} invoice(s) to clients now?`)) return;
                    
                    const response = await fetch('/billing/finance/pre-flight/bulk-approve-and-send', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ ids: this.selected })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        this.showSuccess(`${data.sent} invoice(s) approved and sent to clients`);
                        setTimeout(() => window.location.reload(), 2000);
                    }
                },

                async approveAllClean() {
                    const cleanCount = {{ $invoices->where('anomaly_score', '<', 30)->count() }};
                    if (!confirm(`Approve all ${cleanCount} clean invoices? They will remain in draft status.`)) return;
                    
                    const response = await fetch('/billing/finance/pre-flight/approve-all-clean', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    if (response.ok) {
                        this.showSuccess(`${cleanCount} clean invoice(s) approved successfully`);
                        setTimeout(() => window.location.reload(), 1500);
                    }
                },

                viewInvoice(id) {
                    window.location.href = `/billing/finance/invoices/${id}`;
                },

                showSuccess(message) {
                    this.successMessage = message;
                    setTimeout(() => {
                        this.successMessage = '';
                    }, 5000);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
