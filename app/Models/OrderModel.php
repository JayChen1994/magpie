<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    use Singleton;

    protected $table = 'order';

    public $connection = 'magpie';

    public $timestamps =false;

    const STATUS_PAID = 10;

    const STATUS_DESC = [
        self::STATUS_PAID => '订单已支付'
    ];

    public function package()
    {
        return $this->hasOne(PackageModel::class, 'id');
    }
}
