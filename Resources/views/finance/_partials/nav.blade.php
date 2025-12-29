<div class="border-b border-gray-200 mb-6">
    <nav class="-mb-px flex space-x-8 overflow-x-auto" aria-label="Tabs">
        
        <!-- Dashboard -->
        <a href="{{ route('billing.finance.dashboard') }}" 
           class="{{ Route::is('billing.finance.dashboard') 
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.dashboard') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span>Dashboard</span>
        </a>

        <!-- Pre-Flight -->
        <a href="{{ route('billing.finance.pre-flight') }}" 
           class="{{ Route::is('billing.finance.pre-flight') || Route::is('billing.finance.pre-flight-enhanced') 
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.pre-flight') || Route::is('billing.finance.pre-flight-enhanced') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
            <span>Pre-Flight</span>
            @if(($navCounts['pre_flight'] ?? 0) > 0)
                <span class="ml-2 bg-red-100 text-red-600 py-0.5 px-2 rounded-full text-xs font-bold border border-red-200">
                    {{ $navCounts['pre_flight'] }}
                </span>
            @endif
        </a>

        <!-- Usage Review -->
        <a href="{{ route('billing.finance.usage-review') }}" 
           class="{{ Route::is('billing.finance.usage-review') 
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.usage-review') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
            </svg>
            <span>Usage Review</span>
            @if(($navCounts['usage_review'] ?? 0) > 0)
                <span class="ml-2 bg-amber-100 text-amber-700 py-0.5 px-2 rounded-full text-xs font-bold border border-amber-200">
                    {{ $navCounts['usage_review'] }}
                </span>
            @endif
        </a>

        <!-- Quotes -->
        <a href="{{ route('billing.finance.quotes.index') }}" 
           class="{{ Route::is('billing.finance.quotes.*') || (request()->routeIs('billing.finance.reports-hub') && request()->query('tab') === 'quotes')
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.quotes.*') || (request()->routeIs('billing.finance.reports-hub') && request()->query('tab') === 'quotes') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Quotes</span>
        </a>

        <!-- Invoices -->
        <a href="{{ route('billing.finance.invoices') }}" 
           class="{{ Route::is('billing.finance.invoices*') 
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.invoices*') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span>Invoices</span>
            @if(($navCounts['invoices'] ?? 0) > 0)
                <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs font-bold border border-gray-200">
                    {{ $navCounts['invoices'] }}
                </span>
            @endif
        </a>

        <!-- Payments -->
        <a href="{{ route('billing.finance.payments') }}" 
           class="{{ Route::is('billing.finance.payments') || Route::is('billing.finance.collections') 
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.payments') || Route::is('billing.finance.collections') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Payments</span>
            @if(($navCounts['payments'] ?? 0) > 0)
                <span class="ml-2 bg-red-100 text-red-600 py-0.5 px-2 rounded-full text-xs font-bold border border-red-200">
                    {{ $navCounts['payments'] }}
                </span>
            @endif
        </a>

        <!-- Reports -->
        <a href="{{ route('billing.finance.reports-hub') }}" 
           class="{{ Route::is('billing.finance.reports-hub') || Route::is('billing.finance.revenue-recognition') || Route::is('billing.finance.credit-notes.*') || Route::is('billing.finance.disputes.*') || Route::is('billing.finance.contracts.*') || Route::is('billing.finance.retainers.*') || Route::is('billing.finance.overrides')
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.reports-hub') || Route::is('billing.finance.revenue-recognition') || Route::is('billing.finance.quotes.*') || Route::is('billing.finance.credit-notes.*') || Route::is('billing.finance.disputes.*') || Route::is('billing.finance.contracts.*') || Route::is('billing.finance.retainers.*') || Route::is('billing.finance.overrides') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
            </svg>
            <span>Reports</span>
        </a>

        <!-- Portal Access -->
        <a href="{{ route('billing.finance.portal-access') }}" 
           class="{{ Route::is('billing.finance.portal-access') 
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.portal-access') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Portal Access</span>
        </a>

        <!-- Settings -->
        <a href="{{ route('billing.finance.settings-hub') }}" 
           class="{{ Route::is('billing.finance.settings-hub') 
                ? 'border-primary-500 text-primary-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} 
                group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200 whitespace-nowrap">
            <svg class="{{ Route::is('billing.finance.settings-hub') ? 'text-primary-500' : 'text-gray-400 group-hover:text-gray-500' }} -ml-0.5 mr-2 h-5 w-5 transition-colors duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span>Settings</span>
        </a>
    </nav>
</div>
