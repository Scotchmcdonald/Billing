<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Executive Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">High-level financial overview and key metrics</p>
        </div>

        <!-- Key Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- MRR Card -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">MRR</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            ${{ number_format($metrics['mrr'] / 100, 0) }}
                        </p>
                        @if(isset($metrics['mrr_change']))
                            <p class="mt-1 text-sm {{ $metrics['mrr_change'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                                {{ $metrics['mrr_change'] >= 0 ? '↑' : '↓' }} 
                                {{ abs($metrics['mrr_change']) }}% from last month
                            </p>
                        @endif
                    </div>
                    <div class="p-3 bg-primary-50 rounded-lg">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- AR Aging Card -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Outstanding AR</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            ${{ number_format($metrics['total_ar'] / 100, 0) }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $metrics['overdue_count'] }} overdue invoices
                        </p>
                    </div>
                    <div class="p-3 bg-warning-50 rounded-lg">
                        <svg class="w-8 h-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Clients Card -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Active Clients</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ $metrics['active_clients'] }}
                        </p>
                        @if(isset($metrics['at_risk_clients']))
                            <p class="mt-1 text-sm text-danger-600">
                                {{ $metrics['at_risk_clients'] }} at risk
                            </p>
                        @endif
                    </div>
                    <div class="p-3 bg-success-50 rounded-lg">
                        <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Churn Rate Card -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Churn Rate</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900">
                            {{ number_format($metrics['churn_rate'], 1) }}%
                        </p>
                        <p class="mt-1 text-sm text-gray-600">
                            Last 90 days
                        </p>
                    </div>
                    <div class="p-3 {{ $metrics['churn_rate'] > 5 ? 'bg-danger-50' : 'bg-gray-50' }} rounded-lg">
                        <svg class="w-8 h-8 {{ $metrics['churn_rate'] > 5 ? 'text-danger-600' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Clients by Revenue -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Top 10 Clients by Revenue (All Time)</h3>
                <div class="space-y-3">
                    @foreach($metrics['top_clients'] as $client)
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $client['name'] }}</p>
                                <div class="mt-1 w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-primary-600 h-2 rounded-full" style="width: {{ ($client['revenue'] / $metrics['top_clients'][0]['revenue']) * 100 }}%"></div>
                                </div>
                            </div>
                            <span class="ml-4 text-sm font-semibold text-gray-900">
                                ${{ number_format($client['revenue'] / 100, 0) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Revenue Trend -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend (Last 12 Months)</h3>
                <div class="h-64 flex items-end justify-between space-x-2">
                    @foreach($metrics['revenue_trend'] as $month => $amount)
                        <div class="flex-1 flex flex-col items-center">
                            <div class="w-full bg-primary-600 rounded-t" style="height: {{ ($amount / max($metrics['revenue_trend'])) * 100 }}%"></div>
                            <span class="text-xs text-gray-500 mt-2">{{ $month }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Alert Cards -->
        @if(count($alerts) > 0)
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Critical Alerts</h3>
                <div class="space-y-4">
                    @foreach($alerts as $alert)
                        <div class="bg-{{ $alert['severity'] }}-50 border-l-4 border-{{ $alert['severity'] }}-400 p-4 rounded">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-{{ $alert['severity'] }}-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-{{ $alert['severity'] }}-800">{{ $alert['title'] }}</p>
                                    <p class="mt-1 text-sm text-{{ $alert['severity'] }}-700">{{ $alert['message'] }}</p>
                                    @if(isset($alert['action_url']))
                                        <a href="{{ $alert['action_url'] }}" class="mt-2 inline-block text-sm text-{{ $alert['severity'] }}-800 underline hover:text-{{ $alert['severity'] }}-900">
                                            View Details →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Quick Actions -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('billing.finance.invoices') }}" class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    <span class="mt-2 text-sm font-medium text-gray-900">Create Invoice</span>
                </a>

                <a href="{{ route('billing.finance.credit-notes') }}" class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                    </svg>
                    <span class="mt-2 text-sm font-medium text-gray-900">Credit Notes</span>
                </a>

                <a href="{{ route('billing.finance.retainers') }}" class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="mt-2 text-sm font-medium text-gray-900">Retainers</span>
                </a>

                <a href="{{ route('billing.finance.audit-log') }}" class="flex flex-col items-center justify-center p-4 border-2 border-gray-200 rounded-lg hover:border-primary-500 hover:bg-primary-50 transition-colors">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="mt-2 text-sm font-medium text-gray-900">Audit Log</span>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
