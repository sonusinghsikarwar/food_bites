-- Database Schema for Modern Fast Food Restaurant & POS Billing System

CREATE DATABASE IF NOT EXISTS `food_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `food_db`;

-- 1. admins
CREATE TABLE IF NOT EXISTS `admins` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'staff') DEFAULT 'staff',
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. users
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `address` TEXT DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. products
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `discount_price` DECIMAL(10,2) DEFAULT 0.00,
  `stock_qty` INT DEFAULT 100,
  `is_featured` TINYINT(1) DEFAULT 0,
  `image` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. carts
CREATE TABLE IF NOT EXISTS `carts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `session_id` VARCHAR(100) DEFAULT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `order_no` VARCHAR(50) NOT NULL UNIQUE,
  `customer_name` VARCHAR(100) DEFAULT NULL,
  `customer_phone` VARCHAR(20) DEFAULT NULL,
  `order_type` ENUM('dine_in', 'take_away', 'delivery') DEFAULT 'delivery',
  `table_no` VARCHAR(10) DEFAULT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `tax_amount` DECIMAL(10,2) DEFAULT 0.00,
  `grand_total` DECIMAL(10,2) NOT NULL,
  `payment_method` ENUM('cash', 'card', 'upi', 'cod') DEFAULT 'cod',
  `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
  `status` ENUM('pending', 'preparing', 'completed', 'cancelled') DEFAULT 'pending',
  `address` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `payment_method` VARCHAR(50) NOT NULL,
  `transaction_no` VARCHAR(100) DEFAULT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('success', 'failed', 'pending') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. coupons
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `type` ENUM('fixed', 'percentage') DEFAULT 'percentage',
  `value` DECIMAL(10,2) NOT NULL,
  `min_cart_amount` DECIMAL(10,2) DEFAULT 0.00,
  `expiry_date` DATE NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. banners
CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) DEFAULT NULL,
  `image` VARCHAR(255) NOT NULL,
  `link` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. gallery
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `image` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. contact_messages
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `subject` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('read', 'unread') DEFAULT 'unread',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. settings
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key_name` VARCHAR(100) NOT NULL UNIQUE,
  `key_value` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- SEED DATA --

-- Settings
INSERT INTO `settings` (`key_name`, `key_value`) VALUES
('restaurant_name', 'Crispy Bytes'),
('contact_email', 'info@crispybytes.com'),
('contact_phone', '+91 98765 43210'),
('address', '101, Food Court, MG Road, Indore, India'),
('currency_symbol', '₹'),
('tax_percent', '5.00'),
('opening_hours', 'Daily 11:00 AM - 11:00 PM')
ON DUPLICATE KEY UPDATE `key_value` = VALUES(`key_value`);

-- Admins (password is 'admin123')
INSERT INTO `admins` (`name`, `username`, `email`, `password`, `role`, `status`) VALUES
('Super Admin', 'admin', 'admin@crispybytes.com', '$2y$10$w095tX.zR6aH8V7pA1YlM.WJc48aJigXo03mRoxP9XJ5k2Y2l0eC.', 'admin', 'active');

-- Users (password is 'user123')
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `address`) VALUES
('Rahul Sharma', 'rahul@gmail.com', '9876543211', '$2y$10$v7g91Q/N5wM/bI9HlU.eNef.8v6v1GogZ78mKqC9U5k3Y2l0eC.', 'Flat 202, Heights Apartment, Indore');

-- Categories
INSERT INTO `categories` (`id`, `name`, `slug`) VALUES
(1, 'Burgers', 'burger'),
(2, 'Pizza', 'pizza'),
(3, 'Sandwich', 'sandwich'),
(4, 'Fries', 'fries'),
(5, 'Pasta', 'pasta'),
(6, 'Drinks', 'drinks'),
(7, 'Desserts', 'desserts');

-- Products
INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `discount_price`, `stock_qty`, `is_featured`, `image`) VALUES
(1, 1, 'Classic Cheese Burger', 'A premium grilled chicken or vegetable patty with double cheddar cheese, lettuce, tomatoes, and spicy house sauce.', 149.00, 129.00, 150, 1, 'burger1.png'),
(2, 1, 'Loaded Crispy Chicken Burger', 'Crispy deep-fried chicken breast, pickled cucumbers, cabbage slaw, and delicious honey mustard dressing.', 189.00, 169.00, 80, 1, 'burger2.png'),
(3, 2, 'Double Cheese Margherita Pizza', 'Hand-stretched sourdough crust loaded with premium Italian marinara sauce, fresh mozzarella cheese, basil, and olive oil.', 299.00, 249.00, 50, 1, 'pizza1.png'),
(4, 2, 'Spicy Chicken Pepperoni Pizza', 'Smoky chicken pepperoni slices, dynamic jalapenos, mozzarella, and chili flakes baked in a stone oven.', 399.00, 349.00, 45, 1, 'pizza2.png'),
(5, 3, 'Club Sandwich', 'Three layers of toasted bread packed with grilled egg, smoked turkey, fresh tomatoes, and melting Swiss cheese.', 129.00, 99.00, 100, 0, 'sandwich1.png'),
(6, 4, 'Spicy Peri Peri Fries', 'Golden skin-on potato fries tossed in hot African peri-peri spices, served with dynamic garlic mayo.', 99.00, 89.00, 200, 1, 'fries1.png'),
(7, 5, 'White Sauce Alfredo Pasta', 'Penne pasta tossed in rich butter, fresh cream, grated parmesan, loaded with button mushrooms and green peas.', 199.00, 179.00, 60, 0, 'pasta1.png'),
(8, 6, 'Mint Mojito', 'A refreshing classic drink with fresh mint leaves, lime juice, brown sugar, and sparkling soda water.', 89.00, 79.00, 250, 0, 'drinks1.png'),
(9, 7, 'Sizzling Chocolate Brownie', 'Hot chocolate fudge brownie served on a sizzling hot iron plate with a scoop of vanilla ice cream.', 159.00, 139.00, 40, 1, 'dessert1.png');

-- Coupons
INSERT INTO `coupons` (`code`, `type`, `value`, `min_cart_amount`, `expiry_date`) VALUES
('CRISPY20', 'percentage', 20.00, 300.00, '2027-12-31'),
('FLAT50', 'fixed', 50.00, 250.00, '2027-12-31');

-- Banners
INSERT INTO `banners` (`title`, `image`, `link`) VALUES
('Super Sizzling Burgers - Flat 20% Off', 'banner1.png', 'menu.php?category=burger'),
('Stone Oven Pizza - Free Drink on Large', 'banner2.png', 'menu.php?category=pizza');

-- Gallery
INSERT INTO `gallery` (`image`, `title`) VALUES
('gallery1.png', 'Our Stone Pizza Oven'),
('gallery2.png', 'Chef Crafting Burgers'),
('gallery3.png', 'Dining Area Ambience');
