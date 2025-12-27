<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Quote') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="quoteBuilder()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('billing.finance.quotes.store') }}" method="POST">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    
                    <!-- Company / Prospect -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700">Client</label>
                        <select name="company_id" x-model="companyId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="">-- New Prospect --</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="!companyId" class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prospect Name</label>
                            <input type="text" name="prospect_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Prospect Email</label>
                            <input type="email" name="prospect_email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
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
                                            <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-name="{{ $product->name }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs text-gray-500">Description</label>
                                    <input type="text" :name="'items['+index+'][description]'" x-model="item.description" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="w-24">
                                    <label class="block text-xs text-gray-500">Qty</label>
                                    <input type="number" :name="'items['+index+'][quantity]'" x-model="item.quantity" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs text-gray-500">Price</label>
                                    <input type="number" step="0.01" :name="'items['+index+'][unit_price]'" x-model="item.unit_price" class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="w-32 text-right pb-2 font-bold">
                                    <span x-text="formatMoney(item.quantity * item.unit_price)"></span>
                                </div>
                                <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900 pb-2">
                                    &times;
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addItem()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-900">+ Add Item</button>
                    </div>

                    <!-- Totals -->
                    <div class="border-t pt-4 flex justify-end">
                        <div class="text-right">
                            <div class="text-2xl font-bold" x-text="formatMoney(total)"></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Create Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function quoteBuilder() {
            return {
                companyId: '',
                items: [
                    { product_id: '', description: '', quantity: 1, unit_price: 0 }
                ],
                addItem() {
                    this.items.push({ product_id: '', description: '', quantity: 1, unit_price: 0 });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                updateProduct(index) {
                    // We need to wait for DOM update or access data differently.
                    // Since we are in loop, accessing by ID is tricky.
                    // Let's use a timeout or just rely on x-model binding if we can pass data.
                    // But we can't pass data easily from option to x-model.
                    // So we query selector.
                    setTimeout(() => {
                        let select = document.getElementsByName(`items[${index}][product_id]`)[0];
                        if(select) {
                            let option = select.options[select.selectedIndex];
                            if (option.value) {
                                this.items[index].description = option.getAttribute('data-name');
                                this.items[index].unit_price = parseFloat(option.getAttribute('data-price'));
                            }
                        }
                    }, 50);
                },
                get total() {
                    return this.items.reduce((sum, item) => sum + (item.quantity * item.unit_price), 0);
                },
                formatMoney(amount) {
                    return '$' + amount.toFixed(2);
                }
            }
        }
    </script>
</x-app-layout>
