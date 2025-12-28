# Role Simulation Engine - Proof of Concept

## Overview

This directory contains a working proof-of-concept (POC) implementation of the Role Simulation Engine, as specified in `Docs/WORK_PACKETS/BATCH_10_ROLE_SIMULATION.md`.

## What's Included

### Core Components

1. **SimulationMiddleware.php** (~70 lines)
   - Enforces read-only mode during simulation
   - Blocks POST/PUT/PATCH/DELETE requests
   - Logs override flag usage
   - Returns clear error messages

2. **simulation-banner.blade.php** (~200 lines)
   - Persistent banner with pulse animation
   - Role switcher dropdown
   - Terminate simulation button
   - Developer overlay with permission debug
   - Session timer display
   - Keyboard shortcut support (Ctrl+Shift+D)

3. **SimulationController.php** (~150 lines)
   - Start simulation session
   - Switch simulated role
   - Terminate simulation
   - Get simulation status (API)
   - Complete audit logging

4. **simulation-routes.php** (~25 lines)
   - RESTful routes for simulation features
   - Middleware protection (auth, role)
   - Named routes for easy reference

5. **simulation-gate-override.php** (~70 lines)
   - Session-based permission override
   - Role-to-permissions mapping
   - Developer debug logging
   - Non-intrusive gate logic

6. **simulation-config.php** (~80 lines)
   - Feature toggle
   - Role configuration
   - Session duration limits
   - Audit settings

## Integration Instructions

### Step 1: Register Middleware

Add to `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'web' => [
        // ... existing middleware
        \App\Http\Middleware\SimulationMiddleware::class,
    ],
];
```

Copy `SimulationMiddleware.php` to `app/Http/Middleware/`

### Step 2: Add Routes

Add to `routes/web.php`:

```php
require __DIR__ . '/simulation-routes.php';
```

Or copy the route definitions directly into `web.php`.

### Step 3: Override Gates

Add to `app/Providers/AuthServiceProvider.php` boot() method, **BEFORE** other gate definitions:

```php
public function boot()
{
    $this->registerPolicies();
    
    // Simulation gate override (add FIRST)
    require base_path('POC/simulation-gate-override.php');
    
    // ... other gate definitions
}
```

### Step 4: Add Banner to Layout

Add to `resources/views/layouts/app.blade.php` (or main layout), just after opening `<body>`:

```blade
<body>
    <x-simulation-banner />
    
    <!-- Rest of layout -->
</body>
```

Copy `simulation-banner.blade.php` to `resources/views/components/`

### Step 5: Add Configuration

Copy `simulation-config.php` to `config/simulation.php`

### Step 6: Environment Variables

Add to `.env`:

```env
SIMULATION_ENABLED=true
```

### Step 7: Copy Controller

Copy `SimulationController.php` to `app/Http/Controllers/`

## Testing the POC

### Starting a Simulation

1. Log in as an executive or admin
2. Navigate to any page
3. POST to `/simulation/start` with:
   ```json
   {
     "role": "technician"
   }
   ```
4. Banner appears at top
5. UI adapts to technician permissions

### Switching Roles

1. While in simulation, use dropdown in banner
2. Page reloads with new role
3. Permissions update automatically

### Developer Overlay

1. Press `Ctrl+Shift+D` or click "Debug" button
2. See last permission check details
3. View session information
4. Track allowed/denied decisions

### Read-Only Enforcement

1. Try to submit a form during simulation
2. Receive 403 error with clear message
3. Add `_simulation_override=true` to proceed (logged)

### Terminating Simulation

1. Click "Exit Simulation" button
2. Banner disappears
3. Normal permissions restored
4. Audit log recorded

## Features Demonstrated

### ✅ Implemented in POC

- Session-based role override
- Read-only middleware enforcement
- Animated simulation banner
- Role switcher dropdown
- Terminate button
- Developer overlay
- Permission debugging
- Audit logging
- Keyboard shortcuts
- Timer display
- Override flag mechanism

### 🚧 Not in POC (Full Implementation)

- Specific user simulation (UI)
- Max duration auto-termination
- Simulation history viewer
- Recorded playback
- Advanced permission mapper
- Automated testing UI
- Multi-company simulation
- Visual permission graphs

## Code Metrics

**POC Lines of Code:**
- SimulationMiddleware: 70 lines
- simulation-banner: 200 lines
- SimulationController: 150 lines
- simulation-routes: 25 lines
- simulation-gate-override: 70 lines
- simulation-config: 80 lines
- **Total: ~595 lines**

**Estimated Full Implementation:**
- POC: ~595 lines
- UI enhancements: ~300 lines
- Testing: ~800 lines
- Documentation: ~200 lines
- **Total: ~1,900 lines**

## Security Considerations

### POC Includes

- ✅ Authorization checks (only executives/admins)
- ✅ Read-only enforcement
- ✅ Audit logging (all events)
- ✅ Override flag tracking
- ✅ Session-based (no DB pollution)
- ✅ Clear termination

### Production Enhancements Needed

- [ ] Max session duration enforcement
- [ ] Rate limiting on role switches
- [ ] IP-based session validation
- [ ] Admin notification on overrides
- [ ] Weekly audit report generation

## Next Steps

### For Production Use

1. **Add comprehensive tests** (unit, feature, browser)
2. **Implement session timeout** (auto-terminate after 4 hours)
3. **Create admin dashboard** (view active simulations)
4. **Build simulation history** (searchable log)
5. **Add more roles** (extend simulatable_roles config)
6. **Enhance overlay** (visual permission tree)
7. **Write documentation** (user guide, video tutorial)

### Quick Wins

- Add notification sound on simulation start
- Implement "quick switch" keyboard shortcuts
- Add simulation presets (common scenarios)
- Create "compare roles" side-by-side view
- Build permission diff viewer

## Troubleshooting

### Banner not showing

- Check session: `dd(session('simulating'))`
- Ensure component registered: `php artisan view:clear`
- Verify layout includes: `<x-simulation-banner />`

### Permissions not overriding

- Check gate is registered FIRST in AuthServiceProvider
- Enable debug mode: `APP_DEBUG=true`
- Check X-Simulation-Debug header is sent
- View debug overlay (Ctrl+Shift+D)

### POST requests still working

- Verify middleware registered in Kernel
- Check middleware order (should be near end)
- Ensure request doesn't have override flag
- Check logs for override usage

## Support

For questions or issues with the POC:

1. Review full specification: `Docs/WORK_PACKETS/BATCH_10_ROLE_SIMULATION.md`
2. Check integration instructions above
3. Enable debug mode for detailed logging
4. Review audit logs for session history

## License

Part of the FinOps Billing Module - All Rights Reserved
