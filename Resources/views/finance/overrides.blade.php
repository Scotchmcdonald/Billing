<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Price Overrides') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="{ 
        showCreateModal: false,
        newOverride: {
            company_id: '',
            product_id: '',
            type: 'discount_percent',
            value: 0,
            starts_at: '',
            ends_at: '',
            notes: ''
        },
        basePrice: 100, // Simulated base price for calculation
        costPrice: 60, // Simulated cost price
        
        get calculatedMargin() {
            let price = this.basePrice;
            if (this.newOverride.type === 'fixed_price') {
                price = parseFloat(this.newOverride.value) || 0;
            } else if (this.newOverride.type === 'discount_percent') {
                price = this.basePrice * (1 - (parseFloat(this.newOverride.value) || 0) / 100);
            } else if (this.newOverride.type === 'markup_percent') {
                price = this.basePrice * (1 + (parseFloat(this.newOverride.value) || 0) / 100);
            }
            
            if (price === 0) return 0;
            return ((price - this.costPrice) / price) * 100;
        }
    }">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-4 flex justify-end">
                <button @click="showCreateModal = true" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                    Create Override
                </button>
            </div>

            <!-- Data Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Margin Impact</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($overrides as $override)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $override['company_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $override['product_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $override['type'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-bold">{{ $override['value'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $override['margin_impact'] > 30 ? 'bg-green-100 text-green-800' : ($override['margin_impact'] > 15 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                    {{ $override['margin_impact'] }}%
                                    @if($override['margin_impact'] < 15) <span class="ml-1">!</span> @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $override['starts_at'] }} <br>
                                <span class="text-xs text-gray-400">{{ $override['ends_at'] ?? 'Forever' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Create Modal -->
            <div x-show="showCreateModal" class="fixed z-10 inset-0 overflow-y-auto" style="display: none;">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div x-show="showCreateModal" class="fixed inset-0 transition-opacity" aria-hidden="true">
                        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                    </div>

                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                    <div x-show="showCreateModal" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Create Price Override</h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Company</label>
                                    <select x-model="newOverride.company_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option>Select Company...</option>
                                        <option value="1">Acme Corp</option>
                                        <option value="2">Globex Inc</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Product</label>
                                    <select x-model="newOverride.product_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option>Select Product...</option>
                                        <option value="1">Enterprise License ($100)</option>
                                        <option value="2">Support Package ($50)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Override Type</label>
                                    <div class="mt-2 space-x-4">
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio" name="type" value="fixed_price" x-model="newOverride.type">
                                            <span class="ml-2">Fixed Price</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input type="radio" class="form-radio" name="type" value="discount_percent" x-model="newOverride.type">
                                            <span class="ml-2">Discount %</span>
                                        </label>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Value</label>
                                    <input type="number" x-model="newOverride.value" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>

                                <div class="bg-gray-50 p-3 rounded-md">
                                    <p class="text-sm font-medium text-gray-700">Projected Margin</p>
                                    <div class="flex items-center mt-1">
                                        <span class="text-2xl font-bold" :class="calculatedMargin < 15 ? 'text-rose-600' : (calculatedMargin < 30 ? 'text-amber-600' : 'text-emerald-600')" x-text="calculatedMargin.toFixed(1) + '%'"></span>
                                        <span x-show="calculatedMargin < 15" class="ml-2 text-xs text-rose-600 font-bold uppercase">Below Floor!</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes (Required)</label>
                                    <textarea x-model="newOverride.notes" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md"></textarea>
                                    <p class="mt-1 text-xs text-gray-500" x-show="newOverride.notes.length < 20">Minimum 20 characters required.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" 
                                :disabled="newOverride.notes.length < 20"
                                @click="showCreateModal = false">
                                Create Override
                            </button>
                            <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showCreateModal = false">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
