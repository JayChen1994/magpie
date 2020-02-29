<?php

namespace App\Http\Controllers;

use App\Logic\OrderLogic;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getOrderDetail()
    {
        return OrderLogic::getInstance()->getDetail();
    }
}
