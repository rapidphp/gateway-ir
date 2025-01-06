<?php

use Illuminate\Support\Facades\Route;
use Rapid\GatewayIR\Http\Controllers\GatewayController;

/**
 * Define routes.
 *
 * This routing configuration sets up the necessary endpoints for the payment
 * gateway functionality. It uses a prefix defined in the configuration file
 * to group related routes under a common URI. The following route is defined:
 *
 * - GET /{order_id}: This route accepts an order ID as a parameter and
 *   invokes the 'accept' method of the GatewayController. It is protected
 *   by a throttle middleware to limit the number of requests that can be
 *   made to this endpoint within a specified time frame, as defined in the
 *   configuration. The route is also named according to the configuration
 *   for easy reference in the application.
 *
 * The route configuration is as follows:
 * - Prefix: The base URI for the payment gateway routes, configured in
 *   'gateway-ir.routes.prefix'.
 * - Throttle: The rate limit for this route, configured in
 *   'gateway-ir.routes.throttle'.
 * - Route Name: The name of the route for generating URLs and route
 *   references, configured in 'gateway-ir.routes.name'.
 */
Route::prefix(config('gateway-ir.routes.prefix'))
    ->group(function () {

        Route::get('/{order_id}', [GatewayController::class, 'accept'])
            ->middleware('throttle:' . config('gateway-ir.routes.throttle'))
            ->name(config('gateway-ir.routes.name'));

    });
