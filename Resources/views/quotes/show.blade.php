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
                        <p class="text-sm text-gray-600 mt-2">
                            <span class="font-semibold">Pricing Tier:</span> 
                            <span class="px-2 py-1 bg-gray-100 rounded text-xs">
                                {{ ucfirst(str_replace('_', ' ', $quote->pricing_tier ?? 'standard')) }}
                            </span>
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $quote->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                            {{ $quote->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $quote->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $quote->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($quote->status) }}
                        </span>
                        <p class="text-sm text-gray-500 mt-1">Valid until: {{ $quote->valid_until ? $quote->valid_until->format('M d, Y') : 'N/A' }}</p>
                        
                        @if($quote->requires_approval)
                            <div class="mt-2 px-3 py-1 bg-amber-100 border border-amber-300 rounded text-xs text-amber-800">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Requires Approval
                            </div>
                        @endif
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 mb-6">
                    <thead>
                        <tr>
                            <th class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance</th>
                            <th class="text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($quote->lineItems as $item)
                            <tr>
                                <td class="py-2">{{ $item->description }}</td>
                                <td class="py-2 text-right">{{ $item->quantity }}</td>
                                <td class="py-2 text-right">
                                    ${{ number_format($item->unit_price, 2) }}
                                    @if($item->standard_price && $item->standard_price != $item->unit_price)
                                        <span class="text-xs text-gray-400 block">
                                            (Std: ${{ number_format($item->standard_price, 2) }})
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">
                                    @if($item->variance_percent != 0)
                                        @php
                                            $absVariance = abs($item->variance_percent);
                                            $colorClass = $absVariance > ($quote->approval_threshold_percent ?? 15) 
                                                ? 'text-red-600 font-semibold' 
                                                : ($item->variance_percent > 0 ? 'text-orange-600' : 'text-green-600');
                                        @endphp
                                        <span class="{{ $colorClass }}">
                                            {{ $item->variance_percent > 0 ? '+' : '' }}{{ number_format($item->variance_percent, 1) }}%
                                        </span>
                                        <span class="text-xs text-gray-400 block">
                                            ({{ $item->variance_amount > 0 ? '+' : '' }}${{ number_format($item->variance_amount, 2) }})
                                        </span>
                                    @else
                                        <span class="text-gray-400">--</span>
                                    @endif
                                </td>
                                <td class="py-2 text-right font-bold">${{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right font-bold pt-4">Total</td>
                            <td class="text-right font-bold pt-4 text-xl">${{ number_format($quote->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>

                @if($quote->notes)
                    <div class="mb-6 p-4 bg-gray-50 rounded">
                        <h4 class="text-sm font-semibold text-gray-700 mb-2">Notes</h4>
                        <p class="text-sm text-gray-600">{{ $quote->notes }}</p>
                    </div>
                @endif

                <div class="flex justify-between items-center">
                    <a href="{{ route('billing.finance.reports-hub', ['tab' => 'quotes']) }}" 
                       class="text-gray-600 hover:text-gray-900">
                        ← Back to Quotes
                    </a>
                    <div class="flex space-x-4">
                        @if($quote->token)
                            <a href="{{ route('billing.public.quote.show', $quote->token) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                Public Link
                            </a>
                        @endif
                        
                        {{-- Edit functionality coming soon --}}
                        {{-- @if($quote->status === 'draft' || $quote->status === 'sent')
                            <a href="{{ route('billing.finance.quotes.edit', $quote->id) }}" class="text-gray-600 hover:text-gray-900 border border-gray-300 px-3 py-2 rounded-md">
                                Edit
                            </a>
                        @endif --}}

                        {{-- Send functionality - Coming soon
                        @if($quote->status === 'draft' && !$quote->requires_approval)
                            <a href="#" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                                Send to Client
                            </a>
                        @elseif($quote->status === 'draft' && $quote->requires_approval)
                            <button class="bg-amber-600 text-white px-4 py-2 rounded-md cursor-not-allowed" disabled>
                                Pending Approval
                            </button>
                        @endif
                        --}}
                        
                        @if($quote->is_accepted && $quote->company_id)
                            <form method="POST" action="{{ route('billing.finance.quotes.convert', $quote->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">
                                    Convert to Invoice
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
