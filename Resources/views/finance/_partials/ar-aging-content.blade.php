<!-- AR Aging Content -->
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Accounts Receivable Aging</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track outstanding invoices and analyze collection performance</p>
    </div>

    <!-- AR Aging Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @foreach($arAging ?? [] as $bucket => $data)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-500 mb-2">{{ $bucket }} Days</h3>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($data['amount'], 2) }}</p>
            <p class="text-xs text-gray-500 mt-2">
                {{ $data['count'] }} invoices
            </p>
        </div>
        @endforeach
    </div>

    <!-- AR Aging Detailed Table -->
    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Issue Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Days Overdue</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($overdueInvoices ?? [] as $invoice)
                    @php
                        $daysOverdue = \Carbon\Carbon::parse($invoice->due_date)->diffInDays(now());
                        $bgClass = $daysOverdue <= 30 ? 'bg-yellow-100 text-yellow-800' : ($daysOverdue <= 60 ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800');
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-primary-600">
                            <a href="{{ route('billing.finance.invoices.show', $invoice->id) }}" class="hover:text-primary-800">{{ $invoice->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $invoice->company->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $bgClass }}">
                                {{ number_format($daysOverdue, 0) }} days
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900">${{ number_format($invoice->total, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button class="text-primary-600 hover:text-primary-900 mr-3">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button class="text-gray-600 hover:text-gray-900">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">No overdue invoices found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
