<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    use Singleton;

    protected $table = 'pre_order';

    public $connection = 'magpie';
}