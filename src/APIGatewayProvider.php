<?php

namespace DevTyping\Gateway;

use Illuminate\Support\ServiceProvider;

/**
 * Class APIGatewayProvider
 * @package DevTyping\Gateway
 */
class APIGatewayProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/gateway.php' => config_path('gateway.php'),
        ], 'gateway-config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gateway.php', 'gateway');
    }
}
