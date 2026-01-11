@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8" x-data="quoteBuilder()">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">New Hybrid Quote</h1>
        <button @click="saveQuote()" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
            Create Quote
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left: Line Items -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-xl font-semibold mb-4">Line Items</h2>
                
                <template x-for="(item, index) in items" :key="index">
                    <div class="border-b border-gray-200 py-4 last:border-0">
                        <div class="flex justify-between items-start mb-2">
                            <div class="w-1/3 pr-2 relative" x-data="{ search: '', open: false, get filteredProducts() { return this.availableProducts.filter(p => p.name.toLowerCase().includes(this.search.toLowerCase()) || p.sku.toLowerCase().includes(this.search.toLowerCase())) } }">
                                <label class="block text-sm font-medium text-gray-700">Product</label>
                                <div class="relative mt-1">
                                    <input type="text" x-model="search" @focus="open = true; search = ''" @click.away="open = false" 
                                        :placeholder="item.product_id ? availableProducts.find(p => p.id == item.product_id)?.name : 'Search Product...'"
                                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    
                                    <div x-show="open" class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" style="display: none;">
                                        <template x-for="p in filteredProducts" :key="p.id">
                                            <div @click="item.product_id = p.id; search = p.name; open = false; loadProductDetails(index)" 
                                                 class="relative cursor-default select-none py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white group">
                                                <div class="flex flex-col">
                                                    <span class="block truncate font-semibold" x-text="p.name"></span>
                                                    <span class="block truncate text-xs text-gray-500 group-hover:text-indigo-200" x-text="p.sku"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <div x-show="filteredProducts.length === 0" class="relative cursor-default select-none py-2 px-4 text-gray-700">
                                            No products found.
                                        </div>
                                    </div>
                                </div>
                                <!-- Hidden Select for native binding if needed, though we bind to item.product_id directly -->
                            </div>
                            
                            <div class="w-1/4 px-2">
                                <label class="block text-sm font-medium text-gray-700">Billing Strategy</label>
                                <select x-model="item.strategy" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="one_time">One-Time (Upfront)</option>
                                    <option value="monthly">Monthly Subscription</option>
                                    <template x-if="item.type === 'hardware'">
                                        <option value="rto_12">Rent-to-Own (12 Mo)</option>
                                    </template>
                                    <template x-if="item.type === 'hardware'">
                                        <option value="rto_24">Rent-to-Own (24 Mo)</option>
                                    </template>
                                </select>
                            </div>

                            <div class="w-1/6 px-2">
                                <label class="block text-sm font-medium text-gray-700">Qty</label>
                                <input type="number" x-model.number="item.quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            
                            <div class="w-1/6 pl-2 text-right">
                                <label class="block text-sm font-medium text-gray-700">Total</label>
                                <div class="mt-2 font-mono" x-text="formatMoney(calculateLineTotal(item))"></div>
                                <div class="text-xs text-gray-500" x-show="item.strategy.startsWith('rto')">
                                    <span x-text="formatMoney(calculateMonthlyRTO(item))"></span>/mo
                                </div>
                            </div>

                            <div class="w-8 pt-6 pl-2">
                                <button @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                                    &times;
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

                <button @click="addItem()" class="mt-4 flex items-center text-blue-600 hover:text-blue-800">
                    <span class="text-xl font-bold mr-1">+</span> Add Line Item
                </button>
            </div>
        </div>

        <!-- Right: Summary & Customer -->
        <div class="lg:col-span-1 space-y-6">
            
            <!-- Customer Info -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Customer Details</h3>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Company (Existing)</label>
                    <select x-model="company_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select Company...</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div x-show="!company_id" class="border-t pt-4 mt-4">
                    <p class="text-xs text-gray-500 mb-2">Or New Prospect</p>
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700">Prospect Name</label>
                        <input type="text" x-model="prospect_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-gray-700">Prospect Email</label>
                        <input type="email" x-model="prospect_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>
                </div>
            </div>

            <!-- Totals -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Quote Summary</h3>
                
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Upfront Total:</span>
                    <span class="font-bold" x-text="formatMoney(totals.upfront)"></span>
                </div>
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Monthly Recurring:</span>
                    <span class="font-bold" x-text="formatMoney(totals.monthly)"></span>
                </div>
                <div class="border-t pt-2 mt-2 flex justify-between">
                    <span class="text-gray-800 font-bold">Est. 1st Year TCV:</span>
                    <span class="text-blue-600 font-bold" x-text="formatMoney(totals.tcv)"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Inject PHP data
    const availableProductsData = @json($products);

    function quoteBuilder() {
        return {
            items: [],
            availableProducts: availableProductsData,
            company_id: '',
            prospect_name: '',
            prospect_email: '',
            
            init() {
                this.addItem(); // Start with one item
            },

            addItem() {
                this.items.push({
                    product_id: '',
                    type: 'service', 
                    base_price: 0,
                    monthly_price: 0,
                    quantity: 1,
                    strategy: 'one_time' 
                });
            },
            
            removeItem(index) {
                this.items.splice(index, 1);
            },
            
            loadProductDetails(index) {
                const item = this.items[index];
                const product = this.availableProducts.find(p => p.id == item.product_id);
                if (product) {
                    item.type = product.type;
                    item.base_price = parseFloat(product.base_price);
                    item.monthly_price = parseFloat(product.monthly_price);
                    
                    // Reset strategy if invalid for new type? 
                    // Keeping simple for now, but RTO requires hardware
                    if (item.type !== 'hardware' && item.strategy.startsWith('rto')) {
                        item.strategy = 'one_time';
                    }
                }
            },

            calculateLineTotal(item) {
                if (item.strategy === 'one_time') {
                    return item.base_price * item.quantity;
                }
                // For Monthly or RTO, the 'Upfront' line total is usually just the first month's payment
                // UNLESS the prompt implies RTO has no upfront and just monthly?
                // Standard logic: 
                // One-Time: Full Price
                // Monthly: 1 Month Price
                // RTO: 1 Month Payment
                
                if (item.strategy === 'monthly') {
                    return item.monthly_price * item.quantity;
                }

                if (item.strategy.startsWith('rto')) {
                    return this.calculateMonthlyRTO(item);
                }
                
                return 0;
            },

            calculateMonthlyRTO(item) {
                let months = item.strategy === 'rto_12' ? 12 : 24;
                return (item.base_price * item.quantity) / months;
            },

            get totals() {
                let upfront = 0;
                let monthly = 0;
                
                this.items.forEach(item => {
                    if (item.strategy === 'one_time') {
                        upfront += item.base_price * item.quantity;
                    } else if (item.strategy === 'monthly') {
                        monthly += item.monthly_price * item.quantity; // Fixed calculation to use monthly_price
                    } else if (item.strategy.startsWith('rto')) {
                        monthly += this.calculateMonthlyRTO(item);
                    }
                });

                return {
                    upfront: upfront, // "Initial Due"
                    monthly: monthly,
                    tcv: upfront + (monthly * 12) 
                };
            },

            formatMoney(amount) {
                return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
            },

            async saveQuote() {
                const payload = {
                    company_id: this.company_id,
                    prospect_name: this.prospect_name,
                    prospect_email: this.prospect_email,
                    items: this.items.filter(i => i.product_id).map(item => {
                         return {
                            product_id: item.product_id,
                            quantity: item.quantity,
                            strategy: item.strategy
                         };
                    }),
                    _token: '{{ csrf_token() }}'
                };

                try {
                    const response = await fetch('{{ route('billing.finance.quotes.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(payload)
                    });
                    
                    if (response.redirected) {
                        window.location.href = response.url;
                    } else if (response.ok) {
                         window.location.reload(); 
                    } else {
                        const data = await response.json();
                        alert('Error: ' + (data.message || 'Validation Failed'));
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred.');
                }
            }
        }
    }
</script>
@endsection
