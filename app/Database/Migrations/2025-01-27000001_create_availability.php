<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAvailability extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'availability_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'available_date' => [
                'type' => 'DATE',
            ],
            'available_time' => [
                'type' => 'TIME',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('availability_id');
        $this->forge->addKey('user_id');
        $this->forge->addKey('available_date');
        $this->forge->addKey(['user_id', 'available_date']);
        $this->forge->createTable('availability');
    }

    public function down()
    {
        $this->forge->dropTable('availability');
    }
}

