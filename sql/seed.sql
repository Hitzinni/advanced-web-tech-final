USE `x8m18`;

START TRANSACTION;

-- Sample products for all categories
INSERT INTO `product` (`category`,`name`,`price`,`image_url`) VALUES
  -- Vegetables
  ('Vegetables','Potato', 1.20, 'public/images/products/potato.jpg'),
  ('Vegetables','Carrots',1.50, 'public/images/products/carrots.jpg'),
  ('Vegetables','Broccoli',2.00, 'public/images/products/broccoli.jpg'),
  ('Vegetables','Tomato',1.00, 'public/images/products/tomato.jpg'),
  
  -- Meat products
  ('Meat','Chicken',4.50, 'public/images/products/chicken.jpg'),
  ('Meat','Fish',   5.00, 'public/images/products/fish.jpg'),
  ('Meat','Beef',   6.00, 'public/images/products/beef.jpg'),
  ('Meat','Pork',   5.50, 'public/images/products/pork.jpg'),
  
  -- Fruits products (new)
  ('Fruits','Apples', 2.20, 'public/images/products/apples.jpg'),
  ('Fruits','Bananas', 1.80, 'public/images/products/bananas.jpg'),
  ('Fruits','Oranges', 2.50, 'public/images/products/oranges.jpg'),
  ('Fruits','Strawberries', 3.50, 'public/images/products/strawberries.jpg'),
  
  -- Bakery products (new)
  ('Bakery','Bread', 2.00, 'public/images/products/bread.jpg'),
  ('Bakery','Croissants', 2.50, 'public/images/products/croissants.jpg'),
  ('Bakery','Muffins', 3.00, 'public/images/products/muffins.jpg'),
  ('Bakery','Bagels', 2.75, 'public/images/products/bagels.jpg'),
  
  -- Dairy products (new)
  ('Dairy','Milk', 2.50, 'public/images/products/milk.jpg'),
  ('Dairy','Cheese', 4.00, 'public/images/products/cheese.jpg'),
  ('Dairy','Yogurt', 1.75, 'public/images/products/yogurt.jpg'),
  ('Dairy','Butter', 3.25, 'public/images/products/butter.jpg');

-- Demo user (password = "Password123", bcrypt hash)
INSERT INTO `user` (`name`,`phone`,`email`,`password_hash`) VALUES
  ('Demo User','0712345678','demo@example.com',
   '$2y$12$e0NRpW8zaqjd3XlFZXyBO.yHC8eiZrDtYPaLdvJvxS4kilI0BRF9u');

COMMIT;
