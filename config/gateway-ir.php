<?php

return [

    'routes' => [
        'enabled' => true,
        'prefix' => '/payment',
        'name' => 'gateway-ir.result',
        'throttle' => '4,1',
    ],

    'table' => [
        'model' => \Rapid\GatewayIR\Models\Transaction::class,
        'table' => 'gateway_transactions',
    ],

];
