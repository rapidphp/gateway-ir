<?php

namespace Rapid\GatewayIR;

use Illuminate\Support\ServiceProvider;

class GatewayIRServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerConfig();
        $this->registerLang();
        $this->registerMigrations();
    }

    public function registerConfig()
    {
        $path = __DIR__ . '/../config/gateway-ir.php';

        $this->publishes([$path => base_path('config/gateway-ir.php')], ['gateway-ir']);

        $this->mergeConfigFrom($path, 'gateway-ir');
    }

    public function registerLang()
    {
        $path = __DIR__ . '/../lang';

        $this->publishes([$path => base_path('lang/gateway-ir')], ['gateway-ir']);

        $this->loadTranslationsFrom($path, 'gateway-ir');
    }

    public function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

}