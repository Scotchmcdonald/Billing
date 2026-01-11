@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="bg-white rounded-lg shadow-xl p-8 border-l-4 border-purple-600">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">💳 Helcim Payment Simulator</h1>
        <p class="text-gray-600 mb-6">Control the outcome of the next payment transaction for testing purposes. Integration is currently set to use: <span class="font-mono bg-gray-100 px-2 py-1 rounded">{{ config('billing.helcim.test_mode') ? 'MockHelcimService' : 'RealHelcimService' }}</span></p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- APPROVED -->
            <div class="card cursor-pointer border-2 {{ $currentOutcome === 'APPROVED' ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:border-green-300' }} p-4 rounded-lg transition" onclick="setOutcome('APPROVED')">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold">Simulate Success</h3>
                </div>
                <p class="text-sm text-gray-500">Transaction returns APPROVED. Events `OrderPaid` and `StockReserved` should fire.</p>
            </div>

            <!-- DECLINED -->
            <div class="card cursor-pointer border-2 {{ $currentOutcome === 'DECLINED' ? 'border-red-500 bg-red-50' : 'border-gray-200 hover:border-red-300' }} p-4 rounded-lg transition" onclick="setOutcome('DECLINED')">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 text-red-600 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold">Simulate Declined</h3>
                </div>
                <p class="text-sm text-gray-500">Transaction returns DECLINED (Insufficient Funds). User stays on checkout.</p>
            </div>

            <!-- TIMEOUT -->
            <div class="card cursor-pointer border-2 {{ $currentOutcome === 'TIMEOUT' ? 'border-orange-500 bg-orange-50' : 'border-gray-200 hover:border-orange-300' }} p-4 rounded-lg transition" onclick="setOutcome('TIMEOUT')">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h3 class="text-lg font-semibold">Simulate Timeout</h3>
                </div>
                <p class="text-sm text-gray-500">Transaction hangs or returns Mock Error. System should handle graceful retry.</p>
            </div>
        </div>

        <div id="feedback" class="mt-6 hidden p-4 rounded bg-blue-100 text-blue-800"></div>
    </div>
</div>

<script>
function setOutcome(outcome) {
    fetch('/billing/mock/helcim/outcome', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ outcome: outcome })
    })
    .then(response => response.json())
    .then(data => {
        // Reload or update UI
        window.location.reload();
    });
}
</script>
@endsection
