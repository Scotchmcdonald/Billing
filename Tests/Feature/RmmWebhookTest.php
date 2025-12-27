<?php

namespace Modules\Billing\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Models\Company;
use Modules\Billing\Models\Subscription;
use Modules\Billing\Models\UsageChange;

class RmmWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_rmm_webhook_updates_usage()
    {
        // Setup
        $company = Company::create([
            'name' => 'Test Company',
            'email' => 'test@example.com',
            'is_active' => true
        ]);

        $subscription = Subscription::create([
            'company_id' => $company->id,
            'name' => 'RMM Monitoring',
            'stripe_id' => 'sub_123',
            'stripe_status' => 'active',
            'quantity' => 10,
        ]);

        // Payload
        $payload = [
            'company_id' => $company->id,
            'device_count' => 15,
            'timestamp' => now()->toIso8601String(),
            // 'device_list' => ['device1', 'device2'] // Removed to rely on device_count
        ];

        // Act
        $response = $this->postJson(route('billing.webhooks.rmm.device-count'), $payload);

        // Assert
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('usage_changes', [
            'company_id' => $company->id,
            'subscription_id' => $subscription->id,
            'old_quantity' => 10,
            'new_quantity' => 15,
            'delta' => 5,
            'status' => 'pending'
        ]);
    }
}
