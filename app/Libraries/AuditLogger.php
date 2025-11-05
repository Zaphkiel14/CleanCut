<?php

namespace App\Libraries;

use App\Models\AuditTrailModel;

class AuditLogger
{
    public static function log($model, $modelId, $role, $userId = null, $action, $changes)
    {
        $auditModel = new AuditTrailModel();

        $auditModel->insert([
            'model'        => $model,
            'model_id'     => $modelId,
            'role'         => $role,
            'user_id'      => $userId ?? session()->get('user_id'),
            'action'       => $action,
            'date_created' => date('Y-m-d H:i:s'),
            'changes'      => json_encode($changes, JSON_UNESCAPED_UNICODE),
        ]);
    }
}