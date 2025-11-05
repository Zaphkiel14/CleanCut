<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCommissionSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'setting_id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'shop_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'barber_commission_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 70.00,
                'comment'    => 'Percentage that barber gets from service price'
            ],
            'shop_commission_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',
                'default'    => 30.00,
                'comment'    => 'Percentage that shop owner gets from service price'
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

        $this->forge->addKey('setting_id', true);
        $this->forge->addKey('shop_id');
        $this->forge->addForeignKey('shop_id', 'shops', 'shop_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('commission_settings');
    }

    public function down()
    {
        $this->forge->dropTable('commission_settings');
    }
}
