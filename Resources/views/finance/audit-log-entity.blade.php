<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Audit Log') }} - {{ ucfirst($type) }} #{{ $id }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        Audit Trail: {{ ucfirst($type) }} #{{ $id }}
                    </h1>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">
                        Complete history of changes and activities
                    </p>
                </div>
                <a href="{{ route('billing.finance.audit-log') }}" class="inline-flex items-center px-4 py-3 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 shadow-sm transition-all duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to All Logs
                </a>
            </div>
        </div>

        <!-- State Indicator -->
        <x-billing::state-indicator state="idle" />

        <!-- Summary Cards -->
        @if(isset($summary))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Events</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $summary['total_events'] ?? 0 }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Created</div>
                <div class="mt-2 text-lg text-gray-900 dark:text-gray-100">{{ $summary['created_at'] ?? 'N/A' }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Last Modified</div>
                <div class="mt-2 text-lg text-gray-900 dark:text-gray-100">{{ $summary['last_modified'] ?? 'N/A' }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 transition-all duration-200 hover:shadow-md">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Modified By</div>
                <div class="mt-2 text-lg text-gray-900 dark:text-gray-100">{{ $summary['unique_users'] ?? 0 }} users</div>
            </div>
        </div>
        @endif

        <!-- Audit Timeline -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Activity Timeline</h2>
            </div>
            <div class="p-6">
                @if($logs && $logs->count() > 0)
                    <div class="space-y-6">
                        @foreach($logs as $log)
                            <div class="flex gap-4">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                        <span class="text-blue-600 dark:text-blue-400">
                                            @if($log->event === 'created')
                                                ➕
                                            @elseif($log->event === 'updated')
                                                ✏️
                                            @elseif($log->event === 'deleted')
                                                🗑️
                                            @else
                                                📝
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-semibold text-gray-900 dark:text-gray-100">
                                                {{ $log->user ? $log->user->name : 'System' }}
                                            </span>
                                            <span class="text-gray-600 dark:text-gray-400">
                                                {{ $log->event }}
                                            </span>
                                        </div>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $log->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                    @if($log->old_values || $log->new_values)
                                        <div class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            <details>
                                                <summary class="cursor-pointer hover:text-gray-900 dark:hover:text-gray-200">
                                                    View Changes
                                                </summary>
                                                <div class="mt-2 p-3 bg-gray-50 dark:bg-gray-900 rounded">
                                                    @if($log->old_values)
                                                        <div class="mb-2">
                                                            <strong>Before:</strong>
                                                            <pre class="mt-1 text-xs">{{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                    @endif
                                                    @if($log->new_values)
                                                        <div>
                                                            <strong>After:</strong>
                                                            <pre class="mt-1 text-xs">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                                        </div>
                                                    @endif
                                                </div>
                                            </details>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-500 dark:text-gray-400">
                        No audit logs found for this entity
                    </p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
