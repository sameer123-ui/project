-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 21, 2025 at 08:06 AM
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
-- Database: `car_rental`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `car_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('booked','confirmed','cancelled','completed') NOT NULL DEFAULT 'booked',
  `pickup_location_id` int(11) DEFAULT NULL,
  `drop_location_id` int(11) DEFAULT NULL,
  `pickup_location` varchar(255) NOT NULL DEFAULT '',
  `drop_location` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `car_id`, `start_date`, `end_date`, `total_amount`, `booking_date`, `status`, `pickup_location_id`, `drop_location_id`, `pickup_location`, `drop_location`) VALUES
(1, 2, 2, '2025-06-13', '2025-06-14', 1777776.00, '2025-06-13 05:33:57', 'cancelled', NULL, NULL, '', ''),
(2, 2, 2, '2025-06-17', '2025-06-17', NULL, '2025-06-16 05:55:03', 'cancelled', NULL, NULL, '', ''),
(3, 3, 1, '2025-06-17', '2025-06-18', NULL, '2025-06-16 06:29:27', 'cancelled', NULL, NULL, '', ''),
(4, 3, 4, '2025-06-17', '2025-06-18', NULL, '2025-06-16 06:43:00', 'completed', NULL, NULL, '', ''),
(5, 2, 4, '2025-06-16', '2025-06-16', NULL, '2025-06-16 07:00:58', 'completed', NULL, NULL, '', ''),
(6, 2, 2, '2025-06-16', '2025-06-17', NULL, '2025-06-16 07:05:44', 'completed', NULL, NULL, '', ''),
(7, 3, 1, '2025-06-28', '2025-06-28', NULL, '2025-06-16 07:11:18', 'completed', NULL, NULL, '', ''),
(8, 2, 4, '2025-06-16', '2025-06-16', NULL, '2025-06-16 07:15:35', 'completed', NULL, NULL, '', ''),
(9, 2, 2, '2025-06-17', '2025-06-30', NULL, '2025-06-17 01:59:52', 'cancelled', NULL, NULL, '', ''),
(10, 2, 2, '2025-06-22', '2025-06-23', NULL, '2025-06-21 05:11:16', 'completed', NULL, NULL, '', ''),
(11, 2, 1, '2025-06-22', '2025-06-22', 1000000.00, '2025-06-21 05:13:21', 'completed', NULL, NULL, '', ''),
(12, 3, 1, '2025-06-21', '2025-06-21', 1000000.00, '2025-06-21 05:15:16', 'completed', NULL, NULL, '', ''),
(13, 2, 5, '2025-06-22', '2025-06-23', 50000.00, '2025-06-21 05:55:52', 'cancelled', NULL, NULL, 'Samakhusi', 'Gorkha');

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `status` enum('available','booked') DEFAULT 'available',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `name`, `model`, `price_per_day`, `status`, `image`, `created_at`) VALUES
(1, 'Lamborghini Hurricane', '2000', 1000000.00, 'available', NULL, '2025-06-13 05:08:03'),
(2, 'Audi', '2022', 888888.00, 'available', NULL, '2025-06-13 05:08:38'),
(4, 'Tesla ', 'Model X', 111111.00, 'available', NULL, '2025-06-16 06:30:46'),
(5, 'Hyundai', 'Creta', 50000.00, 'available', NULL, '2025-06-21 05:45:38'),
(6, 'Honda', 'Civic', 70000.00, 'available', NULL, '2025-06-21 05:46:52'),
(7, 'Maruti suzuki', 'Breeza', 70000.00, 'available', NULL, '2025-06-21 05:47:14'),
(8, 'Ford', 'Mustang', 90000.00, 'available', NULL, '2025-06-21 05:48:12'),
(9, 'BMW', '2025', 100000.00, 'available', NULL, '2025-06-21 05:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin', 'admin@admin.com', '25f43b1486ad95a1398e3eeb3d83bc4010015fcc9bedb35b432e00298d5021f7', 'admin', '2025-06-13 04:46:19'),
(2, 'Sahaj', 'sahaj@gmail.com', '04f8996da763b7a969b1028ee3007569eaf3a635486ddab211d512c85b9df8fb', 'user', '2025-06-13 04:48:37'),
(3, 'sushil', 'sushil@gmail.com', '04f8996da763b7a969b1028ee3007569eaf3a635486ddab211d512c85b9df8fb', 'user', '2025-06-16 06:29:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `car_id` (`car_id`),
  ADD KEY `fk_pickup_location` (`pickup_location_id`),
  ADD KEY `fk_drop_location` (`drop_location_id`);

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_drop_location` FOREIGN KEY (`drop_location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `fk_pickup_location` FOREIGN KEY (`pickup_location_id`) REFERENCES `locations` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
