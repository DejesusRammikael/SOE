-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 27, 2025 at 03:28 PM
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
-- Database: `soe_admin`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
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
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_name`, `item_quantity`, `stall_name`, `price`, `ordered_by`, `order_time`, `order_date`, `status`, `payment_status`, `created_at`) VALUES
(1, 'Siomai Rice', 4, 'Stall A', 50.00, 'John Doe', '10:30:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(2, 'Friend Chicken', 1, 'Stall B', 75.00, 'John Doe', '10:35:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(3, 'Siomai Rice', 2, 'Stall D', 50.00, 'John Doe', '10:40:00', '2025-01-06', 'completed', 'paid', '2025-05-27 04:05:36'),
(4, 'Siomai Rice', 1, 'Stall E', 50.00, 'John Doe', '10:45:00', '2025-01-06', 'completed', 'paid', '2025-05-27 04:05:36'),
(5, 'Ramen', 2, 'Stall B', 120.00, 'John Doe', '10:50:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(6, 'Ramen', 1, 'Stall F', 120.00, 'John Doe', '10:55:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(7, 'Fried Chicken', 3, 'Stall C', 75.00, 'John Doe', '11:00:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(8, 'Ramen', 1, 'Stall B', 120.00, 'John Doe', '11:05:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(9, 'Fried Chicken', 2, 'Stall C', 75.00, 'John Doe', '11:10:00', '2025-01-06', 'completed', 'paid', '2025-05-27 04:05:36'),
(10, 'Siomai Rice', 3, 'Stall G', 50.00, 'John Doe', '11:15:00', '2025-01-06', 'completed', 'paid', '2025-05-27 04:05:36'),
(11, 'Fried Chicken', 4, 'Stall A', 75.00, 'John Doe', '11:20:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(12, 'Siomai Rice', 2, 'Stall A', 50.00, 'John Doe', '11:25:00', '2025-01-06', 'completed', 'paid', '2025-05-27 04:05:36'),
(13, 'Siomai Rice', 3, 'Stall H', 50.00, 'John Doe', '11:30:00', '2025-01-06', 'preparing', 'paid', '2025-05-27 04:05:36'),
(14, 'Water Bottle', 1, 'Stall B', 25.00, 'John Doe', '11:35:00', '2025-01-06', 'completed', 'paid', '2025-05-27 04:05:36');

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `id` int(11) NOT NULL,
  `stall_name` varchar(255) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`id`, `stall_name`, `owner_name`, `contact_no`, `email`, `password`, `status`, `created_at`) VALUES
(1, 'Food Corner A', 'John Smith', '09123456789', 'john.smith@example.com', '$2y$10$YourHashedPasswordHere123', 'active', '2025-05-27 11:43:16'),
(2, 'Snack Haven B', 'Mary Johnson', '09234567890', 'mary.j@example.com', '$2y$10$YourHashedPasswordHere456', 'active', '2025-05-27 11:43:16'),
(3, 'Drinks Paradise C', 'Robert Wilson', '09345678901', 'robert.w@example.com', '$2y$10$YourHashedPasswordHere789', 'inactive', '2025-05-27 11:43:16'),
(4, 'Sweet Treats D', 'Sarah Davis', '09456789012', 'sarah.d@example.com', '$2y$10$YourHashedPasswordHere012', 'active', '2025-05-27 11:43:16'),
(5, 'Local Cuisine E', 'Michael Brown', '09567890123', 'michael.b@example.com', '$2y$10$YourHashedPasswordHere345', 'active', '2025-05-27 11:43:16'),
(6, 'Stall Z', 'Lebron', '09999999', 'test@mail.com', '$2y$10$zn1o.TWHYKe0K9OubOLYRuAWnCwtTu7Hjt.57KybovAKerbKFRtW6', 'active', '2025-05-27 11:44:11'),
(7, 'Tindahan ni aling pureng', 'Pureng', '09157990101', 'test2@mail.com', '$2y$10$qWVPGuwC4z1K14V4XOoooe9KZhQ./VXLFVAVmkZC38/TP2xws3t7O', 'active', '2025-05-27 11:54:56'),
(8, 'Tindahan ni aling', 'Pureng', '09157990103', 'testes@mail.com', '$2y$10$kBy9LWw.N6GSikq5p1SGO./CnqKiWY3SGVMrDiapLQwU95xwrSh/G', 'active', '2025-05-27 12:05:51'),
(9, 'Tindahan ni aling', 'Lebron', '09157990103', 'test4@mail.com', '$2y$10$Ky8ufMr8eLguCRSd6CVvve7UouRkBr41rMQ8pw/InM3QmroQYcCm2', 'active', '2025-05-27 12:07:53');

-- --------------------------------------------------------

--
-- Table structure for table `stall_applications`
--

CREATE TABLE `stall_applications` (
  `id` int(11) NOT NULL,
  `stall_name` varchar(255) NOT NULL,
  `owner_name` varchar(255) NOT NULL,
  `contact_no` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stall_applications`
--

INSERT INTO `stall_applications` (`id`, `stall_name`, `owner_name`, `contact_no`, `email`, `password`, `status`, `created_at`) VALUES
(1, 'Stall Z', 'Lebron', '09999999', 'test@mail.com', '$2y$10$zn1o.TWHYKe0K9OubOLYRuAWnCwtTu7Hjt.57KybovAKerbKFRtW6', 'approved', '2025-05-27 11:35:28'),
(3, 'Tindahan ni aling pureng', 'Pureng', '09157990101', 'test2@mail.com', '$2y$10$qWVPGuwC4z1K14V4XOoooe9KZhQ./VXLFVAVmkZC38/TP2xws3t7O', 'approved', '2025-05-27 11:54:42'),
(4, 'aaa', 'Lebron', '09157990102', 'test3@mail.com', '$2y$10$03DjEmzERPPMdpb4Ep4tTOiGcBIAidQCpCS4g8qn8WqODX6XQs6wq', 'rejected', '2025-05-27 11:57:17'),
(5, 'Tindahan ni aling', 'Lebron', '09157990103', 'test4@mail.com', '$2y$10$Ky8ufMr8eLguCRSd6CVvve7UouRkBr41rMQ8pw/InM3QmroQYcCm2', 'approved', '2025-05-27 12:01:08'),
(6, 'Tindahan ni aling', 'Pureng', '09157990103', 'tes3@mail.com', '$2y$10$DtXtBJ66LBQS3JUM41OguuEN6HBQ5HduUsbIqy3c78A/YktOx9gKe', 'rejected', '2025-05-27 12:03:19'),
(7, 'Tindahan ni aling', 'Pureng', '09157990103', 'testes@mail.com', '$2y$10$kBy9LWw.N6GSikq5p1SGO./CnqKiWY3SGVMrDiapLQwU95xwrSh/G', 'approved', '2025-05-27 12:05:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`) VALUES
(1, 'admin@email.com', 'admin123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `stall_applications`
--
ALTER TABLE `stall_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `stall_applications`
--
ALTER TABLE `stall_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
