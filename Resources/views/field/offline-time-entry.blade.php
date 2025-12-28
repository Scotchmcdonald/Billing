{{-- Offline Time Entry (PWA) - Phase 17 Story T.1 --}}
{{-- Progressive Web App with Service Worker for offline capability --}}

@extends('layouts.app')

@section('title', 'Offline Time Entry')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-4xl" x-data="offlineTimeEntry()">
    
    {{-- PWA Install Banner --}}
    <div x-show="showInstallPrompt" 
         x-transition
         class="mb-6 bg-primary-50 border border-primary-200 rounded-lg p-4">
        <div class="flex items-start justify-between">
            <div class="flex items-start space-x-3">
                <svg class="w-6 h-6 text-primary-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <div>
                    <h3 class="font-semibold text-primary-900">Install FinOps Field App</h3>
                    <p class="text-sm text-primary-700 mt-1">Add to your home screen for offline access to time tracking.</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button @click="installPWA()" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-md hover:bg-primary-700">
                    Install
                </button>
                <button @click="showInstallPrompt = false" class="p-2 text-primary-500 hover:text-primary-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Network Status Indicator --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">Time Entry</h1>
            <div class="flex items-center space-x-3">
                {{-- Online/Offline Status --}}
                <div class="flex items-center space-x-2">
                    <div class="relative">
                        <div :class="isOnline ? 'bg-success-500' : 'bg-warning-500'" class="w-2 h-2 rounded-full"></div>
                        <div :class="isOnline ? 'bg-success-500' : 'bg-warning-500'" 
                             class="absolute inset-0 w-2 h-2 rounded-full animate-ping opacity-75"></div>
                    </div>
                    <span class="text-sm font-medium" :class="isOnline ? 'text-success-700' : 'text-warning-700'" x-text="isOnline ? 'Online' : 'Offline'"></span>
                </div>
                
                {{-- Sync Status --}}
                <div x-show="pendingSync > 0" class="flex items-center space-x-2 px-3 py-1 bg-info-50 rounded-md">
                    <svg class="w-4 h-4 text-info-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span class="text-sm font-medium text-info-700" x-text="`${pendingSync} pending`"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <button @click="startNewEntry()" 
                class="flex items-center space-x-3 p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:border-primary-300 hover:shadow-md transition-all">
            <div class="flex-shrink-0 w-12 h-12 bg-primary-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900">New Entry</div>
                <div class="text-sm text-gray-500">Log work time</div>
            </div>
        </button>

        <button @click="tab = 'pending'" 
                class="flex items-center space-x-3 p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:border-warning-300 hover:shadow-md transition-all">
            <div class="flex-shrink-0 w-12 h-12 bg-warning-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-warning-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900">Pending Sync</div>
                <div class="text-sm text-gray-500" x-text="`${pendingSync} entries`"></div>
            </div>
        </button>

        <button @click="syncNow()" 
                :disabled="!isOnline || syncing"
                class="flex items-center space-x-3 p-4 bg-white rounded-lg shadow-sm border border-gray-200 hover:border-success-300 hover:shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed">
            <div class="flex-shrink-0 w-12 h-12 bg-success-100 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-success-600" :class="syncing && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </div>
            <div class="text-left">
                <div class="font-semibold text-gray-900">Sync Now</div>
                <div class="text-sm text-gray-500" x-text="syncing ? 'Syncing...' : 'Push to server'"></div>
            </div>
        </button>
    </div>

    {{-- Tab Navigation --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button @click="tab = 'today'" 
                    :class="tab === 'today' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Today
            </button>
            <button @click="tab = 'week'" 
                    :class="tab === 'week' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                This Week
            </button>
            <button @click="tab = 'pending'" 
                    :class="tab === 'pending' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center space-x-2">
                <span>Pending Sync</span>
                <span x-show="pendingSync > 0" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800" x-text="pendingSync"></span>
            </button>
        </nav>
    </div>

    {{-- Time Entry List --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="divide-y divide-gray-200">
            <template x-for="entry in filteredEntries()" :key="entry.id">
                <div class="p-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <h3 class="font-semibold text-gray-900" x-text="entry.client_name"></h3>
                                <span x-show="!entry.synced" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning-100 text-warning-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    Not Synced
                                </span>
                                <span x-show="entry.synced" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success-100 text-success-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Synced
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1" x-text="entry.description"></p>
                            <div class="flex items-center space-x-4 mt-2 text-sm text-gray-500">
                                <span x-text="entry.date"></span>
                                <span>•</span>
                                <span x-text="`${entry.hours} hours`"></span>
                                <span x-show="entry.ticket_number">•</span>
                                <span x-show="entry.ticket_number" x-text="`Ticket #${entry.ticket_number}`"></span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button @click="editEntry(entry)" 
                                    class="p-2 text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="deleteEntry(entry.id)" 
                                    class="p-2 text-gray-400 hover:text-danger-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty State --}}
            <div x-show="filteredEntries().length === 0" class="p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No time entries</h3>
                <p class="mt-1 text-sm text-gray-500" x-text="tab === 'pending' ? 'All entries are synced' : 'No entries for this period'"></p>
                <div x-show="tab !== 'pending'" class="mt-6">
                    <button @click="startNewEntry()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Add Time Entry
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- New/Edit Entry Modal --}}
    <div x-show="showModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto" 
         x-cloak>
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form @submit.prevent="saveEntry()">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" x-text="currentEntry.id ? 'Edit Time Entry' : 'New Time Entry'"></h3>

                                {{-- Client Selection --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Client</label>
                                    <select x-model="currentEntry.client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                                        <option value="">Select client...</option>
                                        <template x-for="client in clients" :key="client.id">
                                            <option :value="client.id" x-text="client.name"></option>
                                        </template>
                                    </select>
                                </div>

                                {{-- Ticket Number (Optional) --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Ticket # (Optional)</label>
                                    <input type="text" x-model="currentEntry.ticket_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="e.g., 12345">
                                </div>

                                {{-- Date --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                    <input type="date" x-model="currentEntry.date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" :max="new Date().toISOString().split('T')[0]">
                                </div>

                                {{-- Hours --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Hours</label>
                                    <input type="number" x-model.number="currentEntry.hours" required step="0.25" min="0.25" max="24" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="e.g., 2.5">
                                </div>

                                {{-- Description --}}
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                                    <textarea x-model="currentEntry.description" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="What did you work on?"></textarea>
                                </div>

                                {{-- Offline Notice --}}
                                <div x-show="!isOnline" class="rounded-md bg-warning-50 border border-warning-200 p-3">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        <div class="ml-3">
                                            <p class="text-sm text-warning-700">
                                                You're offline. This entry will be saved locally and synced when you're back online.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary-600 text-base font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Entry
                        </button>
                        <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function offlineTimeEntry() {
    return {
        isOnline: navigator.onLine,
        syncing: false,
        showModal: false,
        showInstallPrompt: false,
        deferredPrompt: null,
        tab: 'today',
        pendingSync: 0,
        entries: [],
        clients: @json($clients ?? []),
        currentEntry: {
            id: null,
            client_id: '',
            client_name: '',
            ticket_number: '',
            date: new Date().toISOString().split('T')[0],
            hours: '',
            description: '',
            synced: false,
            created_at: null
        },

        async init() {
            // Initialize IndexedDB
            await this.initDB();
            
            // Load entries from IndexedDB
            await this.loadEntries();
            
            // Set up network listeners
            window.addEventListener('online', () => {
                this.isOnline = true;
                this.syncNow();
            });
            
            window.addEventListener('offline', () => {
                this.isOnline = false;
            });

            // Listen for PWA install prompt
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                this.deferredPrompt = e;
                this.showInstallPrompt = true;
            });

            // Register Service Worker
            if ('serviceWorker' in navigator) {
                try {
                    await navigator.serviceWorker.register('/service-worker.js');
                    console.log('Service Worker registered');
                } catch (error) {
                    console.error('Service Worker registration failed:', error);
                }
            }
        },

        async initDB() {
            return new Promise((resolve, reject) => {
                const request = indexedDB.open('FinOpsTimeEntries', 1);
                
                request.onerror = () => reject(request.error);
                request.onsuccess = () => {
                    this.db = request.result;
                    resolve();
                };
                
                request.onupgradeneeded = (event) => {
                    const db = event.target.result;
                    if (!db.objectStoreNames.contains('timeEntries')) {
                        const objectStore = db.createObjectStore('timeEntries', { keyPath: 'id', autoIncrement: true });
                        objectStore.createIndex('date', 'date', { unique: false });
                        objectStore.createIndex('synced', 'synced', { unique: false });
                    }
                };
            });
        },

        async loadEntries() {
            const transaction = this.db.transaction(['timeEntries'], 'readonly');
            const objectStore = transaction.objectStore('timeEntries');
            const request = objectStore.getAll();
            
            request.onsuccess = () => {
                this.entries = request.result;
                this.updatePendingCount();
            };
        },

        filteredEntries() {
            const now = new Date();
            const today = now.toISOString().split('T')[0];
            // Create new Date object to avoid mutation
            const weekDate = new Date(now.getTime());
            const startOfWeek = new Date(weekDate.setDate(weekDate.getDate() - weekDate.getDay()));
            const weekStart = startOfWeek.toISOString().split('T')[0];

            if (this.tab === 'today') {
                return this.entries.filter(e => e.date === today);
            } else if (this.tab === 'week') {
                return this.entries.filter(e => e.date >= weekStart);
            } else if (this.tab === 'pending') {
                return this.entries.filter(e => !e.synced);
            }
            return this.entries;
        },

        startNewEntry() {
            this.currentEntry = {
                id: null,
                client_id: '',
                client_name: '',
                ticket_number: '',
                date: new Date().toISOString().split('T')[0],
                hours: '',
                description: '',
                synced: false,
                created_at: new Date().toISOString()
            };
            this.showModal = true;
        },

        editEntry(entry) {
            this.currentEntry = { ...entry };
            this.showModal = true;
        },

        async saveEntry() {
            // Set client name from selected client
            const selectedClient = this.clients.find(c => c.id == this.currentEntry.client_id);
            if (selectedClient) {
                this.currentEntry.client_name = selectedClient.name;
            }

            const transaction = this.db.transaction(['timeEntries'], 'readwrite');
            const objectStore = transaction.objectStore('timeEntries');
            
            if (this.currentEntry.id) {
                objectStore.put(this.currentEntry);
            } else {
                this.currentEntry.created_at = new Date().toISOString();
                objectStore.add(this.currentEntry);
            }

            transaction.oncomplete = () => {
                this.loadEntries();
                this.showModal = false;
                
                // Try to sync if online
                if (this.isOnline) {
                    this.syncNow();
                }
            };

            transaction.onerror = (error) => {
                console.error('Failed to save entry:', error);
                alert('Failed to save time entry. Please try again.');
            };
        },

        async deleteEntry(id) {
            if (!confirm('Delete this time entry?')) return;

            const transaction = this.db.transaction(['timeEntries'], 'readwrite');
            const objectStore = transaction.objectStore('timeEntries');
            objectStore.delete(id);

            transaction.oncomplete = () => {
                this.loadEntries();
            };
        },

        async syncNow() {
            if (!this.isOnline || this.syncing) return;

            this.syncing = true;
            const unsyncedEntries = this.entries.filter(e => !e.synced);

            for (const entry of unsyncedEntries) {
                try {
                    const response = await fetch('/api/time-entries', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(entry)
                    });

                    if (response.ok) {
                        // Mark as synced
                        entry.synced = true;
                        const transaction = this.db.transaction(['timeEntries'], 'readwrite');
                        const objectStore = transaction.objectStore('timeEntries');
                        objectStore.put(entry);
                    }
                } catch (error) {
                    console.error('Sync failed for entry:', entry.id, error);
                }
            }

            await this.loadEntries();
            this.syncing = false;
        },

        updatePendingCount() {
            this.pendingSync = this.entries.filter(e => !e.synced).length;
        },

        async installPWA() {
            if (!this.deferredPrompt) return;

            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                this.showInstallPrompt = false;
            }
            
            this.deferredPrompt = null;
        }
    }
}
</script>
@endsection
