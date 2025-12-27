<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Retainers</h1>
                <p class="mt-1 text-sm text-gray-600">Manage pre-paid hour blocks for clients</p>
            </div>
            <a href="{{ route('billing.finance.retainers.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                Sell New Retainer
            </a>
        </div>

        <!-- Alert for low balance retainers -->
        @if($lowBalanceCount > 0)
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-amber-700">
                            <span class="font-medium">{{ $lowBalanceCount }}</span> retainer(s) have low balance (≤ 5 hours remaining)
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" action="{{ route('billing.finance.retainers.index') }}" class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="company_id" class="block text-sm font-medium text-gray-700">Company</label>
                    <select id="company_id" name="company_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="depleted" {{ request('status') == 'depleted' ? 'selected' : '' }}>Depleted</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="low_balance" value="1" {{ request('low_balance') ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Low Balance Only</span>
                    </label>
                </div>
            </div>

            <div class="mt-4 flex justify-end space-x-3">
                <a href="{{ route('billing.finance.retainers.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Clear
                </a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">
                    Filter
                </button>
            </div>
        </form>

        <!-- Retainers Table -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours Purchased</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hours Remaining</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price Paid</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchased</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($retainers as $retainer)
                        <tr class="hover:bg-gray-50 {{ $retainer->hours_remaining <= 5 && $retainer->hours_remaining > 0 ? 'bg-amber-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $retainer->company->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($retainer->hours_purchased, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div class="flex items-center">
                                    <span class="{{ $retainer->hours_remaining <= 5 && $retainer->hours_remaining > 0 ? 'text-amber-600 font-semibold' : '' }}">
                                        {{ number_format($retainer->hours_remaining, 2) }}
                                    </span>
                                    @if($retainer->hours_remaining > 0)
                                        <div class="ml-2 w-32 bg-gray-200 rounded-full h-2">
                                            <div class="bg-indigo-600 h-2 rounded-full {{ $retainer->hours_remaining <= 5 ? 'bg-amber-500' : '' }}" 
                                                 style="width: {{ ($retainer->hours_remaining / $retainer->hours_purchased) * 100 }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                ${{ number_format($retainer->price_paid / 100, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $retainer->purchased_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $retainer->expires_at ? $retainer->expires_at->format('M d, Y') : 'No expiration' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($retainer->status === 'active')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                        Active
                                    </span>
                                @elseif($retainer->status === 'depleted')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Depleted
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-rose-100 text-rose-800">
                                        Expired
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('billing.finance.retainers.show', $retainer) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                                @if($retainer->status === 'active')
                                    <a href="{{ route('billing.finance.retainers.add-hours', $retainer) }}" class="ml-3 text-indigo-600 hover:text-indigo-900">Add Hours</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                No retainers found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $retainers->links() }}
        </div>
    </div>
</x-app-layout>
