<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEarningsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'earning_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'barber_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'appointment_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'service_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
            ],
            'commission_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'comment' => 'Percentage commission',
                'null' => false,
            ],
            'commission_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
            ],
            'payment_method' => [
                'type' => 'ENUM',
                'constraint' => ['cash', 'card', 'online'],
                'default' => 'cash',
            ],
            'payment_status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'completed', 'refunded'],
                'default' => 'pending',
            ],
            'earning_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('earning_id', true);
        $this->forge->addForeignKey('barber_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('appointment_id', 'appointments', 'appointment_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'service_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('earnings');
    }

    public function down()
    {
        $this->forge->dropTable('earnings');
    }
} 