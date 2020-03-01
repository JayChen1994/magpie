<?php

namespace App\Logic;

use App\Models\PackageModel;
use App\Utils\Singleton;

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
        
    }

    public function useList()
    {
        
    }

    public function toUse()
    {
        
    }
}