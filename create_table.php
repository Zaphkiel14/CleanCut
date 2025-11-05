<?php

/**
 * Quick script to create the availability table
 * Run this once: php create_table.php
 */

// Load CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();

require_once rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'Boot.php';

$app = Config\Services::codeigniter();
$app->initialize();

// Create the table
$db = \Config\Database::connect();

if ($db->tableExists('availability')) {
    echo "✓ Availability table already exists!\n";
    exit;
}

try {
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

    $forge = \Config\Database::forge();
    $forge->addField($fields);
    $forge->addPrimaryKey('availability_id');
    $forge->addKey('user_id');
    $forge->addKey('available_date');
    $forge->addKey(['user_id', 'available_date']);
    $forge->createTable('availability', true);
    
    echo "✓ Availability table created successfully!\n";
    
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}

