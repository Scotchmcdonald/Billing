<x-app-layout>
    <div x-data="mileageTracker()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Mileage Tracker</h1>
                    <p class="mt-2 text-sm text-gray-600">Log and track travel to client sites for accurate reimbursement</p>
                </div>
                <button @click="openNewEntry()" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Log Mileage
                </button>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-600">This Month</p>
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900" x-text="stats.month_miles.toLocaleString() + ' mi'"></p>
                <p class="text-sm text-gray-500 mt-1" x-text="stats.month_trips + ' trips'"></p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-600">Pending Reimbursement</p>
                    <svg class="w-5 h-5 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-warning-600" x-text="'$' + stats.pending_amount.toFixed(2)"></p>
                <p class="text-sm text-gray-500 mt-1" x-text="stats.pending_miles + ' mi @ $' + rate.toFixed(3) + '/mi'"></p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-600">YTD Total</p>
                    <svg class="w-5 h-5 text-success-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-success-600" x-text="'$' + stats.ytd_amount.toLocaleString()"></p>
                <p class="text-sm text-gray-500 mt-1" x-text="stats.ytd_miles.toLocaleString() + ' mi reimbursed'"></p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-600">Current Rate</p>
                    <svg class="w-5 h-5 text-info-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <p class="text-3xl font-bold text-gray-900" x-text="'$' + rate.toFixed(3)"></p>
                <p class="text-sm text-gray-500 mt-1">per mile (IRS standard)</p>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                    <select x-model="filters.dateRange" @change="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="7">Last 7 Days</option>
                        <option value="30">Last 30 Days</option>
                        <option value="90">Last 90 Days</option>
                        <option value="ytd">Year to Date</option>
                        <option value="all">All Time</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select x-model="filters.status" @change="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">All Statuses</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="paid">Paid</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                    <select x-model="filters.client" @change="applyFilters()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        <option value="">All Clients</option>
                        <template x-for="client in clients" :key="client">
                            <option :value="client" x-text="client"></option>
                        </template>
                    </select>
                </div>
                <div class="flex items-end">
                    <button @click="exportMileage()" class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Export
                    </button>
                </div>
            </div>
        </div>

        <!-- Mileage Entries -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Mileage Log</h2>
            </div>

            <div class="divide-y divide-gray-200">
                <template x-for="entry in filteredEntries" :key="entry.id">
                    <div class="px-6 py-4 hover:bg-gray-50 transition-colors">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-base font-semibold text-gray-900" x-text="entry.date"></span>
                                    <span :class="{
                                        'bg-warning-100 text-warning-800': entry.status === 'pending',
                                        'bg-info-100 text-info-800': entry.status === 'approved',
                                        'bg-success-100 text-success-800': entry.status === 'paid'
                                    }" class="px-2 py-1 text-xs font-medium rounded-full" x-text="entry.status.toUpperCase()"></span>
                                    
                                    <template x-if="entry.has_receipt">
                                        <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-700 rounded-full flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                                            </svg>
                                            Receipt
                                        </span>
                                    </template>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Route</p>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <div class="text-sm">
                                                <p class="font-medium text-gray-900" x-text="entry.from_address"></p>
                                                <p class="text-gray-600">to</p>
                                                <p class="font-medium text-gray-900" x-text="entry.to_address"></p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Client & Purpose</p>
                                        <p class="text-sm font-medium text-gray-900" x-text="entry.client_name"></p>
                                        <p class="text-sm text-gray-600" x-text="entry.purpose"></p>
                                    </div>
                                    
                                    <div>
                                        <p class="text-xs text-gray-500 mb-1">Distance & Reimbursement</p>
                                        <p class="text-2xl font-bold text-primary-600" x-text="entry.miles + ' mi'"></p>
                                        <p class="text-sm font-medium text-gray-900" x-text="'$' + entry.amount.toFixed(2)"></p>
                                    </div>
                                </div>
                                
                                <div class="flex items-center gap-4">
                                    <button @click="viewOnMap(entry)" class="text-sm text-primary-600 hover:text-primary-800 font-medium flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                        </svg>
                                        View on Map
                                    </button>
                                    <button x-show="entry.has_receipt" @click="viewReceipt(entry)" class="text-sm text-gray-600 hover:text-gray-800 font-medium flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View Receipt
                                    </button>
                                    <button x-show="entry.status === 'pending'" @click="editEntry(entry)" class="text-sm text-gray-600 hover:text-gray-800 font-medium flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button x-show="entry.status === 'pending'" @click="deleteEntry(entry)" class="text-sm text-danger-600 hover:text-danger-800 font-medium flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="filteredEntries.length === 0" class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No mileage entries found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by logging your first trip.</p>
                    <button @click="openNewEntry()" class="mt-4 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                        Log Mileage
                    </button>
                </div>
            </div>
        </div>

        <!-- New Entry Modal -->
        <div x-show="showNewEntryModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div @click="showNewEntryModal = false" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
                <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Log New Mileage Entry</h3>
                    
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                <input type="date" x-model="newEntry.date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                                <select x-model="newEntry.client" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">Select Client</option>
                                    <template x-for="client in clients" :key="client">
                                        <option :value="client" x-text="client"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">From Address</label>
                            <input type="text" x-model="newEntry.from" placeholder="Enter starting location" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">To Address</label>
                            <input type="text" x-model="newEntry.to" placeholder="Enter destination" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <button @click="calculateDistance()" class="mt-2 text-sm text-primary-600 hover:text-primary-800 font-medium">
                                Calculate Distance via Google Maps
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Distance (miles)</label>
                                <input type="number" step="0.1" x-model="newEntry.miles" @input="calculateAmount()" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Reimbursement Amount</label>
                                <input type="text" x-model="'$' + newEntry.amount.toFixed(2)" readonly class="w-full rounded-md border-gray-300 bg-gray-50 shadow-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                            <input type="text" x-model="newEntry.purpose" placeholder="e.g., On-site troubleshooting" class="w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt (Optional)</label>
                            <input type="file" accept="image/*,.pdf" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        </div>
                    </div>
                    
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button @click="showNewEntryModal = false" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="saveEntry()" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700">
                            Save Entry
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mileageTracker() {
            return {
                rate: 0.655, // IRS 2024 standard mileage rate
                stats: {
                    month_miles: 387,
                    month_trips: 23,
                    pending_amount: 253.54,
                    pending_miles: 387,
                    ytd_amount: 3214.85,
                    ytd_miles: 4908
                },
                filters: {
                    dateRange: '30',
                    status: '',
                    client: ''
                },
                clients: ['Acme Corp', 'Widget Industries', 'Tech Solutions', 'Global Systems'],
                entries: [
                    {
                        id: 1,
                        date: '2024-12-15',
                        from_address: 'Office - 123 Main St',
                        to_address: 'Acme Corp - 456 Business Blvd',
                        client_name: 'Acme Corp',
                        purpose: 'Emergency server repair',
                        miles: 24.5,
                        amount: 16.05,
                        status: 'pending',
                        has_receipt: true
                    },
                    {
                        id: 2,
                        date: '2024-12-14',
                        from_address: 'Office - 123 Main St',
                        to_address: 'Widget Industries - 789 Tech Dr',
                        client_name: 'Widget Industries',
                        purpose: 'Quarterly maintenance',
                        miles: 18.2,
                        amount: 11.92,
                        status: 'approved',
                        has_receipt: false
                    },
                    {
                        id: 3,
                        date: '2024-12-10',
                        from_address: 'Office - 123 Main St',
                        to_address: 'Tech Solutions - 321 Innovation Way',
                        client_name: 'Tech Solutions',
                        purpose: 'New system installation',
                        miles: 42.8,
                        amount: 28.03,
                        status: 'paid',
                        has_receipt: true
                    }
                ],
                filteredEntries: [],
                showNewEntryModal: false,
                newEntry: {
                    date: '',
                    client: '',
                    from: '',
                    to: '',
                    miles: 0,
                    amount: 0,
                    purpose: ''
                },
                
                init() {
                    this.filteredEntries = this.entries;
                    this.newEntry.date = new Date().toISOString().split('T')[0];
                },
                
                applyFilters() {
                    let filtered = this.entries;
                    
                    if (this.filters.status) {
                        filtered = filtered.filter(e => e.status === this.filters.status);
                    }
                    
                    if (this.filters.client) {
                        filtered = filtered.filter(e => e.client_name === this.filters.client);
                    }
                    
                    this.filteredEntries = filtered;
                },
                
                openNewEntry() {
                    this.showNewEntryModal = true;
                },
                
                calculateDistance() {
                    // API call to Google Maps Distance Matrix API
                    // For demo, simulate distance calculation
                    this.newEntry.miles = (Math.random() * 50 + 10).toFixed(1);
                    this.calculateAmount();
                    alert('Distance calculated via Google Maps');
                },
                
                calculateAmount() {
                    this.newEntry.amount = parseFloat(this.newEntry.miles) * this.rate;
                },
                
                saveEntry() {
                    // API call to save entry
                    alert('Mileage entry saved successfully');
                    this.showNewEntryModal = false;
                },
                
                viewOnMap(entry) {
                    const url = `https://www.google.com/maps/dir/?api=1&origin=${encodeURIComponent(entry.from_address)}&destination=${encodeURIComponent(entry.to_address)}`;
                    window.open(url, '_blank');
                },
                
                viewReceipt(entry) {
                    // Open receipt viewer
                    window.open(`/billing/field/mileage/receipt/${entry.id}`, '_blank');
                },
                
                editEntry(entry) {
                    // Open edit modal
                    window.location.href = `/billing/field/mileage/${entry.id}/edit`;
                },
                
                deleteEntry(entry) {
                    if (confirm('Are you sure you want to delete this mileage entry?')) {
                        // API call to delete
                        alert('Mileage entry deleted');
                    }
                },
                
                exportMileage() {
                    window.location.href = `/billing/field/mileage/export?${new URLSearchParams(this.filters)}`;
                }
            }
        }
    </script>
</x-app-layout>
