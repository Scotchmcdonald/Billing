<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Add Hours to Retainer') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Add Hours to Retainer</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Top up an existing retainer with additional hours
            </p>
        </div>

        <!-- State Indicator -->
        <x-billing::state-indicator state="idle" />

        <!-- Retainer Details -->
        @if(isset($retainer))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Current Retainer</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Company</div>
                    <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $retainer->company->name }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Hours Remaining</div>
                    <div class="text-gray-900 dark:text-gray-100 font-medium">{{ $retainer->hours_remaining }}</div>
                </div>
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Status</div>
                    <div>
                        <x-billing::status-badge :status="$retainer->status" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Hours Form -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <form method="POST" action="{{ route('billing.finance.retainers.add-hours', $retainer) }}">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Hours to Add *
                        </label>
                        <input 
                            type="number" 
                            name="hours" 
                            id="hours" 
                            step="0.5"
                            min="0.5"
                            required
                            class="mt-1 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 block w-full py-3 shadow-sm text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg transition-colors duration-150"
                            placeholder="10.0"
                            value="{{ old('hours') }}">
                        @error('hours')
                            <p class="mt-1 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Price *
                        </label>
                        <div class="mt-1 relative rounded-lg shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400">$</span>
                            </div>
                            <input 
                                type="number" 
                                name="price" 
                                id="price" 
                                step="0.01"
                                min="0"
                                required
                                class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 pr-12 sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md"
                                placeholder="0.00"
                                value="{{ old('price') }}">
                        </div>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Notes
                        </label>
                        <textarea 
                            name="notes" 
                            id="notes" 
                            rows="3"
                            class="mt-1 shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md"
                            placeholder="Optional notes...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('billing.finance.retainers.show', $retainer) }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Add Hours
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-4">
            <p class="text-yellow-800 dark:text-yellow-200">No retainer specified.</p>
        </div>
        @endif
    </div>
</x-app-layout>
