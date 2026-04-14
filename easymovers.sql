-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 17, 2025 at 04:17 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `easymovers`
--

-- Drop tables to prevent "Table already exists" errors when re-importing
DROP TABLE IF EXISTS `user_notifications`;
DROP TABLE IF EXISTS `moving_requests`;
DROP TABLE IF EXISTS `admins`;
DROP TABLE IF EXISTS `users`;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2025-04-17 18:10:26');

-- --------------------------------------------------------

--
-- Table structure for table `moving_requests`
--

CREATE TABLE `moving_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `from_address` text NOT NULL,
  `to_address` text NOT NULL,
  `moving_date` date NOT NULL,
  `moving_time` varchar(50) NOT NULL,
  `items` text NOT NULL,
  `special_instructions` text DEFAULT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `estimated_cost` decimal(10,2) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `moving_requests`
--

INSERT INTO `moving_requests` (`id`, `user_id`, `service_type`, `from_address`, `to_address`, `moving_date`, `moving_time`, `items`, `special_instructions`, `status`, `admin_notes`, `estimated_cost`, `created_at`, `updated_at`) VALUES
(1, 1, 'Vehicle Transport', 'phagwara, 144411', 'vizag, 530004', '2025-04-25', 'Afternoon (12:00 PM - 4:00 PM)', '[]', '', 'completed', '', 3260.00, '2025-04-17 17:47:00', '2025-04-17 19:35:01'),
(2, 1, 'Furniture Moving', 'phagwara, 144411', 'vizag, 530004', '2025-04-23', 'Afternoon (12:00 PM - 4:00 PM)', '{\"text\":\"No items specified\"}', '', 'cancelled', NULL, 4770.00, '2025-04-17 17:57:04', '2025-04-17 17:57:29'),
(3, 1, 'House Shifting', '144411', '530004', '2025-04-21', 'Evening (4:00 PM - 8:00 PM)', '{\"text\":\"No items specified\"}', 'care', 'cancelled', '', 7610.00, '2025-04-17 18:00:00', '2025-04-17 19:34:56'),
(4, 1, 'Vehicle Transport', '144411', '530004', '2025-04-22', 'Afternoon (12:00 PM - 4:00 PM)', '{\"text\":\"No items specified\"}', 'care', 'cancelled', NULL, 7660.00, '2025-04-17 19:25:49', '2025-04-17 19:32:10'),
(5, 1, 'Furniture Moving', '144411', '530004', '2025-04-28', 'Morning (8:00 AM - 12:00 PM)', '{\"text\":\"No items specified\"}', 'wjdsn', 'cancelled', NULL, 7560.00, '2025-04-17 19:28:48', '2025-04-17 19:33:17'),
(6, 1, 'Vehicle Transport', '144411', '530004', '2025-04-29', 'Evening (4:00 PM - 8:00 PM)', '{\"text\":\"No items specified\"}', '', 'cancelled', 'sorry', 7660.00, '2025-04-17 19:33:51', '2025-04-17 19:34:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `created_at`) VALUES
(1, 'XTZ', 'your.ravi@icloud.com', '$2y$10$Y0jcwgELfDVQ6uGVn6CLguzldsSJe4q6Rc9l7cGm/lQauzoUXx7/2', '9010900688', NULL, '2025-04-17 10:09:21');

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `related_to` varchar(50) NOT NULL,
  `related_id` int(11) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_notifications`
--

INSERT INTO `user_notifications` (`id`, `user_id`, `message`, `related_to`, `related_id`, `is_read`, `created_at`) VALUES
(1, 1, 'Your moving request for April 21, 2025 has been updated to: Completed', 'request', 3, 1, '2025-04-17 19:04:06'),
(2, 1, 'Your moving request for April 29, 2025 has been updated to: Confirmed', 'request', 6, 1, '2025-04-17 19:34:06'),
(3, 1, 'Your moving request for April 29, 2025 has been updated to: Completed', 'request', 6, 1, '2025-04-17 19:34:24'),
(4, 1, 'Your moving request for April 29, 2025 has been updated to: Cancelled. Admin notes: sorry', 'request', 6, 1, '2025-04-17 19:34:34'),
(5, 1, 'Your moving request for April 21, 2025 has been updated to: Cancelled', 'request', 3, 1, '2025-04-17 19:34:56'),
(6, 1, 'Your moving request for April 25, 2025 has been updated to: Completed', 'request', 1, 1, '2025-04-17 19:35:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `moving_requests`
--
ALTER TABLE `moving_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `moving_requests`
--
ALTER TABLE `moving_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
