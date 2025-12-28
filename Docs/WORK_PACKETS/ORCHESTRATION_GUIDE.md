# FinOps Work Packet Orchestration Guide

## Overview

This guide explains how to execute the work packets for transforming the FinOps billing module from "Functional" to "World Class" status.

---

## Batch Summary

| Batch | Name | Priority | Est. Effort | Dependencies |
|-------|------|----------|-------------|--------------|
| 1 | Foundation (DB/Models) | P1 | 2-3 days | None |
| 2 | Services | P1 | 3-4 days | Batch 1 |
| 3A | Finance Admin UI | P1 | 4-5 days | Batch 1, 2 |
| 3B | Client Portal UI | P1 | 3-4 days | Batch 1, 2 |
| 3C | Technician UI | P2 | 2-3 days | Batch 1, 2 |
| 4 | Integrations | P2-P3 | 4-5 days | Batch 1, 2 |
| 5 | Jobs & Automation | P2 | 3-4 days | Batch 1, 2, 4 |
| 6 | Testing | P1 | 5-7 days | All previous |
| 7 | AI/ML Features | P3 | 4-5 days | Batch 1, 2 |
| 8 | Localization & A11y | P3 | 3-4 days | Batch 3 |
| 9 | Advanced Reporting | P2-P3 | 4-5 days | Batch 1, 2 |

**Total Estimated Effort:** 38-49 days (sequential) / 15-20 days (parallel)

---

## Dependency Graph

```
                    ┌─────────────────┐
                    │   Batch 1       │
                    │   Foundation    │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │   Batch 2       │
                    │   Services      │
                    └────────┬────────┘
                             │
        ┌──────────┬─────────┼─────────┬──────────┐
        │          │         │         │          │
        ▼          ▼         ▼         ▼          ▼
   ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐
   │Batch 3A │ │Batch 3B │ │Batch 3C │ │ Batch 4 │ │ Batch 7 │
   │Finance  │ │ Portal  │ │ Tech UI │ │Integr.  │ │   AI    │
   └────┬────┘ └────┬────┘ └────┬────┘ └────┬────┘ └────┬────┘
        │          │         │         │          │
        │          │         │         ▼          │
        │          │         │    ┌─────────┐     │
        │          │         │    │ Batch 5 │     │
        │          │         │    │  Jobs   │     │
        │          │         │    └────┬────┘     │
        │          │         │         │          │
        └──────────┴─────────┴─────────┴──────────┘
                             │
                    ┌────────▼────────┐
                    │   Batch 6       │
                    │   Testing       │
                    └─────────────────┘
                    
   Parallel branches (post Batch 3):
   ┌─────────┐ ┌─────────┐
   │ Batch 8 │ │ Batch 9 │
   │  L10n   │ │Reports  │
   └─────────┘ └─────────┘
```

---

## Execution Strategy

### Phase 1: Foundation (Days 1-7)
**Execute Sequentially**

1. **Batch 1: Foundation** (Days 1-3)
   - Database migrations
   - Model creation
   - Basic relationships

2. **Batch 2: Services** (Days 4-7)
   - Business logic services
   - Depends on Batch 1 models

### Phase 2: UI & Integrations (Days 8-15)
**Execute in Parallel (3-4 Agents)**

| Agent A | Agent B | Agent C | Agent D |
|---------|---------|---------|---------|
| Batch 3A | Batch 3B | Batch 3C | Batch 4 |
| Finance UI | Portal UI | Tech UI | Integrations |

### Phase 3: Automation (Days 12-15)
**Start After Batch 4**

- **Batch 5: Jobs** - Can begin once integrations are ready

### Phase 4: Enhancements (Days 8-15)
**Execute in Parallel (Independent)**

| Agent E | Agent F | Agent G |
|---------|---------|---------|
| Batch 7 | Batch 8 | Batch 9 |
| AI Features | Localization | Reporting |

### Phase 5: Testing (Days 16-22)
**Sequential - Comprehensive**

- **Batch 6: Testing** - After all features complete

---

## Agent Assignment Template

When spawning an agent for a batch, use this template:

```
## Agent Configuration

**Batch:** [BATCH_NUMBER]
**Work Packet:** /var/www/html/Modules/Billing/Docs/WORK_PACKETS/[BATCH_FILE].md

## Context Files to Read

1. Work packet (primary)
2. Related existing code:
   - [Specific files relevant to batch]
3. Documentation:
   - /var/www/html/Modules/Billing/Docs/API_REFERENCE.md
   - /var/www/html/Modules/Billing/Docs/FINOPS_IMPLEMENTATION_PLAN.md

## Instructions

Read the work packet completely before starting. Execute tasks in order.
After completing each major section, verify with the provided commands.
Document any blockers or questions in your completion report.

## Completion Report Requirements

When finished, provide:
1. Tasks completed (with file paths)
2. Tasks skipped (with reason)
3. Issues encountered
4. Recommendations for next batch
```

---

## Parallel Execution Rules

### Can Run Simultaneously
- Batch 3A, 3B, 3C (after Batch 2)
- Batch 4 (after Batch 2)
- Batch 7, 8, 9 (after Batch 2)

### Must Wait For
- Batch 2 waits for Batch 1
- Batch 5 waits for Batch 4
- Batch 6 waits for all others
- Batch 8 should ideally follow Batch 3 (applies to UI)

### Conflict Zones
When running parallel agents, avoid conflicts:

| File/Area | Primary Owner | Coordination Required |
|-----------|--------------|----------------------|
| Migrations | Batch 1 only | Timestamp ordering |
| Models | Batch 1 creates, others extend | Method additions |
| Services | Batch 2 creates, others use | Interface contracts |
| Routes | Each batch owns section | Route name prefix |
| Views | By feature (3A, 3B, 3C) | Shared components |

---

## Quality Gates

### After Batch 1
```bash
php artisan migrate
php artisan tinker --execute="Modules\Billing\Models\Invoice::count()"
```

### After Batch 2
```bash
php artisan test --filter=Unit/Services
```

### After Batch 3 (any)
```bash
# Visual inspection of UI
# Navigate to /billing/invoices
# Verify components render
```

### After Batch 6
```bash
php artisan test --filter=Billing --coverage --min=80
```

### Final Gate
```bash
# Full test suite
php artisan test

# Static analysis
./vendor/bin/phpstan analyse Modules/Billing --level=5

# Code style
./vendor/bin/pint Modules/Billing --test
```

---

## Risk Mitigation

### If Batch Fails
1. Check completion report for specific errors
2. Review dependent batches for impact
3. Fix issues before proceeding to dependent batches

### Common Issues

| Issue | Solution |
|-------|----------|
| Migration conflict | Re-order timestamps |
| Missing model | Verify Batch 1 complete |
| Service not found | Check namespace, run composer dump |
| View not found | Check module namespace in Blade |
| Test failures | Review mocks, check DB seeding |

---

## Progress Tracking

Use this checklist to track overall progress:

- [ ] **Batch 1: Foundation** - Started: ___ / Completed: ___
- [ ] **Batch 2: Services** - Started: ___ / Completed: ___
- [ ] **Batch 3A: Finance UI** - Started: ___ / Completed: ___
- [ ] **Batch 3B: Portal UI** - Started: ___ / Completed: ___
- [ ] **Batch 3C: Technician UI** - Started: ___ / Completed: ___
- [ ] **Batch 4: Integrations** - Started: ___ / Completed: ___
- [ ] **Batch 5: Jobs** - Started: ___ / Completed: ___
- [ ] **Batch 6: Testing** - Started: ___ / Completed: ___
- [ ] **Batch 7: AI Features** - Started: ___ / Completed: ___
- [ ] **Batch 8: Localization** - Started: ___ / Completed: ___
- [ ] **Batch 9: Reporting** - Started: ___ / Completed: ___

---

## File Inventory

All work packets located in:
```
/var/www/html/Modules/Billing/Docs/WORK_PACKETS/
├── BATCH_1_FOUNDATION.md
├── BATCH_2_SERVICES.md
├── BATCH_3A_FINANCE_UI.md
├── BATCH_3B_PORTAL_UI.md
├── BATCH_3C_TECHNICIAN_UI.md
├── BATCH_4_INTEGRATIONS.md
├── BATCH_5_JOBS.md
├── BATCH_6_TESTING.md
├── BATCH_7_AI_FEATURES.md
├── BATCH_8_LOCALIZATION.md
├── BATCH_9_REPORTING.md
└── ORCHESTRATION_GUIDE.md (this file)
```

---

## Related Documentation

- [POST_IMPLEMENTATION_CHECKLIST.md](../POST_IMPLEMENTATION_CHECKLIST.md) - Master task list
- [FINOPS_IMPLEMENTATION_PLAN.md](../FINOPS_IMPLEMENTATION_PLAN.md) - Original implementation plan
- [User Stories/](../User%20Stories/) - Persona documentation
- [API_REFERENCE.md](../API_REFERENCE.md) - API documentation
