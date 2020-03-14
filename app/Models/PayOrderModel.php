<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class PayOrderModel extends Model
{
    use Singleton;

    protected $table = 'pay_order';

    public $connection = 'magpie';

    const UN_PAY = 0;

    const PAID = 1;

    const PAYING = 99;

    const FAILED = 100;
}