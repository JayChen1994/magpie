<?php

namespace App\Logic;

use App\Models\PackageModel;
use App\Utils\Singleton;

class OrderLogic extends BaseLogic
{
    use Singleton;
    public function getDetail()
    {
        return PackageModel::query()->get();
    }
}