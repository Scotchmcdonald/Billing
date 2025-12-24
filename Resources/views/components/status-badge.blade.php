@props(['status', 'label' => null])

@php
    $colors = [
        'success' => 'bg-success-50 text-success-700 border-success-200',
        'warning' => 'bg-warning-50 text-warning-700 border-warning-200',
        'danger' => 'bg-danger-50 text-danger-700 border-danger-200',
        'info' => 'bg-blue-50 text-blue-700 border-blue-200',
        'pending' => 'bg-warning-50 text-warning-700 border-warning-200',
    ];

    $statusKey = strtolower($status);
    $classes = $colors[$statusKey] ?? $colors['info'];
    $isPending = $statusKey === 'pending';
    $displayText = $label ?? ucfirst($status);
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border $classes"]) }}>
    @if($isPending)
        <span class="relative flex h-2 w-2 mr-1.5">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-warning-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-2 w-2 bg-warning-500"></span>
        </span>
    @endif
    {{ $displayText }}
</span>
