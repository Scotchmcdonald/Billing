<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Financial Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Financial Reports</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Generate and view financial reports
            </p>
        </div>

        <!-- State Indicator -->
        <x-billing::state-indicator state="idle" />

        <!-- Report Categories -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Revenue Reports -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Revenue Reports</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="#" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-colors duration-150">
                            Monthly Revenue Summary
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-colors duration-150">
                            Revenue by Customer
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-colors duration-150">
                            Revenue by Product
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-colors duration-150">
                            Recurring Revenue (MRR/ARR)
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Invoice Reports -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Invoice Reports</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="#" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-colors duration-150">
                            Invoice Aging Report
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-primary-600 hover:text-primary-800 dark:text-primary-400 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 rounded transition-colors duration-150">
                            Outstanding Invoices
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Paid Invoices
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Voided Invoices
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Analytics Reports -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Analytics Reports</h3>
                <ul class="space-y-3">
                    <li>
                        <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Profitability Analysis
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Customer Lifetime Value
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Churn Analysis
                        </a>
                    </li>
                    <li>
                        <a href="#" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                            Cash Flow Forecast
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Custom Report Builder -->
        <div class="mt-8 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Custom Report Builder</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                Create custom reports with specific metrics and date ranges
            </p>
            <button class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Build Custom Report
            </button>
        </div>
    </div>
</x-app-layout>
