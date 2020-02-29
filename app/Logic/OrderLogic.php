<?php

namespace App\Logic;

use App\Models\PackageModel;
use App\Utils\Singleton;

class OrderLogic extends BaseLogic
{
    use Singleton;

    /**
     * @return
     */
    public function getDetail($uri)
    {
        PackageModel::query()->get();
        return [];
    }
}