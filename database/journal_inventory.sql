-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Mar 27, 2026 at 03:17 PM
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
-- Table structure for table `journal_inventory`
--

CREATE TABLE `journal_inventory` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) NOT NULL,
  `approved_by` varchar(50) DEFAULT NULL,
  `approved_to` varchar(50) DEFAULT NULL,
  `approved_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `journal_date` date DEFAULT NULL,
  `journal_type_id` varchar(30) DEFAULT NULL,
  `modul` varchar(30) DEFAULT NULL,
  `remarks` text,
  `document_no` varchar(30) DEFAULT NULL,
  `invoice_no` varchar(30) DEFAULT NULL,
  `number` varchar(30) DEFAULT NULL,
  `company_id` varchar(30) DEFAULT NULL,
  `company_name` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `description` text,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `original_debit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `original_credit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `rates` decimal(20,2) NOT NULL DEFAULT '0.00',
  `local_debit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `local_credit` decimal(20,2) NOT NULL DEFAULT '0.00',
  `posting` tinyint(1) NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


--
-- Indexes for table `journal_inventory`
--
ALTER TABLE `journal_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `journal_inventory`
--
ALTER TABLE `journal_inventory`
  ADD CONSTRAINT `journal_inventory_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `journal_inventory_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
