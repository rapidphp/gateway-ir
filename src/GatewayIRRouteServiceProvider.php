<?php

namespace Rapid\GatewayIR;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class GatewayIRRouteServiceProvider extends RouteServiceProvider
{

    public function map()
    {
        if (config('gateway-ir.routes.enabled')) {
            require __DIR__ . '/../routes/gateway.php';
        }
    }

}