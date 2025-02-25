-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2025 at 10:12 PM
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
-- Database: `asset_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_code` varchar(50) NOT NULL,
  `inventory_number` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `asset_type_id` int(11) NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `purchase_date` date NOT NULL,
  `responsible_person` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Active','Retired') DEFAULT 'Active',
  `image` varchar(255) DEFAULT NULL,
  `warranty_expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_code`, `inventory_number`, `name`, `asset_type_id`, `serial_number`, `location`, `department`, `purchase_date`, `responsible_person`, `price`, `created_at`, `updated_at`, `status`, `image`, `warranty_expiry_date`) VALUES
(11, 'COM-IT-26022025-1', 'IT-001', 'Dell OptiPlex 7090', 8, 'SN-DT001', 'ไม่ได้ระบุ', 'IT', '2025-02-26', 'สมชาย', 25000.00, '2025-02-25 18:56:19', '2025-02-25 19:13:46', 'Active', NULL, '2025-05-21'),
(12, 'LAP-IT-26022025-1', 'IT-002', 'Lenovo ThinkPad X1', 9, 'SN-LT002', 'ไม่ได้ระบุ', 'IT', '2025-02-26', 'สมชาย', 45000.00, '2025-02-25 18:57:31', '2025-02-25 18:59:31', 'Active', NULL, '2025-03-13'),
(13, 'PRI-SAL-26022025-1', 'IT-004', 'HP LaserJet Pro', 10, 'SN-PR004', 'ไม่ได้ระบุ', 'SALES', '2025-02-26', 'สุภาวดี', 8500.00, '2025-02-25 19:00:43', '2025-02-25 19:00:43', 'Active', NULL, '2025-02-12'),
(14, 'COM-SAL-26022025-2', 'IT-011', 'HP EliteDesk', 8, 'SN-DT011', 'ไม่ได้ระบุ', 'SALES', '2025-02-26', 'สุภาวดี', 28000.00, '2025-02-25 19:15:49', '2025-02-25 19:15:49', 'Active', NULL, '0000-00-00'),
(15, 'MON-SAL-26022025-1', 'IT-003', 'LG UltraFine 27', 11, 'SN-MN003', 'ไม่ได้ระบุ', 'SALES', '2025-02-26', 'สุภาวดี', 12000.00, '2025-02-25 20:26:38', '2025-02-25 20:27:17', 'Active', NULL, '2025-03-07');

-- --------------------------------------------------------

--
-- Table structure for table `asset_types`
--

CREATE TABLE `asset_types` (
  `assets_types_id` int(11) NOT NULL,
  `asset_type_name` varchar(255) NOT NULL,
  `asset_type_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `asset_types_created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_types`
--

INSERT INTO `asset_types` (`assets_types_id`, `asset_type_name`, `asset_type_hidden`, `asset_types_created_at`) VALUES
(8, 'Computer', 0, '2025-02-25 18:51:37'),
(9, 'Laptop', 0, '2025-02-25 18:51:44'),
(10, 'Printer', 0, '2025-02-25 18:55:10'),
(11, 'Monitor', 0, '2025-02-25 20:25:35');

-- --------------------------------------------------------

--
-- Table structure for table `pin_codes`
--

CREATE TABLE `pin_codes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `pin_code_hash` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pin_codes`
--

INSERT INTO `pin_codes` (`id`, `pin_code_hash`, `created_at`, `updated_at`) VALUES
(1, '8d969eef6ecad3c29a3a629280e686cf0c3f5d5a86aff3ca12020c923adc6c92', '2025-02-23 15:23:16', '2025-02-23 15:23:16');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asset_code` (`asset_code`),
  ADD KEY `asset_type_id` (`asset_type_id`);

--
-- Indexes for table `asset_types`
--
ALTER TABLE `asset_types`
  ADD PRIMARY KEY (`assets_types_id`),
  ADD UNIQUE KEY `asset_type_name` (`asset_type_name`);

--
-- Indexes for table `pin_codes`
--
ALTER TABLE `pin_codes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `asset_types`
--
ALTER TABLE `asset_types`
  MODIFY `assets_types_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `pin_codes`
--
ALTER TABLE `pin_codes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`asset_type_id`) REFERENCES `asset_types` (`assets_types_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
