<x-app-layout>
    <div x-data="scheduledPayments()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Scheduled Payments</h1>
            <p class="mt-2 text-sm text-gray-600">View and manage your upcoming auto-pay charges</p>
        </div>

        <!-- Summary Banner -->
        <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-lg shadow-lg p-6 mb-8 text-white">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <h3 class="text-sm font-medium text-primary-100">Next Payment</h3>
                    </div>
                    <p class="text-3xl font-bold" x-text="nextPayment.date"></p>
                    <p class="text-sm text-primary-100 mt-1" x-text="'in ' + nextPayment.days_away + ' days'"></p>
                </div>
                
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                        <h3 class="text-sm font-medium text-primary-100">Amount Due</h3>
                    </div>
                    <p class="text-3xl font-bold" x-text="'$' + nextPayment.amount.toLocaleString()"></p>
                    <p class="text-sm text-primary-100 mt-1" x-text="nextPayment.invoice_count + ' invoices'"></p>
                </div>
                
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <h3 class="text-sm font-medium text-primary-100">Payment Method</h3>
                    </div>
                    <p class="text-xl font-semibold" x-text="nextPayment.method_name"></p>
                    <p class="text-sm text-primary-100 mt-1" x-text="'**** ' + nextPayment.last_four"></p>
                </div>
            </div>
            
            <!-- Account Balance Warning -->
            <div x-show="nextPayment.balance_warning" class="mt-4 p-4 bg-warning-500 bg-opacity-20 border border-warning-300 rounded-md">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-warning-100 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-white">Ensure Sufficient Funds</h4>
                        <p class="text-sm text-primary-100 mt-1">Please ensure your account has at least <span class="font-semibold" x-text="'$' + nextPayment.amount.toFixed(2)"></span> available by <span x-text="nextPayment.date"></span> to avoid payment failures.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar View Toggle -->
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
                <button @click="view = 'list'" :class="view === 'list' ? 'bg-primary-100 text-primary-700' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    List View
                </button>
                <button @click="view = 'calendar'" :class="view === 'calendar' ? 'bg-primary-100 text-primary-700' : 'bg-white text-gray-700'" class="px-4 py-2 rounded-md text-sm font-medium border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Calendar View
                </button>
            </div>
            
            <div class="flex items-center gap-3">
                <select x-model="filters.timeframe" @change="applyFilters()" class="rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                    <option value="30">Next 30 Days</option>
                    <option value="60">Next 60 Days</option>
                    <option value="90">Next 90 Days</option>
                    <option value="365">Next Year</option>
                </select>
            </div>
        </div>

        <!-- List View -->
        <div x-show="view === 'list'" class="space-y-4">
            <template x-for="payment in filteredPayments" :key="payment.id">
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-3">
                                    <div :class="{
                                        'bg-success-100 text-success-700': payment.days_away > 7,
                                        'bg-warning-100 text-warning-700': payment.days_away <= 7 && payment.days_away > 3,
                                        'bg-danger-100 text-danger-700': payment.days_away <= 3
                                    }" class="px-3 py-1 rounded-full text-xs font-semibold">
                                        <span x-text="payment.days_away === 0 ? 'TODAY' : payment.days_away === 1 ? 'TOMORROW' : 'IN ' + payment.days_away + ' DAYS'"></span>
                                    </div>
                                    <span class="text-lg font-bold text-gray-900" x-text="payment.date"></span>
                                </div>
                                
                                <div class="flex items-center gap-6 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">Amount</p>
                                        <p class="text-2xl font-bold text-gray-900" x-text="'$' + payment.amount.toFixed(2)"></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">Payment Method</p>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                            <span class="text-sm font-medium text-gray-900" x-text="payment.method_name + ' •••• ' + payment.last_four"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600 mb-1">Invoices</p>
                                        <p class="text-sm font-medium text-gray-900" x-text="payment.invoice_numbers.join(', ')"></p>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex items-center gap-3">
                                    <button @click="viewInvoices(payment)" class="text-sm text-primary-600 hover:text-primary-800 font-medium">
                                        View Invoices →
                                    </button>
                                    <button x-show="payment.can_skip" @click="skipPayment(payment)" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                        Skip This Payment
                                    </button>
                                    <button @click="changePaymentMethod(payment)" class="text-sm text-gray-600 hover:text-gray-800 font-medium">
                                        Change Method
                                    </button>
                                </div>
                            </div>
                            
                            <div class="ml-6">
                                <template x-if="payment.retry_count > 0">
                                    <div class="px-3 py-2 bg-warning-100 text-warning-800 rounded-md text-xs font-medium">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Retry Attempt <span x-text="payment.retry_count"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Calendar View -->
        <div x-show="view === 'calendar'" class="bg-white rounded-lg shadow-sm p-6">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900" x-text="currentMonth + ' ' + currentYear"></h3>
                <div class="flex items-center gap-2">
                    <button @click="previousMonth()" class="p-2 hover:bg-gray-100 rounded-md">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <button @click="nextMonth()" class="p-2 hover:bg-gray-100 rounded-md">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Calendar Grid -->
            <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                <!-- Day Headers -->
                <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                    <div class="bg-gray-50 px-2 py-3 text-center text-xs font-semibold text-gray-600" x-text="day"></div>
                </template>
                
                <!-- Calendar Days -->
                <template x-for="day in calendarDays" :key="day.date">
                    <div :class="day.isCurrentMonth ? 'bg-white' : 'bg-gray-50'" class="min-h-24 p-2">
                        <div :class="day.isToday ? 'bg-primary-600 text-white' : 'text-gray-900'" class="text-sm font-medium mb-1 w-6 h-6 flex items-center justify-center rounded-full" x-text="day.day"></div>
                        <template x-for="payment in day.payments">
                            <div class="text-xs bg-primary-100 text-primary-700 px-2 py-1 rounded mb-1 cursor-pointer hover:bg-primary-200" @click="viewPayment(payment)">
                                <div class="font-semibold" x-text="'$' + payment.amount.toFixed(0)"></div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            
            <!-- Legend -->
            <div class="mt-4 flex items-center gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-primary-100 rounded"></div>
                    <span class="text-gray-600">Scheduled Payment</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-warning-100 rounded"></div>
                    <span class="text-gray-600">Payment Due Soon</span>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div x-show="filteredPayments.length === 0" class="bg-white rounded-lg shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Scheduled Payments</h3>
            <p class="text-gray-600 mb-6">You don't have any upcoming auto-pay charges in the selected timeframe.</p>
            <button @click="window.location.href = '/billing/portal/auto-pay'" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                Configure Auto-Pay
            </button>
        </div>
    </div>

    <script>
        function scheduledPayments() {
            return {
                view: 'list',
                filters: {
                    timeframe: '90'
                },
                nextPayment: {
                    date: 'Dec 30, 2024',
                    days_away: 2,
                    amount: 1849.99,
                    invoice_count: 3,
                    method_name: 'Visa',
                    last_four: '4242',
                    balance_warning: true
                },
                payments: [
                    {
                        id: 1,
                        date: 'Dec 30, 2024',
                        days_away: 2,
                        amount: 1849.99,
                        method_name: 'Visa',
                        last_four: '4242',
                        invoice_numbers: ['INV-2024-0523', 'INV-2024-0524', 'INV-2024-0525'],
                        can_skip: false,
                        retry_count: 0
                    },
                    {
                        id: 2,
                        date: 'Jan 15, 2025',
                        days_away: 18,
                        amount: 1250.00,
                        method_name: 'Visa',
                        last_four: '4242',
                        invoice_numbers: ['INV-2025-0001'],
                        can_skip: true,
                        retry_count: 0
                    },
                    {
                        id: 3,
                        date: 'Jan 30, 2025',
                        days_away: 33,
                        amount: 2105.50,
                        method_name: 'Visa',
                        last_four: '4242',
                        invoice_numbers: ['INV-2025-0008', 'INV-2025-0009'],
                        can_skip: true,
                        retry_count: 0
                    },
                    {
                        id: 4,
                        date: 'Feb 15, 2025',
                        days_away: 49,
                        amount: 1375.00,
                        method_name: 'Visa',
                        last_four: '4242',
                        invoice_numbers: ['INV-2025-0015'],
                        can_skip: true,
                        retry_count: 0
                    }
                ],
                filteredPayments: [],
                currentMonth: 'December',
                currentYear: 2024,
                calendarDays: [],
                
                init() {
                    this.applyFilters();
                    this.generateCalendar();
                },
                
                applyFilters() {
                    const days = parseInt(this.filters.timeframe);
                    this.filteredPayments = this.payments.filter(p => p.days_away <= days);
                },
                
                generateCalendar() {
                    // Calendar generation logic would go here
                    // For demo purposes, using mock data
                    this.calendarDays = [];
                },
                
                previousMonth() {
                    // Navigate to previous month
                },
                
                nextMonth() {
                    // Navigate to next month
                },
                
                viewInvoices(payment) {
                    window.location.href = `/billing/portal/invoices?ids=${payment.invoice_numbers.join(',')}`;
                },
                
                skipPayment(payment) {
                    if (confirm('Are you sure you want to skip this payment? The invoices will remain unpaid.')) {
                        // API call to skip payment
                        alert('Payment skipped successfully');
                    }
                },
                
                changePaymentMethod(payment) {
                    window.location.href = '/billing/portal/payment-methods';
                },
                
                viewPayment(payment) {
                    this.view = 'list';
                    // Scroll to payment in list
                }
            }
        }
    </script>
</x-app-layout>
