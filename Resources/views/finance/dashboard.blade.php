<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Finance Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @include('billing::finance._partials.nav')

            <!-- Metrics Row -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <!-- MRR Card -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6 transition-all duration-200 hover:shadow-md">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">Monthly Recurring Revenue</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($totalMrr / 100, 2) }}</span>
                        <span class="ml-2 text-sm font-medium text-success-600 dark:text-success-400">+5.4%</span>
                    </div>
                    <!-- Sparkline Placeholder -->
                    <div class="mt-4 h-10 w-full bg-gray-50 dark:bg-gray-700 rounded" x-data>
                        <!-- Alpine.js sparkline would go here -->
                    </div>
                </div>

                <!-- AR Aging Summary -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6 transition-all duration-200 hover:shadow-md">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">AR Aging (> 30 Days)</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-danger-600 dark:text-danger-400">${{ number_format(($arAging['31-60'] + $arAging['61-90'] + $arAging['90+']) / 100, 2) }}</span>
                    </div>
                    <div class="mt-4 text-xs text-gray-500">
                        <div class="flex justify-between"><span>31-60:</span> <span>${{ number_format($arAging['31-60']/100) }}</span></div>
                        <div class="flex justify-between"><span>61-90:</span> <span>${{ number_format($arAging['61-90']/100) }}</span></div>
                        <div class="flex justify-between"><span>90+:</span> <span>${{ number_format($arAging['90+']/100) }}</span></div>
                    </div>
                </div>

                <!-- Gross Profit -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6 transition-all duration-200 hover:shadow-md">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">Gross Profit (This Month)</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($grossProfit / 100, 2) }}</span>
                    </div>
                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Margin: <span class="font-bold text-success-600 dark:text-success-400">68%</span>
                    </div>
                </div>

                <!-- Unbilled Time/Materials -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm border border-gray-200 dark:border-gray-700 sm:rounded-lg p-6 transition-all duration-200 hover:shadow-md">
                    <div class="text-gray-500 dark:text-gray-400 text-sm font-medium uppercase tracking-wider">Unbilled Items</div>
                    <div class="mt-2 flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">$12,450.00</span>
                    </div>
                    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
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
                <div class="relative h-64 w-full">
                    @if(!empty($forecastData['forecast']))
                        @php
                            $forecast = $forecastData['forecast'];
                            $values = array_values($forecast);
                            $labels = array_keys($forecast);
                            $count = count($values);
                            $max = max($values);
                            $min = min($values);
                            
                            // Add padding to min/max for better visualization
                            $range = $max - $min;
                            if ($range == 0) $range = $max ?: 100; 
                            
                            // Add 20% padding to top and bottom of range
                            $minY = max(0, $min - ($range * 0.2));
                            $maxY = $max + ($range * 0.2);
                            $rangeY = $maxY - $minY;
                            if ($rangeY == 0) $rangeY = 1;

                            $width = 800;
                            $height = 250;
                            $paddingX = 60;
                            $paddingY = 40;
                            
                            $points = [];
                            $stepX = ($width - ($paddingX * 2)) / max(1, $count - 1);
                            
                            foreach ($values as $index => $value) {
                                $x = $paddingX + ($index * $stepX);
                                // Invert Y because SVG 0 is at top
                                $y = $height - $paddingY - ((($value - $minY) / $rangeY) * ($height - ($paddingY * 2)));
                                $points[] = ['x' => $x, 'y' => $y, 'value' => $value, 'label' => $labels[$index]];
                            }
                            
                            $pathData = "";
                            foreach ($points as $i => $point) {
                                $pathData .= ($i === 0 ? "M" : "L") . " {$point['x']} {$point['y']} ";
                            }
                            
                            // Area path (close the loop to the bottom)
                            $areaPathData = $pathData . " L {$points[$count-1]['x']} " . ($height - $paddingY) . " L {$points[0]['x']} " . ($height - $paddingY) . " Z";
                        @endphp

                        <svg viewBox="0 0 {{ $width }} {{ $height }}" class="w-full h-full overflow-visible" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                                    <stop offset="0%" style="stop-color:#6366f1;stop-opacity:0.2" />
                                    <stop offset="100%" style="stop-color:#6366f1;stop-opacity:0" />
                                </linearGradient>
                            </defs>

                            <!-- Area under line -->
                            <path d="{{ $areaPathData }}" fill="url(#gradient)" />

                            <!-- Line -->
                            <path d="{{ $pathData }}" fill="none" stroke="#6366f1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />

                            <!-- Points and Labels -->
                            @foreach($points as $point)
                                <!-- Vertical Grid Line (optional, subtle) -->
                                <line x1="{{ $point['x'] }}" y1="{{ $height - $paddingY }}" x2="{{ $point['x'] }}" y2="{{ $point['y'] }}" stroke="#e5e7eb" stroke-width="1" stroke-dasharray="4 4" />

                                <!-- Point -->
                                <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="5" fill="#ffffff" stroke="#6366f1" stroke-width="2" />
                                
                                <!-- Value Label (Staggered if needed, but with enough width it should be fine) -->
                                <text x="{{ $point['x'] }}" y="{{ $point['y'] - 15 }}" text-anchor="middle" font-size="14" font-weight="bold" fill="#374151" class="dark:fill-gray-200">
                                    ${{ number_format($point['value'], 0) }}
                                </text>
                                
                                <!-- Month Label -->
                                <text x="{{ $point['x'] }}" y="{{ $height - 10 }}" text-anchor="middle" font-size="12" fill="#6b7280" class="dark:fill-gray-400">
                                    {{ $point['label'] }}
                                </text>
                            @endforeach
                        </svg>
                    @else
                        <!-- Empty State -->
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">No forecast data available</p>
                                <p class="text-xs text-gray-400">Add subscriptions or invoices to generate forecast</p>
                            </div>
                        </div>
                    @endif
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
