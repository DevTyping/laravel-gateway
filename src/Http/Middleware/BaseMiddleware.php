<?php

namespace DevTyping\Gateway\Http\Middleware;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Exceptions\UnauthorizedException;

/**
 * Class BaseMiddleware
 * @package DevTyping\Gateway\Http\Middleware
 */
class BaseMiddleware
{
    /**
     * @param array|string $roleString
     */
    public function checkByRole($roleString)
    {
        $roles = is_array($roleString)
            ? $roleString
            : explode('|', $roleString);

        if (!Auth::guard()->user()->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }
    }

    /**
     * @param string $message
     * @param int $httpStatusCode
     * @return JsonResponse
     */
    public function responseError(string $message = "", int $httpStatusCode = 500)
    {
        return response()->json([
            "error" => [
                "type" => "AGWException",
                "message" => $message
            ],
        ], $httpStatusCode ? $httpStatusCode : 500);
    }
}
