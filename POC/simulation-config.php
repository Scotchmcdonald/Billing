<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Simulation Feature Toggle
    |--------------------------------------------------------------------------
    |
    | Enable or disable the role simulation feature globally.
    |
    */

    'enabled' => env('SIMULATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Allowed Roles
    |--------------------------------------------------------------------------
    |
    | User roles that can initiate simulation sessions.
    |
    */

    'allowed_roles' => ['executive', 'admin', 'architect'],

    /*
    |--------------------------------------------------------------------------
    | Simulatable Roles
    |--------------------------------------------------------------------------
    |
    | Roles that can be simulated by authorized users.
    |
    */

    'simulatable_roles' => [
        'technician',
        'client_admin',
        'client_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    | Maximum duration (in hours) for simulation sessions.
    | Sessions exceeding this duration are automatically terminated.
    |
    */

    'max_duration_hours' => 4,

    /*
    |--------------------------------------------------------------------------
    | Read-Only Mode
    |--------------------------------------------------------------------------
    |
    | When true, POST/PUT/PATCH/DELETE requests are blocked during simulation
    | unless explicit override flag is present.
    |
    */

    'read_only' => true,

    /*
    |--------------------------------------------------------------------------
    | Developer Overlay
    |--------------------------------------------------------------------------
    |
    | Configuration for the permission debugging overlay.
    |
    */

    'developer_overlay' => [
        'enabled' => env('APP_DEBUG', false),
        'keyboard_shortcut' => 'Ctrl+Shift+D',
        'max_log_entries' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    |
    | Control what simulation events are logged.
    |
    */

    'audit' => [
        'log_start' => true,
        'log_switches' => true,
        'log_overrides' => true,
        'log_termination' => true,
        'channel' => 'simulation', // Separate log channel
    ],

];
