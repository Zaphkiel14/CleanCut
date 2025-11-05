<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateHaircutHistoryTable extends Migration
{
    public function up()
    {
        // Drop old photo columns
        $this->forge->dropColumn('haircut_history', ['before_photo', 'after_photo']);
        
        // Add new 4-panel photo columns
        $fields = [
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
        ];
        
        $this->forge->addColumn('haircut_history', $fields);
    }

    public function down()
    {
        // Drop new columns
        $this->forge->dropColumn('haircut_history', [
            'top_photo', 'top_description',
            'left_side_photo', 'left_side_description',
            'right_side_photo', 'right_side_description',
            'back_photo', 'back_description'
        ]);
        
        // Add back old columns
        $fields = [
            'before_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'after_photo' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
        ];
        
        $this->forge->addColumn('haircut_history', $fields);
    }
}
