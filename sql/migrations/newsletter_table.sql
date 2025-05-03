-- Add newsletter subscribers table
USE `grocery_store_dev`;

-- NEWSLETTER_SUBSCRIBERS table
DROP TABLE IF EXISTS `newsletter_subscribers`;
CREATE TABLE `newsletter_subscribers` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(120) NOT NULL,
  `subscribed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('active', 'unsubscribed') NOT NULL DEFAULT 'active',
  UNIQUE KEY `uk_newsletter_email` (`email`),
  INDEX `idx_newsletter_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 