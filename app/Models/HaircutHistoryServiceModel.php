<?php

namespace App\Models;

use CodeIgniter\Model;

class HaircutHistoryServiceModel extends Model
{
    protected $table = 'haircut_history_services';
    protected $primaryKey = 'history_id'; // Set to one of the composite key fields
    protected $useAutoIncrement = false;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['history_id', 'service_id'];

    /**
     * Replace the set of services for a given history record.
     */
    public function setServicesForHistory(int $historyId, array $serviceIds): void
    {
        $cleanIds = $this->sanitizeIds($serviceIds);

        // Remove existing rows
        $this->where('history_id', $historyId)->delete();

        if (empty($cleanIds)) {
            return;
        }

        // Insert new rows one by one to avoid composite key issues
        foreach ($cleanIds as $sid) {
            $this->insert([
                'history_id' => $historyId,
                'service_id' => $sid,
            ]);
        }
    }

    /**
     * Return service_id list for a history record.
     */
    public function getServiceIdsForHistory(int $historyId): array
    {
        $rows = $this->select('service_id')->where('history_id', $historyId)->findAll();
        if (empty($rows)) {
            return [];
        }
        return array_map(static fn ($r) => (int) $r['service_id'], $rows);
    }

    /**
     * Return service rows for a history record by joining services table.
     */
    public function getServicesForHistory(int $historyId): array
    {
        $db = \Config\Database::connect();
        return $db->table('haircut_history_services hhs')
                  ->select('s.*')
                  ->join('services s', 's.service_id = hhs.service_id')
                  ->where('hhs.history_id', $historyId)
                  ->get()
                  ->getResultArray();
    }

    private function sanitizeIds(array $ids): array
    {
        $clean = [];
        foreach ($ids as $id) {
            if ($id === null || $id === '') {
                continue;
            }
            $intVal = (int) $id;
            if ($intVal > 0) {
                $clean[$intVal] = $intVal; // de-duplicate
            }
        }
        return array_values($clean);
    }
}


