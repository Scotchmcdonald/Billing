<x-app-layout>
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Quote Pipeline</h1>
                <p class="text-sm text-gray-600 mt-1">Kanban view of all quotes</p>
            </div>
            <a 
                href="{{ route('billing.quotes.create') }}" 
                class="px-4 py-3 bg-primary-600 dark:bg-primary-500 text-white rounded-lg hover:bg-primary-700 dark:hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 shadow-sm transition-all duration-150"
            >
                <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Quote
            </a>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-success-50 dark:bg-success-900 border border-success-200 dark:border-success-700 text-success-700 dark:text-success-200 px-4 py-3 rounded-lg transition-all duration-200">
                {{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-xs font-semibold text-gray-700 mb-1">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        id="search"
                        value="{{ request('search') }}"
                        placeholder="Company or quote number..."
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                </div>
                <div class="min-w-[150px]">
                    <label for="owner" class="block text-xs font-semibold text-gray-700 mb-1">Owner</label>
                    <select 
                        name="owner" 
                        id="owner"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                    >
                        <option value="">All Owners</option>
                        @foreach($owners as $owner)
                            <option value="{{ $owner->id }}" {{ request('owner') == $owner->id ? 'selected' : '' }}>
                                {{ $owner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button 
                    type="submit" 
                    class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 focus:ring-2 focus:ring-gray-500"
                >
                    Apply Filters
                </button>
            </form>
        </div>

        <!-- Kanban Board -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4" x-data="quotePipeline()">
            <!-- Draft Column -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Draft</h3>
                    <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-700 rounded-full">
                        {{ $quotes->where('status', 'draft')->count() }}
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($quotes->where('status', 'draft') as $quote)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition cursor-pointer"
                             @click="showQuoteDetails({{ $quote->id }})">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500">#{{ $quote->quote_number }}</span>
                                <span class="text-xs text-gray-500">{{ $quote->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 mb-2">{{ $quote->company->name }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-primary-600">${{ number_format($quote->total_amount / 100, 2) }}</span>
                                <span class="text-xs text-gray-600">{{ $quote->owner->name ?? 'Unassigned' }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Sent Column -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Sent</h3>
                    <span class="px-2 py-1 text-xs font-semibold bg-gray-200 text-gray-700 rounded-full">
                        {{ $quotes->where('status', 'sent')->count() }}
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($quotes->where('status', 'sent') as $quote)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 hover:shadow-md transition cursor-pointer"
                             @click="showQuoteDetails({{ $quote->id }})">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500">#{{ $quote->quote_number }}</span>
                                <span class="text-xs text-gray-500">{{ $quote->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 mb-2">{{ $quote->company->name }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-primary-600">${{ number_format($quote->total_amount / 100, 2) }}</span>
                                <span class="text-xs text-gray-600">{{ $quote->daysOpen }} days</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Viewed Column -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Viewed</h3>
                    <span class="px-2 py-1 text-xs font-semibold bg-primary-100 text-primary-700 rounded-full">
                        {{ $quotes->where('status', 'viewed')->count() }}
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($quotes->where('status', 'viewed') as $quote)
                        <div class="bg-white rounded-lg p-3 shadow-sm border-l-4 border-primary-500 hover:shadow-md transition cursor-pointer"
                             @click="showQuoteDetails({{ $quote->id }})">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500">#{{ $quote->quote_number }}</span>
                                <span class="text-xs text-primary-600 font-semibold">👁️ Viewed</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 mb-2">{{ $quote->company->name }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-primary-600">${{ number_format($quote->total_amount / 100, 2) }}</span>
                                <span class="text-xs text-gray-600">{{ $quote->daysOpen }} days</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Accepted Column -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Accepted</h3>
                    <span class="px-2 py-1 text-xs font-semibold bg-success-100 text-success-700 rounded-full">
                        {{ $quotes->where('status', 'accepted')->count() }}
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($quotes->where('status', 'accepted') as $quote)
                        <div class="bg-white rounded-lg p-3 shadow-sm border-l-4 border-success-500 hover:shadow-md transition cursor-pointer"
                             @click="showQuoteDetails({{ $quote->id }})">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500">#{{ $quote->quote_number }}</span>
                                <span class="text-xs text-success-600 font-semibold">✓ Accepted</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 mb-2">{{ $quote->company->name }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-success-600">${{ number_format($quote->total_amount / 100, 2) }}</span>
                                <span class="text-xs text-gray-600">{{ $quote->accepted_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Lost Column -->
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700">Lost</h3>
                    <span class="px-2 py-1 text-xs font-semibold bg-danger-100 text-danger-700 rounded-full">
                        {{ $quotes->where('status', 'lost')->count() }}
                    </span>
                </div>
                <div class="space-y-3">
                    @foreach($quotes->where('status', 'lost') as $quote)
                        <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200 opacity-60 hover:opacity-100 transition cursor-pointer"
                             @click="showQuoteDetails({{ $quote->id }})">
                            <div class="flex items-start justify-between mb-2">
                                <span class="text-xs font-semibold text-gray-500">#{{ $quote->quote_number }}</span>
                                <span class="text-xs text-danger-600 font-semibold">✕ Lost</span>
                            </div>
                            <p class="text-sm font-semibold text-gray-900 mb-2">{{ $quote->company->name }}</p>
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-gray-400">${{ number_format($quote->total_amount / 100, 2) }}</span>
                                <span class="text-xs text-gray-600">{{ $quote->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function quotePipeline() {
            return {
                showQuoteDetails(quoteId) {
                    window.location.href = `/billing/quotes/${quoteId}`;
                }
            }
        }
    </script>
    @endpush
</x-app-layout>
