-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2025 at 12:39 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `phone` varchar(191) DEFAULT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','archived') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `position`, `address`, `password`, `phone`, `created_at`, `status`) VALUES
(11, 'Loyld Estardop', 'johnloyd30estardo@yahoo.com', 'Owner', 'Gloc. 9 Lot. 18', '$2y$10$DkXo0P8sgDL7TJCSKsaQM.OWMHDgrMZ260ligObj9SjJ6tS3Dhw9.', '123', '2025-09-25', 'active'),
(12, 'Justin Ibya', 'justinfernandoibaya@gmail.com', 'Maniger', '', '$2y$10$tgMWHMJ9GDfM03.4w2jAJuEVIHQU3wPxt64F1anZPfhfisdaIxiZ.', '123', '2025-09-25', 'active'),
(13, 'Rose Malone', 'javier.rosealessandra@dfcamclp.edu.ph', NULL, NULL, '$2y$10$CPBVIl6UpgMHeL7T3YkY1uYuar6RW2vdGH/dJODP8yGm/qFLtM8H2', '123', '2025-09-25', 'active'),
(14, 'xd', 'noyalgeorge80@gmail.com', 'CEO', 'Gloc 3', '$2y$10$cW7wxKRSutYJku.5ybAr2u0x/QSzLTBOH8L.zz3SqmWaGtIJzFRx.', '9123', '2025-10-01', 'active'),
(15, 'Mozkov', '123@gmail.com', '123', '123', '$2y$10$Hd4RHfYmpPT.Wq4OT9aNK.6YTsPzfRH5T7uCgyNk8HIdFNZMiYwtG', '123', '2025-10-04', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` mediumtext DEFAULT NULL,
  `status` enum('active','archived') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `status`) VALUES
(15, 'Jewelry', 'XD', 'active'),
(16, 'Medicine', 'sobra\r\n', 'active'),
(17, '123', '123', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `message`, `is_read`, `created_at`) VALUES
(1, 'New order #11 placed by Loyld.', 1, '2025-10-10 13:22:06'),
(2, 'New order #12 placed by Rose Malone.', 0, '2025-10-10 13:38:08'),
(3, 'New order #13 placed by Rose Malone.', 0, '2025-10-14 17:34:34'),
(4, 'New order #14 placed by kendis.', 0, '2025-10-26 07:14:49'),
(5, 'New order #15 placed by asd.', 0, '2025-10-26 07:37:42'),
(6, 'New order #16 placed by asdasd.', 0, '2025-10-26 07:40:05'),
(7, 'New order #17 placed by asd.', 0, '2025-10-26 07:45:36'),
(8, 'New order #18 placed by Loyld.', 0, '2025-10-26 07:48:55'),
(9, 'New order #19 placed by 123.', 0, '2025-10-26 07:54:57'),
(10, 'New order #20 placed by 123.', 0, '2025-10-26 07:56:16'),
(11, 'New order #21 placed by jsjsj.', 0, '2025-10-26 08:34:48'),
(12, 'New order #22 placed by ajdhasj.', 0, '2025-10-26 08:42:36'),
(13, 'New order #23 placed by Juan Dela Cruz.', 0, '2025-10-26 09:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','approved','completed','cancelled','archived') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` varchar(50) DEFAULT 'GCash',
  `payment_proof` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_email`, `customer_phone`, `customer_address`, `total_amount`, `status`, `created_at`, `payment_method`, `payment_proof`) VALUES
(1, '123', '123@gmail.com', '123', '123', 123.00, 'pending', '2025-10-10 07:35:45', 'COD', NULL),
(2, '123', '123@gmail.com', '123', '123', 123.00, 'pending', '2025-10-10 07:35:53', 'COD', NULL),
(3, '123', NULL, '123', '123', 246.00, 'pending', '2025-10-10 07:39:59', 'Cash on Delivery', NULL),
(4, '1', NULL, '2', '3', 0.00, 'pending', '2025-10-10 07:41:09', 'Cash on Delivery', NULL),
(5, 'Juan Dela Cruz', 'juan@example.com', '09507762438', 'B3. L18. Camia Dulo St.', 0.00, 'pending', '2025-10-10 07:51:29', 'COD', NULL),
(6, '13213', 'juan@example.com', '213', '123', 123.00, 'pending', '2025-10-10 07:57:15', 'COD', NULL),
(7, '13213', 'juan@example.com', '213', '123', 123.00, 'pending', '2025-10-10 07:57:27', 'COD', NULL),
(8, '13213', 'juan@example.com', '213', '123', 123.00, 'pending', '2025-10-10 07:57:35', 'COD', NULL),
(9, 'Loyld Estardo', NULL, '123', '123213', 123.00, 'pending', '2025-10-10 08:17:07', 'Cash on Delivery', NULL),
(10, 'Loyld', 'loyld30estardo@gmail.com', '2145123', 'B3', 0.00, 'pending', '2025-10-10 13:19:50', 'COD', NULL),
(11, 'Loyld', 'loyld30estardo@gmail.com', '2145123', 'B3', 0.00, 'pending', '2025-10-10 13:22:06', 'COD', NULL),
(12, 'Rose Malone', 'rosekangkangera@yahoo.com', '83139283812983', 'Gloc. 9 Lot. 18', 123.00, 'pending', '2025-10-10 13:38:08', 'COD', NULL),
(13, 'Rose Malone', 'juan@example.com', '83139283812983', '231', 0.00, 'archived', '2025-10-14 17:34:34', 'COD', NULL),
(14, 'kendis', 'rosal@gmail.comm', '9852319', 'RoSALL', 123.00, 'pending', '2025-10-26 07:14:49', 'GCash', NULL),
(15, 'asd', 'asd@gmail.com', 'asd', 'asd', 123.00, 'pending', '2025-10-26 07:37:42', 'GCash', NULL),
(16, 'asdasd', 'asdasd@gmail.com', 'asdasd', '123', 123.00, 'pending', '2025-10-26 07:40:05', 'GCash', NULL),
(17, 'asd', 'juan@example.com', 'asd', 'sad', 123.00, 'pending', '2025-10-26 07:45:36', 'GCash', NULL),
(18, 'Loyld', '123@gmail.com', '123', 'aadas', 0.00, 'pending', '2025-10-26 07:48:55', 'GCash', NULL),
(19, '123', '123@gmail.com', '1231', '213', 0.00, 'pending', '2025-10-26 07:54:57', 'GCash', NULL),
(20, '123', '123@gmail.com', '1231', '213', 0.00, 'pending', '2025-10-26 07:56:16', 'GCash', NULL),
(21, 'jsjsj', 'juan@example.com', '7878', 'sssuus', 123.00, 'pending', '2025-10-26 08:34:48', 'GCash', NULL),
(22, 'ajdhasj', 'asd@gmail.com', '129381293', '1231231', 123.00, 'approved', '2025-10-26 08:42:36', 'GCash', 'proof_1761468156_68fddefc49b00.png'),
(23, 'Juan Dela Cruz', 'loyld30estardo@gmail.com', '095921331', '123123', 123.00, 'pending', '2025-10-26 09:01:20', 'GCash', 'proof_1761469280_68fde360b0113.png');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 8, 1, 123.00, 0.00),
(2, 2, 8, 1, 123.00, 0.00),
(3, 3, 7, 1, 0.00, 0.00),
(4, 3, 8, 2, 123.00, 246.00),
(5, 4, 7, 1, 0.00, 0.00),
(6, 5, 7, 1, 0.00, 0.00),
(7, 6, 8, 1, 123.00, 0.00),
(8, 7, 8, 1, 123.00, 0.00),
(9, 8, 8, 1, 123.00, 0.00),
(10, 9, 7, 1, 0.00, 0.00),
(11, 9, 8, 1, 123.00, 123.00),
(12, 10, 7, 1, 0.00, 0.00),
(13, 11, 7, 1, 0.00, 0.00),
(14, 12, 8, 1, 123.00, 0.00),
(15, 13, 7, 1, 0.00, 0.00),
(16, 14, 8, 1, 123.00, 0.00),
(17, 15, 8, 1, 123.00, 0.00),
(18, 16, 8, 1, 123.00, 0.00),
(19, 17, 8, 1, 123.00, 0.00),
(20, 18, 7, 1, 0.00, 0.00),
(21, 19, 7, 1, 0.00, 0.00),
(22, 20, 7, 1, 0.00, 0.00),
(23, 21, 8, 1, 123.00, 0.00),
(24, 22, 8, 1, 123.00, 0.00),
(25, 23, 8, 1, 123.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` mediumtext NOT NULL,
  `price` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` varchar(10) NOT NULL DEFAULT '0' COMMENT '0=visible,1=hidden',
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `description`, `price`, `quantity`, `image`, `status`, `created_at`) VALUES
(6, 15, '123', 'wqdadaswadwdadasdasdqwewqraegagadfda', 123, 3, 'assets/uploads/products/1761479286.jpeg', 'active', '2025-10-05'),
(7, 15, 'SEIKO WATCH', 'GANDAAAAAAAA', 0, 0, 'assets/uploads/products/1759991608.jpg', 'active', '2025-10-09'),
(8, 15, 'SEIKO WATCH', '123', 123, 2, 'assets/uploads/products/1761479321.jpeg', 'active', '2025-10-09'),
(9, 16, 'asd', 'asd', 123, 123, 'assets/uploads/products/1761483525.jpeg', 'active', '2025-10-09'),
(10, 17, '123', '213213', 123, 123, 'assets/uploads/products/1761483553.jpeg', 'active', '2025-10-09'),
(11, 15, '123', '345', 600, 3, 'assets/uploads/products/1761483574.jpeg', 'active', '2025-10-10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
