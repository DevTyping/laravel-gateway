<?php

namespace DevTyping\Gateway\Http\Middleware;

// In-app
use DevTyping\Gateway\Http\Repository\GatewayRepository;

// Vendors
use Closure;

// Exceptions
use Exception;

/**
 * Class ServiceMiddleware
 * @package DevTyping\Gateway\Http\Middleware
 */
class ServiceMiddleware extends BaseMiddleware
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
            $serviceRoles = (new GatewayRepository())->getRoles($request->route('service'));
            if (count($serviceRoles) > 0) {
                $this->checkByRole($serviceRoles);
            }
            return $next($request);
        } catch (Exception $e) {
            return $this->responseError($e->getMessage(), $e->getCode());
        }
    }
}
