<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Portal Access') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('billing::finance._partials.nav')
            
            <div class="mt-6">
                @include('billing::finance._partials.portal-access-content')
            </div>
        </div>
    </div>
</x-app-layout>
