@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Billing Portal - {{ $company->name }}</h1>
        @if(Auth::user()->can('finance.admin'))
            <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">Admin View</span>
        @endif
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Outstanding Balance Card -->
        <div class="bg-white p-6 rounded-lg shadow border-l-4 border-primary-500">
            <h2 class="text-gray-500 text-sm font-medium uppercase">Outstanding Balance</h2>
            <p class="text-3xl font-bold mt-2 text-gray-900">{{ $company->balance() }}</p>
            <p class="text-xs text-gray-400 mt-1">Due immediately</p>
        </div>

        <!-- Payment Method Card -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-gray-500 text-sm font-medium uppercase">Default Payment Method</h2>
            <div class="mt-2">
                @if($company->pm_type)
                    <div class="flex items-center">
                        @if($company->pm_type === 'us_bank_account')
                            <span class="text-emerald-600 font-semibold flex items-center">
                                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"></path></svg>
                                ACH Direct Debit
                            </span>
                        @else
                            <span class="text-gray-700 font-semibold">{{ ucfirst($company->pm_type) }}</span>
                        @endif
                        <span class="ml-2 text-gray-600">•••• {{ $company->pm_last_four }}</span>
                    </div>
                    @if($company->pm_type !== 'us_bank_account')
                        <p class="text-xs text-amber-600 mt-1">Switch to ACH to save fees.</p>
                    @endif
                @else
                    <p class="text-gray-500 italic">No payment method on file</p>
                @endif
            </div>
            <a href="{{ route('billing.portal.payment_methods', $company) }}" class="text-primary-600 hover:text-primary-800 text-sm mt-3 inline-block font-medium">Manage Payment Methods &rarr;</a>
        </div>

        <!-- Team Access Card -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-gray-500 text-sm font-medium uppercase">Team Access</h2>
            <p class="text-3xl font-bold mt-2 text-gray-900">{{ $company->users()->count() }}</p>
            <p class="text-xs text-gray-400 mt-1">Authorized users</p>
            <a href="{{ route('billing.portal.team', $company) }}" class="text-primary-600 hover:text-primary-800 text-sm mt-3 inline-block font-medium">Manage Team &rarr;</a>
        </div>
    </div>

    <h2 class="text-xl font-bold mb-4 text-gray-900">Recent Invoices</h2>
    <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
        <ul class="divide-y divide-gray-200">
            @forelse($invoices as $invoice)
            <li class="hover:bg-gray-50 transition duration-150 ease-in-out">
                <div class="px-4 py-4 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <p class="text-sm font-medium text-primary-600 truncate">
                                {{ $invoice->number }}
                            </p>
                            <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $invoice->paid ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $invoice->status }}
                            </span>
                        </div>
                        <div class="ml-2 flex-shrink-0 flex">
                            <p class="text-sm font-bold text-gray-900">
                                {{ $invoice->total() }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-2 sm:flex sm:justify-between">
                        <div class="sm:flex">
                            <p class="flex items-center text-sm text-gray-500">
                                <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                {{ $invoice->date()->toFormattedDateString() }}
                            </p>
                        </div>
                        <div class="mt-2 flex items-center text-sm text-gray-500 sm:mt-0">
                            <a href="{{ $invoice->hosted_invoice_url }}" target="_blank" class="flex items-center text-primary-600 hover:text-primary-900 font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download PDF
                            </a>
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <li class="px-4 py-8 text-center text-gray-500">
                No invoices found.
            </li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
