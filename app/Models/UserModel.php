<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{

    protected $table = 'user';

    public $connection = 'magpie';

    public $timestamps = false;
}
