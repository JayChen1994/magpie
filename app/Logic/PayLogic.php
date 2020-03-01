<?php

namespace App\Logic;

use App\Models\PackageModel;
use App\Utils\Singleton;
use Exception;
use Yansongda\LaravelPay\Facades\Pay;

class PayLogic extends BaseLogic
{
    use Singleton;

    public function toPay($uri, $addressInfo, $num)
    {
        $package = PackageModel::getInstance()->getByUri($uri);
        $price = $package->price;
        $payMoney = $price * $num;
        $order = [
            'out_trade_no' => '',
            'body' => '测试',
            'total_fee' => $payMoney,
            'openid' => '',
        ];
        // todo 写预支付表 和 支付表
        return Pay::wechat()->mp($order);
    }

    // 回调,创建订单
    public function notify()
    {
        $pay = Pay::wechat();
        try {
            $pay->verify();
            // todo something
        } catch (Exception $exception) {

        }
        return $pay->success();
    }
}