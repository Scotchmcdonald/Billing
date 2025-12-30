<?php

namespace Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Gate;
use Laravel\Cashier\Cashier;

class BillingServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Billing';
    protected $moduleNameLower = 'billing';

    public function boot()
    {
        Cashier::useSubscriptionModel(\Modules\Billing\Models\Subscription::class);
        Cashier::useSubscriptionItemModel(\Modules\Billing\Models\SubscriptionItem::class);

        $this->registerRoutes();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        Gate::define('finance.admin', function ($user) {
            return $user->isAdmin();
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Billing\Console\SyncCrmCompaniesCommand::class,
                \Modules\Billing\Console\GenerateMonthlyInvoices::class,
                \Modules\Billing\Console\CleanDemoDataCommand::class,
            ]);
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);
            $schedule->command('billing:generate-invoices')->monthlyOn(1, '00:00');

            // Register Navigation Items
            if (class_exists(\App\Services\Navigation\NavigationService::class)) {
                $nav = $this->app->make(\App\Services\Navigation\NavigationService::class);
                $nav->registerDropdown('Finance', [
                    [
                        'label' => 'Dashboard',
                        'route' => 'billing.finance.dashboard',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Pre-Flight',
                        'route' => 'billing.finance.pre-flight',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Usage Review',
                        'route' => 'billing.finance.usage-review',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Quotes',
                        'route' => 'billing.finance.quotes.index',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Invoices',
                        'route' => 'billing.finance.invoices',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Payments',
                        'route' => 'billing.finance.payments',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Reports',
                        'route' => 'billing.finance.reports-hub',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Settings',
                        'route' => 'billing.finance.settings-hub',
                        'permission' => 'finance.admin',
                    ],
                    [
                        'label' => 'Client Portal',
                        'route' => 'billing.portal.entry',
                    ],
                ]);
            }
        });

        \Modules\Billing\Models\Subscription::observe(\Modules\Billing\Observers\SubscriptionObserver::class);

        \Illuminate\Support\Facades\Event::listen(
            \Modules\Billing\Events\QuoteApproved::class,
            \Modules\Billing\Listeners\ProvisionQuote::class
        );

        // View Composer for Finance Navigation
        \Illuminate\Support\Facades\View::composer('billing::finance._partials.nav', function ($view) {
            try {
                // Usage Reviews
                $pendingUsage = \Modules\Billing\Models\UsageChange::where('status', 'pending')->count();
                
                // Pre-flight (Placeholder matching controller logic)
                $pendingPreFlight = 12; 
                
                // Overdue Invoices
                $overdueInvoices = \Modules\Billing\Models\Invoice::where('status', 'overdue')->count();
                
                // Failed Payments
                $failedPayments = \Modules\Billing\Models\Payment::where('status', 'failed')->where('created_at', '>=', now()->subDays(30))->count();

                $view->with('navCounts', [
                    'usage_review' => $pendingUsage,
                    'pre_flight' => $pendingPreFlight,
                    'invoices' => $overdueInvoices,
                    'payments' => $failedPayments
                ]);
            } catch (\Exception $e) {
                // Fail gracefully if tables don't exist yet
                $view->with('navCounts', []);
            }
        });
    }

    public function register()
    {
        // Register BillingConfigServiceProvider to load settings from database
        $this->app->register(BillingConfigServiceProvider::class);
    }

    protected function registerRoutes()
    {
        Route::group([
            'middleware' => 'web',
            'namespace' => 'Modules\Billing\Http\Controllers',
            'prefix' => 'billing',
        ], function ($router) {
            require module_path($this->moduleName, 'Routes/web.php');
        });

        Route::group([
            'middleware' => 'api',
            'namespace' => 'Modules\Billing\Http\Controllers',
            'prefix' => 'api/v1/finance',
        ], function ($router) {
            require module_path($this->moduleName, 'Routes/api.php');
        });
    }

    protected function registerViews()
    {
        $viewPath = resource_path('views/modules/' . $this->moduleNameLower);

        $sourcePath = module_path($this->moduleName, 'Resources/views');

        $this->publishes([
            $sourcePath => $viewPath
        ], ['views', $this->moduleNameLower . '-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower);
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (\Config::get('view.paths') as $path) {
            if (is_dir($path . '/modules/' . $this->moduleNameLower)) {
                $paths[] = $path . '/modules/' . $this->moduleNameLower;
            }
        }
        return $paths;
    }
}
