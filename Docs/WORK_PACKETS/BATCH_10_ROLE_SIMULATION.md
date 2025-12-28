# BATCH 10: Role Simulation Engine

## Executive Summary

The Role Simulation Engine enables Executives and Architects to view the application through different user roles (Technician, Client Admin, Client User) without logging out or affecting real data. This "sandbox" mode verifies the "Visibility Mandate" - ensuring proper permissions and UI states across all roles.

## Primary Objectives

1. **Non-Destructive Impersonation** - View as any role without logging out or modifying data
2. **Read-Only Safety** - Prevent accidental actions during simulation
3. **Permission Debugging** - Show why UI elements are hidden or visible
4. **Audit Trail** - Log all simulation sessions for compliance

## Technical Architecture

### 1. Session-Based Role Override

**Implementation Pattern:**
```php
// Store simulation state in session
session([
    'simulating' => true,
    'simulated_role' => 'technician',
    'simulated_user_id' => 123, // Optional: simulate specific user
    'original_user_id' => auth()->id(),
    'simulation_started_at' => now(),
]);

// Check in Gate/Policy
if (session('simulating')) {
    $effectiveRole = session('simulated_role');
} else {
    $effectiveRole = auth()->user()->role;
}
```

**Key Features:**
- Session-based (survives page refreshes)
- Non-destructive (original auth state preserved)
- Reversible (terminate button clears session)
- Auditable (logs start/end timestamps)

### 2. SimulationMiddleware

**Purpose:** Enforce read-only mode during simulation

**Location:** `app/Http/Middleware/SimulationMiddleware.php`

**Behavior:**
```php
public function handle($request, Closure $next)
{
    if (session('simulating')) {
        // Allow GET, HEAD, OPTIONS
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }
        
        // Block POST/PUT/PATCH/DELETE unless override flag present
        if (!$request->input('_simulation_override')) {
            return response()->json([
                'error' => 'Simulation Mode: Read-Only',
                'message' => 'POST requests blocked during simulation. Add _simulation_override flag to proceed.',
            ], 403);
        }
        
        // Log override usage
        Log::warning('Simulation override used', [
            'user' => auth()->id(),
            'simulated_role' => session('simulated_role'),
            'route' => $request->path(),
        ]);
    }
    
    return $next($request);
}
```

**Registration:**
```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\SimulationMiddleware::class,
    ],
];
```

### 3. Policy/Gate Override Layer

**Gate Helper Extension:**
```php
// app/Providers/AuthServiceProvider.php

Gate::before(function ($user, $ability) {
    // If simulating, override with simulated role
    if (session('simulating')) {
        $simulatedRole = session('simulated_role');
        
        // Map role to permissions
        $rolePermissions = [
            'technician' => ['view_tickets', 'create_time_entries', 'view_own_invoices'],
            'client_admin' => ['view_company_invoices', 'manage_payment_methods', 'view_reports'],
            'client_user' => ['view_own_tickets', 'submit_requests'],
        ];
        
        $hasPermission = in_array($ability, $rolePermissions[$simulatedRole] ?? []);
        
        // Developer overlay data
        if (config('app.debug') && request()->header('X-Simulation-Debug')) {
            session()->flash('simulation_debug', [
                'ability' => $ability,
                'simulated_role' => $simulatedRole,
                'result' => $hasPermission ? 'ALLOWED' : 'DENIED',
                'reason' => $hasPermission ? "Role '{$simulatedRole}' has permission" : "Role '{$simulatedRole}' lacks permission '{$ability}'",
            ]);
        }
        
        return $hasPermission ?: null;
    }
});
```

### 4. Simulation HUD Component

**Location:** `resources/views/components/simulation-banner.blade.php`

**Features:**
- Persistent banner at top of viewport
- Pulse animation to indicate active simulation
- Role switcher dropdown
- Terminate simulation button
- Developer overlay toggle
- Session timer display

**Implementation:**
```blade
@if(session('simulating'))
<div x-data="simulationBanner()" 
     class="fixed top-0 left-0 right-0 z-50 bg-warning-50 border-b-2 border-warning-500 shadow-lg">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <!-- Pulse Indicator -->
        <div class="flex items-center space-x-3">
            <div class="relative">
                <div class="h-3 w-3 bg-warning-500 rounded-full animate-pulse"></div>
                <div class="absolute inset-0 h-3 w-3 bg-warning-500 rounded-full animate-ping"></div>
            </div>
            <span class="text-warning-700 font-semibold">
                🎭 Simulation Mode Active
            </span>
            <span class="text-warning-600 text-sm">
                ({{ session('simulation_elapsed') ?? '0:00' }})
            </span>
        </div>

        <!-- Controls -->
        <div class="flex items-center space-x-4">
            <!-- Role Switcher -->
            <div>
                <label class="text-warning-700 text-sm font-medium mr-2">Viewing as:</label>
                <select x-model="currentRole" 
                        @change="switchRole()"
                        class="bg-white border-warning-300 rounded-md px-3 py-1 text-sm focus:ring-warning-500">
                    <option value="technician">🔧 Technician</option>
                    <option value="client_admin">👔 Client Admin</option>
                    <option value="client_user">👤 Client User</option>
                </select>
            </div>

            <!-- Developer Overlay Toggle -->
            @if(config('app.debug'))
            <button @click="toggleOverlay()" 
                    class="px-3 py-1 bg-warning-100 hover:bg-warning-200 rounded-md text-warning-700 text-sm font-medium transition">
                <svg class="w-4 h-4 inline" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 3.5a1.5 1.5 0 013 0V4a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-.5a1.5 1.5 0 000 3h.5a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-.5a1.5 1.5 0 00-3 0v.5a1 1 0 01-1 1H6a1 1 0 01-1-1v-3a1 1 0 00-1-1h-.5a1.5 1.5 0 010-3H4a1 1 0 001-1V6a1 1 0 011-1h3a1 1 0 001-1v-.5z"></path>
                </svg>
                Debug
            </button>
            @endif

            <!-- Terminate Button -->
            <form action="{{ route('simulation.terminate') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-1 bg-danger-500 hover:bg-danger-600 text-white rounded-md text-sm font-medium transition shadow-sm">
                    ❌ Exit Simulation
                </button>
            </form>
        </div>
    </div>

    <!-- Developer Overlay -->
    @if(config('app.debug') && session('simulation_debug'))
    <div x-show="showOverlay" 
         x-transition
         class="bg-gray-900 text-gray-100 px-4 py-2 text-xs font-mono border-t border-warning-500">
        <div class="container mx-auto">
            <div class="grid grid-cols-4 gap-4">
                <div>
                    <span class="text-gray-400">Ability:</span>
                    <span class="text-warning-300">{{ session('simulation_debug.ability') }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Role:</span>
                    <span class="text-warning-300">{{ session('simulation_debug.simulated_role') }}</span>
                </div>
                <div>
                    <span class="text-gray-400">Result:</span>
                    <span class="{{ session('simulation_debug.result') === 'ALLOWED' ? 'text-success-400' : 'text-danger-400' }}">
                        {{ session('simulation_debug.result') }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-400">Reason:</span>
                    <span class="text-gray-300">{{ session('simulation_debug.reason') }}</span>
                </div>
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
        
        switchRole() {
            fetch('{{ route('simulation.switch-role') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ role: this.currentRole })
            }).then(() => window.location.reload());
        },
        
        toggleOverlay() {
            this.showOverlay = !this.showOverlay;
        }
    }
}
</script>
@endif
```

### 5. Simulation Controller

**Location:** `app/Http/Controllers/SimulationController.php`

**Endpoints:**

```php
class SimulationController extends Controller
{
    /**
     * Start role simulation
     */
    public function start(Request $request)
    {
        $request->validate([
            'role' => 'required|in:technician,client_admin,client_user',
            'user_id' => 'nullable|exists:users,id',
        ]);
        
        // Only allow for executives/admins
        abort_unless(auth()->user()->hasRole(['executive', 'admin']), 403);
        
        session([
            'simulating' => true,
            'simulated_role' => $request->role,
            'simulated_user_id' => $request->user_id,
            'original_user_id' => auth()->id(),
            'simulation_started_at' => now(),
        ]);
        
        // Audit log
        activity()
            ->causedBy(auth()->user())
            ->event('simulation_started')
            ->withProperties([
                'simulated_role' => $request->role,
                'simulated_user_id' => $request->user_id,
            ])
            ->log('Started role simulation');
        
        return redirect()->back()->with('success', "Simulation started: Viewing as {$request->role}");
    }
    
    /**
     * Switch simulated role
     */
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => 'required|in:technician,client_admin,client_user',
        ]);
        
        abort_unless(session('simulating'), 403);
        
        session(['simulated_role' => $request->role]);
        
        return response()->json(['success' => true]);
    }
    
    /**
     * Terminate simulation
     */
    public function terminate()
    {
        if (session('simulating')) {
            $duration = now()->diffInSeconds(session('simulation_started_at'));
            
            // Audit log
            activity()
                ->causedBy(auth()->user())
                ->event('simulation_ended')
                ->withProperties([
                    'simulated_role' => session('simulated_role'),
                    'duration_seconds' => $duration,
                ])
                ->log('Ended role simulation');
            
            session()->forget(['simulating', 'simulated_role', 'simulated_user_id', 'original_user_id', 'simulation_started_at']);
        }
        
        return redirect()->route('dashboard')->with('success', 'Simulation terminated');
    }
}
```

**Routes:**
```php
// routes/web.php
Route::middleware(['auth', 'role:executive,admin'])->prefix('simulation')->name('simulation.')->group(function () {
    Route::post('start', [SimulationController::class, 'start'])->name('start');
    Route::post('switch-role', [SimulationController::class, 'switchRole'])->name('switch-role');
    Route::post('terminate', [SimulationController::class, 'terminate'])->name('terminate');
});
```

### 6. Visibility Mandate Audit

**Audit Patterns:**

Scan all Blade views for permission checks:

```bash
# Find @can directives
grep -r "@can" resources/views/

# Find role checks
grep -r "auth()->user()->role" resources/views/

# Find Livewire authorize
grep -r "\$authorize" app/Http/Livewire/
```

**Common Patterns:**
```blade
{{-- Pattern 1: Gate check --}}
@can('manage-invoices')
    <button>Edit Invoice</button>
@endcan

{{-- Pattern 2: Role check --}}
@if(auth()->user()->role === 'admin')
    <a href="/admin">Admin Panel</a>
@endif

{{-- Pattern 3: Livewire --}}
<livewire:invoice-actions :invoice="$invoice" />
// Component: authorize(['update'], [$invoice])
```

**Refactoring for Simulation:**

All permission checks should use Gates (not direct role checks):

```blade
{{-- ✅ GOOD: Uses Gate (simulation-aware) --}}
@can('manage-invoices')
    <button>Edit Invoice</button>
@endcan

{{-- ❌ BAD: Direct role check (simulation-blind) --}}
@if(auth()->user()->role === 'admin')
    <button>Edit Invoice</button>
@endif
```

### 7. Developer Overlay Implementation

**Purpose:** Show permission logic during simulation

**Features:**
- Displays checked ability
- Shows simulated role
- Indicates ALLOWED/DENIED
- Explains reasoning

**Toggle:**
- Keyboard shortcut: `Ctrl+Shift+D`
- Banner button (debug mode only)
- URL parameter: `?debug=1`

**Display:**
```blade
@if(config('app.debug') && session('simulating'))
<div class="fixed bottom-4 right-4 bg-gray-900 text-white rounded-lg shadow-2xl p-4 max-w-md z-50">
    <h4 class="text-sm font-bold mb-2 text-warning-400">🔍 Permission Debug</h4>
    
    @foreach(session('simulation_debug_log', []) as $check)
    <div class="mb-2 p-2 rounded {{ $check['result'] === 'ALLOWED' ? 'bg-success-900/20' : 'bg-danger-900/20' }}">
        <div class="flex items-center justify-between">
            <span class="font-mono text-xs">{{ $check['ability'] }}</span>
            <span class="text-xs font-bold {{ $check['result'] === 'ALLOWED' ? 'text-success-400' : 'text-danger-400' }}">
                {{ $check['result'] }}
            </span>
        </div>
        <p class="text-xs text-gray-400 mt-1">{{ $check['reason'] }}</p>
    </div>
    @endforeach
</div>
@endif
```

## Security Considerations

### 1. Authorization

**Who Can Simulate:**
- Only users with `executive` or `admin` roles
- Requires `simulate-roles` permission
- Logged for compliance audits

### 2. Data Protection

**Read-Only Enforcement:**
- POST/PUT/PATCH/DELETE blocked by middleware
- Override flag requires explicit intent
- All overrides logged with context

### 3. Session Security

**Hijacking Prevention:**
- Session includes original user ID
- Simulation state tied to session
- Terminates on logout
- Max duration: 4 hours (configurable)

### 4. Audit Trail

**Logged Events:**
- Simulation start (who, when, simulated role)
- Role switches during simulation
- Override flag usage (route, reason)
- Simulation termination (duration)

```php
// Example audit log entry
[
    'event' => 'simulation_started',
    'causer_id' => 1,
    'causer_type' => 'App\Models\User',
    'properties' => [
        'simulated_role' => 'technician',
        'simulated_user_id' => 45,
        'started_at' => '2024-01-15 10:30:00',
    ],
]
```

## Testing Strategy

### Unit Tests

```php
public function test_simulation_middleware_blocks_post_requests()
{
    session(['simulating' => true, 'simulated_role' => 'technician']);
    
    $response = $this->post('/invoices', ['amount' => 100]);
    
    $response->assertStatus(403);
    $response->assertJson(['error' => 'Simulation Mode: Read-Only']);
}

public function test_gate_overrides_with_simulated_role()
{
    session(['simulating' => true, 'simulated_role' => 'technician']);
    
    $this->assertFalse(Gate::allows('manage-invoices'));
    $this->assertTrue(Gate::allows('view-tickets'));
}

public function test_simulation_terminates_correctly()
{
    session(['simulating' => true, 'simulated_role' => 'client_admin']);
    
    $this->post(route('simulation.terminate'));
    
    $this->assertFalse(session('simulating'));
}
```

### Feature Tests

```php
public function test_executive_can_start_simulation()
{
    $executive = User::factory()->executive()->create();
    
    $this->actingAs($executive)
         ->post(route('simulation.start'), ['role' => 'technician'])
         ->assertRedirect()
         ->assertSessionHas('simulating', true);
}

public function test_regular_user_cannot_start_simulation()
{
    $user = User::factory()->create();
    
    $this->actingAs($user)
         ->post(route('simulation.start'), ['role' => 'technician'])
         ->assertStatus(403);
}
```

### Browser Tests

```php
public function test_simulation_banner_displays_correctly()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs(User::factory()->executive()->create())
                ->visit('/simulation/start?role=technician')
                ->assertSee('Simulation Mode Active')
                ->assertSee('Viewing as: Technician')
                ->click('@terminate-button')
                ->assertDontSee('Simulation Mode Active');
    });
}
```

## Performance Considerations

### Caching

**Gate Results:**
- Cache permission checks within request
- Clear cache on role switch
- No persistent cache during simulation

### Session Management

**Optimization:**
- Minimal session data (role string, timestamps)
- No database queries in middleware
- Session driver: `redis` for multi-server

## UI/UX Guidelines

### Banner Design

**Visibility:**
- Fixed position, always visible
- High contrast (warning colors)
- Pulse animation
- Cannot be dismissed (only terminated)

**Controls:**
- Large, obvious terminate button
- Clear role indication
- Timer showing duration
- No ambiguity about active state

### Permission Feedback

**When Hidden:**
```blade
@cannot('edit-invoice')
    {{-- Show explanation in simulation mode --}}
    @if(session('simulating'))
    <div class="text-sm text-gray-500 italic">
        🚫 Button hidden: Technician role lacks 'edit-invoice' permission
    </div>
    @endif
@endcannot
```

**When Disabled:**
```blade
<button 
    @disabled(!Gate::allows('delete-invoice'))
    class="{{ Gate::denies('delete-invoice') && session('simulating') ? 'opacity-50 cursor-not-allowed' : '' }}"
    title="{{ Gate::denies('delete-invoice') && session('simulating') ? 'Disabled: Missing delete-invoice permission' : '' }}">
    Delete
</button>
```

## Configuration

**config/simulation.php:**
```php
return [
    'enabled' => env('SIMULATION_ENABLED', true),
    
    'allowed_roles' => ['executive', 'admin', 'architect'],
    
    'simulatable_roles' => [
        'technician',
        'client_admin',
        'client_user',
    ],
    
    'max_duration_hours' => 4,
    
    'read_only' => true,
    
    'developer_overlay' => [
        'enabled' => env('APP_DEBUG', false),
        'keyboard_shortcut' => 'Ctrl+Shift+D',
    ],
    
    'audit' => [
        'log_start' => true,
        'log_switches' => true,
        'log_overrides' => true,
        'log_termination' => true,
    ],
];
```

## Implementation Checklist

### Phase 1: Core Infrastructure (MVP)
- [ ] Create SimulationMiddleware
- [ ] Implement session-based role storage
- [ ] Create simulation-banner component
- [ ] Build SimulationController (start/terminate)
- [ ] Register routes
- [ ] Add Gate override logic

### Phase 2: Safety & Security
- [ ] Implement read-only enforcement
- [ ] Add override flag mechanism
- [ ] Create audit logging
- [ ] Add authorization checks
- [ ] Set session timeout

### Phase 3: Developer Experience
- [ ] Build developer overlay
- [ ] Add permission debug display
- [ ] Implement keyboard shortcuts
- [ ] Create visibility audit tool
- [ ] Add permission tooltips

### Phase 4: Testing & Polish
- [ ] Write unit tests (middleware, gates)
- [ ] Write feature tests (workflows)
- [ ] Write browser tests (UI)
- [ ] Performance optimization
- [ ] Documentation

## Migration Path

### From Current State

1. **Add middleware:** Register `SimulationMiddleware` in Kernel
2. **Update gates:** Add simulation check to `Gate::before()`
3. **Include banner:** Add `<x-simulation-banner />` to layout
4. **Create routes:** Add simulation routes to `web.php`
5. **Refactor views:** Convert role checks to Gate checks

### Backward Compatibility

- All changes are additive (no breaking changes)
- Simulation feature is opt-in
- Existing auth logic unchanged when not simulating
- Can be disabled via config

## Success Criteria

### Functional
- ✅ Executives can simulate any role
- ✅ UI adapts correctly to simulated role
- ✅ POST requests blocked in simulation mode
- ✅ Simulation terminates cleanly
- ✅ All actions audited

### Security
- ✅ Only authorized users can simulate
- ✅ No data modification during simulation
- ✅ Session hijacking prevented
- ✅ Audit trail complete

### UX
- ✅ Banner always visible when simulating
- ✅ Clear indication of simulated role
- ✅ One-click termination
- ✅ Developer overlay helpful
- ✅ No confusion about real vs simulated state

## Future Enhancements

### Phase 5 (Optional)
- Simulate specific user (not just role)
- Multi-company simulation
- Simulation history viewer
- Recorded simulation playback
- Automated permission testing

### Phase 6 (Advanced)
- AI-powered permission suggestions
- Visual permission mapper
- Bulk permission audits
- Role comparison tool
- Permission conflict detection

## Estimated Effort

**MVP (Phase 1-2):** 16-20 hours
- Core infrastructure: 8 hours
- Safety & security: 8 hours
- Testing: 4 hours

**Full Implementation (Phase 1-4):** 32-40 hours
- MVP: 20 hours
- Developer tools: 8 hours
- Polish & testing: 12 hours

**Lines of Code Estimate:**
- Middleware & Controllers: ~400 lines
- Blade Components: ~600 lines
- Tests: ~800 lines
- Configuration: ~100 lines
- **Total: ~1,900 lines**

## Dependencies

**Required:**
- Laravel 10+ (session, gates, policies)
- Alpine.js (banner interactivity)
- Tailwind CSS (styling)
- Spatie Activity Log (audit trail)

**Optional:**
- Laravel Debugbar (developer overlay integration)
- Laravel Telescope (session monitoring)

## Conclusion

The Role Simulation Engine provides a powerful, secure way for executives to verify permissions and UI states across roles. The architecture prioritizes safety (read-only by default), transparency (comprehensive audit trail), and developer experience (debug overlay showing permission logic).

This feature aligns perfectly with the "Pilot's Cockpit" philosophy: giving users powerful tools with clear feedback, proper guardrails, and no ambiguity about the current state.
