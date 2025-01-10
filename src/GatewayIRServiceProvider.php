<?php

namespace Rapid\GatewayIR;

use Illuminate\Support\ServiceProvider;

class GatewayIRServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->registerConfig();
        $this->registerLang();
        $this->registerMigrations();
    }

    /**
     * Register the configuration file.
     *
     * @return void
     */
    public function registerConfig(): void
    {
        $path = __DIR__ . '/../config/gateway-ir.php';

        $this->publishes([$path => base_path('config/gateway-ir.php')], ['gateway-ir']);

        $this->mergeConfigFrom($path, 'gateway-ir');
    }

    /**
     * Register the language translations.
     *
     * @return void
     */
    public function registerLang(): void
    {
        $path = __DIR__ . '/../lang';

        $this->publishes([$path => base_path('lang/gateway-ir')], ['gateway-ir']);

        $this->loadTranslationsFrom($path, 'gateway-ir');
    }

    /**
     * Register the database migrations.
     *
     * @return void
     */
    public function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

}
