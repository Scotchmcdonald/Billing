<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\UsageChange;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Carbon\Carbon;

class UsageChangeSeeder extends Seeder
{
    /**
     * Seed usage changes for the usage review queue.
     * 
     * This represents changes to subscription quantities that need approval,
     * such as when customers add/remove users or devices from their plans.
     */
    public function run(): void
    {
        // Get companies with subscriptions
        $companies = Company::whereHas('subscriptions')->with('subscriptions')->get();

        if ($companies->isEmpty()) {
            $this->command->warn('⚠ No companies with subscriptions found. Please run CompanySubscriptionSeeder first.');
            return;
        }

        $sources = ['rmm', 'portal', 'technician_request', 'customer_portal', 'api'];
        $changeScenarios = [
            // Growth scenarios
            ['old' => 10, 'new' => 15, 'reason' => 'Customer hired 5 new employees'],
            ['old' => 25, 'new' => 30, 'reason' => 'Added workstations for new department'],
            ['old' => 5, 'new' => 8, 'reason' => 'Expanded office - 3 new users'],
            ['old' => 50, 'new' => 75, 'reason' => 'Company acquisition - merged IT'],
            ['old' => 12, 'new' => 18, 'reason' => 'Seasonal staff increase'],
            ['old' => 20, 'new' => 24, 'reason' => 'Added remote workers'],
            ['old' => 8, 'new' => 12, 'reason' => 'Department expansion'],
            ['old' => 100, 'new' => 120, 'reason' => 'New branch office opened'],
            
            // Reduction scenarios
            ['old' => 30, 'new' => 25, 'reason' => 'Staff reduction - layoffs'],
            ['old' => 15, 'new' => 12, 'reason' => 'End of project - contractors departed'],
            ['old' => 40, 'new' => 35, 'reason' => 'Department restructuring'],
            ['old' => 8, 'new' => 5, 'reason' => 'Seasonal workers left'],
            ['old' => 60, 'new' => 50, 'reason' => 'Office consolidation'],
            
            // Device changes
            ['old' => 45, 'new' => 52, 'reason' => 'New laptops deployed'],
            ['old' => 22, 'new' => 18, 'reason' => 'Old workstations decommissioned'],
            ['old' => 35, 'new' => 42, 'reason' => 'Mobile device rollout'],
        ];

        $created = 0;
        $companiesProcessed = 0;

        foreach ($companies->take(10) as $company) {
            // Create 1-3 usage changes per company
            $changesForCompany = rand(1, 3);
            
            for ($i = 0; $i < $changesForCompany; $i++) {
                $subscription = $company->subscriptions->random();
                $scenario = $changeScenarios[array_rand($changeScenarios)];
                
                $oldQty = $scenario['old'];
                $newQty = $scenario['new'];
                $delta = $newQty - $oldQty;
                
                // Vary the created_at dates to show recent activity
                $daysAgo = rand(0, 7);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(0, 23));
                
                UsageChange::create([
                    'company_id' => $company->id,
                    'subscription_id' => $subscription->id,
                    'old_quantity' => $oldQty,
                    'new_quantity' => $newQty,
                    'delta' => $delta,
                    'status' => 'pending',
                    'source' => $sources[array_rand($sources)],
                    'metadata' => [
                        'reason' => $scenario['reason'],
                        'detected_by' => $this->getDetectionSource($sources[array_rand($sources)]),
                        'requires_approval' => abs($delta) >= 5, // Flag significant changes
                        'estimated_mrr_impact' => $delta * ($subscription->effective_price ?? 100),
                        'change_type' => $delta > 0 ? 'increase' : 'decrease',
                        'percentage_change' => round(($delta / $oldQty) * 100, 2),
                    ],
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
                
                $created++;
            }
            
            $companiesProcessed++;
        }

        $this->command->info("✓ Created {$created} pending usage changes for {$companiesProcessed} companies");
        
        // Create some historical (approved/rejected) changes for context
        $this->seedHistoricalChanges($companies);
    }

    /**
     * Seed some historical approved/rejected changes for reporting context
     */
    private function seedHistoricalChanges($companies)
    {
        $historical = 0;
        
        foreach ($companies->take(5) as $company) {
            $subscription = $company->subscriptions->random();
            
            // Create an approved change from 2 weeks ago
            UsageChange::create([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'old_quantity' => 10,
                'new_quantity' => 12,
                'delta' => 2,
                'status' => 'approved',
                'source' => 'rmm',
                'metadata' => [
                    'reason' => 'New hires onboarded',
                    'approved_by' => 'Finance Team',
                    'approved_at' => Carbon::now()->subDays(14)->toDateTimeString(),
                ],
                'created_at' => Carbon::now()->subDays(14),
                'updated_at' => Carbon::now()->subDays(14),
            ]);
            
            // Create a rejected change from 1 week ago
            UsageChange::create([
                'company_id' => $company->id,
                'subscription_id' => $subscription->id,
                'old_quantity' => 12,
                'new_quantity' => 8,
                'delta' => -4,
                'status' => 'rejected',
                'source' => 'portal',
                'metadata' => [
                    'reason' => 'Customer requested reduction - pending contract verification',
                    'rejected_by' => 'Finance Team',
                    'rejected_at' => Carbon::now()->subDays(7)->toDateTimeString(),
                    'rejection_reason' => 'Contract minimum not met',
                ],
                'created_at' => Carbon::now()->subDays(7),
                'updated_at' => Carbon::now()->subDays(7),
            ]);
            
            $historical += 2;
        }
        
        $this->command->info("✓ Created {$historical} historical usage changes for reporting context");
    }

    /**
     * Get human-readable detection source
     */
    private function getDetectionSource(string $source): string
    {
        return match($source) {
            'rmm' => 'RMM Auto-Detection',
            'portal' => 'Customer Portal Request',
            'technician_request' => 'Technician Field Report',
            'customer_portal' => 'Customer Self-Service',
            'api' => 'API Integration',
            default => 'System',
        };
    }
}
