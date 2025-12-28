# FinOps Master Seeder Documentation

## Overview

The FinOpsMasterSeeder generates production-grade test data to validate the "Triple Threat":
1. **Billing Accuracy** (proration, overrides, anomalies)
2. **Cash Flow** (AR aging, late payers, payment patterns)
3. **Profit Visibility** (COGS, margins, profitability guardrails)

## Data Volume

### Users (98 total)
- **Executives**: 3 (for simulation testing)
- **Architects**: 3 (for simulation testing)
- **Admins**: 2
- **Technicians**: 15 (with hourly cost rates)
- **Client Admins**: 25
- **Client Users**: 50

### Companies (55 total)
- **SMB Clients (Standard Tier)**: 30 companies
  - Employee count: 10-100
  - Monthly budget: $2,000-$10,000
  - Standard pricing
  
- **Non-Profit Clients (Discounted Tier)**: 10 companies
  - 25% discount on all services
  - Tax-exempt status
  - Employee count: 5-30
  
- **Consumer/Prosumer Accounts**: 10 companies
  - Small businesses and individuals
  - Employee count: 1-5
  - Lower budget tier
  
- **Legacy Clients (Custom Pricing)**: 5 companies
  - 20% discount locked via contract
  - Custom pricing notes
  - Higher employee counts (20-150)

### Products (Comprehensive Catalog)

**Recurring Services** (15 products):
- Managed Security (Standard, Premium, Enterprise)
- Helpdesk Support (Essential, Pro, Premium)
- Cloud Backup (100GB, 500GB, 1TB, 5TB)
- Monitoring (Network, Server)
- Email Services (M365 Basic, M365 Standard, Google Workspace)

**Hardware Assets** (15 products):
- Firewalls: Fortinet 60F/100F, SonicWall TZ400, Ubiquiti DMP
- Laptops: Dell Latitude, HP EliteBook, Lenovo X1, MacBook Pro
- VoIP Phones: Yealink T46S, Cisco 8845, Poly VVX450
- Servers: Dell R450, HPE DL360
- Networking: UniFi PoE Switch, Cisco Catalyst

**Labor SKUs** (4 products):
- Project Hours - Standard ($150/hr, COGS $55)
- Project Hours - Senior ($185/hr, COGS $65)
- Emergency After-Hours ($275/hr, COGS $85)
- On-Site Visit ($225/hr, COGS $75)

### Subscriptions (~200 total)
- Seat-based (Helpdesk, Security, Email)
- Usage-based (Cloud Backup)
- Device-based (Monitoring)
- Flat-rate (Network Monitoring)

**Key Features:**
- Random start dates (3-12 months ago)
- Contract lengths: 12, 24, or 36 months
- Renewal statuses: auto_renew, pending, churned
- 20% of subscriptions have mid-month changes (proration test)

### Invoices (1,000+ total)
- **Time Period**: Last 12 months
- **Frequency**: Monthly for each company
- **Components**:
  - Recurring subscription charges
  - 30% include project hours
  - 10% include hardware sales
  
**Payment Patterns (Realistic)**:
- **On-time**: Paid within 0-5 days of due date
- **30-day late**: Paid 30-45 days past due
- **60-day late**: Paid 60-75 days past due  
- **90+ day late**: Paid 90-120 days past due or unpaid
- **Unpaid**: Remains overdue (aging buckets)

**Distribution by Tier**:
- SMB: Mostly on-time (occasional 30/60-day late)
- Non-Profit: Mix of on-time and 30/60/90-day late
- Consumer: More variance (including unpaid)

### Support Tickets (500+ total)
- **Per Company**: 2-15 tickets based on size
- **Time Range**: Last 365 days
- **Priorities**: low, medium, high, urgent
- **Statuses**: open, in_progress, resolved, closed

**Billable Entries (Integrated)**:
- 1-4 entries per ticket
- 85% billable, 15% internal
- Hours: 0.5-4.0 per entry
- After-hours premium rate (6 PM - 8 AM)
- Technician assignment (15 technicians)
- Cost tracking (hourly_cost per technician)

### Retainers (~50 total)
- **Distribution**: 40% of SMB + all legacy clients
- **Sizes**: 20, 40, 80, 120 hours
- **Pricing**: $140/hour
- **Usage**: 10-95% depleted
- **Statuses**: active, depleted, expired

**Test Cases:**
- Some at 80%+ usage (low balance alerts)
- Some fully depleted
- Some expired

### Special Test Cases

#### 1. The "Anomaly" Case
**Company:** First SMB client
**Scenario:** Bill suddenly jumps from $1k to $5k

- **Normal Invoice** (last month): $1,000
- **Anomalous Invoice** (this month): $5,000 (5x increase!)
- **Causes**:
  - Emergency firewall replacement (2x Fortinet 60F)
  - 20 hours emergency after-hours support
- **Purpose:** Test AI anomaly detection alerts

#### 2. The "Proration" Case
**Company:** Second SMB client
**Scenario:** Mid-month seat addition

- **Original Subscription:** 10 seats Helpdesk Pro
- **Change Date:** 15th of last month
- **Addition:** 5 seats added mid-month
- **Invoice Line Items:**
  - Full month: 10 seats @ $125/seat = $1,250
  - Prorated: 5 seats @ ~$62.50/seat = $312.50
  - Total: $1,562.50
- **Purpose:** Test proration logic accuracy

#### 3. The "Dispute" Case
**Company:** Third SMB client
**Scenario:** Client disputes invoice charges

- **Invoice:** $2,500 overdue from 2 months ago
- **Status:** `is_disputed = true`, `dunning_paused = true`
- **Reason:** "Services not rendered" claim
- **Purpose:** Test dispute workflow and dunning pause logic

#### 4. The "Credit Note" Case
**Company:** Fourth SMB client  
**Scenario:** Credit applied for service issue

- **Invoice:** $3,000 total
- **Credit Note:** $500 applied
- **Remaining Balance:** $2,500
- **Status:** `partial` (credit applied, balance remaining)
- **Purpose:** Test credit note application workflow

## AR Aging Distribution

The seeder generates realistic aging buckets:

- **Current** (0-30 days): ~30% of unpaid invoices
- **1-30 days past due**: ~25% of unpaid invoices
- **31-60 days past due**: ~20% of unpaid invoices
- **61-90 days past due**: ~15% of unpaid invoices
- **90+ days past due**: ~10% of unpaid invoices

## Deterministic Data

The seeder uses **Faker seed 42** for reproducible data:

```php
$this->faker->seed(42);
```

This ensures:
- Same companies, products, invoices on every run
- Consistent test results
- Reproducible bug reports
- Reliable automated testing

## Running the Seeder

### Basic Usage

```bash
php artisan db:seed --class=FinOpsMasterSeeder
```

### Fresh Start

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=FinOpsMasterSeeder
```

### Production Considerations

**DO NOT run in production!** This seeder is for development/testing only.

Add to `DatabaseSeeder.php`:

```php
public function run()
{
    if (app()->environment('local', 'testing')) {
        $this->call(FinOpsMasterSeeder::class);
    }
}
```

## Test Credentials

### Executives (Simulation Testing)
```
Email: executive1@finops.test
Email: executive2@finops.test
Email: executive3@finops.test
Password: password (all)
```

### Architects (Simulation Testing)
```
Email: architect1@finops.test
Email: architect2@finops.test
Email: architect3@finops.test
Password: password (all)
```

### Admins
```
Email: admin1@finops.test
Email: admin2@finops.test
Password: password (all)
```

### Technicians
```
Email: john.martinez@finops.test
Email: sarah.johnson@finops.test
... (15 total)
Password: password (all)
```

## What Gets Tested

### Billing Accuracy
- ✅ Proration calculations (mid-month changes)
- ✅ Tiered pricing (standard, non-profit, consumer)
- ✅ Custom pricing overrides (legacy clients)
- ✅ Tax-exempt handling
- ✅ Multi-product invoices
- ✅ Hardware + services + labor bundling

### Cash Flow
- ✅ AR aging buckets (Current, 30, 60, 90+)
- ✅ Late payment patterns
- ✅ Payment method distribution
- ✅ Dunning workflows
- ✅ Credit note applications
- ✅ Partial payments

### Profit Visibility
- ✅ COGS tracking (all products have cost)
- ✅ Margin analysis (revenue - COGS)
- ✅ Labor cost tracking (technician hourly rates)
- ✅ Profitability per client
- ✅ Profitability per product
- ✅ Profitability per ticket

### AI/ML Features
- ✅ Anomaly detection (sudden bill increase)
- ✅ Payment prediction (historical patterns)
- ✅ Churn prediction (renewal status, payment behavior)
- ✅ Client health scoring (multiple factors)

### Operational Workflows
- ✅ Subscription management
- ✅ Invoice generation
- ✅ Payment processing
- ✅ Ticket tracking
- ✅ Time tracking (billable entries)
- ✅ Retainer management

## Data Integrity

### Relationships
All foreign keys properly set:
- Companies → Invoices
- Invoices → Line Items
- Invoices → Payments
- Companies → Subscriptions
- Subscriptions → Products
- Tickets → Companies
- Tickets → Billable Entries
- Billable Entries → Technicians

### Constraints
- Invoice totals = sum of line items
- Payment amounts ≤ invoice totals
- Retainer hours used ≤ hours purchased
- Subscription quantities realistic for company size

### Timestamps
- Created dates span last 12 months
- Due dates follow invoice dates
- Payment dates follow due dates (with delays)
- Logical temporal flow

## Performance

### Seed Time
- Expected: 2-5 minutes (depends on hardware)
- Progress indicators show each step
- Final summary displays all counts

### Database Impact
- ~1,000 invoices
- ~2,500 invoice line items
- ~500 tickets
- ~1,500 billable entries
- ~200 subscriptions
- ~50 retainers
- **Total Records**: ~5,000+

## Extending the Seeder

### Adding More Companies

```php
// In seedCompanies()
for ($i = 1; $i <= 50; $i++) { // Change from 30 to 50
    // ... company creation
}
```

### Adding More Products

```php
// In seedProducts()
$recurringServices[] = [
    'name' => 'New Service',
    'sku' => 'NEW-SKU',
    'price' => 10000,
    'cogs' => 4000,
    'category' => 'new_category',
    'billing_type' => 'seat',
];
```

### Changing Payment Patterns

```php
// In createPaymentPattern()
$paymentBehavior = match($company->pricing_tier) {
    'standard' => $this->faker->randomElement(['on_time', 'on_time', 'late_30']),
    // Adjust probabilities as needed
};
```

## Troubleshooting

### Foreign Key Errors
Ensure migrations ran first:
```bash
php artisan migrate:fresh
php artisan db:seed --class=FinOpsMasterSeeder
```

### Out of Memory
Increase PHP memory limit:
```bash
php -d memory_limit=512M artisan db:seed --class=FinOpsMasterSeeder
```

### Faker Errors
Ensure Faker is installed:
```bash
composer require fakerphp/faker --dev
```

### Model Not Found
Check model namespaces and ensure all referenced models exist in your application.

## Summary Output

After completion, the seeder displays:

```
═══════════════════════════════════════════════════════
           📊 FINOPS MASTER SEEDER SUMMARY             
═══════════════════════════════════════════════════════

👥 USERS:
  • Executives: 3 (for simulation testing)
  • Architects: 3 (for simulation testing)
  • Admins: 2
  • Technicians: 15
  • Client Admins: 25
  • Client Users: 50
  → Total Users: 98

🏢 COMPANIES:
  • SMB Clients (Standard): 30
  • Non-Profits (Discounted): 10
  • Consumer/Prosumer: 10
  • Legacy (Custom Pricing): 5
  → Total Companies: 55

📦 PRODUCTS:
  • Recurring Services: 15
  • Hardware: 15
  • Labor SKUs: 4
  → Total Products: 34

📋 SUBSCRIPTIONS: ~200

📄 INVOICES:
  • Total: 1,000+
  • Paid: ~600
  • Partial: ~50
  • Overdue: ~350
  • Total Payments: ~650

🎫 SUPPORT:
  • Tickets: 500+
  • Billable Entries: 1,500+
  • Total Hours: 3,000+

⏰ RETAINERS:
  • Total: ~50
  • Active: ~30
  • Depleted: ~10
  • Expired: ~10

⚡ SPECIAL TEST CASES:
  ✓ Anomaly Case (bill jump $1k → $5k)
  ✓ Proration Case (mid-month seat addition)
  ✓ Dispute Case (flagged invoice)
  ✓ Credit Note Case (applied credit)

💰 AR AGING BUCKETS:
  • Current: ~350
  • 1-30 days: ~200
  • 31-60 days: ~80
  • 61-90 days: ~50
  • 90+ days: ~20

═══════════════════════════════════════════════════════
✅ Data seeding complete! Deterministic seed: 42
═══════════════════════════════════════════════════════

🔑 TEST CREDENTIALS:
  Email: executive1@finops.test | Password: password
  Email: architect1@finops.test | Password: password
  Email: admin1@finops.test | Password: password
```

## World-Class Data Validation

This seeder generates "World-Class" data because:

1. **Mathematical Accuracy**: Mid-month seat changes force proration logic to prove correctness
2. **Financial Reality**: AR aging with 90-day debts demonstrates emergency/critical UI zones
3. **Margin Testing**: Revenue + COGS enables profitability guardrails and anomaly detection
4. **Real Patterns**: Late payers, disputes, credit notes mirror actual business scenarios
5. **Scale Testing**: 1,000+ invoices validate performance under production load
6. **Edge Cases**: Anomalies, proration, disputes test exceptional handling
7. **Deterministic**: Seed 42 ensures reproducible results for debugging

## License

Part of the FinOps Billing Module - All Rights Reserved
