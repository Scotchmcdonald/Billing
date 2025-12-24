@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    FinOps Control Tower
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Financial overview and operational controls.
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0">
                <!-- Export Dropdown -->
                <div class="relative inline-block text-left" x-data="{ open: false }">
                    <div>
                        <button type="button" @click="open = !open" @click.away="open = false" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50" id="menu-button" aria-expanded="true" aria-haspopup="true">
                            Export Report
                            <svg class="-mr-1 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1" style="display: none;">
                        <div class="py-1" role="none">
                            <a href="#" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-50" role="menuitem" tabindex="-1" id="menu-item-0">PDF Summary</a>
                            <a href="#" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-50" role="menuitem" tabindex="-1" id="menu-item-1">Excel (Detailed)</a>
                            <a href="#" class="text-gray-700 block px-4 py-2 text-sm hover:bg-gray-50" role="menuitem" tabindex="-1" id="menu-item-2">CSV Raw Data</a>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="ml-3 inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                    </svg>
                    New Invoice
                </button>
            </div>
        </div>

        <!-- Metric Bar -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-8">
            <!-- MRR -->
            <div class="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Monthly Recurring Revenue</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                    <x-billing::money-display amount="124500.00" />
                </dd>
                <dd class="mt-2 flex items-baseline text-sm">
                    <span class="text-success-600 font-semibold inline-flex items-baseline">
                        <svg class="h-2.5 w-2.5 self-center flex-shrink-0 text-success-500 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158V16.25A.75.75 0 0110 17z" clip-rule="evenodd" /></svg>
                        12%
                    </span>
                    <span class="text-gray-500 ml-2">from last month</span>
                </dd>
                <!-- Sparkline Decoration -->
                <div class="absolute bottom-0 right-0 -mb-4 -mr-4 opacity-10">
                    <svg width="120" height="60" viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 60L20 40L40 50L60 20L80 30L100 10L120 25V60H0Z" fill="currentColor" class="text-primary-600"/>
                    </svg>
                </div>
            </div>

            <!-- Outstanding AR -->
            <div class="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Outstanding AR</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-gray-900">
                    <x-billing::money-display amount="23400.50" />
                </dd>
                <dd class="mt-2 flex items-baseline text-sm">
                    <span class="text-warning-600 font-semibold inline-flex items-baseline">
                        15 Invoices
                    </span>
                    <span class="text-gray-500 ml-2">overdue > 30 days</span>
                </dd>
                 <!-- Sparkline Decoration -->
                 <div class="absolute bottom-0 right-0 -mb-4 -mr-4 opacity-10">
                    <svg width="120" height="60" viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 60L30 50L60 55L90 30L120 40V60H0Z" fill="currentColor" class="text-warning-600"/>
                    </svg>
                </div>
            </div>

            <!-- Fee Savings -->
            <div class="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow sm:p-6">
                <dt class="truncate text-sm font-medium text-gray-500">Fee Savings (ACH vs CC)</dt>
                <dd class="mt-1 text-3xl font-semibold tracking-tight text-success-600">
                    <x-billing::money-display amount="4250.00" />
                </dd>
                <dd class="mt-2 flex items-baseline text-sm">
                    <span class="text-gray-500">
                        Optimized via Dynamic Fee Offset
                    </span>
                </dd>
                 <!-- Sparkline Decoration -->
                 <div class="absolute bottom-0 right-0 -mb-4 -mr-4 opacity-10">
                    <svg width="120" height="60" viewBox="0 0 120 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M0 60L20 50L40 45L60 30L80 20L100 15L120 5V60H0Z" fill="currentColor" class="text-success-600"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Operations Table -->
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl mb-8">
            <div class="border-b border-gray-200 px-4 py-5 sm:px-6 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Active Operations</h3>
                    <p class="mt-1 text-sm text-gray-500">Recent transactions and their real-time status.</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative rounded-md shadow-sm">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" name="search" id="search" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-primary-600 sm:text-sm sm:leading-6" placeholder="Search transactions...">
                    </div>
                    <button type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M2.628 1.601C5.028 1.206 7.49 1 10 1s4.973.206 7.372.601a.75.75 0 01.628.74v2.288a2.25 2.25 0 01-.659 1.59l-4.682 4.683a2.25 2.25 0 00-.659 1.59v3.037c0 .684-.31 1.33-.844 1.757l-1.937 1.55A.75.75 0 018 18.25v-5.757a2.25 2.25 0 00-.659-1.591L2.659 6.22A2.25 2.25 0 012 4.629V2.34a.75.75 0 01.628-.74z" clip-rule="evenodd" />
                        </svg>
                        Filter
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Transaction ID</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Method</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <!-- Example Row 1: Pending ACH -->
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#TX-99281</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">Acme Corp</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <x-billing::money-display amount="1200.00" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">ACH Direct Debit</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <x-billing::status-badge status="pending" label="Pending ACH" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">2 mins ago</td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <a href="#" class="text-primary-600 hover:text-primary-900">View</a>
                            </td>
                        </tr>
                        <!-- Example Row 2: Success CC -->
                        <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#TX-99280</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">Globex Inc</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <x-billing::money-display amount="450.00" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">Credit Card (**** 4242)</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <x-billing::status-badge status="success" label="Paid" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">15 mins ago</td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <a href="#" class="text-primary-600 hover:text-primary-900">View</a>
                            </td>
                        </tr>
                         <!-- Example Row 3: Failed -->
                         <tr>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">#TX-99279</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">Soylent Corp</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <x-billing::money-display amount="2500.00" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">ACH Direct Debit</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <x-billing::status-badge status="danger" label="Failed" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">1 hour ago</td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <a href="#" class="text-primary-600 hover:text-primary-900">View</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 rounded-b-xl">
                <div class="flex flex-1 justify-between sm:hidden">
                    <a href="#" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                    <a href="#" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium">1</span> to <span class="font-medium">10</span> of <span class="font-medium">97</span> results
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <a href="#" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Previous</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                            </a>
                            <a href="#" aria-current="page" class="relative z-10 inline-flex items-center bg-primary-600 px-4 py-2 text-sm font-semibold text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">1</a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">2</a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">3</a>
                            <a href="#" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0">
                                <span class="sr-only">Next</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Circuit Breaker / Red Zone -->
        <div class="relative overflow-hidden rounded-lg border border-red-200 bg-red-50 p-6">
            <!-- Hazard Stripes Background -->
            <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(45deg, #ef4444 0, #ef4444 10px, transparent 10px, transparent 20px);"></div>
            
            <div class="relative z-10 md:flex md:items-center md:justify-between">
                <div>
                    <h3 class="text-lg font-medium text-red-800 flex items-center">
                        <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Danger Zone
                    </h3>
                    <p class="mt-1 text-sm text-red-600">
                        Irreversible actions. Please proceed with caution.
                    </p>
                </div>
            </div>
            <div class="relative z-10 mt-6 border-t border-red-200 pt-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-red-800">Void Invoice</h4>
                        <p class="text-sm text-red-600">Permanently void an invoice. This cannot be undone.</p>
                    </div>
                    <button type="button" 
                        x-data 
                        @click="$dispatch('open-confirm-modal', { 
                            title: 'Void Invoice', 
                            message: 'Are you sure you want to void this invoice? This action cannot be undone.', 
                            onConfirm: () => { console.log('Invoice voided'); } 
                        })"
                        class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        Void Invoice
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
