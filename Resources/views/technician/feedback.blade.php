<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Time Entries') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8" 
                 x-data="{ 
                     animateIn: false,
                     mounted() { 
                         setTimeout(() => this.animateIn = true, 100); 
                     } 
                 }" 
                 x-init="mounted()">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 transition-all duration-300 hover:shadow-md"
                     :class="animateIn ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-sm font-medium text-gray-500">Total Hours</div>
                        <i class="fas fa-clock text-gray-400"></i>
                    </div>
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($summary['total_hours'], 1) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">Pending</div>
                    <div class="text-3xl font-bold text-gray-600">{{ number_format($summary['pending_hours'], 1) }}</div>
                    <div class="text-xs text-gray-500 mt-1">${{ number_format($summary['pending_value'], 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">Billed</div>
                    <div class="text-3xl font-bold text-blue-600">{{ number_format($summary['billed_hours'], 1) }}</div>
                    <div class="text-xs text-gray-500 mt-1">${{ number_format($summary['billed_value'], 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">Paid</div>
                    <div class="text-3xl font-bold text-green-600">{{ number_format($summary['paid_hours'], 1) }}</div>
                    <div class="text-xs text-gray-500 mt-1">${{ number_format($summary['paid_value'], 2) }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 mb-1">Disputed</div>
                    <div class="text-3xl font-bold text-red-600">{{ number_format($summary['disputed_hours'], 1) }}</div>
                    <div class="text-xs text-gray-500 mt-1">${{ number_format($summary['disputed_value'], 2) }}</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                        <select name="range" class="w-full rounded-md border-gray-300">
                            <option value="week" {{ request('range') === 'week' ? 'selected' : '' }}>This Week</option>
                            <option value="month" {{ request('range') === 'month' ? 'selected' : '' }}>This Month</option>
                            <option value="quarter" {{ request('range') === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            <option value="all" {{ request('range') === 'all' ? 'selected' : '' }}>All Time</option>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full rounded-md border-gray-300">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="billed" {{ request('status') === 'billed' ? 'selected' : '' }}>Billed</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="disputed" {{ request('status') === 'disputed' ? 'selected' : '' }}>Disputed</option>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-md">
                        Filter
                    </button>
                </form>
            </div>

            <!-- Time Entries Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Company
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Description
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hours
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Value
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Invoice
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($timeEntries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $entry->date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $entry->company->name }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ Str::limit($entry->description, 60) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ number_format($entry->hours, 2) }}h
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        ${{ number_format($entry->billable_amount, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($entry->billing_status === 'pending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-clock text-gray-500 mr-1"></i>
                                                Pending Review
                                            </span>
                                        @elseif($entry->billing_status === 'billed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                <i class="fas fa-file-invoice text-blue-500 mr-1"></i>
                                                Billed
                                            </span>
                                        @elseif($entry->billing_status === 'paid')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle text-green-500 mr-1"></i>
                                                Paid
                                            </span>
                                        @elseif($entry->billing_status === 'disputed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-exclamation-triangle text-red-500 mr-1"></i>
                                                Disputed
                                            </span>
                                        @endif

                                        @if($entry->status_changed_at)
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $entry->status_changed_at->diffForHumans() }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($entry->invoice_id)
                                            <a href="{{ route('billing.finance.invoices.show', $entry->invoice_id) }}" 
                                               class="text-primary-600 hover:text-primary-900 font-medium">
                                                #{{ $entry->invoice->invoice_number }}
                                            </a>
                                            @if($entry->invoice->status === 'paid')
                                                <div class="text-xs text-gray-500">
                                                    Paid {{ $entry->invoice->paid_at->format('M d') }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Not invoiced</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No time entries found for this period
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($timeEntries->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $timeEntries->links() }}
                    </div>
                @endif
            </div>

            <!-- Recent Status Changes -->
            @if($recentChanges->isNotEmpty())
                <div class="mt-8 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Status Changes</h3>
                    <div class="space-y-3">
                        @foreach($recentChanges as $change)
                            <div class="flex items-start p-3 rounded-lg bg-gray-50">
                                <div class="flex-shrink-0">
                                    @if($change->new_status === 'billed')
                                        <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                            <i class="fas fa-file-invoice text-blue-600"></i>
                                        </div>
                                    @elseif($change->new_status === 'paid')
                                        <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                            <i class="fas fa-check-circle text-green-600"></i>
                                        </div>
                                    @elseif($change->new_status === 'disputed')
                                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center">
                                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ $change->hours }}h - {{ $change->company->name }}
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $change->description }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Status changed to <span class="font-semibold">{{ ucfirst($change->new_status) }}</span>
                                        {{ $change->status_changed_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="text-sm font-semibold text-gray-900">
                                    ${{ number_format($change->billable_amount, 2) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
