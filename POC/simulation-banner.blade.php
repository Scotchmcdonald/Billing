{{--
  Simulation Banner Component (Proof of Concept)
  
  Displays at top of viewport when simulation mode is active.
  Features:
  - Pulse animation indicating non-real state
  - Role switcher dropdown
  - Terminate simulation button
  - Developer overlay toggle (debug mode only)
  - Session timer display
  
  Part of: BATCH_10_ROLE_SIMULATION
--}}

@if(session('simulating'))
<div x-data="simulationBanner()" 
     x-init="startTimer()"
     class="fixed top-0 left-0 right-0 z-50 bg-warning-50 border-b-2 border-warning-500 shadow-lg">
    
    <!-- Main Banner -->
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <!-- Left: Pulse Indicator & Status -->
        <div class="flex items-center space-x-3">
            <!-- Pulse Animation -->
            <div class="relative">
                <div class="h-3 w-3 bg-warning-500 rounded-full animate-pulse"></div>
                <div class="absolute inset-0 h-3 w-3 bg-warning-500 rounded-full animate-ping"></div>
            </div>
            
            <!-- Status Text -->
            <span class="text-warning-700 font-semibold text-sm">
                🎭 Simulation Mode Active
            </span>
            
            <!-- Timer -->
            <span class="text-warning-600 text-xs font-mono" x-text="timerDisplay"></span>
            
            <!-- Read-Only Badge -->
            <span class="px-2 py-0.5 bg-warning-200 text-warning-800 rounded text-xs font-medium">
                🔒 Read-Only
            </span>
        </div>

        <!-- Right: Controls -->
        <div class="flex items-center space-x-4">
            <!-- Role Switcher -->
            <div class="flex items-center space-x-2">
                <label class="text-warning-700 text-sm font-medium">Viewing as:</label>
                <select x-model="currentRole" 
                        @change="switchRole()"
                        class="bg-white border-warning-300 rounded-md px-3 py-1.5 text-sm focus:ring-warning-500 focus:border-warning-500 cursor-pointer">
                    <option value="technician">🔧 Technician</option>
                    <option value="client_admin">👔 Client Admin</option>
                    <option value="client_user">👤 Client User</option>
                </select>
            </div>

            <!-- Developer Overlay Toggle (Debug Mode Only) -->
            @if(config('app.debug'))
            <button @click="toggleOverlay()" 
                    type="button"
                    class="flex items-center space-x-1 px-3 py-1.5 bg-warning-100 hover:bg-warning-200 rounded-md text-warning-700 text-sm font-medium transition focus:ring-2 focus:ring-warning-500 focus:ring-offset-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"></path>
                </svg>
                <span>Debug</span>
            </button>
            @endif

            <!-- Terminate Button -->
            <form action="{{ route('simulation.terminate') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="flex items-center space-x-1 px-4 py-1.5 bg-danger-500 hover:bg-danger-600 active:bg-danger-700 text-white rounded-md text-sm font-medium transition shadow-sm focus:ring-2 focus:ring-danger-500 focus:ring-offset-1">
                    <span>❌</span>
                    <span>Exit Simulation</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Developer Overlay (Collapsible) -->
    @if(config('app.debug'))
    <div x-show="showOverlay" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform -translate-y-2"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform translate-y-0"
         x-transition:leave-end="opacity-0 transform -translate-y-2"
         class="bg-gray-900 text-gray-100 border-t border-warning-500">
        
        <div class="container mx-auto px-4 py-3">
            <!-- Session Info -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs font-mono mb-3">
                <div>
                    <span class="text-gray-400">Original User:</span>
                    <span class="text-warning-300 ml-2">{{ session('original_user_id') ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Simulated Role:</span>
                    <span class="text-warning-300 ml-2">{{ session('simulated_role') ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Started At:</span>
                    <span class="text-warning-300 ml-2">{{ session('simulation_started_at') ? session('simulation_started_at')->format('H:i:s') : 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Session ID:</span>
                    <span class="text-warning-300 ml-2 truncate">{{ substr(session()->getId(), 0, 12) }}...</span>
                </div>
            </div>

            <!-- Permission Debug (If available) -->
            @if(session()->has('simulation_debug'))
            <div class="border-t border-gray-700 pt-3">
                <h5 class="text-xs font-semibold text-warning-400 mb-2">🔍 Last Permission Check:</h5>
                <div class="grid grid-cols-4 gap-4 text-xs">
                    <div>
                        <span class="text-gray-400">Ability:</span>
                        <span class="text-warning-300 ml-2 font-semibold">{{ session('simulation_debug.ability') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Role:</span>
                        <span class="text-warning-300 ml-2">{{ session('simulation_debug.simulated_role') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Result:</span>
                        <span class="ml-2 font-semibold {{ session('simulation_debug.result') === 'ALLOWED' ? 'text-success-400' : 'text-danger-400' }}">
                            {{ session('simulation_debug.result') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400">Reason:</span>
                        <span class="text-gray-300 ml-2 truncate">{{ session('simulation_debug.reason') }}</span>
                    </div>
                </div>
            </div>
            @endif

            <!-- Tips -->
            <div class="border-t border-gray-700 mt-3 pt-2">
                <p class="text-xs text-gray-400">
                    💡 <span class="text-warning-300">Tip:</span> POST/PUT/PATCH/DELETE requests are blocked in simulation mode. Use <code class="bg-gray-800 px-1 py-0.5 rounded">_simulation_override</code> flag to bypass (logged).
                </p>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
function simulationBanner() {
    return {
        currentRole: '{{ session('simulated_role') }}',
        showOverlay: false,
        timerDisplay: '0:00',
        timerInterval: null,
        startedAt: {{ session('simulation_started_at') ? session('simulation_started_at')->timestamp * 1000 : 'Date.now()' }},
        
        startTimer() {
            this.updateTimer();
            this.timerInterval = setInterval(() => {
                this.updateTimer();
            }, 1000);
        },
        
        updateTimer() {
            const elapsed = Math.floor((Date.now() - this.startedAt) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            this.timerDisplay = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        },
        
        switchRole() {
            fetch('{{ route('simulation.switch-role') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ role: this.currentRole })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Role switch failed:', error);
                alert('Failed to switch role. Please try again.');
            });
        },
        
        toggleOverlay() {
            this.showOverlay = !this.showOverlay;
        }
    }
}

// Keyboard shortcut: Ctrl+Shift+D
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.shiftKey && e.key === 'D') {
        e.preventDefault();
        const banner = Alpine.$data(document.querySelector('[x-data="simulationBanner()"]'));
        if (banner) {
            banner.toggleOverlay();
        }
    }
});
</script>
@endif
