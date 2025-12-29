# Billing Module Seeders

This directory contains seeders for the Billing module, designed to generate realistic data for development, testing, and demonstrations.

## Available Seeders

| Seeder | Class | Purpose |
|--------|-------|---------|
| **Billing Demo Seeder** | `BillingDemoSeeder` | **Recommended for Demos.** Creates a clean, curated set of 8 companies with specific scenarios to showcase UI features (Disputes, Retainers, AR Aging). |
| **FinOps Master Seeder** | `FinOpsMasterSeeder` | **Recommended for Stress Testing.** Generates high-volume data (55 companies, 1000+ invoices) to validate financial accuracy, aging logic, and performance. |

---

## 🚀 Billing Demo Seeder (Quick Start)

Use this seeder when you want to demonstrate specific features or test the UI with manageable data.

### Usage

```bash
php artisan db:seed --class=Modules\\\\Billing\\\\Database\\\\Seeders\\\\BillingDemoSeeder
```

### What It Creates
- **8 Curated Companies** with distinct "personas" (Happy, Late Payer, Disputer, etc.)
- **16 Products** (Service plans, licenses, one-time fees)
- **Specific Scenarios** to populate all tabbed interfaces:
  - **Disputes Tab**: Active dispute with attachments (Initech)
  - **AR Aging Tab**: Overdue invoices (Soylent Industries)
  - **Retainers Tab**: Professional services retainer (Wayne Enterprises)
  - **Credit Notes**: Service credit applied (Wonka Industries)
  - **Price Overrides**: Negotiated rates for Enterprise clients

### Company Scenarios
1. **Acme Corp**: Happy long-term customer (24 months perfect history)
2. **Globex**: Growth story (upgrades + add-ons)
3. **Initech**: Disputed invoice (SLA breach claim)
4. **Soylent**: Overdue payments (AR aging demo)
5. **Umbrella**: Premium negotiated pricing
6. **Wayne Enterprises**: Retainer model
7. **Stark Industries**: New onboarding (setup fees)
8. **Wonka**: Credit notes/refunds

### Cleanup
```bash
php artisan billing:clean-demo
```

---

## 🧪 FinOps Master Seeder (Deep Dive)

Use this seeder when you need to validate complex financial logic, performance, or "Triple Threat" metrics (Accuracy, Cash Flow, Profitability).

### Usage

```bash
php artisan db:seed --class=Modules\\\\Billing\\\\Database\\\\Seeders\\\\FinOpsMasterSeeder
```

### What It Creates
- **55 Companies** across 4 tiers (SMB, Non-Profit, Consumer, Legacy)
- **98 Users** (Admins, Technicians, Clients)
- **1,000+ Invoices** with realistic payment patterns
- **500+ Support Tickets** & 1,500+ Billable Entries
- **Triple Threat Validation**:
  - **Billing Accuracy**: Proration, overrides, tax-exempt status
  - **Cash Flow**: Realistic AR aging (30/60/90+ days late)
  - **Profit Visibility**: COGS tracking, margin analysis

### Special Test Cases
1. **The "Anomaly"**: Bill jumps 5x (test AI detection)
2. **The "Proration"**: Mid-month seat addition (test calculation logic)
3. **The "Dispute"**: Flagged invoice with dunning paused
4. **The "Credit Note"**: Partial credit applied

### Deterministic Data
Uses `Faker seed 42` to ensure reproducible results for automated testing.

---

## 🛠️ General Usage

### Fresh Start (Reset Everything)
To completely reset the database and seed with the Master Seeder:

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=Modules\\\\Billing\\\\Database\\\\Seeders\\\\FinOpsMasterSeeder
```

### Troubleshooting
- **Memory Limit**: If seeding fails with OOM, use `php -d memory_limit=512M artisan ...`
- **Class Not Found**: Ensure you use the full namespace `Modules\Billing\Database\Seeders\...`

---
*Part of the FinOps Billing Module*
