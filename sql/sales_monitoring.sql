-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 23, 2025 at 11:33 PM
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
-- Database: `sales_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `archives`
--

CREATE TABLE `archives` (
  `archive_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `warranty_start` date DEFAULT NULL,
  `warranty_end` date DEFAULT NULL,
  `before_image` varchar(255) DEFAULT NULL,
  `after_image` varchar(255) DEFAULT NULL,
  `date_archived` datetime DEFAULT current_timestamp(),
  `issue` text DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `part_cost` decimal(10,2) DEFAULT 0.00,
  `repair_cost` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archives`
--

INSERT INTO `archives` (`archive_id`, `client_id`, `order_id`, `fullname`, `contact_number`, `address`, `email`, `total_amount`, `status`, `payment_status`, `warranty_start`, `warranty_end`, `before_image`, `after_image`, `date_archived`, `issue`, `date_created`, `part_cost`, `repair_cost`) VALUES
(32, 7, 21, 'Ralph Jay P. Maano', '09913234166', 'Lingating, Baungon, Bukidnon', 'ralphjay69@gmail.com', 3000.00, 'Done', '', NULL, '2025-11-23', '1761254812_541338875_1331289008714604_2265551536205833106_n.jpg', '1761254878', '2025-10-24 05:28:01', 'LCD Replacement', '2025-10-23 00:00:00', 2000.00, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `client_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `contact_number` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `date_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`client_id`, `fullname`, `contact_number`, `address`, `email`, `date_added`) VALUES
(7, 'Ralph Jay P. Maano', '09913234166', 'Lingating, Baungon, Bukidnon', 'ralphjay69@gmail.com', '2025-10-24 05:26:52'),
(8, 'Nicole Catamco', '09913234167', 'Lingating, Baungon, Bukidnon', 'jonel@gmail.com', '2025-10-24 05:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `issue` text DEFAULT NULL,
  `type_of_repair` text DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','On Going','Done','Finished','Pending Payment') DEFAULT 'Pending',
  `payment_status` enum('Not Yet Paid','Paid Downpayment','Paid Full in Cash','Pending','Paid','Unpaid') DEFAULT 'Not Yet Paid',
  `warranty_start` date DEFAULT curdate(),
  `warranty_end` date DEFAULT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `before_image` varchar(255) DEFAULT NULL,
  `after_image` varchar(255) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `client_id`, `issue`, `type_of_repair`, `total_amount`, `status`, `payment_status`, `warranty_start`, `warranty_end`, `date_created`, `created_at`, `before_image`, `after_image`, `is_archived`) VALUES
(20, 7, 'LCD Replacement', 'LCD Replacement', 0.00, 'Pending', '', '2025-10-23', '2025-11-23', '2025-10-23 00:00:00', '2025-10-23 15:26:52', '1761254812_541338875_1331289008714604_2265551536205833106_n.jpg', '1761254848_541047441_1331289228714582_5732811220358366688_n.jpg', 1),
(21, 7, 'LCD Replacement', NULL, 3000.00, 'Done', '', '2025-10-24', '2025-11-23', '2025-10-23 00:00:00', '2025-10-23 21:27:46', '1761254812_541338875_1331289008714604_2265551536205833106_n.jpg', '1761254878_541047441_1331289228714582_5732811220358366688_n.jpg', 1),
(22, 8, 'BATTERY REPLACEMENT', 'Battery Replacement', 0.00, 'On Going', 'Not Yet Paid', '2025-10-23', '2025-11-23', '2025-10-23 00:00:00', '2025-10-23 15:29:14', '1761254954_542148353_1331288315381340_842149815356023577_n.jpg', '1761254981_541733935_1331288378714667_3117834100624217560_n.jpg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `issue_description` text DEFAULT NULL,
  `part_name` varchar(255) DEFAULT NULL,
  `part_cost` decimal(10,2) DEFAULT NULL,
  `repair_cost` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `total_cost` decimal(10,2) NOT NULL DEFAULT 0.00,
  `photo` varchar(255) DEFAULT NULL,
  `date_added` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`detail_id`, `order_id`, `issue_description`, `part_name`, `part_cost`, `repair_cost`, `category`, `total_cost`, `photo`, `date_added`) VALUES
(19, 20, 'LCD Replacement', NULL, 2000.00, 1000.00, NULL, 3000.00, NULL, '2025-10-24 05:26:52'),
(20, 21, NULL, NULL, 2000.00, 1000.00, NULL, 3000.00, NULL, '2025-10-24 05:27:46'),
(21, 22, 'BATTERY REPLACEMENT', NULL, 2000.00, 500.00, NULL, 2500.00, NULL, '2025-10-24 05:29:14');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `shop_link` varchar(255) DEFAULT NULL,
  `address_type` enum('Local','International') DEFAULT 'Local',
  `full_address` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `shop_link`, `address_type`, `full_address`, `address`, `logo`, `date_added`) VALUES
(5, 'CELLMAX', 'https://shopee.ph/cellmaxs.ph', 'International', 'China', NULL, '1761254008_cellmax.webp', '2025-10-23 21:13:28'),
(6, 'ALLPARTS', 'https://shopee.ph/allparts.ph', 'International', 'CHINA', NULL, '1761254074_allparts.webp', '2025-10-23 21:14:34'),
(7, 'LUCKY STORE', 'https://shopee.ph/luckymore999', 'Local', 'Cagayan de Oro', NULL, '1761254144_lucky.jfif', '2025-10-23 21:15:44'),
(8, 'Integrity Digital.PH', 'https://shopee.ph/integritydigital', 'Local', 'Cagayan de Oro City', NULL, '1761254203_INTEGRITY_.webp', '2025-10-23 21:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_supplies`
--

CREATE TABLE `supplier_supplies` (
  `supply_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `category` enum('LCD Replacement','Battery Replacement','Power/Volume Button','Middle Frame','LCD Frame','Back Cover') DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `item_image` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_supplies`
--

INSERT INTO `supplier_supplies` (`supply_id`, `supplier_id`, `category`, `item_name`, `price`, `item_image`, `item_description`, `date_added`) VALUES
(5, 5, 'Battery Replacement', 'Iphone Battery', 1500.00, '1761254375_battery.PNG', 'Cellmaxs High Capacity Battery For 5S SE 6 6s 7 8 SE 2020 X XS XR MAX 11 12 13 14 Mini Plus Pro Max', '2025-10-23 21:18:54'),
(6, 6, 'LCD Replacement', 'Allparts FHD Display For Ip X Xr Xs Max 11 12 Pro Max LCD Touch Screen Digitizer Replacement', 2000.00, '1761254465_cn-11134207-7qukw-lf97ue52jwkq2e.webp', 'Incell', '2025-10-23 21:21:05'),
(7, 7, 'Battery Replacement', 'OPPO A5 2020/A9 2020/BLP727 Built-in Battery Replacement', 600.00, '1761254535_ph-11134207-7r98w-lwdsggrqr6ysc0.webp', 'Original Battery', '2025-10-23 21:22:15'),
(8, 8, 'LCD Replacement', 'AMOLED VIVO V29 V30E V40Lite(5g) CURVE LCD SCREEN REPLACEMENT', 2000.00, '1761254612_ph-11134207-7ra0g-md8fgbnhkz0g40.webp', 'Amoled', '2025-10-23 21:23:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `fullname` varchar(100) DEFAULT 'Administrator',
  `role` enum('admin') DEFAULT 'admin',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `fullname`, `role`, `created_at`) VALUES
(1, 'admin', '$2y$10$QFZP42/MoH.m/JT91ltqk.dcVid6joLfVUntNX/VK8fjA06Od4z/a', 'Administrator', 'admin', '2025-10-23 22:16:55'),
(2, 'admin1', '$2y$10$V7CeB/Af0MIiZPPhC3vay.eD7moxmpjI2gGRxoAiDIPgkQEFYd17G', 'Ralph Jay', 'admin', '2025-10-24 04:10:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `unique_client_email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_orders_client` (`client_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `fk_details_order` (`order_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `supplier_supplies`
--
ALTER TABLE `supplier_supplies`
  ADD PRIMARY KEY (`supply_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `archives`
--
ALTER TABLE `archives`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `supplier_supplies`
--
ALTER TABLE `supplier_supplies`
  MODIFY `supply_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `archives`
--
ALTER TABLE `archives`
  ADD CONSTRAINT `archives_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `fk_details_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_supplies`
--
ALTER TABLE `supplier_supplies`
  ADD CONSTRAINT `supplier_supplies_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
