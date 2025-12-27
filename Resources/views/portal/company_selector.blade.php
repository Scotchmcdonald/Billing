<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Select Company') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Please select a company to view:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($companies as $company)
                        <a href="{{ route('billing.portal.dashboard', $company) }}" class="block p-6 border border-gray-200 rounded-lg hover:bg-gray-50 transition duration-150 ease-in-out">
                            <h4 class="text-xl font-semibold text-gray-800">{{ $company->name }}</h4>
                            <p class="text-gray-600 mt-2">ID: {{ $company->id }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
