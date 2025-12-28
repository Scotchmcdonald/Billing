<x-app-layout>
    <div x-data="yoyGrowthDashboard()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Year-over-Year Growth</h1>
                    <p class="mt-2 text-sm text-gray-600">Track business growth trajectory and key performance indicators</p>
                </div>
                <div class="flex items-center gap-3">
                    <select x-model="selectedYear" @change="loadData()" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="2024">2024 vs 2023</option>
                        <option value="2023">2023 vs 2022</option>
                        <option value="2022">2022 vs 2021</option>
                    </select>
                    <button @click="exportReport()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Key Growth Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Revenue Growth -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" :class="metrics.revenue.growth >= 0 ? 'border-success-500' : 'border-danger-500'">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Revenue Growth</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900" x-text="metrics.revenue.growth + '%'"></p>
                    </div>
                    <div :class="metrics.revenue.growth >= 0 ? 'bg-success-100' : 'bg-danger-100'" class="p-3 rounded-full">
                        <svg x-show="metrics.revenue.growth >= 0" class="w-6 h-6 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        <svg x-show="metrics.revenue.growth < 0" class="w-6 h-6 text-danger-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"/>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Previous Year</span>
                    <span class="font-medium text-gray-900" x-text="'$' + (metrics.revenue.previous / 1000).toFixed(0) + 'K'"></span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600">Current Year</span>
                    <span class="font-bold text-gray-900" x-text="'$' + (metrics.revenue.current / 1000).toFixed(0) + 'K'"></span>
                </div>
            </div>

            <!-- Client Growth -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" :class="metrics.clients.growth >= 0 ? 'border-success-500' : 'border-danger-500'">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Client Growth</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900" x-text="metrics.clients.growth + '%'"></p>
                    </div>
                    <div :class="metrics.clients.growth >= 0 ? 'bg-success-100' : 'bg-danger-100'" class="p-3 rounded-full">
                        <svg class="w-6 h-6" :class="metrics.clients.growth >= 0 ? 'text-success-600' : 'text-danger-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Previous Year</span>
                    <span class="font-medium text-gray-900" x-text="metrics.clients.previous"></span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600">Current Year</span>
                    <span class="font-bold text-gray-900" x-text="metrics.clients.current"></span>
                </div>
            </div>

            <!-- MRR Growth -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" :class="metrics.mrr.growth >= 0 ? 'border-success-500' : 'border-danger-500'">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">MRR Growth</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900" x-text="metrics.mrr.growth + '%'"></p>
                    </div>
                    <div :class="metrics.mrr.growth >= 0 ? 'bg-success-100' : 'bg-danger-100'" class="p-3 rounded-full">
                        <svg class="w-6 h-6" :class="metrics.mrr.growth >= 0 ? 'text-success-600' : 'text-danger-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Previous Year</span>
                    <span class="font-medium text-gray-900" x-text="'$' + (metrics.mrr.previous / 1000).toFixed(0) + 'K'"></span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600">Current Year</span>
                    <span class="font-bold text-gray-900" x-text="'$' + (metrics.mrr.current / 1000).toFixed(0) + 'K'"></span>
                </div>
            </div>

            <!-- Ticket Volume Growth -->
            <div class="bg-white rounded-lg shadow-sm p-6 border-l-4" :class="metrics.tickets.growth >= 0 ? 'border-success-500' : 'border-danger-500'">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Ticket Volume</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900" x-text="metrics.tickets.growth + '%'"></p>
                    </div>
                    <div :class="metrics.tickets.growth >= 0 ? 'bg-success-100' : 'bg-danger-100'" class="p-3 rounded-full">
                        <svg class="w-6 h-6" :class="metrics.tickets.growth >= 0 ? 'text-success-600' : 'text-danger-600'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600">Previous Year</span>
                    <span class="font-medium text-gray-900" x-text="metrics.tickets.previous.toLocaleString()"></span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600">Current Year</span>
                    <span class="font-bold text-gray-900" x-text="metrics.tickets.current.toLocaleString()"></span>
                </div>
            </div>
        </div>

        <!-- Monthly Comparison Chart -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-semibold text-gray-900">Monthly Revenue Comparison</h2>
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-primary-600 rounded"></div>
                        <span class="text-gray-600" x-text="selectedYear"></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="w-3 h-3 bg-gray-300 rounded"></div>
                        <span class="text-gray-600" x-text="selectedYear - 1"></span>
                    </div>
                </div>
            </div>
            
            <!-- Chart Container -->
            <div class="h-64 flex items-end justify-between gap-2">
                <template x-for="(month, index) in monthlyData" :key="index">
                    <div class="flex-1 flex flex-col items-center gap-2">
                        <!-- Bars -->
                        <div class="w-full flex gap-1 items-end" style="height: 200px;">
                            <div :style="'height: ' + (month.previous / maxRevenue * 100) + '%'" class="flex-1 bg-gray-300 rounded-t hover:bg-gray-400 transition-colors cursor-pointer" :title="'Previous: $' + month.previous.toLocaleString()"></div>
                            <div :style="'height: ' + (month.current / maxRevenue * 100) + '%'" class="flex-1 bg-primary-600 rounded-t hover:bg-primary-700 transition-colors cursor-pointer" :title="'Current: $' + month.current.toLocaleString()"></div>
                        </div>
                        <!-- Month Label -->
                        <span class="text-xs text-gray-600 font-medium" x-text="month.name"></span>
                    </div>
                </template>
            </div>
        </div>

        <!-- Quarterly Performance -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <template x-for="quarter in quarterlyData" :key="quarter.name">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-sm font-medium text-gray-600 mb-4" x-text="quarter.name"></h3>
                    <div class="space-y-3">
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600">Revenue</span>
                                <span :class="quarter.revenue_growth >= 0 ? 'text-success-600' : 'text-danger-600'" class="font-semibold" x-text="(quarter.revenue_growth >= 0 ? '+' : '') + quarter.revenue_growth + '%'"></span>
                            </div>
                            <div class="text-lg font-bold text-gray-900" x-text="'$' + (quarter.revenue / 1000).toFixed(0) + 'K'"></div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="text-gray-600">New Clients</span>
                                <span :class="quarter.clients_growth >= 0 ? 'text-success-600' : 'text-danger-600'" class="font-semibold" x-text="(quarter.clients_growth >= 0 ? '+' : '') + quarter.clients_growth + '%'"></span>
                            </div>
                            <div class="text-lg font-bold text-gray-900" x-text="quarter.new_clients"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Key Insights -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Key Insights & Trends</h2>
            <div class="space-y-4">
                <template x-for="insight in insights" :key="insight.id">
                    <div class="flex items-start gap-3 p-4 rounded-lg" :class="{
                        'bg-success-50 border border-success-200': insight.type === 'positive',
                        'bg-warning-50 border border-warning-200': insight.type === 'warning',
                        'bg-info-50 border border-info-200': insight.type === 'info'
                    }">
                        <div class="flex-shrink-0 mt-0.5">
                            <svg x-show="insight.type === 'positive'" class="w-5 h-5 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <svg x-show="insight.type === 'warning'" class="w-5 h-5 text-warning-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <svg x-show="insight.type === 'info'" class="w-5 h-5 text-info-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-gray-900 mb-1" x-text="insight.title"></h4>
                            <p class="text-sm text-gray-700" x-text="insight.description"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function yoyGrowthDashboard() {
            return {
                selectedYear: 2024,
                metrics: {
                    revenue: { growth: 23.5, previous: 487000, current: 601445 },
                    clients: { growth: 15.2, previous: 342, current: 394 },
                    mrr: { growth: 18.7, previous: 38500, current: 45700 },
                    tickets: { growth: 12.3, previous: 5230, current: 5873 }
                },
                monthlyData: [
                    { name: 'Jan', previous: 38500, current: 45200 },
                    { name: 'Feb', previous: 41200, current: 48900 },
                    { name: 'Mar', previous: 39800, current: 51200 },
                    { name: 'Apr', previous: 42100, current: 49800 },
                    { name: 'May', previous: 40900, current: 52100 },
                    { name: 'Jun', previous: 43500, current: 53800 },
                    { name: 'Jul', previous: 39200, current: 48200 },
                    { name: 'Aug', previous: 41800, current: 50900 },
                    { name: 'Sep', previous: 44200, current: 51500 },
                    { name: 'Oct', previous: 42900, current: 49200 },
                    { name: 'Nov', previous: 45100, current: 52800 },
                    { name: 'Dec', previous: 47800, current: 54845 }
                ],
                quarterlyData: [
                    { name: 'Q1 2024', revenue: 145300, revenue_growth: 22.1, new_clients: 28, clients_growth: 16.7 },
                    { name: 'Q2 2024', revenue: 155700, revenue_growth: 24.3, new_clients: 31, clients_growth: 19.2 },
                    { name: 'Q3 2024', revenue: 150600, revenue_growth: 21.8, new_clients: 24, clients_growth: 14.3 },
                    { name: 'Q4 2024', revenue: 156845, revenue_growth: 25.7, new_clients: 29, clients_growth: 15.8 }
                ],
                insights: [
                    { id: 1, type: 'positive', title: 'Strong Revenue Growth', description: 'Revenue has grown 23.5% YoY, exceeding industry average of 18%. This momentum is driven by increased client retention and higher average contract values.' },
                    { id: 2, type: 'positive', title: 'Client Acquisition Accelerating', description: 'Client growth of 15.2% shows effective sales and marketing strategies. Q2 2024 showed the strongest performance with 19.2% growth.' },
                    { id: 3, type: 'warning', title: 'Q3 Performance Dip', description: 'Q3 revenue growth (21.8%) and client acquisition (14.3%) were below average. Consider reviewing seasonal factors and competitive dynamics.' },
                    { id: 4, type: 'info', title: 'MRR Trending Upward', description: 'Monthly Recurring Revenue shows consistent 18.7% growth, indicating strong subscription model health and improved retention rates.' }
                ],
                maxRevenue: 60000,
                
                loadData() {
                    // API call to load data for selected year
                },
                
                exportReport() {
                    window.location.href = `/billing/executive/reports/yoy-growth?year=${this.selectedYear}&format=pdf`;
                }
            }
        }
    </script>
</x-app-layout>
