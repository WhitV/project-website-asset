--
-- Database: `asset_management_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `asset_types`
--

CREATE TABLE `asset_types` (
  `assets_types_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_type_name` varchar(255) NOT NULL UNIQUE,
  `asset_type_hidden` tinyint(1) NOT NULL DEFAULT 0, -- Change column name
  `asset_types_created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`assets_types_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(50) NOT NULL UNIQUE,
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
  PRIMARY KEY (`id`),
  KEY `asset_type_id` (`asset_type_id`),
  CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`asset_type_id`) REFERENCES `asset_types` (`assets_types_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;


