<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Configuration
    |--------------------------------------------------------------------------
    | This section defines the configuration settings for the payment gateway,
    | including route settings, database model and table, default gateway, and
    | other portal-specific settings.
    */

    'routes' => [
        'prefix' => '/payment',
        'name' => 'gateway.result',
        'throttle' => '4,1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Additional Payment Gateway Settings
    |--------------------------------------------------------------------------
    | This section can be used to define any additional settings or configurations
    | that are specific to the payment gateway implementation.
    |
    */

    'database' => [
        'model' => \Rapid\GatewayIR\Models\Transaction::class,
        'table' => 'gateway_transactions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    | This option allows you to enable or disable sandbox mode for the payment
    | gateway. When enabled, the gateway will use test credentials and endpoints.
    */

    'sandbox' => env('GATEWAY_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    | This option controls the default payment gateway that will be used for
    | processing payments. You may set this to any of the gateways defined
    | in the `portals` array below.
    */

    'default' => env('GATEWAY_DEFAULT', 'zarinpal'),

    /*
    |--------------------------------------------------------------------------
    | Secondary Payment Gateway
    |--------------------------------------------------------------------------
    | This option controls the secondary payment gateway that will be used for
    | processing payments. You may set this to any of the gateways defined
    | in the `portals` array below.
    */

    'secondary' => env('GATEWAY_SECONDARY', 'nextpay'),

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Portals
    |--------------------------------------------------------------------------
    | This section defines the available payment gateway portals that can be
    | used for processing payments. Each portal should be configured with
    | the necessary settings and credentials.
    */

    'portals' => [

        'zarinpal' => [
            'driver' => \Rapid\GatewayIR\Portals\ZarinPal::class,
            'key' => env('GATEWAY_ZARINPAL_KEY', '9f82b83f-7893-4b2e-93b8-9a096ceb3428'),
            'sandbox' => env('GATEWAY_ZARINPAL_SANDBOX'),
        ],

        'nextpay' => [
            'driver' => \Rapid\GatewayIR\Portals\NextPay::class,
            'key' => env('GATEWAY_NEXTPAY_KEY', ''),
            'sandbox' => env('GATEWAY_NEXTPAY_SANDBOX'),
        ],

    ],

];
