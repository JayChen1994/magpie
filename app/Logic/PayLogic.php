<?php

namespace App\Logic;

use App\Models\PackageModel;
use App\Utils\Singleton;

class PayLogic extends BaseLogic
{
    use Singleton;

    /**
     * @param $uri
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getDetail($uri)
    {
        return PackageModel::query()->select(['uri', 'title', 'imgUrl', 'price', 'type', 'cleanNum', 'unit'])
            ->where('uri', $uri)->first();
    }
}