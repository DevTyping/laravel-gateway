<?php

namespace DevTyping\Gateway\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

// Exceptions
use Exception;

/**
 * Class GatewayController
 * @package DevTyping\Gateway\Http\Controllers
 */
class GatewayController extends BaseController
{
    /**
     * Gateway
     *
     * @param Request $request
     * @param string $service
     * @param string $endpoint
     * @return Response
     * @throws Exception
     */
    public function gateway(Request $request, string $service, string $endpoint): Response
    {
        $payload = [];
        foreach ($request->request->all() as $key => $param) {
            $payload[$key] = $param;
        }
        $gatewayConfigModel = $this->service;
        $requestUrl = $this->buildRequestUrl($this->buildUrl($gatewayConfigModel['scheme'], $gatewayConfigModel['host'], $gatewayConfigModel['port']), $endpoint);
        return $this->curl($requestUrl, $request->method(), $gatewayConfigModel['timeout'], [], $payload);
    }

    /**
     * Build url
     *
     * @param string $scheme
     * @param string $host
     * @param int $port
     * @return string
     */
    private function buildUrl(string $scheme, string $host, int $port = 80)
    {
        return sprintf(
            "%s://%s%s",
            $scheme,
            $host,
            $port != 80 ? ":{$port}" : ''
        );
    }

    /**
     * Build request url
     *
     * @param string $url
     * @param string $endpoint
     * @return string
     */
    public function buildRequestUrl(string $url, string $endpoint): string
    {
        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        return $url . $endpoint;
    }


    /**
     * Curl
     *
     * @param string $url
     * @param string $http_method
     * @param int $timeout
     * @param array $header
     * @param array $payload
     * @return Response
     */
    private function curl(
        string $url,
        string $http_method,
        int $timeout,
        array $header = [],
        array $payload = []
    ): Response
    {
        $curl = curl_init();

        if ($http_method === "GET") {
            $url = $url . '?' . http_build_query($payload);
        }

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        if ($http_method === 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
        } elseif ($http_method === "PUT") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
        } elseif ($http_method === "PATCH") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($payload));
        }

        $result = curl_exec($curl);

        if (curl_errno($curl)) {
            return new Response([
                'status' => 'ERROR #' . curl_errno($curl),
                'result' => [
                    'message' => 'Request timeout',
                    'url' => $url,
                    'http_method' => $http_method
                ]
            ], Response::HTTP_REQUEST_TIMEOUT);
        }

        if ($result) {
            $result = json_decode($result, true);
        } else {
            $result = curl_error($curl);
        }

        $curlInfo = curl_getinfo($curl);
        $http_status_code = $curlInfo['http_code'];

        curl_close($curl);

        return new Response($result, $http_status_code);
    }
}
