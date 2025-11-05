<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHaircutHistoryServicesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'history_id' => [
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
        ]);

        $this->forge->addKey(['history_id', 'service_id'], true);
        $this->forge->addForeignKey('history_id', 'haircut_history', 'history_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('service_id', 'services', 'service_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('haircut_history_services');
    }

    public function down()
    {
        $this->forge->dropTable('haircut_history_services');
    }
}
