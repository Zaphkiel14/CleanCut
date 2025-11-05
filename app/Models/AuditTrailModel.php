<?php

namespace App\Models;

use CodeIgniter\Model;

class AuditTrailModel extends Model
{
    protected $table      = 'audit_trail';
    protected $primaryKey = 'audit_id';
    protected $allowedFields = ['model', 'model_id', 'role', 'user_id', 'action', 'date_created', 'changes'];
    protected $useTimestamps = false; // We handle timestamps manually in the logger

}
