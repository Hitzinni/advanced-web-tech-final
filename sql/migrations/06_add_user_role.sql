-- Add role column to user table
ALTER TABLE `user` 
ADD COLUMN `role` ENUM('customer', 'manager') NOT NULL DEFAULT 'customer' AFTER `email`;

-- For testing: Update the admin user based on email
UPDATE `user` SET `role` = 'manager' WHERE `email` = 'admin@admin.com'; 