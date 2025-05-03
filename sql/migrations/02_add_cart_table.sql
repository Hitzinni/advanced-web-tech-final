-- Add cart tables
USE `x8m18`;

-- CART table
CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_cart_user` (`user_id`),
  CONSTRAINT `fk_cart_user`
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- CART_ITEM table
CREATE TABLE IF NOT EXISTS `cart_item` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
  `added_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_cart_item_cart` (`cart_id`),
  KEY `idx_cart_item_product` (`product_id`),
  CONSTRAINT `fk_cart_item_cart`
    FOREIGN KEY (`cart_id`) REFERENCES `cart`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_cart_item_product`
    FOREIGN KEY (`product_id`) REFERENCES `product`(`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  UNIQUE KEY `uk_cart_product` (`cart_id`, `product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 