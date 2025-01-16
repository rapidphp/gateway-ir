<?php

namespace Rapid\GatewayIR\Http\Controllers;

use Illuminate\Http\Request;
use Rapid\GatewayIR\Services\GatewayService;

class GatewayController
{

    public function __construct(protected GatewayService $service)
    {
    }

    public function accept(string $orderId, Request $request)
    {
        return $this->service->verify($orderId, $request);
    }

}
