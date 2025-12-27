<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Metrics Row -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <!-- MRR Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Monthly Recurring Revenue</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900">${{ number_format($totalMrr / 100, 2) }}</span>
                        <span class="ml-2 text-sm font-medium text-emerald-600">+5.4%</span>
                    </div>
                    <!-- Sparkline Placeholder -->
                    <div class="mt-4 h-10 w-full bg-gray-50 rounded" x-data>
                        <!-- Alpine.js sparkline would go here -->
                    </div>
                </div>

                <!-- AR Aging Summary -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">AR Aging (> 30 Days)</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-rose-600">${{ number_format(($arAging['31-60'] + $arAging['61-90'] + $arAging['90+']) / 100, 2) }}</span>
                    </div>
                    <div class="mt-4 text-xs text-gray-500">
                        <div class="flex justify-between"><span>31-60:</span> <span>${{ number_format($arAging['31-60']/100) }}</span></div>
                        <div class="flex justify-between"><span>61-90:</span> <span>${{ number_format($arAging['61-90']/100) }}</span></div>
                        <div class="flex justify-between"><span>90+:</span> <span>${{ number_format($arAging['90+']/100) }}</span></div>
                    </div>
                </div>

                <!-- Gross Profit -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Gross Profit (This Month)</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900">${{ number_format($grossProfit / 100, 2) }}</span>
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        Margin: <span class="font-bold text-emerald-600">68%</span>
                    </div>
                </div>

                <!-- Unbilled Time/Materials -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Unbilled Items</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900">$12,450.00</span>
                    </div>
                    <div class="mt-4 text-sm text-gray-500">
                        Across 8 clients
                    </div>
                </div>
            </div>

            <!-- Forecasting Widget -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Revenue Forecast (Next 6 Months)</h3>
                    <div class="text-sm text-gray-500">
                        Projected Growth: <span class="font-bold text-emerald-600">{{ number_format($forecastData['avg_growth_rate'] * 100, 1) }}% / mo</span>
                        <span class="mx-2">|</span>
                        Predicted Churn: <span class="font-bold text-rose-600">{{ $churnRate }}%</span>
                    </div>
                </div>
                <div class="relative h-64">
                    <!-- Simple Bar Chart using CSS Grid -->
                    <div class="absolute inset-0 flex items-end justify-between space-x-2 px-4 pb-6">
                        @php
                            $maxVal = max($forecastData['forecast']);
                            $minVal = min($forecastData['forecast']);
                            // Add some buffer
                            $scale = $maxVal > 0 ? $maxVal * 1.1 : 100; 
                        @endphp
                        
                        @foreach($forecastData['forecast'] as $month => $amount)
                            <div class="flex flex-col items-center flex-1 group">
                                <div class="w-full bg-indigo-100 rounded-t hover:bg-indigo-200 transition-all relative" style="height: {{ ($amount / $scale) * 100 }}%">
                                    <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs rounded px-2 py-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        ${{ number_format($amount, 2) }}
                                    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">{{ $month }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Advanced Analytics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">ARPU</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($metrics['arpu'], 2) }}</div>
                    <div class="text-xs text-gray-500">Avg Revenue Per User</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">LTV</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($metrics['ltv'], 2) }}</div>
                    <div class="text-xs text-gray-500">Lifetime Value</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Gross Margin</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($metrics['gross_margin'], 1) }}%</div>
                    <div class="text-xs text-gray-500">Last Month</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-gray-500 text-sm font-medium uppercase tracking-wider">Rev / Tech</div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">${{ number_format($metrics['revenue_per_tech'], 2) }}</div>
                    <div class="text-xs text-gray-500">Revenue per Technician</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Pre-Flight Queue Widget -->
                <div class="lg:col-span-2 bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-indigo-500 relative">
                    <!-- Hazard Stripes Background -->
                    <div class="absolute top-0 right-0 w-32 h-32 opacity-5 pointer-events-none" style="background-image: repeating-linear-gradient(45deg, #000 25%, transparent 25%, transparent 50%, #000 50%, #000 75%, transparent 75%, transparent); background-size: 10px 10px;"></div>
                    
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Pre-Flight Billing Queue</h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                {{ $pendingInvoicesCount }} Pending
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-6">
                            Review and approve pending invoices before they are sent to clients. 
                            <span class="text-rose-600 font-bold">3 anomalies detected.</span>
                        </p>
                        <div class="flex space-x-4">
                            <a href="{{ route('billing.finance.pre-flight') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Review Queue Now
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Feed -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
                    <ul class="space-y-4">
                        @foreach($recentActivity as $activity)
                        <li class="flex space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <!-- Icon placeholder -->
                                    <span class="text-xs font-bold text-gray-500">A</span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $activity['action'] }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ $activity['description'] }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $activity['time'] }}
                                </p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
