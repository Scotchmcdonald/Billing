@props([
    'status' => 'current',
    'daysOverdue' => 0,
    'amount' => 0,
    'showTooltip' => true
])

@php
    $config = [
        'current' => [
            'class' => 'bg-success-100 text-success-800',
            'label' => 'Current',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ],
        '1-30' => [
            'class' => 'bg-warning-100 text-warning-800',
            'label' => '1-30 Days',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />'
        ],
        '31-60' => [
            'class' => 'bg-danger-100 text-danger-700',
            'label' => '31-60 Days',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />'
        ],
        '60+' => [
            'class' => 'bg-danger-100 text-danger-800',
            'label' => '60+ Days',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />'
        ]
    ];
    
    $cfg = $config[$status] ?? $config['current'];
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cfg['class'] }}" 
      @if($showTooltip)
      x-data="{ open: false }" 
      @mouseenter="open = true" 
      @mouseleave="open = false"
      @endif>
    <svg class="-ml-0.5 mr-1.5 h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        {!! $cfg['icon'] !!}
    </svg>
    {{ $cfg['label'] }}
    
    @if($showTooltip)
    <div x-show="open" x-transition class="absolute z-10 px-3 py-2 text-xs font-normal text-white bg-gray-900 rounded-lg shadow-sm" style="bottom: 100%; left: 50%; transform: translateX(-50%);">
        @if($daysOverdue > 0)
            <p>{{ number_format($daysOverdue, 0) }} days overdue</p>
            <p>${{ number_format($amount / 100, 2) }} outstanding</p>
        @else
            <p>All invoices current</p>
        @endif
    </div>
    @endif
</span>
