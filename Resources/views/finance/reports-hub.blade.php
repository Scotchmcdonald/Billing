<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Financial Reports & Analytics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('billing::finance._partials.nav')
            
            <!-- Actions -->
            <div class="mb-6 flex justify-end gap-3">
                <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                    <i class="fas fa-print mr-2"></i>
                    Print
                </button>
                <a href="{{ route('billing.finance.reports-hub.export', request()->all()) }}" class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 focus:bg-primary-700 active:bg-primary-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 transition">
                    <i class="fas fa-download mr-2"></i>
                    Export
                </a>
            </div>

            <!-- Tabbed Interface -->
    <x-billing::tabs :active="request()->query('tab', 'executive')" :tabs="[
        ['id' => 'executive', 'label' => 'Executive Dashboard', 'icon' => 'chart-line'],
        ['id' => 'reports', 'label' => 'Detailed Reports', 'icon' => 'file-alt'],
        ['id' => 'ar-aging', 'label' => 'AR Aging', 'icon' => 'clock', 'count' => $overdueCount ?? 0],
        ['id' => 'profitability', 'label' => 'Profitability', 'icon' => 'dollar-sign'],
        ['id' => 'credit-notes', 'label' => 'Credit Notes', 'icon' => 'receipt'],
        ['id' => 'revenue-recognition', 'label' => 'Rev Rec', 'icon' => 'chart-pie'],
        ['id' => 'contracts', 'label' => 'Contracts', 'icon' => 'file-contract'],
        ['id' => 'retainers', 'label' => 'Retainers', 'icon' => 'hourglass-half'],
        ['id' => 'disputes', 'label' => 'Disputes', 'icon' => 'exclamation-circle'],
        ['id' => 'overrides', 'label' => 'Overrides', 'icon' => 'tag'],
    ]">
        <!-- Executive Dashboard Tab -->
        <x-billing::tab-panel id="executive">
            @include('billing::finance._partials.executive-dashboard-content')
        </x-billing::tab-panel>

        <!-- Detailed Reports Tab -->
        <x-billing::tab-panel id="reports" lazy="true">
            @include('billing::finance._partials.reports-content')
        </x-billing::tab-panel>

        <!-- AR Aging Tab -->
        <x-billing::tab-panel id="ar-aging" lazy="true">
            @include('billing::finance._partials.ar-aging-content')
        </x-billing::tab-panel>

        <!-- Profitability Tab -->
        <x-billing::tab-panel id="profitability" lazy="true">
            @include('billing::finance._partials.profitability-content')
        </x-billing::tab-panel>

        <!-- Credit Notes Tab -->
        <x-billing::tab-panel id="credit-notes" lazy="true">
            @include('billing::finance._partials.credit-notes-content')
        </x-billing::tab-panel>

        <!-- Revenue Recognition Tab -->
        <x-billing::tab-panel id="revenue-recognition" lazy="true">
            @include('billing::finance._partials.revenue-recognition-content')
        </x-billing::tab-panel>

        <!-- Contracts Tab -->
        <x-billing::tab-panel id="contracts" lazy="true">
            @include('billing::finance._partials.contracts-content')
        </x-billing::tab-panel>

        <!-- Retainers Tab -->
        <x-billing::tab-panel id="retainers" lazy="true">
            @include('billing::finance._partials.retainers-content')
        </x-billing::tab-panel>

        <!-- Disputes Tab -->
        <x-billing::tab-panel id="disputes" lazy="true">
            @include('billing::finance._partials.disputes-content')
        </x-billing::tab-panel>

        <!-- Overrides Tab -->
        <x-billing::tab-panel id="overrides" lazy="true">
            @include('billing::finance._partials.overrides-content')
        </x-billing::tab-panel>
    </x-billing::tabs>
        </div>
    </div>
</x-app-layout>

<style>
    @media print {
        .no-print, nav, button { display: none !important; }
        .tab-content { display: block !important; }
    }
</style>

