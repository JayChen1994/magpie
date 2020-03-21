<?php

namespace App\Logic;

use App\Models\OrderModel;
use App\Models\PackageModel;
use App\Models\PayOrderModel;
use App\Models\PreOrderModel;
use App\Utils\CommonUtil;
use App\Utils\Singleton;
use Exception;
use Yansongda\LaravelPay\Facades\Pay;

class PayLogic extends BaseLogic
{
    use Singleton;
    const CENT = 100; // 分

    public function toPay($uri, $addressInfo, $num)
    {
        $uid = 1;
        $openid = '';
        $package = PackageModel::getInstance()->getByUri($uri);
        $price = $package->price;
        $payMoney = $price * $num * self::CENT;
        $outTradeNo = CommonUtil::createUri();
        $nowTime = time();
        $order = [
            'out_trade_no' => $outTradeNo,
            'body' => '测试',
            'total_fee' => $payMoney,
            'openid' => $openid,
        ];
        $contentJson = json_encode(['packageId' => $package->id, 'type' => $package->type, 'addressInfo' => $addressInfo]);
        // todo 写预支付表 和 支付表
        $preId = PreOrderModel::query()->insert([
            'uri' => CommonUtil::createUri(),
            'uid' => $uid,
            'payMoney' => $payMoney,
            'num' => $num,
            'status' => 0, // 1成功
            'contentJson' => $contentJson,
            'createTime' => $nowTime
        ]);
        PayOrderModel::query()->insert([
            'uid' => $uid,
            'outTradeNo' => $outTradeNo,
            'targetId' => $preId,
            'payStatus' => PayOrderModel::UN_PAY, //
            'num' => $num,
            'price' => $payMoney,
            'paidTime' => 0,
            'createTime' => $nowTime
        ]);
        $result = Pay::wechat()->mp($order);
        // 返回信息给前台 ，前台调起来微信支付
        return  [
            'timeStamp' => $result->timeStamp,
            'nonceStr' => $result->nonceStr,
            'packageStr' => $result->package,
            'signType' => $result->signType,
            'paySign' => $result->paySign,
        ];
    }

    // 回调,创建订单
    public function notify()
    {
        $nowTime = time();
        $pay = Pay::wechat();
        try {
            $data = $pay->verify();
            // todo something
            if (!$data) {
                return '';
            }
            $payOrderNo = $data->out_trade_no;
            $payOrder = PayOrderModel::query()->select(['contentJson', 'uid', 'type', 'num'])->where($payOrderNo)->first();
            $content = json_decode($payOrder->contentJson, true);
            $packageId = $content['packageId'];
            $package = PackageModel::query()->where(['id' => $packageId])->first();
            // 更新支付状态
            OrderModel::query()->insert([
                'uri' => CommonUtil::createUri(),
                'packageId' => $packageId,
                'uid' => $payOrder->uid,
                'type' => $content['type'],
                'surplusTimes' => $package->cleanNum * $payOrder->num,
                'status' => 50,
                'num' => $payOrder->num,
                'payMoney' => $payOrder->price,
                'addressJson' => $content['addressInfo'],
                'payOrderNo' => $payOrderNo,
                'paidTime' => $nowTime,
                'createTime' => $nowTime
            ]);
        } catch (Exception $exception) {

        }
        return $pay->success();
    }
}
