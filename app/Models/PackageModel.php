<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class PackageModel extends Model
{
    use Singleton;

    protected $table = 'package';

    public $connection = 'magpie';

    const COLUMN = ['id', 'uri', 'pid', 'title', 'imgUrl', 'price', 'type', 'cleanNum', 'content', 'unit', 'detailImgs'];


    public function getByUri($uri)
    {
        $package = PackageModel::query()->select(self::COLUMN)
            ->where('uri', $uri)->first();
        $package->imgUrl = env('APP_URL') . '/static/media/' . $package->imgUrl;
        $package->detailImgs = array_map(function ($img) {
            return env('APP_URL') . '/static/media/' . $img;
        }, json_decode($package->detailImgs, true));
        $package->price = $package->price / 100;
        return $package;
    }
}