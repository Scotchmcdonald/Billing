<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">Invoice Activity Timeline</h3>

        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach($activities as $index => $activity)
                    <li class="transition-all duration-300 hover:bg-gray-50 -mx-4 px-4 rounded-lg">
                        <div class="relative pb-8">
                            @if($index < count($activities) - 1)
                                <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                            @endif
                            
                            <div class="relative flex space-x-3">
                                <!-- Icon -->
                                <div>
                                    @if($activity->event === 'created')
                                        <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-file-invoice text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'approved')
                                        <span class="h-8 w-8 rounded-full bg-green-400 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-check text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'sent')
                                        <span class="h-8 w-8 rounded-full bg-blue-400 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-paper-plane text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'viewed')
                                        <span class="h-8 w-8 rounded-full bg-indigo-400 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-eye text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'payment_attempted')
                                        <span class="h-8 w-8 rounded-full bg-yellow-400 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-credit-card text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'paid')
                                        <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-check-circle text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'disputed')
                                        <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-exclamation-triangle text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'overdue')
                                        <span class="h-8 w-8 rounded-full bg-orange-500 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-clock text-white text-sm"></i>
                                        </span>
                                    @elseif($activity->event === 'reminder_sent')
                                        <span class="h-8 w-8 rounded-full bg-purple-400 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-envelope text-white text-sm"></i>
                                        </span>
                                    @else
                                        <span class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center ring-8 ring-white">
                                            <i class="fas fa-circle text-white text-sm"></i>
                                        </span>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $activity->description }}
                                        </p>
                                        @if($activity->properties)
                                            <div class="mt-1 text-xs text-gray-500">
                                                @foreach($activity->properties as $key => $value)
                                                    @if($key === 'amount')
                                                        <span class="mr-3">
                                                            <span class="font-medium">Amount:</span> ${{ number_format($value, 2) }}
                                                        </span>
                                                    @elseif($key === 'payment_method')
                                                        <span class="mr-3">
                                                            <span class="font-medium">Method:</span> {{ ucfirst($value) }}
                                                        </span>
                                                    @elseif($key === 'ip_address')
                                                        <span class="mr-3">
                                                            <span class="font-medium">IP:</span> {{ $value }}
                                                        </span>
                                                    @elseif($key === 'reason')
                                                        <div class="mt-1">
                                                            <span class="font-medium">Reason:</span> {{ $value }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                        @if($activity->causer)
                                            <p class="mt-1 text-xs text-gray-500">
                                                by {{ $activity->causer->name }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="whitespace-nowrap text-right text-sm text-gray-500">
                                        <time datetime="{{ $activity->created_at->toIso8601String() }}">
                                            {{ $activity->created_at->format('M d, Y') }}
                                        </time>
                                        <div class="text-xs">
                                            {{ $activity->created_at->format('g:i A') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        @if($activities->isEmpty())
            <div class="text-center py-12">
                <i class="fas fa-history text-gray-300 text-4xl mb-3"></i>
                <p class="text-sm text-gray-500">No activity recorded yet</p>
            </div>
        @endif
    </div>
</div>
