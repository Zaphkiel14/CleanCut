<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'service_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'shop_id', 'service_name', 'description', 'price', 
        'duration', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'shop_id' => 'required|integer',
        'service_name' => 'required|min_length[2]|max_length[255]',
        'price' => 'required|decimal',
        // duration no longer required; keep optional for backward compat
        'duration' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'service_name' => [
            'required' => 'Service name is required.'
        ],
        'price' => [
            'required' => 'Price is required.',
            'decimal' => 'Price must be a valid number.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getActiveServices($shopId = null)
    {
        $builder = $this->where('is_active', 1);
        if ($shopId) {
            $builder->where('shop_id', $shopId);
        }
        return $builder->findAll();
    }

    public function getServiceById($serviceId)
    {
        return $this->find($serviceId);
    }

    public function getServicesForBooking($shopId)
    {
        return $this->where('shop_id', $shopId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function calculateTotalPrice($serviceIds)
    {
        $total = 0;
        foreach ($serviceIds as $serviceId) {
            $service = $this->find($serviceId);
            if ($service) {
                $total += $service['price'];
            }
        }
        return $total;
    }

    public function getServiceDuration($serviceId)
    {
        $service = $this->find($serviceId);
        return $service ? $service['duration'] : 0;
    }

    public function getServicesByShop($shopId)
    {
        return $this->where('shop_id', $shopId)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function searchServices($search, $shopId = null)
    {
        $builder = $this->like('service_name', $search)
                        ->orLike('description', $search)
                        ->where('is_active', 1);
        
        if ($shopId) {
            $builder->where('shop_id', $shopId);
        }
        
        return $builder->findAll();
    }

    // Get services by IDs
    public function getServicesByIds($serviceIds)
    {
        if (empty($serviceIds)) {
            return [];
        }
        
        return $this->whereIn('service_id', $serviceIds)
                    ->findAll();
    }
} 