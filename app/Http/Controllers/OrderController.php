<?php

namespace App\Http\Controllers;

use App\ConstDir\ErrorConst;
use App\Logic\OrderLogic;
use App\Utils\CommonUtil;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    protected $orderLogic = null;
    public function __construct()
    {
        $this->orderLogic = OrderLogic::getInstance();
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws \App\Exceptions\ApiException
     */
    public function getOrderDetail(Request $request)
    {
        $uri = $request->input('uri');
        if (!$uri) {
            CommonUtil::throwException(ErrorConst::ERROR_PARAM_CODE, ErrorConst::ERROR_PARAM_MSG);
        }
        return $this->orderLogic->getDetail($uri);
    }
}
