<?php

namespace App\Http\Controllers;

use App\ConstDir\ErrorConst;
use App\Logic\OrderLogic;
use App\Logic\PayLogic;
use App\Utils\CommonUtil;
use Illuminate\Http\Request;

class PayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $payLogic = null;
    public function __construct()
    {
        $this->payLogic = PayLogic::getInstance();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \App\Exceptions\ApiException
     */
    public function getOrderDetail(Request $request)
    {
        $uri = $request->input();
        if (!$uri) {
            CommonUtil::throwException(ErrorConst::ERROR_PARAM_CODE, ErrorConst::ERROR_PARAM_MSG);
        }
        return $this->payLogic->getDetail($uri);
    }
}
