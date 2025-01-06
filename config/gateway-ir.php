<?php

/**
 * Configuration file for the Payment Gateway.
 *
 * This configuration file defines the routing and database table settings
 * for the payment gateway functionality. It allows customization of the
 * route prefix, route name, throttling settings, and the database model
 * and table used for storing transaction records.
 *
 * @return array The configuration settings for the payment gateway.
 *
 * @config array $routes Configuration for routing settings.
 * @config string $routes['prefix'] The URI prefix for the payment gateway routes.
 *                                   Default is '/payment'.
 * @config string $routes['name'] The name of the route for the payment gateway result.
 *                                 This is used for generating URLs and route references.
 *                                 Default is 'gateway.result'.
 * @config string $routes['throttle'] Throttling settings for the payment gateway routes.
 *                                      This defines the maximum number of requests allowed
 *                                      within a specified time frame. Format is 'max_requests,time_period'.
 *                                      Default is '4,1' (4 requests per 1 minute).
 *
 * @config array $database Configuration for the database table settings.
 * @config string $database['transaction_model'] The fully qualified class name of the model used for
 *                                 transaction records. This model should extend the base
 *                                 Eloquent model and define the necessary relationships and
 *                                 attributes. Default is \Rapid\GatewayIR\Models\Transaction::class.
 * @config string $database['transaction_table'] The name of the database table used to store transaction
 *                                 records. This should match the actual table name in the
 *                                 database. Default is 'gateway_transactions'.
 */
return [

    'routes' => [
        'prefix' => '/payment',
        'name' => 'gateway.result',
        'throttle' => '4,1',
    ],

    'database' => [
        'model' => \Rapid\GatewayIR\Models\Transaction::class,
        'tabel' => 'gateway_transactions',
    ],

];
