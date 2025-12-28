@props([
    'current',
    'previous',
    'format' => 'number',
    'label' => 'vs last period',
    'inverse' => false
])

@php
    $change = $previous > 0 ? (($current - $previous) / $previous) * 100 : 0;
    $isPositive = $change > 0;
    $isGood = $inverse ? !$isPositive : $isPositive;
@endphp

<div class="flex items-center space-x-2 text-sm">
    @if($change > 0)
        <svg class="h-4 w-4 {{ $isGood ? 'text-success-600' : 'text-danger-600' }}" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
        </svg>
    @elseif($change < 0)
        <svg class="h-4 w-4 {{ $isGood ? 'text-success-600' : 'text-danger-600' }}" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
        </svg>
    @else
        <span class="text-gray-500">—</span>
    @endif
    
    <span class="{{ $isGood ? 'text-success-700' : 'text-danger-700' }} font-medium">
        {{ number_format(abs($change), 1) }}%
    </span>
    <span class="text-gray-600">{{ $label }}</span>
</div>
