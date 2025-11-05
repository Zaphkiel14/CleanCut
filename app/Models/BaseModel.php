<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\AuditLogger;

class BaseModel extends Model
{
    protected $auditEnabled = true;

    public function insert($data = null, bool $returnID = true)
    {
        $result = parent::insert($data, $returnID);

        if ($this->auditEnabled && $result) {
            $id = $returnID ? $this->getInsertID() : null;
            AuditLogger::log(
                $this->table,
                $id,
                session()->get('user_role') ?? 'system',
                session()->get('user_id'),
                'INSERT',
                ['new' => $data]
            );
        }

        return $result;
    }

    public function update($id = null, $data = null): bool
    {
        $old = $this->find($id);
        $result = parent::update($id, $data);

        if ($this->auditEnabled && $result && $old) {
            AuditLogger::log(
                $this->table,
                $id,
                session()->get('user_role') ?? 'system',
                session()->get('user_id'),
                'UPDATE',
                ['old' => $old, 'new' => $data]
            );
        }

        return $result;
    }

    public function delete($id = null, bool $purge = false): bool
    {
        $old = $this->find($id);
        $result = parent::delete($id, $purge);

        if ($this->auditEnabled && $result && $old) {
            AuditLogger::log(
                $this->table,
                $id,
                session()->get('user_role') ?? 'system',
                session()->get('user_id'),
                'DELETE',
                ['old' => $old]
            );
        }

        return $result;
    }
}
