<?php

namespace Rapid\GatewayIR\Http\Controllers;

use Illuminate\Http\Request;
use Rapid\GatewayIR\Http\Services\GatewayService;

class GatewayController
{

    public function accept(string $orderId, Request $request)
    {
        app(GatewayService::class)->verify($orderId, $request);
    }

}