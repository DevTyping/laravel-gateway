<?php

namespace DevTyping\Gateway\Http\Helpers;

use Exception;
use Illuminate\Support\Facades\Config;

/**
 * Class APIGateway
 * @package DevTyping\Gateway\Http\Helpers
 */
class APIGateway
{
    /**
     * @param String $path
     * @return String
     */
    public static function service(string $path)
    {
        $service = explode("/", $path);

        if ($service[0] && $service[0] === Config::get('gateway.gateway.prefix') && $service[1] && $service[1] !== "") {
            return $service[1];
        }

        return null;
    }

    /**
     * @param String $string
     * @return null
     * @throws Exception
     */
    public static function getPath(string $string)
    {
        $path = explode("/", $string);

        if ($path[0] && $path[0] === Config::get('gateway.gateway.prefix') && (!$path[1] || $path[1] === "")) {
            throw new Exception('Service not found');
        }

        $destPath = array_slice($path, 2);
        return implode("/", $destPath);
    }
}
