<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Quote') }} #{{ $quote->quote_number }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="quoteBuilder({{ json_encode($products) }}, {{ $quote->approval_threshold_percent }}, {{ json_encode($quote->load('lineItems')) }})">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('billing.finance.quotes.update', $quote->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <!-- Company / Prospect -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Client</label>
                        <select name="company_id" x-model="companyId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- New Prospect --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" data-tier="{{ $company->pricing_tier ?? 'standard' }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="!companyId" class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prospect Name</label>
                            <input type="text" name="prospect_name" value="{{ $quote->prospect_name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prospect Email</label>
                            <input type="email" name="prospect_email" value="{{ $quote->prospect_email }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <!-- Pricing Tier Selection -->
                    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pricing Tier</label>
                                <select name="pricing_tier" x-model="pricingTier" @change="updateAllPrices()" 
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="standard">Standard</option>
                                    <option value="non_profit">Non-Profit (Discounted)</option>
                                    <option value="consumer">Consumer</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Select pricing tier to auto-populate prices</p>
                            </div>
                            
                            <div class="ml-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Approval Threshold</label>
                                <div class="flex items-center">
                                    <input type="number" name="approval_threshold_percent" x-model="approvalThreshold" 
                                           min="0" max="100" step="0.01"
                                           class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <span class="ml-2 text-sm text-gray-600">%</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Variance threshold for approval</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">{{ $quote->notes }}</textarea>
                    </div>

                    <!-- Valid Until -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Valid Until</label>
                        <input type="date" name="valid_until" value="{{ $quote->valid_until ? $quote->valid_until->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <!-- Line Items -->
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Line Items</h3>
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex gap-4 mb-2 items-end">
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500">Product</label>
                                    <select :name="'items['+index+'][product_id]'" x-model="item.product_id" @change="updateProduct(index)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                        <option value="">-- Custom Item --</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500">Description</label>
                                    <input type="text" :name="'items['+index+'][description]'" x-model="item.description" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs text-gray-500">Qty</label>
                                    <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" @input="calculateItemVariance(index)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs text-gray-500">Price</label>
                                    <input type="number" step="0.01" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" @input="calculateItemVariance(index)" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                    <input type="hidden" :name="'items['+index+'][standard_price]'" :value="item.standard_price">
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs text-gray-500">Subtotal</label>
                                    <div class="text-right pb-2 font-bold">
                                        <span x-text="formatMoney(item.quantity * item.unit_price)"></span>
                                    </div>
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs text-gray-500">Variance</label>
                                    <div class="text-right pb-2" :class="getVarianceClass(item.variance_percent)">
                                        <span x-show="item.variance_percent !== 0" x-text="formatVariance(item.variance_percent)"></span>
                                        <span x-show="item.variance_percent === 0" class="text-gray-400">--</span>
                                    </div>
                                </div>
                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 pb-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addItem()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">
                            + Add Item
                        </button>
                    </div>

                    <!-- Approval Warning -->
                    <div x-show="requiresApproval" class="mb-6 p-4 bg-amber-50 border border-amber-300 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-amber-600 mt-0.5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <div>
                                <h4 class="font-semibold text-amber-800">Approval Required</h4>
                                <p class="text-sm text-amber-700 mt-1">
                                    One or more items have a price variance exceeding <span x-text="approvalThreshold"></span>% from the standard price. 
                                    This quote will require management approval before it can be sent to the client.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-t pt-4 flex justify-end">
                        <div class="text-right">
                            <div class="text-2xl font-bold" x-text="formatMoney(total)"></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-2">
                        <a href="{{ route('billing.finance.quotes.show', $quote->id) }}" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                            <span x-show="requiresApproval">Update Quote (Pending Approval)</span>
                            <span x-show="!requiresApproval">Update Quote</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function quoteBuilder(products, defaultApprovalThreshold, existingQuote = null) {
            return {
                companyId: existingQuote ? existingQuote.company_id : '',
                pricingTier: existingQuote ? existingQuote.pricing_tier : 'standard',
                approvalThreshold: defaultApprovalThreshold,
                products: products,
                items: existingQuote ? existingQuote.line_items.map(item => ({
                    product_id: item.product_id,
                    description: item.description,
                    quantity: item.quantity,
                    unit_price: item.unit_price,
                    standard_price: item.standard_price || 0,
                    variance_percent: item.variance_percent || 0
                })) : [
                    { product_id: '', description: '', quantity: 1, unit_price: 0, standard_price: 0, variance_percent: 0 }
                ],
                
                init() {
                    // Watch for company selection to auto-select pricing tier
                    this.$watch('companyId', (value) => {
                        if (value) {
                            const select = document.querySelector('select[name="company_id"]');
                            const option = select.options[select.selectedIndex];
                            const tier = option.getAttribute('data-tier') || 'standard';
                            this.pricingTier = tier;
                            // Only update prices if not editing existing quote or if user explicitly changes company
                            // But here we are editing, so maybe we shouldn't auto-update prices immediately?
                            // Let's assume if they change company, they want new prices.
                            if (value != (existingQuote ? existingQuote.company_id : '')) {
                                this.updateAllPrices();
                            }
                        }
                    });
                },
                
                addItem() {
                    this.items.push({ 
                        product_id: '', 
                        description: '', 
                        quantity: 1, 
                        unit_price: 0,
                        standard_price: 0,
                        variance_percent: 0
                    });
                },
                
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                
                updateProduct(index) {
                    const item = this.items[index];
                    if (item.product_id) {
                        const product = this.products.find(p => p.id == item.product_id);
                        if (product) {
                            item.description = product.name;
                            item.standard_price = product.tier_prices.standard;
                            item.unit_price = product.tier_prices[this.pricingTier] || product.tier_prices.standard;
                            this.calculateItemVariance(index);
                        }
                    }
                },
                
                updateAllPrices() {
                    this.items.forEach((item, index) => {
                        if (item.product_id) {
                            const product = this.products.find(p => p.id == item.product_id);
                            if (product) {
                                item.unit_price = product.tier_prices[this.pricingTier] || product.tier_prices.standard;
                                this.calculateItemVariance(index);
                            }
                        }
                    });
                },
                
                calculateItemVariance(index) {
                    const item = this.items[index];
                    if (item.standard_price && item.standard_price > 0) {
                        const variance = item.unit_price - item.standard_price;
                        item.variance_percent = (variance / item.standard_price) * 100;
                    } else {
                        item.variance_percent = 0;
                    }
                },
                
                get total() {
                    return this.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },
                
                get requiresApproval() {
                    return this.items.some(item => Math.abs(item.variance_percent) > this.approvalThreshold);
                },
                
                formatMoney(amount) {
                    return '$' + amount.toFixed(2);
                },
                
                formatVariance(percent) {
                    const sign = percent > 0 ? '+' : '';
                    return sign + percent.toFixed(1) + '%';
                },
                
                getVarianceClass(percent) {
                    const absPercent = Math.abs(percent);
                    if (absPercent > this.approvalThreshold) {
                        return 'text-red-600 font-semibold';
                    } else if (absPercent > 0) {
                        return percent > 0 ? 'text-orange-600' : 'text-green-600';
                    }
                    return 'text-gray-500';
                }
            }
        }
    </script>
</x-app-layout>
