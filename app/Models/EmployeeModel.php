<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeeModel extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id', 'shop_id', 'position', 'hire_date', 'salary', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'user_id' => 'required|integer',
        'shop_id' => 'required|integer',
        'position' => 'required|min_length[2]|max_length[100]',
        'hire_date' => 'required|valid_date',
        'salary' => 'permit_empty|decimal'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User is required.'
        ],
        'shop_id' => [
            'required' => 'Shop is required.'
        ],
        'position' => [
            'required' => 'Position is required.'
        ],
        'hire_date' => [
            'required' => 'Hire date is required.',
            'valid_date' => 'Please enter a valid date.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getEmployeesByShop($shopId)
    {
        $db = \Config\Database::connect();
        return $db->table('employees e')
                  ->select('e.*, u.first_name, u.last_name, u.email')
                  ->join('users u', 'u.user_id = e.user_id')
                  ->where('e.shop_id', $shopId)
                  ->where('e.is_active', 1)
                  ->get()
                  ->getResultArray();
    }

    public function getEmployeeWithDetails($employeeId)
    {
        $db = \Config\Database::connect();
        
        return $db->table('employees e')
                  ->select('e.*, u.first_name, u.last_name, u.email, s.shop_name')
                  ->join('users u', 'u.user_id = e.user_id')
                  ->join('shops s', 's.shop_id = e.shop_id')
                  ->where('e.employee_id', $employeeId)
                  ->get()
                  ->getRowArray();
    }

    public function getEmployeeByUserId($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_active', 1)
                    ->first();
    }

    public function getActiveEmployees($shopId = null)
    {
        $builder = $this->where('is_active', 1);
        
        if ($shopId) {
            $builder->where('shop_id', $shopId);
        }
        
        return $builder->findAll();
    }

    public function searchEmployees($search, $shopId = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('employees e')
                      ->select('e.*, u.first_name, u.last_name, u.email, s.shop_name')
                      ->join('users u', 'u.user_id = e.user_id')
                      ->join('shops s', 's.shop_id = e.shop_id')
                      ->where('e.is_active', 1)
                      ->groupStart()
                      ->like('u.first_name', $search)
                      ->orLike('u.last_name', $search)
                      ->orLike('e.position', $search)
                      ->groupEnd();
        
        if ($shopId) {
            $builder->where('e.shop_id', $shopId);
        }
        
        return $builder->orderBy('u.first_name', 'ASC')->get()->getResultArray();
    }

    public function updateEmployeeStatus($employeeId, $isActive)
    {
        return $this->update($employeeId, ['is_active' => $isActive]);
    }

    public function getEmployeeStats($shopId = null)
    {
        $builder = $this;
        
        if ($shopId) {
            $builder->where('shop_id', $shopId);
        }
        
        $stats = [
            'total_employees' => $builder->countAllResults(),
            'active_employees' => $builder->where('is_active', 1)->countAllResults(),
            'inactive_employees' => $builder->where('is_active', 0)->countAllResults()
        ];
        
        return $stats;
    }
} 