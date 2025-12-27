<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Contract Management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage subscription contracts and renewals</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Key Metrics (Control Tower Pattern) -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white shadow-sm rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase">Expiring Soon</p>
                        <p class="text-3xl font-bold text-warning-600 mt-2">{{ $stats['expiring_soon'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-warning-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Next 90 days</p>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase">Auto-Renewing</p>
                        <p class="text-3xl font-bold text-success-600 mt-2">{{ $stats['auto_renewing'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-success-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Set to auto-renew</p>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase">Churned (30d)</p>
                        <p class="text-3xl font-bold text-danger-600 mt-2">{{ $stats['churned_30d'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-danger-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Last 30 days</p>
            </div>

            <div class="bg-white shadow-sm rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-600 uppercase">Churn Rate</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['churn_rate'], 1) }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Trailing 12 months</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="company" class="block text-xs font-semibold text-gray-700 mb-1">Company</label>
                    <input 
                        type="text" 
                        name="company" 
                        id="company"
                        value="{{ request('company') }}"
                        placeholder="Search company..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>
                <div class="min-w-[150px]">
                    <label for="status" class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                    <select 
                        name="status" 
                        id="status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="expiring" {{ request('status') == 'expiring' ? 'selected' : '' }}>Expiring Soon</option>
                        <option value="churned" {{ request('status') == 'churned' ? 'selected' : '' }}>Churned</option>
                    </select>
                </div>
                <div class="min-w-[150px]">
                    <label for="renewal_status" class="block text-xs font-semibold text-gray-700 mb-1">Renewal Status</label>
                    <select 
                        name="renewal_status" 
                        id="renewal_status"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="">All</option>
                        <option value="auto_renew" {{ request('renewal_status') == 'auto_renew' ? 'selected' : '' }}>Auto-Renew</option>
                        <option value="manual" {{ request('renewal_status') == 'manual' ? 'selected' : '' }}>Manual</option>
                        <option value="pending" {{ request('renewal_status') == 'pending' ? 'selected' : '' }}>Pending Decision</option>
                    </select>
                </div>
                <button 
                    type="submit" 
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500"
                >
                    Apply Filters
                </button>
            </form>
        </div>

        <!-- Contracts Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Company</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Service</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">MRR</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Contract Dates</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Days to Expiry</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Renewal Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($contracts as $contract)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('billing.companies.show', $contract->company) }}" class="font-semibold text-primary-600 hover:text-primary-700">
                                    {{ $contract->company->name }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $contract->service_name }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">${{ number_format($contract->price / 100, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <div>{{ $contract->contract_start_date->format('M d, Y') }}</div>
                                <div class="text-xs">to {{ $contract->contract_end_date->format('M d, Y') }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $daysToExpiry = now()->diffInDays($contract->contract_end_date, false);
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $daysToExpiry < 30 ? 'bg-danger-100 text-danger-700' : '' }}
                                    {{ $daysToExpiry >= 30 && $daysToExpiry < 90 ? 'bg-warning-100 text-warning-700' : '' }}
                                    {{ $daysToExpiry >= 90 ? 'bg-success-100 text-success-700' : '' }}
                                ">
                                    {{ $daysToExpiry > 0 ? $daysToExpiry . ' days' : 'Expired' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold
                                    {{ $contract->renewal_status === 'auto_renew' ? 'bg-success-100 text-success-700' : '' }}
                                    {{ $contract->renewal_status === 'pending' ? 'bg-warning-100 text-warning-700' : '' }}
                                    {{ $contract->renewal_status === 'manual' ? 'bg-gray-100 text-gray-700' : '' }}
                                ">
                                    {{ ucfirst(str_replace('_', ' ', $contract->renewal_status ?? 'manual')) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center space-x-2">
                                    <form method="POST" action="{{ route('billing.contracts.send-reminder', $contract) }}" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-primary-600 hover:text-primary-700 text-xs font-semibold"
                                            title="Send Renewal Reminder"
                                        >
                                            Remind
                                        </button>
                                    </form>
                                    <span class="text-gray-300">|</span>
                                    <form method="POST" action="{{ route('billing.contracts.renew', $contract) }}" class="inline">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-success-600 hover:text-success-700 text-xs font-semibold"
                                            title="Mark as Renewed"
                                        >
                                            Renew
                                        </button>
                                    </form>
                                    <span class="text-gray-300">|</span>
                                    <form method="POST" action="{{ route('billing.contracts.churn', $contract) }}" class="inline" 
                                          onsubmit="return confirm('Are you sure this client has churned?')">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-danger-600 hover:text-danger-700 text-xs font-semibold"
                                            title="Mark as Churned"
                                        >
                                            Churn
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-4 text-sm text-gray-600">No contracts found</p>
                                <p class="mt-1 text-xs text-gray-500">Try adjusting your filters</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($contracts->hasPages())
            <div class="mt-6">
                {{ $contracts->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
