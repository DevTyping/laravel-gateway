<?php

namespace DevTyping\Gateway\Http\Controllers;

use DevTyping\Gateway\Http\Repository\GatewayRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as Controller;

// Exceptions
use Exception;

/**
 * Class GatewayController
 * @package DevTyping\Gateway\Http\Controllers
 */
class BaseController extends Controller
{
    protected $service = null;

    /**
     * GatewayController constructor.
     * @param Request $request
     * @throws Exception
     */
    public function __construct(Request $request)
    {
        try {
            $this->service = (new GatewayRepository())->getService($request->route('service'));
        } catch (Exception $e) {
            $this->responseError($e->getMessage(), 401);
        }
    }

    /**
     * @param string $message
     * @param int $httpStatusCode
     * @return JsonResponse
     */
    public function responseError(string $message = "", int $httpStatusCode = 500)
    {
        return response()->json([
            "error" => [
                "type" => "AGWException",
                "message" => $message
            ],
        ], $httpStatusCode ? $httpStatusCode : 500);
    }
}
