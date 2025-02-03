-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 03, 2025 at 04:22 PM
-- Server version: 10.4.10-MariaDB
-- PHP Version: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simple_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_keys`
--

CREATE TABLE `admin_keys` (
  `key_id` int(11) NOT NULL,
  `access_key` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin_keys`
--

INSERT INTO `admin_keys` (`key_id`, `access_key`, `created_at`) VALUES
(1, '$2y$10$your_secure_key_hash_here', '2025-02-02 00:25:50');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','sold','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paypal_email` varchar(100) NOT NULL,
  `seller_address` text DEFAULT NULL,
  `seller_address_iv` varchar(64) DEFAULT NULL,
  `seller_contact_email` varchar(100) DEFAULT NULL,
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` int(14) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `sku`, `seller_id`, `title`, `description`, `price`, `status`, `created_at`, `paypal_email`, `seller_address`, `seller_address_iv`, `seller_contact_email`, `id`, `user_id`, `amount`, `quantity`, `image`) VALUES
(4, '232e', 0, 'Test', 'qw4terwt', '3.00', 'active', '2025-02-02 05:39:41', 'Vikerus1@gmail.com', NULL, NULL, NULL, 0, 0, 0, 1, NULL),
(8, '22', 0, 'Beautiful Comments', 'dawdawd', '2.00', 'active', '2025-02-02 14:25:04', 'Vikerus1@gmail.com', NULL, NULL, NULL, 1, 0, 0, 1, NULL),
(9, '2222', 0, 'Beautiful Test', 'test', '1.00', 'active', '2025-02-03 01:12:22', 'Vikerus1@gmail.com', NULL, NULL, NULL, 2, 0, 0, 1, NULL),
(10, '1', 0, 'Minecraft Mod', 'Purchase a mod', '2.00', 'active', '2025-02-03 01:17:03', 'Vikerus1@gmail.com', NULL, NULL, NULL, 3, 0, 0, 1, NULL),
(11, '112', 0, 'Beautiful Test', 'fasdfasdfasdfasdf', '12.00', 'active', '2025-02-03 01:22:53', 'Vikerus1@gmail.com', NULL, NULL, NULL, 4, 0, 0, 17, NULL),
(12, '11', 0, 'Beautiful Test', 'ewfasdfasdf', '1.00', 'active', '2025-02-03 01:23:27', 'Vikerus1@gmail.com', NULL, NULL, NULL, 5, 0, 0, 11, NULL),
(13, '1111', 0, '45234', '22', '1.00', 'active', '2025-02-03 06:12:58', 'Vikerus1@gmail.com', NULL, NULL, NULL, 6, 0, 0, 11, NULL),
(14, 'mm', 0, 'Minecraft Mod2', 'www', '2.00', 'active', '2025-02-03 06:45:58', 'Vikerus1@gmail.com', NULL, NULL, NULL, 7, 0, 0, 3, NULL),
(17, 'memlpt', 0, 'Beautiful Testd', 'Memory for sale', '12.00', 'active', '2025-02-03 15:07:04', 'Vikerus1@gmail.com', NULL, NULL, NULL, 8, 0, 0, 1, NULL),
(18, '2', 0, 'Beautiful Test', 'mobo', '1.00', 'active', '2025-02-03 15:07:53', 'Vikerus1@gmail.com', NULL, NULL, NULL, 9, 0, 0, 1, NULL),
(19, '3', 0, 'Beautiful Test', 'another test', '1.00', 'active', '2025-02-03 15:13:59', 'Vikerus1@gmail.com', NULL, NULL, NULL, 10, 0, 0, 1, 'uploads/67a0dd376972c_22.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

CREATE TABLE `listings` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `paypal_email` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `from_user` varchar(255) NOT NULL,
  `to_user` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `from_user`, `to_user`, `message`, `created_at`) VALUES
(1, 'Bikerus', 'Bikerus', 'hi', '2025-02-03 14:47:39'),
(2, 'Bikerus', 'Bikerus', 'message', '2025-02-03 15:18:24'),
(3, 'Test', 'Test', 'Hello', '2025-02-03 15:21:24');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `buyer_email` varchar(255) NOT NULL,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `tracking_number` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(192) NOT NULL,
  `id` int(11) NOT NULL,
  `seller_id` varchar(190) NOT NULL,
  `paypal_email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `created_at`, `password`, `id`, `seller_id`, `paypal_email`) VALUES
(1, 'Bikerus', 'Bikerus111@yahoo.com', '', '2025-02-02 05:25:39', '$2y$10$phY5EvHVK77XavwwIWdTnuzwL1gr36bgeXKJKmSu1JXuvBwumavO6', 0, '', ''),
(2, 'Vikerus', 'vikerus1@gmail.com', '', '2025-02-03 15:18:55', '$2y$10$aw2ba2o9UeP0ccuPHmvdxuC6GLD/LY2LE9UwOnjlAjTAzMHUw3km2', 0, '', ''),
(3, 'Test', '100@gmail.com', '', '2025-02-03 15:21:02', '$2y$10$xVz/FOhHLiyFCyXbO4OcVe/h5NtuRs1Q6MqWqUoA2zPgmRomyzT.2', 0, '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_keys`
--
ALTER TABLE `admin_keys`
  ADD PRIMARY KEY (`key_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `id` (`id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `idx_sku` (`sku`);

--
-- Indexes for table `listings`
--
ALTER TABLE `listings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`),
  ADD KEY `sku` (`sku`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_keys`
--
ALTER TABLE `admin_keys`
  MODIFY `key_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `listings`
--
ALTER TABLE `listings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`sku`) REFERENCES `items` (`sku`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
