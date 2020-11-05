<?php
$gatewayMiddleware = Config::get('gateway.gateway.middleware');

// Vendor
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

// Controller
use DevTyping\Gateway\Http\Controllers\GatewayController;

Route::group([
    'prefix' => Config::get('gateway.gateway.prefix') . '/{service}/{endpoint}',
    'where' => [
        'service' => '[a-zA-Z]+',
        'endpoint' => '.*'
    ],
    'middleware' => array_merge($gatewayMiddleware, ['gateway.service.middleware', 'gateway.route.middleware'])
], function () {
    Route::get('', [GatewayController::class, 'gateway']);
    Route::post('', [GatewayController::class, 'gateway']);
    Route::put('', [GatewayController::class, 'gateway']);
    Route::patch('', [GatewayController::class, 'gateway']);
    Route::delete('', [GatewayController::class, 'gateway']);
});
