<?php

namespace Rapid\GatewayIR;

use Illuminate\Support\ServiceProvider;
use Rapid\GatewayIR\Contracts\PaymentGateway;

class GatewayIRServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->registerConfig();
        $this->registerLang();
        $this->registerMigrations();
        $this->registerGateways();
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
            $driver = $gateway['driver'];
            unset($gateway['driver']);

            Payment::define($idName, function () use ($driver, $gateway) {
                return new $driver(...$gateway);
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
