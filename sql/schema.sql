-- Drop existing database and recreate for idempotency
DROP DATABASE IF EXISTS `grocery_store_dev`;
CREATE DATABASE `grocery_store_dev`
  DEFAULT CHARACTER SET = utf8mb4
  DEFAULT COLLATE = utf8mb4_unicode_ci;
USE `grocery_store_dev`;

-- PRODUCTS table
DROP TABLE IF EXISTS `product`;
CREATE TABLE `product` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category` ENUM('Vegetables','Fruits','Meat','Bakery','Dairy') NOT NULL,
  `name` VARCHAR(64) NOT NULL,
  `price` DECIMAL(6,2) NOT NULL,
  `image_url` VARCHAR(255) NOT NULL,
  INDEX `idx_product_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- USERS table
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(60) NOT NULL,
  `phone` CHAR(10) NOT NULL,
  `email` VARCHAR(120) NOT NULL,
  `password_hash` CHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_user_email` (`email`),
  INDEX `idx_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ORDERS table
DROP TABLE IF EXISTS `order`;
CREATE TABLE `order` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NOT NULL,
  `price_at_order` DECIMAL(6,2) NOT NULL,
  `ordered_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_order_user` (`user_id`),
  CONSTRAINT `fk_order_user`
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`)
    ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `fk_order_product`
    FOREIGN KEY (`product_id`) REFERENCES `product`(`id`)
    ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
