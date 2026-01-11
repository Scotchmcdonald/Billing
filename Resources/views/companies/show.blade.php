<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $company->name }} - Billing Settings
            </h2>
            <a href="{{ route('billing.companies.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Back to List</a>
        </div>
    </x-slot>

    <div class="py-12" x-data="{ activeTab: 'details' }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Tabs -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button @click="activeTab = 'details'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'details', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'details' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Company Details
                    </button>
                    <button @click="activeTab = 'subscriptions'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'subscriptions', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'subscriptions' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Subscriptions
                    </button>
                    <button @click="activeTab = 'invoices'" :class="{ 'border-indigo-500 text-indigo-600': activeTab === 'invoices', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'invoices' }" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Invoices
                    </button>
                </nav>
            </div>

            <!-- Details Tab -->
            <div x-show="activeTab === 'details'" class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- General Info -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">General Information</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
                        <dl class="sm:divide-y sm:divide-gray-200">
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $company->name }}</dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Email Address</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $company->email }}</dd>
                            </div>
                            <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                                <dt class="text-sm font-medium text-gray-500">Helcim ID</dt>
                                <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 font-mono">{{ $company->helcim_id ?? 'Not Connected' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Pricing Tier Configuration -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Pricing Configuration</h3>
                    </div>
                    <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                        <form method="POST" action="{{ route('billing.companies.update', $company) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="pricing_tier" class="block text-sm font-medium text-gray-700">Pricing Tier</label>
                                    <select id="pricing_tier" name="pricing_tier" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="standard" {{ ($company->pricing_tier ?? 'standard') == 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="non-profit" {{ ($company->pricing_tier ?? 'non-profit') == 'non-profit' ? 'selected' : '' }}>Non-Profit (Discounted)</option>
                                        <option value="consumer" {{ ($company->pricing_tier ?? 'consumer') == 'consumer' ? 'selected' : '' }}>Consumer</option>
                                        <option value="enterprise" {{ ($company->pricing_tier ?? 'enterprise') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                                    </select>
                                    <p class="mt-2 text-sm text-gray-500">Affects base rates for all services.</p>
                                </div>

                                <div class="flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                        Save Changes
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Subscriptions Tab -->
            <div x-show="activeTab === 'subscriptions'" style="display: none;">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Active Subscriptions</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <ul role="list" class="divide-y divide-gray-200">
                            @forelse($company->subscriptions as $subscription)
                            <li class="px-4 py-4 sm:px-6">
                                <div class="flex items-center justify-between">
                                    <div class="text-sm font-medium text-indigo-600 truncate">
                                        {{ $subscription->name }}
                                    </div>
                                    <div class="ml-2 flex-shrink-0 flex">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subscription->stripe_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ ucfirst($subscription->stripe_status) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2 sm:flex sm:justify-between">
                                    <div class="sm:flex">
                                        <p class="flex items-center text-sm text-gray-500">
                                            Ends: {{ $subscription->ends_at ? $subscription->ends_at->format('M d, Y') : 'Auto-renews' }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="px-4 py-4 sm:px-6 text-gray-500 text-sm">No active subscriptions found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Invoices Tab -->
            <div x-show="activeTab === 'invoices'" style="display: none;">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Invoices</h3>
                    </div>
                    <div class="border-t border-gray-200">
                        <!-- Placeholder for invoice list -->
                        <div class="p-6 text-center text-gray-500">
                            Invoice history integration pending.
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
