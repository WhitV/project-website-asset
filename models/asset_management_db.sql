-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2025 at 06:49 AM
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
(1, 'TES-PO-08022025-1', '1ก0101010', 'test0', 1, '0123456', 'ไม่ได้ระบุ', 'PO', '2025-02-08', 'Jane Smiths', 100.00, '2025-02-07 18:56:42', '2025-02-08 14:49:59', 'Active', '67a76f171b494-australia.png', NULL),
(2, 'TES-CS-10022025-2', 'test1022222', 'test102212212', 1, '22222', 'ไม่ได้ระบุ', 'CS', '2025-02-10', 'SALES', 200.00, '2025-02-10 10:52:46', '2025-02-10 10:52:46', 'Active', NULL, NULL),
(3, 'TES-PO-18022025-3', '10101010ก', 'test', 1, 'fdsf', 'ไม่ได้ระบุ', 'PO', '2025-02-18', 'Jane Smiths', 50000.00, '2025-02-18 05:11:33', '2025-02-18 05:13:41', 'Retired', NULL, '2025-02-27'),
(4, 'TES-BD-18022025-4', 'test101', '1001', 1, '2002', '55', 'BD', '2025-02-18', '555', 2002.00, '2025-02-18 05:25:29', '2025-02-18 05:32:41', 'Retired', NULL, '2025-02-28'),
(5, 'TES-SAL-18022025-1', '1025', '5555', 2, '7788', '9999', 'SALES', '2025-02-18', 'fdasf', 8888.00, '2025-02-18 05:35:36', '2025-02-18 05:35:36', 'Active', NULL, '2025-02-28'),
(6, 'TES-SAL-18022025-2', '55555', '66666', 2, '88888', '8888', 'SALES', '2025-02-18', 'fdasf', 5555.00, '2025-02-18 05:37:02', '2025-02-18 05:37:02', 'Active', NULL, '2025-04-18'),
(7, 'TES-SAL-18022025-5', '7777', '777', 1, '888', '888', 'SALES', '2025-02-18', 'fdasf', 555.00, '2025-02-18 05:38:38', '2025-02-18 05:38:38', 'Active', NULL, '2025-02-18'),
(8, 'TES-MKT-18022025-3', '555', '555', 2, '55544', '55', 'MKT', '2025-02-18', '555', 555.00, '2025-02-18 05:43:13', '2025-02-18 05:43:13', 'Active', NULL, '0000-00-00');

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
(1, 'test', 0, '2025-02-07 18:55:27'),
(2, 'test2', 0, '2025-02-18 05:31:21');

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
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `asset_types`
--
ALTER TABLE `asset_types`
  MODIFY `assets_types_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
