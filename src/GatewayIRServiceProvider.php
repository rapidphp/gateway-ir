<?php

namespace Rapid\GatewayIR;

use Illuminate\Support\ServiceProvider;

class GatewayIRServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerConfig();
        $this->registerRoutes();
    }

    public function registerConfig()
    {
        $config = __DIR__ . '/../config/gateway-ir.php';

        $this->publishes([$config => base_path('config/gateway-ir.php')], ['gateway-ir']);

        $this->mergeConfigFrom($config, 'gateway-ir');
    }

}