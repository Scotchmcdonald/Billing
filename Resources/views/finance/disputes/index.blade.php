<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Invoice Disputes') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Invoice Disputes</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Manage and resolve disputed invoices
            </p>
        </div>

        <!-- State Indicator -->
        <x-billing::state-indicator state="idle" />

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Disputes</div>
                <div class="mt-2 text-3xl font-bold text-danger-600 dark:text-danger-400">0</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Resolution</div>
                <div class="mt-2 text-3xl font-bold text-warning-600 dark:text-warning-400">0</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Resolved This Month</div>
                <div class="mt-2 text-3xl font-bold text-success-600 dark:text-success-400">0</div>
            </div>
        </div>

        <!-- Disputes Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">All Disputes</h2>
                    <div class="flex space-x-2">
                        <select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm">
                            <option>All Status</option>
                            <option>Open</option>
                            <option>In Progress</option>
                            <option>Resolved</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Invoice
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Customer
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Dispute Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Reason
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Amount
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                No disputes found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dispute Resolution Guidelines -->
        <div class="mt-8 bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">Dispute Resolution Guidelines</h3>
            <ul class="space-y-2 text-sm text-blue-800 dark:text-blue-200">
                <li>• Respond to disputes within 48 hours</li>
                <li>• Gather all relevant documentation before resolution</li>
                <li>• Document all communications with the customer</li>
                <li>• Escalate complex disputes to management</li>
                <li>• Update the customer on resolution progress</li>
            </ul>
        </div>
    </div>
</x-app-layout>
