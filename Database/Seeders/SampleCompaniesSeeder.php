<?php

namespace Modules\Billing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Models\Company;

class SampleCompaniesSeeder extends Seeder
{
    public function run()
    {
        $companies = [
            [
                'name' => 'Acme Corp (Standard)',
                'pricing_tier' => 'standard',
                'margin_floor_percent' => 20.00,
            ],
            [
                'name' => 'Charity Org (Non-Profit)',
                'pricing_tier' => 'non_profit',
                'margin_floor_percent' => 10.00,
            ],
            [
                'name' => 'John Doe (Consumer)',
                'pricing_tier' => 'consumer',
                'margin_floor_percent' => 30.00,
            ],
            [
                'name' => 'Tech Startups Inc (Standard)',
                'pricing_tier' => 'standard',
                'margin_floor_percent' => 25.00,
            ],
            [
                'name' => 'Global NGO (Non-Profit)',
                'pricing_tier' => 'non_profit',
                'margin_floor_percent' => 15.00,
            ],
        ];

        foreach ($companies as $data) {
            Company::firstOrCreate(
                ['name' => $data['name']],
                [
                    'pricing_tier' => $data['pricing_tier'],
                    'margin_floor_percent' => $data['margin_floor_percent'],
                    'email' => strtolower(str_replace([' ', '(', ')'], ['', '', ''], $data['name'])) . '@example.com',
                ]
            );
        }
    }
}
