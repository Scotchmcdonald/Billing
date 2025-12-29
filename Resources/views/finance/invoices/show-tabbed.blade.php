@extends('billing::layouts.master')

@section('module-content')
<div class="max-w-7xl mx-auto">
    <!-- Page Header -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Invoice #{{ $invoice->invoice_number }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ $invoice->company->name }} • Issued {{ $invoice->issue_date->format('M d, Y') }}
                    @if($invoice->due_date)
                        • Due {{ $invoice->due_date->format('M d, Y') }}
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Status Badge -->
                @if($invoice->status === 'paid')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-success-100 text-success-800">
                        <i class="fas fa-check-circle mr-1"></i> Paid
                    </span>
                @elseif($invoice->status === 'disputed')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-danger-100 text-danger-800">
                        <i class="fas fa-flag mr-1"></i> Disputed
                    </span>
                @elseif($invoice->status === 'overdue')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-warning-100 text-warning-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                    </span>
                @elseif($invoice->status === 'sent')
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-info-100 text-info-800">
                        <i class="fas fa-paper-plane mr-1"></i> Sent
                    </span>
                @else
                    <span class="px-3 py-1 text-sm font-semibold rounded-full bg-gray-100 text-gray-800">
                        <i class="fas fa-file mr-1"></i> Draft
                    </span>
                @endif

                <!-- Actions -->
                @if($invoice->status === 'draft')
                    <a href="{{ route('billing.finance.pre-flight-enhanced') }}" 
                       class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Review & Send
                    </a>
                @elseif(in_array($invoice->status, ['sent', 'overdue']))
                    <a href="{{ route('billing.finance.invoices.dispute.form', $invoice) }}" 
                       class="inline-flex items-center px-4 py-2 bg-danger-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-danger-700 focus:bg-danger-700 active:bg-danger-900 focus:outline-none focus:ring-2 focus:ring-danger-500 focus:ring-offset-2 transition">
                        <i class="fas fa-flag mr-2"></i>
                        Dispute
                    </a>
                @endif
                
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                    <i class="fas fa-print mr-2"></i>
                    Print
                </button>
                
                <a href="{{ route('billing.finance.invoices.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </div>

    <!-- Tabbed Interface -->
    @php
        $disputeCount = $invoice->disputes()->where('status', 'open')->count();
        $timelineCount = $invoice->activities()->count() ?? 0;
    @endphp
    
    <x-billing::tabs :active="request()->query('tab', 'details')" :tabs="[
        ['id' => 'details', 'label' => 'Invoice Details', 'icon' => 'file-invoice-dollar'],
        ['id' => 'line-items', 'label' => 'Line Items', 'icon' => 'list'],
        ['id' => 'timeline', 'label' => 'Activity Timeline', 'icon' => 'history', 'count' => $timelineCount],
        ['id' => 'disputes', 'label' => 'Disputes', 'icon' => 'flag', 'count' => $disputeCount],
        ['id' => 'payments', 'label' => 'Payments', 'icon' => 'credit-card'],
    ]">
        <!-- Details Tab -->
        <x-billing::tab-panel id="details">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Main Invoice Info -->
                <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Invoice Information</h3>
                    
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">From</h4>
                            <p class="font-semibold">Your Company Name</p>
                            <p class="text-sm text-gray-600">123 Business St</p>
                            <p class="text-sm text-gray-600">City, ST 12345</p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Bill To</h4>
                            <p class="font-semibold">{{ $invoice->company->name }}</p>
                            <p class="text-sm text-gray-600">{{ $invoice->company->address }}</p>
                            <p class="text-sm text-gray-600">{{ $invoice->company->city }}, {{ $invoice->company->state }} {{ $invoice->company->zip }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <dl class="grid grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Invoice Number</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $invoice->invoice_number }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Issue Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $invoice->issue_date->format('F d, Y') }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $invoice->due_date?->format('F d, Y') ?? 'Upon receipt' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Terms</dt>
                                <dd class="mt-1 text-sm text-gray-900">Net {{ $invoice->payment_terms ?? 30 }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
                
                <!-- Amount Summary -->
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Amount Due</h3>
                    
                    <dl class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Subtotal</dt>
                            <dd class="font-medium">${{ number_format($invoice->subtotal, 2) }}</dd>
                        </div>
                        @if($invoice->tax_amount > 0)
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-600">Tax</dt>
                            <dd class="font-medium">${{ number_format($invoice->tax_amount, 2) }}</dd>
                        </div>
                        @endif
                        @if($invoice->discount_amount > 0)
                        <div class="flex justify-between text-sm text-success-600">
                            <dt>Discount</dt>
                            <dd class="font-medium">-${{ number_format($invoice->discount_amount, 2) }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between text-lg font-bold pt-3 border-t border-gray-200">
                            <dt>Total</dt>
                            <dd class="text-primary-600">${{ number_format($invoice->total, 2) }}</dd>
                        </div>
                        @if($invoice->amount_paid > 0)
                        <div class="flex justify-between text-sm text-success-600">
                            <dt>Paid</dt>
                            <dd class="font-medium">-${{ number_format($invoice->amount_paid, 2) }}</dd>
                        </div>
                        <div class="flex justify-between text-lg font-bold pt-2 border-t border-gray-200">
                            <dt>Balance Due</dt>
                            <dd class="text-danger-600">${{ number_format($invoice->total - $invoice->amount_paid, 2) }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </x-billing::tab-panel>

        <!-- Line Items Tab -->
        <x-billing::tab-panel id="line-items">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($invoice->lineItems ?? [] as $item)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $item->description }}</div>
                                    @if($item->notes)
                                    <div class="text-xs text-gray-500 mt-1">{{ $item->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($item->rate, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2 text-gray-300"></i>
                                    <p>No line items found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-billing::tab-panel>

        <!-- Timeline Tab -->
        <x-billing::tab-panel id="timeline" lazy="true">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Activity History</h3>
                <x-billing::invoice-timeline :invoice="$invoice" />
            </div>
        </x-billing::tab-panel>

        <!-- Disputes Tab -->
        <x-billing::tab-panel id="disputes" lazy="true">
            <div class="space-y-4">
                @forelse($invoice->disputes ?? [] as $dispute)
                <div class="bg-white shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-medium text-gray-900">{{ $dispute->reason }}</h4>
                            <p class="text-sm text-gray-500 mt-1">Submitted {{ $dispute->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                        <span class="px-3 py-1 text-sm font-semibold rounded-full 
                                     {{ $dispute->status === 'resolved' ? 'bg-success-100 text-success-800' : 'bg-warning-100 text-warning-800' }}">
                            {{ ucfirst($dispute->status) }}
                        </span>
                    </div>
                    <p class="text-gray-700 mb-4">{{ $dispute->explanation }}</p>
                    
                    @if($dispute->attachments && $dispute->attachments->count() > 0)
                    <div class="border-t border-gray-200 pt-4">
                        <h5 class="text-sm font-medium text-gray-700 mb-2">Attachments</h5>
                        <div class="space-y-2">
                            @foreach($dispute->attachments as $attachment)
                            <a href="{{ $attachment->file_path }}" target="_blank" class="flex items-center text-sm text-primary-600 hover:text-primary-800">
                                <i class="fas fa-file mr-2"></i>
                                {{ $attachment->file_name }}
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
                @empty
                <div class="bg-white shadow-sm sm:rounded-lg p-12 text-center">
                    <i class="fas fa-check-circle text-6xl text-success-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Disputes</h3>
                    <p class="text-gray-500">This invoice has no disputes on record.</p>
                </div>
                @endforelse
            </div>
        </x-billing::tab-panel>

        <!-- Payments Tab -->
        <x-billing::tab-panel id="payments" lazy="true">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Method</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reference</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($invoice->payments ?? [] as $payment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $payment->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <i class="fas fa-{{ $payment->method === 'card' ? 'credit-card' : 'money-check-alt' }} mr-2"></i>
                                    {{ ucfirst($payment->method) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->reference }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-success-600">
                                    ${{ number_format($payment->amount, 2) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-wallet text-4xl mb-2 text-gray-300"></i>
                                    <p>No payments recorded</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-billing::tab-panel>
    </x-billing::tabs>
</div>

<style>
    @media print {
        .no-print, nav, button, .border-b { display: none !important; }
        #details { display: block !important; }
    }
</style>
@endsection
