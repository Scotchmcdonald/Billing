{{-- Bulk Price Override Manager - Guided Journey Wizard --}}
<div x-data="bulkOverrideWizard()" x-init="init()" class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- Wizard Progress Stepper --}}
        <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
            <nav aria-label="Progress">
                <ol class="flex items-center justify-between">
                    <li class="relative" :class="step >= 1 ? 'text-primary-600' : 'text-gray-400'">
                        <div class="flex items-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2" 
                                  :class="step >= 1 ? 'border-primary-600 bg-primary-600 text-white' : 'border-gray-300'">
                                <span x-show="step > 1">✓</span>
                                <span x-show="step === 1">1</span>
                                <span x-show="step < 1">1</span>
                            </span>
                            <span class="ml-3 text-sm font-medium">Select Clients</span>
                        </div>
                    </li>
                    <li class="relative" :class="step >= 2 ? 'text-primary-600' : 'text-gray-400'">
                        <div class="flex items-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2" 
                                  :class="step >= 2 ? 'border-primary-600 bg-primary-600 text-white' : 'border-gray-300'">
                                <span x-show="step > 2">✓</span>
                                <span x-show="step === 2">2</span>
                                <span x-show="step < 2">2</span>
                            </span>
                            <span class="ml-3 text-sm font-medium">Configure Overrides</span>
                        </div>
                    </li>
                    <li class="relative" :class="step >= 3 ? 'text-primary-600' : 'text-gray-400'">
                        <div class="flex items-center">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full border-2" 
                                  :class="step >= 3 ? 'border-primary-600 bg-primary-600 text-white' : 'border-gray-300'">
                                <span x-show="step > 3">✓</span>
                                <span x-show="step === 3">3</span>
                                <span x-show="step < 3">3</span>
                            </span>
                            <span class="ml-3 text-sm font-medium">Review & Apply</span>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>

        {{-- Step 1: Select Clients --}}
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Select Clients</h2>
                <p class="text-sm text-gray-600 mb-6">Choose the clients to apply bulk price overrides</p>

                <div class="mb-4">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" x-model="selectAll" @change="toggleSelectAll()" 
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-700">Select All Clients</span>
                    </label>
                </div>

                <div class="border border-gray-200 rounded-lg divide-y divide-gray-200 max-h-96 overflow-y-auto">
                    <template x-for="client in clients" :key="client.id">
                        <label class="flex items-center p-4 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" :value="client.id" x-model="selectedClients"
                                   class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <div class="ml-3 flex-1">
                                <div class="text-sm font-medium text-gray-900" x-text="client.name"></div>
                                <div class="text-xs text-gray-500" x-text="'Current Rate: $' + client.hourly_rate + '/hr'"></div>
                            </div>
                            <div class="text-sm text-gray-500" x-text="'MRR: $' + client.mrr"></div>
                        </label>
                    </template>
                </div>

                <div class="mt-6 flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <span x-text="selectedClients.length"></span> clients selected
                    </div>
                    <button @click="nextStep()" :disabled="selectedClients.length === 0"
                            class="px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        Next: Configure Overrides →
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 2: Configure Overrides --}}
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Configure Price Overrides</h2>
                <p class="text-sm text-gray-600 mb-6">Set new pricing for selected clients</p>

                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Override Type</label>
                        <select x-model="overrideType" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                            <option value="hourly">Hourly Rate Override</option>
                            <option value="percentage">Percentage Adjustment</option>
                            <option value="fixed">Fixed Monthly Amount</option>
                        </select>
                    </div>

                    <div x-show="overrideType === 'hourly'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Hourly Rate</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">$</span>
                            </div>
                            <input type="number" x-model="hourlyRate" step="5" min="0"
                                   class="w-full pl-7 py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="150.00">
                        </div>
                    </div>

                    <div x-show="overrideType === 'percentage'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Percentage Adjustment</label>
                        <div class="relative">
                            <input type="number" x-model="percentageAdjustment" step="1" min="-50" max="100"
                                   class="w-full py-3 px-4 pr-8 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="10">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">%</span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Use negative values for discounts</p>
                    </div>

                    <div x-show="overrideType === 'fixed'">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fixed Monthly Amount</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500">$</span>
                            </div>
                            <input type="number" x-model="fixedAmount" step="100" min="0"
                                   class="w-full pl-7 py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="5000.00">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Effective Date</label>
                        <input type="date" x-model="effectiveDate"
                               class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Override</label>
                        <textarea x-model="reason" rows="3"
                                  class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Annual price adjustment, contract renewal, etc."></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-between">
                    <button @click="prevStep()"
                            class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        ← Back
                    </button>
                    <button @click="nextStep()"
                            class="px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Next: Review →
                    </button>
                </div>
            </div>
        </div>

        {{-- Step 3: Review & Apply --}}
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-x-4"
             x-transition:enter-end="opacity-100 transform translate-x-0">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Review Changes</h2>
                <p class="text-sm text-gray-600 mb-6">Verify the changes before applying</p>

                {{-- Summary Card --}}
                <div class="bg-primary-50 border border-primary-200 rounded-lg p-4 mb-6">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <div class="text-2xl font-bold text-primary-600" x-text="selectedClients.length"></div>
                            <div class="text-xs text-gray-600">Clients Affected</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-primary-600" x-text="getOverrideDisplay()"></div>
                            <div class="text-xs text-gray-600">New Rate</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-primary-600" x-text="effectiveDate"></div>
                            <div class="text-xs text-gray-600">Effective Date</div>
                        </div>
                    </div>
                </div>

                {{-- Preview Table --}}
                <div class="border border-gray-200 rounded-lg overflow-hidden mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">New</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Change</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="client in getSelectedClientsData()" :key="client.id">
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900" x-text="client.name"></td>
                                    <td class="px-4 py-3 text-sm text-gray-600" x-text="'$' + client.hourly_rate"></td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900" x-text="'$' + calculateNewRate(client)"></td>
                                    <td class="px-4 py-3 text-sm">
                                        <span :class="calculateChange(client) >= 0 ? 'text-success-700' : 'text-danger-700'" 
                                              x-text="(calculateChange(client) >= 0 ? '+' : '') + calculateChange(client) + '%'"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                {{-- Circuit Breaker: Confirmation Required --}}
                <div class="bg-warning-50 border border-warning-200 rounded-lg p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-warning-700" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-warning-800">Confirmation Required</h3>
                            <div class="mt-2 text-sm text-warning-700">
                                <p>Type <strong>APPLY</strong> below to confirm these changes:</p>
                            </div>
                            <div class="mt-3">
                                <input type="text" x-model="confirmText" placeholder="Type APPLY"
                                       class="w-full py-2 px-3 border border-warning-300 rounded-lg focus:ring-warning-500 focus:border-warning-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between">
                    <button @click="prevStep()"
                            class="px-6 py-3 bg-white text-gray-700 font-medium rounded-lg border border-gray-300 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        ← Back
                    </button>
                    <button @click="applyOverrides()" :disabled="confirmText !== 'APPLY' || isProcessing"
                            class="px-6 py-3 bg-success-600 text-white font-medium rounded-lg hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!isProcessing">Apply Overrides ✓</span>
                        <span x-show="isProcessing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
function bulkOverrideWizard() {
    return {
        step: 1,
        selectAll: false,
        selectedClients: [],
        clients: [],
        overrideType: 'hourly',
        hourlyRate: '',
        percentageAdjustment: '',
        fixedAmount: '',
        effectiveDate: '',
        reason: '',
        confirmText: '',
        isProcessing: false,

        init() {
            // Mock data - replace with actual API call
            this.clients = [
                { id: 1, name: 'ABC Corp', hourly_rate: 150, mrr: 4500 },
                { id: 2, name: 'XYZ Ltd', hourly_rate: 175, mrr: 8750 },
                { id: 3, name: 'Tech Solutions', hourly_rate: 125, mrr: 3125 },
            ];
            this.effectiveDate = new Date().toISOString().split('T')[0];
        },

        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedClients = this.clients.map(c => c.id);
            } else {
                this.selectedClients = [];
            }
        },

        nextStep() {
            if (this.step < 3) {
                this.step++;
            }
        },

        prevStep() {
            if (this.step > 1) {
                this.step--;
            }
        },

        getSelectedClientsData() {
            return this.clients.filter(c => this.selectedClients.includes(c.id));
        },

        calculateNewRate(client) {
            if (this.overrideType === 'hourly') {
                return parseFloat(this.hourlyRate) || client.hourly_rate;
            } else if (this.overrideType === 'percentage') {
                return (client.hourly_rate * (1 + parseFloat(this.percentageAdjustment) / 100)).toFixed(2);
            } else {
                return parseFloat(this.fixedAmount) || client.hourly_rate;
            }
        },

        calculateChange(client) {
            const newRate = this.calculateNewRate(client);
            return (((newRate - client.hourly_rate) / client.hourly_rate) * 100).toFixed(1);
        },

        getOverrideDisplay() {
            if (this.overrideType === 'hourly') {
                return '$' + (this.hourlyRate || '0') + '/hr';
            } else if (this.overrideType === 'percentage') {
                return (this.percentageAdjustment || '0') + '%';
            } else {
                return '$' + (this.fixedAmount || '0');
            }
        },

        async applyOverrides() {
            this.isProcessing = true;
            // API call to apply overrides
            setTimeout(() => {
                this.isProcessing = false;
                // Redirect or show success message
                alert('Overrides applied successfully!');
            }, 2000);
        }
    }
}
</script>
