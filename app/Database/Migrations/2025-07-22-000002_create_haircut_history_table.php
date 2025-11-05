<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHaircutHistoryTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'history_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'customer_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
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
                'null' => true,
            ],
            'haircut_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'style_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'style_notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // 4-panel photo system
            'top_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'top_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'left_side_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'left_side_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'right_side_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'right_side_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'back_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'back_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'services_used' => [
                'type' => 'TEXT',
                'comment' => 'JSON array of service IDs',
                'null' => true,
            ],
            'total_cost' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => false,
            ],
            'customer_rating' => [
                'type' => 'INT',
                'constraint' => 1,
                'comment' => '1-5 rating',
                'null' => true,
            ],
            'customer_feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addKey('history_id', true);
        $this->forge->addForeignKey('customer_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('barber_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('appointment_id', 'appointments', 'appointment_id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('haircut_history');
    }

    public function down()
    {
        $this->forge->dropTable('haircut_history');
    }
} 