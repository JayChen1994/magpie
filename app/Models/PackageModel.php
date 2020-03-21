<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class PackageModel extends Model
{
    use Singleton;

    protected $table = 'package';

    public $connection = 'magpie';

    const COLUMN = ['id', 'uri', 'pid', 'title', 'imgUrl', 'price', 'type', 'cleanNum', 'content', 'unit'];

    const TYPE_HAS_TIME = 1; // 有使用次数的套餐
    const TYPE_NO_TIME = 0; // 没有使用次数的套餐

    public function getByUri($uri)
    {
        $package = PackageModel::query()->select(self::COLUMN)
            ->where('uri', $uri)->first();
        $package->imgUrl = env('APP_URL') . '/imgs/' . $package->imgUrl;
        return $package;
    }
}