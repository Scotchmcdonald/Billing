@extends('layouts.app')

@section('content')
<div x-data="salesPipeline()" x-init="init()" class="py-6">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Sales Pipeline
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Track quotes from creation to close • Drag to move stages
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
                <!-- Filters Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Filters
                        <span x-show="activeFilters > 0" class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800" x-text="activeFilters"></span>
                    </button>
                    
                    <div x-show="open" x-transition class="absolute right-0 z-10 mt-2 w-72 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                        <div class="p-4">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                                    <select x-model="filters.date_range" @change="applyFilters()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        <option value="all">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="quarter">This Quarter</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Sales Agent</label>
                                    <select x-model="filters.agent" @change="applyFilters()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        <option value="all">All Agents</option>
                                        @foreach($agents as $agent)
                                            <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Client Type</label>
                                    <select x-model="filters.client_type" @change="applyFilters()" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        <option value="all">All Clients</option>
                                        <option value="new">New Prospects</option>
                                        <option value="existing">Existing Clients</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Value Range</label>
                                    <div class="flex items-center space-x-2">
                                        <input type="number" x-model="filters.min_value" @change="applyFilters()" placeholder="Min" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                        <span class="text-gray-500">-</span>
                                        <input type="number" x-model="filters.max_value" @change="applyFilters()" placeholder="Max" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm">
                                    </div>
                                </div>
                                
                                <button @click="clearFilters()" type="button" class="w-full text-sm text-gray-700 hover:text-gray-900">
                                    Clear All Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <a href="{{ route('billing.finance.quotes.create') }}" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    New Quote
                </a>
            </div>
        </div>

        <!-- Pipeline Metrics -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-4 mb-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Pipeline Value</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900" x-text="'$' + formatNumber(metrics.total_value)"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Deal Size</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900" x-text="'$' + formatNumber(metrics.avg_deal_size)"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Conversion Rate</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900" x-text="metrics.conversion_rate.toFixed(1) + '%'"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg Days to Close</p>
                            <p class="mt-2 text-2xl font-bold text-gray-900" x-text="Math.round(metrics.avg_days_to_close)"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="flex space-x-4 overflow-x-auto pb-4">
            <!-- Draft Stage -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Draft
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-800" x-text="quotes.draft.length"></span>
                    </div>
                    <div class="space-y-3 min-h-[200px]" 
                         x-ref="draft"
                         data-stage="draft">
                        <template x-for="quote in quotes.draft" :key="quote.id">
                            <div x-html="renderQuoteCard(quote)" 
                                 :data-quote-id="quote.id"
                                 class="quote-card cursor-move">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Sent Stage -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Sent
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-200 text-blue-800" x-text="quotes.sent.length"></span>
                    </div>
                    <div class="space-y-3 min-h-[200px]"
                         x-ref="sent"
                         data-stage="sent">
                        <template x-for="quote in quotes.sent" :key="quote.id">
                            <div x-html="renderQuoteCard(quote)"
                                 :data-quote-id="quote.id"
                                 class="quote-card cursor-move">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Viewed Stage -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Viewed
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-200 text-purple-800" x-text="quotes.viewed.length"></span>
                    </div>
                    <div class="space-y-3 min-h-[200px]"
                         x-ref="viewed"
                         data-stage="viewed">
                        <template x-for="quote in quotes.viewed" :key="quote.id">
                            <div x-html="renderQuoteCard(quote)"
                                 :data-quote-id="quote.id"
                                 class="quote-card cursor-move">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Negotiating Stage -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-yellow-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Negotiating
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-200 text-yellow-800" x-text="quotes.negotiating.length"></span>
                    </div>
                    <div class="space-y-3 min-h-[200px]"
                         x-ref="negotiating"
                         data-stage="negotiating">
                        <template x-for="quote in quotes.negotiating" :key="quote.id">
                            <div x-html="renderQuoteCard(quote)"
                                 :data-quote-id="quote.id"
                                 class="quote-card cursor-move">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Accepted Stage -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Accepted
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-200 text-green-800" x-text="quotes.accepted.length"></span>
                    </div>
                    <div class="space-y-3 min-h-[200px]"
                         x-ref="accepted"
                         data-stage="accepted">
                        <template x-for="quote in quotes.accepted" :key="quote.id">
                            <div x-html="renderQuoteCard(quote)"
                                 :data-quote-id="quote.id"
                                 class="quote-card cursor-move">
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Lost Stage -->
            <div class="flex-shrink-0 w-80">
                <div class="bg-red-50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                            Lost
                        </h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-200 text-red-800" x-text="quotes.lost.length"></span>
                    </div>
                    <div class="space-y-3 min-h-[200px]"
                         x-ref="lost"
                         data-stage="lost">
                        <template x-for="quote in quotes.lost" :key="quote.id">
                            <div x-html="renderQuoteCard(quote)"
                                 :data-quote-id="quote.id"
                                 class="quote-card cursor-move">
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
function salesPipeline() {
    return {
        quotes: {
            draft: @json($pipeline['draft'] ?? []),
            sent: @json($pipeline['sent'] ?? []),
            viewed: @json($pipeline['viewed'] ?? []),
            negotiating: @json($pipeline['negotiating'] ?? []),
            accepted: @json($pipeline['accepted'] ?? []),
            lost: @json($pipeline['lost'] ?? [])
        },
        
        metrics: @json($metrics),
        
        filters: {
            date_range: 'all',
            agent: 'all',
            client_type: 'all',
            min_value: null,
            max_value: null
        },
        
        sortables: {},
        
        get activeFilters() {
            let count = 0;
            if (this.filters.date_range !== 'all') count++;
            if (this.filters.agent !== 'all') count++;
            if (this.filters.client_type !== 'all') count++;
            if (this.filters.min_value || this.filters.max_value) count++;
            return count;
        },
        
        init() {
            // Initialize Sortable.js on each stage
            const stages = ['draft', 'sent', 'viewed', 'negotiating', 'accepted', 'lost'];
            
            stages.forEach(stage => {
                const el = this.$refs[stage];
                if (el) {
                    this.sortables[stage] = Sortable.create(el, {
                        group: 'quotes',
                        animation: 150,
                        handle: '.quote-card',
                        onEnd: (evt) => this.handleDrop(evt)
                    });
                }
            });
        },
        
        renderQuoteCard(quote) {
            const daysInStage = Math.floor((Date.now() - new Date(quote.updated_at).getTime()) / (1000 * 60 * 60 * 24));
            const marginColor = quote.margin >= 30 ? 'text-success-700' : quote.margin >= 20 ? 'text-warning-700' : 'text-danger-700';
            const needsAttention = daysInStage > 7 && ['sent', 'viewed'].includes(quote.status);
            
            return `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-900">${quote.company_name}</p>
                            <p class="text-xs text-gray-500">${quote.number}</p>
                        </div>
                        ${needsAttention ? '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-warning-100 text-warning-800">!</span>' : ''}
                    </div>
                    
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-lg font-bold text-gray-900">$${this.formatNumber(quote.total)}</span>
                        <span class="${marginColor} text-xs font-medium">${quote.margin}% margin</span>
                    </div>
                    
                    <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                        <span>${daysInStage} days in stage</span>
                        <span>${quote.agent_name}</span>
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        <a href="/billing/quotes/${quote.id}" class="flex-1 text-center px-3 py-1.5 text-xs font-medium text-primary-600 hover:text-primary-700 bg-primary-50 rounded">
                            View
                        </a>
                        ${quote.status === 'accepted' ? `
                            <button onclick="convertQuote(${quote.id})" class="flex-1 text-center px-3 py-1.5 text-xs font-medium text-white bg-success-600 hover:bg-success-700 rounded">
                                Convert
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        },
        
        async handleDrop(evt) {
            const quoteId = parseInt(evt.item.dataset.quoteId);
            const newStage = evt.to.dataset.stage;
            const oldStage = evt.from.dataset.stage;
            
            if (newStage === oldStage) return;
            
            // Confirmation for Lost stage
            if (newStage === 'lost') {
                if (!confirm('Mark this quote as lost? This action can be undone.')) {
                    // Revert the move
                    evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
                    return;
                }
            }
            
            try {
                const response = await fetch(`/billing/quotes/${quoteId}/update-stage`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ stage: newStage })
                });
                
                if (response.ok) {
                    // Update local data
                    const quoteIndex = this.quotes[oldStage].findIndex(q => q.id === quoteId);
                    const quote = this.quotes[oldStage][quoteIndex];
                    this.quotes[oldStage].splice(quoteIndex, 1);
                    this.quotes[newStage].push({...quote, status: newStage});
                    
                    // Refresh metrics
                    await this.refreshMetrics();
                } else {
                    throw new Error('Failed to update quote stage');
                }
            } catch (error) {
                alert('Failed to update quote stage. Please try again.');
                // Revert the move
                evt.from.insertBefore(evt.item, evt.from.children[evt.oldIndex]);
            }
        },
        
        async applyFilters() {
            // Implementation would filter quotes
            console.log('Applying filters:', this.filters);
        },
        
        clearFilters() {
            this.filters = {
                date_range: 'all',
                agent: 'all',
                client_type: 'all',
                min_value: null,
                max_value: null
            };
            this.applyFilters();
        },
        
        async refreshMetrics() {
            try {
                const response = await fetch('/billing/quotes/pipeline-metrics');
                const data = await response.json();
                this.metrics = data;
            } catch (error) {
                console.error('Failed to refresh metrics:', error);
            }
        },
        
        formatNumber(num) {
            return (num / 100).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }
    }
}

function convertQuote(quoteId) {
    window.location.href = `/billing/quotes/${quoteId}/convert`;
}
</script>
@endpush
@endsection
