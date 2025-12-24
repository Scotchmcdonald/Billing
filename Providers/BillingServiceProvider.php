<?php

namespace Modules\Billing\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class BillingServiceProvider extends ServiceProvider
{
    protected $moduleName = 'Billing';
    protected $moduleNameLower = 'billing';

    public function boot()
    {
        $this->registerRoutes();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->moduleName, 'Database/Migrations'));

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Modules\Billing\Console\SyncCrmCompaniesCommand::class,
            ]);
        }
    }

    public function register()
    {
        //
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
