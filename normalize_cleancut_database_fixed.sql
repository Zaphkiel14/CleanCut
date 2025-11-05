-- CleanCut Database Normalization Script (Fixed)
-- Import this file directly into phpMyAdmin to normalize your existing database

USE `cleancut`;

-- 1. Add missing columns to earnings table (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'earnings' 
     AND COLUMN_NAME = 'barber_id') = 0,
    'ALTER TABLE `earnings` ADD COLUMN `barber_id` int(11) UNSIGNED NOT NULL AFTER `appointment_id`',
    'SELECT "barber_id column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'earnings' 
     AND COLUMN_NAME = 'service_id') = 0,
    'ALTER TABLE `earnings` ADD COLUMN `service_id` int(11) UNSIGNED NOT NULL AFTER `barber_id`',
    'SELECT "service_id column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Update earnings with barber and service info from appointments
UPDATE earnings e 
JOIN appointments a ON e.appointment_id = a.appointment_id 
SET e.barber_id = a.barber_id, e.service_id = a.service_id;

-- 3. Add foreign key constraints for earnings (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'earnings' 
     AND CONSTRAINT_NAME = 'fk_earnings_barber') = 0,
    'ALTER TABLE `earnings` ADD CONSTRAINT `fk_earnings_barber` FOREIGN KEY (`barber_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_earnings_barber constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'earnings' 
     AND CONSTRAINT_NAME = 'fk_earnings_service') = 0,
    'ALTER TABLE `earnings` ADD CONSTRAINT `fk_earnings_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE',
    'SELECT "fk_earnings_service constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Add missing indexes for earnings (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'earnings' 
     AND INDEX_NAME = 'idx_barber') = 0,
    'ALTER TABLE `earnings` ADD KEY `idx_barber` (`barber_id`)',
    'SELECT "idx_barber index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'earnings' 
     AND INDEX_NAME = 'idx_service') = 0,
    'ALTER TABLE `earnings` ADD KEY `idx_service` (`service_id`)',
    'SELECT "idx_service index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Add missing columns to feedback table (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND COLUMN_NAME = 'customer_id') = 0,
    'ALTER TABLE `feedback` ADD COLUMN `customer_id` int(11) UNSIGNED NOT NULL AFTER `appointment_id`',
    'SELECT "customer_id column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND COLUMN_NAME = 'barber_id') = 0,
    'ALTER TABLE `feedback` ADD COLUMN `barber_id` int(11) UNSIGNED NOT NULL AFTER `customer_id`',
    'SELECT "barber_id column already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 6. Update feedback with customer and barber info from appointments
UPDATE feedback f 
JOIN appointments a ON f.appointment_id = a.appointment_id 
SET f.customer_id = a.customer_id, f.barber_id = a.barber_id;

-- 7. Add foreign keys for feedback (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND CONSTRAINT_NAME = 'fk_feedback_customer') = 0,
    'ALTER TABLE `feedback` ADD CONSTRAINT `fk_feedback_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_feedback_customer constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND CONSTRAINT_NAME = 'fk_feedback_barber') = 0,
    'ALTER TABLE `feedback` ADD CONSTRAINT `fk_feedback_barber` FOREIGN KEY (`barber_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_feedback_barber constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 8. Add unique constraint to prevent duplicate feedback per appointment
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND INDEX_NAME = 'unique_appointment_feedback') = 0,
    'ALTER TABLE `feedback` ADD UNIQUE KEY `unique_appointment_feedback` (`appointment_id`)',
    'SELECT "unique_appointment_feedback constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 9. Add unique constraint to haircut_history
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history' 
     AND INDEX_NAME = 'unique_appointment_history') = 0,
    'ALTER TABLE `haircut_history` ADD UNIQUE KEY `unique_appointment_history` (`appointment_id`)',
    'SELECT "unique_appointment_history constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 10. Add missing indexes for performance (only if they don't exist)
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND INDEX_NAME = 'idx_customer') = 0,
    'ALTER TABLE `feedback` ADD KEY `idx_customer` (`customer_id`)',
    'SELECT "idx_customer index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND INDEX_NAME = 'idx_barber') = 0,
    'ALTER TABLE `feedback` ADD KEY `idx_barber` (`barber_id`)',
    'SELECT "idx_barber index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND INDEX_NAME = 'idx_rating') = 0,
    'ALTER TABLE `feedback` ADD KEY `idx_rating` (`rating`)',
    'SELECT "idx_rating index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 11. Add missing indexes to appointments
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'appointments' 
     AND INDEX_NAME = 'idx_datetime') = 0,
    'ALTER TABLE `appointments` ADD KEY `idx_datetime` (`appointment_date`, `appointment_time`)',
    'SELECT "idx_datetime index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 12. Add missing indexes to users
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'users' 
     AND INDEX_NAME = 'idx_role') = 0,
    'ALTER TABLE `users` ADD KEY `idx_role` (`role`)',
    'SELECT "idx_role index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'users' 
     AND INDEX_NAME = 'idx_active') = 0,
    'ALTER TABLE `users` ADD KEY `idx_active` (`is_active`)',
    'SELECT "idx_active index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 13. Add missing indexes to services
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'services' 
     AND INDEX_NAME = 'idx_active') = 0,
    'ALTER TABLE `services` ADD KEY `idx_active` (`is_active`)',
    'SELECT "idx_active index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 14. Add missing indexes to schedules
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'schedules' 
     AND INDEX_NAME = 'unique_user_day') = 0,
    'ALTER TABLE `schedules` ADD UNIQUE KEY `unique_user_day` (`user_id`, `day_of_week`)',
    'SELECT "unique_user_day constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 15. Add missing indexes to messages
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'messages' 
     AND INDEX_NAME = 'idx_read') = 0,
    'ALTER TABLE `messages` ADD KEY `idx_read` (`is_read`)',
    'SELECT "idx_read index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'messages' 
     AND INDEX_NAME = 'idx_created') = 0,
    'ALTER TABLE `messages` ADD KEY `idx_created` (`created_at`)',
    'SELECT "idx_created index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 16. Add missing indexes to social_feed
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'social_feed' 
     AND INDEX_NAME = 'idx_type') = 0,
    'ALTER TABLE `social_feed` ADD KEY `idx_type` (`post_type`)',
    'SELECT "idx_type index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'social_feed' 
     AND INDEX_NAME = 'idx_public') = 0,
    'ALTER TABLE `social_feed` ADD KEY `idx_public` (`is_public`)',
    'SELECT "idx_public index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'social_feed' 
     AND INDEX_NAME = 'idx_created') = 0,
    'ALTER TABLE `social_feed` ADD KEY `idx_created` (`created_at`)',
    'SELECT "idx_created index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 17. Add missing foreign keys for shops
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'shops' 
     AND CONSTRAINT_NAME = 'fk_shops_owner') = 0,
    'ALTER TABLE `shops` ADD CONSTRAINT `fk_shops_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_shops_owner constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 18. Add missing indexes to employees
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'employees' 
     AND INDEX_NAME = 'unique_user_shop') = 0,
    'ALTER TABLE `employees` ADD UNIQUE KEY `unique_user_shop` (`user_id`, `shop_id`)',
    'SELECT "unique_user_shop constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'employees' 
     AND INDEX_NAME = 'idx_status') = 0,
    'ALTER TABLE `employees` ADD KEY `idx_status` (`status`)',
    'SELECT "idx_status index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 19. Add missing indexes to haircut_history
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history' 
     AND INDEX_NAME = 'idx_rating') = 0,
    'ALTER TABLE `haircut_history` ADD KEY `idx_rating` (`customer_rating`)',
    'SELECT "idx_rating index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 20. Add missing indexes to haircut_history_services
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history_services' 
     AND INDEX_NAME = 'idx_service') = 0,
    'ALTER TABLE `haircut_history_services` ADD KEY `idx_service` (`service_id`)',
    'SELECT "idx_service index already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 21. Add missing foreign keys for messages
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'messages' 
     AND CONSTRAINT_NAME = 'fk_messages_sender') = 0,
    'ALTER TABLE `messages` ADD CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_messages_sender constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'messages' 
     AND CONSTRAINT_NAME = 'fk_messages_receiver') = 0,
    'ALTER TABLE `messages` ADD CONSTRAINT `fk_messages_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_messages_receiver constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 22. Add missing foreign keys for social_feed
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'social_feed' 
     AND CONSTRAINT_NAME = 'fk_social_feed_user') = 0,
    'ALTER TABLE `social_feed` ADD CONSTRAINT `fk_social_feed_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_social_feed_user constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 23. Add missing foreign keys for schedules
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'schedules' 
     AND CONSTRAINT_NAME = 'fk_schedules_user') = 0,
    'ALTER TABLE `schedules` ADD CONSTRAINT `fk_schedules_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_schedules_user constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 24. Add missing foreign keys for haircut_history
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history' 
     AND CONSTRAINT_NAME = 'fk_haircut_history_customer') = 0,
    'ALTER TABLE `haircut_history` ADD CONSTRAINT `fk_haircut_history_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_haircut_history_customer constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history' 
     AND CONSTRAINT_NAME = 'fk_haircut_history_barber') = 0,
    'ALTER TABLE `haircut_history` ADD CONSTRAINT `fk_haircut_history_barber` FOREIGN KEY (`barber_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_haircut_history_barber constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history' 
     AND CONSTRAINT_NAME = 'fk_haircut_history_appointment') = 0,
    'ALTER TABLE `haircut_history` ADD CONSTRAINT `fk_haircut_history_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE',
    'SELECT "fk_haircut_history_appointment constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 25. Add missing foreign keys for services
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'services' 
     AND CONSTRAINT_NAME = 'fk_services_shop') = 0,
    'ALTER TABLE `services` ADD CONSTRAINT `fk_services_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`) ON DELETE CASCADE',
    'SELECT "fk_services_shop constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 26. Add missing foreign keys for haircut_history_services
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history_services' 
     AND CONSTRAINT_NAME = 'fk_haircut_history_services_history') = 0,
    'ALTER TABLE `haircut_history_services` ADD CONSTRAINT `fk_haircut_history_services_history` FOREIGN KEY (`history_id`) REFERENCES `haircut_history` (`history_id`) ON DELETE CASCADE',
    'SELECT "fk_haircut_history_services_history constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'haircut_history_services' 
     AND CONSTRAINT_NAME = 'fk_haircut_history_services_service') = 0,
    'ALTER TABLE `haircut_history_services` ADD CONSTRAINT `fk_haircut_history_services_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE',
    'SELECT "fk_haircut_history_services_service constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 27. Add missing foreign keys for employees
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'employees' 
     AND CONSTRAINT_NAME = 'fk_employees_user') = 0,
    'ALTER TABLE `employees` ADD CONSTRAINT `fk_employees_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_employees_user constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'employees' 
     AND CONSTRAINT_NAME = 'fk_employees_shop') = 0,
    'ALTER TABLE `employees` ADD CONSTRAINT `fk_employees_shop` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`shop_id`) ON DELETE SET NULL',
    'SELECT "fk_employees_shop constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 28. Add missing foreign keys for feedback
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'feedback' 
     AND CONSTRAINT_NAME = 'fk_feedback_appointment') = 0,
    'ALTER TABLE `feedback` ADD CONSTRAINT `fk_feedback_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE',
    'SELECT "fk_feedback_appointment constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 29. Add missing foreign keys for earnings
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'earnings' 
     AND CONSTRAINT_NAME = 'fk_earnings_appointment') = 0,
    'ALTER TABLE `earnings` ADD CONSTRAINT `fk_earnings_appointment` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE',
    'SELECT "fk_earnings_appointment constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 30. Add missing foreign keys for appointments
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'appointments' 
     AND CONSTRAINT_NAME = 'fk_appointments_customer') = 0,
    'ALTER TABLE `appointments` ADD CONSTRAINT `fk_appointments_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_appointments_customer constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'appointments' 
     AND CONSTRAINT_NAME = 'fk_appointments_barber') = 0,
    'ALTER TABLE `appointments` ADD CONSTRAINT `fk_appointments_barber` FOREIGN KEY (`barber_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE',
    'SELECT "fk_appointments_barber constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
     WHERE TABLE_SCHEMA = 'cleancut' 
     AND TABLE_NAME = 'appointments' 
     AND CONSTRAINT_NAME = 'fk_appointments_service') = 0,
    'ALTER TABLE `appointments` ADD CONSTRAINT `fk_appointments_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`) ON DELETE CASCADE',
    'SELECT "fk_appointments_service constraint already exists" as message'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Verification queries to check normalization
SELECT 'Database normalization completed successfully!' as status;

-- Check foreign key relationships
SELECT 
    TABLE_NAME,
    COLUMN_NAME,
    CONSTRAINT_NAME,
    REFERENCED_TABLE_NAME,
    REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
WHERE REFERENCED_TABLE_SCHEMA = 'cleancut' 
AND REFERENCED_TABLE_NAME IS NOT NULL
ORDER BY TABLE_NAME, COLUMN_NAME;

-- Check data integrity
SELECT 'Appointments with valid relationships:' as check_type, COUNT(*) as count
FROM appointments a 
JOIN users u1 ON a.customer_id = u1.user_id 
JOIN users u2 ON a.barber_id = u2.user_id 
JOIN services s ON a.service_id = s.service_id;

SELECT 'Earnings with valid relationships:' as check_type, COUNT(*) as count
FROM earnings e 
JOIN appointments a ON e.appointment_id = a.appointment_id
JOIN users u ON e.barber_id = u.user_id
JOIN services s ON e.service_id = s.service_id;
