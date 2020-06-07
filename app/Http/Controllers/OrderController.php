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

    public function list()
    {
        return $this->orderLogic->list();
    }

    /**
     * 去使用
     * @param \Illuminate\Http\Request $request
     * @return int
     * @throws \App\Exceptions\ApiException
     */
    public function toUse(Request $request)
    {
        $orderId = $request->input('orderId');
        if (!$orderId) {
            CommonUtil::throwException(ErrorConst::ERROR_PARAM_CODE, ErrorConst::ERROR_PARAM_MSG);
        }
        return $this->orderLogic->toUse($orderId);
    }

    /**
     * 使用历史记录
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \App\Exceptions\ApiException
     */
    public function useList(Request $request)
    {
        $orderId = $request->input('orderId');
        if (!$orderId) {
            CommonUtil::throwException(ErrorConst::ERROR_PARAM_CODE, ErrorConst::ERROR_PARAM_MSG);
        }
        return $this->orderLogic->useList($orderId);
    }

    public function adminUseLog()
    {
        return $this->orderLogic->adminUseLog();
    }

    public function adminPaidList()
    {
        return $this->orderLogic->adminPaidList();
    }
}
