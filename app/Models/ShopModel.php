<?php

namespace App\Models;

use CodeIgniter\Model;

class ShopModel extends Model
{
    protected $table = 'shops';
    protected $primaryKey = 'shop_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'owner_id', 'shop_name', 'description', 'address', 'phone', 
        'email', 'website', 'opening_hours', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'owner_id' => 'required|integer',
        'shop_name' => 'required|min_length[2]|max_length[255]',
        'address' => 'required',
        'phone' => 'permit_empty|max_length[20]',
        'email' => 'permit_empty|valid_email|max_length[255]'
    ];

    protected $validationMessages = [
        'shop_name' => [
            'required' => 'Shop name is required.'
        ],
        'address' => [
            'required' => 'Shop address is required.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getActiveShops()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function getShopByOwner($ownerId)
    {
        return $this->where('owner_id', $ownerId)
                    ->where('is_active', 1)
                    ->first();
    }

    public function getShopWithOwner($shopId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('shops s')
                  ->select('s.*, u.first_name as owner_name, u.email as owner_email')
                  ->join('users u', 'u.user_id = s.owner_id')
                  ->where('s.shop_id', $shopId)
                  ->get()
                  ->getRowArray();
    }

    public function searchShops($search)
    {
        return $this->like('shop_name', $search)
                    ->orLike('address', $search)
                    ->where('is_active', 1)
                    ->findAll();
    }

    public function getShopsByLocation($location)
    {
        return $this->like('address', $location)
                    ->where('is_active', 1)
                    ->findAll();
    }
} 