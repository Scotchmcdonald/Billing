@props([
    'data' => [],
    'width' => 100,
    'height' => 30,
    'color' => 'primary'
])

@php
    $max = !empty($data) ? max($data) : 1;
    $min = !empty($data) ? min($data) : 0;
    $range = $max - $min;
    $range = $range > 0 ? $range : 1;
    
    $points = [];
    $count = count($data);
    
    if ($count > 0) {
        $xStep = $width / ($count - 1);
        foreach ($data as $index => $value) {
            $x = $index * $xStep;
            $y = $height - (($value - $min) / $range * $height);
            $points[] = "$x,$y";
        }
    }
    
    $pathData = 'M ' . implode(' L ', $points);
    
    $colorClass = match($color) {
        'success' => '#10b981',
        'warning' => '#f59e0b',
        'danger' => '#ef4444',
        'primary' => '#6366f1',
        default => '#6366f1'
    };
@endphp

@if(count($data) > 1)
<svg width="{{ $width }}" height="{{ $height }}" class="inline-block" viewBox="0 0 {{ $width }} {{ $height }}">
    <path d="{{ $pathData }}" fill="none" stroke="{{ $colorClass }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
@endif
