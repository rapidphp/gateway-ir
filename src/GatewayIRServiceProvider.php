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

    /**
     * Register the payment gateways from configuration.
     *
     * @return void
     */
    public function registerGateways(): void
    {
        foreach (config('gateway-ir.portals', []) as $idName => $gateway) {
            [
                'driver' => $driver,
                'sandbox' => $sandbox,
                'key' => $key,
            ] = $gateway;

            Payment::define($idName, function () use ($driver, $key, $sandbox) {
                return new $driver($key, $sandbox);
            });
        }

        if ($default = config('gateway-ir.default')) {
            Payment::setPrimary($default);
        }

        if ($default = config('gateway-ir.secondary')) {
            Payment::setSecondary($default);
        }
    }

}
