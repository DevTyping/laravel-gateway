<?php

namespace DevTyping\Gateway\Http\Repository;

use DevTyping\Gateway\Http\Models\Service;
use Exception;
use Illuminate\Support\Str;

/**
 * Class GatewayRepository
 * @package DevTyping\Gateway\Http\Repository
 */
class GatewayRepository
{
    private $config = [];

    /**
     * GatewayRepository constructor.
     * @param array|null $config
     */
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
        $serviceOrm = Service::query()->where('path', $service)->orWhere('path', '/' . $service)->first();

        // If the service does not contain in database, try to get from config
        if (!$serviceOrm) {
            if (!array_key_exists('services', $this->config)) {
                throw new Exception('Services Config is not defined.');
            }

            if (!array_key_exists($service, $this->config['services'])) {
                throw new Exception('Service ' . $service . ' not found.');
            }

            return $this->config['services'][$service];
        }

        return $serviceOrm;
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
        $routes = null;

        if (is_array($serviceModel)) {
            $routes = $serviceModel['routes'];
        } else {
            $routes = $serviceModel->routes;
        }

        $arrayRoles = [];

        if (count($routes) > 0) {
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
