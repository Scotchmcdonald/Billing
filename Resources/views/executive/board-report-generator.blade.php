<x-app-layout>
    <div x-data="boardReportGenerator()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Board Report Generator</h1>
                    <p class="mt-2 text-sm text-gray-600">Create comprehensive one-page reports for stakeholder presentations</p>
                </div>
                <button @click="generateReport()" :disabled="generating" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:bg-gray-400">
                    <svg x-show="!generating" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <svg x-show="generating" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-text="generating ? 'Generating...' : 'Generate PDF'"></span>
                </button>
            </div>
        </div>

        <!-- Report Configuration -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Configuration Panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Report Settings</h2>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Period</label>
                            <select x-model="config.period" @change="loadPreview()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="current_month">Current Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="current_quarter">Current Quarter</option>
                                <option value="last_quarter">Last Quarter</option>
                                <option value="ytd">Year to Date</option>
                                <option value="last_year">Last Full Year</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Title</label>
                            <input type="text" x-model="config.title" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Q4 2024 Financial Summary">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sections to Include</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.sections.revenue" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">Revenue Summary</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.sections.clients" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">Client Metrics</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.sections.profitability" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">Profitability Analysis</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.sections.cashflow" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">Cash Flow Status</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.sections.pipeline" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">Sales Pipeline</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.sections.risks" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">Key Risks & Actions</span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Traffic Light Thresholds</label>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Green:</span>
                                    <span class="text-success-700 font-medium">≥ Target</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Yellow:</span>
                                    <span class="text-warning-700 font-medium">90-99% of Target</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600">Red:</span>
                                    <span class="text-danger-700 font-medium">< 90% of Target</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Include Comparisons</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.comparisons.prior_period" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">vs. Prior Period</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.comparisons.prior_year" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">vs. Prior Year</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" x-model="config.comparisons.budget" class="rounded text-primary-600 focus:ring-primary-500 mr-2">
                                    <span class="text-sm text-gray-700">vs. Budget</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Reports -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Reports</h2>
                    <div class="space-y-2">
                        <template x-for="report in recentReports" :key="report.id">
                            <button @click="loadReport(report)" class="w-full text-left px-3 py-2 rounded-md hover:bg-gray-50 transition-colors">
                                <p class="text-sm font-medium text-gray-900" x-text="report.title"></p>
                                <p class="text-xs text-gray-500" x-text="report.date"></p>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-sm p-8 border-2 border-gray-200">
                    <div class="space-y-6">
                        <!-- Report Header -->
                        <div class="border-b border-gray-200 pb-4">
                            <h1 class="text-2xl font-bold text-gray-900" x-text="config.title || 'Board Report'"></h1>
                            <p class="text-sm text-gray-600 mt-1" x-text="'Period: ' + getPeriodLabel()"></p>
                            <p class="text-xs text-gray-500" x-text="'Generated: ' + new Date().toLocaleDateString()"></p>
                        </div>

                        <!-- Executive Summary (Always Included) -->
                        <div>
                            <h2 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Executive Summary
                            </h2>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-center mb-2">
                                        <div :class="getTrafficLight(metrics.revenue_vs_target)" class="w-4 h-4 rounded-full"></div>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-900" x-text="'$' + (metrics.revenue / 1000).toFixed(0) + 'K'"></p>
                                    <p class="text-xs text-gray-600 mt-1">Total Revenue</p>
                                    <p class="text-xs font-medium" :class="metrics.revenue_vs_target >= 100 ? 'text-success-600' : 'text-danger-600'" x-text="metrics.revenue_vs_target + '% of target'"></p>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-center mb-2">
                                        <div :class="getTrafficLight(metrics.margin_vs_target)" class="w-4 h-4 rounded-full"></div>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-900" x-text="metrics.margin + '%'"></p>
                                    <p class="text-xs text-gray-600 mt-1">Gross Margin</p>
                                    <p class="text-xs font-medium" :class="metrics.margin_vs_target >= 100 ? 'text-success-600' : 'text-danger-600'" x-text="metrics.margin_vs_target + '% of target'"></p>
                                </div>
                                <div class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="flex items-center justify-center mb-2">
                                        <div :class="getTrafficLight(metrics.cashflow_vs_target)" class="w-4 h-4 rounded-full"></div>
                                    </div>
                                    <p class="text-2xl font-bold text-gray-900" x-text="'$' + (metrics.cashflow / 1000).toFixed(0) + 'K'"></p>
                                    <p class="text-xs text-gray-600 mt-1">Free Cash Flow</p>
                                    <p class="text-xs font-medium" :class="metrics.cashflow_vs_target >= 100 ? 'text-success-600' : 'text-danger-600'" x-text="metrics.cashflow_vs_target + '% of target'"></p>
                                </div>
                            </div>
                        </div>

                        <!-- Revenue Summary -->
                        <div x-show="config.sections.revenue">
                            <h2 class="text-lg font-bold text-gray-900 mb-3">Revenue Summary</h2>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600 mb-1">Total Revenue</p>
                                    <p class="text-xl font-bold text-gray-900">$548,234</p>
                                    <p class="text-xs text-success-600">+12.3% vs prior period</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 mb-1">Recurring Revenue (MRR)</p>
                                    <p class="text-xl font-bold text-gray-900">$45,700</p>
                                    <p class="text-xs text-success-600">+8.2% vs prior period</p>
                                </div>
                            </div>
                        </div>

                        <!-- Client Metrics -->
                        <div x-show="config.sections.clients">
                            <h2 class="text-lg font-bold text-gray-900 mb-3">Client Metrics</h2>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-600 mb-1">Active Clients</p>
                                    <p class="text-xl font-bold text-gray-900">394</p>
                                    <p class="text-xs text-success-600">+29 net new this period</p>
                                </div>
                                <div>
                                    <p class="text-gray-600 mb-1">Client Retention Rate</p>
                                    <p class="text-xl font-bold text-gray-900">96.2%</p>
                                    <p class="text-xs text-success-600">Above target of 95%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Key Risks & Actions -->
                        <div x-show="config.sections.risks">
                            <h2 class="text-lg font-bold text-gray-900 mb-3">Key Risks & Actions</h2>
                            <div class="space-y-2 text-sm">
                                <div class="flex items-start gap-2 p-3 bg-danger-50 border border-danger-200 rounded-md">
                                    <div class="w-3 h-3 bg-danger-500 rounded-full mt-1"></div>
                                    <div>
                                        <p class="font-medium text-danger-900">High AR aging: 3 clients >90 days ($42K total)</p>
                                        <p class="text-xs text-danger-700 mt-1">Action: Collections escalation initiated</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-3 bg-warning-50 border border-warning-200 rounded-md">
                                    <div class="w-3 h-3 bg-warning-500 rounded-full mt-1"></div>
                                    <div>
                                        <p class="font-medium text-warning-900">Sales pipeline velocity down 15%</p>
                                        <p class="text-xs text-warning-700 mt-1">Action: Q1 sales enablement training scheduled</p>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 p-3 bg-success-50 border border-success-200 rounded-md">
                                    <div class="w-3 h-3 bg-success-500 rounded-full mt-1"></div>
                                    <div>
                                        <p class="font-medium text-success-900">All financial targets met or exceeded</p>
                                        <p class="text-xs text-success-700 mt-1">Continue current strategy into Q1</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="border-t border-gray-200 pt-4 mt-8">
                            <p class="text-xs text-gray-500 text-center">
                                This report was automatically generated by the FinOps Billing Module on <span x-text="new Date().toLocaleString()"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function boardReportGenerator() {
            return {
                generating: false,
                config: {
                    period: 'current_month',
                    title: 'Executive Board Report - December 2024',
                    sections: {
                        revenue: true,
                        clients: true,
                        profitability: true,
                        cashflow: true,
                        pipeline: true,
                        risks: true
                    },
                    comparisons: {
                        prior_period: true,
                        prior_year: true,
                        budget: true
                    }
                },
                metrics: {
                    revenue: 548234,
                    revenue_vs_target: 104,
                    margin: 38.5,
                    margin_vs_target: 98,
                    cashflow: 127500,
                    cashflow_vs_target: 112
                },
                recentReports: [
                    { id: 1, title: 'Q3 2024 Board Report', date: '2024-10-15' },
                    { id: 2, title: 'September 2024 Summary', date: '2024-10-01' },
                    { id: 3, title: 'Q2 2024 Board Report', date: '2024-07-15' }
                ],
                
                getPeriodLabel() {
                    const labels = {
                        'current_month': 'Current Month',
                        'last_month': 'Last Month',
                        'current_quarter': 'Current Quarter (Q4 2024)',
                        'last_quarter': 'Last Quarter (Q3 2024)',
                        'ytd': 'Year to Date (Jan-Dec 2024)',
                        'last_year': 'Full Year 2023'
                    };
                    return labels[this.config.period] || 'Custom Period';
                },
                
                getTrafficLight(percentage) {
                    if (percentage >= 100) return 'bg-success-500';
                    if (percentage >= 90) return 'bg-warning-500';
                    return 'bg-danger-500';
                },
                
                loadPreview() {
                    // API call to load preview data
                },
                
                async generateReport() {
                    this.generating = true;
                    // Simulate API call
                    await new Promise(resolve => setTimeout(resolve, 2000));
                    
                    const params = new URLSearchParams({
                        period: this.config.period,
                        title: this.config.title,
                        sections: JSON.stringify(this.config.sections),
                        comparisons: JSON.stringify(this.config.comparisons)
                    });
                    
                    window.location.href = `/billing/executive/reports/board-report/generate?${params.toString()}`;
                    this.generating = false;
                },
                
                loadReport(report) {
                    // Load saved report configuration
                    alert('Loading report: ' + report.title);
                }
            }
        }
    </script>
</x-app-layout>
