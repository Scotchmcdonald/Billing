@extends('layouts.app')

@section('content')
<div x-data="executiveDashboard()" x-init="init()" class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-8">
            <div class="min-w-0 flex-1">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                    Executive Dashboard
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Strategic financial overview • Real-time KPIs • Last updated: <span x-text="lastUpdated"></span>
                </p>
            </div>
            <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
                <button @click="configureAlerts()" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    Configure Alerts
                </button>
                
                <button @click="exportDashboard()" class="inline-flex items-center rounded-md bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export PDF
                </button>
            </div>
        </div>

        <!-- Key Metrics Grid - 5 Primary KPIs -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5 mb-8">
            <!-- MRR -->
            <x-billing::kpi-card 
                title="Monthly Recurring Revenue"
                :value="$metrics['mrr']"
                format="currency"
                status="success"
                :trend="$trends['mrr'] ?? null"
            >
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </x-slot:icon>
                
                @if(isset($sparklines['mrr']))
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <x-billing::sparkline :data="$sparklines['mrr']" color="success" />
                    </div>
                @endif
            </x-billing::kpi-card>

            <!-- Churn Rate -->
            <x-billing::kpi-card 
                title="Churn Rate"
                :value="$metrics['churn_rate']"
                format="percentage"
                :status="$metrics['churn_rate'] > 5 ? 'danger' : 'success'"
                :trend="$trends['churn'] ?? null"
            >
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </x-slot:icon>
                
                @if(isset($sparklines['churn']))
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <x-billing::sparkline :data="$sparklines['churn']" :color="$metrics['churn_rate'] > 5 ? 'danger' : 'success'" />
                    </div>
                @endif
            </x-billing::kpi-card>

            <!-- Gross Margin -->
            <x-billing::kpi-card 
                title="Gross Margin"
                :value="$metrics['gross_margin']"
                format="percentage"
                :status="$metrics['gross_margin'] >= 50 ? 'success' : 'warning'"
                :trend="$trends['margin'] ?? null"
            >
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </x-slot:icon>
            </x-billing::kpi-card>

            <!-- Customer LTV -->
            <x-billing::kpi-card 
                title="Avg Customer LTV"
                :value="$metrics['ltv']"
                format="currency"
                status="primary"
                :trend="$trends['ltv'] ?? null"
            >
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </x-slot:icon>
            </x-billing::kpi-card>

            <!-- AR Aging -->
            <x-billing::kpi-card 
                title="Outstanding AR"
                :value="$metrics['total_ar']"
                format="currency"
                :status="$metrics['ar_overdue_percent'] > 20 ? 'danger' : 'warning'"
                :trend="$trends['ar'] ?? null"
            >
                <x-slot:icon>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </x-slot:icon>
                
                <div class="mt-3 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-600">
                        {{ $metrics['ar_overdue_count'] }} overdue • {{ number_format($metrics['ar_overdue_percent'], 1) }}% past due
                    </p>
                </div>
            </x-billing::kpi-card>
        </div>

        <!-- Secondary Metrics Row -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Effective Hourly Rate -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Effective Hourly Rate</h3>
                    @if(isset($benchmarks['ehr']))
                        <span class="px-2 py-1 text-xs font-medium rounded-full 
                            {{ $metrics['ehr'] >= $benchmarks['ehr']['target'] ? 'bg-success-100 text-success-800' : 'bg-warning-100 text-warning-800' }}">
                            {{ $metrics['ehr'] >= $benchmarks['ehr']['target'] ? 'On Target' : 'Below Target' }}
                        </span>
                    @endif
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-gray-900">${{ number_format($metrics['ehr'], 0) }}</p>
                        @if(isset($benchmarks['ehr']))
                            <p class="mt-1 text-sm text-gray-600">Target: ${{ number_format($benchmarks['ehr']['target'], 0) }}</p>
                        @endif
                    </div>
                    @if(isset($sparklines['ehr']))
                        <x-billing::sparkline :data="$sparklines['ehr']" width="80" height="40" />
                    @endif
                </div>
            </div>

            <!-- Active Clients -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Active Clients</h3>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-gray-900">{{ $metrics['active_clients'] }}</p>
                        @if($metrics['at_risk_clients'] > 0)
                            <p class="mt-1 text-sm text-danger-600">{{ $metrics['at_risk_clients'] }} at risk</p>
                        @else
                            <p class="mt-1 text-sm text-success-600">All healthy</p>
                        @endif
                    </div>
                    <x-billing::trend-indicator 
                        :current="$metrics['active_clients']"
                        :previous="$metrics['active_clients_previous']"
                        label="vs last month"
                    />
                </div>
            </div>

            <!-- Revenue per Employee -->
            @if(isset($benchmarks['revenue_per_employee']))
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Revenue / Employee</h3>
                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                        Industry: ${{ number_format($benchmarks['revenue_per_employee']['industry_avg'] / 1000, 0) }}K
                    </span>
                </div>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-gray-900">${{ number_format($metrics['revenue_per_employee'] / 1000, 0) }}K</p>
                        <p class="mt-1 text-sm text-gray-600">{{ $metrics['employee_count'] }} employees</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Historical Comparison Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- MoM Comparison -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Month-over-Month Growth</h3>
                <div class="space-y-4">
                    @foreach($metrics['mom_comparison'] as $metric => $data)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $data['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $data['current_label'] }} vs {{ $data['previous_label'] }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm font-semibold text-gray-900">
                                    @if($data['format'] === 'currency')
                                        ${{ number_format($data['current'] / 100, 0) }}
                                    @elseif($data['format'] === 'percentage')
                                        {{ number_format($data['current'], 1) }}%
                                    @else
                                        {{ number_format($data['current']) }}
                                    @endif
                                </span>
                                <x-billing::trend-indicator 
                                    :current="$data['current']"
                                    :previous="$data['previous']"
                                    label=""
                                    :inverse="$data['inverse'] ?? false"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- YoY Comparison -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Year-over-Year Growth</h3>
                <div class="space-y-4">
                    @foreach($metrics['yoy_comparison'] as $metric => $data)
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $data['label'] }}</p>
                                <p class="text-xs text-gray-500">{{ $data['current_label'] }} vs {{ $data['previous_label'] }}</p>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="text-sm font-semibold text-gray-900">
                                    @if($data['format'] === 'currency')
                                        ${{ number_format($data['current'] / 100, 0) }}
                                    @elseif($data['format'] === 'percentage')
                                        {{ number_format($data['current'], 1) }}%
                                    @else
                                        {{ number_format($data['current']) }}
                                    @endif
                                </span>
                                <x-billing::trend-indicator 
                                    :current="$data['current']"
                                    :previous="$data['previous']"
                                    label=""
                                    :inverse="$data['inverse'] ?? false"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Active Alerts -->
        @if(!empty($alerts))
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Alerts</h3>
                <div class="space-y-3">
                    @foreach($alerts as $alert)
                        <div class="rounded-lg border-l-4 p-4
                            @if($alert['severity'] === 'critical') border-danger-500 bg-danger-50
                            @elseif($alert['severity'] === 'warning') border-warning-500 bg-warning-50
                            @else border-primary-500 bg-primary-50
                            @endif
                        ">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5
                                        @if($alert['severity'] === 'critical') text-danger-600
                                        @elseif($alert['severity'] === 'warning') text-warning-600
                                        @else text-primary-600
                                        @endif
                                    " fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3 flex-1">
                                    <p class="text-sm font-medium
                                        @if($alert['severity'] === 'critical') text-danger-900
                                        @elseif($alert['severity'] === 'warning') text-warning-900
                                        @else text-primary-900
                                        @endif
                                    ">{{ $alert['title'] }}</p>
                                    <p class="mt-1 text-sm
                                        @if($alert['severity'] === 'critical') text-danger-800
                                        @elseif($alert['severity'] === 'warning') text-warning-800
                                        @else text-primary-800
                                        @endif
                                    ">{{ $alert['message'] }}</p>
                                    @if(isset($alert['action_url']))
                                        <a href="{{ $alert['action_url'] }}" class="mt-2 inline-block text-sm font-medium
                                            @if($alert['severity'] === 'critical') text-danger-900 hover:text-danger-800
                                            @elseif($alert['severity'] === 'warning') text-warning-900 hover:text-warning-800
                                            @else text-primary-900 hover:text-primary-800
                                            @endif
                                        ">
                                            {{ $alert['action_label'] ?? 'View Details' }} →
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- At-Risk Clients -->
        @if(isset($at_risk_clients) && count($at_risk_clients) > 0)
            <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Clients Requiring Attention</h3>
                <div class="space-y-3">
                    @foreach($at_risk_clients as $client)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $client['name'] }}</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    @foreach($client['risk_factors'] as $factor)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-danger-100 text-danger-800 mr-1">
                                            {{ $factor }}
                                        </span>
                                    @endforeach
                                </p>
                            </div>
                            <div class="ml-4 flex items-center space-x-2">
                                <span class="text-sm font-semibold text-gray-900">${{ number_format($client['monthly_value'] / 100, 0) }}/mo</span>
                                <a href="{{ route('billing.finance.companies.show', $client['id']) }}" class="text-primary-600 hover:text-primary-900">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function executiveDashboard() {
    return {
        lastUpdated: new Date().toLocaleTimeString(),
        refreshInterval: null,
        
        init() {
            // Set up auto-refresh every 30 seconds
            this.refreshInterval = setInterval(() => {
                this.refreshMetrics();
            }, 30000);
        },
        
        async refreshMetrics() {
            try {
                const response = await fetch('{{ route("billing.finance.executive.refresh") }}');
                const data = await response.json();
                
                // Update metrics (implementation would update Alpine data)
                this.lastUpdated = new Date().toLocaleTimeString();
                
                // Show subtle update notification
                this.$dispatch('metrics-updated');
            } catch (error) {
                console.error('Failed to refresh metrics:', error);
            }
        },
        
        configureAlerts() {
            // Open alert configuration modal
            window.location.href = '{{ route("billing.finance.executive.alerts.configure") }}';
        },
        
        exportDashboard() {
            window.location.href = '{{ route("billing.finance.executive.export") }}';
        }
    }
}
</script>
@endpush
@endsection
