@extends('layouts.app')

@section('content')
<div x-data="bulkOverrides()" x-init="init()" class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold leading-7 text-gray-900">
                Bulk Price Override Manager
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Apply global price changes across multiple clients • Preview before applying
            </p>
        </div>

        <!-- Progress Stepper -->
        <div class="mb-8">
            <nav aria-label="Progress">
                <ol role="list" class="flex items-center">
                    <li class="relative pr-8 sm:pr-20 flex-1">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full" :class="step >= 2 ? 'bg-primary-600' : 'bg-gray-200'"></div>
                        </div>
                        <a href="#" @click.prevent="goToStep(1)" class="relative flex h-8 w-8 items-center justify-center rounded-full" :class="step >= 1 ? 'bg-primary-600' : 'bg-gray-300'">
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        </a>
                        <p class="mt-2 text-xs font-medium text-gray-900">Select Clients</p>
                    </li>

                    <li class="relative pr-8 sm:pr-20 flex-1">
                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                            <div class="h-0.5 w-full" :class="step >= 3 ? 'bg-primary-600' : 'bg-gray-200'"></div>
                        </div>
                        <a href="#" @click.prevent="step >= 2 && goToStep(2)" class="relative flex h-8 w-8 items-center justify-center rounded-full" :class="step >= 2 ? 'bg-primary-600' : 'bg-gray-300'">
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        </a>
                        <p class="mt-2 text-xs font-medium text-gray-900">Configure Changes</p>
                    </li>

                    <li class="relative">
                        <a href="#" @click.prevent="step >= 3 && goToStep(3)" class="relative flex h-8 w-8 items-center justify-center rounded-full" :class="step >= 3 ? 'bg-primary-600' : 'bg-gray-300'">
                            <span class="h-2.5 w-2.5 rounded-full bg-white"></span>
                        </a>
                        <p class="mt-2 text-xs font-medium text-gray-900">Preview & Apply</p>
                    </li>
                </ol>
            </nav>
        </div>

        <!-- Step 1: Selection -->
        <div x-show="step === 1" x-transition>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Select Clients</h3>
                
                <div class="space-y-4">
                    <!-- Selection Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Selection Type</label>
                        <div class="space-y-2">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer" :class="form.selection_type === 'all' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                                <input type="radio" x-model="form.selection_type" value="all" class="text-primary-600 focus:ring-primary-500">
                                <span class="ml-3 text-sm font-medium text-gray-900">All Active Clients</span>
                            </label>
                            
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer" :class="form.selection_type === 'tier' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                                <input type="radio" x-model="form.selection_type" value="tier" class="text-primary-600 focus:ring-primary-500">
                                <span class="ml-3 text-sm font-medium text-gray-900">Filter by Pricing Tier</span>
                            </label>
                            
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer" :class="form.selection_type === 'specific' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                                <input type="radio" x-model="form.selection_type" value="specific" class="text-primary-600 focus:ring-primary-500">
                                <span class="ml-3 text-sm font-medium text-gray-900">Select Specific Clients</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tier Filter -->
                    <div x-show="form.selection_type === 'tier'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pricing Tier</label>
                        <select x-model="form.tier" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">Select a tier...</option>
                            <option value="bronze">Bronze</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                            <option value="platinum">Platinum</option>
                        </select>
                    </div>

                    <!-- Specific Client Selection -->
                    <div x-show="form.selection_type === 'specific'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Clients</label>
                        <div class="border border-gray-300 rounded-md p-3 max-h-60 overflow-y-auto">
                            <template x-for="client in availableClients" :key="client.id">
                                <label class="flex items-center py-2">
                                    <input type="checkbox" :value="client.id" x-model="form.selected_clients" class="rounded text-primary-600 focus:ring-primary-500">
                                    <span class="ml-3 text-sm text-gray-900" x-text="client.name"></span>
                                </label>
                            </template>
                        </div>
                        <p class="mt-2 text-sm text-gray-500" x-text="form.selected_clients.length + ' clients selected'"></p>
                    </div>

                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-sm text-gray-700">
                            <strong x-text="getAffectedCount()"></strong> clients will be affected by this change
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button @click="nextStep()" :disabled="!canProceedFromStep1()" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Continue
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 2: Configuration -->
        <div x-show="step === 2" x-transition>
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Configure Price Changes</h3>
                
                <div class="space-y-6">
                    <!-- Change Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Change Type</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="form.change_type === 'percentage' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                                <input type="radio" x-model="form.change_type" value="percentage" class="text-primary-600 focus:ring-primary-500">
                                <span class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Percentage</span>
                                    <span class="block text-xs text-gray-500">e.g., +5%</span>
                                </span>
                            </label>
                            
                            <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="form.change_type === 'flat' ? 'border-primary-600 bg-primary-50' : 'border-gray-300'">
                                <input type="radio" x-model="form.change_type" value="flat" class="text-primary-600 focus:ring-primary-500">
                                <span class="ml-3">
                                    <span class="block text-sm font-medium text-gray-900">Flat Amount</span>
                                    <span class="block text-xs text-gray-500">e.g., +$50</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <span x-show="form.change_type === 'percentage'">Percentage Change (%)</span>
                            <span x-show="form.change_type === 'flat'">Amount Change ($)</span>
                        </label>
                        <div class="relative rounded-md shadow-sm">
                            <input type="number" x-model="form.amount" step="0.01" class="block w-full rounded-md border-gray-300 pr-12 focus:border-primary-500 focus:ring-primary-500" placeholder="0.00">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <span class="text-gray-500 sm:text-sm" x-text="form.change_type === 'percentage' ? '%' : '$'"></span>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Use negative values for price decreases</p>
                    </div>

                    <!-- Effective Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Effective Date</label>
                        <div class="flex items-center space-x-4">
                            <label class="flex items-center">
                                <input type="radio" x-model="form.effective_when" value="immediate" class="text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-900">Immediate</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" x-model="form.effective_when" value="scheduled" class="text-primary-600 focus:ring-primary-500">
                                <span class="ml-2 text-sm text-gray-900">Scheduled</span>
                            </label>
                        </div>
                        
                        <div x-show="form.effective_when === 'scheduled'" x-transition class="mt-3">
                            <input type="date" x-model="form.effective_date" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Reason for Change</label>
                        <textarea x-model="form.reason" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500" placeholder="e.g., Annual price adjustment, Vendor cost increase"></textarea>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="previousStep()" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>
                <button @click="generatePreview()" :disabled="!form.amount || form.amount == 0" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    Generate Preview
                    <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Step 3: Preview & Confirm -->
        <div x-show="step === 3" x-transition>
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview Changes</h3>
                
                <!-- Summary Stats -->
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Affected Clients</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900" x-text="preview.affected_count"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Total Impact</p>
                        <p class="mt-1 text-2xl font-bold" :class="preview.total_impact >= 0 ? 'text-success-700' : 'text-danger-700'" x-text="'$' + Math.abs(preview.total_impact / 100).toLocaleString()"></p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 uppercase tracking-wider">Avg Change per Client</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900" x-text="'$' + Math.abs(preview.avg_change / 100).toFixed(2)"></p>
                    </div>
                </div>

                <!-- Preview Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Current</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">New</th>
                                <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Change</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="item in preview.items" :key="item.client_id">
                                <tr>
                                    <td class="px-3 py-4 text-sm text-gray-900" x-text="item.client_name"></td>
                                    <td class="px-3 py-4 text-sm text-right text-gray-900" x-text="'$' + (item.current / 100).toFixed(2)"></td>
                                    <td class="px-3 py-4 text-sm text-right font-medium text-gray-900" x-text="'$' + (item.new / 100).toFixed(2)"></td>
                                    <td class="px-3 py-4 text-sm text-right font-medium" :class="item.change >= 0 ? 'text-success-700' : 'text-danger-700'">
                                        <span x-text="(item.change >= 0 ? '+' : '') + '$' + (item.change / 100).toFixed(2)"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Confirmation -->
            <div class="bg-warning-50 border-l-4 border-warning-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-warning-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm text-warning-800 font-medium">
                            This action will modify pricing for <strong x-text="preview.affected_count"></strong> clients. This change can be rolled back within 24 hours.
                        </p>
                        <div class="mt-4">
                            <label class="block text-sm text-warning-800 mb-2">
                                Type <code class="bg-warning-100 px-2 py-1 rounded font-mono">APPLY CHANGES</code> to confirm:
                            </label>
                            <input type="text" x-model="confirmation" class="block w-full rounded-md border-warning-300 shadow-sm focus:border-warning-500 focus:ring-warning-500" placeholder="Type here...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <button @click="previousStep()" type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </button>
                <button @click="applyChanges()" :disabled="confirmation !== 'APPLY CHANGES' || submitting" type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-success-600 hover:bg-success-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-text="submitting ? 'Applying...' : 'Apply Changes'"></span>
                </button>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function bulkOverrides() {
    return {
        step: 1,
        submitting: false,
        confirmation: '',
        
        form: {
            selection_type: 'all',
            tier: '',
            selected_clients: [],
            change_type: 'percentage',
            amount: null,
            effective_when: 'immediate',
            effective_date: null,
            reason: ''
        },
        
        preview: {
            affected_count: 0,
            total_impact: 0,
            avg_change: 0,
            items: []
        },
        
        availableClients: @json($clients ?? []),
        
        init() {
            // Initialize
        },
        
        getAffectedCount() {
            if (this.form.selection_type === 'all') {
                return this.availableClients.length;
            } else if (this.form.selection_type === 'tier' && this.form.tier) {
                return this.availableClients.filter(c => c.tier === this.form.tier).length;
            } else if (this.form.selection_type === 'specific') {
                return this.form.selected_clients.length;
            }
            return 0;
        },
        
        canProceedFromStep1() {
            if (this.form.selection_type === 'all') return true;
            if (this.form.selection_type === 'tier' && this.form.tier) return true;
            if (this.form.selection_type === 'specific' && this.form.selected_clients.length > 0) return true;
            return false;
        },
        
        nextStep() {
            if (this.step < 3) this.step++;
        },
        
        previousStep() {
            if (this.step > 1) {
                this.step--;
                this.confirmation = '';
            }
        },
        
        goToStep(target) {
            if (target <= this.step) this.step = target;
        },
        
        async generatePreview() {
            try {
                const response = await fetch('{{ route("billing.finance.bulk-overrides.preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.form)
                });
                
                if (response.ok) {
                    this.preview = await response.json();
                    this.nextStep();
                } else {
                    alert('Failed to generate preview');
                }
            } catch (error) {
                alert('An error occurred');
            }
        },
        
        async applyChanges() {
            this.submitting = true;
            
            try {
                const response = await fetch('{{ route("billing.finance.bulk-overrides.apply") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.form)
                });
                
                if (response.ok) {
                    window.location.href = '{{ route("billing.finance.bulk-overrides.success") }}';
                } else {
                    alert('Failed to apply changes');
                }
            } catch (error) {
                alert('An error occurred');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
@endsection
