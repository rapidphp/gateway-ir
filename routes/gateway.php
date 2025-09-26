<?php

use Illuminate\Support\Facades\Route;
use Rapid\GatewayIR\Http\Controllers\GatewayController;

Route::prefix(config('gateway-ir.routes.prefix'))
    ->group(function () {
        Route::get('/{order_id}', [GatewayController::class, 'accept'])
            ->middleware('throttle:' . config('gateway-ir.routes.throttle'))
            ->name(config('gateway-ir.routes.name'));

        Route::get(config('gateway-ir.routes.redirect'), [GatewayController::class, 'redirect'])
            ->middleware('signed')
            ->name(config('gateway-ir.routes.redirect_name'));
    });
