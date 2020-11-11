<?php

namespace DevTyping\Gateway\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use DevTyping\Gateway\Http\Services\RestClient;

// Exceptions
use Exception;

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
            $headers = $this->buildHeader($request);

            // Build request url
            $requestUrl = $this->buildRequestUrl(
                $this->buildUrl(
                    $this->service['protocol'],
                    $this->service['host'],
                    $this->service['port']
                ),
                $endpoint,
                $request->query()
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


    /**
     * @param Request $request
     * @return string[]
     */
    private function buildHeader(Request $request)
    {
        $disallow = ['host'];
        $headers = $request->header();
        $combined = [];

        foreach ($headers as $key => $val) {
            if ($val[0] && $val[0] !== '' && !in_array($key, $disallow)) {
                $combined[$key] = $val[0];
            }
        }

        return $combined;
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
     * @param array $query
     * @return string
     */
    public function buildRequestUrl(string $url, string $endpoint, array $query): string
    {
        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        if (count($query) > 0) {
            $query = '?' . http_build_query($query);
        } else {
            $query = '';
        }

        return $url . $endpoint . $query;
    }


    /**
     * Curl
     *
     * @param string $url
     * @param string $httpMethod
     * @param int $timeout
     * @param array $header
     * @param array $payload
     * @return Response
     * @throws Exception
     * @deprecated 1.0.2
     */
    private function curl(
        string $url,
        string $httpMethod,
        int $timeout,
        array $header = [],
        array $payload = []
    ): Response
    {
        // Init curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

        // Custom request payload
        $customPayload = (isset($header['content-type']) && $header['content-type'] === 'application/json') ? json_encode($payload) : $payload;

        if ($httpMethod === 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        } elseif ($httpMethod === "PUT") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        } elseif ($httpMethod === "PATCH") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
            curl_setopt($curl, CURLOPT_POSTFIELDS, $customPayload);
        } elseif ($httpMethod === "DELETE") {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        }

        // Exec
        $result = curl_exec($curl);

        // Handle if have any errors when curl running
        if (curl_errno($curl)) {
            return new Response([
                'error' => [
                    'type' => 'AGWException/curl',
                    'code' => curl_errno($curl),
                    'message' => 'Request timeout',
                    'trace' => [
                        'url' => $url,
                        'http_method' => $httpMethod
                    ]
                ]
            ], Response::HTTP_REQUEST_TIMEOUT);
        }

        if ($result) {
            $result = json_decode($result, true);
        } else {
            $result = curl_error($curl);
        }

        $curlInfo = curl_getinfo($curl);
        $httpStatusCode = $curlInfo['http_code'];

        curl_close($curl);

        if (!$result && $httpStatusCode >= 400 && $httpStatusCode < 600) {
            throw new Exception('Error ' . $httpStatusCode, $httpStatusCode);
        }

        return new Response($result, $httpStatusCode);
    }
}
