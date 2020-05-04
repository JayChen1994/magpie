<?php

namespace App\Logic;

use App\Models\OrderModel;
use App\Models\PackageModel;
use App\Models\UseLogModel;
use App\Utils\CommonUtil;
use App\Utils\Singleton;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Yansongda\Pay\Log;

class OrderLogic extends BaseLogic
{
    use Singleton;

    const PERCENT = 100;
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
        $user = Auth::user();
        $uid = $user->id;
        $userInfo = [
            'headImg' => $user->headImg,
            'nickName' => $user->nickName
        ];
        $orders = OrderModel::query()->select(['id', 'uri', 'status', 'createTime', 'payMoney', 'surplusTimes', 'type', 'packageId'])
            ->where(['uid' => $uid])->orderBy('createTime', 'desc')->get();
        if ($orders->isEmpty()) {
            return [
                'list' => [],
                'userInfo' => $userInfo
            ];
        }
        $packageIds = $orders->pluck('packageId')->toArray();
        $packages = PackageModel::query()->select(['imgUrl', 'id'])->whereIn('id' , $packageIds)->get()->keyBy('id');
        foreach ($orders as $order) {
            $order->status = OrderModel::STATUS_DESC[$order->status];
            $order->payMoney = $order->payMoney / self::PERCENT;
            $order->imgUrl = env('APP_URL') . '/static/media/' . $packages->get($order->packageId)->imgUrl;
        }
        return [
            'list' => $orders,
            'userInfo' => $userInfo
        ];
    }

    public function useList($orderId)
    {
        $useLogs = UseLogModel::query()->where(['orderId' => $orderId])->get();
        foreach ($useLogs as $useLog) {
            $useLog->createTime = Carbon::createFromTimestamp($log->createTime, 'Asia/Shanghai')->format('Y-m-d H:i:s');
        }
        return $useLogs;
    }

    public function toUse($orderId)
    {
        $user = Auth::user();
        $uid = $user->id;
        $ret = 0;
        $nowTime = time();
        $packageId = OrderModel::query()->where(['id' => $orderId])->value('packageId');
        $title = PackageModel::query()->where(['id' => $packageId])->value('title');
        try {
            $ret = OrderModel::query()->where(['id' => $orderId, 'uid' => $uid])->decrement('surplusTimes', 1);
            UseLogModel::query()->insert([
                'uid' => $uid,
                'orderId' => $orderId,
                'packageTitle' => $title,
                'createTime' => $nowTime,
            ]);
        } catch (Exception $e) {
            Log::error('更新错误', ['msg' => $e->getMessage(), 'log' => $e->getTraceAsString()]);
            CommonUtil::throwException(100, '已经没有剩余的次数了');
        }
        return $ret;
    }

    public function adminUseLog()
    {
        $user = Auth::user();
        /** @var $isAdmin */
        $isAdmin = $user->isAdmin;
        if (!$isAdmin) {
            CommonUtil::throwException(100, '你没有该权限');
        }
        $logs = UseLogModel::query()->where('createTime', '>=' , strtotime(date('Y-m-d', strtotime('-7 days'))))
            ->orderBy('createTime', 'desc')
            ->get();
        $orderIds = $logs->pluck('orderId')->toArray();
        $orders = OrderModel::query()->whereIn('id', $orderIds)->select(['id', 'addressJson'])->get();
        $orderKeyBy = $orders->keyBy('id');
        $data = [];
        foreach ($logs as $log) {
            $data[] = [
                'title' => $log->packageTitle,
                'applyTime' => Carbon::createFromTimestamp($log->createTime, 'Asia/Shanghai')->format('Y-m-d H:i:s'),
                'addressInfo' => isset($orderKeyBy->get($log->orderId)->addressJson) ?
                    json_decode($orderKeyBy->get($log->orderId)->addressJson, true) : [],
            ];
        }
        return [
            'list' => $data,
            'isAdmin' => $isAdmin
        ];
    }
}
