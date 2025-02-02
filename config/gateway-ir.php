<?php

use Rapid\GatewayIR\Portals;

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
    | Expiration Settings
    |--------------------------------------------------------------------------
    | This section defines the expiration settings for the payment gateway.
    |
    */

    'expire' => [
        'expire_after' => 60 * 30,
        'dont_keep' => [
            // \Rapid\GatewayIR\Enums\TransactionStatuses::Expired,
        ],
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

    'secondary' => env('GATEWAY_SECONDARY', 'idpay'),

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
            'driver' => Portals\ZarinPal::class,
            'key' => env('GATEWAY_ZARINPAL_KEY', '9f82b83f-7893-4b2e-93b8-9a096ceb3428'),
            'sandbox' => env('GATEWAY_ZARINPAL_SANDBOX', false),
        ],

        'idpay' => [
            'driver' => Portals\IDPay::class,
            'key' => env('GATEWAY_IDPAY_KEY', ''),
            'sandbox' => env('GATEWAY_IDPAY_SANDBOX', false),
        ],

        'nextpay' => [
            'driver' => Portals\NextPay::class,
            'key' => env('GATEWAY_NEXTPAY_KEY', ''),
        ],

        'internal_sandbox' => [
            'driver' => Portals\InternalSandbox::class,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Views
    |--------------------------------------------------------------------------
    | This section defines the views that will be used for different payment
    | statuses such as successful, cancelled, expired, failed, and pending.
    */

    'views' => [
        'successful' => 'payment.successful',
        'cancelled' => 'payment.cancelled',
        'expired' => 'payment.expired',
        'failed' => 'payment.failed',
        'pending' => 'payment.pending',
    ],

];
