<?php


namespace App\Models;

use App\Utils\Singleton;
use Illuminate\Database\Eloquent\Model;

class PackageModel extends Model
{
    use Singleton;

    protected $table = 'package';

    public $connection = 'magpie';
}