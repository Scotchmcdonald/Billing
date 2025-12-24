@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Team Access - {{ $company->name }}</h1>
        <a href="{{ route('billing.portal.dashboard', $company) }}" class="text-sm text-gray-600 hover:text-gray-900">&larr; Back to Dashboard</a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Authorized Users</h3>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage who can view and pay invoices for this company.</p>
        </div>
        <ul class="divide-y divide-gray-200">
            @foreach($users as $user)
            <li class="px-4 py-4 sm:px-6 flex items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                        {{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ $user->first_name }} {{ $user->last_name }}</div>
                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="flex items-center">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->pivot->role === 'billing.admin' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800' }}">
                        {{ $user->pivot->role }}
                    </span>
                    @if($user->id !== Auth::id())
                        <!-- TODO: Add Remove/Edit Role buttons -->
                        <button class="ml-4 text-sm text-red-600 hover:text-red-900">Remove</button>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>

    <!-- Add User Section (Mockup) -->
    <div class="bg-gray-50 p-6 rounded-lg border border-gray-200">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Add Team Member</h3>
        <form action="#" method="POST" class="flex gap-4">
            @csrf
            <div class="flex-grow">
                <label for="email" class="sr-only">Email Address</label>
                <input type="email" name="email" id="email" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="colleague@example.com">
            </div>
            <div class="w-48">
                <label for="role" class="sr-only">Role</label>
                <select name="role" id="role" class="shadow-sm focus:ring-primary-500 focus:border-primary-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    <option value="billing.payer">Payer (View & Pay)</option>
                    <option value="billing.admin">Admin (Manage Team)</option>
                </select>
            </div>
            <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Invite
            </button>
        </form>
    </div>
</div>
@endsection
