<?php

namespace DevTyping\Gateway\Http\Middleware;

use Closure;
use GuzzleHttp\Client;

// Helpers
use DevTyping\Gateway\Http\Helpers\APIGateway;

// Exceptions
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Gateway
 * @package DevTyping\Gateway\Http\Middleware
 */
class Gateway
{

    /**
     * @param $request
     * @param Closure $next
     * @return JsonResponse|mixed|ResponseInterface
     */
    public function handle($request, Closure $next)
    {
        try {
            // Gateway configs
            $exceptPaths = Config::get('gateway.except_paths');
            $serviceConfig = Config::get('gateway.services');

            // Check service is available
            $path = $request->path();
            $service = APIGateway::service($path);
            $endpoint = isset($serviceConfig[$service]) ? $serviceConfig[$service] : null;

            if (in_array($service, $exceptPaths) || $service === null || $endpoint === null) {
                return $next($request);
            }

            // If service available then try to make request
            $http = new Client();
            $requestMethod = $request->method();
            $requestProtocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, strpos($_SERVER["SERVER_PROTOCOL"], '/'))) . '://';
            $redirectUrl = $requestProtocol . $endpoint . '/' . APIGateway::getPath($path);

            // Return response from upstream
            return $response = $http->request($requestMethod, $redirectUrl);
        } catch (Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 404);
        } catch (GuzzleException $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], $e->getCode());
        }
    }
}
