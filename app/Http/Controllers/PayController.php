<?php

namespace App\Http\Controllers;

use App\ConstDir\ErrorConst;
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
    public function toPay(Request $request)
    {
        $uri = $request->input('uri');
        $num = $request->input('num');
        $addressInfo = $request->input('addressInfo');
        if (!$uri || !$num || !$addressInfo) {
            CommonUtil::throwException(ErrorConst::ERROR_PARAM_CODE, ErrorConst::ERROR_PARAM_MSG);
        }
        return $this->payLogic->toPay($uri, $addressInfo, $num);
    }

    public function notify()
    {
        return $this->payLogic->notify();
    }
}
