<x-guest-layout>
    <div class="py-12" x-data="quoteBuilder()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-900 mb-8 text-center">Build Your MSP Plan</h1>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Product Selection -->
                        <div class="md:col-span-2 space-y-6">
                            <h2 class="text-xl font-semibold text-gray-800">Select Services</h2>
                            
                            @foreach($products as $product)
                                <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition">
                                    <div class="flex-1">
                                        <h3 class="font-medium text-gray-900">{{ $product->name }}</h3>
                                        <p class="text-sm text-gray-500">{{ $product->description }}</p>
                                        <div class="mt-1 text-sm font-bold text-indigo-600">${{ number_format($product->base_price, 2) }} / unit</div>
                                    </div>
                                    <div class="w-32">
                                        <label class="block text-xs text-gray-500 mb-1">Quantity</label>
                                        <input type="number" min="0" 
                                            x-model.number="items[{{ $product->id }}].quantity" 
                                            @change="calculateTotal()"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Summary & Contact -->
                        <div class="md:col-span-1">
                            <div class="bg-gray-50 p-6 rounded-lg sticky top-6">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4">Estimated Monthly Cost</h2>
                                
                                <div class="text-4xl font-bold text-indigo-600 mb-2" x-text="formatMoney(total)">$0.00</div>
                                <p class="text-sm text-gray-500 mb-6">Per month. Taxes not included.</p>

                                <hr class="border-gray-200 my-6">

                                <h3 class="font-medium text-gray-900 mb-4">Get Your Official Quote</h3>
                                
                                <form @submit.prevent="submitQuote">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">First Name</label>
                                            <input type="text" x-model="form.first_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Last Name</label>
                                            <input type="text" x-model="form.last_name" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Email</label>
                                            <input type="email" x-model="form.email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Company (Optional)</label>
                                            <input type="text" x-model="form.company_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        </div>
                                    </div>

                                    <button type="submit" 
                                        class="mt-6 w-full bg-indigo-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
                                        :disabled="loading || total <= 0">
                                        <span x-show="!loading">Request Consultation</span>
                                        <span x-show="loading">Processing...</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function quoteBuilder() {
            return {
                items: {
                    @foreach($products as $product)
                        {{ $product->id }}: { quantity: 0, price: {{ $product->base_price }} },
                    @endforeach
                },
                total: 0,
                loading: false,
                form: {
                    first_name: '',
                    last_name: '',
                    email: '',
                    company_name: ''
                },
                calculateTotal() {
                    this.total = Object.values(this.items).reduce((sum, item) => {
                        return sum + (item.quantity * item.price);
                    }, 0);
                },
                formatMoney(amount) {
                    return '$' + amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                },
                async submitQuote() {
                    this.loading = true;
                    
                    // Prepare items array
                    const selectedItems = Object.entries(this.items)
                        .filter(([id, item]) => item.quantity > 0)
                        .map(([id, item]) => ({
                            product_id: id,
                            quantity: item.quantity
                        }));

                    try {
                        const response = await fetch('{{ route('billing.public.quote.store') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                ...this.form,
                                items: selectedItems
                            })
                        });

                        const data = await response.json();
                        
                        if (response.ok) {
                            window.location.href = data.redirect_url;
                        } else {
                            alert('Error: ' + (data.message || 'Something went wrong'));
                        }
                    } catch (error) {
                        console.error(error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-guest-layout>
