<x-app-layout>
    <div x-data="disputeWorkflow()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dispute Workflow</h1>
                    <p class="mt-2 text-sm text-gray-600">Track the status of your invoice disputes</p>
                </div>
                <button @click="openNewDispute()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Dispute
                </button>
            </div>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Open Disputes</p>
                        <p class="mt-2 text-3xl font-bold text-warning-600" x-text="stats.open">0</p>
                    </div>
                    <div class="p-3 bg-warning-100 rounded-full">
                        <svg class="w-8 h-8 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Under Review</p>
                        <p class="mt-2 text-3xl font-bold text-info-600" x-text="stats.under_review">0</p>
                    </div>
                    <div class="p-3 bg-info-100 rounded-full">
                        <svg class="w-8 h-8 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Resolved</p>
                        <p class="mt-2 text-3xl font-bold text-success-600" x-text="stats.resolved">0</p>
                    </div>
                    <div class="p-3 bg-success-100 rounded-full">
                        <svg class="w-8 h-8 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Avg Resolution</p>
                        <p class="mt-2 text-3xl font-bold text-gray-900" x-text="stats.avg_days + 'd'">0d</p>
                    </div>
                    <div class="p-3 bg-gray-100 rounded-full">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="filters.status" @change="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">All Statuses</option>
                        <option value="submitted">Submitted</option>
                        <option value="under_review">Under Review</option>
                        <option value="resolved">Resolved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select x-model="filters.dateRange" @change="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">All Time</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="180">Last 6 Months</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Amount Range</label>
                    <select x-model="filters.amountRange" @change="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">Any Amount</option>
                        <option value="0-100">$0 - $100</option>
                        <option value="100-500">$100 - $500</option>
                        <option value="500+">$500+</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="resetFilters()" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Reset Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Disputes List -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">My Disputes</h2>
            </div>

            <div class="divide-y divide-gray-200">
                <template x-for="dispute in filteredDisputes" :key="dispute.id">
                    <div @click="viewDispute(dispute)" class="px-6 py-4 hover:bg-gray-50 cursor-pointer transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-base font-semibold text-gray-900" x-text="'Invoice #' + dispute.invoice_number"></h3>
                                    <span :class="{
                                        'bg-warning-100 text-warning-800': dispute.status === 'submitted',
                                        'bg-info-100 text-info-800': dispute.status === 'under_review',
                                        'bg-success-100 text-success-800': dispute.status === 'resolved',
                                        'bg-danger-100 text-danger-800': dispute.status === 'rejected'
                                    }" class="px-2 py-1 text-xs font-medium rounded-full" x-text="dispute.status.replace('_', ' ').toUpperCase()"></span>
                                    
                                    <!-- SLA Indicator -->
                                    <template x-if="dispute.days_open > dispute.sla_days">
                                        <span class="px-2 py-1 text-xs font-medium bg-danger-100 text-danger-800 rounded-full flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                            </svg>
                                            SLA Breach
                                        </span>
                                    </template>
                                </div>
                                
                                <p class="text-sm text-gray-600 mb-3" x-text="dispute.reason"></p>
                                
                                <div class="flex items-center gap-6 text-sm text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                        </svg>
                                        <span x-text="'$' + dispute.amount.toFixed(2)"></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span x-text="'Opened ' + dispute.days_open + ' days ago'"></span>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        <span x-text="dispute.updates_count + ' updates'"></span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="ml-4">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Progress Timeline -->
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <div :class="dispute.timeline_progress >= 1 ? 'bg-success-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold">1</div>
                                    <div :class="dispute.timeline_progress >= 1 ? 'bg-success-500' : 'bg-gray-300'" class="h-1 w-16"></div>
                                    <div :class="dispute.timeline_progress >= 2 ? 'bg-success-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold">2</div>
                                    <div :class="dispute.timeline_progress >= 2 ? 'bg-success-500' : 'bg-gray-300'" class="h-1 w-16"></div>
                                    <div :class="dispute.timeline_progress >= 3 ? 'bg-success-500' : 'bg-gray-300'" class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold">3</div>
                                </div>
                                <span class="text-xs text-gray-500" x-text="'Last update: ' + dispute.last_update"></span>
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-600">Submitted</span>
                                <span class="text-xs text-gray-600 ml-2">Review</span>
                                <span class="text-xs text-gray-600 ml-8">Resolved</span>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="filteredDisputes.length === 0" class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No disputes found</h3>
                    <p class="mt-1 text-sm text-gray-500">Try adjusting your filters or create a new dispute.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function disputeWorkflow() {
            return {
                stats: {
                    open: 3,
                    under_review: 1,
                    resolved: 12,
                    avg_days: 4
                },
                filters: {
                    status: '',
                    dateRange: 'all',
                    amountRange: ''
                },
                disputes: [
                    {
                        id: 1,
                        invoice_number: '2024-0523',
                        status: 'under_review',
                        reason: 'Duplicate charge for cloud storage service',
                        amount: 149.99,
                        days_open: 3,
                        sla_days: 5,
                        updates_count: 2,
                        last_update: '2 hours ago',
                        timeline_progress: 2
                    },
                    {
                        id: 2,
                        invoice_number: '2024-0489',
                        status: 'submitted',
                        reason: 'Service not provided as agreed',
                        amount: 325.00,
                        days_open: 1,
                        sla_days: 5,
                        updates_count: 0,
                        last_update: '1 day ago',
                        timeline_progress: 1
                    },
                    {
                        id: 3,
                        invoice_number: '2024-0467',
                        status: 'resolved',
                        reason: 'Incorrect billing hours',
                        amount: 89.50,
                        days_open: 14,
                        sla_days: 5,
                        updates_count: 5,
                        last_update: '3 days ago',
                        timeline_progress: 3
                    },
                    {
                        id: 4,
                        invoice_number: '2024-0421',
                        status: 'submitted',
                        reason: 'Already paid via check',
                        amount: 450.00,
                        days_open: 7,
                        sla_days: 5,
                        updates_count: 1,
                        last_update: '5 days ago',
                        timeline_progress: 1
                    }
                ],
                filteredDisputes: [],
                
                init() {
                    this.filteredDisputes = this.disputes;
                },
                
                applyFilters() {
                    let filtered = this.disputes;
                    
                    if (this.filters.status) {
                        filtered = filtered.filter(d => d.status === this.filters.status);
                    }
                    
                    if (this.filters.dateRange !== 'all') {
                        const days = parseInt(this.filters.dateRange);
                        filtered = filtered.filter(d => d.days_open <= days);
                    }
                    
                    if (this.filters.amountRange) {
                        if (this.filters.amountRange === '0-100') {
                            filtered = filtered.filter(d => d.amount <= 100);
                        } else if (this.filters.amountRange === '100-500') {
                            filtered = filtered.filter(d => d.amount > 100 && d.amount <= 500);
                        } else if (this.filters.amountRange === '500+') {
                            filtered = filtered.filter(d => d.amount > 500);
                        }
                    }
                    
                    this.filteredDisputes = filtered;
                },
                
                resetFilters() {
                    this.filters = {
                        status: '',
                        dateRange: 'all',
                        amountRange: ''
                    };
                    this.applyFilters();
                },
                
                viewDispute(dispute) {
                    window.location.href = `/billing/portal/disputes/${dispute.id}`;
                },
                
                openNewDispute() {
                    window.location.href = '/billing/portal/disputes/create';
                }
            }
        }
    </script>
</x-app-layout>
