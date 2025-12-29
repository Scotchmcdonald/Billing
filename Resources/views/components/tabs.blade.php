@props([
    'active' => '',
    'tabs' => [],
    'showCounts' => false
])

<div x-data="{ 
    activeTab: '{{ $active }}',
    init() {
        // Check URL hash
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            if ({{ json_encode(collect($tabs)->pluck('id')->toArray()) }}.includes(hash)) {
                this.activeTab = hash;
            }
        }
        
        // Watch for tab changes and update URL
        this.$watch('activeTab', value => {
            const url = new URL(window.location);
            url.searchParams.set('tab', value);
            if (url.hash) url.hash = '';
            window.history.pushState({}, '', url);
        });
    }
}" class="w-full">
    <!-- Tab Navigation -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8" role="tablist">
            @foreach($tabs as $tab)
                <button 
                    @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}' 
                        ? 'border-primary-500 text-primary-600' 
                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="group inline-flex items-center whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-all duration-150"
                    role="tab"
                    :aria-selected="activeTab === '{{ $tab['id'] }}'"
                    :tabindex="activeTab === '{{ $tab['id'] }}' ? 0 : -1"
                    @keydown.arrow-right.prevent="
                        const tabs = {{ json_encode(collect($tabs)->pluck('id')->toArray()) }};
                        const index = tabs.indexOf(activeTab);
                        activeTab = tabs[(index + 1) % tabs.length];
                    "
                    @keydown.arrow-left.prevent="
                        const tabs = {{ json_encode(collect($tabs)->pluck('id')->toArray()) }};
                        const index = tabs.indexOf(activeTab);
                        activeTab = tabs[(index - 1 + tabs.length) % tabs.length];
                    ">
                    @if(isset($tab['icon']))
                        <i class="fas fa-{{ $tab['icon'] }} mr-2 group-hover:scale-110 transition-transform"></i>
                    @endif
                    <span>{{ $tab['label'] }}</span>
                    @if(isset($tab['count']) && $tab['count'] > 0)
                        <span :class="activeTab === '{{ $tab['id'] }}' ? 'bg-primary-100 text-primary-600' : 'bg-gray-100 text-gray-600'"
                              class="ml-2 py-0.5 px-2 rounded-full text-xs font-medium transition-colors">
                            {{ $tab['count'] }}
                        </span>
                    @endif
                    @if(isset($tab['badge']))
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $tab['badge']['color'] }}-100 text-{{ $tab['badge']['color'] }}-800">
                            {{ $tab['badge']['text'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="tab-content">
        {{ $slot }}
    </div>
</div>

<style>
    /* Ensure smooth transitions */
    [x-cloak] { display: none !important; }
</style>
