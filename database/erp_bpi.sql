-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Waktu pembuatan: 30 Okt 2023 pada 11.51
-- Versi server: 10.6.15-MariaDB-cll-lve
-- Versi PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u1738363_erp_bpi`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `abc_class`
--

CREATE TABLE `abc_class` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `class` varchar(30) DEFAULT NULL,
  `safety_stock` int(11) DEFAULT 0,
  `formula` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `abc_class`
--

INSERT INTO `abc_class` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `class`, `safety_stock`, `formula`) VALUES
('20231016000001', 'admin', '2023-10-16 13:45:11', NULL, NULL, 0, 'A', 50, 'X>=5 hari'),
('20231016000002', 'admin', '2023-10-16 13:45:36', NULL, NULL, 0, 'B', 100, '2<X<5'),
('20231016000003', 'admin', '2023-10-16 13:45:55', NULL, NULL, 0, 'C', 50, '1<X<2'),
('20231016000004', 'admin', '2023-10-16 13:46:10', NULL, NULL, 0, 'D', 50, 'X<1');

-- --------------------------------------------------------

--
-- Struktur dari tabel `approvals`
--

CREATE TABLE `approvals` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `user_approval_1` varchar(30) DEFAULT NULL,
  `user_approval_2` varchar(30) DEFAULT NULL,
  `user_approval_3` varchar(30) DEFAULT NULL,
  `user_approval_4` varchar(30) DEFAULT NULL,
  `user_approval_5` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `approvals`
--

INSERT INTO `approvals` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `table_name`, `user_approval_1`, `user_approval_2`, `user_approval_3`, `user_approval_4`, `user_approval_5`, `status`) VALUES
('20230718000001', 'admin', '2023-07-18 17:13:31', NULL, NULL, 0, 'users', 'admin', '', '', '', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `bom`
--

CREATE TABLE `bom` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `recyle` int(11) DEFAULT 0,
  `composition` decimal(20,5) DEFAULT 0.00000,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `calendars`
--

CREATE TABLE `calendars` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `working_date` date NOT NULL,
  `remarks` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `chats`
--

CREATE TABLE `chats` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `from_users_id` varchar(30) NOT NULL,
  `to_users_id` varchar(30) NOT NULL,
  `messages` text NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `config`
--

CREATE TABLE `config` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo` text DEFAULT NULL,
  `favicon` text DEFAULT NULL,
  `image` text DEFAULT NULL,
  `theme` varchar(50) DEFAULT NULL,
  `tax` int(11) NOT NULL DEFAULT 0,
  `pph` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) DEFAULT 0,
  `fg_ss` int(11) NOT NULL DEFAULT 0,
  `cutoff_day_from` varchar(2) DEFAULT NULL,
  `cutoff_day_to` varchar(2) DEFAULT NULL,
  `wp_day_from` varchar(2) DEFAULT NULL,
  `wp_day_to` varchar(2) DEFAULT NULL,
  `cutoff_current` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `config`
--

INSERT INTO `config` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `address`, `logo`, `favicon`, `image`, `theme`, `tax`, `pph`, `status`, `fg_ss`, `cutoff_day_from`, `cutoff_day_to`, `wp_day_from`, `wp_day_to`, `cutoff_current`) VALUES
('4c9a9e62-3ff6-11ed-a526-7085c2', 'admin', '2022-10-01 06:31:13', NULL, NULL, 0, 'ERP', 'PT BANSHU PLASTIC INDONESIA', 'ENTERPRISE RESOURCE PLANNING', '', 'https://bpi.hris-server.com/assets/image/config/logo/1693392555.png', 'https://bpi.hris-server.com/assets/image/config/favicon/1693392555.png', 'https://bpi.hris-server.com/assets/image/config/login/1696989945.jpg', 'default', 11, 0, 0, 3, '16', '17', '21', '22', 'off');

-- --------------------------------------------------------

--
-- Struktur dari tabel `config_iso`
--

CREATE TABLE `config_iso` (
  `doc_delivery_order` varchar(30) DEFAULT NULL,
  `doc_delivery_note` varchar(30) DEFAULT NULL,
  `doc_sales_invoice` varchar(30) DEFAULT NULL,
  `doc_packing_list` varchar(30) DEFAULT NULL,
  `doc_checksheet` varchar(30) DEFAULT NULL,
  `doc_purchase_request` varchar(30) DEFAULT NULL,
  `doc_purchase_order` varchar(30) DEFAULT NULL,
  `doc_job_order` varchar(30) DEFAULT NULL,
  `doc_supply_sheet` varchar(30) DEFAULT NULL,
  `doc_receiving_note` varchar(30) DEFAULT NULL,
  `doc_customer` varchar(30) DEFAULT NULL,
  `form_delivery_order` varchar(30) DEFAULT NULL,
  `form_delivery_note` varchar(30) DEFAULT NULL,
  `form_sales_invoice` varchar(30) DEFAULT NULL,
  `form_packing_list` varchar(30) DEFAULT NULL,
  `form_checksheet` varchar(30) DEFAULT NULL,
  `form_purchase_request` varchar(30) DEFAULT NULL,
  `form_purchase_order` varchar(30) DEFAULT NULL,
  `form_job_order` varchar(30) DEFAULT NULL,
  `form_supply_sheet` varchar(30) DEFAULT NULL,
  `form_receiving_note` varchar(30) DEFAULT NULL,
  `form_customer` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `config_iso`
--

INSERT INTO `config_iso` (`doc_delivery_order`, `doc_delivery_note`, `doc_sales_invoice`, `doc_packing_list`, `doc_checksheet`, `doc_purchase_request`, `doc_purchase_order`, `doc_job_order`, `doc_supply_sheet`, `doc_receiving_note`, `doc_customer`, `form_delivery_order`, `form_delivery_note`, `form_sales_invoice`, `form_packing_list`, `form_checksheet`, `form_purchase_request`, `form_purchase_order`, `form_job_order`, `form_supply_sheet`, `form_receiving_note`, `form_customer`) VALUES
('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `convertions`
--

CREATE TABLE `convertions` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_rm_id` varchar(30) NOT NULL,
  `uom_po` varchar(30) DEFAULT NULL,
  `uom_soft` varchar(30) DEFAULT NULL,
  `convertion` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `currencies`
--

CREATE TABLE `currencies` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `symbol` varchar(10) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `currencies`
--

INSERT INTO `currencies` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `symbol`, `status`) VALUES
('CR01', 'admin', '2023-08-07 20:06:08', 'admin', '2023-08-07 22:24:45', 0, '', 'IDR', 'INDONESIA RUPIAH', 'Rp', 0),
('CR02', 'admin', '2023-08-07 20:13:15', 'admin', '2023-08-07 22:25:38', 0, '', 'USD', 'DOLAR A.S', '$', 0),
('CR03', 'admin', '2023-08-07 22:26:17', NULL, NULL, 0, '', 'JPY', 'JAPANESE YEN', '¥', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `customers`
--

CREATE TABLE `customers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `currency` varchar(20) NOT NULL,
  `payment_term` int(11) NOT NULL DEFAULT 0,
  `taxes` int(11) NOT NULL DEFAULT 0,
  `bank_account` varchar(30) DEFAULT NULL,
  `bank_name` int(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customers`
--

INSERT INTO `customers` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `type`, `currency`, `payment_term`, `taxes`, `bank_account`, `bank_name`, `status`) VALUES
('C001', 'admin', '2023-08-14 21:11:00', 'admin', '2023-10-16 18:18:46', 0, 'BEI', 'PT. BANSHU ELECTRIC INDONESIA', '', 'LOCAL', 'IDR', 0, 11, '', 0, 0),
('C002', 'admin', '2023-08-16 09:18:57', NULL, NULL, 0, 'BP1', 'PT. CUSTOMER BPI 1', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C003', 'admin', '2023-08-16 09:18:57', NULL, NULL, 0, 'BP2', 'PT. CUSTOMER BPI 2', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C004', 'admin', '2023-08-16 09:18:57', NULL, NULL, 0, 'BP3', 'PT. CUSTOMER BPI 3', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C005', 'admin', '2023-08-16 09:18:57', NULL, NULL, 0, 'BP4', 'PT. CUSTOMER BPI 4', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C006', 'admin', '2023-08-16 09:18:58', NULL, NULL, 0, 'BP5', 'PT. CUSTOMER BPI 5', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C007', 'admin', '2023-08-16 09:18:58', NULL, NULL, 0, 'BP6', 'PT. CUSTOMER BPI 6', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C008', 'admin', '2023-08-16 09:18:58', NULL, NULL, 0, 'BP7', 'PT. CUSTOMER BPI 7', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C009', 'admin', '2023-08-16 09:18:58', NULL, NULL, 0, 'BP8', 'PT. CUSTOMER BPI 8', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C010', 'admin', '2023-08-16 09:18:58', NULL, NULL, 0, 'BP9', 'PT. CUSTOMER BPI 9', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C011', 'admin', '2023-08-16 09:18:58', NULL, NULL, 0, 'BP10', 'PT. CUSTOMER BPI 10', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C012', 'admin', '2023-08-16 09:18:58', NULL, NULL, 0, 'BP11', 'PT. CUSTOMER BPI 11', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C013', 'admin', '2023-09-21 15:07:10', NULL, NULL, 0, '0', '', NULL, '', '', 0, 0, '', 0, 0),
('C014', 'admin', '2023-09-21 15:07:10', NULL, NULL, 0, 'CUSTOMER ID', 'SALES ORDER NO.', NULL, 'PRODUCT ID', '', 0, 0, '', 0, 0),
('C015', 'admin', '2023-09-21 15:07:10', NULL, NULL, 0, 'C002', 'SO230902', NULL, 'BPIFG-IP09', '', 0, 0, '', 0, 0),
('C016', 'admin', '2023-09-21 16:23:43', 'admin', '2023-09-21 16:24:55', 0, 'CUS', 'TESTCUST', NULL, 'LOCAL', 'IDR', 30, 0, '', 0, 0),
('C017', 'admin', '2023-09-21 16:30:13', NULL, NULL, 0, 'ASK', 'PT. ASKARA INTERNAL', NULL, 'LOCAL', 'IDR', 60, 0, '', 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_address`
--

CREATE TABLE `customer_address` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_billing` text DEFAULT NULL,
  `contact_person` varchar(20) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `telp_billing` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer_address`
--

INSERT INTO `customer_address` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `address`, `address_billing`, `contact_person`, `telp`, `telp_billing`, `email`, `website`) VALUES
('20231016000001', 'admin', '2023-10-16 18:19:43', NULL, NULL, 0, 'C001', 'JL JUPITER', 'JL JUPITER', 'Mr. John', '081289949111', '08012090331', 'jupiter@gmail.com', 'jupiter.com'),
('20231016000002', 'admin', '2023-10-16 21:09:31', NULL, NULL, 0, 'C001', 'JL. TAURUS', 'JL. TAURUS', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_items`
--

CREATE TABLE `customer_items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `price` decimal(20,2) DEFAULT 0.00,
  `valid_date` date DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer_items`
--

INSERT INTO `customer_items` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `item_fg_id`, `price`, `valid_date`, `remark`) VALUES
('20230823000002', 'admin', '2023-08-23 22:34:42', 'admin', '2023-08-28 09:12:29', 0, 'C003', 'BPIFG-IP08230002', '2750.00', '2023-08-28', ''),
('20230828000001', 'admin', '2023-08-28 05:34:17', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP08230002', '2000.00', '2023-08-28', ''),
('20230828000004', 'admin', '2023-08-28 09:14:48', NULL, NULL, 0, 'C004', 'BPIFG-IP08230002', '17500.00', '2023-08-29', 'TEST'),
('20230828000005', 'admin', '2023-08-28 16:53:11', NULL, NULL, 0, 'C008', 'BPIFG-IP08230002', '2000.00', '2023-08-28', ''),
('20230904000001', 'admin', '2023-09-04 12:25:35', 'admin', '2023-09-21 16:47:28', 0, 'C001', 'BPIFG-IP08230001', '10.00', '2023-09-30', ''),
('20230921000001', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230001', '215000.00', '2024-09-09', 'TEST1'),
('20230921000002', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230002', '215001.00', '2024-09-09', 'TEST2'),
('20230921000003', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230003', '250000.00', '2024-09-09', 'TEST3'),
('20230921000004', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230004', '215003.00', '2024-09-09', 'TEST4'),
('20230921000005', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230005', '215004.00', '2024-09-09', 'TEST5'),
('20230921000006', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230006', '215005.00', '2024-09-09', 'TEST6'),
('20230921000007', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230007', '215006.00', '2024-09-09', 'TEST7'),
('20230921000008', 'admin', '2023-09-21 14:28:58', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230008', '215007.00', '2024-09-09', 'TEST8'),
('20230921000009', 'admin', '2023-09-21 14:28:59', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230009', '215008.00', '2024-09-09', 'TEST9'),
('20230921000010', 'admin', '2023-09-21 14:28:59', 'admin', '2023-09-21 14:29:24', 0, 'C002', 'BPIFG-IP09230010', '215009.00', '2024-09-09', 'TEST10');

-- --------------------------------------------------------

--
-- Struktur dari tabel `customer_item_histories`
--

CREATE TABLE `customer_item_histories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `price` decimal(20,2) DEFAULT 0.00,
  `valid_date` date DEFAULT NULL,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `customer_item_histories`
--

INSERT INTO `customer_item_histories` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `item_fg_id`, `price`, `valid_date`, `remark`) VALUES
('20230828000001', 'admin', '2023-08-28 05:34:17', NULL, NULL, 0, 'C002', 'BPIFG-IP08230001', '1000.00', '2023-08-14', ''),
('20230828000002', 'admin', '2023-08-28 05:34:17', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', '1500.00', '2023-08-28', ''),
('20230828000003', 'admin', '2023-08-28 07:47:00', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', '2000.00', '2023-08-28', ''),
('20230828000004', 'admin', '2023-08-28 09:05:15', NULL, NULL, 0, 'C003', 'BPIFG-IP08230002', '2500.00', '2023-08-28', ''),
('20230828000005', 'admin', '2023-08-28 09:06:02', NULL, NULL, 0, 'C003', 'BPIFG-IP08230001', '17500.00', '2023-08-28', ''),
('20230828000006', 'admin', '2023-08-28 09:06:37', NULL, NULL, 0, 'C003', 'BPIFG-IP08230002', '3500.00', '2023-08-28', ''),
('20230828000007', 'admin', '2023-08-28 09:08:42', NULL, NULL, 0, 'C003', 'BPIFG-IP08230002', '4500.00', '2023-08-28', ''),
('20230828000008', 'admin', '2023-08-28 09:09:11', NULL, NULL, 0, 'C003', 'BPIFG-IP08230002', '1.00', '2023-08-28', ''),
('20230828000009', 'admin', '2023-08-28 09:12:29', NULL, NULL, 0, 'C003', 'BPIFG-IP08230002', '2750.00', '2023-08-28', ''),
('20230828000010', 'admin', '2023-08-28 16:53:11', NULL, NULL, 0, 'C008', 'BPIFG-IP08230002', '2000.00', '2023-08-28', ''),
('20230904000001', 'admin', '2023-09-04 12:25:35', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', '1.00', '2023-09-04', ''),
('20230921000001', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230001', '215000.00', '2024-09-09', 'TEST1'),
('20230921000002', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230002', '215001.00', '2024-09-09', 'TEST2'),
('20230921000003', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230004', '215003.00', '2024-09-09', 'TEST4'),
('20230921000004', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230003', '250000.00', '2024-09-09', 'TEST3'),
('20230921000005', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230005', '215004.00', '2024-09-09', 'TEST5'),
('20230921000006', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230007', '215006.00', '2024-09-09', 'TEST7'),
('20230921000007', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230009', '215008.00', '2024-09-09', 'TEST9'),
('20230921000008', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230006', '215005.00', '2024-09-09', 'TEST6'),
('20230921000009', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230008', '215007.00', '2024-09-09', 'TEST8'),
('20230921000010', 'admin', '2023-09-21 14:29:24', NULL, NULL, 0, 'C002', 'BPIFG-IP09230010', '215009.00', '2024-09-09', 'TEST10'),
('20230921000011', 'admin', '2023-09-21 16:46:57', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', '0.00', '2023-09-30', ''),
('20230921000012', 'admin', '2023-09-21 16:47:28', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', '10.00', '2023-09-30', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery_areas`
--

CREATE TABLE `delivery_areas` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `delivery_areas`
--

INSERT INTO `delivery_areas` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `name`, `description`) VALUES
('A001', 'admin', '2023-08-28 10:37:16', NULL, NULL, 0, 'CIBITUNG', ''),
('A002', 'admin', '2023-08-28 10:37:27', NULL, NULL, 0, 'CIKARANG', ''),
('A003', 'admin', '2023-08-28 10:37:41', NULL, NULL, 0, 'PURWAKARTA', ''),
('A004', 'admin', '2023-08-28 11:00:04', 'admin', '2023-09-22 13:38:52', 0, 'KARAWANG', 'KKIC');

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery_notes`
--

CREATE TABLE `delivery_notes` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `customer_address_id` varchar(30) DEFAULT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `customer_order_no` varchar(30) DEFAULT NULL,
  `delivery_order_no` varchar(30) DEFAULT NULL,
  `delivery_note_no` varchar(30) DEFAULT NULL,
  `delivery_note_date` date DEFAULT NULL,
  `police_no` varchar(20) DEFAULT NULL,
  `driver_name` varchar(20) DEFAULT NULL,
  `trans_type` varchar(30) DEFAULT NULL,
  `origin` varchar(30) DEFAULT NULL,
  `sailing` varchar(30) DEFAULT NULL,
  `ship_by` varchar(30) DEFAULT NULL,
  `incoterm` varchar(30) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `uom` varchar(20) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `delivery_orders`
--

CREATE TABLE `delivery_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `delivery_order_no` varchar(30) DEFAULT NULL,
  `delivery_order_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `trans_type` varchar(10) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `uom` varchar(20) DEFAULT NULL,
  `qty_so` int(11) NOT NULL DEFAULT 0,
  `qty_remain` int(11) NOT NULL DEFAULT 0,
  `qty_do` int(11) NOT NULL DEFAULT 0,
  `qty_del` int(11) NOT NULL DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `stock_bal` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `divisions`
--

CREATE TABLE `divisions` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `divisions`
--

INSERT INTO `divisions` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`) VALUES
('DIV01', 'admin', '2023-08-18 06:18:04', NULL, NULL, 0, 'IP', 'INJECTION', ''),
('DIV02', 'admin', '2023-08-18 06:18:12', NULL, NULL, 0, 'MD', 'MOLDING', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `forecasts`
--

CREATE TABLE `forecasts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `p_month` varchar(10) DEFAULT NULL,
  `p_year` varchar(10) DEFAULT NULL,
  `revision` int(1) DEFAULT 0,
  `month_1` int(11) DEFAULT 0,
  `month_2` int(11) DEFAULT 0,
  `month_3` int(11) DEFAULT 0,
  `month_4` int(11) DEFAULT 0,
  `month_5` int(11) DEFAULT 0,
  `month_6` int(11) DEFAULT 0,
  `month_7` int(11) DEFAULT 0,
  `month_8` int(11) DEFAULT 0,
  `month_9` int(11) DEFAULT 0,
  `month_10` int(11) DEFAULT 0,
  `month_11` int(11) DEFAULT 0,
  `month_12` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `forecasts`
--

INSERT INTO `forecasts` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `item_fg_id`, `document_no`, `issued_date`, `p_month`, `p_year`, `revision`, `month_1`, `month_2`, `month_3`, `month_4`, `month_5`, `month_6`, `month_7`, `month_8`, `month_9`, `month_10`, `month_11`, `month_12`, `remark`) VALUES
('20230904000001', 'admin', '2023-09-04 16:12:07', 'admin', '2023-09-04 16:12:30', 0, 'C001', 'BPIFG-IP08230001', 'FC2309002', '2023-09-01', '09', '2023', 1, 100, 400, 500, 400, 500, 600, 0, 0, 0, 0, 0, 0, ''),
('20230904000002', 'admin', '2023-09-04 16:13:37', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2310001', '2023-10-02', '10', '2023', 0, 300, 400, 500, 200, 100, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000001', 'admin', '2023-09-11 03:09:23', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2306001', '2023-06-01', '06', '2023', 0, 1000, 1000, 1000, 1000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000002', 'admin', '2023-09-11 03:09:50', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2306002', '2023-06-01', '06', '2023', 0, 2000, 2000, 2000, 2000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000003', 'admin', '2023-09-11 03:10:10', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2307001', '2023-07-01', '07', '2023', 0, 2000, 2000, 2000, 2000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000004', 'admin', '2023-09-11 03:10:31', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2307002', '2023-07-03', '07', '2023', 0, 3000, 3000, 3000, 3000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000005', 'admin', '2023-09-11 03:10:52', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2308001', '2023-08-01', '08', '2023', 0, 1000, 1000, 1000, 1000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000006', 'admin', '2023-09-11 03:11:12', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2308002', '2023-08-01', '08', '2023', 0, 1000, 1000, 1000, 1000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000007', 'admin', '2023-09-11 03:11:33', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2309003', '2023-09-11', '09', '2023', 0, 3000, 3000, 3000, 3000, 3000, 0, 0, 0, 0, 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `forecast_histories`
--

CREATE TABLE `forecast_histories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `document_no` varchar(30) DEFAULT NULL,
  `issued_date` date DEFAULT NULL,
  `p_month` varchar(10) DEFAULT NULL,
  `p_year` varchar(10) DEFAULT NULL,
  `revision` int(1) DEFAULT 0,
  `month_1` int(11) DEFAULT 0,
  `month_2` int(11) DEFAULT 0,
  `month_3` int(11) DEFAULT 0,
  `month_4` int(11) DEFAULT 0,
  `month_5` int(11) DEFAULT 0,
  `month_6` int(11) DEFAULT 0,
  `month_7` int(11) DEFAULT 0,
  `month_8` int(11) DEFAULT 0,
  `month_9` int(11) DEFAULT 0,
  `month_10` int(11) DEFAULT 0,
  `month_11` int(11) DEFAULT 0,
  `month_12` int(11) DEFAULT 0,
  `remark` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `forecast_histories`
--

INSERT INTO `forecast_histories` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `item_fg_id`, `document_no`, `issued_date`, `p_month`, `p_year`, `revision`, `month_1`, `month_2`, `month_3`, `month_4`, `month_5`, `month_6`, `month_7`, `month_8`, `month_9`, `month_10`, `month_11`, `month_12`, `remark`) VALUES
('20230904000001', 'admin', '2023-09-04 16:12:07', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2309001', '2023-09-01', '09', '2023', 0, 100, 200, 300, 400, 500, 600, 0, 0, 0, 0, 0, 0, ''),
('20230904000002', 'admin', '2023-09-04 16:12:30', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2309002', '2023-09-01', '09', '2023', 1, 100, 400, 500, 400, 500, 600, 0, 0, 0, 0, 0, 0, ''),
('20230904000003', 'admin', '2023-09-04 16:13:37', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2310001', '2023-10-02', '10', '2023', 0, 300, 400, 500, 200, 100, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000001', 'admin', '2023-09-11 03:09:23', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2306001', '2023-06-01', '06', '2023', 0, 1000, 1000, 1000, 1000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000002', 'admin', '2023-09-11 03:09:50', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2306002', '2023-06-01', '06', '2023', 0, 2000, 2000, 2000, 2000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000003', 'admin', '2023-09-11 03:10:10', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2307001', '2023-07-01', '07', '2023', 0, 2000, 2000, 2000, 2000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000004', 'admin', '2023-09-11 03:10:31', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2307002', '2023-07-03', '07', '2023', 0, 3000, 3000, 3000, 3000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000005', 'admin', '2023-09-11 03:10:52', NULL, NULL, 0, 'C001', 'BPIFG-IP08230001', 'FC2308001', '2023-08-01', '08', '2023', 0, 1000, 1000, 1000, 1000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000006', 'admin', '2023-09-11 03:11:12', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2308002', '2023-08-01', '08', '2023', 0, 1000, 1000, 1000, 1000, 0, 0, 0, 0, 0, 0, 0, 0, ''),
('20230911000007', 'admin', '2023-09-11 03:11:33', NULL, NULL, 0, 'C002', 'BPIFG-IP08230002', 'FC2309003', '2023-09-11', '09', '2023', 0, 3000, 3000, 3000, 3000, 3000, 0, 0, 0, 0, 0, 0, 0, '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mps`
--

CREATE TABLE `generate_mps` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `wip_month` varchar(50) DEFAULT NULL,
  `pp` int(11) DEFAULT 0,
  `p1` int(11) DEFAULT 0,
  `p2` int(11) DEFAULT 0,
  `p3` int(11) DEFAULT 0,
  `fg` int(11) DEFAULT 0,
  `os_mpp` int(11) DEFAULT 0,
  `os_so` int(11) DEFAULT 0,
  `total_stock` int(11) DEFAULT 0,
  `balance` int(11) DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `generate_mps_details`
--

CREATE TABLE `generate_mps_details` (
  `id` varchar(20) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` int(4) DEFAULT NULL,
  `ltpp_month` varchar(50) DEFAULT NULL,
  `ltpp_month2` date DEFAULT NULL,
  `hkw` int(11) DEFAULT 0,
  `begin_balance` int(11) DEFAULT 0,
  `ito` int(11) DEFAULT 0,
  `forecast` int(11) DEFAULT 0,
  `delivery_rate` int(11) DEFAULT 0,
  `safety_stock` int(11) DEFAULT 0,
  `prod_plan` int(11) DEFAULT 0,
  `need` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_boxs`
--

CREATE TABLE `item_boxs` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_kind_id` varchar(30) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `material` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_boxs`
--

INSERT INTO `item_boxs` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `item_kind_id`, `name`, `size`, `color`, `material`, `status`) VALUES
('B001', 'admin', '2023-08-08 22:22:24', 'admin', '2023-08-08 22:45:26', 0, 'CB002', 'CONTAINER BOX 2044', '620 x 430 x 250MM', 'GREEN & YELLOW', 'PLASTIC', 0),
('B002', 'admin', '2023-08-08 22:46:00', NULL, NULL, 0, 'CB002', 'CONTAINER BOX 3328', '600 x 400 x 350MM', 'GREEN', 'PLASTIC', 0),
('B003', 'admin', '2023-08-11 14:35:16', NULL, NULL, 0, 'CB002', 'KONTAINER BOX 4088', '425 X 290 X 205 MM', 'BLUE', 'PLASTIC', 0),
('B004', 'admin', '2023-08-11 14:35:44', NULL, NULL, 0, 'CB002', 'KONTAINER BOX BPI', '475 X 300 X 240 MM', 'BLACK', 'PLASTIC', 0),
('B005', 'admin', '2023-08-11 14:36:07', NULL, NULL, 0, 'CB001', 'KARTON BOX 1', '490 X 350 X 370 MM', 'BROWN', 'KARTON', 0),
('B006', 'admin', '2023-08-11 14:36:28', NULL, NULL, 0, 'CB001', 'KARTON BOX 2', '450 X 350 X 370 MM', 'BROWN', 'KARTON', 0),
('B007', 'admin', '2023-08-11 15:03:10', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD KUNING', '420 X 280 X 160 MM', 'YELLOW', 'IMPRABOARD', 0),
('B008', 'admin', '2023-08-11 15:03:45', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K1)', '420 X 280 X 160 MM', 'BLUE', 'IMPRABOARD', 0),
('B009', 'admin', '2023-08-11 15:04:05', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K2)', '700 X 600 X 450 MM', 'BLUE', 'IMPRABOARD', 0),
('B010', 'admin', '2023-08-11 15:04:23', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K3)', '450 X 350 X 250 MM', 'BLUE', 'IMPRABOARD', 0),
('B011', 'admin', '2023-08-11 15:04:53', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K4)', '650 X 650 X 550 MM', 'BLUE', 'IMPRABOARD', 0),
('B012', 'admin', '2023-08-11 15:05:30', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K5)', '750 X 600 X 300 MM', 'BLUE', 'IMPRABOARD', 0),
('B013', 'admin', '2023-08-11 15:06:03', NULL, NULL, 0, 'CB001', 'KARTON BOX EXPORT', '630 X 620 X 500 MM', 'BLUE', 'KARTON', 0),
('B014', 'admin', '2023-08-11 15:07:19', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K6)', '320 X 320 X 460 MM', 'BLUE', 'IMPRABOARD', 0),
('B015', 'admin', '2023-08-11 15:09:15', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K7)', '520 X 460 X 400 MM', 'BLUE', 'IMPRABOARD', 0),
('B016', 'admin', '2023-08-11 15:09:33', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K8)', '1000 X 540 X 400 MM', 'BLUE', 'IMPRABOARD', 0),
('B017', 'admin', '2023-08-11 15:11:50', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K9)', '450 X 445 X 150 MM', 'BLUE', 'IMPRABOARD', 0),
('B018', 'admin', '2023-08-11 15:12:08', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K10)', '720 X 500 X 460 MM', 'BLUE', 'IMPRABOARD', 0),
('B019', 'admin', '2023-08-11 15:12:25', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU (K11)', '385 X 375 X 150 MM', 'BLUE', 'IMPRABOARD', 0),
('B020', 'admin', '2023-08-11 15:12:46', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD BIRU PT.BS', '675 X 520 X 465 MM', 'BLUE', 'IMPRABOARD', 0),
('B021', 'admin', '2023-08-11 15:13:07', NULL, NULL, 0, 'CB002', 'KONTAINER BOX 6653', '670 X 335 X 100 MM', 'BLUE', 'PLASTIC', 0),
('B022', 'admin', '2023-08-11 15:13:24', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD KUNING AJI', '435 X 300 X 280', 'YELLOW', 'IMPRABOARD', 0),
('B023', 'admin', '2023-08-11 15:13:44', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD T32', '850 X 450 X 350', 'BLUE', 'IMPRABOARD', 0),
('B024', 'admin', '2023-08-11 15:14:00', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD T34', '500 X 500 X 300', 'BLUE', 'IMPRABOARD', 0),
('B025', 'admin', '2023-08-11 15:15:06', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD T44', '850 X 450 X 350', 'BLUE', 'IMPRABOARD', 0),
('B026', 'admin', '2023-08-11 15:15:22', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD (K6)', '500 X 460 X 90', 'GRAY', 'IMPRABOARD', 0),
('B027', 'admin', '2023-08-11 15:15:54', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD (K7)', '520 X 460 X 400', 'GRAY', 'IMPRABOARD', 0),
('B028', 'admin', '2023-08-11 15:16:11', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD (K8)', '720 X 500 X 460', 'GRAY', 'IMPRABOARD', 0),
('B029', 'admin', '2023-08-11 15:16:29', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD (K9)', '395 X 375 X 150', 'GRAY', 'IMPRABOARD', 0),
('B030', 'admin', '2023-08-11 15:16:47', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD (K10)', '450 X 445 X 150', 'GRAY', 'IMPRABOARD', 0),
('B031', 'admin', '2023-08-11 15:17:06', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD (K11)', '1000 X 540 X 400', 'GRAY', 'IMPRABOARD', 0),
('B032', 'admin', '2023-08-11 15:17:27', NULL, NULL, 0, 'CB002', 'BOX INFRABOARD (K12)', '670 X 550 X 410', 'GRAY', 'IMPRABOARD', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_categories`
--

CREATE TABLE `item_categories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_categories`
--

INSERT INTO `item_categories` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `status`) VALUES
('C01', 'admin', '2023-08-07 11:26:00', 'admin', '2023-08-07 22:22:48', 0, 'RM', 'RAW MATERIAL', '', 0),
('C02', 'admin', '2023-08-07 11:39:39', 'admin', '2023-08-07 22:22:54', 0, 'SA', 'SUB ASSY', '', 0),
('C03', 'admin', '2023-08-07 11:39:48', 'admin', '2023-08-07 22:22:59', 0, 'FG', 'FINISHED GOOD', '', 0),
('C04', 'admin', '2023-08-07 11:43:26', 'admin', '2023-08-07 22:23:05', 0, 'CO', 'CONSUMABLE', '', 0),
('C05', 'admin', '2023-08-07 11:43:37', 'admin', '2023-08-07 22:23:12', 0, 'AS', 'ASSET', '', 0),
('C06', 'admin', '2023-08-07 11:43:43', 'admin', '2023-08-07 22:23:17', 0, 'SC', 'SCRAP', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_colors`
--

CREATE TABLE `item_colors` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `kind` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_colors`
--

INSERT INTO `item_colors` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `name`, `description`, `kind`, `status`) VALUES
('C001', 'admin', '2023-08-08 22:29:19', 'admin', '2023-10-01 12:07:34', 0, 'BLUE', '', 'CLEAR', 0),
('C002', 'admin', '2023-08-08 22:48:08', 'admin', '2023-10-01 12:07:40', 0, 'GREEN', '', 'COLORFULL', 0),
('C003', 'admin', '2023-08-08 22:48:18', NULL, NULL, 0, 'GREY', '', '', 0),
('C004', 'admin', '2023-08-08 22:50:21', NULL, NULL, 0, 'YELLOW', '', '', 0),
('C005', 'admin', '2023-08-08 22:51:20', 'admin', '2023-10-01 12:07:45', 0, 'BLACK', '', 'BLACK', 0),
('C006', 'admin', '2023-08-08 22:51:43', NULL, NULL, 0, 'WHITE', '', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_familys`
--

CREATE TABLE `item_familys` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `number` varchar(20) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(30) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_familys`
--

INSERT INTO `item_familys` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `item_category_id`, `number`, `account_number`, `account_name`, `name`, `description`, `status`) VALUES
('P01', 'admin', '2023-08-07 11:26:58', 'admin', '2023-08-07 22:23:29', 0, 'C01', 'CP', '', '', 'CHILD PART', '', 0),
('P02', 'admin', '2023-08-07 11:41:52', 'admin', '2023-08-07 22:23:34', 0, 'C01', 'MB', '', '', 'MASTER BATCH', '', 0),
('P03', 'admin', '2023-08-07 11:44:14', 'admin', '2023-08-07 22:23:37', 0, 'C04', 'PB', '', '', 'POLYBAG', '', 0),
('P04', 'admin', '2023-08-07 11:44:31', 'admin', '2023-08-07 22:23:40', 0, 'C06', 'PU', '', '', 'PURGING', '', 0),
('P05', 'admin', '2023-08-07 11:44:42', 'admin', '2023-08-07 22:23:44', 0, 'C01', 'RC', '', '', 'RECYCLE', '', 0),
('P06', 'admin', '2023-08-07 11:44:55', 'admin', '2023-08-07 22:23:47', 0, 'C01', 'VG', '', '', 'VIRGIN', '', 0),
('P07', 'admin', '2023-08-07 11:45:08', 'admin', '2023-08-07 22:23:49', 0, 'C02', 'WP', '', '', 'WIP', '', 0),
('P08', 'admin', '2023-09-21 10:04:33', NULL, NULL, 0, 'C03', 'FG', '', '', 'FINISH GOOD', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_family_subs`
--

CREATE TABLE `item_family_subs` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_family_subs`
--

INSERT INTO `item_family_subs` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `item_category_id`, `item_family_id`, `number`, `name`, `description`, `status`) VALUES
('PS001', 'admin', '2023-09-25 10:40:51', NULL, NULL, 0, 'C01', 'P06', 'PVC', 'PVC', '', 0),
('PS002', 'admin', '2023-09-25 10:41:25', NULL, NULL, 0, 'C01', 'P06', 'ABS', 'ABS', '', 0),
('PS003', 'admin', '2023-09-25 10:41:47', NULL, NULL, 0, 'C01', 'P06', 'ASA', 'ASA', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_fg`
--

CREATE TABLE `item_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `division_id` varchar(30) DEFAULT NULL,
  `number` varchar(30) NOT NULL,
  `number_customer` varchar(30) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `process` varchar(30) DEFAULT NULL,
  `boxs` varchar(30) DEFAULT NULL,
  `polybag` varchar(10) DEFAULT NULL,
  `box_label` varchar(10) DEFAULT NULL,
  `ng_ration` decimal(20,2) DEFAULT 0.00,
  `is_no` varchar(30) DEFAULT NULL,
  `weight` decimal(20,5) DEFAULT 0.00000,
  `color` varchar(30) DEFAULT NULL,
  `leadtime` int(11) DEFAULT 0,
  `mpq` int(11) DEFAULT 0,
  `moq` int(11) DEFAULT 0,
  `uom` varchar(10) DEFAULT NULL,
  `qty_box` int(11) DEFAULT 0,
  `attachment` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_fg`
--

INSERT INTO `item_fg` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `division_id`, `number`, `number_customer`, `name`, `process`, `boxs`, `polybag`, `box_label`, `ng_ration`, `is_no`, `weight`, `color`, `leadtime`, `mpq`, `moq`, `uom`, `qty_box`, `attachment`, `status`) VALUES
('BPIFG-IP08230001', 'admin', '2023-08-18 06:24:55', 'admin', '2023-10-16 18:20:40', 0, 'DIV01', '101', 'PC1', 'TEST FG', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.00', '', '0.00000', 'BLUE', 0, 0, 0, 'PCS', 0, NULL, 0),
('BPIFG-IP08230002', 'admin', '2023-08-18 06:25:29', 'admin', '2023-10-16 18:21:28', 0, 'DIV01', '102', 'PC2', 'TEST FG 2', 'B', 'CONTAINER BOX 3328', 'YES', 'YES', '0.00', '', '0.00000', 'GREEN', 0, 0, 0, 'PCS', 0, NULL, 0),
('BPIFG-IP09230001', 'admin', '2023-09-21 10:13:00', 'admin', '2023-10-16 18:21:33', 0, 'DIV01', 'TEST SEPTI', 'TEST SEPTI', 'COVER', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.00', '', '10.00000', 'BLUE', 14, 10, 100, 'PCS', 10, NULL, 0),
('BPIFG-IP09230002', 'admin', '2023-09-21 14:17:14', NULL, NULL, 0, 'DIV01', '103', '103', '103', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.00', '103', '12.00000', 'BLUE', 1, 0, 0, NULL, 0, NULL, 0),
('BPIFG-IP09230003', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 1', 'PRODUCT 1', 'PRODUCT 1', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '100.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230004', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 2', 'PRODUCT 2', 'PRODUCT 2', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '101.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230005', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 3', 'PRODUCT 3', 'PRODUCT 3', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '102.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230006', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 4', 'PRODUCT 4', 'PRODUCT 4', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '103.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230007', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 5', 'PRODUCT 5', 'PRODUCT 5', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '104.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230008', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 6', 'PRODUCT 6', 'PRODUCT 6', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '105.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230009', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 7', 'PRODUCT 7', 'PRODUCT 7', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '106.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230010', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 8', 'PRODUCT 8', 'PRODUCT 8', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '107.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230011', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 9', 'PRODUCT 9', 'PRODUCT 9', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '108.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230012', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 10', 'PRODUCT 10', 'PRODUCT 10', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '109.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230013', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 11', 'PRODUCT 11', 'PRODUCT 11', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '110.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230014', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 12', 'PRODUCT 12', 'PRODUCT 12', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '111.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230015', 'admin', '2023-09-21 14:20:38', NULL, NULL, 0, 'DIV01', 'PRODUCT 13', 'PRODUCT 13', 'PRODUCT 13', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '112.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230016', 'admin', '2023-09-21 14:20:39', NULL, NULL, 0, 'DIV01', 'PRODUCT 14', 'PRODUCT 14', 'PRODUCT 14', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '113.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230017', 'admin', '2023-09-21 14:20:39', NULL, NULL, 0, 'DIV01', 'PRODUCT 15', 'PRODUCT 15', 'PRODUCT 15', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '', '114.00000', '2', 1, 1, 1, NULL, 0, NULL, 0),
('BPIFG-IP09230018', 'admin', '2023-09-25 08:50:05', NULL, NULL, 0, 'DIV01', 'PRODUCT 100', 'PRODUCT 100 CUSTOMER', 'PRODUCT 100 TEST', 'A', 'CONTAINER BOX 2044', 'YES', 'YES', '0.10', '001', '1.00000', 'BLUE', 1, 1, 1, NULL, 1, NULL, 0),
('BPIFG-IP09230019', 'admin', '2023-09-25 09:08:34', NULL, NULL, 0, 'DIV01', 'PRODUCT 101', 'PRODUCT 101 CUSTOMER', 'PRODUCT 101 TET', 'B', 'CONTAINER BOX 3328', 'YES', 'YES', '1.00', '1', '1.00000', 'GREEN', 1, 1, 1, NULL, 1, NULL, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_kinds`
--

CREATE TABLE `item_kinds` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_kinds`
--

INSERT INTO `item_kinds` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `name`, `description`, `status`) VALUES
('CB001', 'admin', '2023-08-08 22:19:03', 'admin', '2023-08-08 22:43:27', 0, 'CARTON', '', 0),
('CB002', 'admin', '2023-08-08 22:43:48', NULL, NULL, 0, 'REUSEABLE BOX', '', 0),
('CB003', 'admin', '2023-08-08 22:44:27', NULL, NULL, 0, 'SACK', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_mold`
--

CREATE TABLE `item_mold` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `customer_id` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL,
  `model` varchar(30) DEFAULT NULL,
  `mold_size` varchar(30) DEFAULT NULL,
  `project_year` varchar(4) DEFAULT NULL,
  `cavity_standard` varchar(30) DEFAULT NULL,
  `cavity_actual` varchar(30) DEFAULT NULL,
  `shoot_standard` varchar(30) DEFAULT NULL,
  `shoot_actual` varchar(30) DEFAULT NULL,
  `remark` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_mold`
--

INSERT INTO `item_mold` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `item_fg_id`, `customer_id`, `type`, `model`, `mold_size`, `project_year`, `cavity_standard`, `cavity_actual`, `shoot_standard`, `shoot_actual`, `remark`, `status`) VALUES
('M-0823001', 'admin', '2023-08-23 22:40:07', NULL, NULL, 0, 'BPIFG-IP08230001', 'C001', 'INTERNAL', 'MODEL BEI', '', '', '1', '1', '', '', '', 0),
('M-0823002', 'admin', '2023-08-23 22:40:25', NULL, NULL, 0, 'BPIFG-IP08230001', 'C002', 'INTERNAL', 'MODEL BPI 1', '', '', '1', '1', '', '', '', 0),
('M-0823003', 'admin', '2023-08-23 22:40:46', NULL, NULL, 0, 'BPIFG-IP08230002', 'C003', 'INTERNAL', 'MODEL BPI 2', '', '', '1', '1', '', '', '', 0),
('M-0923001', 'admin', '2023-09-21 21:38:42', NULL, NULL, 0, 'BPIFG-IP09230001', 'C002', 'INTERNAL', 'TEST SEPTI', '', '', '1', '1', '', '', '', 0),
('M-0923002', 'admin', '2023-09-22 17:37:04', NULL, NULL, 0, 'BPIFG-IP09230003', 'C002', 'INTERNAL', 'MODEL 1', '100', '2023', '1', '1', '100', '10', '', 0),
('M-0923003', 'admin', '2023-09-25 09:03:52', 'admin', '2023-09-25 09:04:31', 0, 'BPIFG-IP09230018', 'C002', 'INTERNAL', 'TEST MODEL', '5', '2023', '1', '2', '3', '4', 'TEST', 0),
('M-0923004', 'admin', '2023-09-25 09:09:23', NULL, NULL, 0, 'BPIFG-IP09230019', 'C003', 'INTERNAL', 'TEST1', '15', '2023', '1', '2', '3', '4', 'TESTING', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_process`
--

CREATE TABLE `item_process` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_process`
--

INSERT INTO `item_process` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `name`, `description`, `status`) VALUES
('PC001', 'admin', '2023-08-08 22:32:51', 'admin', '2023-08-08 22:54:56', 0, 'INJECTION', '', 0),
('PC002', 'admin', '2023-08-08 22:33:04', 'admin', '2023-08-08 22:55:27', 0, 'PACKAGING', '', 0),
('PC003', 'admin', '2023-08-08 22:55:04', NULL, NULL, 0, 'ASSEMBLY', '', 0),
('PC004', 'admin', '2023-08-08 22:55:15', NULL, NULL, 0, 'SUBCONT', '', 0),
('PC005', 'admin', '2023-08-08 22:55:36', NULL, NULL, 0, 'RECEIVING SUBCONT', '', 0),
('PC006', 'admin', '2023-09-21 21:05:05', NULL, NULL, 0, 'TESTING', 'TESTING', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_process_flow`
--

CREATE TABLE `item_process_flow` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `process_a` int(2) DEFAULT 0,
  `process_b` int(2) DEFAULT 0,
  `process_c` int(2) DEFAULT 0,
  `process_d` int(2) DEFAULT 0,
  `process_e` int(2) DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_process_flow`
--

INSERT INTO `item_process_flow` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `name`, `process_a`, `process_b`, `process_c`, `process_d`, `process_e`, `status`) VALUES
('TP01', 'admin', '2023-08-09 21:18:49', NULL, NULL, 0, 'A', 1, 2, 0, 0, 3, 0),
('TP02', 'admin', '2023-08-09 21:19:12', NULL, NULL, 0, 'B', 0, 0, 1, 2, 0, 0),
('TP03', 'admin', '2023-08-09 21:19:22', 'admin', '2023-08-11 14:25:01', 0, 'C', 0, 0, 1, 2, 3, 0),
('TP04', 'admin', '2023-08-09 21:19:35', NULL, NULL, 0, 'D', 1, 0, 0, 0, 2, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_rm`
--

CREATE TABLE `item_rm` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_sub_family_id` varchar(30) DEFAULT NULL,
  `number` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `uom` varchar(30) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `item_rm`
--

INSERT INTO `item_rm` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `item_category_id`, `item_family_id`, `item_sub_family_id`, `number`, `name`, `uom`, `account_number`, `account_name`, `status`) VALUES
('BPIRM-VG10230003', 'admin', '2023-10-01 11:52:39', NULL, NULL, 0, 'C01', 'P06', 'PS001', 'PVC 2', 'TEST PVC 2', 'MTR', '', '', 0),
('BPIRM-VG10230004', 'admin', '2023-10-01 11:45:19', 'admin', '2023-10-01 11:52:53', 0, 'C01', 'P06', 'PS001', 'PVC 1', 'TEST PVC', 'MTR', '', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `logins`
--

CREATE TABLE `logins` (
  `id` bigint(30) NOT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `mac_address` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `logins`
--

INSERT INTO `logins` (`id`, `created_by`, `created_date`, `deleted`, `ip_address`, `mac_address`, `username`, `description`, `status`) VALUES
(70, NULL, '2023-09-29 16:46:49', 0, '139.255.103.226', '0;padding-left:20', 'MKT01', '', 0),
(72, NULL, '2023-10-03 09:28:14', 0, '139.255.103.226', '0;padding-left:20', 'TCH02', '', 0),
(96, NULL, '2023-10-30 12:42:03', 0, '103.134.87.3', '0;padding-left:20', 'admin', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `logs`
--

CREATE TABLE `logs` (
  `id` bigint(20) NOT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` datetime NOT NULL,
  `deleted` tinyint(1) NOT NULL,
  `ip_address` varchar(30) NOT NULL,
  `action` varchar(30) NOT NULL,
  `menu` varchar(30) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `logs`
--

INSERT INTO `logs` (`id`, `created_by`, `created_date`, `deleted`, `ip_address`, `action`, `menu`, `description`) VALUES
(1, 'admin', '2023-10-01 15:50:30', 0, '111.94.116.8', 'Update Before', 'users', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"created_by\":\"admin\",\"created_date\":\"2021-12-26 11:24:58\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 15:09:23\",\"approved\":\"1\",\"approved_to\":\"admin\",\"approved_by\":\"admin\",\"approved_date\":\"2023-08-30 11:05:51\",\"deleted\":\"0\",\"number\":\"1\",\"name\":\"Administrator\",\"description\":\"\",\"username\":\"admin\",\"password\":\"Login@190320\",\"email\":\"admin@aeconsys.com\",\"phone\":\"88888888888\",\"position\":\"Admin System\",\"avatar\":null,\"theme\":\"material-teal\",\"actived\":\"0\",\"status\":\"0\"}'),
(2, 'admin', '2023-10-01 15:50:30', 0, '111.94.116.8', 'Update New', 'users', '{\"name\":\"Administrator\",\"username\":\"admin\",\"email\":\"admin@aeconsys.com\",\"phone\":\"88888888888\",\"position\":\"Admin System\",\"theme\":\"metro-blue\",\"avatar\":null,\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 15:50:30\"}'),
(3, 'admin', '2023-10-01 15:51:23', 0, '111.94.116.8', 'Create', 'menus', '{\"menus_id\":\"20230811000001\",\"name\":\"Working Calendars\",\"link\":\"master\\/calendars\",\"sort\":\"7\",\"state\":\"\",\"id\":\"20231001000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-01 15:51:23\"}'),
(4, 'admin', '2023-10-01 15:51:29', 0, '111.94.116.8', 'Create', 'setting_menus', '{\"menus_id\":\"20231001000001\",\"m_view\":\"on\",\"id\":\"20231001000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-01 15:51:29\"}'),
(5, 'admin', '2023-10-01 08:51:41', 0, '111.94.116.8', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231001000001\",\"id\":\"20231001000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-01 08:51:41\"}'),
(6, 'admin', '2023-10-01 08:51:46', 0, '111.94.116.8', 'Update Before', 'setting_users', '{\"id\":\"20231001000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-01 08:51:41\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231001000001\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(7, 'admin', '2023-10-01 08:51:46', 0, '111.94.116.8', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 08:51:46\"}'),
(8, NULL, '2023-10-01 21:12:20', 0, '43.254.126.105', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(9, 'admin', '2023-10-01 21:16:50', 0, '43.254.126.105', 'Update Before', 'menus', '{\"id\":\"20230811000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 15:05:49\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-11 15:27:34\",\"deleted\":\"0\",\"menus_id\":\"20230811000010\",\"number\":null,\"name\":\"Generate MPS\",\"description\":null,\"link\":\"\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(10, 'admin', '2023-10-01 21:16:50', 0, '43.254.126.105', 'Update New', 'menus', '{\"menus_id\":\"20230811000010\",\"name\":\"Generate MPS\",\"link\":\"planning\\/generate_mps\",\"sort\":\"1\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 21:16:50\"}'),
(11, 'admin', '2023-10-01 21:17:00', 0, '43.254.126.105', 'Delete', 'setting_menus', '{\"id\":\"20230811000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 15:08:30\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230811000018\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_upload\":\"on\",\"m_download\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"status\":\"0\"}'),
(12, 'admin', '2023-10-01 21:17:00', 0, '43.254.126.105', 'Create', 'setting_menus', '{\"menus_id\":\"20230811000018\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20231001000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 21:17:00\"}'),
(13, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230109000001\",\"id\":20231001000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(14, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230803000001\",\"id\":20231001000003,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(15, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230803000002\",\"id\":20231001000004,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(16, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230803000003\",\"id\":20231001000005,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(17, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230803000006\",\"id\":20231001000006,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(18, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230803000007\",\"id\":20231001000007,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(19, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230807000001\",\"id\":20231001000008,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(20, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230808000001\",\"id\":20231001000009,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(21, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230808000002\",\"id\":20231001000010,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(22, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230808000003\",\"id\":20231001000011,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(23, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230808000004\",\"id\":20231001000012,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(24, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230808000005\",\"id\":20231001000013,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(25, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230810000001\",\"id\":20231001000014,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(26, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000001\",\"id\":20231001000015,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(27, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000002\",\"id\":20231001000016,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(28, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000003\",\"id\":20231001000017,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(29, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000005\",\"id\":20231001000018,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(30, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000006\",\"id\":20231001000019,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(31, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000007\",\"id\":20231001000020,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(32, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000008\",\"id\":20231001000021,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(33, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000009\",\"id\":20231001000022,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(34, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000010\",\"id\":20231001000023,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(35, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000011\",\"id\":20231001000024,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(36, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000012\",\"id\":20231001000025,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(37, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000013\",\"id\":20231001000026,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(38, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000014\",\"id\":20231001000027,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(39, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000015\",\"id\":20231001000028,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(40, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000016\",\"id\":20231001000029,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(41, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000017\",\"id\":20231001000030,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(42, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000018\",\"id\":20231001000031,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(43, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000019\",\"id\":20231001000032,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(44, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000020\",\"id\":20231001000033,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(45, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000021\",\"id\":20231001000034,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(46, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000022\",\"id\":20231001000035,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(47, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000024\",\"id\":20231001000036,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(48, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000025\",\"id\":20231001000037,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(49, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000026\",\"id\":20231001000038,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(50, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000027\",\"id\":20231001000039,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(51, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000028\",\"id\":20231001000040,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(52, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000029\",\"id\":20231001000041,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(53, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000030\",\"id\":20231001000042,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(54, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000031\",\"id\":20231001000043,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(55, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000032\",\"id\":20231001000044,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(56, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000033\",\"id\":20231001000045,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(57, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000034\",\"id\":20231001000046,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(58, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000035\",\"id\":20231001000047,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(59, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000036\",\"id\":20231001000048,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(60, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000037\",\"id\":20231001000049,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(61, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000038\",\"id\":20231001000050,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(62, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000039\",\"id\":20231001000051,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(63, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000040\",\"id\":20231001000052,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(64, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000041\",\"id\":20231001000053,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(65, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000042\",\"id\":20231001000054,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(66, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000043\",\"id\":20231001000055,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(67, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000044\",\"id\":20231001000056,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(68, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000045\",\"id\":20231001000057,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(69, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000046\",\"id\":20231001000058,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(70, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000047\",\"id\":20231001000059,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(71, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000048\",\"id\":20231001000060,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(72, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000049\",\"id\":20231001000061,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(73, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000050\",\"id\":20231001000062,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(74, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000051\",\"id\":20231001000063,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(75, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000052\",\"id\":20231001000064,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(76, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230811000053\",\"id\":20231001000065,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(77, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230814000001\",\"id\":20231001000066,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(78, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230814000002\",\"id\":20231001000067,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(79, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230814000003\",\"id\":20231001000068,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(80, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230815000001\",\"id\":20231001000069,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(81, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230815000002\",\"id\":20231001000070,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(82, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230815000003\",\"id\":20231001000071,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(83, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230817000001\",\"id\":20231001000072,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(84, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230817000002\",\"id\":20231001000073,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(85, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230817000003\",\"id\":20231001000074,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(86, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230818000001\",\"id\":20231001000075,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(87, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230823000001\",\"id\":20231001000076,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(88, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230826000001\",\"id\":20231001000077,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(89, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230826000002\",\"id\":20231001000078,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(90, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230826000003\",\"id\":20231001000079,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(91, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230828000001\",\"id\":20231001000080,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(92, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230828000002\",\"id\":20231001000081,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(93, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230828000003\",\"id\":20231001000082,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(94, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230828000004\",\"id\":20231001000083,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(95, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230828000005\",\"id\":20231001000084,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(96, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230828000006\",\"id\":20231001000085,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(97, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000001\",\"id\":20231001000086,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(98, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000002\",\"id\":20231001000087,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(99, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000005\",\"id\":20231001000088,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(100, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000006\",\"id\":20231001000089,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(101, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000007\",\"id\":20231001000090,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(102, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000008\",\"id\":20231001000091,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(103, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000009\",\"id\":20231001000092,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(104, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000011\",\"id\":20231001000093,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(105, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000012\",\"id\":20231001000094,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(106, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000013\",\"id\":20231001000095,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(107, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000014\",\"id\":20231001000096,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(108, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230830000015\",\"id\":20231001000097,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(109, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230901000001\",\"id\":20231001000098,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(110, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230910000001\",\"id\":20231001000099,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(111, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230918000001\",\"id\":20231001000100,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(112, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20230918000002\",\"id\":20231001000101,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(113, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"20231001000001\",\"id\":20231001000102,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(114, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"44964312f0264429978158ada88843\",\"id\":20231001000103,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(115, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"6ccd20c54d1d415189120ec5cc6c81\",\"id\":20231001000104,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(116, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"b679033b3256414b8f916c69f17674\",\"id\":20231001000105,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(117, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"c8f8362a5f6c432ab27d37213f15d4\",\"id\":20231001000106,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(118, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"id\":20231001000107,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(119, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"d13439e3f2324450a69b4e0e50159a\",\"id\":20231001000108,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(120, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"id\":20231001000109,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(121, 'admin', '2023-10-01 14:17:06', 0, '43.254.126.105', 'Create', 'setting_users', '{\"users_id\":\"IMPLEMENTATOR\",\"menus_id\":\"e3c31e10b6c64e119b068ae4b73be6\",\"id\":20231001000110,\"created_by\":\"admin\",\"created_date\":\"2023-10-01 14:17:06\"}'),
(122, 'admin', '2023-10-01 14:17:15', 0, '43.254.126.105', 'Update Before', 'setting_users', '{\"id\":\"20230811000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 08:09:04\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-11 08:09:58\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230811000018\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(123, 'admin', '2023-10-01 14:17:15', 0, '43.254.126.105', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 14:17:15\"}'),
(124, 'admin', '2023-10-01 14:17:16', 0, '43.254.126.105', 'Update Before', 'setting_users', '{\"id\":\"20230811000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 08:09:04\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 14:17:15\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230811000018\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(125, 'admin', '2023-10-01 14:17:16', 0, '43.254.126.105', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 14:17:16\"}'),
(126, 'admin', '2023-10-01 14:17:17', 0, '43.254.126.105', 'Update Before', 'setting_users', '{\"id\":\"20230811000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 08:09:04\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 14:17:16\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230811000018\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(127, 'admin', '2023-10-01 14:17:17', 0, '43.254.126.105', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 14:17:17\"}'),
(128, 'admin', '2023-10-01 14:17:18', 0, '43.254.126.105', 'Update Before', 'setting_users', '{\"id\":\"20230811000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 08:09:04\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 14:17:17\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230811000018\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(129, 'admin', '2023-10-01 14:17:18', 0, '43.254.126.105', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-01 14:17:18\"}'),
(130, NULL, '2023-10-01 22:34:11', 0, '125.161.197.112', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(131, NULL, '2023-10-01 22:57:27', 0, '180.244.167.152', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(132, NULL, '2023-10-02 08:49:35', 0, '111.94.116.8', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(133, NULL, '2023-10-03 09:02:00', 0, '182.3.43.23', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(134, NULL, '2023-10-03 09:28:14', 0, '139.255.103.226', 'Login', 'Login', '{\"id\":\"20230920000001\",\"departement_id\":null,\"number\":\"TCH02\",\"name\":\"Vimal Raj\",\"username\":\"TCH02\",\"position\":\"TECHNICAL\"}'),
(135, NULL, '2023-10-03 14:18:29', 0, '180.244.167.152', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(136, NULL, '2023-10-03 15:58:49', 0, '103.134.87.3', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(137, NULL, '2023-10-04 09:18:42', 0, '180.244.167.152', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(138, NULL, '2023-10-05 09:57:43', 0, '139.255.103.226', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(139, NULL, '2023-10-05 13:07:04', 0, '43.254.126.105', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(140, NULL, '2023-10-06 12:01:29', 0, '182.0.239.165', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(141, NULL, '2023-10-08 20:30:05', 0, '180.244.163.201', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(142, NULL, '2023-10-08 23:13:03', 0, '180.244.163.201', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(143, NULL, '2023-10-09 11:45:10', 0, '114.10.115.253', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(144, NULL, '2023-10-10 13:19:22', 0, '180.244.163.201', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(145, NULL, '2023-10-10 16:54:37', 0, '180.244.163.201', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(146, NULL, '2023-10-10 17:37:39', 0, '180.244.163.201', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(147, NULL, '2023-10-11 08:57:58', 0, '111.94.81.173', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(148, NULL, '2023-10-11 09:04:09', 0, '111.94.81.173', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(149, NULL, '2023-10-11 09:05:12', 0, '180.244.163.201', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(150, NULL, '2023-10-11 09:06:00', 0, '180.244.163.201', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(151, NULL, '2023-10-12 12:48:45', 0, '13.250.113.96', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(152, NULL, '2023-10-15 16:50:55', 0, '139.255.103.226', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(153, NULL, '2023-10-16 10:01:43', 0, '180.244.165.83', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(154, NULL, '2023-10-16 10:55:14', 0, '103.147.8.209', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(155, NULL, '2023-10-16 11:22:16', 0, '114.122.117.163', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(156, 'admin', '2023-10-16 12:24:23', 0, '114.122.108.11', 'Update Before', 'menus', '{\"id\":\"20230811000016\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 14:57:06\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-11 15:00:23\",\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Sales Order\",\"description\":null,\"link\":\"\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(157, 'admin', '2023-10-16 12:24:23', 0, '114.122.108.11', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order\",\"link\":\"planning\\/sales_orders\",\"sort\":\"1\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 12:24:23\"}'),
(158, 'admin', '2023-10-16 13:40:12', 0, '103.147.8.209', 'Create', 'menus', '{\"menus_id\":\"20230811000001\",\"name\":\"ABC Class\",\"link\":\"master\\/abc_class\",\"sort\":\"8\",\"state\":\"\",\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 13:40:12\"}'),
(159, 'admin', '2023-10-16 13:40:29', 0, '103.147.8.209', 'Create', 'setting_menus', '{\"menus_id\":\"20231016000001\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 13:40:29\"}'),
(160, 'admin', '2023-10-16 06:40:42', 0, '103.147.8.209', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231016000001\",\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 06:40:42\"}'),
(161, 'admin', '2023-10-16 06:40:47', 0, '103.147.8.209', 'Update Before', 'setting_users', '{\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 06:40:42\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231016000001\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(162, 'admin', '2023-10-16 06:40:47', 0, '103.147.8.209', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:47\"}'),
(163, 'admin', '2023-10-16 06:40:48', 0, '103.147.8.209', 'Update Before', 'setting_users', '{\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 06:40:42\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:47\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231016000001\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(164, 'admin', '2023-10-16 06:40:48', 0, '103.147.8.209', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:48\"}'),
(165, 'admin', '2023-10-16 06:40:48', 0, '103.147.8.209', 'Update Before', 'setting_users', '{\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 06:40:42\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:48\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231016000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(166, 'admin', '2023-10-16 06:40:48', 0, '103.147.8.209', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:48\"}'),
(167, 'admin', '2023-10-16 06:40:49', 0, '103.147.8.209', 'Update Before', 'setting_users', '{\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 06:40:42\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:48\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231016000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(168, 'admin', '2023-10-16 06:40:49', 0, '103.147.8.209', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:49\"}'),
(169, 'admin', '2023-10-16 06:40:50', 0, '103.147.8.209', 'Update Before', 'setting_users', '{\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 06:40:42\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:49\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231016000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(170, 'admin', '2023-10-16 06:40:50', 0, '103.147.8.209', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:50\"}'),
(171, 'admin', '2023-10-16 06:40:51', 0, '103.147.8.209', 'Update Before', 'setting_users', '{\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 06:40:42\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:50\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231016000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(172, 'admin', '2023-10-16 06:40:51', 0, '103.147.8.209', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 06:40:51\"}'),
(173, 'admin', '2023-10-16 13:45:11', 0, '103.147.8.209', 'Create', 'abc_class', '{\"class\":\"A\",\"safety_stock\":\"50\",\"formula\":\"X>=5 hari\",\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 13:45:11\"}'),
(174, 'admin', '2023-10-16 13:45:36', 0, '103.147.8.209', 'Create', 'abc_class', '{\"class\":\"B\",\"safety_stock\":\"100\",\"formula\":\"2<X<5\",\"id\":20231016000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-16 13:45:36\"}'),
(175, 'admin', '2023-10-16 13:45:55', 0, '103.147.8.209', 'Create', 'abc_class', '{\"class\":\"C\",\"safety_stock\":\"50\",\"formula\":\"1<X<2\",\"id\":20231016000003,\"created_by\":\"admin\",\"created_date\":\"2023-10-16 13:45:55\"}'),
(176, 'admin', '2023-10-16 13:46:10', 0, '103.147.8.209', 'Create', 'abc_class', '{\"class\":\"D\",\"safety_stock\":\"50\",\"formula\":\"X<1\",\"id\":20231016000004,\"created_by\":\"admin\",\"created_date\":\"2023-10-16 13:46:10\"}'),
(177, NULL, '2023-10-16 18:17:26', 0, '114.122.117.163', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(178, 'admin', '2023-10-16 18:17:41', 0, '114.122.117.163', 'Delete', 'setting_menus', '{\"id\":\"20230811000016\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 14:57:52\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230811000016\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_upload\":\"on\",\"m_download\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"status\":\"0\"}'),
(179, 'admin', '2023-10-16 18:17:41', 0, '114.122.117.163', 'Create', 'setting_menus', '{\"menus_id\":\"20230811000016\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20231016000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-16 18:17:41\"}'),
(180, 'admin', '2023-10-16 11:17:57', 0, '114.122.117.163', 'Update Before', 'setting_users', '{\"id\":\"20230811000016\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 07:58:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-11 07:58:20\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230811000016\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(181, 'admin', '2023-10-16 11:17:57', 0, '114.122.117.163', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 11:17:57\"}'),
(182, 'admin', '2023-10-16 11:17:58', 0, '114.122.117.163', 'Update Before', 'setting_users', '{\"id\":\"20230811000016\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 07:58:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 11:17:57\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230811000016\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(183, 'admin', '2023-10-16 11:17:58', 0, '114.122.117.163', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 11:17:58\"}');
INSERT INTO `logs` (`id`, `created_by`, `created_date`, `deleted`, `ip_address`, `action`, `menu`, `description`) VALUES
(184, 'admin', '2023-10-16 18:18:46', 0, '114.122.117.163', 'Update Before', 'customers', '{\"id\":\"C001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-14 21:11:00\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"number\":\"BEI\",\"name\":\"PT. BANSHU ELECTRIC INDONESIA\",\"description\":\"\",\"type\":null,\"currency\":\"\",\"payment_term\":\"0\",\"taxes\":\"0\",\"bank_account\":null,\"bank_name\":null,\"status\":\"0\"}'),
(185, 'admin', '2023-10-16 18:18:46', 0, '114.122.117.163', 'Update New', 'customers', '{\"id\":\"C001\",\"name\":\"PT. BANSHU ELECTRIC INDONESIA\",\"number\":\"BEI\",\"type\":\"LOCAL\",\"currency\":\"IDR\",\"taxes\":\"11\",\"payment_term\":\"0\",\"bank_account\":\"\",\"bank_name\":\"\",\"status\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 18:18:46\"}'),
(186, 'admin', '2023-10-16 18:19:43', 0, '114.122.117.163', 'Create', 'customer_address', '{\"customer_id\":\"C001\",\"address\":\"JL JUPITER\",\"address_billing\":\"JL JUPITER\",\"contact_person\":\"Mr. John\",\"telp\":\"081289949111\",\"telp_billing\":\"08012090331\",\"email\":\"jupiter@gmail.com\",\"website\":\"jupiter.com\",\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 18:19:43\"}'),
(187, 'admin', '2023-10-16 18:20:40', 0, '114.122.117.163', 'Update Before', 'item_fg', '{\"id\":\"BPIFG-IP08230001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-18 06:24:55\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-23 21:51:07\",\"deleted\":\"0\",\"division_id\":\"DIV01\",\"number\":\"101\",\"number_customer\":\"PC1\",\"name\":\"TEST FG\",\"process\":\"A\",\"boxs\":\"CONTAINER BOX 2044\",\"polybag\":\"YES\",\"box_label\":\"YES\",\"ng_ration\":\"0.00\",\"is_no\":\"\",\"weight\":\"0.00000\",\"color\":\"BLUE\",\"leadtime\":\"0\",\"mpq\":\"0\",\"moq\":\"0\",\"uom\":null,\"qty_box\":\"0\",\"attachment\":null,\"status\":\"0\"}'),
(188, 'admin', '2023-10-16 18:20:40', 0, '114.122.117.163', 'Update New', 'item_fg', '{\"id\":\"BPIFG-IP08230001\",\"number\":\"101\",\"name\":\"TEST FG\",\"number_customer\":\"PC1\",\"process\":\"A\",\"division_id\":\"DIV01\",\"boxs\":\"CONTAINER BOX 2044\",\"polybag\":\"YES\",\"box_label\":\"YES\",\"ng_ration\":\"0.00000\",\"is_no\":\"\",\"weight\":\"0.00\",\"color\":\"BLUE\",\"leadtime\":\"0\",\"mpq\":\"0\",\"moq\":\"0\",\"uom\":\"PCS\",\"qty_box\":\"0\",\"status\":\"0\",\"attachment\":null,\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 18:20:40\"}'),
(189, 'admin', '2023-10-16 18:21:28', 0, '114.122.117.163', 'Update Before', 'item_fg', '{\"id\":\"BPIFG-IP08230002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-18 06:25:29\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-23 21:51:21\",\"deleted\":\"0\",\"division_id\":\"DIV01\",\"number\":\"102\",\"number_customer\":\"PC2\",\"name\":\"TEST FG 2\",\"process\":\"B\",\"boxs\":\"CONTAINER BOX 3328\",\"polybag\":\"YES\",\"box_label\":\"YES\",\"ng_ration\":\"0.00\",\"is_no\":\"\",\"weight\":\"0.00000\",\"color\":\"GREEN\",\"leadtime\":\"0\",\"mpq\":\"0\",\"moq\":\"0\",\"uom\":null,\"qty_box\":\"0\",\"attachment\":null,\"status\":\"0\"}'),
(190, 'admin', '2023-10-16 18:21:28', 0, '114.122.117.163', 'Update New', 'item_fg', '{\"id\":\"BPIFG-IP08230002\",\"number\":\"102\",\"name\":\"TEST FG 2\",\"number_customer\":\"PC2\",\"process\":\"B\",\"division_id\":\"DIV01\",\"boxs\":\"CONTAINER BOX 3328\",\"polybag\":\"YES\",\"box_label\":\"YES\",\"ng_ration\":\"0.00000\",\"is_no\":\"\",\"weight\":\"0.00\",\"color\":\"GREEN\",\"leadtime\":\"0\",\"mpq\":\"0\",\"moq\":\"0\",\"uom\":\"PCS\",\"qty_box\":\"0\",\"status\":\"0\",\"attachment\":null,\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 18:21:28\"}'),
(191, 'admin', '2023-10-16 18:21:33', 0, '114.122.117.163', 'Update Before', 'item_fg', '{\"id\":\"BPIFG-IP09230001\",\"created_by\":\"admin\",\"created_date\":\"2023-09-21 10:13:00\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"division_id\":\"DIV01\",\"number\":\"TEST SEPTI\",\"number_customer\":\"TEST SEPTI\",\"name\":\"COVER\",\"process\":\"A\",\"boxs\":\"CONTAINER BOX 2044\",\"polybag\":\"YES\",\"box_label\":\"YES\",\"ng_ration\":\"0.00\",\"is_no\":\"\",\"weight\":\"10.00000\",\"color\":\"BLUE\",\"leadtime\":\"14\",\"mpq\":\"10\",\"moq\":\"100\",\"uom\":null,\"qty_box\":\"10\",\"attachment\":null,\"status\":\"0\"}'),
(192, 'admin', '2023-10-16 18:21:33', 0, '114.122.117.163', 'Update New', 'item_fg', '{\"id\":\"BPIFG-IP09230001\",\"number\":\"TEST SEPTI\",\"name\":\"COVER\",\"number_customer\":\"TEST SEPTI\",\"process\":\"A\",\"division_id\":\"DIV01\",\"boxs\":\"CONTAINER BOX 2044\",\"polybag\":\"YES\",\"box_label\":\"YES\",\"ng_ration\":\"0.00000\",\"is_no\":\"\",\"weight\":\"10.00\",\"color\":\"BLUE\",\"leadtime\":\"14\",\"mpq\":\"10\",\"moq\":\"100\",\"uom\":\"PCS\",\"qty_box\":\"10\",\"status\":\"0\",\"attachment\":null,\"updated_by\":\"admin\",\"updated_date\":\"2023-10-16 18:21:33\"}'),
(193, NULL, '2023-10-16 19:48:04', 0, '125.165.93.43', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(194, 'admin', '2023-10-16 19:59:52', 0, '125.165.93.43', 'Create', 'sales_orders', '{\"customer_id\":\"C002\",\"customer_order_no\":\"COTEST1\",\"sales_order_date\":\"2023-10-16\",\"sales_order_no\":\"SOC002231016001\",\"division\":\"INJECTION\",\"delivery_date\":\"2023-10-23\",\"customer_address_id\":\"-\",\"remarks\":\"\",\"total_sub\":\"10950000\",\"total_tax\":\"0\",\"pph\":\"0\",\"taxes\":\"0\",\"total_pph\":\"0\",\"total_grand\":\"10950000\",\"item_fg_id\":\"BPIFG-IP08230002\",\"uom\":\"PCS\",\"qty\":\"100\",\"delivery\":\"0\",\"outstanding\":\"100\",\"currency\":\"IDR\",\"price\":\"2000\",\"total\":\"200000\",\"id\":\"20231016000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-16 19:59:52\"}'),
(195, 'admin', '2023-10-16 19:59:52', 0, '125.165.93.43', 'Create', 'sales_orders', '{\"customer_id\":\"C002\",\"customer_order_no\":\"COTEST1\",\"sales_order_date\":\"2023-10-16\",\"sales_order_no\":\"SOC002231016001\",\"division\":\"INJECTION\",\"delivery_date\":\"2023-10-23\",\"customer_address_id\":\"-\",\"remarks\":\"\",\"total_sub\":\"10950000\",\"total_tax\":\"0\",\"pph\":\"0\",\"taxes\":\"0\",\"total_pph\":\"0\",\"total_grand\":\"10950000\",\"item_fg_id\":\"BPIFG-IP09230001\",\"uom\":\"PCS\",\"qty\":\"50\",\"delivery\":\"0\",\"outstanding\":\"50\",\"currency\":\"IDR\",\"price\":\"215000\",\"total\":\"10750000\",\"id\":20231016000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-16 19:59:52\"}'),
(196, NULL, '2023-10-16 20:59:33', 0, '180.244.165.83', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(197, NULL, '2023-10-16 21:08:42', 0, '180.244.165.83', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(198, 'admin', '2023-10-16 21:09:31', 0, '180.244.165.83', 'Create', 'customer_address', '{\"customer_id\":\"C001\",\"address\":\"JL. TAURUS\",\"address_billing\":\"JL. TAURUS\",\"contact_person\":\"\",\"telp\":\"\",\"telp_billing\":\"\",\"email\":\"\",\"website\":\"\",\"id\":20231016000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-16 21:09:31\"}'),
(199, NULL, '2023-10-17 09:47:19', 0, '111.94.81.202', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(200, NULL, '2023-10-17 11:36:40', 0, '111.94.81.202', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(201, NULL, '2023-10-17 14:51:28', 0, '111.94.81.202', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(202, NULL, '2023-10-18 12:37:38', 0, '111.94.81.202', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(203, NULL, '2023-10-18 14:05:18', 0, '43.254.126.105', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(204, NULL, '2023-10-18 15:26:03', 0, '43.254.126.105', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(205, NULL, '2023-10-19 09:54:19', 0, '111.94.81.26', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(206, 'admin', '2023-10-19 09:54:57', 0, '111.94.81.26', 'Update Before', 'menus', '{\"id\":\"20230811000017\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 15:02:47\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Outstanding Sales Order\",\"description\":null,\"link\":\"\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(207, 'admin', '2023-10-19 09:54:57', 0, '111.94.81.26', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Outstanding Sales Order\",\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"2\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 09:54:57\"}'),
(208, 'admin', '2023-10-19 09:55:09', 0, '111.94.81.26', 'Delete', 'setting_menus', '{\"id\":\"20230811000017\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 15:03:37\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230811000017\",\"m_view\":\"on\",\"m_add\":null,\"m_edit\":null,\"m_delete\":null,\"m_upload\":null,\"m_download\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"status\":\"0\"}'),
(209, 'admin', '2023-10-19 09:55:09', 0, '111.94.81.26', 'Create', 'setting_menus', '{\"menus_id\":\"20230811000017\",\"m_view\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"20231019000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 09:55:09\"}'),
(210, 'admin', '2023-10-19 02:55:20', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20230811000017\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 08:03:45\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-11 08:03:58\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230811000017\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(211, 'admin', '2023-10-19 02:55:20', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 02:55:20\"}'),
(212, NULL, '2023-10-19 14:21:00', 0, '111.94.81.26', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(213, 'admin', '2023-10-19 14:35:43', 0, '111.94.81.26', 'Create', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order Delivery\",\"link\":\"planning\\/sales_order_deliveries\",\"sort\":\"2\",\"state\":\"\",\"id\":\"20231019000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:35:43\"}'),
(214, 'admin', '2023-10-19 14:35:57', 0, '111.94.81.26', 'Update Before', 'menus', '{\"id\":\"20230811000017\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 15:02:47\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 09:54:57\",\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Outstanding Sales Order\",\"description\":null,\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(215, 'admin', '2023-10-19 14:35:57', 0, '111.94.81.26', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order Report\",\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"3\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:35:57\"}'),
(216, 'admin', '2023-10-19 14:36:08', 0, '111.94.81.26', 'Create', 'setting_menus', '{\"menus_id\":\"20231019000001\",\"m_view\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20231019000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:36:08\"}'),
(217, 'admin', '2023-10-19 07:36:12', 0, '111.94.81.26', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231019000001\",\"id\":\"20231019000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:36:12\"}'),
(218, 'admin', '2023-10-19 07:36:18', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:36:12\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000001\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(219, 'admin', '2023-10-19 07:36:18', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:36:18\"}'),
(220, 'admin', '2023-10-19 07:36:19', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:36:12\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:36:18\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000001\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(221, 'admin', '2023-10-19 07:36:19', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:36:19\"}'),
(222, 'admin', '2023-10-19 07:36:20', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:36:12\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:36:19\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000001\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(223, 'admin', '2023-10-19 07:36:20', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:36:20\"}'),
(224, 'admin', '2023-10-19 14:39:12', 0, '111.94.81.26', 'Update Before', 'menus', '{\"id\":\"20230811000017\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 15:02:47\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:35:57\",\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Sales Order Report\",\"description\":null,\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(225, 'admin', '2023-10-19 14:39:12', 0, '111.94.81.26', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order Outstanding\",\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"3\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:39:12\"}'),
(226, 'admin', '2023-10-19 14:43:54', 0, '111.94.81.26', 'Create', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Delivery Schedules\",\"link\":\"planning\\/report_delivery_schedules\",\"sort\":\"4\",\"state\":\"\",\"id\":20231019000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:43:54\"}'),
(227, 'admin', '2023-10-19 14:44:14', 0, '111.94.81.26', 'Create', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Delivery Orders\",\"link\":\"planning\\/delivery_orders\",\"sort\":\"5\",\"state\":\"\",\"id\":20231019000003,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:44:14\"}'),
(228, 'admin', '2023-10-19 14:44:34', 0, '111.94.81.26', 'Create', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Shipping Orders\",\"link\":\"planning\\/shipping_orders\",\"sort\":\"6\",\"state\":\"\",\"id\":20231019000004,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:44:34\"}'),
(229, 'admin', '2023-10-19 14:44:51', 0, '111.94.81.26', 'Create', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Delivery Notes\",\"link\":\"planning\\/delivery_notes\",\"sort\":\"7\",\"state\":\"\",\"id\":20231019000005,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:44:51\"}'),
(230, 'admin', '2023-10-19 14:45:15', 0, '111.94.81.26', 'Create', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order Closing\",\"link\":\"planning\\/sales_order_closing\",\"sort\":\"8\",\"state\":\"\",\"id\":20231019000006,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:45:15\"}'),
(231, 'admin', '2023-10-19 14:45:30', 0, '111.94.81.26', 'Update Before', 'menus', '{\"id\":\"20231019000006\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:45:15\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Sales Order Closing\",\"description\":null,\"link\":\"planning\\/sales_order_closing\",\"sort\":\"8\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(232, 'admin', '2023-10-19 14:45:30', 0, '111.94.81.26', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order Closing\",\"link\":\"planning\\/sales_order_closing\",\"sort\":\"2\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:45:30\"}'),
(233, 'admin', '2023-10-19 14:45:36', 0, '111.94.81.26', 'Update Before', 'menus', '{\"id\":\"20231019000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:35:43\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Sales Order Delivery\",\"description\":null,\"link\":\"planning\\/sales_order_deliveries\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(234, 'admin', '2023-10-19 14:45:36', 0, '111.94.81.26', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order Delivery\",\"link\":\"planning\\/sales_order_deliveries\",\"sort\":\"3\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:45:36\"}'),
(235, 'admin', '2023-10-19 14:45:44', 0, '111.94.81.26', 'Update Before', 'menus', '{\"id\":\"20230811000017\",\"created_by\":\"admin\",\"created_date\":\"2023-08-11 15:02:47\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:39:12\",\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Sales Order Outstanding\",\"description\":null,\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(236, 'admin', '2023-10-19 14:45:44', 0, '111.94.81.26', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Sales Order Outstanding\",\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"4\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:45:44\"}'),
(237, 'admin', '2023-10-19 14:46:04', 0, '111.94.81.26', 'Update Before', 'menus', '{\"id\":\"20231019000002\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:43:54\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230811000015\",\"number\":null,\"name\":\"Delivery Schedules\",\"description\":null,\"link\":\"planning\\/report_delivery_schedules\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(238, 'admin', '2023-10-19 14:46:04', 0, '111.94.81.26', 'Update New', 'menus', '{\"menus_id\":\"20230811000015\",\"name\":\"Delivery Schedules\",\"link\":\"planning\\/report_delivery_schedules\",\"sort\":\"8\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 14:46:04\"}'),
(239, 'admin', '2023-10-19 14:46:29', 0, '111.94.81.26', 'Create', 'setting_menus', '{\"menus_id\":\"20231019000005\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20231019000003,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:46:29\"}'),
(240, 'admin', '2023-10-19 14:46:31', 0, '111.94.81.26', 'Create', 'setting_menus', '{\"menus_id\":\"20231019000003\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20231019000004,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:46:31\"}'),
(241, 'admin', '2023-10-19 14:46:35', 0, '111.94.81.26', 'Create', 'setting_menus', '{\"menus_id\":\"20231019000002\",\"m_view\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20231019000005,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:46:35\"}'),
(242, 'admin', '2023-10-19 14:47:44', 0, '111.94.81.26', 'Create', 'setting_menus', '{\"menus_id\":\"20231019000006\",\"m_view\":\"on\",\"m_edit\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20231019000006,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:47:44\"}'),
(243, 'admin', '2023-10-19 14:47:49', 0, '111.94.81.26', 'Create', 'setting_menus', '{\"menus_id\":\"20231019000004\",\"m_view\":\"on\",\"id\":20231019000007,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 14:47:49\"}'),
(244, 'admin', '2023-10-19 07:48:07', 0, '111.94.81.26', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231019000002\",\"id\":20231019000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\"}'),
(245, 'admin', '2023-10-19 07:48:07', 0, '111.94.81.26', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231019000003\",\"id\":20231019000003,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\"}'),
(246, 'admin', '2023-10-19 07:48:07', 0, '111.94.81.26', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231019000004\",\"id\":20231019000004,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\"}'),
(247, 'admin', '2023-10-19 07:48:07', 0, '111.94.81.26', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231019000005\",\"id\":20231019000005,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\"}'),
(248, 'admin', '2023-10-19 07:48:07', 0, '111.94.81.26', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20231019000006\",\"id\":20231019000006,\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\"}'),
(249, 'admin', '2023-10-19 07:48:13', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000006\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000006\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(250, 'admin', '2023-10-19 07:48:13', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:13\"}'),
(251, 'admin', '2023-10-19 07:48:14', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000006\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:13\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000006\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(252, 'admin', '2023-10-19 07:48:14', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:14\"}'),
(253, 'admin', '2023-10-19 07:48:15', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000006\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:14\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000006\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(254, 'admin', '2023-10-19 07:48:15', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:15\"}'),
(255, 'admin', '2023-10-19 07:48:16', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000006\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:15\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000006\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(256, 'admin', '2023-10-19 07:48:16', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:16\"}'),
(257, 'admin', '2023-10-19 07:48:17', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000003\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000003\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(258, 'admin', '2023-10-19 07:48:17', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:17\"}'),
(259, 'admin', '2023-10-19 07:48:18', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000003\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:17\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000003\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(260, 'admin', '2023-10-19 07:48:18', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:18\"}'),
(261, 'admin', '2023-10-19 07:48:19', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000003\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:18\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000003\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(262, 'admin', '2023-10-19 07:48:19', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:19\"}'),
(263, 'admin', '2023-10-19 07:48:19', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000003\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:19\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000003\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(264, 'admin', '2023-10-19 07:48:19', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:19\"}'),
(265, 'admin', '2023-10-19 07:48:20', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000003\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:19\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000003\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(266, 'admin', '2023-10-19 07:48:20', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"0\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:20\"}'),
(267, 'admin', '2023-10-19 07:48:21', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000003\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:20\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000003\",\"v_view\":\"0\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"status\":\"0\"}'),
(268, 'admin', '2023-10-19 07:48:21', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:21\"}'),
(269, 'admin', '2023-10-19 07:48:22', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000004\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000004\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(270, 'admin', '2023-10-19 07:48:22', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:22\"}'),
(271, 'admin', '2023-10-19 07:48:22', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000005\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000005\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(272, 'admin', '2023-10-19 07:48:22', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:22\"}'),
(273, 'admin', '2023-10-19 07:48:23', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000002\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000002\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(274, 'admin', '2023-10-19 07:48:23', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:23\"}'),
(275, 'admin', '2023-10-19 07:48:24', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000005\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:22\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000005\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(276, 'admin', '2023-10-19 07:48:24', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:24\"}'),
(277, 'admin', '2023-10-19 07:48:25', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000005\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:24\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000005\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(278, 'admin', '2023-10-19 07:48:25', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:25\"}'),
(279, 'admin', '2023-10-19 07:48:26', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000005\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:25\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(280, 'admin', '2023-10-19 07:48:26', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:26\"}'),
(281, 'admin', '2023-10-19 07:48:28', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000002\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:23\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000002\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(282, 'admin', '2023-10-19 07:48:28', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:28\"}'),
(283, 'admin', '2023-10-19 07:48:28', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000005\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:26\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(284, 'admin', '2023-10-19 07:48:28', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:28\"}'),
(285, 'admin', '2023-10-19 07:48:29', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000005\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:28\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(286, 'admin', '2023-10-19 07:48:29', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:29\"}'),
(287, 'admin', '2023-10-19 07:48:31', 0, '111.94.81.26', 'Update Before', 'setting_users', '{\"id\":\"20231019000002\",\"created_by\":\"admin\",\"created_date\":\"2023-10-19 07:48:07\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:28\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20231019000002\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(288, 'admin', '2023-10-19 07:48:31', 0, '111.94.81.26', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-10-19 07:48:31\"}'),
(289, NULL, '2023-10-20 14:31:40', 0, '140.213.9.207', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(290, NULL, '2023-10-21 18:41:37', 0, '103.134.87.3', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(291, NULL, '2023-10-22 18:07:14', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(292, NULL, '2023-10-22 19:21:16', 0, '103.134.87.3', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(293, NULL, '2023-10-23 09:32:28', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(294, NULL, '2023-10-24 09:01:01', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(295, NULL, '2023-10-24 09:56:00', 0, '13.250.113.96', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(296, NULL, '2023-10-24 10:21:22', 0, '13.250.113.96', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(297, NULL, '2023-10-24 10:31:31', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(298, NULL, '2023-10-24 11:40:26', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(299, NULL, '2023-10-26 09:33:53', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(300, NULL, '2023-10-26 10:14:26', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(301, NULL, '2023-10-26 11:01:57', 0, '180.244.161.160', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(302, 'admin', '2023-10-26 11:03:20', 0, '111.94.81.157', 'Create', 'sales_order_deliveries', '{\"customer_id\":\"C002\",\"sales_order_no\":\"SOC002231016001\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"50\",\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:03:20\"}'),
(303, 'admin', '2023-10-26 11:04:04', 0, '111.94.81.157', 'Delete', 'sales_order_deliveries', '{\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:03:20\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"sales_order_no\":\"SOC002231016001\",\"customer_id\":\"C002\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"50\",\"status\":\"0\"}'),
(304, 'admin', '2023-10-26 11:04:18', 0, '111.94.81.157', 'Create', 'sales_order_deliveries', '{\"customer_id\":\"C002\",\"sales_order_no\":\"SOC002231016001\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"50\",\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:04:18\"}'),
(305, 'admin', '2023-10-26 11:05:18', 0, '111.94.81.157', 'Create', 'sales_order_deliveries', '{\"customer_id\":\"C002\",\"sales_order_no\":\"SOC002231016001\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-27\",\"qty\":\"1\",\"id\":20231026000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:05:18\"}'),
(306, 'admin', '2023-10-26 11:05:22', 0, '111.94.81.157', 'Delete', 'sales_order_deliveries', '{\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:04:18\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"sales_order_no\":\"SOC002231016001\",\"customer_id\":\"C002\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"50\",\"status\":\"0\"}'),
(307, 'admin', '2023-10-26 11:05:22', 0, '111.94.81.157', 'Delete', 'sales_order_deliveries', '{\"id\":\"20231026000002\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:05:18\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"sales_order_no\":\"SOC002231016001\",\"customer_id\":\"C002\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-27\",\"qty\":\"1\",\"status\":\"0\"}'),
(308, 'admin', '2023-10-26 11:05:25', 0, '111.94.81.157', 'Create', 'sales_order_deliveries', '{\"customer_id\":\"C002\",\"sales_order_no\":\"SOC002231016001\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"1\",\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:05:25\"}'),
(309, 'admin', '2023-10-26 11:05:37', 0, '111.94.81.157', 'Delete', 'sales_order_deliveries', '{\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:05:25\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"sales_order_no\":\"SOC002231016001\",\"customer_id\":\"C002\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"1\",\"status\":\"0\"}'),
(310, 'admin', '2023-10-26 11:05:43', 0, '111.94.81.157', 'Create', 'sales_order_deliveries', '{\"customer_id\":\"C002\",\"sales_order_no\":\"SOC002231016001\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"1\",\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:05:43\"}'),
(311, 'admin', '2023-10-26 11:07:13', 0, '111.94.81.157', 'Delete', 'sales_order_deliveries', '{\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:05:43\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"sales_order_no\":\"SOC002231016001\",\"customer_id\":\"C002\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"1\",\"status\":\"0\"}'),
(312, 'admin', '2023-10-26 11:07:17', 0, '111.94.81.157', 'Create', 'sales_order_deliveries', '{\"customer_id\":\"C002\",\"sales_order_no\":\"SOC002231016001\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-26\",\"qty\":\"10\",\"id\":\"20231026000001\",\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:07:17\"}'),
(313, 'admin', '2023-10-26 11:07:24', 0, '111.94.81.157', 'Create', 'sales_order_deliveries', '{\"customer_id\":\"C002\",\"sales_order_no\":\"SOC002231016001\",\"item_fg_id\":\"BPIFG-IP08230002\",\"trans_date\":\"2023-10-27\",\"qty\":\"20\",\"id\":20231026000002,\"created_by\":\"admin\",\"created_date\":\"2023-10-26 11:07:24\"}'),
(314, NULL, '2023-10-26 11:15:09', 0, '180.244.161.160', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(315, NULL, '2023-10-26 11:17:58', 0, '180.244.161.160', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(316, NULL, '2023-10-26 14:17:39', 0, '43.254.126.105', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(317, NULL, '2023-10-26 14:34:22', 0, '182.3.47.112', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(318, NULL, '2023-10-27 07:26:40', 0, '103.134.87.3', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(319, NULL, '2023-10-27 09:22:34', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(320, NULL, '2023-10-27 13:40:40', 0, '114.124.146.170', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(321, NULL, '2023-10-27 14:00:21', 0, '114.124.146.170', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(322, NULL, '2023-10-27 15:40:33', 0, '114.124.146.170', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(323, NULL, '2023-10-27 16:26:03', 0, '111.94.81.157', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(324, 'admin', '2023-10-27 16:31:43', 0, '111.94.81.157', 'Delete', 'delivery_orders', 'null'),
(325, 'admin', '2023-10-27 16:32:07', 0, '111.94.81.157', 'Delete', 'delivery_orders', 'null'),
(326, NULL, '2023-10-30 12:42:03', 0, '103.134.87.3', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}');

-- --------------------------------------------------------

--
-- Struktur dari tabel `machines`
--

CREATE TABLE `machines` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `specification` text DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `manufacturing_date` date DEFAULT NULL,
  `maker` varchar(30) DEFAULT NULL,
  `toonage` int(11) DEFAULT 0,
  `tiebar` varchar(30) DEFAULT NULL,
  `uom_tiebar` varchar(10) DEFAULT NULL,
  `min_closing` int(11) DEFAULT 0,
  `uom_min` varchar(10) DEFAULT NULL,
  `max_open` int(11) DEFAULT 0,
  `uom_max` varchar(10) DEFAULT NULL,
  `volume` int(11) DEFAULT 0,
  `uom_volume` varchar(10) DEFAULT NULL,
  `diameter` int(11) DEFAULT 0,
  `uom_diameter` varchar(10) DEFAULT NULL,
  `brand` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `machines`
--

INSERT INTO `machines` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `specification`, `purchase_date`, `manufacturing_date`, `maker`, `toonage`, `tiebar`, `uom_tiebar`, `min_closing`, `uom_min`, `max_open`, `uom_max`, `volume`, `uom_volume`, `diameter`, `uom_diameter`, `brand`, `status`) VALUES
('MC-0823001', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 04', 'Injection Molding', 'FS180/355SE', '0000-00-00', '0000-00-00', 'NISSEI', 180, '520 x 430', 'MM', 300, 'MM', 930, 'MM', 306, 'GR', 50, 'MM', 'NISSEI', 0),
('MC-0823002', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 05', 'Injection Molding', 'FE210S50ASE', '0000-00-00', '0000-00-00', 'NISSEI', 210, '570 x 570', 'MM', 260, 'MM', 1000, 'MM', 358, 'GR', 63, 'MM', 'NISSEI', 0),
('MC-0823003', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 10', 'Injection Molding', 'MA 860 /G', '0000-00-00', '0000-00-00', 'HAITIAN', 86, '360 x 360', 'MM', 310, 'MM', 670, 'MM', 139, 'GR', 36, 'MM', 'HAITIAN', 0),
('MC-0823004', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 11', 'Injection Molding', 'MA 860 /G', '0000-00-00', '0000-00-00', 'HAITIAN ', 86, '360 x 360', 'MM', 310, 'MM', 670, 'MM', 140, 'GR', 36, 'MM', 'HAITIAN ', 0),
('MC-0823005', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 13', 'Injection Molding', 'FS360S100ASE', '0000-00-00', '0000-00-00', 'NISSEI', 360, '680 x 700', 'MM', 315, 'MM', 1300, 'MM', 0, 'GR', 75, 'MM', 'NISSEI', 0),
('MC-0823006', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 20', 'Injection Molding', 'IS 80 G-2A', '0000-00-00', '0000-00-00', 'TOSHIBA', 80, '375 x 375', 'MM', 170, 'MM', 630, 'MM', 105, 'GR', 32, 'MM', 'TOSHIBA', 0),
('MC-0823007', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 22', 'Injection Molding', 'PS40E5A', '0000-00-00', '0000-00-00', 'NISSEI', 40, '310 x 310', 'MM', 200, 'MM', 500, 'MM', 30, 'GR', 22, 'MM', 'NISSEI', 0),
('MC-0823008', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 27', 'Injection Molding', 'TE 110', '0000-00-00', '0000-00-00', 'WOOJIN', 110, '410 x 410', 'MM', 350, 'MM', 750, 'MM', 50, 'GR', 25, 'MM', 'WOOJIN', 0),
('MC-0823009', 'admin', '2023-08-16 14:24:11', NULL, NULL, 0, 'INJ 28', 'Injection Molding', 'FN 8000', '0000-00-00', '0000-00-00', 'NISSEI', 450, '820 x 820', 'MM', 375, 'MM', 1400, 'MM', 708, 'GR', 63, 'MM', 'NISSEI', 0),
('MC-0823010', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 29', 'Injection Molding', 'IS 450 GSW', '0000-00-00', '0000-00-00', 'TOSHIBA', 450, '870 x 870', 'MM', 350, 'MM', 1400, 'MM', 1360, 'GR', 90, 'MM', 'TOSHIBA', 0),
('MC-0823011', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 30', 'Injection Molding', 'TB 160S', '0000-00-00', '0000-00-00', 'WOOJIN', 160, '460 x 460', 'MM', 405, 'MM', 855, 'MM', 255, 'GR', 40, 'MM', 'WOOJIN', 0),
('MC-0823012', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 31', 'Injection Molding', 'TB 120S', '0000-00-00', '0000-00-00', 'WOOJIN', 120, '410 x 410', 'MM', 370, 'MM', 770, 'MM', 113, 'GR', 28, 'MM', 'WOOJIN', 0),
('MC-0823013', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 32', 'Injection Molding', 'TB 120S', '0000-00-00', '0000-00-00', 'WOOJIN', 120, '410 x 410', 'MM', 370, 'MM', 770, 'MM', 113, 'GR', 28, 'MM', 'WOOJIN', 0),
('MC-0823014', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 33', 'Injection Molding', 'TB 120S', '0000-00-00', '0000-00-00', 'WOOJIN', 120, '410 x 410', 'MM', 370, 'MM', 770, 'MM', 113, 'GR', 28, 'MM', 'WOOJIN', 0),
('MC-0823015', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 34', 'Injection Molding', 'TB 120S', '0000-00-00', '0000-00-00', 'WOOJIN', 120, '410 x 410', 'MM', 370, 'MM', 770, 'MM', 113, 'GR', 28, 'MM', 'WOOJIN', 0),
('MC-0823016', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 35', 'Injection Molding', 'TB 120S', '0000-00-00', '0000-00-00', 'WOOJIN', 120, '410 x 410', 'MM', 370, 'MM', 770, 'MM', 113, 'GR', 28, 'MM', 'WOOJIN', 0),
('MC-0823017', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 36', 'Injection Molding', 'TB 160S', '0000-00-00', '0000-00-00', 'WOOJIN', 160, '460 x 460', 'MM', 405, 'MM', 855, 'MM', 255, 'GR', 40, 'MM', 'WOOJIN', 0),
('MC-0823018', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 37', 'Injection Molding', 'TB 240S', '0000-00-00', '0000-00-00', 'WOOJIN', 240, '560 x 560', 'MM', 520, 'MM', 1120, 'MM', 507, 'GR', 50, 'MM', 'WOOJIN', 0),
('MC-0823019', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 38', 'Injection Molding', 'MA1200/370G', '0000-00-00', '0000-00-00', 'HAITIAN ', 120, '410 x 410', 'MM', 350, 'MM', 780, 'MM', 195, 'GR', 40, 'MM', 'HAITIAN ', 0),
('MC-0823020', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 39', 'Injection Molding', 'MA1200/370G', '0000-00-00', '0000-00-00', 'HAITIAN ', 120, '410 x 410', 'MM', 350, 'MM', 780, 'MM', 195, 'GR', 40, 'MM', 'HAITIAN ', 0),
('MC-0823021', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 40', 'Injection Molding', 'MA1200/370G', '0000-00-00', '0000-00-00', 'HAITIAN ', 120, '410 x 410', 'MM', 350, 'MM', 780, 'MM', 195, 'GR', 40, 'MM', 'HAITIAN ', 0),
('MC-0823022', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 41', 'Injection Molding', 'MA1200/370G', '0000-00-00', '0000-00-00', 'HAITIAN ', 120, '410 x 410', 'MM', 350, 'MM', 780, 'MM', 195, 'GR', 40, 'MM', 'HAITIAN ', 0),
('MC-0823023', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 42', 'Injection Molding', 'MA1200/370G', '0000-00-00', '0000-00-00', 'HAITIAN ', 120, '410 x 410', 'MM', 350, 'MM', 780, 'MM', 195, 'GR', 40, 'MM', 'HAITIAN ', 0),
('MC-0823024', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 43', 'Injection Molding', 'TB 120S', '0000-00-00', '0000-00-00', 'WOOJIN', 120, '410 x 410', 'MM', 370, 'MM', 770, 'MM', 113, 'GR', 28, 'MM', 'WOOJIN', 0),
('MC-0823025', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 44', 'Injection Molding', 'TB 240 G5', '0000-00-00', '0000-00-00', 'WOOJIN', 240, '560 x 560', 'MM', 520, 'MM', 1120, 'MM', 452, 'GR', 50, 'MM', 'WOOJIN', 0),
('MC-0823026', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 45', 'Injection Molding', 'TB 240 G6', '0000-00-00', '0000-00-00', 'WOOJIN', 240, '560 x 560', 'MM', 520, 'MM', 1120, 'MM', 452, 'GR', 50, 'MM', 'WOOJIN', 0),
('MC-0823027', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 46', 'Injection Molding', 'NEX460-140LE', '0000-00-00', '0000-00-00', 'NISSEI', 460, '820 x 820', 'MM', 360, 'MM', 1790, 'MM', 1107, 'GR', 63, 'MM', 'NISSEI', 0),
('MC-0823028', 'admin', '2023-08-16 14:24:12', NULL, NULL, 0, 'INJ 47', 'Injection Molding', 'MA1600III-ZXD', '0000-00-00', '0000-00-00', 'HAITIAN', 160, '470 x 470', 'MM', 430, 'MM', 950, 'MM', 230, 'GR', 40, 'MM', 'HAITIAN', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `menus`
--

CREATE TABLE `menus` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `menus_id` varchar(30) NOT NULL,
  `number` varchar(20) DEFAULT NULL,
  `name` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `link` text NOT NULL,
  `sort` int(11) NOT NULL,
  `icon` varchar(30) NOT NULL,
  `flag` varchar(10) DEFAULT NULL,
  `color` varchar(10) DEFAULT NULL,
  `state` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menus`
--

INSERT INTO `menus` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `menus_id`, `number`, `name`, `description`, `link`, `sort`, `icon`, `flag`, `color`, `state`, `status`) VALUES
('20230109000001', 'admin', '2023-01-09 00:20:26', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Config ISO', NULL, 'admin/config_iso', 7, '', NULL, NULL, '', 0),
('20230803000001', 'admin', '2023-08-03 17:22:49', NULL, NULL, 0, '', NULL, 'Master Data', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230803000002', 'admin', '2023-08-03 17:25:00', 'admin', '2023-08-11 08:29:02', 0, '20230811000001', NULL, 'Category', NULL, 'master/item_categories', 1, '', NULL, NULL, '', 0),
('20230803000003', 'admin', '2023-08-03 17:25:23', 'admin', '2023-08-11 08:29:29', 0, '20230811000001', NULL, 'Product Family', NULL, 'master/item_familys', 2, '', NULL, NULL, '', 0),
('20230803000006', 'admin', '2023-08-03 17:50:05', 'admin', '2023-08-11 08:31:12', 0, '20230811000001', NULL, 'Currency', NULL, 'master/currencies', 4, '', NULL, NULL, '', 0),
('20230803000007', 'admin', '2023-08-03 17:52:41', 'admin', '2023-08-11 08:31:17', 0, '20230811000001', NULL, 'Unit of Measure', NULL, 'master/uom', 5, '', NULL, NULL, '', 0),
('20230807000001', 'admin', '2023-08-07 17:48:00', 'admin', '2023-08-11 08:30:46', 0, '20230811000001', NULL, 'Sub Product Family', NULL, 'master/item_family_subs', 3, '', NULL, NULL, '', 0),
('20230808000001', 'admin', '2023-08-08 22:18:04', 'admin', '2023-08-11 08:31:48', 0, '20230811000002', NULL, 'Kind Of Box', NULL, 'master/item_kinds', 1, '', NULL, NULL, '', 0),
('20230808000002', 'admin', '2023-08-08 22:21:11', 'admin', '2023-08-11 08:32:06', 0, '20230811000002', NULL, 'Boxs', NULL, 'master/item_boxs', 2, '', NULL, NULL, '', 0),
('20230808000003', 'admin', '2023-08-08 22:24:35', 'admin', '2023-08-11 08:32:17', 0, '20230811000002', NULL, 'Colors', NULL, 'master/item_colors', 3, '', NULL, NULL, '', 0),
('20230808000004', 'admin', '2023-08-08 22:31:53', 'admin', '2023-08-11 08:33:04', 0, '20230811000002', NULL, 'Process', NULL, 'master/item_process', 4, '', NULL, NULL, '', 0),
('20230808000005', 'admin', '2023-08-08 22:34:48', 'admin', '2023-08-11 08:33:21', 0, '20230811000002', NULL, 'Flow Process', NULL, 'master/item_process_flow', 5, '', NULL, NULL, '', 0),
('20230810000001', 'admin', '2023-08-10 20:54:27', 'admin', '2023-08-11 08:33:32', 0, '20230811000002', NULL, 'Item Raw Material', NULL, 'master/item_rm', 6, '', NULL, NULL, '', 0),
('20230811000001', 'admin', '2023-08-11 08:26:24', NULL, NULL, 0, '20230803000001', NULL, 'General Master', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230811000002', 'admin', '2023-08-11 08:27:01', NULL, NULL, 0, '20230803000001', NULL, 'Engineering', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000003', 'admin', '2023-08-11 08:27:11', 'admin', '2023-08-11 08:28:04', 0, '20230803000001', NULL, 'Maintenance', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230811000005', 'admin', '2023-08-11 08:27:29', 'admin', '2023-08-11 08:28:15', 0, '20230803000001', NULL, 'Material Control', NULL, '', 5, '', NULL, NULL, 'closed', 0),
('20230811000006', 'admin', '2023-08-11 08:27:39', 'admin', '2023-08-11 08:28:19', 0, '20230803000001', NULL, 'PPIC', NULL, '', 6, '', NULL, NULL, 'closed', 0),
('20230811000007', 'admin', '2023-08-11 08:27:44', 'admin', '2023-08-11 08:28:24', 0, '20230803000001', NULL, 'General Affair', NULL, '', 7, '', NULL, NULL, 'closed', 0),
('20230811000008', 'admin', '2023-08-11 14:31:34', 'admin', '2023-08-11 14:52:40', 0, '', NULL, 'Manufacturing', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('20230811000009', 'admin', '2023-08-11 14:33:23', 'admin', '2023-09-18 18:43:20', 0, '20230811000008', NULL, 'Advance Planning', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000010', 'admin', '2023-08-11 14:37:36', 'admin', '2023-08-11 15:41:41', 0, '20230811000009', NULL, 'Production Schedule (MPS)', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230811000011', 'admin', '2023-08-11 14:41:34', 'admin', '2023-08-11 14:55:28', 0, '20230811000014', NULL, 'Forecasting', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230811000012', 'admin', '2023-08-11 14:42:42', 'admin', '2023-09-04 16:05:02', 0, '20230811000011', NULL, 'Forecast Customer', NULL, 'planning/forecasts', 1, '', NULL, NULL, '', 0),
('20230811000013', 'admin', '2023-08-11 14:45:56', 'admin', '2023-09-10 23:04:42', 0, '20230811000011', NULL, 'Forecast Analysis', NULL, 'planning/forecast_analysis', 3, '', NULL, NULL, '', 0),
('20230811000014', 'admin', '2023-08-11 14:51:43', 'admin', '2023-08-30 17:15:32', 0, '', NULL, 'Order Management', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230811000015', 'admin', '2023-08-11 14:56:29', 'admin', '2023-08-11 15:00:04', 0, '20230811000014', NULL, 'Customer Order', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000016', 'admin', '2023-08-11 14:57:06', 'admin', '2023-10-16 12:24:23', 0, '20230811000015', NULL, 'Sales Order', NULL, 'planning/sales_orders', 1, '', NULL, NULL, '', 0),
('20230811000017', 'admin', '2023-08-11 15:02:47', 'admin', '2023-10-19 14:45:44', 0, '20230811000015', NULL, 'Sales Order Outstanding', NULL, 'planning/report_outstanding_so', 4, '', NULL, NULL, '', 0),
('20230811000018', 'admin', '2023-08-11 15:05:49', 'admin', '2023-10-01 21:16:50', 0, '20230811000010', NULL, 'Generate MPS', NULL, 'planning/generate_mps', 1, '', NULL, NULL, '', 0),
('20230811000019', 'admin', '2023-08-11 15:06:48', 'admin', '2023-09-18 09:28:39', 0, '20230811000010', NULL, 'Upload Data FG', NULL, 'planning/stock_fg', 2, '', NULL, NULL, '', 0),
('20230811000020', 'admin', '2023-08-11 15:07:12', 'admin', '2023-09-18 09:28:55', 0, '20230811000010', NULL, 'Upload Data WIP', NULL, 'planning/stock_wip', 3, '', NULL, NULL, '', 0),
('20230811000021', 'admin', '2023-08-11 15:08:05', 'admin', '2023-09-18 18:44:14', 0, '20230811000010', NULL, 'Upload OS SO', NULL, 'planning/os_so', 5, '', NULL, NULL, '', 0),
('20230811000022', 'admin', '2023-08-11 15:11:57', 'admin', '2023-08-11 15:39:01', 0, '20230811000009', NULL, 'Monthly Production (MPP)', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000024', 'admin', '2023-08-11 15:12:28', 'admin', '2023-08-11 15:44:12', 0, '20230811000008', NULL, 'Warehouse Management ', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000025', 'admin', '2023-08-11 15:13:32', 'admin', '2023-08-11 15:39:54', 0, '20230811000009', NULL, 'Capacity Planning (CRP)', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230811000026', 'admin', '2023-08-11 15:14:04', NULL, NULL, 0, '20230811000009', NULL, 'Delivery Control ', NULL, '', 5, '', NULL, NULL, 'closed', 0),
('20230811000027', 'admin', '2023-08-11 15:15:45', 'admin', '2023-08-11 15:40:34', 0, '20230811000009', NULL, 'Material Resource (MRP)', NULL, '', 5, '', NULL, NULL, 'closed', 0),
('20230811000028', 'admin', '2023-08-11 15:45:17', 'admin', '2023-08-11 16:38:59', 0, '20230811000036', NULL, 'Vendor Incoming', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230811000029', 'admin', '2023-08-11 15:46:04', 'admin', '2023-08-11 16:39:29', 0, '20230811000036', NULL, 'Issued Material', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000030', 'admin', '2023-08-11 15:46:14', 'admin', '2023-08-11 16:40:56', 0, '20230811000036', NULL, 'Report Transaction RM', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230811000031', 'admin', '2023-08-11 15:51:46', NULL, NULL, 0, '20230811000024', NULL, 'Master Warehouse', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230811000032', 'admin', '2023-08-11 15:52:23', NULL, NULL, 0, '20230811000031', NULL, 'List of Warehouse', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230811000033', 'admin', '2023-08-11 15:52:41', NULL, NULL, 0, '20230811000031', NULL, 'Master Location', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000034', 'admin', '2023-08-11 15:52:50', NULL, NULL, 0, '20230811000031', NULL, 'Setting Item Location', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000035', 'admin', '2023-08-11 15:53:42', NULL, NULL, 0, '20230811000031', NULL, 'Warehouse Transfered', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('20230811000036', 'admin', '2023-08-11 16:36:13', NULL, NULL, 0, '20230811000024', NULL, 'Warehouse Raw Material', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230811000037', 'admin', '2023-08-11 16:36:34', NULL, NULL, 0, '20230811000024', NULL, 'Warehouse FG', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230811000038', 'admin', '2023-08-11 16:42:04', NULL, NULL, 0, '20230811000037', NULL, 'WIP Receipt', NULL, '', 1, '', NULL, NULL, '', 0),
('20230811000039', 'admin', '2023-08-11 16:42:25', NULL, NULL, 0, '20230811000037', NULL, 'Scan Receiving FG', NULL, '', 2, '', NULL, NULL, '', 0),
('20230811000040', 'admin', '2023-08-11 16:43:17', NULL, NULL, 0, '20230811000037', NULL, 'Scan Out Finished Good', NULL, '', 2, '', NULL, NULL, '', 0),
('20230811000041', 'admin', '2023-08-11 16:43:20', NULL, NULL, 0, '20230811000037', NULL, 'Scan Out Finished Good', NULL, '', 3, '', NULL, NULL, '', 0),
('20230811000042', 'admin', '2023-08-11 16:43:44', NULL, NULL, 0, '20230811000037', NULL, 'Report Transaction FG', NULL, '', 4, '', NULL, NULL, '', 0),
('20230811000043', 'admin', '2023-08-11 16:45:40', NULL, NULL, 0, '', NULL, 'Procurement', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('20230811000044', 'admin', '2023-08-11 16:46:08', NULL, NULL, 0, '20230811000043', NULL, 'Purchase Requisition', NULL, '', 1, '', NULL, NULL, '', 0),
('20230811000045', 'admin', '2023-08-11 16:46:21', NULL, NULL, 0, '20230811000043', NULL, 'Convert PR to PO', NULL, '', 2, '', NULL, NULL, '', 0),
('20230811000046', 'admin', '2023-08-11 16:46:41', NULL, NULL, 0, '20230811000043', NULL, 'Vendor Confimation', NULL, '', 3, '', NULL, NULL, '', 0),
('20230811000047', 'admin', '2023-08-11 16:47:13', NULL, NULL, 0, '20230811000043', NULL, 'Outstanding PO', NULL, '', 4, '', NULL, NULL, '', 0),
('20230811000048', 'admin', '2023-08-11 16:47:29', NULL, NULL, 0, '20230811000043', NULL, 'Delivery Confirmation', NULL, '', 5, '', NULL, NULL, '', 0),
('20230811000049', 'admin', '2023-08-11 16:47:55', NULL, NULL, 0, '20230811000043', NULL, 'Vendor Forecast', NULL, '', 6, '', NULL, NULL, '', 0),
('20230811000050', 'admin', '2023-08-11 17:09:36', NULL, NULL, 0, '20230811000008', NULL, 'Shop Floor Control', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230811000051', 'admin', '2023-08-11 17:09:57', 'admin', '2023-08-11 17:10:41', 0, '20230811000050', NULL, 'WIP Position', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230811000052', 'admin', '2023-08-11 17:10:10', 'admin', '2023-08-11 17:12:41', 0, '20230811000052', NULL, 'Subcont Transaction', NULL, '', 1, '', NULL, NULL, '', 0),
('20230811000053', 'admin', '2023-08-11 17:10:21', NULL, NULL, 0, '20230811000050', NULL, 'Subcont ', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230814000001', 'admin', '2023-08-14 20:29:21', 'admin', '2023-08-15 09:20:09', 0, '20230815000001', NULL, 'Customer', NULL, 'master/customers', 1, '', NULL, NULL, '', 0),
('20230814000002', 'admin', '2023-08-14 20:31:09', NULL, NULL, 0, '20230811000002', NULL, 'Item Finish Good', NULL, 'master/item_fg', 8, '', NULL, NULL, '', 0),
('20230814000003', 'admin', '2023-08-14 20:32:53', 'admin', '2023-08-22 13:35:36', 0, '20230811000002', NULL, 'Mold', NULL, 'master/item_mold', 9, '', NULL, NULL, '', 0),
('20230815000001', 'admin', '2023-08-15 09:18:15', NULL, NULL, 0, '20230803000001', NULL, 'Marketing', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230815000002', 'admin', '2023-08-15 16:31:16', NULL, NULL, 0, '20230811000002', NULL, 'Bill of Materials', NULL, 'master/bom', 9, '', NULL, NULL, '', 0),
('20230815000003', 'admin', '2023-08-15 16:31:32', NULL, NULL, 0, '20230811000003', NULL, 'Machines', NULL, 'master/machines', 1, '', NULL, NULL, '', 0),
('20230817000001', 'admin', '2023-08-17 13:35:34', NULL, NULL, 0, '20230811000002', NULL, 'Menu Loadings', NULL, 'master/menu_loadings', 10, '', NULL, NULL, '', 0),
('20230817000002', 'admin', '2023-08-17 13:36:01', NULL, NULL, 0, '20230811000002', NULL, 'Purgings', NULL, 'master/purgings', 11, '', NULL, NULL, '', 0),
('20230817000003', 'admin', '2023-08-17 13:36:32', NULL, NULL, 0, '20230811000002', NULL, 'Setting Parameters', NULL, 'master/setting_parameters', 12, '', NULL, NULL, '', 0),
('20230818000001', 'admin', '2023-08-18 06:08:45', NULL, NULL, 0, '20230811000001', NULL, 'Divisions', NULL, 'master/divisions', 6, '', NULL, NULL, '', 0),
('20230823000001', 'admin', '2023-08-23 21:45:20', 'admin', '2023-08-23 21:48:27', 0, '20230815000001', NULL, 'Customer Items', NULL, 'master/customer_items', 2, '', NULL, NULL, '', 0),
('20230826000001', 'admin', '2023-08-26 10:25:00', NULL, NULL, 0, '20230811000005', NULL, 'Suppliers', NULL, 'master/suppliers', 1, '', NULL, NULL, '', 0),
('20230826000002', 'admin', '2023-08-26 10:25:12', NULL, NULL, 0, '20230811000005', NULL, 'Supplier Items', NULL, 'master/supplier_items', 2, '', NULL, NULL, '', 0),
('20230826000003', 'admin', '2023-08-26 10:25:42', NULL, NULL, 0, '20230811000005', NULL, 'Convertions', NULL, 'master/convertions', 3, '', NULL, NULL, '', 0),
('20230828000001', 'admin', '2023-08-28 09:28:10', NULL, NULL, 0, '20230811000006', NULL, 'Delivery Areas', NULL, 'master/delivery_areas', 1, '', NULL, NULL, '', 0),
('20230828000002', 'admin', '2023-08-28 09:28:26', NULL, NULL, 0, '20230811000006', NULL, 'Subcont Types', NULL, 'master/subcont_types', 2, '', NULL, NULL, '', 0),
('20230828000003', 'admin', '2023-08-28 09:28:33', NULL, NULL, 0, '20230811000006', NULL, 'Subconts', NULL, 'master/subconts', 3, '', NULL, NULL, '', 0),
('20230828000004', 'admin', '2023-08-28 09:28:46', NULL, NULL, 0, '20230811000006', NULL, 'Setting Stocks', NULL, 'master/setting_stocks', 4, '', NULL, NULL, '', 0),
('20230828000005', 'admin', '2023-08-28 10:57:32', 'admin', '2023-08-28 20:38:27', 0, '20230811000006', NULL, 'Setting Subconts Product', NULL, 'master/setting_subconts', 5, '', NULL, NULL, '', 0),
('20230828000006', 'admin', '2023-08-28 16:54:50', 'admin', '2023-09-22 17:28:35', 0, '20230811000007', NULL, 'Vehicle', NULL, 'master/vehicles', 1, '', NULL, NULL, '', 0),
('20230830000001', 'admin', '2023-08-30 15:32:11', 'admin', '2023-08-30 17:28:12', 0, '', NULL, 'Customer Relation', NULL, '', 11, '', NULL, NULL, 'closed', 0),
('20230830000002', 'admin', '2023-08-30 15:34:15', NULL, NULL, 0, '', NULL, 'New Project Development', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('20230830000003', 'admin', '2023-08-30 15:54:59', NULL, NULL, 0, '20230830000002', NULL, 'Feasibility Study', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230830000005', 'admin', '2023-08-30 16:59:48', 'admin', '2023-08-30 17:06:21', 0, '20230830000002', NULL, 'Product Planning & Quality ', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230830000006', 'admin', '2023-08-30 17:00:13', 'admin', '2023-08-30 17:05:13', 0, '20230830000002', NULL, 'Product Design & development', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230830000007', 'admin', '2023-08-30 17:00:39', 'admin', '2023-08-30 17:07:37', 0, '20230830000002', NULL, 'Process Design & Development', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('20230830000008', 'admin', '2023-08-30 17:01:03', 'admin', '2023-08-30 17:05:22', 0, '20230830000002', NULL, 'Product Process & Validation', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('20230830000009', 'admin', '2023-08-30 17:01:50', 'admin', '2023-08-30 17:07:17', 0, '20230830000002', NULL, 'Product Launch & Assestment', NULL, '', 5, '', NULL, NULL, 'closed', 0),
('20230830000010', 'admin', '2023-08-30 17:09:50', NULL, NULL, 0, '20230830000005', NULL, 'Feasibility Study', NULL, '', 1, '', NULL, NULL, '', 0),
('20230830000011', 'admin', '2023-08-30 17:16:42', NULL, NULL, 0, '', NULL, 'Asset Management', NULL, '', 6, '', NULL, NULL, 'closed', 0),
('20230830000012', 'admin', '2023-08-30 17:17:03', NULL, NULL, 0, '', NULL, 'Accounting & Finance', NULL, '', 7, '', NULL, NULL, 'closed', 0),
('20230830000013', 'admin', '2023-08-30 17:19:07', NULL, NULL, 0, '', NULL, 'Budgeting', NULL, '', 8, '', NULL, NULL, 'closed', 0),
('20230830000014', 'admin', '2023-08-30 17:24:55', NULL, NULL, 0, '', NULL, 'Plant Maintenance', NULL, '', 9, '', NULL, NULL, 'closed', 0),
('20230830000015', 'admin', '2023-08-30 17:25:20', NULL, NULL, 0, '', NULL, 'Quality Assurance', NULL, '', 10, '', NULL, NULL, 'closed', 0),
('20230901000001', 'admin', '2023-09-01 16:53:49', NULL, NULL, 0, '20230811000006', NULL, 'Loading Subcont', NULL, 'master/menu_loading_subconts', 6, '', NULL, NULL, '', 0),
('20230910000001', 'admin', '2023-09-10 23:04:10', NULL, NULL, 0, '20230811000011', NULL, 'Summary Forecast', NULL, 'planning/summary_forecasts', 2, '', NULL, NULL, '', 0),
('20230918000001', 'admin', '2023-09-18 18:41:42', 'admin', '2023-09-18 18:44:26', 0, '20230811000010', NULL, 'Upload Data SO', NULL, 'planning/stock_so', 4, '', NULL, NULL, '', 0),
('20230918000002', 'admin', '2023-09-18 18:42:01', NULL, NULL, 0, '20230811000010', NULL, 'Upload OS MPP', NULL, 'planning/os_mpp', 6, '', NULL, NULL, '', 0),
('20231001000001', 'admin', '2023-10-01 15:51:23', NULL, NULL, 0, '20230811000001', NULL, 'Working Calendars', NULL, 'master/calendars', 7, '', NULL, NULL, '', 0),
('20231016000001', 'admin', '2023-10-16 13:40:12', NULL, NULL, 0, '20230811000001', NULL, 'ABC Class', NULL, 'master/abc_class', 8, '', NULL, NULL, '', 0),
('20231019000001', 'admin', '2023-10-19 14:35:43', 'admin', '2023-10-19 14:45:36', 0, '20230811000015', NULL, 'Sales Order Delivery', NULL, 'planning/sales_order_deliveries', 3, '', NULL, NULL, '', 0),
('20231019000002', 'admin', '2023-10-19 14:43:54', 'admin', '2023-10-19 14:46:04', 0, '20230811000015', NULL, 'Delivery Schedules', NULL, 'planning/report_delivery_schedules', 8, '', NULL, NULL, '', 0),
('20231019000003', 'admin', '2023-10-19 14:44:14', NULL, NULL, 0, '20230811000015', NULL, 'Delivery Orders', NULL, 'planning/delivery_orders', 5, '', NULL, NULL, '', 0),
('20231019000004', 'admin', '2023-10-19 14:44:34', NULL, NULL, 0, '20230811000015', NULL, 'Shipping Orders', NULL, 'planning/shipping_orders', 6, '', NULL, NULL, '', 0),
('20231019000005', 'admin', '2023-10-19 14:44:51', NULL, NULL, 0, '20230811000015', NULL, 'Delivery Notes', NULL, 'planning/delivery_notes', 7, '', NULL, NULL, '', 0),
('20231019000006', 'admin', '2023-10-19 14:45:15', 'admin', '2023-10-19 14:45:30', 0, '20230811000015', NULL, 'Sales Order Closing', NULL, 'planning/sales_order_closing', 2, '', NULL, NULL, '', 0),
('44964312f0264429978158ada88843', 'admin', '2022-09-29 16:12:08', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Users', NULL, 'admin/users', 2, '', NULL, NULL, '', 0),
('6ccd20c54d1d415189120ec5cc6c81', 'admin', '2022-09-29 16:41:40', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Config', NULL, 'admin/config', 7, '', NULL, NULL, '', 0),
('b679033b3256414b8f916c69f17674', 'admin', '2022-09-29 16:22:01', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Approval', NULL, 'admin/approvals', 1, '', NULL, NULL, '', 0),
('c8f8362a5f6c432ab27d37213f15d4', 'admin', '2022-09-29 16:35:49', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Setting Users', NULL, 'admin/setting_users', 6, '', NULL, NULL, '', 0),
('cf98f97766f6405590b26daa586e00', 'admin', '2022-09-29 16:05:52', NULL, NULL, 0, '', NULL, 'Administrator', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('d13439e3f2324450a69b4e0e50159a', 'admin', '2022-09-29 16:15:42', 'admin', '2022-09-29 16:36:50', 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Menu', NULL, 'admin/menus', 3, '', NULL, NULL, '', 0),
('de3f6855009e49deb7fd2fdd0f3b3d', 'admin', '2022-09-29 16:32:23', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Logs', NULL, 'admin/logs', 4, '', NULL, NULL, '', 0),
('e3c31e10b6c64e119b068ae4b73be6', 'admin', '2022-09-29 16:35:33', 'admin', '2022-09-29 16:36:31', 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Setting Menu', NULL, 'admin/setting_menus', 5, '', NULL, NULL, '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu_loadings`
--

CREATE TABLE `menu_loadings` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `item_mold_id` varchar(30) DEFAULT NULL,
  `machine_id` varchar(30) DEFAULT NULL,
  `shift` int(11) DEFAULT 0,
  `shift_hour` int(11) DEFAULT 0,
  `productcivity` int(11) DEFAULT 0,
  `cycle_time` decimal(20,2) DEFAULT 0.00,
  `cycle_time_process` decimal(20,2) DEFAULT 0.00,
  `manpower` int(11) DEFAULT 0,
  `runner` decimal(20,5) DEFAULT 0.00000,
  `priority` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu_loadings`
--

INSERT INTO `menu_loadings` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `item_fg_id`, `item_mold_id`, `machine_id`, `shift`, `shift_hour`, `productcivity`, `cycle_time`, `cycle_time_process`, `manpower`, `runner`, `priority`) VALUES
('20230818000001', 'admin', '2023-08-18 06:27:13', NULL, NULL, 0, 'BPIFG-IP08230001', NULL, 'MC-0823001', 3, 7, 0, '0.00', '0.00', 0, '0.00000', 0),
('20230818000002', 'admin', '2023-08-18 06:28:42', 'admin', '2023-08-21 20:58:23', 0, 'BPIFG-IP08230002', NULL, 'MC-0823001', 0, 0, 0, '0.00', '0.00', 0, '0.00000', 0),
('20230818000003', 'admin', '2023-08-18 21:38:07', NULL, NULL, 0, 'BPIFG-IP08230001', NULL, 'MC-0823001', 1, 7, 90, '40.00', '23.11', 2, '0.00257', 1),
('20230821000001', 'admin', '2023-08-21 21:00:05', 'admin', '2023-08-21 21:03:22', 0, 'BPIFG-IP08230002', NULL, 'MC-0823003', 0, 0, 0, '0.00', '0.00', 0, '0.00000', 0),
('20230823000001', 'admin', '2023-08-23 22:41:01', NULL, NULL, 0, 'BPIFG-IP08230001', 'M-0823001', 'MC-0823001', 0, 0, 0, '0.00', '0.00', 0, '0.00000', 0),
('20230823000002', 'admin', '2023-08-23 22:41:14', NULL, NULL, 0, 'BPIFG-IP08230001', 'M-0823002', 'MC-0823002', 0, 0, 0, '0.00', '0.00', 0, '0.00000', 0),
('20230921000001', 'admin', '2023-09-21 21:10:55', NULL, NULL, 0, 'BPIFG-IP09230003', 'M-0823001', 'MC-0823001', 1, 0, 0, '0.00', '0.00', 0, '0.00000', 0),
('20230922000001', 'admin', '2023-09-22 13:21:35', NULL, NULL, 0, 'BPIFG-IP09230001', 'M-0923001', 'MC-0823001', 3, 7, 90, '5.00', '5.00', 1, '2.00000', 1);

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu_loading_subconts`
--

CREATE TABLE `menu_loading_subconts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `subcont_id` varchar(30) DEFAULT NULL,
  `machine_id` varchar(30) DEFAULT NULL,
  `capacity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu_loading_subconts`
--

INSERT INTO `menu_loading_subconts` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `subcont_id`, `machine_id`, `capacity`) VALUES
('20230901000001', 'admin', '2023-09-01 16:54:46', 'admin', '2023-09-01 16:55:09', 0, 'S001', 'MC-0823001', 15000),
('20230904000001', 'admin', '2023-09-04 11:28:34', NULL, NULL, 0, 'S002', 'MC-0823001', 12579),
('20230904000002', 'admin', '2023-09-04 11:28:48', NULL, NULL, 0, 'S002', 'MC-0823004', 1000),
('20230904000003', 'admin', '2023-09-04 11:30:40', NULL, NULL, 0, 'S002', 'MC-0823006', 10001),
('20230904000004', 'admin', '2023-09-04 11:30:40', NULL, NULL, 0, 'S002', 'MC-0823007', 10002);

-- --------------------------------------------------------

--
-- Struktur dari tabel `notifications`
--

CREATE TABLE `notifications` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `approvals_id` varchar(30) DEFAULT NULL,
  `users_id_from` varchar(30) DEFAULT NULL,
  `users_id_to` varchar(30) DEFAULT NULL,
  `table_id` varchar(30) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_mpp`
--

CREATE TABLE `os_mpp` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT 0,
  `document_no` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `os_mpp`
--

INSERT INTO `os_mpp` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `item_fg_id`, `p_month`, `p_year`, `revision`, `document_no`, `qty`) VALUES
('20230921000001', 'admin', '2023-09-21 19:11:02', 'admin', '2023-09-21 19:11:56', 0, 'C002', 'BPIFG-IP09230005', '09', '2023', 0, 'TEST23', '690.00'),
('20230921000002', 'admin', '2023-09-21 19:11:43', NULL, NULL, 0, 'C002', 'BPIFG-IP09230006', '09', '2023', 0, 'TESTMPP1', '700.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `os_so`
--

CREATE TABLE `os_so` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT 0,
  `document_no` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `os_so`
--

INSERT INTO `os_so` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `item_fg_id`, `p_month`, `p_year`, `revision`, `document_no`, `qty`) VALUES
('20230921000001', 'admin', '2023-09-21 19:08:46', NULL, NULL, 0, 'C002', 'BPIFG-IP09230003', '09', '2023', 0, 'S0O21', '100.00'),
('20230921000002', 'admin', '2023-09-21 19:10:29', NULL, NULL, 0, 'C002', 'BPIFG-IP09230007', '09', '2023', 0, 'TESSO', '2000.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `purgings`
--

CREATE TABLE `purgings` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `machine_id` varchar(30) DEFAULT NULL,
  `item_sub_family` varchar(30) DEFAULT NULL,
  `kind` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT 0.00,
  `uom` varchar(10) DEFAULT NULL,
  `total` decimal(20,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `purgings`
--

INSERT INTO `purgings` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `machine_id`, `item_sub_family`, `kind`, `qty`, `uom`, `total`, `status`) VALUES
('20230823000001', 'admin', '2023-08-23 18:52:20', 'admin', '2023-10-01 13:39:34', 0, 'MC-0823002', 'ABS', 'BLACK', '2.50', 'KG', '0.90', 0),
('20230823000002', 'admin', '2023-08-23 23:16:46', NULL, NULL, 0, 'MC-0823003', NULL, NULL, '0.50', 'KG', '0.07', 0),
('20230921000001', 'admin', '2023-09-21 20:28:25', NULL, NULL, 0, 'MC-0823002', NULL, NULL, '2.00', 'KG', '0.72', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_orders`
--

CREATE TABLE `sales_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `customer_address_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `customer_order_no` varchar(30) DEFAULT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `sales_order_date` date DEFAULT NULL,
  `division` varchar(30) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `attachment` text DEFAULT NULL,
  `uom` varchar(20) DEFAULT NULL,
  `pph` int(11) NOT NULL DEFAULT 0,
  `taxes` int(11) NOT NULL DEFAULT 0,
  `qty` int(11) DEFAULT 0,
  `delivery` int(11) NOT NULL DEFAULT 0,
  `outstanding` int(11) NOT NULL DEFAULT 0,
  `currency` varchar(10) DEFAULT NULL,
  `price` int(11) DEFAULT 0,
  `total` int(11) DEFAULT 0,
  `total_sub` int(11) DEFAULT 0,
  `total_tax` int(11) DEFAULT 0,
  `total_pph` int(11) DEFAULT 0,
  `total_grand` int(11) DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sales_orders`
--

INSERT INTO `sales_orders` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `customer_address_id`, `item_fg_id`, `customer_order_no`, `sales_order_no`, `sales_order_date`, `division`, `delivery_date`, `remarks`, `attachment`, `uom`, `pph`, `taxes`, `qty`, `delivery`, `outstanding`, `currency`, `price`, `total`, `total_sub`, `total_tax`, `total_pph`, `total_grand`, `status`) VALUES
('20231016000001', 'admin', '2023-10-16 19:59:52', NULL, NULL, 0, 'C002', '-', 'BPIFG-IP08230002', 'COTEST1', 'SOC002231016001', '2023-10-16', 'INJECTION', '2023-10-23', '', NULL, 'PCS', 0, 0, 100, 0, 100, 'IDR', 2000, 200000, 10950000, 0, 0, 10950000, 0),
('20231016000002', 'admin', '2023-10-16 19:59:52', NULL, NULL, 0, 'C002', '-', 'BPIFG-IP09230001', 'COTEST1', 'SOC002231016001', '2023-10-16', 'INJECTION', '2023-10-23', '', NULL, 'PCS', 0, 0, 50, 0, 50, 'IDR', 215000, 10750000, 10950000, 0, 0, 10950000, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_order_deliveries`
--

CREATE TABLE `sales_order_deliveries` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `qty` int(11) DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sales_order_deliveries`
--

INSERT INTO `sales_order_deliveries` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `sales_order_no`, `customer_id`, `item_fg_id`, `trans_date`, `qty`, `status`) VALUES
('20231026000001', 'admin', '2023-10-26 11:07:17', NULL, NULL, 0, 'SOC002231016001', 'C002', 'BPIFG-IP08230002', '2023-10-26', 10, 0),
('20231026000002', 'admin', '2023-10-26 11:07:24', NULL, NULL, 0, 'SOC002231016001', 'C002', 'BPIFG-IP08230002', '2023-10-27', 20, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_menus`
--

CREATE TABLE `setting_menus` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `menus_id` varchar(30) DEFAULT NULL,
  `m_view` varchar(5) DEFAULT NULL,
  `m_add` varchar(5) DEFAULT NULL,
  `m_edit` varchar(5) DEFAULT NULL,
  `m_delete` varchar(5) DEFAULT NULL,
  `m_upload` varchar(5) DEFAULT NULL,
  `m_download` varchar(5) DEFAULT NULL,
  `m_print` varchar(5) DEFAULT NULL,
  `m_excel` varchar(5) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `setting_menus`
--

INSERT INTO `setting_menus` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `menus_id`, `m_view`, `m_add`, `m_edit`, `m_delete`, `m_upload`, `m_download`, `m_print`, `m_excel`, `status`) VALUES
('04a7682cc50247a8a75f609d17e14a', 'admin', '2022-09-29 17:03:33', NULL, NULL, 0, 'b679033b3256414b8f916c69f17674', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('0fddaa1405bf4a6081704dba2da56b', 'admin', '2022-09-29 17:01:01', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('18944e423a144c35b0c76050a4d74d', 'admin', '2022-09-29 17:04:17', NULL, NULL, 0, 'c8f8362a5f6c432ab27d37213f15d4', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230109000001', 'admin', '2023-01-09 00:20:33', NULL, NULL, 0, '20230109000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230803000001', 'admin', '2023-08-03 17:28:54', NULL, NULL, 0, '20230803000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230803000002', 'admin', '2023-08-03 17:29:01', NULL, NULL, 0, '20230803000002', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230803000003', 'admin', '2023-08-03 17:29:05', NULL, NULL, 0, '20230803000003', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230803000006', 'admin', '2023-08-03 17:50:29', NULL, NULL, 0, '20230803000006', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230803000007', 'admin', '2023-08-03 17:52:55', NULL, NULL, 0, '20230803000007', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230807000001', 'admin', '2023-08-07 17:48:10', NULL, NULL, 0, '20230807000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230808000001', 'admin', '2023-08-08 22:18:23', NULL, NULL, 0, '20230808000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230808000002', 'admin', '2023-08-08 22:21:31', NULL, NULL, 0, '20230808000002', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230808000003', 'admin', '2023-08-08 22:25:02', NULL, NULL, 0, '20230808000003', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230808000004', 'admin', '2023-08-08 22:32:09', NULL, NULL, 0, '20230808000004', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230808000005', 'admin', '2023-08-08 22:35:05', NULL, NULL, 0, '20230808000005', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230810000001', 'admin', '2023-08-10 20:54:52', NULL, NULL, 0, '20230810000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230811000001', 'admin', '2023-08-11 08:33:46', NULL, NULL, 0, '20230811000002', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000002', 'admin', '2023-08-11 08:33:48', NULL, NULL, 0, '20230811000007', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000003', 'admin', '2023-08-11 08:33:50', NULL, NULL, 0, '20230811000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000004', 'admin', '2023-08-11 08:33:52', NULL, NULL, 0, '20230811000003', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000006', 'admin', '2023-08-11 08:33:56', NULL, NULL, 0, '20230811000005', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000007', 'admin', '2023-08-11 08:33:58', NULL, NULL, 0, '20230811000006', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000008', 'admin', '2023-08-11 14:32:13', NULL, NULL, 0, '20230811000008', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000009', 'admin', '2023-08-11 14:33:38', NULL, NULL, 0, '20230811000009', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000010', 'admin', '2023-08-11 14:39:33', NULL, NULL, 0, '20230811000010', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000011', 'admin', '2023-08-11 14:41:47', NULL, NULL, 0, '20230811000011', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000012', 'admin', '2023-08-11 14:43:18', NULL, NULL, 0, '20230811000012', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230811000013', 'admin', '2023-08-11 14:47:04', NULL, NULL, 0, '20230811000013', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230811000014', 'admin', '2023-08-11 14:52:01', NULL, NULL, 0, '20230811000014', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000015', 'admin', '2023-08-11 14:57:36', NULL, NULL, 0, '20230811000015', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000019', 'admin', '2023-08-11 15:08:40', NULL, NULL, 0, '20230811000019', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230811000020', 'admin', '2023-08-11 15:08:46', NULL, NULL, 0, '20230811000020', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230811000021', 'admin', '2023-08-11 15:08:51', NULL, NULL, 0, '20230811000021', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230811000022', 'admin', '2023-08-11 15:16:33', NULL, NULL, 0, '20230811000022', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000023', 'admin', '2023-08-11 15:16:41', NULL, NULL, 0, '20230811000025', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000024', 'admin', '2023-08-11 15:16:55', NULL, NULL, 0, '20230811000027', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000025', 'admin', '2023-08-11 15:44:24', NULL, NULL, 0, '20230811000024', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000026', 'admin', '2023-08-11 15:49:28', NULL, NULL, 0, '20230811000028', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000027', 'admin', '2023-08-11 15:49:39', NULL, NULL, 0, '20230811000029', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000028', 'admin', '2023-08-11 15:49:46', NULL, NULL, 0, '20230811000030', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000029', 'admin', '2023-08-11 16:03:48', NULL, NULL, 0, '20230811000031', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000030', 'admin', '2023-08-11 16:03:56', NULL, NULL, 0, '20230811000033', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000031', 'admin', '2023-08-11 16:04:07', NULL, NULL, 0, '20230811000026', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000032', 'admin', '2023-08-11 16:04:13', NULL, NULL, 0, '20230811000032', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000033', 'admin', '2023-08-11 16:04:19', NULL, NULL, 0, '20230811000034', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000034', 'admin', '2023-08-11 16:04:25', NULL, NULL, 0, '20230811000035', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000035', 'admin', '2023-08-11 16:37:03', NULL, NULL, 0, '20230811000037', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000036', 'admin', '2023-08-11 16:37:08', NULL, NULL, 0, '20230811000036', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000037', 'admin', '2023-08-11 16:44:15', NULL, NULL, 0, '20230811000038', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000038', 'admin', '2023-08-11 16:44:23', NULL, NULL, 0, '20230811000039', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000039', 'admin', '2023-08-11 16:44:34', NULL, NULL, 0, '20230811000042', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000040', 'admin', '2023-08-11 16:44:42', NULL, NULL, 0, '20230811000040', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000041', 'admin', '2023-08-11 16:48:07', NULL, NULL, 0, '20230811000043', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000042', 'admin', '2023-08-11 16:48:17', NULL, NULL, 0, '20230811000044', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000043', 'admin', '2023-08-11 16:48:23', NULL, NULL, 0, '20230811000045', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000044', 'admin', '2023-08-11 16:48:28', NULL, NULL, 0, '20230811000048', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000045', 'admin', '2023-08-11 16:48:35', NULL, NULL, 0, '20230811000047', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000046', 'admin', '2023-08-11 16:48:42', NULL, NULL, 0, '20230811000049', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000047', 'admin', '2023-08-11 16:48:49', NULL, NULL, 0, '20230811000046', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000048', 'admin', '2023-08-11 16:49:00', NULL, NULL, 0, '20230811000041', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000049', 'admin', '2023-08-11 17:10:57', NULL, NULL, 0, '20230811000050', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000050', 'admin', '2023-08-11 17:11:06', NULL, NULL, 0, '20230811000052', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000051', 'admin', '2023-08-11 17:11:11', NULL, NULL, 0, '20230811000053', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230811000052', 'admin', '2023-08-11 17:11:16', NULL, NULL, 0, '20230811000051', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230814000001', 'admin', '2023-08-14 20:29:34', NULL, NULL, 0, '20230814000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230814000002', 'admin', '2023-08-14 20:31:32', NULL, NULL, 0, '20230814000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230814000003', 'admin', '2023-08-14 20:33:08', NULL, NULL, 0, '20230814000003', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230815000001', 'admin', '2023-08-15 09:18:47', NULL, NULL, 0, '20230815000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230815000002', 'admin', '2023-08-15 16:31:43', NULL, NULL, 0, '20230815000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230815000003', 'admin', '2023-08-15 16:31:45', NULL, NULL, 0, '20230815000003', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230817000001', 'admin', '2023-08-17 13:36:47', NULL, NULL, 0, '20230817000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230817000002', 'admin', '2023-08-17 13:36:50', NULL, NULL, 0, '20230817000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230817000003', 'admin', '2023-08-17 13:37:02', NULL, NULL, 0, '20230817000003', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230818000001', 'admin', '2023-08-18 06:08:55', NULL, NULL, 0, '20230818000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230823000001', 'admin', '2023-08-23 21:45:31', NULL, NULL, 0, '20230823000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230826000002', 'admin', '2023-08-26 10:25:56', NULL, NULL, 0, '20230826000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230826000003', 'admin', '2023-08-26 10:25:59', NULL, NULL, 0, '20230826000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230828000004', 'admin', '2023-08-28 09:29:04', NULL, NULL, 0, '20230828000003', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230828000005', 'admin', '2023-08-28 10:57:39', NULL, NULL, 0, '20230828000005', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230828000006', 'admin', '2023-08-28 11:09:08', NULL, NULL, 0, '20230828000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230828000007', 'admin', '2023-08-28 11:09:18', NULL, NULL, 0, '20230828000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230828000008', 'admin', '2023-08-28 11:09:53', NULL, NULL, 0, '20230828000004', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230828000009', 'admin', '2023-08-28 11:10:08', NULL, NULL, 0, '20230826000003', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230828000010', 'admin', '2023-08-28 16:54:59', NULL, NULL, 0, '20230828000006', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230830000001', 'admin', '2023-08-30 15:33:03', NULL, NULL, 0, '20230830000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000002', 'admin', '2023-08-30 15:34:32', NULL, NULL, 0, '20230830000002', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000004', 'admin', '2023-08-30 17:02:26', NULL, NULL, 0, '20230830000005', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000005', 'admin', '2023-08-30 17:02:38', NULL, NULL, 0, '20230830000008', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000006', 'admin', '2023-08-30 17:02:43', NULL, NULL, 0, '20230830000009', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000007', 'admin', '2023-08-30 17:02:46', NULL, NULL, 0, '20230830000006', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000008', 'admin', '2023-08-30 17:03:15', NULL, NULL, 0, '20230830000007', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000009', 'admin', '2023-08-30 17:18:05', NULL, NULL, 0, '20230830000011', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000010', 'admin', '2023-08-30 17:18:22', NULL, NULL, 0, '20230830000012', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000011', 'admin', '2023-08-30 17:19:18', NULL, NULL, 0, '20230830000013', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000012', 'admin', '2023-08-30 17:25:47', NULL, NULL, 0, '20230830000014', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230830000013', 'admin', '2023-08-30 17:26:00', NULL, NULL, 0, '20230830000015', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230901000001', 'admin', '2023-09-01 16:54:02', NULL, NULL, 0, '20230901000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230910000001', 'admin', '2023-09-10 23:04:56', NULL, NULL, 0, '20230910000001', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230918000003', 'admin', '2023-09-18 18:42:31', NULL, NULL, 0, '20230918000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230918000004', 'admin', '2023-09-18 18:42:35', NULL, NULL, 0, '20230918000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20231001000001', 'admin', '2023-10-01 15:51:29', NULL, NULL, 0, '20231001000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20231001000002', 'admin', '2023-10-01 21:17:00', NULL, NULL, 0, '20230811000018', 'on', 'on', NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20231016000001', 'admin', '2023-10-16 13:40:29', NULL, NULL, 0, '20231016000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20231016000002', 'admin', '2023-10-16 18:17:41', NULL, NULL, 0, '20230811000016', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20231019000001', 'admin', '2023-10-19 09:55:09', NULL, NULL, 0, '20230811000017', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20231019000002', 'admin', '2023-10-19 14:36:08', NULL, NULL, 0, '20231019000001', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20231019000003', 'admin', '2023-10-19 14:46:29', NULL, NULL, 0, '20231019000005', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20231019000004', 'admin', '2023-10-19 14:46:31', NULL, NULL, 0, '20231019000003', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20231019000005', 'admin', '2023-10-19 14:46:35', NULL, NULL, 0, '20231019000002', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20231019000006', 'admin', '2023-10-19 14:47:44', NULL, NULL, 0, '20231019000006', 'on', NULL, 'on', NULL, NULL, NULL, 'on', 'on', 0),
('20231019000007', 'admin', '2023-10-19 14:47:49', NULL, NULL, 0, '20231019000004', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('65bdf777a5564d5c94068feb0edcb9', 'admin', '2022-09-29 17:03:57', NULL, NULL, 0, 'de3f6855009e49deb7fd2fdd0f3b3d', 'on', NULL, NULL, 'on', NULL, NULL, 'on', 'on', 0),
('836ff9fa6650482fbf81e4f49bb255', 'admin', '2022-09-29 17:03:46', NULL, NULL, 0, '6ccd20c54d1d415189120ec5cc6c81', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('962311b4039448699f25c8ef64f470', 'admin', '2022-09-29 17:04:22', NULL, NULL, 0, '44964312f0264429978158ada88843', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('972a76a5a04e416e92a9b4e225c19c', 'admin', '2022-09-29 17:04:02', NULL, NULL, 0, 'd13439e3f2324450a69b4e0e50159a', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('c150682d482f4c25b613172ba9b880', 'admin', '2022-09-29 17:04:13', NULL, NULL, 0, 'e3c31e10b6c64e119b068ae4b73be6', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_stocks`
--

CREATE TABLE `setting_stocks` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_category_id` varchar(30) NOT NULL,
  `kind` varchar(10) DEFAULT NULL,
  `min` int(11) DEFAULT 0,
  `max` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `setting_stocks`
--

INSERT INTO `setting_stocks` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `item_category_id`, `kind`, `min`, `max`) VALUES
('20230828000001', 'admin', '2023-08-28 20:07:45', NULL, NULL, 0, 'C01', 'LOCAL', 3, 6),
('20230828000002', 'admin', '2023-08-28 20:08:00', NULL, NULL, 0, 'C01', 'IMPORT', 0, 30),
('20230828000003', 'admin', '2023-08-28 20:08:21', NULL, NULL, 0, 'C03', 'LOCAL', 7, 21);

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_subconts`
--

CREATE TABLE `setting_subconts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `subcont_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `share_order` int(11) DEFAULT 0,
  `type` varchar(20) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,2) NOT NULL DEFAULT 0.00,
  `valid_date` date DEFAULT NULL,
  `capacity` int(11) DEFAULT 0,
  `leadtime` int(11) DEFAULT 0,
  `status` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `setting_subconts`
--

INSERT INTO `setting_subconts` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `subcont_id`, `item_fg_id`, `share_order`, `type`, `currency`, `price`, `valid_date`, `capacity`, `leadtime`, `status`) VALUES
('20230828000001', 'admin', '2023-08-28 20:37:42', 'admin', '2023-08-29 11:56:05', 0, 'S001', 'BPIFG-IP08230001', 5, 'SERVICE CHARGE', 'IDR', '1500.00', '2023-08-31', 12, 30, 0),
('20230922000001', 'admin', '2023-09-22 14:00:03', NULL, NULL, 0, 'S003', 'BPIFG-IP08230001', 100, 'PRODUCT', 'IDR', '100.00', '2023-09-30', 10000, 7, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `setting_users`
--

CREATE TABLE `setting_users` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_date` datetime NOT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `users_id` varchar(30) DEFAULT NULL,
  `menus_id` varchar(30) DEFAULT NULL,
  `v_view` tinyint(1) NOT NULL DEFAULT 0,
  `v_add` tinyint(1) NOT NULL DEFAULT 0,
  `v_edit` tinyint(1) NOT NULL DEFAULT 0,
  `v_delete` tinyint(1) NOT NULL DEFAULT 0,
  `v_upload` tinyint(1) NOT NULL DEFAULT 0,
  `v_download` tinyint(1) NOT NULL DEFAULT 0,
  `v_print` tinyint(1) NOT NULL DEFAULT 0,
  `v_excel` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `setting_users`
--

INSERT INTO `setting_users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `users_id`, `menus_id`, `v_view`, `v_add`, `v_edit`, `v_delete`, `v_upload`, `v_download`, `v_print`, `v_excel`, `status`) VALUES
('20230108000010', 'admin', '2023-01-08 17:20:44', 'admin', '2023-01-08 17:20:57', 0, 'admin', '20230109000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000001', 'admin', '2023-08-03 10:29:16', 'admin', '2023-08-03 10:29:18', 0, 'admin', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000002', 'admin', '2023-08-03 10:29:16', 'admin', '2023-08-03 10:29:32', 0, 'admin', '20230803000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230803000003', 'admin', '2023-08-03 10:29:16', 'admin', '2023-08-03 10:29:32', 0, 'admin', '20230803000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230803000020', 'admin', '2023-08-03 10:53:00', 'admin', '2023-08-03 10:53:09', 0, 'admin', '20230803000006', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230803000021', 'admin', '2023-08-03 10:53:00', 'admin', '2023-08-03 10:53:10', 0, 'admin', '20230803000007', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230807000001', 'admin', '2023-08-07 10:48:15', 'admin', '2023-08-07 10:48:22', 0, 'admin', '20230807000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230808000001', 'admin', '2023-08-08 15:18:29', 'admin', '2023-08-08 15:18:38', 0, 'admin', '20230808000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230808000002', 'admin', '2023-08-08 15:21:40', 'admin', '2023-08-08 15:21:49', 0, 'admin', '20230808000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230808000003', 'admin', '2023-08-08 15:25:07', 'admin', '2023-08-08 15:25:15', 0, 'admin', '20230808000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230808000004', 'admin', '2023-08-08 15:32:18', 'admin', '2023-08-08 15:32:28', 0, 'admin', '20230808000004', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230808000005', 'admin', '2023-08-08 15:35:11', 'admin', '2023-08-08 15:35:20', 0, 'admin', '20230808000005', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230810000001', 'admin', '2023-08-10 13:55:02', 'admin', '2023-08-10 13:55:13', 0, 'admin', '20230810000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230811000001', 'admin', '2023-08-11 01:34:03', 'admin', '2023-08-11 01:34:11', 0, 'admin', '20230811000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000002', 'admin', '2023-08-11 01:34:03', 'admin', '2023-08-11 01:34:12', 0, 'admin', '20230811000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000003', 'admin', '2023-08-11 01:34:03', 'admin', '2023-08-15 09:31:54', 0, 'admin', '20230811000003', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000005', 'admin', '2023-08-11 01:34:03', 'admin', '2023-08-26 03:26:09', 0, 'admin', '20230811000005', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000006', 'admin', '2023-08-11 01:34:03', 'admin', '2023-08-28 02:29:13', 0, 'admin', '20230811000006', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000007', 'admin', '2023-08-11 01:34:03', 'admin', '2023-08-28 09:55:09', 0, 'admin', '20230811000007', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000008', 'admin', '2023-08-11 07:32:32', 'admin', '2023-08-11 07:32:36', 0, 'admin', '20230811000008', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000009', 'admin', '2023-08-11 07:34:04', 'admin', '2023-08-11 07:34:08', 0, 'admin', '20230811000009', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000010', 'admin', '2023-08-11 07:39:41', 'admin', '2023-08-11 07:39:48', 0, 'admin', '20230811000010', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000011', 'admin', '2023-08-11 07:41:57', 'admin', '2023-08-11 07:42:03', 0, 'admin', '20230811000011', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000012', 'admin', '2023-08-11 07:45:02', 'admin', '2023-08-11 07:45:15', 0, 'admin', '20230811000012', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230811000013', 'admin', '2023-08-11 07:46:30', 'admin', '2023-08-11 07:48:52', 0, 'admin', '20230811000013', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20230811000014', 'admin', '2023-08-11 07:54:42', 'admin', '2023-08-11 07:54:45', 0, 'admin', '20230811000014', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000015', 'admin', '2023-08-11 07:58:07', 'admin', '2023-08-11 07:58:13', 0, 'admin', '20230811000015', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000016', 'admin', '2023-08-11 07:58:07', 'admin', '2023-10-16 11:17:58', 0, 'admin', '20230811000016', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230811000017', 'admin', '2023-08-11 08:03:45', 'admin', '2023-10-19 02:55:20', 0, 'admin', '20230811000017', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20230811000018', 'admin', '2023-08-11 08:09:04', 'admin', '2023-10-01 14:17:18', 0, 'admin', '20230811000018', 1, 1, 0, 0, 0, 0, 1, 1, 0),
('20230811000019', 'admin', '2023-08-11 08:09:04', 'admin', '2023-08-11 08:09:59', 0, 'admin', '20230811000019', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230811000020', 'admin', '2023-08-11 08:09:04', 'admin', '2023-08-11 08:09:59', 0, 'admin', '20230811000020', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230811000021', 'admin', '2023-08-11 08:09:04', 'admin', '2023-08-11 08:10:00', 0, 'admin', '20230811000021', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230811000022', 'admin', '2023-08-11 08:17:16', 'admin', '2023-08-11 08:17:53', 0, 'admin', '20230811000022', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000024', 'admin', '2023-08-11 08:17:16', 'admin', '2023-08-11 08:17:54', 0, 'admin', '20230811000025', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000025', 'admin', '2023-08-11 08:17:16', 'admin', '2023-08-11 08:17:55', 0, 'admin', '20230811000027', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000026', 'admin', '2023-08-11 08:44:35', 'admin', '2023-08-11 08:44:40', 0, 'admin', '20230811000024', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000027', 'admin', '2023-08-11 08:50:41', 'admin', '2023-08-11 08:50:47', 0, 'admin', '20230811000028', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000028', 'admin', '2023-08-11 08:50:41', 'admin', '2023-08-11 08:50:48', 0, 'admin', '20230811000029', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000029', 'admin', '2023-08-11 08:50:41', 'admin', '2023-08-11 08:50:48', 0, 'admin', '20230811000030', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000030', 'admin', '2023-08-11 09:29:17', 'admin', '2023-09-18 11:42:54', 0, 'admin', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000031', 'admin', '2023-08-11 09:29:17', 'admin', '2023-08-11 09:29:26', 0, 'admin', '20230811000031', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000032', 'admin', '2023-08-11 09:29:17', 'admin', '2023-08-11 09:29:26', 0, 'admin', '20230811000032', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000033', 'admin', '2023-08-11 09:29:17', 'admin', '2023-08-11 09:29:27', 0, 'admin', '20230811000033', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000034', 'admin', '2023-08-11 09:29:17', 'admin', '2023-08-11 09:29:27', 0, 'admin', '20230811000034', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000035', 'admin', '2023-08-11 09:29:17', 'admin', '2023-08-11 09:29:28', 0, 'admin', '20230811000035', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000036', 'admin', '2023-08-11 09:37:32', 'admin', '2023-08-11 09:37:39', 0, 'admin', '20230811000036', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000037', 'admin', '2023-08-11 09:37:32', 'admin', '2023-08-11 09:37:40', 0, 'admin', '20230811000037', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000038', 'admin', '2023-08-11 09:44:50', 'admin', '2023-08-11 09:44:59', 0, 'admin', '20230811000038', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000039', 'admin', '2023-08-11 09:44:50', 'admin', '2023-08-11 09:44:59', 0, 'admin', '20230811000039', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000040', 'admin', '2023-08-11 09:44:50', 'admin', '2023-08-11 09:45:00', 0, 'admin', '20230811000040', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000041', 'admin', '2023-08-11 09:44:50', 'admin', '2023-08-11 09:45:01', 0, 'admin', '20230811000042', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000042', 'admin', '2023-08-11 09:49:09', NULL, NULL, 0, 'admin', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000043', 'admin', '2023-08-11 09:49:09', 'admin', '2023-08-11 09:49:14', 0, 'admin', '20230811000043', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000044', 'admin', '2023-08-11 09:49:09', 'admin', '2023-08-11 09:49:15', 0, 'admin', '20230811000044', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000045', 'admin', '2023-08-11 09:49:09', 'admin', '2023-08-11 09:49:15', 0, 'admin', '20230811000045', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000046', 'admin', '2023-08-11 09:49:09', 'admin', '2023-08-11 09:49:16', 0, 'admin', '20230811000046', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000047', 'admin', '2023-08-11 09:49:09', 'admin', '2023-08-11 09:49:17', 0, 'admin', '20230811000047', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000048', 'admin', '2023-08-11 09:49:09', 'admin', '2023-08-11 09:49:17', 0, 'admin', '20230811000048', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000049', 'admin', '2023-08-11 09:49:09', 'admin', '2023-08-11 09:49:18', 0, 'admin', '20230811000049', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000050', 'admin', '2023-08-11 10:11:29', 'admin', '2023-08-11 10:11:38', 0, 'admin', '20230811000050', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000051', 'admin', '2023-08-11 10:11:29', 'admin', '2023-08-11 10:11:38', 0, 'admin', '20230811000051', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000052', 'admin', '2023-08-11 10:11:29', 'admin', '2023-08-11 10:11:39', 0, 'admin', '20230811000052', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230811000053', 'admin', '2023-08-11 10:11:29', 'admin', '2023-08-11 10:11:39', 0, 'admin', '20230811000053', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230814000001', 'admin', '2023-08-14 13:29:42', 'admin', '2023-08-14 13:29:58', 0, 'admin', '20230814000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230814000002', 'admin', '2023-08-14 13:31:45', 'admin', '2023-08-14 13:32:01', 0, 'admin', '20230814000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230814000003', 'admin', '2023-08-14 13:33:14', 'admin', '2023-08-14 13:33:27', 0, 'admin', '20230814000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230815000001', 'admin', '2023-08-15 02:18:55', 'admin', '2023-08-15 02:19:53', 0, 'admin', '20230815000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230815000002', 'admin', '2023-08-15 09:31:50', 'admin', '2023-08-15 09:32:02', 0, 'admin', '20230815000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230815000003', 'admin', '2023-08-15 09:31:50', 'admin', '2023-08-15 09:32:09', 0, 'admin', '20230815000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230817000001', 'admin', '2023-08-17 06:37:10', 'admin', '2023-08-17 06:37:41', 0, 'admin', '20230817000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230817000002', 'admin', '2023-08-17 06:37:10', 'admin', '2023-08-17 06:37:42', 0, 'admin', '20230817000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230817000003', 'admin', '2023-08-17 06:37:10', 'admin', '2023-08-17 06:37:44', 0, 'admin', '20230817000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230817000004', 'admin', '2023-08-17 23:09:00', 'admin', '2023-08-17 23:09:20', 0, 'admin', '20230818000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230823000001', 'admin', '2023-08-23 14:45:36', 'admin', '2023-08-23 14:45:47', 0, 'admin', '20230823000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230826000001', 'admin', '2023-08-26 03:26:02', 'admin', '2023-08-26 03:26:22', 0, 'admin', '20230826000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230826000002', 'admin', '2023-08-26 03:26:02', 'admin', '2023-08-26 03:26:22', 0, 'admin', '20230826000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230826000003', 'admin', '2023-08-26 03:26:02', 'admin', '2023-08-26 03:26:23', 0, 'admin', '20230826000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230828000001', 'admin', '2023-08-28 02:29:09', 'admin', '2023-08-28 04:10:19', 0, 'admin', '20230828000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230828000002', 'admin', '2023-08-28 02:29:09', 'admin', '2023-08-28 04:10:20', 0, 'admin', '20230828000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230828000003', 'admin', '2023-08-28 02:29:09', 'admin', '2023-08-28 02:29:29', 0, 'admin', '20230828000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230828000004', 'admin', '2023-08-28 02:29:09', 'admin', '2023-08-28 04:10:21', 0, 'admin', '20230828000004', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230828000005', 'admin', '2023-08-28 03:57:43', 'admin', '2023-08-28 03:57:54', 0, 'admin', '20230828000005', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230828000006', 'admin', '2023-08-28 09:55:06', 'admin', '2023-08-28 09:55:15', 0, 'admin', '20230828000006', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230830000001', 'admin', '2023-08-30 08:33:09', 'admin', '2023-08-30 08:33:18', 0, 'admin', '20230830000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000002', 'admin', '2023-08-30 08:34:42', 'admin', '2023-08-30 08:34:46', 0, 'admin', '20230830000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000004', 'admin', '2023-08-30 10:02:56', 'admin', '2023-08-30 10:03:01', 0, 'admin', '20230830000005', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000005', 'admin', '2023-08-30 10:02:56', 'admin', '2023-08-30 10:03:02', 0, 'admin', '20230830000006', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000006', 'admin', '2023-08-30 10:02:56', 'admin', '2023-08-30 10:03:02', 0, 'admin', '20230830000008', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000007', 'admin', '2023-08-30 10:02:56', 'admin', '2023-08-30 10:03:04', 0, 'admin', '20230830000009', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000008', 'admin', '2023-08-30 10:03:22', 'admin', '2023-08-30 10:03:28', 0, 'admin', '20230830000007', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000009', 'admin', '2023-08-30 10:19:30', 'admin', '2023-08-30 10:19:33', 0, 'admin', '20230830000011', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000010', 'admin', '2023-08-30 10:19:30', 'admin', '2023-08-30 10:19:34', 0, 'admin', '20230830000012', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000011', 'admin', '2023-08-30 10:19:30', 'admin', '2023-08-30 10:19:35', 0, 'admin', '20230830000013', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000012', 'admin', '2023-08-30 10:26:38', 'admin', '2023-08-30 10:26:41', 0, 'admin', '20230830000014', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230830000013', 'admin', '2023-08-30 10:26:38', 'admin', '2023-08-30 10:26:41', 0, 'admin', '20230830000015', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230901000001', 'admin', '2023-09-01 09:54:07', 'admin', '2023-09-01 09:54:18', 0, 'admin', '20230901000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230910000001', 'admin', '2023-09-10 16:05:03', 'admin', '2023-09-10 16:05:10', 0, 'admin', '20230910000001', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20230918000001', 'admin', '2023-09-18 11:42:43', 'admin', '2023-09-18 11:43:07', 0, 'admin', '20230918000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230918000002', 'admin', '2023-09-18 11:42:43', 'admin', '2023-09-18 11:43:07', 0, 'admin', '20230918000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000001', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000002', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:11:56', 0, 'TCH01', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000003', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:12:31', 0, 'TCH01', '20230803000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000004', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:12:33', 0, 'TCH01', '20230803000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000005', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000006', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:12:44', 0, 'TCH01', '20230803000007', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000007', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:12:34', 0, 'TCH01', '20230807000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000008', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:30', 0, 'TCH01', '20230808000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000009', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:34', 0, 'TCH01', '20230808000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000010', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:40', 0, 'TCH01', '20230808000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000011', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:41', 0, 'TCH01', '20230808000004', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000012', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:43', 0, 'TCH01', '20230808000005', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000013', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:47', 0, 'TCH01', '20230810000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000014', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:12:02', 0, 'TCH01', '20230811000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000015', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:12:51', 0, 'TCH01', '20230811000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000016', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000017', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000018', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000019', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000020', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000021', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000022', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000023', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000024', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000025', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000026', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000027', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000028', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000029', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000030', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000031', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000032', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000033', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000034', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000035', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000036', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000037', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000038', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000039', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000040', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000041', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000042', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000043', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000044', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000045', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000046', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000047', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000048', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000049', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000050', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000051', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000052', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000053', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000054', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000055', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000056', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000057', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000058', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000059', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000060', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000061', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000062', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000063', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000064', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000065', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000066', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:47', 0, 'TCH01', '20230814000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000067', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:54', 0, 'TCH01', '20230814000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000068', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000069', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:55', 0, 'TCH01', '20230815000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000070', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000071', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:56', 0, 'TCH01', '20230817000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000072', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:57', 0, 'TCH01', '20230817000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000073', 'admin', '2023-09-20 02:11:43', 'admin', '2023-09-20 02:16:59', 0, 'TCH01', '20230817000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000074', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000075', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000076', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000077', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000078', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000079', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000080', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000081', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000082', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000083', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000084', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000085', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000086', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000087', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000088', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000089', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000090', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000091', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000092', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000093', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000094', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000095', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000096', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000097', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000098', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000099', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000100', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000101', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000102', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000103', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000104', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000105', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000106', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000107', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000108', 'admin', '2023-09-20 02:11:43', NULL, NULL, 0, 'TCH01', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000109', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000110', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:17:33', 0, 'TCH02', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000111', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:18:09', 0, 'TCH02', '20230803000002', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000112', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:18:07', 0, 'TCH02', '20230803000003', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000113', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000114', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:18:03', 0, 'TCH02', '20230803000007', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000115', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:18:06', 0, 'TCH02', '20230807000001', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000116', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:25', 0, 'TCH02', '20230808000001', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000117', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:24', 0, 'TCH02', '20230808000002', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000118', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:26', 0, 'TCH02', '20230808000003', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000119', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:27', 0, 'TCH02', '20230808000004', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000120', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:28', 0, 'TCH02', '20230808000005', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000121', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:28', 0, 'TCH02', '20230810000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000122', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:17:38', 0, 'TCH02', '20230811000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000123', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:17:37', 0, 'TCH02', '20230811000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000124', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000125', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000126', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000127', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000128', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000129', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000130', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000131', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000132', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000133', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000134', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000135', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000136', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000137', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000138', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000139', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000140', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000141', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000142', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000143', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000144', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000145', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000146', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000147', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000148', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000149', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000150', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000151', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000152', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000153', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000154', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000155', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000156', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000157', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000158', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000159', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000160', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000161', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000162', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000163', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000164', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000165', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000166', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000167', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000168', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000169', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000170', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000171', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000172', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000173', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000174', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:29', 0, 'TCH02', '20230814000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000175', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:30', 0, 'TCH02', '20230814000003', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000176', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000177', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:31', 0, 'TCH02', '20230815000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000178', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000179', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:32', 0, 'TCH02', '20230817000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000180', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:33', 0, 'TCH02', '20230817000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000181', 'admin', '2023-09-20 02:17:29', 'admin', '2023-09-20 02:20:34', 0, 'TCH02', '20230817000003', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000182', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000183', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000184', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000185', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000186', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000187', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000188', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000189', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000190', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000191', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000192', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000193', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000194', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000195', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000196', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000197', 'admin', '2023-09-20 02:17:29', NULL, NULL, 0, 'TCH02', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000198', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000199', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000200', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000201', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000202', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000203', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000204', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000205', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000206', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000207', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000208', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000209', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000210', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000211', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000212', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000213', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000214', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000215', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000216', 'admin', '2023-09-20 02:17:30', NULL, NULL, 0, 'TCH02', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000217', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000218', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:20:45', 0, 'TCH03', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000219', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:21:23', 0, 'TCH03', '20230803000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000220', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:21:24', 0, 'TCH03', '20230803000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000221', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000222', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:21:26', 0, 'TCH03', '20230803000007', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000223', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:21:25', 0, 'TCH03', '20230807000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230920000224', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:22:40', 0, 'TCH03', '20230808000001', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000225', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:22:42', 0, 'TCH03', '20230808000002', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000226', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:23:05', 0, 'TCH03', '20230808000003', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000227', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:23:04', 0, 'TCH03', '20230808000004', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000228', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:23:03', 0, 'TCH03', '20230808000005', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000229', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:23:02', 0, 'TCH03', '20230810000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000230', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:20:52', 0, 'TCH03', '20230811000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000231', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:20:49', 0, 'TCH03', '20230811000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000232', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000233', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000234', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000235', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000236', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000237', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000238', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000239', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000240', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000241', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000242', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000243', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000244', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000245', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000246', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000247', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000248', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000249', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000250', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000251', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000252', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000253', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000254', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000255', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000256', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000257', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000258', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000259', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000260', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000261', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000262', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000263', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000264', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000265', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000266', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000267', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000268', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000269', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000270', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000271', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000272', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000273', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000274', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000275', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000276', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000277', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000278', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000279', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000280', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000281', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000282', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:23:01', 0, 'TCH03', '20230814000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000283', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:23:00', 0, 'TCH03', '20230814000003', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000284', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000285', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:22:59', 0, 'TCH03', '20230815000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000286', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000287', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:22:58', 0, 'TCH03', '20230817000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000288', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:22:57', 0, 'TCH03', '20230817000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000289', 'admin', '2023-09-20 02:20:43', 'admin', '2023-09-20 02:22:55', 0, 'TCH03', '20230817000003', 1, 1, 1, 0, 0, 0, 1, 1, 0);
INSERT INTO `setting_users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `users_id`, `menus_id`, `v_view`, `v_add`, `v_edit`, `v_delete`, `v_upload`, `v_download`, `v_print`, `v_excel`, `status`) VALUES
('20230920000290', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000291', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000292', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000293', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000294', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000295', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000296', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000297', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000298', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000299', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000300', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000301', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000302', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000303', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000304', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000305', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000306', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000307', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000308', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000309', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000310', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000311', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000312', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000313', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000314', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000315', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000316', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000317', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000318', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000319', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000320', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000321', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000322', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000323', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000324', 'admin', '2023-09-20 02:20:43', NULL, NULL, 0, 'TCH03', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000325', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000326', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:25:13', 0, 'SCM01', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000327', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000328', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000329', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000330', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000331', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000332', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000333', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000334', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000335', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000336', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000337', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000338', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000339', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000340', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000341', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:25:15', 0, 'SCM01', '20230811000005', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000342', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:26:02', 0, 'SCM01', '20230811000006', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000343', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000344', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000345', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000346', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000347', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000348', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000349', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000350', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000351', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000352', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000353', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000354', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000355', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000356', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000357', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000358', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000359', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000360', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000361', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000362', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000363', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000364', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000365', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000366', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000367', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000368', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000369', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000370', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000371', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000372', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000373', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000374', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000375', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000376', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000377', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000378', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000379', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000380', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000381', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000382', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000383', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000384', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000385', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000386', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000387', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000388', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000389', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000390', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000391', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000392', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000393', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000394', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000395', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000396', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000397', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000398', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000399', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000400', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:25:54', 0, 'SCM01', '20230826000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000401', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:25:53', 0, 'SCM01', '20230826000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000402', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:25:52', 0, 'SCM01', '20230826000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000403', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:27:12', 0, 'SCM01', '20230828000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000404', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:27:10', 0, 'SCM01', '20230828000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000405', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:27:09', 0, 'SCM01', '20230828000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000406', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:27:08', 0, 'SCM01', '20230828000004', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000407', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:27:07', 0, 'SCM01', '20230828000005', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000408', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000409', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000410', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000411', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000412', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000413', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000414', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000415', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000416', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000417', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000418', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000419', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000420', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000421', 'admin', '2023-09-20 02:23:12', 'admin', '2023-09-20 02:27:06', 0, 'SCM01', '20230901000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920000422', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000423', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000424', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000425', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000426', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000427', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000428', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000429', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000430', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000431', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000432', 'admin', '2023-09-20 02:23:12', NULL, NULL, 0, 'SCM01', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000433', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000434', 'admin', '2023-09-20 02:27:26', 'admin', '2023-09-20 02:27:29', 0, 'SCM02', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000435', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000436', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000437', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000438', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000439', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000440', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000441', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000442', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000443', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000444', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000445', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000446', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000447', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000448', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000449', 'admin', '2023-09-20 02:27:26', 'admin', '2023-09-20 02:27:40', 0, 'SCM02', '20230811000005', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000450', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000451', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000452', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000453', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000454', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000455', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000456', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000457', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000458', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000459', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000460', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000461', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000462', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000463', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000464', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000465', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000466', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000467', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000468', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000469', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000470', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000471', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000472', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000473', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000474', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000475', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000476', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000477', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000478', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000479', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000480', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000481', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000482', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000483', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000484', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000485', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000486', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000487', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000488', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000489', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000490', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000491', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000492', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000493', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000494', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000495', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000496', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000497', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000498', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000499', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000500', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000501', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000502', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000503', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000504', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000505', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000506', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000507', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000508', 'admin', '2023-09-20 02:27:26', 'admin', '2023-09-20 02:28:09', 0, 'SCM02', '20230826000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000509', 'admin', '2023-09-20 02:27:26', 'admin', '2023-09-20 02:28:10', 0, 'SCM02', '20230826000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000510', 'admin', '2023-09-20 02:27:26', 'admin', '2023-09-20 02:28:11', 0, 'SCM02', '20230826000003', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000511', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000512', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000513', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000514', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000515', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000516', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000517', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000518', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000519', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000520', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000521', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000522', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000523', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000524', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000525', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000526', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000527', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000528', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000529', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000530', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000531', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000532', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000533', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000534', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000535', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000536', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000537', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000538', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000539', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000540', 'admin', '2023-09-20 02:27:26', NULL, NULL, 0, 'SCM02', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000541', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000542', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:28:24', 0, 'SCM03', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000543', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000544', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000545', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000546', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000547', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000548', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000549', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000550', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000551', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000552', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000553', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000554', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000555', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000556', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000557', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000558', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:28:35', 0, 'SCM03', '20230811000006', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000559', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000560', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000561', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000562', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000563', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000564', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000565', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000566', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000567', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000568', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000569', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000570', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000571', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000572', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000573', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000574', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000575', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000576', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000577', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000578', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000579', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000580', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000581', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000582', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000583', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000584', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000585', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000586', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000587', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000588', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000589', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000590', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000591', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000592', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000593', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000594', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000595', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000596', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000597', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000598', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000599', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000600', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000601', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000602', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000603', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000604', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000605', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000606', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000607', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000608', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000609', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000610', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000611', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000612', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000613', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000614', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000615', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000616', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000617', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000618', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000619', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:29:46', 0, 'SCM03', '20230828000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000620', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:29:51', 0, 'SCM03', '20230828000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000621', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:29:52', 0, 'SCM03', '20230828000003', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000622', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:29:54', 0, 'SCM03', '20230828000004', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000623', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:29:55', 0, 'SCM03', '20230828000005', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000624', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000625', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000626', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000627', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000628', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000629', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000630', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000631', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000632', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000633', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000634', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000635', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000636', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000637', 'admin', '2023-09-20 02:28:20', 'admin', '2023-09-20 02:29:56', 0, 'SCM03', '20230901000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000638', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000639', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000640', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000641', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000642', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000643', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000644', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000645', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000646', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000647', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000648', 'admin', '2023-09-20 02:28:20', NULL, NULL, 0, 'SCM03', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000649', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000650', 'admin', '2023-09-20 02:30:24', 'admin', '2023-09-20 02:30:30', 0, 'SCM04', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000651', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000652', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000653', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000654', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000655', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000656', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000657', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000658', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000659', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000660', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000661', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000662', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000663', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000664', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000665', 'admin', '2023-09-20 02:30:24', 'admin', '2023-09-20 02:31:08', 0, 'SCM04', '20230811000005', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000666', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000667', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000668', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000669', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000670', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000671', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000672', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000673', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000674', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000675', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000676', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000677', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000678', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000679', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000680', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000681', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000682', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000683', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000684', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000685', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000686', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000687', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000688', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000689', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000690', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000691', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000692', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000693', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000694', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000695', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000696', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000697', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000698', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `setting_users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `users_id`, `menus_id`, `v_view`, `v_add`, `v_edit`, `v_delete`, `v_upload`, `v_download`, `v_print`, `v_excel`, `status`) VALUES
('20230920000699', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000700', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000701', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000702', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000703', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000704', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000705', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000706', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000707', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000708', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000709', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000710', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000711', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000712', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000713', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000714', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000715', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000716', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000717', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000718', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000719', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000720', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000721', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000722', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000723', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000724', 'admin', '2023-09-20 02:30:24', 'admin', '2023-09-20 02:31:45', 0, 'SCM04', '20230826000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000725', 'admin', '2023-09-20 02:30:24', 'admin', '2023-09-20 02:31:44', 0, 'SCM04', '20230826000002', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000726', 'admin', '2023-09-20 02:30:24', 'admin', '2023-09-20 02:31:43', 0, 'SCM04', '20230826000003', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000727', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000728', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000729', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000730', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000731', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000732', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000733', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000734', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000735', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000736', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000737', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000738', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000739', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000740', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000741', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000742', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000743', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000744', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000745', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000746', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000747', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000748', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000749', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000750', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000751', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000752', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000753', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000754', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000755', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000756', 'admin', '2023-09-20 02:30:24', NULL, NULL, 0, 'SCM04', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000757', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000758', 'admin', '2023-09-20 02:31:55', 'admin', '2023-09-20 02:32:02', 0, 'HRD01', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000759', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000760', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000761', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000762', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000763', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000764', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000765', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000766', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000767', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000768', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000769', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000770', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000771', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000772', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000773', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000774', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000775', 'admin', '2023-09-20 02:31:55', 'admin', '2023-09-20 02:32:06', 0, 'HRD01', '20230811000007', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000776', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000777', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000778', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000779', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000780', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000781', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000782', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000783', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000784', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000785', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000786', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000787', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000788', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000789', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000790', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000791', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000792', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000793', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000794', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000795', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000796', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000797', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000798', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000799', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000800', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000801', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000802', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000803', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000804', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000805', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000806', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000807', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000808', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000809', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000810', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000811', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000812', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000813', 'admin', '2023-09-20 02:31:55', NULL, NULL, 0, 'HRD01', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000814', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000815', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000816', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000817', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000818', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000819', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000820', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000821', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000822', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000823', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000824', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000825', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000826', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000827', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000828', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000829', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000830', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000831', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000832', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000833', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000834', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000835', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000836', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000837', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000838', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000839', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000840', 'admin', '2023-09-20 02:31:56', 'admin', '2023-09-20 02:32:21', 0, 'HRD01', '20230828000006', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000841', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000842', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000843', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000844', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000845', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000846', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000847', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000848', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000849', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000850', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000851', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000852', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000853', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000854', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000855', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000856', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000857', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000858', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000859', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000860', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000861', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000862', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000863', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000864', 'admin', '2023-09-20 02:31:56', NULL, NULL, 0, 'HRD01', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000865', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000866', 'admin', '2023-09-20 02:32:27', 'admin', '2023-09-20 02:32:31', 0, 'HRD02', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000867', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000868', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000869', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000870', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000871', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000872', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000873', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000874', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000875', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000876', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000877', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000878', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000879', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000880', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000881', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000882', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000883', 'admin', '2023-09-20 02:32:27', 'admin', '2023-09-20 02:32:38', 0, 'HRD02', '20230811000007', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000884', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000885', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000886', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000887', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000888', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000889', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000890', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000891', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000892', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000893', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000894', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000895', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000896', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000897', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000898', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000899', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000900', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000901', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000902', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000903', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000904', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000905', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000906', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000907', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000908', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000909', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000910', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000911', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000912', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000913', 'admin', '2023-09-20 02:32:27', NULL, NULL, 0, 'HRD02', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000914', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000915', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000916', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000917', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000918', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000919', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000920', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000921', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000922', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000923', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000924', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000925', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000926', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000927', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000928', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000929', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000930', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000931', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000932', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000933', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000934', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000935', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000936', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000937', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000938', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000939', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000940', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000941', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000942', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000943', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000944', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000945', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000946', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000947', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000948', 'admin', '2023-09-20 02:32:28', 'admin', '2023-09-20 02:32:53', 0, 'HRD02', '20230828000006', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920000949', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000950', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000951', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000952', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000953', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000954', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000955', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000956', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000957', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000958', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000959', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000960', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000961', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000962', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000963', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000964', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000965', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000966', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000967', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000968', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000969', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000970', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000971', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000972', 'admin', '2023-09-20 02:32:28', NULL, NULL, 0, 'HRD02', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000973', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000974', 'admin', '2023-09-20 02:33:09', 'admin', '2023-09-20 02:33:10', 0, 'MKT01', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000975', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000976', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000977', 'admin', '2023-09-20 02:33:09', 'admin', '2023-09-20 02:33:24', 0, 'MKT01', '20230803000006', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920000978', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000979', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000980', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000981', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000982', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000983', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000984', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000985', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000986', 'admin', '2023-09-20 02:33:09', 'admin', '2023-09-20 02:33:18', 0, 'MKT01', '20230811000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000987', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000988', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000989', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000990', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000991', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000992', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000993', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000994', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000995', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000996', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000997', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000998', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920000999', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001000', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001001', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001002', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001003', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001004', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001005', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001006', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001007', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001008', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001009', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001010', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001011', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001012', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001013', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001014', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001015', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001016', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001017', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001018', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001019', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001020', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001021', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001022', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001023', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001024', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001025', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001026', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001027', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001028', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001029', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001030', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001031', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001032', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001033', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001034', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001035', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001036', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001037', 'admin', '2023-09-20 02:33:09', 'admin', '2023-09-20 02:33:53', 0, 'MKT01', '20230814000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920001038', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001039', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001040', 'admin', '2023-09-20 02:33:09', 'admin', '2023-09-20 02:33:35', 0, 'MKT01', '20230815000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001041', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001042', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001043', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001044', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001045', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001046', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001047', 'admin', '2023-09-20 02:33:09', 'admin', '2023-09-20 02:33:54', 0, 'MKT01', '20230823000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230920001048', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001049', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001050', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001051', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001052', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001053', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001054', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001055', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001056', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001057', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001058', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001059', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001060', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001061', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001062', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001063', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001064', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001065', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001066', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001067', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001068', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001069', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001070', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001071', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001072', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001073', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001074', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001075', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001076', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001077', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001078', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001079', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001080', 'admin', '2023-09-20 02:33:09', NULL, NULL, 0, 'MKT01', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001081', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001082', 'admin', '2023-09-20 02:34:09', 'admin', '2023-09-20 02:34:14', 0, 'MKT02', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001083', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001084', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001085', 'admin', '2023-09-20 02:34:09', 'admin', '2023-09-20 02:34:38', 0, 'MKT02', '20230803000006', 1, 1, 1, 0, 0, 0, 1, 1, 0),
('20230920001086', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001087', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001088', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001089', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001090', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001091', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001092', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001093', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001094', 'admin', '2023-09-20 02:34:09', 'admin', '2023-09-20 02:34:29', 0, 'MKT02', '20230811000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001095', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001096', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001097', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001098', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001099', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001100', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001101', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001102', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001103', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001104', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001105', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001106', 'admin', '2023-09-20 02:34:09', NULL, NULL, 0, 'MKT02', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001107', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001108', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `setting_users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `users_id`, `menus_id`, `v_view`, `v_add`, `v_edit`, `v_delete`, `v_upload`, `v_download`, `v_print`, `v_excel`, `status`) VALUES
('20230920001109', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001110', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001111', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001112', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001113', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001114', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001115', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001116', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001117', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001118', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001119', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001120', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001121', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001122', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001123', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001124', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001125', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001126', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001127', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001128', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001129', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001130', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001131', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001132', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001133', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001134', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001135', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001136', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001137', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001138', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001139', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001140', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001141', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001142', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001143', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001144', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001145', 'admin', '2023-09-20 02:34:10', 'admin', '2023-09-20 02:34:59', 0, 'MKT02', '20230814000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920001146', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001147', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001148', 'admin', '2023-09-20 02:34:10', 'admin', '2023-09-20 02:34:43', 0, 'MKT02', '20230815000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001149', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001150', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001151', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001152', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001153', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001154', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001155', 'admin', '2023-09-20 02:34:10', 'admin', '2023-09-20 02:35:00', 0, 'MKT02', '20230823000001', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920001156', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001157', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001158', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001159', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001160', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001161', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001162', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001163', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001164', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001165', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001166', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001167', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001168', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001169', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001170', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001171', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001172', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001173', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001174', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001175', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001176', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001177', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001178', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001179', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001180', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001181', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001182', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001183', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001184', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001185', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001186', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001187', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001188', 'admin', '2023-09-20 02:34:10', NULL, NULL, 0, 'MKT02', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001189', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001190', 'admin', '2023-09-20 02:35:19', 'admin', '2023-09-20 02:35:28', 0, 'MTN01', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001191', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001192', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001193', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001194', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001195', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001196', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001197', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001198', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001199', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001200', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001201', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001202', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001203', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001204', 'admin', '2023-09-20 02:35:19', 'admin', '2023-09-20 02:35:34', 0, 'MTN01', '20230811000003', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001205', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001206', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001207', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001208', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001209', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001210', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001211', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001212', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001213', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001214', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001215', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001216', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001217', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001218', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001219', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001220', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001221', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001222', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001223', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001224', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001225', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001226', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001227', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001228', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001229', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001230', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001231', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001232', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001233', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001234', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001235', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001236', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001237', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001238', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001239', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001240', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001241', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001242', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001243', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001244', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001245', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001246', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001247', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001248', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001249', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001250', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001251', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001252', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001253', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001254', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001255', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001256', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001257', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001258', 'admin', '2023-09-20 02:35:19', 'admin', '2023-09-20 02:35:47', 0, 'MTN01', '20230815000003', 1, 1, 1, 0, 1, 1, 1, 1, 0),
('20230920001259', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001260', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001261', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001262', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001263', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001264', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001265', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001266', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001267', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001268', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001269', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001270', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001271', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001272', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001273', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001274', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001275', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001276', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001277', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001278', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001279', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001280', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001281', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001282', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001283', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001284', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001285', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001286', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001287', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001288', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001289', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001290', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001291', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001292', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001293', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001294', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001295', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230920001296', 'admin', '2023-09-20 02:35:19', NULL, NULL, 0, 'MTN01', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000001', 'admin', '2023-10-01 08:51:41', 'admin', '2023-10-01 08:51:46', 0, 'admin', '20231001000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000002', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000003', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230803000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000004', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230803000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000005', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230803000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000006', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230803000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000007', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230803000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000008', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230807000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000009', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230808000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000010', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230808000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000011', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230808000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000012', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230808000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000013', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230808000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000014', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230810000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000015', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000016', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000017', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000018', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000019', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000020', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000021', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000022', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000023', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000010', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000024', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000025', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000026', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000027', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000028', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000029', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000016', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000030', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000017', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000031', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000018', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000032', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000019', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000033', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000020', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000034', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000021', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000035', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000022', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000036', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000024', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000037', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000025', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000038', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000026', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000039', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000027', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000040', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000028', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000041', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000029', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000042', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000030', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000043', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000031', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000044', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000032', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000045', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000033', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000046', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000034', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000047', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000035', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000048', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000036', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000049', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000037', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000050', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000038', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000051', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000039', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000052', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000040', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000053', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000041', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000054', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000042', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000055', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000043', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000056', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000044', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000057', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000045', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000058', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000046', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000059', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000060', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000048', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000061', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000049', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000062', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000050', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000063', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000051', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000064', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000052', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000065', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230811000053', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000066', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230814000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000067', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230814000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000068', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230814000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000069', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230815000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000070', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230815000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000071', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230815000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000072', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230817000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000073', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230817000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000074', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230817000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000075', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230818000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000076', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230823000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000077', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230826000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000078', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230826000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000079', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230826000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000080', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230828000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000081', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230828000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000082', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230828000003', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000083', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230828000004', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000084', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230828000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000085', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230828000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000086', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000087', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000088', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000005', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000089', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000006', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000090', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000007', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000091', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000008', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000092', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000009', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000093', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000011', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000094', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000012', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000095', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000013', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000096', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000014', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000097', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230830000015', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000098', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230901000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000099', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230910000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000100', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230918000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000101', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20230918000002', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000102', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '20231001000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000103', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '44964312f0264429978158ada88843', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000104', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000105', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000106', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', 'c8f8362a5f6c432ab27d37213f15d4', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000107', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', 'cf98f97766f6405590b26daa586e00', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000108', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000109', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', 'de3f6855009e49deb7fd2fdd0f3b3d', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231001000110', 'admin', '2023-10-01 14:17:06', NULL, NULL, 0, 'IMPLEMENTATOR', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20231016000001', 'admin', '2023-10-16 06:40:42', 'admin', '2023-10-16 06:40:51', 0, 'admin', '20231016000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20231019000001', 'admin', '2023-10-19 07:36:12', 'admin', '2023-10-19 07:36:20', 0, 'admin', '20231019000001', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20231019000002', 'admin', '2023-10-19 07:48:07', 'admin', '2023-10-19 07:48:31', 0, 'admin', '20231019000002', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20231019000003', 'admin', '2023-10-19 07:48:07', 'admin', '2023-10-19 07:48:21', 0, 'admin', '20231019000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20231019000004', 'admin', '2023-10-19 07:48:07', 'admin', '2023-10-19 07:48:22', 0, 'admin', '20231019000004', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20231019000005', 'admin', '2023-10-19 07:48:07', 'admin', '2023-10-19 07:48:29', 0, 'admin', '20231019000005', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20231019000006', 'admin', '2023-10-19 07:48:07', 'admin', '2023-10-19 07:48:16', 0, 'admin', '20231019000006', 1, 0, 1, 0, 0, 0, 1, 1, 0),
('2c192616c6374feebbbc2778dd4443', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:46', 0, 'admin', '44964312f0264429978158ada88843', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('56c39e081a4d4d4c8db20e988f14cc', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:52', 0, 'admin', 'de3f6855009e49deb7fd2fdd0f3b3d', 1, 0, 0, 1, 0, 0, 1, 1, 0),
('62aa172fcf7c443aba135013fbcc54', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:26', 0, 'admin', 'cf98f97766f6405590b26daa586e00', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('67bf89a8259942d799ead773394497', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:31', 0, 'admin', 'c8f8362a5f6c432ab27d37213f15d4', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('746d6989a790471ca2e4de24a9f871', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:48', 0, 'admin', 'b679033b3256414b8f916c69f17674', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('9b9c36848ff4460eb505761f37efaf', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:51', 0, 'admin', 'd13439e3f2324450a69b4e0e50159a', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('af473d04fefb479e967d32fd497e2e', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:53', 0, 'admin', 'e3c31e10b6c64e119b068ae4b73be6', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('c93d58aadd4b46ce94ac9e8af4f42c', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:30', 0, 'admin', '6ccd20c54d1d415189120ec5cc6c81', 1, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `shipping_orders`
--

CREATE TABLE `shipping_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `delivery_order_no` varchar(30) NOT NULL,
  `sales_order_no` varchar(30) DEFAULT NULL,
  `customer_order_no` varchar(30) DEFAULT NULL,
  `checksheet_label` varchar(30) DEFAULT NULL,
  `delivery` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_fg`
--

CREATE TABLE `stock_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT 0,
  `document_no` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_so`
--

CREATE TABLE `stock_so` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) DEFAULT NULL,
  `p_year` varchar(4) DEFAULT NULL,
  `revision` tinyint(1) NOT NULL DEFAULT 0,
  `document_no` varchar(30) DEFAULT NULL,
  `qty` decimal(20,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `stock_so`
--

INSERT INTO `stock_so` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `customer_id`, `item_fg_id`, `p_month`, `p_year`, `revision`, `document_no`, `qty`) VALUES
('20230921000001', 'admin', '2023-09-21 15:04:26', NULL, NULL, 0, 'C002', 'BPIFG-IP09230003', '09', '2023', 0, 'SO230901', '1000.00'),
('20230921000002', 'admin', '2023-09-21 15:07:26', NULL, NULL, 0, 'C002', 'BPIFG-IP09230004', '09', '2023', 0, 'SO230903', '1001.00'),
('20230921000003', 'admin', '2023-09-21 15:07:26', NULL, NULL, 0, 'C002', 'BPIFG-IP09230005', '09', '2023', 0, 'SO230904', '1002.00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `stock_wip`
--

CREATE TABLE `stock_wip` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_fg_id` varchar(30) DEFAULT NULL,
  `p_month` varchar(2) NOT NULL,
  `p_year` varchar(4) NOT NULL,
  `revision` tinyint(4) NOT NULL DEFAULT 0,
  `document_no` varchar(30) DEFAULT NULL,
  `pp` decimal(20,2) NOT NULL DEFAULT 0.00,
  `p1` decimal(20,2) NOT NULL DEFAULT 0.00,
  `p2` decimal(20,2) NOT NULL,
  `p3` decimal(20,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `subconts`
--

CREATE TABLE `subconts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `subcont_type_id` varchar(30) DEFAULT NULL,
  `delivery_area_id` varchar(30) DEFAULT NULL,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_person` varchar(30) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(30) DEFAULT NULL,
  `status` int(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `subconts`
--

INSERT INTO `subconts` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `subcont_type_id`, `delivery_area_id`, `number`, `name`, `address`, `contact_person`, `telp`, `fax`, `email`, `website`, `status`) VALUES
('S001', 'admin', '2023-08-28 20:37:13', NULL, NULL, 0, 'TS001', 'A001', 'TEST', 'TEST', 'TEST', 'TEST', '141411', '', '', '', 0),
('S002', 'admin', '2023-09-04 11:27:54', NULL, NULL, 0, 'TS001', 'A001', 'S002', 'SUBCONT PT.BPI1', '', '', '', '', '', '', 0),
('S003', 'admin', '2023-09-22 13:47:04', NULL, NULL, 0, 'TS002', 'A002', 'DJPI', 'PT. DANO JAYA PLAST INDONESIA', 'JL. Scientia Timur 3 B5B No. 5, Jababeka Phase 5 Jayamukti - Cikarang Pusat', 'Mr Indra ', '081317969694', '', '', '', 0),
('S004', 'admin', '2023-09-22 13:50:33', NULL, NULL, 0, 'TS002', 'A002', 'CGS', 'PT. CGS PLASTIK INDONESIA', 'JL. KAWASAN INDUSTRI JABABEKA IIH BLOK CC NO. 15-16 CIKARANG - BEKASI', 'Mr. Erik', '(021) 89953363', '(021) 89834491', '', '', 0),
('S005', 'admin', '2023-09-22 13:50:34', NULL, NULL, 0, 'TS002', 'A002', 'HTP', 'PT. HYUN TECH PERKASA', 'JL. JABABEKA VI B, BLOK J NO 7 F KAWASAN INDUSTRI JABABEKA CIKARANG, BEKASI, JAWA BARAT 17530', 'Mr Dadan Padriawan', '081280853680 / 08212', '', 'dfadriawan@yahoo.co.id', '', 0),
('S006', 'admin', '2023-09-22 13:50:34', NULL, NULL, 0, 'TS001', 'A002', 'CPP', 'PT. CAHAYA PRIMA PLASTINDO', 'JL. NAMBO POJOK DESA SUKA SEJATI KAB BEKASI 17530', 'Mr. Slamet', '081289750252', '', '', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `subcont_types`
--

CREATE TABLE `subcont_types` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `subcont_types`
--

INSERT INTO `subcont_types` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `name`, `description`) VALUES
('TS001', 'admin', '2023-08-28 11:01:37', NULL, NULL, 0, 'INJECT PROCESS', ''),
('TS002', 'admin', '2023-08-28 11:01:44', NULL, NULL, 0, 'INJECT PROCESS INCLUDE MATERIA', '');

-- --------------------------------------------------------

--
-- Struktur dari tabel `suppliers`
--

CREATE TABLE `suppliers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_person` varchar(30) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `fax` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `website` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `payment_term` int(11) DEFAULT 0,
  `incoterm` varchar(20) DEFAULT NULL,
  `bank_account` varchar(20) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `suppliers`
--

INSERT INTO `suppliers` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `type`, `address`, `contact_person`, `telp`, `fax`, `email`, `website`, `currency`, `payment_term`, `incoterm`, `bank_account`, `bank_name`, `status`) VALUES
('SP-0823001', 'admin', '2023-08-28 07:53:12', NULL, NULL, 0, 'TS', 'TEST SUPPLIER', 'LOCAL', '', 'TEST PEOPLE', '', '', '', '', 'IDR', 0, '', '', '', 0),
('SP-0823002', 'admin', '2023-08-28 09:19:40', NULL, NULL, 0, 'AIT', 'PT. ALPHA INTEGRATED', 'LOCAL', 'Jl. Industri Selatan Blok MM No.12, Kawasan Industri Jababeka Tahap II, Cikarang,  Bekasi 17550 Indonesia.', 'Bpk Eko S', '021-8983 3028', '021-8983 2086', '', '', 'IDR', 30, '', '', '', 0),
('SP-0823003', 'admin', '2023-08-28 09:23:04', NULL, NULL, 0, 'ATP', 'PT. ARTOMORO PRECISION', 'LOCAL', 'Jababeka Industrial Estate I Jl. Jababeka XVII D Blok U31E Cikarang - Bekasi 17530 Indonesia', 'Ibu Anita / Bpk Heri / Bpk. Al', ' 021-89840 334-36', ' 021-89840 334-36', '', '', 'IDR', 30, '', '', '', 0),
('SP-0823004', 'admin', '2023-08-28 09:23:04', NULL, NULL, 0, 'AIN', 'PT. ASKARA INTERNAL', 'LOCAL', 'Jl KH Noer Ali, Komp. Duta Permai Block F No1 Jaka Sampurna ,Bekasi Barat 17145 Jawa Barat', 'Ibu Olhga, Bpk Ali', '021-88963777', '021-88963777', '', '', 'IDR', 30, '', '', '', 0),
('SP-0923001', 'admin', '2023-09-22 14:35:35', NULL, NULL, 0, 'ALP', 'PT. ALPHA INTEGRATED', 'LOCAL', 'Jl. Industri Selatan Blok MM No.12, Kawasan Industri Jababeka Tahap II, Cikarang,  Bekasi 17550 Indonesia.', 'Bpk Eko S', '021-8983 3028', '021-8983 2086', '', '', 'IDR', 0, 'NONE', '', '', 0),
('SP-0923002', 'admin', '2023-09-22 14:35:35', NULL, NULL, 0, 'ARP', 'PT. ARTOMORO PRECISION', 'LOCAL', 'Jababeka Industrial Estate I Jl. Jababeka XVII D Blok U31E \'Cikarang - Bekasi 17530 Indonesia', 'Ibu Anita / Bpk Heri / Bpk. Al', ' 021-89840 334-36', '021-89840 337', '', '', 'IDR', 0, 'NONE', '', '', 0),
('SP-0923004', 'admin', '2023-09-22 14:35:36', NULL, NULL, 0, 'AJI', 'PT. ASTRA JUOKU INDONESIA', 'LOCAL', 'Kawasan Industri Mitra Karawang Blok D, No. 6 Parang Mulya, Ciampel, Karawang', 'Ibu Maya Lestari', '0267- 8638064 ext 23', '', '', '', 'IDR', 0, 'NONE', '', '', 0),
('SP-0923005', 'admin', '2023-09-22 14:35:36', NULL, NULL, 0, 'BMI', 'PT. BANSHU METAL INDONESIA', 'LOCAL', 'JL. Jababeka XIV, Blok J/12-B, Kawasan Industri Jababeka, 17520, Harja Mekar, Kec. Cibitung, Kabupaten Bekasi, Jawa Barat 17532', 'Bpk Maksudin/ Cc Bpk Yusman N', '021-8934895', '', '', '', 'IDR', 0, 'NONE', '', '', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier_items`
--

CREATE TABLE `supplier_items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `maker` varchar(30) DEFAULT NULL,
  `item_supplier` varchar(30) DEFAULT NULL,
  `mpq` int(11) DEFAULT 0,
  `moq` int(11) DEFAULT 0,
  `share_order` int(11) DEFAULT 0,
  `leadtime` int(11) DEFAULT 0,
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,2) DEFAULT 0.00,
  `valid_date` date DEFAULT NULL,
  `safety_stock` int(11) DEFAULT 0,
  `calculate` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supplier_item_histories`
--

CREATE TABLE `supplier_item_histories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_rm_id` varchar(30) DEFAULT NULL,
  `maker` varchar(30) DEFAULT NULL,
  `item_supplier` varchar(30) DEFAULT NULL,
  `mpq` int(11) DEFAULT 0,
  `moq` int(11) DEFAULT 0,
  `share_order` int(11) DEFAULT 0,
  `leadtime` int(11) DEFAULT 0,
  `currency` varchar(10) DEFAULT NULL,
  `price` decimal(20,2) DEFAULT 0.00,
  `valid_date` date DEFAULT NULL,
  `safety_stock` int(11) DEFAULT 0,
  `calculate` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `uom`
--

CREATE TABLE `uom` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `uom`
--

INSERT INTO `uom` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `status`) VALUES
('U01', 'admin', '2023-08-07 20:23:20', NULL, NULL, 0, '', 'KG', 'KILO GRAM', 0),
('U02', 'admin', '2023-08-07 20:23:32', NULL, NULL, 0, '', 'MTR', 'METER', 0),
('U03', 'admin', '2023-08-07 20:23:42', NULL, NULL, 0, '', 'PAIL', 'PAIL', 0),
('U04', 'admin', '2023-08-07 20:23:57', NULL, NULL, 0, '', 'PCS', 'PIECES', 0),
('U05', 'admin', '2023-08-07 20:24:10', NULL, NULL, 0, '', 'MM', 'MILI METER', 0),
('U06', 'admin', '2023-08-07 20:24:20', NULL, NULL, 0, '', 'GR', 'GRAM', 0),
('U07', 'admin', '2023-08-07 20:24:28', NULL, NULL, 0, '', 'SET', 'SET', 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `approved` tinyint(1) NOT NULL DEFAULT 0,
  `approved_to` varchar(30) DEFAULT NULL,
  `approved_by` varchar(30) DEFAULT NULL,
  `approved_date` timestamp NULL DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `number` varchar(30) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `avatar` text DEFAULT NULL,
  `theme` varchar(30) NOT NULL DEFAULT 'default',
  `actived` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `approved`, `approved_to`, `approved_by`, `approved_date`, `deleted`, `number`, `name`, `description`, `username`, `password`, `email`, `phone`, `position`, `avatar`, `theme`, `actived`, `status`) VALUES
('20230919000001', 'admin', '2023-09-19 10:40:12', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'TCH01', 'Fakhrudin', NULL, 'TCH01', 'TCH01@123#', 'bpi@erp.com', '', 'TECHNICAL', NULL, 'default', 0, 0),
('20230920000001', 'admin', '2023-09-20 09:03:30', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'TCH02', 'Vimal Raj', NULL, 'TCH02', 'TCH02@123#', 'bpi1@erp.com', '', 'TECHNICAL', NULL, 'default', 0, 0),
('20230920000002', 'admin', '2023-09-20 09:05:58', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'TCH03', 'Sri Nina Oktavia', NULL, 'TCH03', 'TCH03@123#', 'bpi3@erp.com', '', 'TECHNICAL', NULL, 'default', 0, 0),
('20230920000003', 'admin', '2023-09-20 09:06:53', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'SCM01', 'Dicky Zulfikar', NULL, 'SCM01', 'SCM01@23#', 'bpi4@erp.com', '', 'SCM', NULL, 'default', 0, 0),
('20230920000004', 'admin', '2023-09-20 09:07:25', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'SCM02', 'Arik Rosdiana', NULL, 'SCM02', 'SCM02@123#', 'bpi5@erp.com', '', 'SCM', NULL, 'default', 0, 0),
('20230920000005', 'admin', '2023-09-20 09:07:49', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'SCM03', 'Diah Mayasari', NULL, 'SCM03', 'SCM03@123#', 'bpi6@erp.com', '', 'SCM', NULL, 'default', 0, 0),
('20230920000006', 'admin', '2023-09-20 09:08:19', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'SCM04', 'Tyas Nurhayati', NULL, 'SCM04', 'SCM04@123#', 'bpi7@erp.com', '', 'SCM', NULL, 'default', 0, 0),
('20230920000007', 'admin', '2023-09-20 09:08:45', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'HRD01', 'Ria Yunita', NULL, 'HRD01', 'HRD01@123#', 'bpi8@erp.com', '', 'HRD & GA', NULL, 'default', 0, 0),
('20230920000008', 'admin', '2023-09-20 09:09:08', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'HRD02', 'Teguh Rahayu', NULL, 'HRD02', 'HRD02@123#', 'bpi9@erp.com', '', 'HRD & GA', NULL, 'default', 0, 0),
('20230920000009', 'admin', '2023-09-20 09:09:32', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'MKT01', 'Maksudin', NULL, 'MKT01', 'MKT01@123#', 'bpi10@erp.com', '', 'MKT', NULL, 'default', 0, 0),
('20230920000010', 'admin', '2023-09-20 09:09:58', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'MKT02', 'Fanny Nurhanif Fajriany', NULL, 'MKT02', 'MKT02@123#', 'bpi11@erp.com', '', 'MKT', NULL, 'default', 0, 0),
('20230920000011', 'admin', '2023-09-20 09:10:28', NULL, NULL, 2, '', 'admin', '2023-10-03 10:11:00', 0, 'MTN01', 'Sumarto', NULL, 'MTN01', 'MTN01@123#', 'bpi12@erp.com', '', 'MTN', NULL, 'default', 0, 0),
('20230920000012', 'admin', '2023-09-20 09:11:18', 'admin', '2023-09-20 09:11:33', 2, '', 'admin', '2023-10-03 10:11:00', 0, 'ASTEK02', 'Implementator', NULL, 'IMPLEMENTATOR', 'ASTEK02@123#', 'ASTEK@ASTEK.COM', '', 'ASTEK', NULL, 'default', 0, 0),
('86f9f296025243ed953fe6014ff765', 'admin', '2021-12-26 11:24:58', 'admin', '2023-10-01 15:50:30', 2, '', 'admin', '2023-10-03 10:11:00', 0, '1', 'Administrator', '', 'admin', 'Login@190320', 'admin@aeconsys.com', '88888888888', 'Admin System', NULL, 'metro-blue', 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `vehicles`
--

CREATE TABLE `vehicles` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `name` varchar(30) NOT NULL,
  `police_no` varchar(10) DEFAULT NULL,
  `dimension_p` int(11) NOT NULL DEFAULT 0,
  `dimension_l` int(11) NOT NULL DEFAULT 0,
  `dimension_t` int(11) NOT NULL DEFAULT 0,
  `volume` int(11) DEFAULT 0,
  `remark` text NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `vehicles`
--

INSERT INTO `vehicles` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `name`, `police_no`, `dimension_p`, `dimension_l`, `dimension_t`, `volume`, `remark`, `status`) VALUES
('V001', 'admin', '2023-08-28 20:02:02', NULL, NULL, 0, 'Double Long', 'B 9446 UCW', 520, 185, 185, 17797000, '', 0),
('V002', 'admin', '2023-08-28 20:02:25', NULL, NULL, 0, 'Double Long', 'B 9110 UXA', 555, 190, 165, 17399250, '', 0),
('V003', 'admin', '2023-08-28 20:02:44', NULL, NULL, 0, 'Double Long', 'B 9973 UXA', 555, 190, 185, 19508250, '', 0),
('V004', 'admin', '2023-09-21 09:12:31', 'admin', '2023-09-21 09:13:08', 0, 'Engkle Long', 'B 9099 FCJ', 385, 165, 165, 10481625, '', 0),
('V005', 'admin', '2023-09-21 09:15:21', 'admin', '2023-09-30 23:09:15', 0, 'DOUBLE ENGKLE', 'X8', 10, 10, 10, 0, 'Service', 1),
('V006', 'admin', '2023-09-21 09:15:21', NULL, NULL, 0, 'LONG', 'X2', 11, 12, 13, 0, '', 0),
('V007', 'HRD02', '2023-09-21 09:27:28', NULL, NULL, 0, 'X11', 'B55555', 111, 111, 111, 1367631, '', 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `abc_class`
--
ALTER TABLE `abc_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `calendars`
--
ALTER TABLE `calendars`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `from_users_id` (`from_users_id`),
  ADD KEY `to_users_id` (`to_users_id`);

--
-- Indeks untuk tabel `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `convertions`
--
ALTER TABLE `convertions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `customer_address`
--
ALTER TABLE `customer_address`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_address_ibfk_3` (`customer_id`);

--
-- Indeks untuk tabel `customer_items`
--
ALTER TABLE `customer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `customer_item_histories`
--
ALTER TABLE `customer_item_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `delivery_areas`
--
ALTER TABLE `delivery_areas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `sales_order_no` (`sales_order_no`),
  ADD KEY `delivery_order_no` (`delivery_order_no`),
  ADD KEY `delivery_note_no` (`delivery_note_no`);

--
-- Indeks untuk tabel `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `sales_order_no` (`sales_order_no`),
  ADD KEY `delivery_order_no` (`delivery_order_no`);

--
-- Indeks untuk tabel `divisions`
--
ALTER TABLE `divisions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `forecasts`
--
ALTER TABLE `forecasts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `forecast_histories`
--
ALTER TABLE `forecast_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `generate_mps`
--
ALTER TABLE `generate_mps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `generate_mps_details`
--
ALTER TABLE `generate_mps_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `item_boxs`
--
ALTER TABLE `item_boxs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_kind_id` (`item_kind_id`);

--
-- Indeks untuk tabel `item_categories`
--
ALTER TABLE `item_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_colors`
--
ALTER TABLE `item_colors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_familys`
--
ALTER TABLE `item_familys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_category_number` (`item_category_id`);

--
-- Indeks untuk tabel `item_family_subs`
--
ALTER TABLE `item_family_subs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_category_id` (`item_category_id`),
  ADD KEY `item_family_id` (`item_family_id`);

--
-- Indeks untuk tabel `item_fg`
--
ALTER TABLE `item_fg`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_family_id` (`division_id`);

--
-- Indeks untuk tabel `item_kinds`
--
ALTER TABLE `item_kinds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_mold`
--
ALTER TABLE `item_mold`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeks untuk tabel `item_process`
--
ALTER TABLE `item_process`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_process_flow`
--
ALTER TABLE `item_process_flow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_rm`
--
ALTER TABLE `item_rm`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_category_id` (`item_category_id`),
  ADD KEY `item_family_id` (`item_family_id`),
  ADD KEY `item_sub_family_id` (`item_sub_family_id`);

--
-- Indeks untuk tabel `logins`
--
ALTER TABLE `logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indeks untuk tabel `machines`
--
ALTER TABLE `machines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `menu_loadings`
--
ALTER TABLE `menu_loadings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `machine_id` (`machine_id`),
  ADD KEY `item_mold_id` (`item_mold_id`);

--
-- Indeks untuk tabel `menu_loading_subconts`
--
ALTER TABLE `menu_loading_subconts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `subcont_id` (`subcont_id`),
  ADD KEY `machine_id` (`machine_id`);

--
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `approvals_id` (`approvals_id`);

--
-- Indeks untuk tabel `os_mpp`
--
ALTER TABLE `os_mpp`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeks untuk tabel `os_so`
--
ALTER TABLE `os_so`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeks untuk tabel `purgings`
--
ALTER TABLE `purgings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `machine_id` (`machine_id`);

--
-- Indeks untuk tabel `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `sales_orders_ibfk_3` (`customer_id`),
  ADD KEY `sales_orders_ibfk_4` (`item_fg_id`),
  ADD KEY `sales_order_no` (`sales_order_no`);

--
-- Indeks untuk tabel `sales_order_deliveries`
--
ALTER TABLE `sales_order_deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `trans_date` (`trans_date`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `sales_order_no` (`sales_order_no`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `menus_id` (`menus_id`);

--
-- Indeks untuk tabel `setting_stocks`
--
ALTER TABLE `setting_stocks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_category_id` (`item_category_id`);

--
-- Indeks untuk tabel `setting_subconts`
--
ALTER TABLE `setting_subconts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `subcont_id` (`subcont_id`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `setting_users`
--
ALTER TABLE `setting_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `users_id` (`users_id`),
  ADD KEY `menus_id` (`menus_id`);

--
-- Indeks untuk tabel `shipping_orders`
--
ALTER TABLE `shipping_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_order_no` (`delivery_order_no`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `stock_fg`
--
ALTER TABLE `stock_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `stock_so`
--
ALTER TABLE `stock_so`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indeks untuk tabel `stock_wip`
--
ALTER TABLE `stock_wip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_fg_id` (`item_fg_id`);

--
-- Indeks untuk tabel `subconts`
--
ALTER TABLE `subconts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `subcont_type_id` (`subcont_type_id`),
  ADD KEY `delivery_area_id` (`delivery_area_id`);

--
-- Indeks untuk tabel `subcont_types`
--
ALTER TABLE `subcont_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `supplier_item_histories`
--
ALTER TABLE `supplier_item_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_rm_id` (`item_rm_id`);

--
-- Indeks untuk tabel `uom`
--
ALTER TABLE `uom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `logins`
--
ALTER TABLE `logins`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT untuk tabel `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=327;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `abc_class`
--
ALTER TABLE `abc_class`
  ADD CONSTRAINT `abc_class_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `abc_class_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `bom`
--
ALTER TABLE `bom`
  ADD CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `calendars`
--
ALTER TABLE `calendars`
  ADD CONSTRAINT `calendars_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `calendars_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `chats`
--
ALTER TABLE `chats`
  ADD CONSTRAINT `chats_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chats_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chats_ibfk_3` FOREIGN KEY (`from_users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `chats_ibfk_4` FOREIGN KEY (`to_users_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `config`
--
ALTER TABLE `config`
  ADD CONSTRAINT `config_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `config_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `convertions`
--
ALTER TABLE `convertions`
  ADD CONSTRAINT `convertions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `convertions_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `convertions_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`);

--
-- Ketidakleluasaan untuk tabel `currencies`
--
ALTER TABLE `currencies`
  ADD CONSTRAINT `currencies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `currencies_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `customer_address`
--
ALTER TABLE `customer_address`
  ADD CONSTRAINT `customer_address_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_address_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_address_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `customer_items`
--
ALTER TABLE `customer_items`
  ADD CONSTRAINT `customer_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_items_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `customer_items_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `customer_item_histories`
--
ALTER TABLE `customer_item_histories`
  ADD CONSTRAINT `customer_item_histories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_item_histories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_item_histories_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `customer_item_histories_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `delivery_areas`
--
ALTER TABLE `delivery_areas`
  ADD CONSTRAINT `delivery_areas_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_areas_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD CONSTRAINT `delivery_notes_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_notes_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `delivery_notes_ibfk_5` FOREIGN KEY (`sales_order_no`) REFERENCES `sales_orders` (`sales_order_no`),
  ADD CONSTRAINT `delivery_notes_ibfk_6` FOREIGN KEY (`delivery_order_no`) REFERENCES `delivery_orders` (`delivery_order_no`),
  ADD CONSTRAINT `delivery_notess_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_notess_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD CONSTRAINT `delivery_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_orders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_orders_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `delivery_orders_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `delivery_orders_ibfk_5` FOREIGN KEY (`sales_order_no`) REFERENCES `sales_orders` (`sales_order_no`);

--
-- Ketidakleluasaan untuk tabel `divisions`
--
ALTER TABLE `divisions`
  ADD CONSTRAINT `divisions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `divisions_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `forecasts`
--
ALTER TABLE `forecasts`
  ADD CONSTRAINT `forecasts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecasts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecasts_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `forecasts_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `forecast_histories`
--
ALTER TABLE `forecast_histories`
  ADD CONSTRAINT `forecast_histories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecast_histories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `forecast_histories_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `forecast_histories_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `generate_mps`
--
ALTER TABLE `generate_mps`
  ADD CONSTRAINT `generate_mps_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `generate_mps_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `generate_mps_details`
--
ALTER TABLE `generate_mps_details`
  ADD CONSTRAINT `generate_mps_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `generate_mps_details_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `generate_mps_details_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_boxs`
--
ALTER TABLE `item_boxs`
  ADD CONSTRAINT `item_boxs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_boxs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_boxs_ibfk_3` FOREIGN KEY (`item_kind_id`) REFERENCES `item_kinds` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_categories`
--
ALTER TABLE `item_categories`
  ADD CONSTRAINT `item_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_categories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_colors`
--
ALTER TABLE `item_colors`
  ADD CONSTRAINT `item_colors_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_colors_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_familys`
--
ALTER TABLE `item_familys`
  ADD CONSTRAINT `item_familys_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_familys_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_familys_ibfk_3` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_family_subs`
--
ALTER TABLE `item_family_subs`
  ADD CONSTRAINT `item_family_subs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_family_subs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_family_subs_ibfk_4` FOREIGN KEY (`item_family_id`) REFERENCES `item_familys` (`id`),
  ADD CONSTRAINT `item_family_subs_ibfk_5` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_fg`
--
ALTER TABLE `item_fg`
  ADD CONSTRAINT `item_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_fg_ibfk_3` FOREIGN KEY (`division_id`) REFERENCES `divisions` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_kinds`
--
ALTER TABLE `item_kinds`
  ADD CONSTRAINT `item_kinds_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_kinds_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_mold`
--
ALTER TABLE `item_mold`
  ADD CONSTRAINT `item_mold_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_mold_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_mold_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `item_mold_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `item_process`
--
ALTER TABLE `item_process`
  ADD CONSTRAINT `item_process_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_process_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_process_flow`
--
ALTER TABLE `item_process_flow`
  ADD CONSTRAINT `item_process_flow_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_process_flow_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_rm`
--
ALTER TABLE `item_rm`
  ADD CONSTRAINT `item_rm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_rm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_rm_ibfk_3` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`),
  ADD CONSTRAINT `item_rm_ibfk_4` FOREIGN KEY (`item_family_id`) REFERENCES `item_familys` (`id`);

--
-- Ketidakleluasaan untuk tabel `logins`
--
ALTER TABLE `logins`
  ADD CONSTRAINT `logins_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `machines`
--
ALTER TABLE `machines`
  ADD CONSTRAINT `machines_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `machines_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `menu_loadings`
--
ALTER TABLE `menu_loadings`
  ADD CONSTRAINT `menu_loadings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_loadings_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_loadings_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `menu_loadings_ibfk_4` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`),
  ADD CONSTRAINT `menu_loadings_ibfk_5` FOREIGN KEY (`item_mold_id`) REFERENCES `item_mold` (`id`);

--
-- Ketidakleluasaan untuk tabel `menu_loading_subconts`
--
ALTER TABLE `menu_loading_subconts`
  ADD CONSTRAINT `menu_loading_subconts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_loading_subconts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menu_loading_subconts_ibfk_3` FOREIGN KEY (`subcont_id`) REFERENCES `subconts` (`id`),
  ADD CONSTRAINT `menu_loading_subconts_ibfk_4` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`);

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`approvals_id`) REFERENCES `approvals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `os_mpp`
--
ALTER TABLE `os_mpp`
  ADD CONSTRAINT `os_mpp_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_mpp_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_mpp_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `os_mpp_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `os_so`
--
ALTER TABLE `os_so`
  ADD CONSTRAINT `os_so_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_so_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `os_so_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `purgings`
--
ALTER TABLE `purgings`
  ADD CONSTRAINT `purgings_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purgings_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purgings_ibfk_3` FOREIGN KEY (`machine_id`) REFERENCES `machines` (`id`);

--
-- Ketidakleluasaan untuk tabel `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_orders_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `sales_order_deliveries`
--
ALTER TABLE `sales_order_deliveries`
  ADD CONSTRAINT `sales_order_deliveries_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_order_deliveries_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_order_deliveries_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_order_deliveries_ibfk_4` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `sales_order_deliveries_ibfk_5` FOREIGN KEY (`sales_order_no`) REFERENCES `sales_orders` (`sales_order_no`);

--
-- Ketidakleluasaan untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD CONSTRAINT `setting_menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_3` FOREIGN KEY (`menus_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `setting_stocks`
--
ALTER TABLE `setting_stocks`
  ADD CONSTRAINT `setting_stocks_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_stocks_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_stocks_ibfk_3` FOREIGN KEY (`item_category_id`) REFERENCES `item_categories` (`id`);

--
-- Ketidakleluasaan untuk tabel `setting_subconts`
--
ALTER TABLE `setting_subconts`
  ADD CONSTRAINT `setting_subconts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_subconts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_subconts_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `setting_subconts_ibfk_4` FOREIGN KEY (`subcont_id`) REFERENCES `subconts` (`id`);

--
-- Ketidakleluasaan untuk tabel `setting_users`
--
ALTER TABLE `setting_users`
  ADD CONSTRAINT `setting_users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_3` FOREIGN KEY (`menus_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_users_ibfk_4` FOREIGN KEY (`users_id`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `shipping_orders`
--
ALTER TABLE `shipping_orders`
  ADD CONSTRAINT `shipping_orders_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shipping_orders_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `shipping_orders_fg_ibfk_3` FOREIGN KEY (`delivery_order_no`) REFERENCES `delivery_orders` (`delivery_order_no`);

--
-- Ketidakleluasaan untuk tabel `stock_fg`
--
ALTER TABLE `stock_fg`
  ADD CONSTRAINT `stock_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_fg_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_so`
--
ALTER TABLE `stock_so`
  ADD CONSTRAINT `stock_so_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_so_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_so_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`),
  ADD CONSTRAINT `stock_so_ibfk_4` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `stock_wip`
--
ALTER TABLE `stock_wip`
  ADD CONSTRAINT `stock_wip_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_wip_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `stock_wip_ibfk_3` FOREIGN KEY (`item_fg_id`) REFERENCES `item_fg` (`id`);

--
-- Ketidakleluasaan untuk tabel `subconts`
--
ALTER TABLE `subconts`
  ADD CONSTRAINT `subconts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subconts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subconts_ibfk_3` FOREIGN KEY (`subcont_type_id`) REFERENCES `subcont_types` (`id`),
  ADD CONSTRAINT `subconts_ibfk_4` FOREIGN KEY (`delivery_area_id`) REFERENCES `delivery_areas` (`id`);

--
-- Ketidakleluasaan untuk tabel `subcont_types`
--
ALTER TABLE `subcont_types`
  ADD CONSTRAINT `subcont_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `subcont_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `suppliers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD CONSTRAINT `supplier_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_items_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `supplier_items_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `supplier_item_histories`
--
ALTER TABLE `supplier_item_histories`
  ADD CONSTRAINT `supplier_item_histories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_histories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supplier_item_histories_ibfk_3` FOREIGN KEY (`item_rm_id`) REFERENCES `item_rm` (`id`),
  ADD CONSTRAINT `supplier_item_histories_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `uom`
--
ALTER TABLE `uom`
  ADD CONSTRAINT `uom_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `uom_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `vehicles_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
