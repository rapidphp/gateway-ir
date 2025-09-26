<?php

namespace Rapid\GatewayIR\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Rapid\GatewayIR\Services\GatewayService;

class GatewayController
{
    public function __construct(protected GatewayService $service)
    {
    }

    public function redirect(Request $request)
    {
        $to = $request->string('to')->toString();
        $method = $request->string('method')->toString();
        $data = json_decode($request->string('data')->toString() ?: "null", true);

        if ($method == 'GET' && !$data) {
            return response()->redirectTo($to);
        }

        return view(config('gateway-ir.views.redirect'), [
            'action' => $to,
            'method' => $method,
            'data' => $data ?? [],
        ]);
    }

    public function accept(string $orderId, Request $request)
    {
        return $this->service->verify($orderId, $request);
    }
}
