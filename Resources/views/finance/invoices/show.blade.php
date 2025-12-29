<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Invoice #{{ $invoice->invoice_number }}
            </h2>
            <div class="flex items-center gap-3">
                @if($invoice->status === 'draft')
                    <a href="{{ route('billing.finance.pre-flight-enhanced') }}" 
                       class="px-4 py-2 bg-primary-600 text-white rounded-md hover:bg-primary-700">
                        Review & Send
                    </a>
                @elseif($invoice->status === 'sent' || $invoice->status === 'overdue')
                    <a href="{{ route('billing.finance.invoices.dispute.form', $invoice) }}" 
                       class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        <i class="fas fa-flag mr-2"></i>
                        Dispute Invoice
                    </a>
                @endif
                <a href="{{ route('billing.finance.invoices') }}" 
                   class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100">
                    Back to Invoices
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Main Content (Left Column - 2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- Invoice Header -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-6">
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $invoice->company->name }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ $invoice->company->address }}</p>
                                    <p class="text-sm text-gray-500">{{ $invoice->company->city }}, {{ $invoice->company->state }} {{ $invoice->company->zip }}</p>
                                </div>
                                <div class="text-right">
                                    @if($invoice->status === 'paid')
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                            Paid
                                        </span>
                                    @elseif($invoice->status === 'disputed')
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                            Disputed
                                        </span>
                                    @elseif($invoice->status === 'overdue')
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-orange-100 text-orange-800">
                                            Overdue
                                        </span>
                                    @elseif($invoice->status === 'sent')
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">
                                            Sent
                                        </span>
                                    @else
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                                            {{ ucfirst($invoice->status) }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-6 mb-6 pb-6 border-b border-gray-200">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Invoice Date</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->invoice_date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Due Date</p>
                                    <p class="font-semibold text-gray-900">{{ $invoice->due_date->format('M d, Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Invoice Period</p>
                                    <p class="font-semibold text-gray-900">
                                        {{ $invoice->period_start->format('M d') }} - {{ $invoice->period_end->format('M d') }}
                                    </p>
                                </div>
                            </div>

                            <!-- Line Items -->
                            <table class="min-w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                                        <th class="py-3 text-right text-xs font-medium text-gray-500 uppercase">Rate</th>
                                        <th class="py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($invoice->lineItems as $item)
                                        <tr>
                                            <td class="py-3 text-sm text-gray-900">{{ $item->description }}</td>
                                            <td class="py-3 text-sm text-right text-gray-900">{{ $item->quantity }}</td>
                                            <td class="py-3 text-sm text-right text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                                            <td class="py-3 text-sm text-right font-semibold text-gray-900">${{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="border-t-2 border-gray-300">
                                        <td colspan="3" class="py-3 text-right text-sm font-semibold text-gray-900">Subtotal</td>
                                        <td class="py-3 text-right text-sm font-semibold text-gray-900">${{ number_format($invoice->subtotal, 2) }}</td>
                                    </tr>
                                    @if($invoice->tax > 0)
                                        <tr>
                                            <td colspan="3" class="py-2 text-right text-sm text-gray-700">Tax</td>
                                            <td class="py-2 text-right text-sm text-gray-900">${{ number_format($invoice->tax, 2) }}</td>
                                        </tr>
                                    @endif
                                    <tr class="border-t border-gray-200">
                                        <td colspan="3" class="py-3 text-right text-lg font-bold text-gray-900">Total</td>
                                        <td class="py-3 text-right text-2xl font-bold text-gray-900">${{ number_format($invoice->total, 2) }}</td>
                                    </tr>
                                    @if($invoice->amount_paid > 0)
                                        <tr>
                                            <td colspan="3" class="py-2 text-right text-sm text-gray-700">Amount Paid</td>
                                            <td class="py-2 text-right text-sm text-green-600">-${{ number_format($invoice->amount_paid, 2) }}</td>
                                        </tr>
                                        <tr class="border-t border-gray-200">
                                            <td colspan="3" class="py-3 text-right text-base font-semibold text-gray-900">Balance Due</td>
                                            <td class="py-3 text-right text-xl font-bold text-gray-900">${{ number_format($invoice->balance_due, 2) }}</td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <!-- Activity Timeline -->
                    @include('billing::components.invoice-timeline', ['activities' => $activities])
                </div>

                <!-- Sidebar (Right Column - 1/3 width) -->
                <div class="space-y-6">
                    
                    <!-- Quick Stats -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">Quick Stats</h4>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-xs text-gray-500">Days Since Sent</dt>
                                <dd class="text-lg font-bold text-gray-900">
                                    {{ $invoice->sent_at ? $invoice->sent_at->diffInDays(now()) : 'Not sent' }}
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Times Viewed</dt>
                                <dd class="text-lg font-bold text-gray-900">{{ $invoice->view_count ?? 0 }}</dd>
                            </div>
                            @if($invoice->last_viewed_at)
                                <div>
                                    <dt class="text-xs text-gray-500">Last Viewed</dt>
                                    <dd class="text-sm font-semibold text-gray-900">{{ $invoice->last_viewed_at->diffForHumans() }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>

                    <!-- Dispute Info (if disputed) -->
                    @if($invoice->status === 'disputed' && $invoice->dispute)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                <h4 class="text-sm font-semibold text-red-900">Dispute Information</h4>
                            </div>
                            <dl class="space-y-2 text-sm">
                                <div>
                                    <dt class="text-red-700 font-medium">Reason</dt>
                                    <dd class="text-red-900">{{ ucwords(str_replace('_', ' ', $invoice->dispute->reason)) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-red-700 font-medium">Disputed Amount</dt>
                                    <dd class="text-red-900 font-bold">${{ number_format($invoice->dispute->disputed_amount, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-red-700 font-medium">Filed</dt>
                                    <dd class="text-red-900">{{ $invoice->dispute->created_at->format('M d, Y g:i A') }}</dd>
                                </div>
                            </dl>
                            @if($invoice->dunning_paused)
                                <div class="mt-3 pt-3 border-t border-red-300">
                                    <p class="text-xs text-red-700">
                                        <i class="fas fa-pause-circle mr-1"></i>
                                        Collections paused during review
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Payment Info (if paid) -->
                    @if($invoice->status === 'paid')
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                <h4 class="text-sm font-semibold text-green-900">Payment Received</h4>
                            </div>
                            <dl class="space-y-2 text-sm">
                                <div>
                                    <dt class="text-green-700 font-medium">Amount</dt>
                                    <dd class="text-green-900 font-bold">${{ number_format($invoice->amount_paid, 2) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-green-700 font-medium">Date</dt>
                                    <dd class="text-green-900">{{ $invoice->paid_at->format('M d, Y g:i A') }}</dd>
                                </div>
                                @if($invoice->payment_method)
                                    <div>
                                        <dt class="text-green-700 font-medium">Method</dt>
                                        <dd class="text-green-900">{{ ucfirst($invoice->payment_method) }}</dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h4 class="text-sm font-semibold text-gray-900 mb-4">Actions</h4>
                        <div class="space-y-2">
                            <a href="#" class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                                <i class="fas fa-download mr-2"></i>
                                Download PDF
                            </a>
                            <a href="#" class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                                <i class="fas fa-envelope mr-2"></i>
                                Email Invoice
                            </a>
                            @if($invoice->status !== 'paid')
                                <a href="#" class="block w-full px-4 py-2 text-center border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
                                    <i class="fas fa-redo mr-2"></i>
                                    Send Reminder
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
