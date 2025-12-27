<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Work Order #WO-2024-885') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Ticket Context (Simulated) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Server Maintenance - Acme Corp</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Perform routine maintenance on the primary database server. Check logs for anomalies.
                    </p>
                    <div class="mt-4 flex items-center text-sm text-gray-500">
                        <span class="mr-4">Status: <span class="font-semibold text-green-600">In Progress</span></span>
                        <span>Assigned to: <span class="font-semibold">You</span></span>
                    </div>
                </div>
            </div>

            <!-- Billing Panel -->
            <div x-data="{
                expanded: true,
                billable: true,
                startTime: null,
                endTime: null,
                manualHours: 1.5,
                rate: 125.00,
                
                expenses: [],
                showExpenseModal: false,
                newExpense: { description: '', amount: '', category: 'Travel' },
                
                parts: [],
                showPartModal: false,
                newPart: { id: '', name: '', quantity: 1, price: 0 },
                availableParts: [
                    { id: 1, name: 'Cat6 Cable (100ft)', price: 25.00 },
                    { id: 2, name: 'RJ45 Connectors (Pack)', price: 12.50 },
                    { id: 3, name: 'Switch Port Module', price: 150.00 }
                ],

                get timeTotal() {
                    return this.billable ? (this.manualHours * this.rate) : 0;
                },

                get expensesTotal() {
                    return this.expenses.reduce((sum, item) => sum + parseFloat(item.amount), 0);
                },

                get partsTotal() {
                    return this.parts.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                get grandTotal() {
                    return this.timeTotal + this.expensesTotal + this.partsTotal;
                },

                addExpense() {
                    if (!this.newExpense.description || !this.newExpense.amount) return;
                    this.expenses.push({ ...this.newExpense, id: Date.now() });
                    this.newExpense = { description: '', amount: '', category: 'Travel' };
                    this.showExpenseModal = false;
                },

                removeExpense(id) {
                    this.expenses = this.expenses.filter(e => e.id !== id);
                },

                addPart() {
                    if (!this.newPart.id) return;
                    const part = this.availableParts.find(p => p.id == this.newPart.id);
                    this.parts.push({ 
                        ...part, 
                        quantity: this.newPart.quantity, 
                        instanceId: Date.now() 
                    });
                    this.newPart = { id: '', name: '', quantity: 1, price: 0 };
                    this.showPartModal = false;
                },

                removePart(instanceId) {
                    this.parts = this.parts.filter(p => p.instanceId !== instanceId);
                },
                
                scanBarcode() {
                    alert('Camera access requested for barcode scanning...');
                    // Simulate scan
                    setTimeout(() => {
                        this.newPart.id = 1; // Simulate finding Cat6 Cable
                        alert('Scanned: Cat6 Cable (100ft)');
                    }, 1000);
                }
            }" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-indigo-100">
                
                <!-- Header / Toggle -->
                <div class="px-6 py-4 bg-indigo-50 border-b border-indigo-100 flex justify-between items-center cursor-pointer" @click="expanded = !expanded">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-indigo-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <h3 class="text-lg font-medium text-indigo-900">Billing & Materials</h3>
                    </div>
                    <div class="flex items-center">
                        <span class="text-indigo-700 font-bold mr-3" x-text="'$' + grandTotal.toFixed(2)"></span>
                        <svg class="h-5 w-5 text-indigo-400 transform transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </div>
                </div>

                <div x-show="expanded" class="p-6 space-y-8">
                    
                    <!-- Time Entry -->
                    <section>
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Labor</h4>
                            <div class="flex items-center">
                                <span class="mr-2 text-sm text-gray-600" :class="!billable ? 'font-bold' : ''">Non-Billable</span>
                                <button type="button" @click="billable = !billable" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" :class="billable ? 'bg-green-500' : 'bg-gray-200'">
                                    <span class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200" :class="billable ? 'translate-x-5' : 'translate-x-0'"></span>
                                </button>
                                <span class="ml-2 text-sm text-gray-600" :class="billable ? 'font-bold text-green-600' : ''">Billable</span>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <label class="block text-xs text-gray-500">Hours Worked</label>
                                <div class="flex items-center mt-1">
                                    <button @click="manualHours = Math.max(0, manualHours - 0.25)" class="p-1 rounded-md bg-white border hover:bg-gray-50 text-gray-500">-</button>
                                    <input type="number" x-model="manualHours" class="mx-2 w-20 text-center border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" step="0.25">
                                    <button @click="manualHours = parseFloat(manualHours) + 0.25" class="p-1 rounded-md bg-white border hover:bg-gray-50 text-gray-500">+</button>
                                </div>
                            </div>
                            <div class="text-right">
                                <label class="block text-xs text-gray-500">Rate</label>
                                <div class="text-sm font-medium text-gray-900">$<span x-text="rate.toFixed(2)"></span>/hr</div>
                            </div>
                            <div class="text-right border-t sm:border-t-0 sm:border-l border-gray-200 pt-2 sm:pt-0 sm:pl-4">
                                <label class="block text-xs text-gray-500">Total Labor</label>
                                <div class="text-xl font-bold text-gray-900">$<span x-text="timeTotal.toFixed(2)"></span></div>
                            </div>
                        </div>
                    </section>

                    <!-- Expenses -->
                    <section>
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Expenses</h4>
                            <button @click="showExpenseModal = true" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">+ Add Expense</button>
                        </div>
                        
                        <template x-if="expenses.length === 0">
                            <div class="text-sm text-gray-400 italic text-center py-2 border-2 border-dashed border-gray-200 rounded-lg">No expenses added</div>
                        </template>
                        
                        <ul class="divide-y divide-gray-200">
                            <template x-for="expense in expenses" :key="expense.id">
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="expense.description"></p>
                                        <p class="text-xs text-gray-500" x-text="expense.category"></p>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900 mr-4">$<span x-text="parseFloat(expense.amount).toFixed(2)"></span></span>
                                        <button @click="removeExpense(expense.id)" class="text-gray-400 hover:text-rose-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </section>

                    <!-- Parts / Materials -->
                    <section>
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Parts & Materials</h4>
                            <div class="flex space-x-2">
                                <button @click="scanBarcode()" class="p-1 text-gray-500 hover:text-indigo-600" title="Scan Barcode">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                                </button>
                                <button @click="showPartModal = true" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">+ Add Part</button>
                            </div>
                        </div>

                        <template x-if="parts.length === 0">
                            <div class="text-sm text-gray-400 italic text-center py-2 border-2 border-dashed border-gray-200 rounded-lg">No parts used</div>
                        </template>

                        <ul class="divide-y divide-gray-200">
                            <template x-for="part in parts" :key="part.instanceId">
                                <li class="py-3 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900" x-text="part.name"></p>
                                        <p class="text-xs text-gray-500">Qty: <span x-text="part.quantity"></span> @ $<span x-text="part.price.toFixed(2)"></span></p>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="text-sm font-medium text-gray-900 mr-4">$<span x-text="(part.price * part.quantity).toFixed(2)"></span></span>
                                        <button @click="removePart(part.instanceId)" class="text-gray-400 hover:text-rose-500">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </section>

                    <!-- Summary Footer -->
                    <div class="border-t border-gray-200 pt-4 flex justify-between items-center">
                        <div class="text-xs text-gray-500">
                            <p>Items will be added to the next invoice.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">Total Billable</p>
                            <p class="text-2xl font-bold text-indigo-600">$<span x-text="grandTotal.toFixed(2)"></span></p>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button class="w-full sm:w-auto inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Save Billing Entries
                        </button>
                    </div>
                </div>

                <!-- Expense Modal -->
                <div x-show="showExpenseModal" class="fixed z-20 inset-0 overflow-y-auto" style="display: none;">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showExpenseModal = false">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Expense</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <input type="text" x-model="newExpense.description" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Amount</label>
                                        <input type="number" x-model="newExpense.amount" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" step="0.01">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Category</label>
                                        <select x-model="newExpense.category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option>Travel</option>
                                            <option>Meals</option>
                                            <option>Parking</option>
                                            <option>Other</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="addExpense()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Add</button>
                                <button type="button" @click="showExpenseModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Part Modal -->
                <div x-show="showPartModal" class="fixed z-20 inset-0 overflow-y-auto" style="display: none;">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="showPartModal = false">
                            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                        </div>
                        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Part from Inventory</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Product</label>
                                        <select x-model="newPart.id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="">Select a product...</option>
                                            <template x-for="p in availableParts" :key="p.id">
                                                <option :value="p.id" x-text="p.name + ' ($' + p.price.toFixed(2) + ')'"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" x-model="newPart.quantity" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" min="1">
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button type="button" @click="addPart()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">Add</button>
                                <button type="button" @click="showPartModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>