-- Initialize cart data for testing
USE `grocery_store_dev`;

-- First, clear any existing cart data
DELETE FROM `cart_item`;
DELETE FROM `cart`;

-- Insert cart entries for demo user (ID 1) and Hitinjiv (ID 2)
INSERT INTO `cart` (`user_id`) VALUES 
(1), -- Demo User
(2); -- Hitinjiv

-- Get the cart IDs we just created
SET @demo_cart_id = (SELECT `id` FROM `cart` WHERE `user_id` = 1 LIMIT 1);
SET @hitinjiv_cart_id = (SELECT `id` FROM `cart` WHERE `user_id` = 2 LIMIT 1);

-- Add items to Demo User's cart
INSERT INTO `cart_item` (`cart_id`, `product_id`, `quantity`) VALUES
(@demo_cart_id, 1, 2), -- 2 Potatoes
(@demo_cart_id, 3, 1), -- 1 Broccoli
(@demo_cart_id, 5, 3); -- 3 Chicken

-- Add items to Hitinjiv's cart
INSERT INTO `cart_item` (`cart_id`, `product_id`, `quantity`) VALUES
(@hitinjiv_cart_id, 2, 1), -- 1 Carrots
(@hitinjiv_cart_id, 4, 4), -- 4 Tomatoes
(@hitinjiv_cart_id, 5, 2); -- 2 Chicken

-- Verify the data
SELECT 'Cart table:' AS '';
SELECT * FROM `cart`;

SELECT 'Cart items:' AS '';
SELECT ci.*, p.name, p.price, p.category, p.image_url
FROM `cart_item` ci
JOIN `product` p ON ci.product_id = p.id
ORDER BY ci.cart_id, ci.id; 