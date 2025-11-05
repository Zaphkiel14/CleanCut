<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'inventory_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'shop_id', 'item_name', 'description', 'quantity', 
        'unit_price', 'reorder_level'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'shop_id' => 'required|integer',
        'item_name' => 'required|min_length[2]|max_length[255]',
        'quantity' => 'required|integer|greater_than_equal_to[0]',
        'unit_price' => 'permit_empty|decimal',
        'reorder_level' => 'permit_empty|integer|greater_than_equal_to[0]'
    ];

    protected $validationMessages = [
        'item_name' => [
            'required' => 'Item name is required.'
        ],
        'quantity' => [
            'required' => 'Quantity is required.',
            'greater_than_equal_to' => 'Quantity cannot be negative.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getInventoryByShop($shopId)
    {
        return $this->where('shop_id', $shopId)
                    ->orderBy('item_name', 'ASC')
                    ->findAll();
    }

    public function getLowStockItems($shopId = null)
    {
        $builder = $this->where('quantity <= reorder_level');
        
        if ($shopId) {
            $builder->where('shop_id', $shopId);
        }
        
        return $builder->orderBy('quantity', 'ASC')->findAll();
    }

    public function updateQuantity($inventoryId, $newQuantity)
    {
        return $this->update($inventoryId, ['quantity' => $newQuantity]);
    }

    public function addStock($inventoryId, $quantity)
    {
        $item = $this->find($inventoryId);
        if ($item) {
            $newQuantity = $item['quantity'] + $quantity;
            return $this->update($inventoryId, ['quantity' => $newQuantity]);
        }
        return false;
    }

    public function removeStock($inventoryId, $quantity)
    {
        $item = $this->find($inventoryId);
        if ($item && $item['quantity'] >= $quantity) {
            $newQuantity = $item['quantity'] - $quantity;
            return $this->update($inventoryId, ['quantity' => $newQuantity]);
        }
        return false;
    }

    public function searchInventory($search, $shopId = null)
    {
        $builder = $this->like('item_name', $search)
                        ->orLike('description', $search);
        
        if ($shopId) {
            $builder->where('shop_id', $shopId);
        }
        
        return $builder->orderBy('item_name', 'ASC')->findAll();
    }

    public function getInventoryStats($shopId = null)
    {
        $builder = $this;
        
        if ($shopId) {
            $builder->where('shop_id', $shopId);
        }
        
        $stats = [
            'total_items' => $builder->countAllResults(),
            'low_stock_items' => $builder->where('quantity <= reorder_level')->countAllResults(),
            'out_of_stock' => $builder->where('quantity', 0)->countAllResults(),
            'total_value' => $builder->selectSum('quantity * unit_price')->get()->getRow()->sum ?? 0
        ];
        
        return $stats;
    }
} 