-- ============================================
-- Create availability table for date-specific time slots
-- Run this in phpMyAdmin under the 'cleancut' database
-- ============================================

CREATE TABLE IF NOT EXISTS `availability` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Verify table creation
SELECT 'Availability table created successfully!' as message;

