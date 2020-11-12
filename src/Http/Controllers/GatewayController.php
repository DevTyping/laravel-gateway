<?php

namespace DevTyping\Gateway\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use DevTyping\Gateway\Http\Helpers\RequestBuilder;
use DevTyping\Gateway\Http\Services\RestClient;

// Exceptions
use Exception;
use Illuminate\Support\Arr;

/**
 * Class GatewayController
 * @package DevTyping\Gateway\Http\Controllers
 */
class GatewayController extends BaseController
{
    /**
     * Root path controller
     *
     * @param Request $request
     * @param string $endpoint
     * @return JsonResponse|Response
     * @throws Exception
     */
    public function rootPath(Request $request, string $endpoint)
    {
        return $this->gateway($request, $endpoint);
    }

    /**
     * Full path controller
     *
     * @param Request $request
     * @param string $service
     * @param string $endpoint
     * @return JsonResponse|Response
     */
    public function fullPath(Request $request, string $service, string $endpoint)
    {
        return $this->gateway($request, $endpoint, $service);
    }


    /**
     * Gateway
     *
     * @param Request $request
     * @param string $endpoint
     * @param string|null $service
     * @return JsonResponse|Response
     */
    private function gateway(Request $request, string $endpoint, string $service = null)
    {
        try {
            // Build header
            $headers = RequestBuilder::buildHeader($request);
            $defaultQueries = $this->service['defaults'] && isset($this->service['defaults']["query"]) ? $this->service['defaults']["query"] : [];

            // Build request url
            $requestUrl = RequestBuilder::buildRequestUrl(
                RequestBuilder::buildUrl(
                    $this->service['protocol'],
                    $this->service['host'],
                    $this->service['port']
                ),
                $endpoint,
                array_merge($defaultQueries, $request->query())
            );

            // Request via CURL
            $client = new RestClient(
                $this->service['connect_timeout']
            );

            $client->setHeaders($headers);
            $client->setBody($request->getContentType(), $request);

            return $client->request(
                $request->method(),
                $requestUrl
            );
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), $exception->getCode());
        }
    }
}
