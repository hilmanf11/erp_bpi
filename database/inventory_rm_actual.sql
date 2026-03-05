-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 04, 2026 at 10:30 AM
-- Server version: 5.7.43-log
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erp-bpi-dummy`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory_rm_actual`
--

CREATE TABLE `inventory_rm_actual` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` VARCHAR(30) DEFAULT NULL,
  `part_no` varchar(30) DEFAULT NULL,
  `cutoff_date` date DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT '0.00',
  `price` decimal(20,4) DEFAULT '0.0000',

  `upload` varchar(5) DEFAULT NULL,
  `upload_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Indexes for table `inventory_rm_actual`
--
ALTER TABLE `inventory_rm_actual`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`),
  ADD KEY `part_no` (`part_no`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_rm_actual`
--
ALTER TABLE `inventory_rm_actual`
  ADD CONSTRAINT `inventory_rm_actual_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_rm_actual_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
