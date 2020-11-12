<?php

namespace DevTyping\Gateway\Http\Helpers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RequestBuilder
{
    /**
     * @param Request $request
     * @return string[]
     */
    public static function buildHeader(Request $request)
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
    public static function buildUrl(string $scheme, string $host, int $port = 80)
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
    public static function buildRequestUrl(string $url, string $endpoint, array $query): string
    {
        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        if (count($query) > 0) {
            $query = '?' . Arr::query($query);
        } else {
            $query = '';
        }

        return $url . $endpoint . urldecode($query);
    }
}
