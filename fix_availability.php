<?php
// Quick fix to create availability table
require 'vendor/autoload.php';

$mysqli = new mysqli('localhost', 'root', '', 'cleancut');

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS `availability` (
  `availability_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `available_date` date NOT NULL,
  `available_time` time NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`availability_id`),
  KEY `user_id` (`user_id`),
  KEY `available_date` (`available_date`),
  KEY `user_date` (`user_id`, `available_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($mysqli->query($sql)) {
    echo "✓ Availability table created successfully!\n";
} else {
    echo "✗ Error: " . $mysqli->error . "\n";
}

$mysqli->close();

