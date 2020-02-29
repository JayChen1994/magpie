<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class PackageModel extends Model
{
    use Singleton;

    protected $table = 'package';

    public $connection = 'magpie';

    const COLUMN = ['id', 'uri', 'pid', 'title', 'imgUrl', 'price', 'type', 'cleanNum', 'content'];

    const TYPE_HAS_TIME = 1; // 有使用次数的套餐
    const TYPE_NO_TIME = 2; // 没有使用次数的套餐
}