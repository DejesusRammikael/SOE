-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2025 at 02:35 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ezorderdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `stall_id` int(11) DEFAULT NULL,
  `price` double DEFAULT NULL,
  `product_path` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `available` tinyint(4) DEFAULT 1,
  `category` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `stall_id`, `price`, `product_path`, `description`, `is_featured`, `available`, `category`) VALUES
(1, 'chizdawg', NULL, 100, 'uploads/products/683bc228b7724_Bush.jpg', 'ayoko', 0, 1, NULL),
(2, 'chizdawg', NULL, 100, 'uploads/products/683bc2518861e_Bush.jpg', 'aaaaa', 0, 1, NULL),
(3, 'chizdawg', NULL, 100, 'uploads/products/683bc259a6998_Bush.jpg', 'aaaaa', 0, 1, NULL),
(4, 'chizdawg', 1, 100, 'uploads/products/683bc7d987fd6_Bush.jpg', 'ayoko', 1, 1, NULL),
(5, 'chizdawg', 1, 100, 'uploads/products/683bd58aec0c7_Bush.jpg', 'ayoko', 1, 1, NULL),
(6, 'Bombardino Crocodillo', 2, 100, 'uploads/products/683c0b1b77f42_texture fence.jpg', 'tae', 1, 1, NULL),
(7, 'bombombini', 3, 500, 'uploads/products/683c2939668fb_Door.jpg', 'ayoko na neto', 1, 1, NULL),
(8, 'cccd', 3, 300, 'uploads/products/683c296f5199f_Bush.jpg', 'sdasdsad', 1, 1, NULL),
(9, 'dd', 3, 300, 'uploads/products/683c296f53013_Bush.jpg', 'asdsadasdad', 0, 1, NULL),
(10, 'aaaa', 3, 300, 'uploads/products/683c296f5615a_Bush.jpg', 'asdsad', 1, 1, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `stall_id` (`stall_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`stall_id`) REFERENCES `stall` (`stall_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
