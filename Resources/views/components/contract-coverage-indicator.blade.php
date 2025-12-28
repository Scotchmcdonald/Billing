@props([
    'coverage' => 'unknown',
    'details' => null
])

@php
    $config = [
        'covered' => [
            'class' => 'bg-success-100 text-success-800 border-success-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'label' => 'Covered'
        ],
        'partial' => [
            'class' => 'bg-warning-100 text-warning-800 border-warning-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
            'label' => 'Partially Covered'
        ],
        'not_covered' => [
            'class' => 'bg-danger-100 text-danger-800 border-danger-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'label' => 'Not Covered'
        ],
        'unknown' => [
            'class' => 'bg-gray-100 text-gray-800 border-gray-200',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'label' => 'Unknown'
        ]
    ];
    
    $cfg = $config[$coverage] ?? $config['unknown'];
@endphp

<div class="inline-flex items-center px-3 py-1 rounded-md text-sm font-medium border {{ $cfg['class'] }}"
     @if($details)
     x-data="{ open: false }"
     @mouseenter="open = true"
     @mouseleave="open = false"
     @endif>
    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $cfg['icon'] !!}
    </svg>
    {{ $cfg['label'] }}
    
    @if($details)
    <div x-show="open" x-transition class="absolute z-10 mt-2 p-3 text-xs font-normal text-white bg-gray-900 rounded-lg shadow-lg" style="top: 100%; left: 0; min-width: 200px;">
        <p class="font-semibold mb-1">{{ $details['subscription_name'] ?? 'Details' }}</p>
        <p class="text-gray-300">{{ $details['description'] ?? 'Check subscription for coverage details' }}</p>
        @if(isset($details['link']))
            <a href="{{ $details['link'] }}" class="block mt-2 text-primary-300 hover:text-primary-200">View Details →</a>
        @endif
    </div>
    @endif
</div>
