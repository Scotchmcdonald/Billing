@props(['title' => 'Troubleshooting', 'type' => 'warning'])

@php
    $typeClasses = [
        'error' => 'bg-danger-50 dark:bg-danger-900 border-danger-500 dark:border-danger-600',
        'warning' => 'bg-warning-50 dark:bg-warning-900 border-warning-500 dark:border-warning-600',
        'info' => 'bg-blue-50 dark:bg-blue-900 border-blue-500 dark:border-blue-600',
    ];
    
    $iconColors = [
        'error' => 'text-danger-600 dark:text-danger-400',
        'warning' => 'text-warning-600 dark:text-warning-400',
        'info' => 'text-blue-600 dark:text-blue-400',
    ];
    
    $textColors = [
        'error' => 'text-danger-900 dark:text-danger-100',
        'warning' => 'text-warning-900 dark:text-warning-100',
        'info' => 'text-blue-900 dark:text-blue-100',
    ];
    
    $baseClass = $typeClasses[$type] ?? $typeClasses['warning'];
    $iconColor = $iconColors[$type] ?? $iconColors['warning'];
    $textColor = $textColors[$type] ?? $textColors['warning'];
@endphp

<div class="{{ $baseClass }} border-l-4 p-4 my-4 rounded-r-md shadow-sm transition-all duration-200" role="alert">
    <div class="flex">
        <div class="flex-shrink-0">
            @if($type === 'error')
                <svg class="h-5 w-5 {{ $iconColor }}" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            @else
                <svg class="h-5 w-5 {{ $iconColor }}" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            @endif
        </div>
        <div class="ml-3 flex-1">
            <h3 class="text-sm leading-5 font-bold {{ $textColor }}">
                {{ $title }}
            </h3>
            <div class="mt-2 text-sm leading-5 {{ $textColor }} opacity-90">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
