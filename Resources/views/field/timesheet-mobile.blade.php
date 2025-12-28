{{-- Technician Daily Timesheet - Mobile-First Design --}}
<div x-data="timesheetApp()" x-init="init()" class="min-h-screen bg-gray-50">
    
    {{-- Mobile Header --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="px-4 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Today's Timesheet</h1>
                    <p class="text-xs text-gray-500" x-text="currentDate"></p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold text-primary-600" x-text="totalHours + 'h'"></div>
                    <div class="text-xs text-gray-500">Today</div>
                </div>
            </div>
        </div>

        {{-- Quick Stats Bar --}}
        <div class="px-4 py-2 bg-gray-50 border-t border-gray-200">
            <div class="flex items-center justify-between text-xs">
                <div class="flex items-center space-x-4">
                    <span class="text-gray-600">Billable: <span class="font-semibold text-success-600" x-text="billableHours + 'h'"></span></span>
                    <span class="text-gray-600">Internal: <span class="font-semibold text-info-600" x-text="internalHours + 'h'"></span></span>
                </div>
                <button @click="showSummary = !showSummary" class="text-primary-600 font-medium">
                    <span x-show="!showSummary">View Summary</span>
                    <span x-show="showSummary">Hide Summary</span>
                </button>
            </div>
        </div>
    </header>

    {{-- Summary Panel (Collapsible) --}}
    <div x-show="showSummary" x-transition class="bg-white border-b border-gray-200 px-4 py-3">
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="bg-success-50 rounded-lg p-2">
                <div class="text-lg font-bold text-success-700" x-text="'$' + estimatedRevenue"></div>
                <div class="text-xs text-gray-600">Est. Revenue</div>
            </div>
            <div class="bg-info-50 rounded-lg p-2">
                <div class="text-lg font-bold text-info-700" x-text="entries.length"></div>
                <div class="text-xs text-gray-600">Entries</div>
            </div>
            <div class="bg-warning-50 rounded-lg p-2">
                <div class="text-lg font-bold text-warning-700" x-text="uniqueClients"></div>
                <div class="text-xs text-gray-600">Clients</div>
            </div>
        </div>
    </div>

    {{-- Time Entries List --}}
    <main class="px-4 py-4 space-y-3 pb-24">
        
        {{-- Empty State --}}
        <div x-show="entries.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No entries yet</h3>
            <p class="mt-1 text-sm text-gray-500">Start tracking your time below</p>
        </div>

        {{-- Time Entry Cards --}}
        <template x-for="(entry, index) in entries" :key="index">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-gray-900" x-text="entry.client"></h3>
                        <p class="text-xs text-gray-600 mt-0.5" x-text="entry.description"></p>
                    </div>
                    <button @click="removeEntry(index)" class="text-danger-600 hover:text-danger-700">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center justify-between mt-3">
                    <div class="flex items-center space-x-3">
                        <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium"
                              :class="entry.billable ? 'bg-success-100 text-success-800' : 'bg-gray-100 text-gray-800'">
                            <span x-text="entry.billable ? 'Billable' : 'Internal'"></span>
                        </span>
                        <span class="text-sm text-gray-600" x-text="entry.ticket_number ? '#' + entry.ticket_number : ''"></span>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-gray-900" x-text="entry.hours + 'h'"></div>
                        <div class="text-xs text-gray-500" x-text="entry.time_range"></div>
                    </div>
                </div>
            </div>
        </template>
    </main>

    {{-- Floating Action Button --}}
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-4 py-3 safe-area-bottom">
        <button @click="showAddEntry = true" 
                class="w-full py-4 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 flex items-center justify-center space-x-2">
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
            </svg>
            <span>Add Time Entry</span>
        </button>
    </div>

    {{-- Add Entry Modal (Full Screen on Mobile) --}}
    <div x-show="showAddEntry" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 overflow-y-auto bg-white">
        
        <div class="sticky top-0 bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">Add Time Entry</h2>
            <button @click="showAddEntry = false" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-4 py-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Client *</label>
                <select x-model="newEntry.client" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                    <option value="">Select client...</option>
                    <template x-for="client in clients" :key="client">
                        <option :value="client" x-text="client"></option>
                    </template>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description *</label>
                <textarea x-model="newEntry.description" rows="3"
                          class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                          placeholder="What did you work on?"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                    <input type="time" x-model="newEntry.start_time"
                           class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                    <input type="time" x-model="newEntry.end_time"
                           class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hours</label>
                <div class="flex space-x-2">
                    <button @click="newEntry.hours = 0.5" class="flex-1 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" :class="newEntry.hours === 0.5 ? 'bg-primary-50 border-primary-600 text-primary-700' : ''">0.5h</button>
                    <button @click="newEntry.hours = 1" class="flex-1 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" :class="newEntry.hours === 1 ? 'bg-primary-50 border-primary-600 text-primary-700' : ''">1h</button>
                    <button @click="newEntry.hours = 2" class="flex-1 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" :class="newEntry.hours === 2 ? 'bg-primary-50 border-primary-600 text-primary-700' : ''">2h</button>
                    <button @click="newEntry.hours = 4" class="flex-1 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50" :class="newEntry.hours === 4 ? 'bg-primary-50 border-primary-600 text-primary-700' : ''">4h</button>
                    <input type="number" x-model="newEntry.hours" step="0.25" min="0" class="w-20 py-2 px-3 text-sm border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Ticket # (Optional)</label>
                <input type="text" x-model="newEntry.ticket_number"
                       class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                       placeholder="12345">
            </div>

            <div>
                <label class="flex items-center space-x-2">
                    <input type="checkbox" x-model="newEntry.billable"
                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm font-medium text-gray-700">Billable</span>
                </label>
            </div>

            <div class="sticky bottom-0 bg-white pt-4 pb-safe">
                <button @click="addEntry()" :disabled="!newEntry.client || !newEntry.description || !newEntry.hours"
                        class="w-full py-4 bg-success-600 text-white font-semibold rounded-lg hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-success-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Save Entry
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function timesheetApp() {
    return {
        showAddEntry: false,
        showSummary: false,
        currentDate: new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
        entries: [],
        clients: ['ABC Corp', 'XYZ Ltd', 'Tech Solutions', 'Finance Co'],
        newEntry: {
            client: '',
            description: '',
            start_time: '',
            end_time: '',
            hours: 1,
            ticket_number: '',
            billable: true
        },

        init() {
            // Load existing entries
            this.loadEntries();
        },

        get totalHours() {
            return this.entries.reduce((sum, e) => sum + parseFloat(e.hours), 0).toFixed(2);
        },

        get billableHours() {
            return this.entries.filter(e => e.billable).reduce((sum, e) => sum + parseFloat(e.hours), 0).toFixed(2);
        },

        get internalHours() {
            return this.entries.filter(e => !e.billable).reduce((sum, e) => sum + parseFloat(e.hours), 0).toFixed(2);
        },

        get estimatedRevenue() {
            return (this.billableHours * 150).toFixed(0);
        },

        get uniqueClients() {
            return new Set(this.entries.map(e => e.client)).size;
        },

        loadEntries() {
            // Mock data
            this.entries = [
                {
                    client: 'ABC Corp',
                    description: 'Fixed server issue and updated SSL certificates',
                    hours: 2.5,
                    time_range: '9:00 AM - 11:30 AM',
                    ticket_number: '1234',
                    billable: true
                }
            ];
        },

        addEntry() {
            const entry = {
                ...this.newEntry,
                time_range: this.newEntry.start_time && this.newEntry.end_time 
                    ? `${this.newEntry.start_time} - ${this.newEntry.end_time}`
                    : 'Manual entry'
            };
            this.entries.push(entry);
            this.resetNewEntry();
            this.showAddEntry = false;
        },

        removeEntry(index) {
            if (confirm('Remove this entry?')) {
                this.entries.splice(index, 1);
            }
        },

        resetNewEntry() {
            this.newEntry = {
                client: '',
                description: '',
                start_time: '',
                end_time: '',
                hours: 1,
                ticket_number: '',
                billable: true
            };
        }
    }
}
</script>

<style>
.safe-area-bottom {
    padding-bottom: env(safe-area-inset-bottom);
}

.pb-safe {
    padding-bottom: calc(1rem + env(safe-area-inset-bottom));
}
</style>
