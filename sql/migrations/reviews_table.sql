-- Add reviews table
USE `grocery_store_dev`;

-- REVIEWS table
DROP TABLE IF EXISTS `review`;
CREATE TABLE `review` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `rating` INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  `content` TEXT NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_review_user` (`user_id`),
  CONSTRAINT `fk_review_user`
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 