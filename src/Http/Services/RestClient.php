<?php

namespace DevTyping\Gateway\Http\Services;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

use GuzzleHttp\Psr7\MultipartStream;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Class RestClient
 * @package DevTyping\Gateway\Http\Services
 */
class RestClient
{
    private $client = null;
    private $requestOptions = [];
    private $isFakeMethod = false;

    /**
     * RestClient constructor.
     * @param int $timeout
     */
    public function __construct(
        int $timeout
    )
    {
        if (!$this->client) {
            $this->client = new Client([
                'timeout' => $timeout,
            ]);
        }
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->requestOptions['headers'];
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        $this->requestOptions['headers'] = $headers;
    }

    /**
     * @param $contentType
     * @param $request
     * @return $this
     */
    public function setBody($contentType, Request $request)
    {
        $payload = [];
        $fields = $request->all();

        if ($contentType === null && strpos($request->header('content-type'), 'multipart/form-data;') === 0) {
            foreach ($fields as $key => $val) {
                if ($request->hasFile($key)) {
                    $payload[] = [
                        'Content-type' => 'multipart/form-data',
                        'name' => $key,
                        'contents' => fopen($val->getRealPath(), 'r'),
                        'filename' => $val->getClientOriginalName()
                    ];
                } else {
                    $payload[] = [
                        'name' => $key,
                        'contents' => $val
                    ];
                }
            }

            // Tricky!
            if (isset($this->requestOptions['headers']['content-type'])) {
                unset($this->requestOptions['headers']['content-type']);
            }

            $this->isFakeMethod = true;
            $this->requestOptions['multipart'] = $payload;
        } else if ($contentType === 'json') {
            foreach ($fields as $key => $param) {
                $payload[$key] = $param;
            }
            $this->requestOptions['json'] = $payload;
        } else if ($contentType === 'form') {
            $this->requestOptions['form_params'] = $fields;
        }

        return $this;
    }


    /**
     * @param string $httpMethod
     * @param $url
     * @return Response
     * @throws Exception
     */
    public function request(string $httpMethod, $url)
    {
        try {
            if ($this->isFakeMethod) {
                $httpMethod = 'POST';
            }
            $response = $this->client->request($httpMethod, $url, $this->requestOptions);
            $responseBody = $response->getBody()->getContents();
            return new Response(json_decode($responseBody, 1), $response->getStatusCode());
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                $message = $e->getResponse()->getBody();
                return new Response(json_decode($message, 1), $e->getCode());
            } else {
                throw new Exception('An error occurred');
            }
        } catch (GuzzleException $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }
}
