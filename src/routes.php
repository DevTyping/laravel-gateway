<?php
$gatewayMiddleware = Config::get('gateway.gateway.middleware');

// Vendor
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;

// Controller
use DevTyping\Gateway\Http\Controllers\GatewayController;
use DevTyping\Gateway\Http\Controllers\ServiceController;

// Routes for root path
Route::group([
    'prefix' => Config::get('gateway.gateway.prefix') . '/{service}',
    'where' => [
        'service' => '[a-zA-Z0-9-_]+',
    ],
    'middleware' => array_merge($gatewayMiddleware, ['gateway.service.middleware'])
], function () {
    Route::get('', [GatewayController::class, 'rootPath']);
    Route::post('', [GatewayController::class, 'rootPath']);
    Route::put('', [GatewayController::class, 'rootPath']);
    Route::patch('', [GatewayController::class, 'rootPath']);
    Route::delete('', [GatewayController::class, 'rootPath']);
});

// Routes for path path
Route::group([
    'prefix' => Config::get('gateway.gateway.prefix') . '/{service}/{endpoint}',
    'where' => [
        'service' => '[a-zA-Z0-9-_]+',
        'endpoint' => '.*'
    ],
    'middleware' => array_merge($gatewayMiddleware, ['gateway.service.middleware', 'gateway.route.middleware'])
], function () {
    Route::get('', [GatewayController::class, 'fullPath']);
    Route::post('', [GatewayController::class, 'fullPath']);
    Route::put('', [GatewayController::class, 'fullPath']);
    Route::patch('', [GatewayController::class, 'fullPath']);
    Route::delete('', [GatewayController::class, 'fullPath']);
});

// Gateway Managements
Route::group([
    'prefix' => Config::get('gateway.gateway_admin.prefix'),
    'middleware' => Config::get('gateway.gateway_admin.middleware')
], function () {
    Route::apiResource('services', ServiceController::class);
});
