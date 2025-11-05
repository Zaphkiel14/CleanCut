<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = false; // Disable field protection to avoid issues
    protected $allowedFields = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'role',
        'is_active',
        'profile_picture',
        'is_freelancer'
    ];

    protected $validationRules = [
        'is_freelancer' => 'permit_empty|in_list[0,1]'
    ];

    // Disable timestamps to avoid issues
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = null;
    protected $updatedField = null;

    // Disable validation to avoid conflicts
    protected $skipValidation = true;
    protected $cleanValidationRules = true;

    // Disable callbacks to avoid password hashing issues
    protected $allowCallbacks = false;

    // Helper methods
    public function getBarbers()
    {
        return $this->where('role', 'barber')
            ->where('is_active', 1)
            ->findAll();
    }

    public function getCustomers()
    {
        return $this->where('role', 'customer')
            ->where('is_active', 1)
            ->findAll();
    }

    public function getUsersByRole($role)
    {
        return $this->where('role', $role)
            ->where('is_active', 1)
            ->findAll();
    }

    public function isBarber($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'barber';
    }

    public function isCustomer($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'customer';
    }

    public function isAdmin($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'admin';
    }

    public function getActiveUsers()
    {
        return $this->where('is_active', 1)->findAll();
    }

    public function searchUsers($search)
    {
        return $this->like('first_name', $search)
            ->orLike('last_name', $search)
            ->orLike('email', $search)
            ->where('is_active', 1)
            ->findAll();
    }
}
