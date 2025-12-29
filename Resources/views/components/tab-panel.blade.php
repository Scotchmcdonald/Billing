@props([
    'id' => '',
    'lazy' => false
])

<div 
    x-show="activeTab === '{{ $id }}'" 
    x-transition:enter="transition ease-out duration-200" 
    x-transition:enter-start="opacity-0 translate-y-2" 
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    role="tabpanel"
    {{ $attributes->merge(['class' => '']) }}
    x-cloak>
    @if($lazy)
        <div x-data="{ loaded: false }" x-init="if (activeTab === '{{ $id }}') loaded = true; $watch('activeTab', value => { if (value === '{{ $id }}' && !loaded) loaded = true })">
            <template x-if="loaded">
                <div>
                    {{ $slot }}
                </div>
            </template>
        </div>
    @else
        {{ $slot }}
    @endif
</div>
