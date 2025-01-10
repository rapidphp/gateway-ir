<?php

return [

    'routes' => [
        'prefix' => '/payment',
        'name' => 'gateway.result',
        'throttle' => '4,1',
    ],

    'database' => [
        'model' => \Rapid\GatewayIR\Models\Transaction::class,
        'table' => 'gateway_transactions',
    ],

];
