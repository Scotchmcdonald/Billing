@props(['amount', 'currency' => 'USD', 'showCurrency' => true])

@php
    $formatted = number_format($amount, 2);
@endphp

<span {{ $attributes->merge(['class' => 'font-mono tracking-tight']) }}>
    @if($showCurrency)
        <span class="text-gray-500 text-xs mr-0.5">{{ $currency }}</span>
    @endif
    <span class="font-semibold">{{ $formatted }}</span>
</span>
