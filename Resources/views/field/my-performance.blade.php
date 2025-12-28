@extends('layouts.app')

@section('content')
<div x-data="technicianDashboard()" x-init="init()" class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold leading-7 text-gray-900">
                My Performance
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Track your productivity and billable hours
            </p>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Utilization Rate -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Utilization Rate</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                          :class="metrics.utilization >= metrics.target ? 'bg-success-100 text-success-800' : 'bg-warning-100 text-warning-800'">
                        <span x-text="metrics.utilization >= metrics.target ? 'On Target' : 'Below Target'"></span>
                    </span>
                </div>
                
                <!-- Circular Progress -->
                <div class="relative w-32 h-32 mx-auto mb-4">
                    <svg class="w-full h-full transform -rotate-90">
                        <circle cx="64" cy="64" r="56" stroke="#e5e7eb" stroke-width="8" fill="none" />
                        <circle cx="64" cy="64" r="56" 
                                :stroke="metrics.utilization >= metrics.target ? '#10b981' : '#f59e0b'"
                                stroke-width="8" 
                                fill="none" 
                                stroke-linecap="round"
                                :stroke-dasharray="351.86"
                                :stroke-dashoffset="351.86 * (1 - metrics.utilization / 100)" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-3xl font-bold text-gray-900" x-text="metrics.utilization + '%'"></span>
                    </div>
                </div>
                
                <p class="text-center text-sm text-gray-600">
                    Target: <span x-text="metrics.target + '%'"></span>
                </p>
                
                @if(isset($streak) && $streak > 0)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <p class="text-center text-xs text-gray-600">
                            🔥 <span class="font-semibold">{{ $streak }} day streak</span> above target!
                        </p>
                    </div>
                @endif
            </div>

            <!-- Billable Hours (This Week) -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">Billable Hours</h3>
                <div class="flex items-end justify-between">
                    <div>
                        <p class="text-3xl font-bold text-gray-900" x-text="metrics.billable_hours"></p>
                        <p class="text-sm text-gray-600">This week</p>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-semibold text-gray-700" x-text="metrics.non_billable_hours"></p>
                        <p class="text-xs text-gray-500">Non-billable</p>
                    </div>
                </div>
                
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs text-gray-600 mb-1">
                        <span>Progress</span>
                        <span x-text="metrics.billable_hours + '/' + metrics.weekly_target + ' hrs'"></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" 
                             :style="'width: ' + Math.min((metrics.billable_hours / metrics.weekly_target) * 100, 100) + '%'"></div>
                    </div>
                </div>
            </div>

            <!-- Avg Ticket Resolution -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">Avg Resolution Time</h3>
                <p class="text-3xl font-bold text-gray-900" x-text="formatTime(metrics.avg_resolution_time)"></p>
                <p class="text-sm text-gray-600">Per ticket</p>
                
                <div class="mt-4 flex items-center text-sm">
                    <svg class="h-5 w-5" :class="metrics.resolution_trend > 0 ? 'text-danger-600' : 'text-success-600'" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" :d="metrics.resolution_trend > 0 ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z'" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1" :class="metrics.resolution_trend > 0 ? 'text-danger-700' : 'text-success-700'" x-text="Math.abs(metrics.resolution_trend) + '%'"></span>
                    <span class="ml-2 text-gray-600">vs last week</span>
                </div>
            </div>

            <!-- First-Time Fix Rate -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-4">First-Time Fix Rate</h3>
                <p class="text-3xl font-bold text-gray-900" x-text="metrics.first_time_fix_rate + '%'"></p>
                <p class="text-sm text-gray-600">Last 30 days</p>
                
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-600">Industry Avg</span>
                        <span class="font-semibold text-gray-700">75%</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="bg-gray-400 h-2 rounded-full" style="width: 75%"></div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-xs mt-1">
                        <span class="text-gray-600">You</span>
                        <span class="font-semibold" :class="metrics.first_time_fix_rate >= 75 ? 'text-success-700' : 'text-gray-700'" x-text="metrics.first_time_fix_rate + '%'"></span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="flex-1 bg-gray-200 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all" 
                                 :class="metrics.first_time_fix_rate >= 75 ? 'bg-success-600' : 'bg-primary-600'"
                                 :style="'width: ' + metrics.first_time_fix_rate + '%'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Weekly Hours Chart -->
        <div class="bg-white shadow-sm rounded-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">This Week's Activity</h3>
            <div class="flex items-end justify-between space-x-2" style="height: 200px;">
                <template x-for="(day, index) in weeklyData" :key="index">
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full flex flex-col justify-end" style="height: 180px;">
                            <!-- Billable Hours -->
                            <div class="w-full bg-primary-600 rounded-t" 
                                 :style="'height: ' + (day.billable / 10 * 180) + 'px'"
                                 :title="'Billable: ' + day.billable + 'hrs'"></div>
                            <!-- Non-Billable Hours -->
                            <div class="w-full bg-gray-300" 
                                 :style="'height: ' + (day.non_billable / 10 * 180) + 'px'"
                                 :title="'Non-billable: ' + day.non_billable + 'hrs'"></div>
                        </div>
                        <span class="mt-2 text-xs text-gray-600" x-text="day.label"></span>
                    </div>
                </template>
            </div>
            <div class="mt-4 flex items-center justify-center space-x-6 text-sm">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-primary-600 rounded mr-2"></div>
                    <span class="text-gray-600">Billable</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-300 rounded mr-2"></div>
                    <span class="text-gray-600">Non-Billable</span>
                </div>
            </div>
        </div>

        <!-- Recent Tickets -->
        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Recent Tickets</h3>
                <a href="{{ route('billing.field.timesheet') }}" class="text-sm text-primary-600 hover:text-primary-900">
                    View Timesheet →
                </a>
            </div>
            
            <div class="space-y-3">
                @foreach($recent_tickets as $ticket)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <p class="text-sm font-medium text-gray-900">{{ $ticket['client_name'] }}</p>
                                <x-billing::ar-status-badge 
                                    :status="$ticket['ar_status']" 
                                    :daysOverdue="$ticket['days_overdue']"
                                    :amount="$ticket['ar_amount']"
                                />
                            </div>
                            <p class="text-xs text-gray-600 mt-1">{{ $ticket['description'] }}</p>
                            <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                <span>{{ $ticket['hours'] }} hours logged</span>
                                <span>•</span>
                                <span>{{ $ticket['date'] }}</span>
                            </div>
                        </div>
                        <div class="ml-4">
                            @if($ticket['billable'])
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">
                                    Billable
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Non-Billable
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function technicianDashboard() {
    return {
        metrics: @json($metrics),
        weeklyData: @json($weekly_data),
        
        init() {
            // Initialize
        },
        
        formatTime(minutes) {
            if (minutes < 60) {
                return minutes + 'm';
            }
            const hours = Math.floor(minutes / 60);
            const mins = minutes % 60;
            return mins > 0 ? hours + 'h ' + mins + 'm' : hours + 'h';
        }
    }
}
</script>
@endpush
@endsection
