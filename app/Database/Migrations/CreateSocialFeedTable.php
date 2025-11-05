<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSocialFeedTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'post_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'post_type' => [
                'type' => 'ENUM',
                'constraint' => ['work_showcase', 'status_update', 'announcement'],
                'default' => 'work_showcase',
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'content' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'images' => [
                'type' => 'TEXT',
                'comment' => 'JSON array of image paths',
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['open', 'closed', 'on_break', 'busy'],
                'null' => true,
            ],
            'is_public' => [
                'type' => 'BOOLEAN',
                'default' => true,
            ],
            'likes_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
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

        $this->forge->addKey('post_id', true);
        $this->forge->addForeignKey('user_id', 'users', 'user_id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('social_feed');
    }

    public function down()
    {
        $this->forge->dropTable('social_feed');
    }
} 