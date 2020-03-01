<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class PayOrderModel extends Model
{
    use Singleton;

    protected $table = 'pay_order';

    public $connection = 'magpie';
}