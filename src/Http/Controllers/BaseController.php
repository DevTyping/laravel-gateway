<?php

namespace DevTyping\Gateway\Http\Controllers;

use DevTyping\Gateway\Http\Repository\GatewayRepository;
use Illuminate\Http\Request;

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
            $this->responseError($e->getMessage(), $e->getCode());
        }
    }
}
