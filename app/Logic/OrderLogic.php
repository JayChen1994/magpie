<?php

namespace App\Logic;

use App\Models\OrderModel;
use App\Models\PackageModel;
use App\Models\UseLogModel;
use App\Utils\CommonUtil;
use App\Utils\Singleton;
use Exception;

class OrderLogic extends BaseLogic
{
    use Singleton;

    /**
     * @param $uri
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getDetail($uri)
    {
        return PackageModel::getInstance()->getByUri($uri);
    }

    public function list()
    {
        $uid = 1;
        $orders = OrderModel::query()->select(['id', 'uri', 'status', 'createTime', 'payMoney', 'surplusTimes', 'type', 'packageId'])
            ->where(['uid' => $uid])->get();
        $packageIds = $orders->pluck('packageId')->toArray();
        $packages = PackageModel::query()->select(['imgUrl', 'id'])->where(['id' => $packageIds])->get()->keyBy('id');
        foreach ($orders as $order) {
            $order->imgUrl = env('APP_URL') . '/imgs/' . $packages->get($order->packageId)->imgUrl;
        }
        return $orders;
    }

    public function useList($orderId)
    {
        $useLogs = UseLogModel::query()->where(['orderId' => $orderId])->get();
        foreach ($useLogs as $useLog) {
            $useLog->createTime = date('Y-m-d H:i:s', $useLog->createTime);
        }
        return $useLogs;
    }

    public function toUse($orderId)
    {
        $uid = 1;
        $ret = 0;
        $nowTime = time();
        try {
            $ret = OrderModel::query()->where(['id' => $orderId, 'uid' => $uid])->decrement('surplusTimes', 1);
            UseLogModel::query()->insert([
                'uid' => $uid,
                'orderId' => $orderId,
                'createTime' => $nowTime,
            ]);
        } catch (Exception $e) {
            CommonUtil::throwException(100, '已经没有剩余的次数了');
        }
        return $ret;
    }
}