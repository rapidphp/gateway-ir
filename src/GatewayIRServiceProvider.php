<?php

namespace Rapid\GatewayIR;

use Illuminate\Support\ServiceProvider;

/**
 * Service provider for managing configuration, language, and migrations.
 *
 * This class is responsible for registering various components such as
 * configuration files, language translations, and database migrations
 * within the application. It extends the base ServiceProvider and
 * provides methods to handle the registration of these components.
 */
class GatewayIRServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider's services.
     *
     * This method is called to register the services provided by the
     * service provider. It includes the registration of configuration,
     * language translations, and migrations.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
        $this->registerLang();
        $this->registerMigrations();
    }

    /**
     * Register the configuration file.
     *
     * This method publishes the configuration file to the application's
     * config directory and merges it with the existing configuration.
     *
     * @return void
     */
    public function registerConfig()
    {
        $path = __DIR__ . '/../config/gateway-ir.php';

        $this->publishes([$path => base_path('config/gateway-ir.php')], ['gateway-ir']);

        $this->mergeConfigFrom($path, 'gateway-ir');
    }

    /**
     * Register the language translations.
     *
     * This method publishes the language files to the application's
     * language directory and loads the translations for the application.
     *
     * @return void
     */
    public function registerLang()
    {
        $path = __DIR__ . '/../lang';

        $this->publishes([$path => base_path('lang/gateway-ir')], ['gateway-ir']);

        $this->loadTranslationsFrom($path, 'gateway-ir');
    }

    /**
     * Register the database migrations.
     *
     * This method loads the database migrations from the specified
     * directory, making them available for the application.
     *
     * @return void
     */
    public function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}
