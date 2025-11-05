<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAvailabilityTable extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'create:availability-table';
    protected $description = 'Create the availability table for date-specific schedules.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        
        // Check if table exists
        if ($db->tableExists('availability')) {
            CLI::write('Availability table already exists!', 'yellow');
            return;
        }

        CLI::write('Creating availability table...', 'green');
        
        $fields = [
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
        ];

        try {
            $this->forge->addField($fields);
            $this->forge->addPrimaryKey('availability_id');
            $this->forge->addKey('user_id');
            $this->forge->addKey('available_date');
            $this->forge->addKey(['user_id', 'available_date']);
            $this->forge->createTable('availability', true);
            
            CLI::write('Availability table created successfully!', 'green');
        } catch (\Exception $e) {
            CLI::write('Error creating table: ' . $e->getMessage(), 'red');
            return;
        }
    }
}

