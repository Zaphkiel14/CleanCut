<?php

namespace App\Controllers;

use App\Models\CommissionSettingsModel;
use App\Models\ShopModel;

class CommissionController extends BaseController
{
    protected $commissionSettingsModel;
    protected $shopModel;

    public function __construct()
    {
        $this->commissionSettingsModel = new CommissionSettingsModel();
        $this->shopModel = new ShopModel();
    }

    // Show commission settings
    public function index()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');

        if ($userRole !== 'owner' && $userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        // Get shop for owner
        if ($userRole === 'owner') {
            $shop = $this->shopModel->where('owner_id', $userId)->first();
            if (!$shop) {
                return redirect()->to('/dashboard')->with('error', 'Shop not found.');
            }
            $shopId = $shop['shop_id'];
        } else {
            // Admin can manage all shops
            $shopId = $this->request->getGet('shop_id') ?? 1;
            $shop = $this->shopModel->find($shopId);
        }

        // Get current commission settings
        $settings = $this->commissionSettingsModel->getShopCommissionSettings($shopId);

        $data = [
            'title' => 'Commission Settings',
            'user_role' => $userRole,
            'shop' => $shop,
            'settings' => $settings,
        ];

        if ($userRole === 'admin') {
            $data['shops'] = $this->shopModel->findAll();
        }

        return view('commission/index', $data);
    }

    // Update commission settings
    public function update()
    {
        $userId = session()->get('user_id');
        $userRole = session()->get('role') ?? session()->get('user_role');

        if ($userRole !== 'owner' && $userRole !== 'admin') {
            return redirect()->to('/commission')->with('error', 'Access denied.');
        }

        $shopId = $this->request->getPost('shop_id');
        $barberRate = (float) $this->request->getPost('barber_commission_rate');
        $shopRate = (float) $this->request->getPost('shop_commission_rate');

        // Validate rates
        if (($barberRate + $shopRate) != 100) {
            return redirect()->back()->withInput()->with('error', 'Commission rates must add up to 100%.');
        }

        if ($barberRate < 0 || $barberRate > 100 || $shopRate < 0 || $shopRate > 100) {
            return redirect()->back()->withInput()->with('error', 'Commission rates must be between 0% and 100%.');
        }

        // Check authorization for shop
        if ($userRole === 'owner') {
            $shop = $this->shopModel->where('owner_id', $userId)->where('shop_id', $shopId)->first();
            if (!$shop) {
                return redirect()->to('/commission')->with('error', 'Unauthorized.');
            }
        }

        // Update settings
        $result = $this->commissionSettingsModel->updateShopCommissionSettings($shopId, $barberRate, $shopRate);

        if ($result) {
            return redirect()->to('/commission')->with('success', 'Commission settings updated successfully.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update commission settings.');
        }
    }
}
