<?php

namespace DevTyping\Gateway\Http\Middleware;

// In-app
use DevTyping\Gateway\Http\Repository\GatewayRepository;

// Vendors
use Closure;

// Exceptions
use Exception;

/**
 * Class RouteMiddleware
 * @package DevTyping\Gateway\Http\Middleware
 */
class RouteMiddleware extends BaseMiddleware
{

    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        try {
            $routeRoles = (new GatewayRepository())->getRouteRoles($request->route('service'), $request->route('endpoint'));
            if (count($routeRoles) > 0) {
                $this->checkByRole($routeRoles);
            }
            return $next($request);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage(), $e->getCode());
        }
    }
}
