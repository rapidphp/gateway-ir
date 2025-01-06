<?php

namespace Rapid\GatewayIR;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider;

/**
 * Service provider for routing.
 *
 * This class is responsible for registering the routes
 * within the Laravel application. It extends the base RouteServiceProvider
 * and overrides the map method to load the routes defined in the specified
 * routes file.
 */
class GatewayIRRouteServiceProvider extends RouteServiceProvider
{
    /**
     * Map the routes.
     *
     * This method loads the routes defined in the 'gateway.php' file located
     * in the routes directory. It is called during
     * the application's route registration process.
     *
     * @return void
     */
    public function map()
    {
        require __DIR__ . '/../routes/gateway.php';
    }
}