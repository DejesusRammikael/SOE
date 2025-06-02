-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 01, 2025 at 04:56 PM
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
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `id` int(11) NOT NULL,
  `order_name` varchar(100) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `stall_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `ordered_by` varchar(100) NOT NULL,
  `order_time` time NOT NULL,
  `order_date` date NOT NULL,
  `status` enum('preparing','completed','pending') DEFAULT 'pending',
  `payment_status` enum('paid','unpaid') DEFAULT 'unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order`
--

INSERT INTO `order` (`id`, `order_name`, `item_quantity`, `stall_name`, `price`, `ordered_by`, `order_time`, `order_date`, `status`, `payment_status`, `created_at`) VALUES
(1, 'Burger', 2, 'Main Stall', 99.50, 'Juan Dela Cruz', '12:30:00', '2025-06-01', 'pending', 'unpaid', '2025-06-01 14:55:49');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
