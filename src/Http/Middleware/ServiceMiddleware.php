<?php

namespace DevTyping\Gateway\Http\Middleware;

// In-app
use DevTyping\Gateway\Http\Repository\GatewayRepository;

// Vendors
use Closure;

// Exceptions
use Exception;

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
        $serviceRoles = (new GatewayRepository())->getRoles($request->route('service'));
        $this->checkByRole($serviceRoles);
        return $next($request);
    }
}
