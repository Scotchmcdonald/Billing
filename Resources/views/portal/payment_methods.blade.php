@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manage Payment Methods - {{ $company->name }}</h1>
        @if(auth()->user()->isAdmin() || auth()->user()->can('finance.admin'))
            <div class="flex items-center">
                <span class="text-sm text-gray-500 mr-2">Viewing as Customer</span>
                <a href="{{ route('billing.finance.portal-access') }}" class="text-sm text-indigo-600 hover:text-indigo-900 bg-indigo-50 px-3 py-1 rounded-md border border-indigo-200">
                    {{ __('Switch Company') }}
                </a>
            </div>
        @elseif(isset($hasMultipleCompanies) && $hasMultipleCompanies)
            <a href="{{ route('billing.portal.entry') }}" class="text-sm text-indigo-600 hover:text-indigo-900">
                {{ __('Switch Company') }}
            </a>
        @endif
    </div>

    @if($highValueTransaction)
    <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-amber-700">
                    Due to the high volume of your recent transactions, we kindly request that you use a Bank Account (ACH) for payments.
                </p>
            </div>
        </div>
    </div>
    @endif

    <div class="bg-white p-6 rounded-lg shadow">
        <x-billing::stripe-payment-element 
            :intent="$intent" 
            :return-url="route('billing.portal.dashboard', $company)"
            :company="$company"
        />
    </div>
</div>
@endsection
