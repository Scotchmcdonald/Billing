{{-- Technician Stats Dashboard - Mobile Optimized --}}
<div x-data="statsApp()" x-init="init()" class="min-h-screen bg-gray-50">
    
    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 px-4 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-lg font-semibold text-gray-900">My Stats</h1>
                <p class="text-xs text-gray-500" x-text="currentPeriod"></p>
            </div>
            <select x-model="period" @change="loadStats()" class="text-sm border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                <option value="today">Today</option>
                <option value="week">This Week</option>
                <option value="month" selected>This Month</option>
                <option value="quarter">This Quarter</option>
            </select>
        </div>
    </header>

    <main class="px-4 py-4 space-y-4">
        
        {{-- Key Metrics Grid --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-500 uppercase">Hours</span>
                    <svg class="h-4 w-4 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900" x-text="stats.total_hours"></div>
                <div class="mt-1 flex items-center text-xs">
                    <span :class="stats.hours_change >= 0 ? 'text-success-600' : 'text-danger-600'" x-text="(stats.hours_change >= 0 ? '+' : '') + stats.hours_change + '%'"></span>
                    <span class="ml-1 text-gray-500">vs last period</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-500 uppercase">Revenue</span>
                    <svg class="h-4 w-4 text-success-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900" x-text="'$' + stats.revenue.toLocaleString()"></div>
                <div class="mt-1 flex items-center text-xs">
                    <span :class="stats.revenue_change >= 0 ? 'text-success-600' : 'text-danger-600'" x-text="(stats.revenue_change >= 0 ? '+' : '') + stats.revenue_change + '%'"></span>
                    <span class="ml-1 text-gray-500">vs last period</span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-500 uppercase">Tickets</span>
                    <svg class="h-4 w-4 text-info-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900" x-text="stats.tickets_closed"></div>
                <div class="mt-1 flex items-center text-xs">
                    <span class="text-gray-500">Closed</span>
                    <span class="mx-1">•</span>
                    <span class="text-gray-500" x-text="stats.tickets_open + ' open'"></span>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-500 uppercase">Clients</span>
                    <svg class="h-4 w-4 text-warning-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                    </svg>
                </div>
                <div class="text-3xl font-bold text-gray-900" x-text="stats.unique_clients"></div>
                <div class="mt-1 text-xs text-gray-500">Served this period</div>
            </div>
        </div>

        {{-- Leaderboard Card --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-semibold text-gray-900">Team Ranking</h2>
                <span class="text-xs text-gray-500">This Month</span>
            </div>
            <div class="flex items-center justify-between bg-primary-50 rounded-lg p-3 mb-3">
                <div class="flex items-center space-x-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-600 text-white font-bold">
                        <span x-text="leaderboard.your_rank"></span>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900">Your Rank</div>
                        <div class="text-xs text-gray-600" x-text="'Out of ' + leaderboard.total_techs + ' technicians'"></div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-lg font-bold text-primary-600" x-text="leaderboard.your_score + ' pts'"></div>
                    <div class="text-xs text-gray-500">Score</div>
                </div>
            </div>
            <div class="space-y-2">
                <template x-for="(tech, index) in leaderboard.top_3" :key="index">
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div class="flex items-center space-x-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold" 
                                 :class="index === 0 ? 'bg-warning-100 text-warning-700' : index === 1 ? 'bg-gray-200 text-gray-700' : 'bg-warning-50 text-warning-600'">
                                <span x-text="index + 1"></span>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900" x-text="tech.name"></div>
                                <div class="text-xs text-gray-500" x-text="tech.hours + 'h billable'"></div>
                            </div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900" x-text="tech.score + ' pts'"></div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Achievement Badges --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-3">Recent Achievements</h2>
            <div class="grid grid-cols-3 gap-3">
                <template x-for="achievement in achievements" :key="achievement.id">
                    <div class="text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full mb-2"
                             :class="achievement.unlocked ? 'bg-success-100' : 'bg-gray-100'">
                            <span class="text-2xl" x-text="achievement.icon"></span>
                        </div>
                        <div class="text-xs font-medium" :class="achievement.unlocked ? 'text-gray-900' : 'text-gray-400'" x-text="achievement.name"></div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Monthly Trend Chart --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Monthly Trends</h2>
            <div class="space-y-3">
                <template x-for="month in monthlyTrend" :key="month.month">
                    <div>
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-gray-600" x-text="month.month"></span>
                            <span class="font-semibold text-gray-900" x-text="month.hours + 'h'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" :style="'width: ' + (month.hours / maxHours * 100) + '%'"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </main>

</div>

<script>
function statsApp() {
    return {
        period: 'month',
        currentPeriod: 'December 2025',
        stats: {
            total_hours: 0,
            hours_change: 0,
            revenue: 0,
            revenue_change: 0,
            tickets_closed: 0,
            tickets_open: 0,
            unique_clients: 0
        },
        leaderboard: {
            your_rank: 0,
            your_score: 0,
            total_techs: 0,
            top_3: []
        },
        achievements: [],
        monthlyTrend: [],
        maxHours: 200,

        init() {
            this.loadStats();
            this.loadLeaderboard();
            this.loadAchievements();
            this.loadMonthlyTrend();
        },

        loadStats() {
            // Mock data
            this.stats = {
                total_hours: 165,
                hours_change: 12,
                revenue: 24750,
                revenue_change: 8,
                tickets_closed: 42,
                tickets_open: 8,
                unique_clients: 15
            };
        },

        loadLeaderboard() {
            this.leaderboard = {
                your_rank: 3,
                your_score: 850,
                total_techs: 12,
                top_3: [
                    { name: 'John Smith', hours: 180, score: 950 },
                    { name: 'Sarah Johnson', hours: 175, score: 920 },
                    { name: 'Mike Davis', hours: 165, score: 850 }
                ]
            };
        },

        loadAchievements() {
            this.achievements = [
                { id: 1, name: 'Speed Demon', icon: '⚡', unlocked: true },
                { id: 2, name: 'Perfect Week', icon: '🎯', unlocked: true },
                { id: 3, name: '100 Tickets', icon: '💯', unlocked: false },
                { id: 4, name: 'Client Favorite', icon: '⭐', unlocked: true },
                { id: 5, name: 'Early Bird', icon: '🐦', unlocked: false },
                { id: 6, name: 'Night Owl', icon: '🦉', unlocked: true }
            ];
        },

        loadMonthlyTrend() {
            this.monthlyTrend = [
                { month: 'Aug', hours: 145 },
                { month: 'Sep', hours: 160 },
                { month: 'Oct', hours: 155 },
                { month: 'Nov', hours: 170 },
                { month: 'Dec', hours: 165 }
            ];
            this.maxHours = Math.max(...this.monthlyTrend.map(m => m.hours)) * 1.1;
        }
    }
}
</script>
