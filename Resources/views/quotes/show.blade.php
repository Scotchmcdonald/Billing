<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Quote #') }}{{ $quote->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold">
                            {{ $quote->company ? $quote->company->name : $quote->prospect_name }}
                        </h3>
                        <p class="text-gray-500">{{ $quote->company ? $quote->company->email : $quote->prospect_email }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            {{ ucfirst($quote->status) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">Valid until: {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 mb-6">
                    <thead>
                        <tr>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($quote->lineItems as $item)
                            <tr>
                                <td class="py-2">{{ $item->description }}</td>
                                <td class="py-2 text-right">{{ $item->quantity }}</td>
                                <td class="py-2 text-right">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-2 text-right font-bold">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right font-bold pt-4">Total</td>
                            <td class="text-right font-bold pt-4 text-xl">${{ number_format($quote->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                <div class="flex justify-end space-x-4">
                    <a href="#" class="text-gray-600 hover:text-gray-900">Download PDF</a>
                    <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Send to Client</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
