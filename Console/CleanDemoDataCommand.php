<?php

namespace Modules\Billing\Console;

use Illuminate\Console\Command;
use Modules\Billing\Database\Seeders\BillingDemoSeeder;

class CleanDemoDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:clean-demo 
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all demo billing data (companies, invoices, products)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete all demo billing data. Continue?', false)) {
                $this->info('Cancelled.');
                return 0;
            }
        }

        $seeder = new BillingDemoSeeder();
        $seeder->setCommand($this);
        $seeder->cleanup();

        return 0;
    }
}
