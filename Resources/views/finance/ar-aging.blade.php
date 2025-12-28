<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('AR Aging Report') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">AR Aging Report</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">
                    Track outstanding receivables by aging period
                </p>
            </div>
            <div class="flex space-x-3">
                <button type="button" class="inline-flex items-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition-all duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        <!-- State Indicator -->
        <x-billing::state-indicator state="idle" />

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Current (0-30 days)</div>
                <div class="mt-2 text-3xl font-bold text-success-600 dark:text-success-400">$0.00</div>
                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-success-600 dark:bg-success-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">31-60 Days</div>
                <div class="mt-2 text-3xl font-bold text-warning-600 dark:text-warning-400">$0.00</div>
                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-warning-600 dark:bg-warning-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">61-90 Days</div>
                <div class="mt-2 text-3xl font-bold text-orange-600 dark:text-orange-400">$0.00</div>
                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-orange-600 dark:bg-orange-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">90+ Days</div>
                <div class="mt-2 text-3xl font-bold text-danger-600 dark:text-danger-400">$0.00</div>
                <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                    <div class="bg-danger-600 dark:bg-danger-500 h-1.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>
        </div>

        <!-- AR Aging Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Outstanding Invoices by Customer</h2>
                <div class="flex space-x-2">
                    <button type="button" class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        CSV
                    </button>
                    <button type="button" class="inline-flex items-center px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all duration-150">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        PDF
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customer
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Current
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                31-60 Days
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                61-90 Days
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                90+ Days
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No outstanding invoices found</p>
                                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">All receivables are current</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Troubleshooting Card (shown when errors occur) -->
        @if(false)
        <x-billing::troubleshooting-card title="Unable to Load Aging Data" type="error">
            <div class="space-y-3">
                <div>
                    <p class="font-semibold">What happened:</p>
                    <p class="mt-1">The system couldn't retrieve AR aging information from the database.</p>
                </div>
                <div>
                    <p class="font-semibold">Why it happened:</p>
                    <p class="mt-1">Database connection may have timed out or required tables are missing.</p>
                </div>
                <div>
                    <p class="font-semibold">What to do now:</p>
                    <ul class="mt-1 list-disc list-inside space-y-1">
                        <li>Click "Refresh" to retry loading the data</li>
                        <li>Verify database migrations are current</li>
                        <li>Check application logs for connection errors</li>
                    </ul>
                </div>
            </div>
        </x-billing::troubleshooting-card>
        @endif
        </div>
    </div>
</x-app-layout>
