<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'notification_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'comment' => 'User who receives the notification'
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'comment' => 'Notification title'
            ],
            'message' => [
                'type' => 'TEXT',
                'comment' => 'Notification message content'
            ],
            'type' => [
                'type' => 'ENUM',
                'constraint' => ['booking', 'cancellation', 'reminder', 'reschedule', 'general'],
                'default' => 'general',
                'comment' => 'Type of notification'
            ],
            'related_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'comment' => 'ID of related record (e.g., appointment_id)'
            ],
            'is_read' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => 'Whether the notification has been read'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('notification_id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('type');
        $this->forge->addKey('is_read');
        $this->forge->addKey('created_at');

        $this->forge->createTable('notifications');
    }

    public function down()
    {
        $this->forge->dropTable('notifications');
    }
}
