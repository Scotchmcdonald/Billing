<!-- Executive Dashboard Content -->
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Executive Dashboard</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">High-level overview of key financial metrics and performance indicators</p>
    </div>

    <!-- Key Metrics Row -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- MRR Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Monthly Recurring Revenue</h3>
                <i class="fas fa-chart-line text-primary-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($metrics['mrr'] ?? 0, 0) }}</p>
            <p class="text-xs {{ ($metrics['mrr_growth'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }} mt-2">
                <i class="fas fa-arrow-{{ ($metrics['mrr_growth'] ?? 0) >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($metrics['mrr_growth'] ?? 0), 1) }}% vs last month
            </p>
        </div>

        <!-- AR Total Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Accounts Receivable</h3>
                <i class="fas fa-file-invoice-dollar text-warning-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">${{ number_format(collect($arAging ?? [])->sum('amount'), 0) }}</p>
            <p class="text-xs text-gray-500 mt-2">
                {{ collect($arAging ?? [])->sum('count') }} invoices outstanding
            </p>
        </div>

        <!-- Gross Profit Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Gross Profit (MTD)</h3>
                <i class="fas fa-dollar-sign text-success-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($profitability['gross_profit'] ?? 0, 0) }}</p>
            <p class="text-xs text-success-600 mt-2">
                {{ $profitability['margin'] ?? 0 }}% margin
            </p>
        </div>

        <!-- Churn Rate Card -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Churn Rate</h3>
                <i class="fas fa-user-slash text-danger-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($churnRate ?? 0, 1) }}%</p>
            <p class="text-xs text-gray-500 mt-2">
                Target: &lt; 5%
            </p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- MRR Forecast -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">MRR Forecast (6 Months)</h3>
            <div class="h-64 flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <i class="fas fa-chart-area text-6xl mb-4"></i>
                    <p>Chart visualization would go here</p>
                    <p class="text-sm mt-2">Projected MRR: ${{ number_format(collect($forecastData['forecast'] ?? [])->last() ?? 0, 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Revenue by Client -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Clients by Revenue (All Time)</h3>
            <div class="space-y-3">
                @forelse($topClients ?? [] as $clientData)
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center mr-3">
                            <span class="text-sm font-semibold text-primary-600">{{ substr($clientData->company->name ?? '?', 0, 1) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $clientData->company->name ?? 'Unknown Client' }}</p>
                            <p class="text-xs text-gray-500">Lifetime Revenue</p>
                        </div>
                    </div>
                    <span class="text-sm font-semibold text-gray-900">${{ number_format($clientData->total_revenue, 2) }}</span>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">No revenue data available yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
