<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_2024_01_01_000000_AddAppointmentNotes extends Migration
{
    public function up()
    {
        $this->forge->addColumn('appointments', [
            'haircut_type' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
                'comment' => 'Type of haircut requested'
            ],
            'urgency' => [
                'type' => 'ENUM',
                'constraint' => ['normal', 'urgent', 'asap'],
                'default' => 'normal',
                'comment' => 'Urgency level of the appointment'
            ],
            'appointment_notes' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Additional notes about the appointment'
            ]
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('appointments', ['haircut_type', 'urgency', 'appointment_notes']);
    }
}
