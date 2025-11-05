<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionSettingsModel extends Model
{
    protected $table      = 'commission_settings';
    protected $primaryKey = 'setting_id';

    protected $allowedFields = [
        'shop_id',
        'barber_commission_rate',
        'shop_commission_rate',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'shop_id'               => 'required|integer',
        'barber_commission_rate' => 'required|numeric|greater_than[0]|less_than_equal_to[100]',
        'shop_commission_rate'   => 'required|numeric|greater_than[0]|less_than_equal_to[100]',
    ];

    // Get commission settings for a shop
    public function getShopCommissionSettings($shopId)
    {
        $settings = $this->where('shop_id', $shopId)->first();
        
        // Return default settings if none exist
        if (!$settings) {
            return [
                'shop_id' => $shopId,
                'barber_commission_rate' => 70.00,
                'shop_commission_rate' => 30.00
            ];
        }
        
        return $settings;
    }

    // Update or create commission settings for a shop
    public function updateShopCommissionSettings($shopId, $barberRate, $shopRate)
    {
        // Validate rates add up to 100%
        if (($barberRate + $shopRate) != 100) {
            return false;
        }

        $existing = $this->where('shop_id', $shopId)->first();
        
        $data = [
            'shop_id' => $shopId,
            'barber_commission_rate' => $barberRate,
            'shop_commission_rate' => $shopRate,
        ];

        if ($existing) {
            return $this->update($existing['setting_id'], $data);
        } else {
            return $this->insert($data);
        }
    }

    // Get default commission settings
    public function getDefaultSettings()
    {
        return [
            'barber_commission_rate' => 70.00,
            'shop_commission_rate' => 30.00
        ];
    }
}
