<?php

namespace DevTyping\Gateway\Http\Controllers;

use App\Http\Resources\BaseCollection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

// Exceptions
use Exception;

// Models
use DevTyping\Gateway\Http\Models\Service;
use Illuminate\Validation\ValidationException;

/**
 * Class GatewayController
 * @package DevTyping\Gateway\Http\Controllers
 */
class ServiceController extends Controller
{
    /**
     * Get all services
     *
     * @return BaseCollection|JsonResponse
     */
    public function index()
    {
        try {
            $roles = Service::query()->paginate(20);
            return new BaseCollection($roles);
        } catch (Exception $e) {
            return $this->responseError('An error occurred', 500);
        }
    }

    /**
     * Get a single service
     *
     * @param $service
     * @return JsonResponse
     */
    public function show($service)
    {
        try {
            $serviceOrm = Service::query()->where('id', $service)->firstOrFail();
            return $this->responseSuccess($serviceOrm);
        } catch (ModelNotFoundException $e) {
            return $this->responseError('No query results.', 404);
        } catch (Exception $e) {
            return $this->responseError('An error occurred', 500);
        }
    }

    /**
     * Create a new service
     *
     * @return JsonResponse
     */
    public function store()
    {
        try {
            $validatedData = request()->validate([
                'name' => 'required',
                'protocol' => 'required',
                'host' => 'required',
                'path' => ['required', 'unique:services'],
                'roles' => ['required', 'array'],
                'port' => 'required',
                'connect_timeout' => 'required',
                'routes' => ['array'],
                'defaults' => ['array']
            ]);

            $service = Service::query()->create($validatedData);
            return $this->responseSuccess($service);
        } catch (ValidationException $e) {
            return $this->responseValidationFailed($e->errors());
        } catch (Exception $e) {
            return $this->responseError('An error occurred', 500);
        }
    }

    public function update($service)
    {
        return response()->json(['message' => 'under construction']);
    }

    /**
     * Destroy a service
     *
     * @param $service
     * @return JsonResponse
     */
    public function destroy($service)
    {
        try {
            $serviceOrm = Service::query()->findOrFail($service);
            $serviceOrm->delete();
            return $this->responseMsg('Role ' . $service . ' has been deleted.');
        } catch (ModelNotFoundException $e) {
            return $this->responseError('No query results.', 404);
        } catch (Exception $e) {
            return $this->responseError('An error occurred', 500);
        }
    }
}
