<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Quote Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for quote pricing and approval workflows
    |
    */

    'pricing_tiers' => [
        'standard' => [
            'label' => 'Standard',
            'description' => 'Standard pricing for regular customers',
        ],
        'non_profit' => [
            'label' => 'Non-Profit',
            'description' => 'Discounted pricing for non-profit organizations',
        ],
        'consumer' => [
            'label' => 'Consumer',
            'description' => 'Consumer pricing tier',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Approval Threshold
    |--------------------------------------------------------------------------
    |
    | The default percentage variance from standard price that triggers
    | an approval requirement. Can be overridden per quote.
    |
    */
    'default_approval_threshold' => env('QUOTE_APPROVAL_THRESHOLD', 15.00),

    /*
    |--------------------------------------------------------------------------
    | Quote Validity Period
    |--------------------------------------------------------------------------
    |
    | Default number of days a quote remains valid
    |
    */
    'default_validity_days' => env('QUOTE_VALIDITY_DAYS', 30),

];
