<?php

namespace DevTyping\Gateway;

use DevTyping\Gateway\Http\Middleware\RouteMiddleware;
use Illuminate\Support\ServiceProvider;

// Middleware
use DevTyping\Gateway\Http\Middleware\ServiceMiddleware;

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
        // Routers
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        if ($this->app->runningInConsole()) {
            // Publish config
            $this->publishes([
                __DIR__ . '/../config/gateway.php' => config_path('gateway.php'),
            ], 'gateway-config');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/gateway.php', 'gateway');

        // Middleware
        $router = $this->app['router'];

        $router->aliasMiddleware('gateway.service.middleware', ServiceMiddleware::class);
        $router->aliasMiddleware('gateway.route.middleware', RouteMiddleware::class);
    }
}
