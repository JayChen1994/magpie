<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class UseLogModel extends Model
{
    use Singleton;

    protected $table = 'use_log';

    public $connection = 'magpie';

}