<!-- Profitability Content -->
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Profitability Analysis</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Analyze revenue, expenses, and gross profit margins</p>
    </div>

    <!-- Profitability Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Total Revenue</h3>
                <i class="fas fa-arrow-up text-success-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($profitability['revenue'] ?? 0, 0) }}</p>
            <p class="text-xs {{ ($profitability['revenue_growth'] ?? 0) >= 0 ? 'text-success-600' : 'text-danger-600' }} mt-2">
                <i class="fas fa-arrow-{{ ($profitability['revenue_growth'] ?? 0) >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($profitability['revenue_growth'] ?? 0), 1) }}% vs last month
            </p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Total Expenses</h3>
                <i class="fas fa-arrow-down text-danger-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($profitability['expenses'] ?? 0, 0) }}</p>
            <p class="text-xs text-gray-500 mt-2">
                Based on product cost price
            </p>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-500">Gross Profit</h3>
                <i class="fas fa-dollar-sign text-primary-500"></i>
            </div>
            <p class="text-3xl font-bold text-gray-900">${{ number_format($profitability['gross_profit'] ?? 0, 0) }}</p>
            <p class="text-xs text-success-600 mt-2">
                {{ $profitability['margin'] ?? 0 }}% margin
            </p>
        </div>
    </div>

    <!-- Profitability by Service Type -->
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Profitability by Product/Service</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product/Service</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Revenue</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Profit</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Margin</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($profitabilityByService ?? [] as $service)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $service->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">${{ number_format($service->revenue, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-danger-600">${{ number_format($service->cost, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-success-600">${{ number_format($service->profit, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $service->margin >= 40 ? 'bg-success-100 text-success-800' : ($service->margin >= 20 ? 'bg-warning-100 text-warning-800' : 'bg-danger-100 text-danger-800') }}">
                                {{ number_format($service->margin, 1) }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">No paid invoice data available for analysis.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Trend Chart Placeholder -->
    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Profitability Trend (Last 6 Months)</h3>
        <div class="h-64 flex items-center justify-center text-gray-400">
            <div class="text-center">
                <i class="fas fa-chart-line text-6xl mb-4"></i>
                <p>Trend chart visualization would go here</p>
            </div>
        </div>
    </div>
</div>
