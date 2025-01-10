<?php

namespace Rapid\GatewayIR;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

class GatewayIRRouteServiceProvider extends RouteServiceProvider
{

    /**
     * Map the routes.
     *
     * @return void
     */
    public function map()
    {
        require __DIR__ . '/../routes/gateway.php';
    }

}