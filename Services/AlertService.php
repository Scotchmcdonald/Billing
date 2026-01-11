<?php

namespace Modules\Billing\Services;

use Modules\Billing\Models\Company;
use Modules\Billing\Models\Invoice;
use Modules\Billing\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AlertService
{
    /**
     * Check all thresholds and return triggered alerts.
     *
     * @return array<int, array<string, mixed>>
     */
    public function checkThresholds(): array
    {
        $alerts = [];
        $config = $this->getThresholdConfig();

        // Check churn rate
        if ($config['churn_rate']['enabled']) {
            $churnRate = $this->calculateChurnRate();
            if ($churnRate > $config['churn_rate']['threshold']) {
                $alerts[] = [
                    'type' => 'churn_rate',
                    'severity' => 'high',
                    'message' => "Churn rate is {$churnRate}%, exceeds threshold of {$config['churn_rate']['threshold']}%",
                    'value' => $churnRate,
                    'threshold' => $config['churn_rate']['threshold'],
                ];
            }
        }

        // Check AR aging
        if ($config['ar_aging']['enabled']) {
            $arTotal = $this->calculateArTotal();
            if ($arTotal > $config['ar_aging']['threshold']) {
                $alerts[] = [
                    'type' => 'ar_aging',
                    'severity' => 'medium',
                    'message' => "Total AR is \${$arTotal}, exceeds threshold of \${$config['ar_aging']['threshold']}",
                    'value' => $arTotal,
                    'threshold' => $config['ar_aging']['threshold'],
                ];
            }
        }

        // Check MRR drop
        if ($config['mrr_drop']['enabled']) {
            $mrrChange = $this->calculateMrrChange();
            if ($mrrChange < -$config['mrr_drop']['threshold']) {
                $alerts[] = [
                    'type' => 'mrr_drop',
                    'severity' => 'high',
                    'message' => "MRR dropped by " . abs($mrrChange) . "%, exceeds threshold of {$config['mrr_drop']['threshold']}%",
                    'value' => $mrrChange,
                    'threshold' => -$config['mrr_drop']['threshold'],
                ];
            }
        }

        // Check low margin clients
        if ($config['low_margin']['enabled']) {
            $lowMarginClients = $this->getLowMarginClients($config['low_margin']['threshold']);
            if ($lowMarginClients->count() > 0) {
                $alerts[] = [
                    'type' => 'low_margin',
                    'severity' => 'medium',
                    'message' => "{$lowMarginClients->count()} clients below {$config['low_margin']['threshold']}% margin",
                    'value' => $lowMarginClients->count(),
                    'threshold' => $config['low_margin']['threshold'],
                    'companies' => $lowMarginClients->pluck('name', 'id')->toArray(),
                ];
            }
        }

        // Log alerts
        if (count($alerts) > 0) {
            Log::warning('Billing alerts triggered', [
                'count' => count($alerts),
                'alerts' => $alerts,
            ]);
        }

        return $alerts;
    }

    /**
     * Get threshold configuration.
     *
     * @return array<string, array{enabled: bool, threshold: float}>
     */
    public function getThresholdConfig(): array
    {
        return Cache::remember('billing_alert_thresholds', 3600, function () {
            return [
                'churn_rate' => [
                    'enabled' => true,
                    'threshold' => 5.0, // percentage
                ],
                'ar_aging' => [
                    'enabled' => true,
                    'threshold' => 50000.0, // dollars
                ],
                'mrr_drop' => [
                    'enabled' => true,
                    'threshold' => 10.0, // percentage
                ],
                'low_margin' => [
                    'enabled' => true,
                    'threshold' => 20.0, // percentage
                ],
            ];
        });
    }

    /**
     * Set a threshold value.
     */
    public function setThreshold(string $metric, float $value): void
    {
        $config = $this->getThresholdConfig();
        
        if (!isset($config[$metric])) {
            throw new \InvalidArgumentException("Invalid metric: {$metric}");
        }

        $config[$metric]['threshold'] = $value;
        Cache::put('billing_alert_thresholds', $config, 3600);

        Log::info('Alert threshold updated', [
            'metric' => $metric,
            'value' => $value,
        ]);
    }

    /**
     * Send an alert through configured channels.
     *
     * @param string $alertType
     * @param array<string, mixed> $data
     */
    public function sendAlert(string $alertType, array $data): void
    {
        // In real implementation, send via email.
        Log::alert('Billing alert', [
            'type' => $alertType,
            'data' => $data,
        ]);

        // Dispatch notification
        // Notification::route('slack', config('billing.alert_slack_webhook'))
        //     ->notify(new BillingAlert($alertType, $data));
    }

    /**
     * Calculate current churn rate.
     */
    protected function calculateChurnRate(): float
    {
        $startOfMonth = now()->startOfMonth();
        $activeAtStart = Subscription::where('is_active', true)
            ->where('created_at', '<', $startOfMonth)
            ->count();

        if ($activeAtStart === 0) {
            return 0.0;
        }

        $churned = Subscription::where('renewal_status', 'churned')
            ->where('updated_at', '>=', $startOfMonth)
            ->count();

        return ($churned / $activeAtStart) * 100;
    }

    /**
     * Calculate total accounts receivable.
     */
    protected function calculateArTotal(): float
    {
        return (float) Invoice::whereIn('status', ['sent', 'overdue'])
            ->sum('total');
    }

    /**
     * Calculate MRR change month-over-month.
     */
    protected function calculateMrrChange(): float
    {
        $currentMrr = (float) Subscription::where('is_active', true)
            ->where('billing_frequency', 'monthly')
            ->sum('effective_price');

        // Get last month's MRR from cache or calculate
        $lastMonthMrr = (float) Cache::get('billing_mrr_last_month', $currentMrr);

        if ($lastMonthMrr == 0) {
            return 0.0;
        }

        $change = (($currentMrr - $lastMonthMrr) / $lastMonthMrr) * 100;

        // Cache current MRR for next month's comparison
        Cache::put('billing_mrr_last_month', $currentMrr, now()->addMonth());

        return $change;
    }

    /**
     * Get companies with margins below threshold.
     *
     * @param float $threshold
     * @return \Illuminate\Database\Eloquent\Collection<int, Company>
     */
    protected function getLowMarginClients(float $threshold)
    {
        return Company::where('is_active', true)
            ->where('margin_floor_percent', '<', $threshold)
            ->orWhereNull('margin_floor_percent')
            ->get();
    }

    /**
     * Enable or disable alert type.
     */
    public function toggleAlert(string $metric, bool $enabled): void
    {
        $config = $this->getThresholdConfig();
        
        if (!isset($config[$metric])) {
            throw new \InvalidArgumentException("Invalid metric: {$metric}");
        }

        $config[$metric]['enabled'] = $enabled;
        Cache::put('billing_alert_thresholds', $config, 3600);

        Log::info('Alert toggled', [
            'metric' => $metric,
            'enabled' => $enabled,
        ]);
    }
}
