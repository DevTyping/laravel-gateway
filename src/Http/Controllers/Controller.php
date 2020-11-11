<?php

namespace DevTyping\Gateway\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class GatewayController
 * @package DevTyping\Gateway\Http\Controllers
 */
class Controller extends BaseController
{
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

    /**
     * @param array|object $data
     * @param int $httpStatus
     * @return JsonResponse
     */
    protected function responseSuccess($data, $httpStatus = 200)
    {
        return response()->json([
            'data' => $data ?? []
        ], $httpStatus ? $httpStatus : 200);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    protected function responseValidationFailed($data)
    {
        return response()->json([
            'message' => 'Validation Failed',
            'errors' => $data
        ], 422);
    }

    /**
     * @param string $msg
     * @param int $httpStatus
     * @return JsonResponse
     */
    protected function responseMsg($msg, $httpStatus = 200)
    {
        return response()->json([
            'message' => $msg
        ], $httpStatus);
    }
}
