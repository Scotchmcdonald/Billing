<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white shadow-sm rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Sell New Retainer</h2>

            @if(session('success'))
                <div class="mb-6 bg-success-50 border border-success-200 text-success-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-danger-50 border border-danger-200 text-danger-700 px-4 py-3 rounded">
                    <p class="font-semibold">Please correct the following errors:</p>
                    <ul class="list-disc list-inside mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('billing.finance.retainers.store') }}" x-data="retainerCalculator()">
                @csrf

                <!-- Company Selection -->
                <div class="mb-6">
                    <label for="company_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Company <span class="text-danger-600">*</span>
                    </label>
                    <select 
                        name="company_id" 
                        id="company_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        required
                    >
                        <option value="">Select a company...</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Hours -->
                <div class="mb-6">
                    <label for="hours" class="block text-sm font-semibold text-gray-700 mb-2">
                        Pre-Paid Hours <span class="text-danger-600">*</span>
                    </label>
                    
                    <!-- Quick Select Buttons -->
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button 
                            type="button"
                            @click="hours = 10"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary-500"
                        >
                            10 Hours
                        </button>
                        <button 
                            type="button"
                            @click="hours = 20"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary-500"
                        >
                            20 Hours
                        </button>
                        <button 
                            type="button"
                            @click="hours = 40"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary-500"
                        >
                            40 Hours
                        </button>
                        <button 
                            type="button"
                            @click="hours = 80"
                            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-primary-500"
                        >
                            80 Hours
                        </button>
                    </div>

                    <input 
                        type="number" 
                        name="hours" 
                        id="hours"
                        x-model="hours"
                        step="0.25"
                        min="1"
                        value="{{ old('hours', 10) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        required
                    >
                </div>

                <!-- Hourly Rate -->
                <div class="mb-6">
                    <label for="hourly_rate" class="block text-sm font-semibold text-gray-700 mb-2">
                        Hourly Rate <span class="text-danger-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                        <input 
                            type="number" 
                            name="hourly_rate" 
                            id="hourly_rate"
                            x-model="hourlyRate"
                            step="0.01"
                            min="0.01"
                            value="{{ old('hourly_rate', config('billing.default_hourly_rate', 150)) }}"
                            class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                            required
                        >
                    </div>
                </div>

                <!-- Price Calculator -->
                <div class="mb-6 bg-primary-50 border border-primary-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-gray-700">Total Price:</span>
                        <span class="text-2xl font-bold text-primary-600" x-text="'$' + totalPrice.toFixed(2)"></span>
                    </div>
                    <input type="hidden" name="price_paid" :value="Math.round(totalPrice * 100)">
                </div>

                <!-- Expiration Date (Optional) -->
                <div class="mb-6">
                    <label for="expires_at" class="block text-sm font-semibold text-gray-700 mb-2">
                        Expiration Date (Optional)
                    </label>
                    <input 
                        type="date" 
                        name="expires_at" 
                        id="expires_at"
                        value="{{ old('expires_at') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                    <p class="mt-1 text-xs text-gray-500">Leave blank for no expiration</p>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a 
                        href="{{ route('billing.finance.retainers.index') }}" 
                        class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 focus:ring-2 focus:ring-gray-500"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 focus:ring-2 focus:ring-primary-500"
                    >
                        Sell Retainer
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function retainerCalculator() {
            return {
                hours: {{ old('hours', 10) }},
                hourlyRate: {{ old('hourly_rate', config('billing.default_hourly_rate', 150)) }},
                get totalPrice() {
                    return this.hours * this.hourlyRate;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
