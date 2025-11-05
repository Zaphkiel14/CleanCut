<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFeedbackTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'feedback_id' => [
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
            'appointment_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'rating' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'comment'    => 'Rating from 1 to 5',
            ],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('feedback_id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('appointment_id');
        $this->forge->addKey('rating');
        $this->forge->addKey('created_at');

        $this->forge->createTable('feedback');

        // Add foreign key constraints
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('appointment_id', 'appointments', 'appointment_id', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->forge->dropTable('feedback');
    }
}
