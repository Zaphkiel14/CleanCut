<?php
namespace App\Models;

use CodeIgniter\Model;

class LoginLogModel extends Model
{
    protected $table = 'login_logs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'username', 'role', 'login_time'];
    public $timestamps = false;
}
