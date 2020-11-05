<?php

namespace DevTyping\Gateway\Http\Repository;

use Exception;
use Illuminate\Support\Str;

/**
 * Class GatewayRepository
 * @package DevTyping\Gateway\Http\Repository
 */
class GatewayRepository
{
    private $config = [];

    public function __construct(?array $config = null)
    {
        if ($config) {
            $this->config = $config;
        } else {
            $this->config = config('gateway');
        }
    }


    /**
     * @param string $service
     * @return mixed
     * @throws Exception
     */
    public function getService(string $service)
    {
        if (!array_key_exists('services', $this->config)) {
            throw new Exception('Services Config is not defined.');
        }

        if (!array_key_exists($service, $this->config['services'])) {
            throw new Exception('Service ' . $service . ' not found.');
        }

        return $this->config['services'][$service];
    }


    /**
     * @param string $service
     * @return array[]
     * @throws Exception
     */
    public function getRoles(string $service)
    {
        $serviceModel = $this->getService($service);
        return $serviceModel['roles'];
    }


    /**
     * @param string $service
     * @param string $endpoint
     * @return array
     * @throws Exception
     */
    public function getRouteRoles(string $service, string $endpoint)
    {
        $serviceModel = $this->getService($service);

        if (!array_key_exists('routes', $serviceModel)) {
            throw new Exception('Routes middleware is not defined.');
        }

        $arrayRoles = [];

        if (count($serviceModel['routes']) > 0) {
            $routes = $serviceModel['routes'];
            foreach ($routes as $route) {
                if (array_key_exists('path', $route)) {
                    if (Str::startsWith($endpoint, $route['path'])) {
                        if (array_key_exists('roles', $route)) {
                            array_push($arrayRoles, $route['roles']);
                        }
                    }
                }
            }
        }

        return $arrayRoles;
    }
}
