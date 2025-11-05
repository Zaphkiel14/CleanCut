<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'payment_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'appointment_id', 'amount', 'payment_method', 'status', 
        'transaction_id', 'payment_date'
    ];

    // Dates
    protected $useTimestamps = false; // Using payment_date instead
    protected $dateFormat = 'datetime';

    // Validation
    protected $validationRules = [
        'appointment_id' => 'required|integer',
        'amount' => 'required|decimal',
        'payment_method' => 'required|in_list[cash,credit_card,debit_card,online]',
        'status' => 'required|in_list[pending,completed,failed,refunded]'
    ];

    protected $validationMessages = [
        'appointment_id' => [
            'required' => 'Appointment is required.'
        ],
        'amount' => [
            'required' => 'Payment amount is required.',
            'decimal' => 'Amount must be a valid number.'
        ],
        'payment_method' => [
            'required' => 'Payment method is required.'
        ]
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Helper methods
    public function getPaymentByAppointment($appointmentId)
    {
        return $this->where('appointment_id', $appointmentId)->first();
    }

    public function getPaymentsByUser($userId, $dateRange = null)
    {
        $db = \Config\Database::connect();
        
        $builder = $db->table('payments p')
                      ->select('p.*, a.appointment_date, a.appointment_time, s.service_name')
                      ->join('appointments a', 'a.appointment_id = p.appointment_id')
                      ->join('services s', 's.service_id = a.service_id')
                      ->where('a.user_id', $userId);
        
        if ($dateRange) {
            $builder->where('p.payment_date >=', $dateRange['start'])
                    ->where('p.payment_date <=', $dateRange['end']);
        }
        
        return $builder->orderBy('p.payment_date', 'DESC')->get()->getResultArray();
    }

    public function getPaymentStats($dateRange = null)
    {
        $builder = $this;
        
        if ($dateRange) {
            $builder->where('payment_date >=', $dateRange['start'])
                    ->where('payment_date <=', $dateRange['end']);
        }
        
        $stats = [
            'total_amount' => $builder->selectSum('amount')->where('status', 'completed')->get()->getRow()->amount ?? 0,
            'total_payments' => $builder->where('status', 'completed')->countAllResults(),
            'pending_payments' => $builder->where('status', 'pending')->countAllResults(),
            'failed_payments' => $builder->where('status', 'failed')->countAllResults()
        ];
        
        return $stats;
    }

    public function getPaymentsByMethod($method, $dateRange = null)
    {
        $builder = $this->where('payment_method', $method)
                        ->where('status', 'completed');
        
        if ($dateRange) {
            $builder->where('payment_date >=', $dateRange['start'])
                    ->where('payment_date <=', $dateRange['end']);
        }
        
        return $builder->findAll();
    }

    public function createPayment($appointmentId, $amount, $method, $transactionId = null)
    {
        $data = [
            'appointment_id' => $appointmentId,
            'amount' => $amount,
            'payment_method' => $method,
            'status' => 'pending',
            'transaction_id' => $transactionId,
            'payment_date' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data);
    }

    public function updatePaymentStatus($paymentId, $status)
    {
        return $this->update($paymentId, ['status' => $status]);
    }
} 