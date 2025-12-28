<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Retainer Details</h1>
                <p class="text-sm text-gray-600 mt-1">{{ $retainer->company->name }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a 
                    href="{{ route('billing.finance.retainers.index') }}" 
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-gray-500"
                >
                    Back to List
                </a>
            </div>
        </div>

        <!-- Status Banner -->
        @if($retainer->status === 'depleted')
            <div class="mb-6 bg-warning-50 border border-warning-200 text-warning-700 px-4 py-3 rounded-lg">
                <p class="font-semibold">⚠️ This retainer is depleted</p>
                <p class="text-sm mt-1">No hours remaining. Consider selling a new retainer to this client.</p>
            </div>
        @elseif($retainer->status === 'expired')
            <div class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded-lg">
                <p class="font-semibold">❌ This retainer has expired</p>
                <p class="text-sm mt-1">Expired on {{ $retainer->expires_at->format('M d, Y') }}</p>
            </div>
        @elseif($retainer->hours_remaining < 5)
            <div class="mb-6 bg-warning-50 border border-warning-200 text-warning-700 px-4 py-3 rounded-lg">
                <p class="font-semibold">⚠️ Low Balance Warning</p>
                <p class="text-sm mt-1">Only {{ $retainer->hours_remaining }} hours remaining</p>
            </div>
        @endif

        <!-- Balance Card -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Balance</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <span class="text-sm text-gray-600">Hours Remaining</span>
                    <p class="text-3xl font-bold text-primary-600">{{ number_format($retainer->hours_remaining, 2) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Hours Purchased</span>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($retainer->hours_purchased, 2) }}</p>
                </div>
                <div>
                    <span class="text-sm text-gray-600">Hours Used</span>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($retainer->hours_used, 2) }}</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                    <span>Usage Progress</span>
                    <span>{{ number_format(($retainer->hours_used / $retainer->hours_purchased) * 100, 1) }}% Used</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-3">
                    <div 
                        class="h-3 rounded-full {{ $retainer->hours_remaining < 5 ? 'bg-warning-500' : 'bg-primary-600' }}"
                        style="width: {{ min(($retainer->hours_used / $retainer->hours_purchased) * 100, 100) }}%"
                    ></div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm pt-4 border-t border-gray-200">
                <div>
                    <span class="text-gray-600">Status:</span>
                    <span class="ml-2 px-2 py-1 rounded text-xs font-semibold
                        {{ $retainer->status === 'active' ? 'bg-success-100 text-success-700' : '' }}
                        {{ $retainer->status === 'depleted' ? 'bg-warning-100 text-warning-700' : '' }}
                        {{ $retainer->status === 'expired' ? 'bg-danger-100 text-danger-700' : '' }}
                    ">
                        {{ ucfirst($retainer->status) }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600">Purchased:</span>
                    <span class="text-gray-900">{{ $retainer->created_at->format('M d, Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Expires:</span>
                    <span class="text-gray-900">{{ $retainer->expires_at ? $retainer->expires_at->format('M d, Y') : 'No expiration' }}</span>
                </div>
            </div>
        </div>

        <!-- Usage History -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Usage History</h2>
                <button 
                    type="button"
                    class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500"
                    onclick="alert('Add Hours modal would open here')"
                >
                    Add Hours
                </button>
            </div>

            @if($retainer->usageHistory && $retainer->usageHistory->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Ticket</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Technician</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase">Hours</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($retainer->usageHistory as $entry)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry->date->format('M d, Y') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($entry->ticket_number)
                                            <a href="#" class="text-primary-600 hover:text-primary-700">#{{ $entry->ticket_number }}</a>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry->description }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry->technician_name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">{{ number_format($entry->hours, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="mt-4 text-sm text-gray-600">No usage history yet</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
