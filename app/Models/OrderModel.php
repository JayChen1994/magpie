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
}