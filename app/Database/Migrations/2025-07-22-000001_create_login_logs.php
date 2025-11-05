<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateLoginLogs extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'username'   => ['type' => 'VARCHAR', 'constraint' => 50],
            'role'       => ['type' => 'ENUM', 'constraint' => ['admin', 'customer', 'barber', 'owner']],
            'action'     => ['type' => 'ENUM', 'constraint' => ['login', 'logout']],
            'login_time' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('login_logs');
    }

    public function down()
    {
        $this->forge->dropTable('login_logs');
    }
}
