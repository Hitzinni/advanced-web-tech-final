-- Add 'received' status to orders table
ALTER TABLE `orders` 
MODIFY COLUMN `status` ENUM('pending', 'processing', 'shipped', 'delivered', 'received', 'cancelled') NOT NULL DEFAULT 'pending';

-- Update any existing orders that might need this status
-- This is empty as we don't want to automatically change any existing orders 