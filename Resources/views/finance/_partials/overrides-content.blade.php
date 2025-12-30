<div class="space-y-6" x-data="{ 
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
    }" @open-create-override-modal.window="showCreateModal = true">
    
    <!-- Header -->
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Price Overrides</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage custom pricing and discounts for clients</p>
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
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valid Period</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($overrides as $override)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $override->company->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $override->product->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ ucfirst(str_replace('_', ' ', $override->type)) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                    @if($override->type == 'fixed_price' || $override->type == 'fixed')
                                        ${{ number_format($override->value, 2) }}
                                    @else
                                        {{ number_format($override->value, 2) }}%
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $override->starts_at ? $override->starts_at->format('M d, Y') : 'Now' }} - 
                                    {{ $override->ends_at ? $override->ends_at->format('M d, Y') : 'Forever' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $override->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ $override->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $overrides->links() }}
                </div>
            </div>
</div>
