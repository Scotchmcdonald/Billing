@props([
    'title',
    'value',
    'trend' => null,
    'format' => 'number',
    'status' => 'neutral',
    'icon' => null
])

<div class="relative overflow-hidden rounded-lg bg-white px-4 py-5 shadow-sm sm:p-6">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <dt class="truncate text-xs font-medium text-gray-500 uppercase tracking-wider">
                {{ $title }}
            </dt>
            <dd class="mt-2 text-3xl font-bold text-gray-900">
                @if($format === 'currency')
                    ${{ number_format($value / 100, 0) }}
                @elseif($format === 'percentage')
                    {{ number_format($value, 1) }}%
                @else
                    {{ number_format($value) }}
                @endif
            </dd>
            
            @if($trend)
                <div class="mt-2 flex items-center text-sm">
                    @if($trend['change'] > 0)
                        <svg class="h-4 w-4 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-1 text-success-700 font-medium">
                            {{ abs($trend['change']) }}%
                        </span>
                    @elseif($trend['change'] < 0)
                        <svg class="h-4 w-4 text-danger-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        <span class="ml-1 text-danger-700 font-medium">
                            {{ abs($trend['change']) }}%
                        </span>
                    @else
                        <span class="text-gray-600 font-medium">—</span>
                    @endif
                    <span class="ml-2 text-gray-600">{{ $trend['label'] ?? 'vs last month' }}</span>
                </div>
            @endif
        </div>
        
        @if($icon)
            <div class="ml-4 flex-shrink-0">
                <div class="p-3 rounded-lg
                    @if($status === 'success') bg-success-50
                    @elseif($status === 'warning') bg-warning-50
                    @elseif($status === 'danger') bg-danger-50
                    @else bg-gray-50
                    @endif
                ">
                    <svg class="h-8 w-8
                        @if($status === 'success') text-success-600
                        @elseif($status === 'warning') text-warning-600
                        @elseif($status === 'danger') text-danger-600
                        @else text-gray-600
                        @endif
                    " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        {!! $icon !!}
                    </svg>
                </div>
            </div>
        @endif
    </div>
    
    {{ $slot }}
</div>
