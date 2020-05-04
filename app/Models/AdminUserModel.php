<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class AdminUserModel extends Model
{
    use Singleton;

    protected $table = 'admin_user';

    public $connection = 'magpie';

    public $timestamps = false;
}
