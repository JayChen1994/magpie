<?php

namespace App\Logic;

use App\Models\OrderModel;
use App\Models\PackageModel;
use App\Models\PayOrderModel;
use App\Models\PreOrderModel;
use App\Utils\CommonUtil;
use App\Utils\Singleton;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yansongda\LaravelPay\Facades\Pay;

class PayLogic extends BaseLogic
{
    use Singleton;
    const CENT = 100; // 分

    public function toPay($uri, $addressInfo, $num)
    {
        $user = Auth::user();
        $openid = $user->openid;
        $uid = $user->id;
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
            'contentJson' => $contentJson,
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
            $payOrder = PayOrderModel::query()->select(['contentJson', 'uid', 'num'])->where(['outTradeNo' => $payOrderNo])->first();
            $content = json_decode($payOrder->contentJson, true);
            $packageId = $content['packageId'];
            $type = $content['type'];
            $package = PackageModel::query()->where(['id' => $packageId])->first();
            // 拼团的
            if ($type == 2) {
                try {
                    PackageModel::query()->where(['type' => $type])->decrement('personLimit', 1);
                } catch (Exception $e) {
                    CommonUtil::throwException(100, '此次参团已');
                }
            }
            // 更新支付状态
            OrderModel::query()->insert([
                'uri' => CommonUtil::createUri(),
                'packageId' => $packageId,
                'uid' => $payOrder->uid,
                'type' => $type,
                'surplusTimes' => $package->cleanNum * $payOrder->num,
                'status' => OrderModel::STATUS_PAID,
                'num' => $payOrder->num,
                'payMoney' => $payOrder->price,
                'addressJson' => json_encode($content['addressInfo']),
                'payOrderNo' => $payOrderNo,
                'paidTime' => $nowTime,
                'createTime' => $nowTime
            ]);
        } catch (Exception $exception) {
            Log::info($exception->getCode() . $exception->getTraceAsString());
        }
        return $pay->success();
    }
}
