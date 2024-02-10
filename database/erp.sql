-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 19 Jul 2023 pada 06.56
-- Versi server: 10.4.27-MariaDB
-- Versi PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `erp_bri`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_balance_customers`
--

CREATE TABLE `account_balance_customers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `currency_local` varchar(5) DEFAULT NULL,
  `balance` decimal(20,4) DEFAULT 0.0000,
  `balance_local` decimal(20,2) NOT NULL DEFAULT 0.00,
  `credit_limit` decimal(20,4) DEFAULT 0.0000,
  `start_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_balance_suppliers`
--

CREATE TABLE `account_balance_suppliers` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `currency` varchar(5) DEFAULT NULL,
  `currency_local` varchar(5) DEFAULT NULL,
  `balance` decimal(20,4) DEFAULT 0.0000,
  `balance_local` decimal(20,2) NOT NULL DEFAULT 0.00,
  `debt_limit` decimal(20,4) DEFAULT 0.0000,
  `start_date` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_banks`
--

CREATE TABLE `account_banks` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `account_group_detail_id` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `bank_name` varchar(100) NOT NULL,
  `bank_account` varchar(30) NOT NULL,
  `bank_code` varchar(30) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `currency_local` varchar(10) DEFAULT NULL,
  `balance` int(11) DEFAULT NULL,
  `balance_local` varchar(10) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `p_supplier` tinyint(1) NOT NULL DEFAULT 0,
  `p_customer` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_coa`
--

CREATE TABLE `account_coa` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `account_group_detail_id` varchar(30) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_name` varchar(50) DEFAULT NULL,
  `original_currency` varchar(10) DEFAULT NULL,
  `original_debit` decimal(20,4) DEFAULT 0.0000,
  `original_kredit` decimal(20,4) DEFAULT 0.0000,
  `local_currency` varchar(10) DEFAULT NULL,
  `local_debit` decimal(20,2) DEFAULT 0.00,
  `local_kredit` decimal(20,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_groups`
--

CREATE TABLE `account_groups` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `account_group_details`
--

CREATE TABLE `account_group_details` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `account_group_id` varchar(30) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Struktur dari tabel `ap_payments`
--

CREATE TABLE `ap_payments` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `payment_type` varchar(30) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_no` varchar(30) DEFAULT NULL,
  `payment_by` varchar(30) DEFAULT NULL,
  `bank_account` varchar(30) DEFAULT NULL,
  `cheque_no` varchar(30) DEFAULT NULL,
  `purchase_invoice` varchar(30) DEFAULT NULL,
  `supplier_invoice` varchar(30) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `amount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(20,2) NOT NULL DEFAULT 0.00,
  `payment` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_payment` decimal(20,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `note` text DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `type` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `barcode_divides`
--

CREATE TABLE `barcode_divides` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `reff` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `label_divided` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT 0.00,
  `type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `bc_kind`
--

CREATE TABLE `bc_kind` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(20) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `customer_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `component_id` varchar(30) DEFAULT NULL,
  `qpa` decimal(10,2) DEFAULT 0.00,
  `operation` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `checksheets`
--

CREATE TABLE `checksheets` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `receipt` decimal(10,2) NOT NULL DEFAULT 0.00,
  `accumulate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
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
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `config`
--

INSERT INTO `config` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `deleted`, `number`, `name`, `description`, `address`, `logo`, `favicon`, `image`, `theme`, `tax`, `pph`, `status`) VALUES
('4c9a9e62-3ff6-11ed-a526-7085c2', 'admin', '2022-10-01 06:31:13', NULL, NULL, 0, 'ERP', 'PT BANSHU RUBBER INDONESIA', 'ENTERPRISE RESOURCE PLANNING', 'Cikananga, Cikumpay, Cempaka, Kabupaten Purwakarta, Jawa Barat 41181', 'http://localhost/erp_bri/assets/image/config/logo/1689741200.png', 'http://localhost/erp_bri/assets/image/config/favicon/1689741200.png', 'http://localhost/erp_bri/assets/image/config/login/1689741200.jpg', 'metro-orange', 11, 0, 0);

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
('WH-RCD-11', 'WH-RCD-12', NULL, 'EXIM-RCD-02', 'QC-RCD-15', 'PUR-RCD-01', 'PUR-RCD-02', 'PC-RCD-03', 'PC-RCD-04', 'WH-RCD-03', 'PC-RCD-10', 'DO/11-22/00', 'DN/11-22/00', NULL, 'PL/10-22/00', 'FCS/11-22/00', 'PR/11-22/00', 'PO/10-22/00', 'JO/11-22/00', 'SS/11-22/00', 'GRN/11-22/00', 'CL/11-22/00');

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
  `item_id` varchar(30) NOT NULL,
  `uom_id` varchar(30) NOT NULL,
  `qty` decimal(10,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
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
  `account_number` varchar(30) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `address_billing` text DEFAULT NULL,
  `attention` varchar(30) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `telp_billing` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `payment_term` int(11) DEFAULT 0,
  `bank_account` varchar(30) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `item_id` varchar(30) DEFAULT NULL,
  `item_cust` varchar(50) DEFAULT NULL,
  `max_order` int(11) NOT NULL DEFAULT 0,
  `ar_balance` int(11) NOT NULL DEFAULT 0,
  `expired` date DEFAULT NULL,
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `item_id` varchar(30) DEFAULT NULL,
  `do_number` varchar(50) DEFAULT NULL,
  `customer_po` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `trans_type` varchar(20) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `origin` varchar(30) DEFAULT NULL,
  `sailing` varchar(30) DEFAULT NULL,
  `ship` varchar(10) DEFAULT NULL,
  `incoterm` varchar(10) DEFAULT NULL,
  `bc_kind` varchar(10) DEFAULT NULL,
  `bc_no` varchar(30) DEFAULT NULL,
  `bc_date` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
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
  `item_id` varchar(30) DEFAULT NULL,
  `so_number` varchar(50) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `trans_type` varchar(20) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_do` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_do` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delivery` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `note` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `exchange_rates`
--

CREATE TABLE `exchange_rates` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `currency_from` varchar(5) DEFAULT NULL,
  `currency_to` varchar(5) DEFAULT NULL,
  `selling` decimal(20,2) DEFAULT 0.00,
  `buying` decimal(20,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `issued_materials`
--

CREATE TABLE `issued_materials` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(50) NOT NULL,
  `component_id` varchar(50) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `period` varchar(6) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `issued_material_details`
--

CREATE TABLE `issued_material_details` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `request_no` varchar(50) NOT NULL,
  `label_no` varchar(50) NOT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `items`
--

CREATE TABLE `items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_family_id` varchar(30) DEFAULT NULL,
  `item_category_id` varchar(30) DEFAULT NULL,
  `uom_id` varchar(30) DEFAULT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `number` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `leadtime` int(11) NOT NULL DEFAULT 0,
  `box` int(11) NOT NULL DEFAULT 0,
  `box_sub` int(11) NOT NULL DEFAULT 0,
  `lot` int(11) NOT NULL DEFAULT 0,
  `supply` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `number` varchar(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_makers`
--

CREATE TABLE `item_makers` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_ng`
--

CREATE TABLE `item_ng` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `document` varchar(30) DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL,
  `process` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `stock` decimal(20,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(11,2) NOT NULL DEFAULT 0.00,
  `scrap` decimal(20,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(20,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(10) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_options`
--

CREATE TABLE `item_options` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_products`
--

CREATE TABLE `item_products` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_sizes`
--

CREATE TABLE `item_sizes` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_specifications`
--

CREATE TABLE `item_specifications` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `item_types`
--

CREATE TABLE `item_types` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_orders`
--

CREATE TABLE `job_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `circuit` varchar(10) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `wire` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `size` varchar(30) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `length` varchar(30) DEFAULT NULL,
  `a_terminal` varchar(30) DEFAULT NULL,
  `a_seal` varchar(30) DEFAULT NULL,
  `a_chi` varchar(20) DEFAULT NULL,
  `a_chc` varchar(30) DEFAULT NULL,
  `a_stripping` varchar(30) DEFAULT NULL,
  `a_process` varchar(30) DEFAULT NULL,
  `a_note` varchar(30) DEFAULT NULL,
  `b_terminal` varchar(30) DEFAULT NULL,
  `b_seal` varchar(30) DEFAULT NULL,
  `b_chi` varchar(30) DEFAULT NULL,
  `b_chc` varchar(30) DEFAULT NULL,
  `b_stripping` varchar(30) DEFAULT NULL,
  `b_process` varchar(30) DEFAULT NULL,
  `b_note` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `job_order_labels`
--

CREATE TABLE `job_order_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `circuit` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `lines`
--

CREATE TABLE `lines` (
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
(1, 'admin', '2023-07-18 17:30:59', 0, '::1', 'Create', 'item_categories', '{\"number\":\"1\",\"name\":\"FINISH GOOD\",\"description\":\"\",\"id\":\"20230718000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-18 17:30:59\"}'),
(2, 'admin', '2023-07-18 17:31:05', 0, '::1', 'Delete', 'item_categories', '{\"id\":\"20230718000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-18 17:30:59\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"number\":\"1\",\"name\":\"FINISH GOOD\",\"description\":\"\",\"status\":\"0\"}');

-- --------------------------------------------------------

--
-- Struktur dari tabel `main_process`
--

CREATE TABLE `main_process` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `number` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `main_process_subs`
--

CREATE TABLE `main_process_subs` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `main_process_id` varchar(30) NOT NULL,
  `number` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `description` text DEFAULT NULL,
  `efficiency` decimal(20,2) DEFAULT 0.00,
  `mp` int(11) DEFAULT 0,
  `flag` int(11) DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('059428c1b43c4e8ca99d7314a7575b', 'admin', '2022-10-16 12:40:26', 'admin', '2022-11-22 23:52:16', 0, '5f52889797034e918456da3c3a7971', NULL, 'Historical Transactions', NULL, 'warehouse/report_history_transactions', 1, '', NULL, NULL, '', 0),
('0bf49f569bb04892bda649b91bf21d', 'admin', '2022-10-16 12:46:14', 'admin', '2023-01-14 12:16:06', 0, 'ae9dda6c8d9e4366a241528c7390ff', NULL, 'LPJ Barang Jadi', NULL, 'beacukai/pertanggung_jawaban_barang_jadi', 4, '', NULL, NULL, '', 0),
('0e4bb0451f1d4a64a4f7be6be13708', 'admin', '2022-10-07 21:38:10', 'admin', '2023-01-13 21:01:33', 0, '93d8c32f689c41399f7ccb5ce434c3', NULL, 'Production Schedules', NULL, 'planning/production_schedules', 3, '', NULL, NULL, '', 0),
('1bae0b2618ae47b5b7884c40519b61', 'admin', '2022-10-19 22:47:40', NULL, NULL, 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', NULL, 'Locations', NULL, 'warehouse/fg_locations', 1, '', NULL, NULL, '', 0),
('1d9918c716514cdfac598ba366aaef', 'admin', '2022-10-24 11:48:12', 'admin', '2023-05-17 21:28:15', 0, '20230517000003', NULL, 'Job Order Parameter', NULL, 'engineering/job_orders', 2, '', NULL, NULL, '', 0),
('1f0d78d3e80a457bb0914831b94c7b', 'admin', '2022-10-16 12:42:52', NULL, NULL, 0, 'e7f55074baa54065bca03b77c62574', NULL, 'Delivery Orders', NULL, 'shipping/delivery_orders', 1, '', NULL, NULL, '', 0),
('20221130000001', 'admin', '2022-11-30 01:01:15', NULL, NULL, 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Conversions', NULL, 'master/convertions', 13, '', NULL, NULL, '', 0),
('20221130000002', 'admin', '2022-11-30 01:01:57', NULL, NULL, 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'WIP Balances', NULL, 'warehouse/wip_balances', 6, '', NULL, NULL, '', 0),
('20221204000001', 'admin', '2022-12-04 20:58:28', 'admin', '2023-02-14 23:27:24', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Upload STO RM', NULL, 'warehouse/sto_rm', 11, '', NULL, NULL, '', 0),
('20221204000002', 'admin', '2022-12-04 22:40:27', 'admin', '2022-12-08 09:40:13', 0, '', NULL, 'MRP', NULL, '', 6, '', NULL, NULL, 'closed', 0),
('20221204000003', 'admin', '2022-12-04 22:43:14', 'admin', '2023-04-03 13:26:37', 0, '20221204000002', NULL, 'Generate MRP', NULL, '', 1, '', NULL, NULL, '', 0),
('20221204000004', 'admin', '2022-12-04 22:44:48', 'admin', '2023-04-03 13:26:45', 0, '20221204000002', NULL, 'MRP Result', NULL, '', 2, '', NULL, NULL, '', 0),
('20221204000005', 'admin', '2022-12-04 22:46:00', 'admin', '2023-04-03 13:26:51', 0, '20221204000002', NULL, 'Convert MRP to PR', NULL, '', 3, '', NULL, NULL, '', 0),
('20221205000002', 'admin', '2022-12-05 08:37:45', 'admin', '2023-02-14 23:27:15', 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', NULL, 'Upload STO FG', NULL, 'warehouse/sto_fg', 6, '', NULL, NULL, '', 0),
('20221205000003', 'admin', '2022-12-05 08:39:27', 'admin', '2023-02-14 23:26:29', 0, '4f841c89e8274fd88335b2346d1ee2', NULL, 'Upload STO WIP', NULL, 'production/sto_wip', 2, '', NULL, NULL, '', 0),
('20230102000001', 'admin', '2023-01-02 13:30:11', 'admin', '2023-02-06 10:39:04', 0, '4f841c89e8274fd88335b2346d1ee2', NULL, 'Final Check Sheets', NULL, 'production/checksheets', 4, '', NULL, NULL, '', 0),
('20230106000001', 'admin', '2023-01-06 00:29:26', 'admin', '2023-01-08 12:24:34', 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', NULL, 'Report', NULL, '', 7, '', NULL, NULL, 'closed', 0),
('20230108000001', 'admin', '2023-01-08 12:23:19', 'admin', '2023-01-12 20:37:06', 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', NULL, 'Scan Receiving FG', NULL, 'warehouse/item_receipts_fg', 4, '', NULL, NULL, '', 0),
('20230109000001', 'admin', '2023-01-09 00:20:26', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Config ISO', NULL, 'admin/config_iso', 7, '', NULL, NULL, '', 0),
('20230111000002', 'admin', '2023-01-11 06:57:14', 'admin', '2023-01-12 22:52:45', 0, 'e7f55074baa54065bca03b77c62574', NULL, 'Shipping Order', NULL, 'shipping/shipping_orders', 2, '', NULL, NULL, '', 0),
('20230113000001', 'admin', '2023-01-13 01:07:37', NULL, NULL, 0, 'e7f55074baa54065bca03b77c62574', NULL, 'Packing Lists', NULL, 'shipping/packing_lists', 4, '', NULL, NULL, '', 0),
('20230113000002', 'admin', '2023-01-13 21:01:05', NULL, NULL, 0, '93d8c32f689c41399f7ccb5ce434c3', NULL, 'Forecast', NULL, '', 1, '', NULL, NULL, '', 0),
('20230114000001', 'admin', '2023-01-14 11:44:54', NULL, NULL, 0, '8344f4bb673644908cf7f59e61f84d', NULL, 'Account Payable', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('20230114000003', 'admin', '2023-01-14 11:49:12', 'admin', '2023-01-31 22:52:47', 0, '20230114000001', NULL, 'Purchase Invoicing', NULL, 'finance/purchase_invoices', 1, '', NULL, NULL, '', 0),
('20230114000005', 'admin', '2023-01-14 11:56:20', NULL, NULL, 0, '8344f4bb673644908cf7f59e61f84d', NULL, 'Account Receivable', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230114000006', 'admin', '2023-01-14 11:58:33', 'admin', '2023-02-02 23:48:50', 0, '20230114000005', NULL, 'Sales Invoicing', NULL, 'finance/sales_invoices', 2, '', NULL, NULL, '', 0),
('20230206000001', 'admin', '2023-02-06 10:00:11', 'admin', '2023-04-04 10:56:35', 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', NULL, 'Scrap Transaction', NULL, 'production/scraps', 6, '', NULL, NULL, '', 0),
('20230215000001', 'admin', '2023-02-15 14:33:49', 'admin', '2023-03-02 23:46:01', 0, 'c10a8ffd0165455989b58f9090798c', NULL, 'Purchase Return', NULL, 'purchase/purchase_returns', 4, '', NULL, NULL, '', 0),
('20230515000001', 'admin', '2023-05-15 21:35:15', NULL, NULL, 0, '8344f4bb673644908cf7f59e61f84d', NULL, 'Master Data', NULL, '', 0, '', NULL, NULL, 'closed', 0),
('20230515000002', 'admin', '2023-05-15 21:35:49', NULL, NULL, 0, '20230515000001', NULL, 'Account Groups', NULL, 'finance/account_groups', 1, '', NULL, NULL, '', 0),
('20230515000003', 'admin', '2023-05-15 21:35:56', NULL, NULL, 0, '20230515000001', NULL, 'Account Group Details', NULL, 'finance/account_group_details', 2, '', NULL, NULL, '', 0),
('20230515000004', 'admin', '2023-05-15 21:36:08', NULL, NULL, 0, '20230515000001', NULL, 'Account Banks', NULL, 'finance/account_banks', 3, '', NULL, NULL, '', 0),
('20230515000005', 'admin', '2023-05-15 21:36:21', NULL, NULL, 0, '20230515000001', NULL, 'Chart of Account', NULL, 'finance/account_coa', 4, '', NULL, NULL, '', 0),
('20230517000001', 'admin', '2023-05-17 00:14:42', NULL, NULL, 0, '20230515000001', NULL, 'Balance Supplier', NULL, 'finance/balance_suppliers', 5, '', NULL, NULL, '', 0),
('20230517000002', 'admin', '2023-05-17 00:14:50', NULL, NULL, 0, '20230515000001', NULL, 'Balance Customer', NULL, 'finance/balance_customers', 6, '', NULL, NULL, '', 0),
('20230517000003', 'admin', '2023-05-17 21:26:51', NULL, NULL, 0, '76e9ec30b2274d13b6fd78bd9771b0', NULL, 'Master Data', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('20230517000004', 'admin', '2023-05-17 21:27:34', NULL, NULL, 0, '20230517000003', NULL, 'Main Process', NULL, 'engineering/main_process', 3, '', NULL, NULL, '', 0),
('20230517000005', 'admin', '2023-05-17 21:27:39', 'admin', '2023-05-17 21:27:47', 0, '20230517000003', NULL, 'Main Process Subs', NULL, 'engineering/main_process_subs', 4, '', NULL, NULL, '', 0),
('20230518000001', 'admin', '2023-05-18 23:37:01', NULL, NULL, 0, '20230517000003', NULL, 'Unit Man Hour', NULL, 'engineering/umh', 5, '', NULL, NULL, '', 0),
('20230601000001', 'admin', '2023-06-01 23:21:01', NULL, NULL, 0, 'c10a8ffd0165455989b58f9090798c', NULL, 'Purchase Order Misc', NULL, 'purchase/purchase_order_others', 3, '', NULL, NULL, '', 0),
('20230611000001', 'admin', '2023-06-11 22:00:16', NULL, NULL, 0, '20230515000001', NULL, 'Exchange Rates', NULL, 'finance/exchange_rates', 7, '', NULL, NULL, '', 0),
('20230614000001', 'admin', '2023-06-14 23:31:10', NULL, NULL, 0, '20230114000001', NULL, 'AP Payments', NULL, 'finance/ap_payments', 3, '', NULL, NULL, '', 0),
('20230709000001', 'admin', '2023-07-09 19:30:57', NULL, NULL, 0, '', NULL, 'Assets', NULL, '', 11, '', NULL, NULL, 'closed', 0),
('20230709000002', 'admin', '2023-07-09 19:31:20', NULL, NULL, 0, '20230709000001', NULL, 'Categories', NULL, 'assets/categories', 1, '', NULL, NULL, '', 0),
('20230710000001', 'admin', '2023-07-10 10:42:44', NULL, NULL, 0, '20230709000001', NULL, 'Fixed Assets', NULL, 'assets/fixeds', 2, '', NULL, NULL, '', 0),
('20230710000002', 'admin', '2023-07-10 10:43:32', NULL, NULL, 0, '20230709000001', NULL, 'Disposed Fixed Assets', NULL, 'assets/disposeds', 3, '', NULL, NULL, '', 0),
('20230710000003', 'admin', '2023-07-10 10:44:07', NULL, NULL, 0, '20230709000001', NULL, 'Transfer Assets', NULL, 'assets/transfers', 4, '', NULL, NULL, '', 0),
('20230710000004', 'admin', '2023-07-10 10:45:12', NULL, NULL, 0, '20230709000001', NULL, 'Report Assets', NULL, 'assets/reports', 5, '', NULL, NULL, '', 0),
('20230710000005', 'admin', '2023-07-10 22:14:47', NULL, NULL, 0, '20230515000001', NULL, 'Serial Faktur Pajak', NULL, 'finance/serial_faktur', 8, '', NULL, NULL, '', 0),
('20230711000001', 'admin', '2023-07-11 10:09:46', NULL, NULL, 0, '20230114000001', NULL, 'Purchase Credit Note', NULL, 'finance/purchase_credits', 2, '', NULL, NULL, '', 0),
('20230717000001', 'admin', '2023-07-17 09:19:30', NULL, NULL, 0, '20230114000005', NULL, 'Sales Invoice Tax', NULL, 'finance/sales_invoice_taxs', 2, '', NULL, NULL, '', 0),
('20230717000002', 'admin', '2023-07-17 09:20:35', 'admin', '2023-07-17 09:20:44', 0, '8344f4bb673644908cf7f59e61f84d', NULL, 'Report', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('20230717000003', 'admin', '2023-07-17 09:21:44', NULL, NULL, 0, '20230717000002', NULL, 'Foreign Currrency Revaluation', NULL, 'finance/foreign_currencies', 1, '', NULL, NULL, '', 0),
('20230718000001', 'admin', '2023-07-18 11:51:39', 'admin', '2023-07-18 11:52:55', 0, '20230114000005', NULL, 'Cash Bank Receipts', NULL, 'finance/ar_receipts', 3, '', NULL, NULL, '', 0),
('23ce7975fd3a45abaf71ef4a805056', 'admin', '2022-10-16 12:38:59', 'admin', '2022-10-19 22:48:18', 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', NULL, 'Wip Receipts', NULL, 'warehouse/wip_receipts', 3, '', NULL, NULL, '', 0),
('255bcee4fb4c45d8aa938b8279893c', 'admin', '2022-10-07 18:05:21', 'admin', '2022-10-07 18:07:44', 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Customers', NULL, 'master/customers', 4, '', NULL, NULL, '', 0),
('2a4389152cc646148f199267b768d0', 'admin', '2022-11-04 21:06:23', 'admin', '2022-11-05 23:22:26', 0, '834ee81f71d14a1ba35708d85f7526', NULL, 'Outstanding PR', NULL, 'purchase/report_outstanding_pr', 2, '', NULL, NULL, '', 0),
('2c33a59b7b6c41be80df4129f90b71', 'admin', '2022-10-26 11:41:41', 'admin', '2022-11-15 10:14:43', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Warehouse **', NULL, '', 1, '', NULL, NULL, '', 0),
('2d0a643865084c4ba9fbc7869370fa', 'admin', '2022-10-16 12:45:54', 'admin', '2023-01-14 12:15:46', 0, 'ae9dda6c8d9e4366a241528c7390ff', NULL, 'LPJ Bahan Penolong', NULL, 'beacukai/pertanggung_jawaban_penolong', 3, '', NULL, NULL, '', 0),
('3038d96df22048608bbbb8762bbe24', 'admin', '2022-10-07 18:06:43', NULL, NULL, 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Unit Of Measure', NULL, 'master/uom', 9, '', NULL, NULL, '', 0),
('30baffda74154b428e3effd069d09e', 'admin', '2022-11-14 21:19:04', 'admin', '2023-02-06 23:06:21', 0, '4f841c89e8274fd88335b2346d1ee2', NULL, 'Item NG Transaction', NULL, 'production/item_ng', 2, '', NULL, NULL, '', 0),
('3257e9a11a8543b8afd8e27c0a21d2', 'admin', '2022-11-14 22:24:23', 'admin', '2022-12-25 15:36:53', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Return Material', NULL, 'warehouse/return_materials', 9, '', NULL, NULL, '', 0),
('3265ca2128e841a9ba488846de25f7', 'admin', '2022-10-16 12:44:03', 'admin', '2023-02-05 21:20:38', 0, '20230114000001', NULL, 'AP Aging Schedules', NULL, 'finance/ap_schedules', 2, '', NULL, NULL, '', 0),
('3562627a9d084609a7dea66faf8598', 'admin', '2022-10-17 22:24:53', 'admin', '2022-10-17 22:25:25', 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Products', NULL, 'master/item_products', 8, '', NULL, NULL, '', 0),
('37f8809007bf4f16aa993f6c0548e9', 'admin', '2022-11-04 21:26:14', 'admin', '2023-01-06 23:41:46', 0, '20230106000001', NULL, 'Historical Transaction FG', NULL, 'warehouse/report_history_transactions_fg', 1, '', NULL, NULL, '', 0),
('39894a92c9e9456997bb6af3b21bd3', 'admin', '2022-10-07 17:23:06', NULL, NULL, 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Sizes', NULL, 'master/item_sizes', 5, '', NULL, NULL, '', 0),
('3c5e75290ca947eab000aa09269753', 'admin', '2022-10-07 18:07:01', NULL, NULL, 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Line Productions', NULL, 'master/lines', 10, '', NULL, NULL, '', 0),
('3ca074598cf94973b83bb1facb1190', 'admin', '2022-10-07 17:22:58', NULL, NULL, 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Types', NULL, 'master/item_types', 4, '', NULL, NULL, '', 0),
('3dd1a46ef2834b79a8da5080c496e4', 'admin', '2022-10-07 17:23:26', 'admin', '2022-10-07 17:47:28', 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Colors', NULL, 'master/item_colors', 6, '', NULL, NULL, '', 0),
('3f423b73a7b04367853767d7de4bc5', 'admin', '2022-10-13 16:14:28', 'admin', '2022-10-26 11:49:14', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Barcode Divides', NULL, 'warehouse/barcode_divides', 7, '', NULL, NULL, '', 0),
('42c5d26116ee453e9225b91d3c827d', 'admin', '2022-10-16 12:43:53', 'admin', '2023-07-18 11:52:31', 0, '20230114000005', NULL, 'AR Aging Schedules', NULL, 'finance/ar_schedules', 4, '', NULL, NULL, '', 0),
('4436260df6074dfb8676de448a1a89', 'admin', '2022-10-26 11:23:34', 'admin', '2022-11-14 20:56:50', 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Transaction Type', NULL, 'master/transaction_types', 3, '', NULL, NULL, '', 0),
('44964312f0264429978158ada88843', 'admin', '2022-09-29 16:12:08', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Users', NULL, 'admin/users', 2, '', NULL, NULL, '', 0),
('48be2218f3134d0d900c14b875cd4b', 'admin', '2022-10-19 22:47:47', NULL, NULL, 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', NULL, 'Location Items', NULL, 'warehouse/fg_location_items', 2, '', NULL, NULL, '', 0),
('4f841c89e8274fd88335b2346d1ee2', 'admin', '2022-10-13 15:34:01', 'admin', '2022-10-24 11:23:35', 0, '', NULL, 'Production', NULL, '', 6, '', NULL, NULL, 'closed', 0),
('50db640c2a0f47ab8958d00958d1b7', 'admin', '2022-10-16 12:43:01', 'admin', '2023-01-11 07:01:11', 0, 'e7f55074baa54065bca03b77c62574', NULL, 'Delivery Notes', NULL, 'shipping/delivery_notes', 3, '', NULL, NULL, '', 0),
('50f02a4e5e0845f4aab2b43bf63047', 'admin', '2022-10-26 11:48:34', 'admin', '2022-12-04 20:53:12', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Stock Transfer **', NULL, '', 4, '', NULL, NULL, '', 0),
('5214613be76e4e08ab01a260c8ef55', 'admin', '2022-10-16 12:46:49', 'admin', '2023-01-14 12:16:45', 0, 'ae9dda6c8d9e4366a241528c7390ff', NULL, 'LPJ  Mesin Dan Kantor', NULL, 'beacukai/pertanggung_jawaban_mesin_kantor', 6, '', NULL, NULL, '', 0),
('5666bae5c6254bab977fcd4fd7129e', 'admin', '2022-10-19 22:48:06', 'admin', '2022-10-26 11:42:06', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Locations', NULL, 'warehouse/rm_locations', 2, '', NULL, NULL, '', 0),
('5ad1d62d08a3408d9ab3a84c105d15', 'admin', '2022-10-13 16:14:00', 'admin', '2022-10-26 11:48:52', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Issued Materials', NULL, 'warehouse/issued_materials', 5, '', NULL, NULL, '', 0),
('5cf9545d619a497ab20036d1c587c6', 'admin', '2022-11-04 21:15:14', 'admin', '2022-11-14 19:39:53', 0, '5f52889797034e918456da3c3a7971', NULL, 'Check Serial No', NULL, 'warehouse/report_check_serialno', 3, '', NULL, NULL, '', 0),
('5f52889797034e918456da3c3a7971', 'admin', '2022-10-16 12:39:34', 'admin', '2022-12-04 21:01:52', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Reports', NULL, '', 14, '', NULL, NULL, 'closed', 0),
('612df21b8cd34fb5869011b0e92f81', 'admin', '2022-10-07 17:22:46', NULL, NULL, 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Makers', NULL, 'master/item_makers', 3, '', NULL, NULL, '', 0),
('62309884bfe74e5696f10f32243d97', 'admin', '2022-10-07 17:22:15', NULL, NULL, 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Item Cards', NULL, 'master/items', 1, '', NULL, NULL, '', 0),
('62482f126fe940939924a187d617ea', 'admin', '2022-11-04 20:55:41', 'admin', '2022-11-05 23:22:08', 0, 'f0546fc46bf54b9385492a0da1fbf2', NULL, 'Outstanding SO', NULL, 'planning/report_outstanding_so', 1, '', NULL, NULL, '', 0),
('670141b0c0ac4f61bba46f0c85acd6', 'admin', '2022-10-12 04:55:52', NULL, NULL, 0, 'c10a8ffd0165455989b58f9090798c', NULL, 'Purchase Order Receipts', NULL, 'purchase/purchase_order_receipts', 3, '', NULL, NULL, '', 0),
('687d6466b56c4d0f926dd655ae7ab9', 'admin', '2022-10-16 12:47:04', 'admin', '2022-12-27 02:21:48', 0, 'ae9dda6c8d9e4366a241528c7390ff', NULL, 'Laporan Posisi WIP', NULL, 'beacukai/posisi_wip', 7, '', NULL, NULL, '', 0),
('6ccd20c54d1d415189120ec5cc6c81', 'admin', '2022-09-29 16:41:40', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Config', NULL, 'admin/config', 7, '', NULL, NULL, '', 0),
('7097c7632da5440994c39f828678ab', 'admin', '2022-10-16 12:46:32', 'admin', '2023-01-14 12:16:24', 0, 'ae9dda6c8d9e4366a241528c7390ff', NULL, 'LPJ Barang Scrap', NULL, 'beacukai/pertanggung_jawaban_barang_scrap', 5, '', NULL, NULL, '', 0),
('76e9ec30b2274d13b6fd78bd9771b0', 'admin', '2022-10-24 11:20:53', 'admin', '2022-10-26 11:31:04', 0, '', NULL, 'Engineering', NULL, '', 2, '', NULL, NULL, 'closed', 0),
('77b2347e87ce44baab3508ea740325', 'admin', '2022-11-04 21:26:32', 'admin', '2022-11-04 21:37:15', 0, '1288d74e8d834fd69abd1b7460dc15', NULL, 'Stock Value  FG **', NULL, '', 2, '', NULL, NULL, '', 0),
('7b3fd71d12d04dfdbfe4323903def5', 'admin', '2022-10-07 21:38:23', 'admin', '2023-01-13 21:01:50', 0, '93d8c32f689c41399f7ccb5ce434c3', NULL, 'Supply Sheets', NULL, 'planning/supply_sheets', 4, '', NULL, NULL, '', 0),
('7d7f997ca58f425c8783ff7a7f994c', 'admin', '2022-10-07 18:04:20', 'admin', '2022-11-13 22:44:34', 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Product Familys', NULL, 'master/item_familys', 1, '', NULL, NULL, '', 0),
('7e92720f68204d2b948412828a116b', 'admin', '2022-10-16 12:38:34', 'admin', '2022-10-26 11:49:04', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Item Receipts', NULL, 'warehouse/item_receipts', 6, '', NULL, NULL, '', 0),
('8001792212e640eb8e67b13a16a26d', 'admin', '2022-10-07 18:02:48', 'admin', '2023-05-17 21:28:26', 0, '20230517000003', NULL, 'Bill Of Materials', NULL, 'master/bom', 1, '', NULL, NULL, '', 0),
('8344f4bb673644908cf7f59e61f84d', 'admin', '2022-10-16 12:42:09', 'admin', '2022-10-24 11:24:23', 0, '', NULL, 'Finance', NULL, '', 10, '', NULL, NULL, 'closed', 0),
('834ee81f71d14a1ba35708d85f7526', 'admin', '2022-10-26 11:53:43', 'admin', '2022-10-26 11:57:10', 0, 'c10a8ffd0165455989b58f9090798c', NULL, 'Report ', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('865cff3b613848cebcc7cb9667fbb1', 'admin', '2022-10-16 12:45:08', NULL, NULL, 0, 'ae9dda6c8d9e4366a241528c7390ff', NULL, 'Pemasukan Pabean', NULL, 'beacukai/pemasukan_pabean', 1, '', NULL, NULL, '', 0),
('8a5ae39e16a54354bd0ea87c1ff963', 'admin', '2022-10-07 18:07:11', NULL, NULL, 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Plants', NULL, 'master/plants', 11, '', NULL, NULL, '', 0),
('8f2e2ea5fa0649619863e4a30f1cbc', 'admin', '2022-10-12 04:55:32', NULL, NULL, 0, 'c10a8ffd0165455989b58f9090798c', NULL, 'Purchase Orders', NULL, 'purchase/purchase_orders', 2, '', NULL, NULL, '', 0),
('90af5528e9a4458585b2d74bb73b05', 'admin', '2022-10-07 17:22:38', 'admin', '2022-10-24 11:44:25', 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Specifications', NULL, 'master/item_specifications', 2, '', NULL, NULL, '', 0),
('93d8c32f689c41399f7ccb5ce434c3', 'admin', '2022-10-07 21:37:16', 'admin', '2022-10-24 11:23:03', 0, '', NULL, 'Planning', NULL, '', 4, '', NULL, NULL, 'closed', 0),
('93d9f074b9e142eaa1103045d07273', 'admin', '2022-10-16 12:45:20', NULL, NULL, 0, 'ae9dda6c8d9e4366a241528c7390ff', NULL, 'Pengeluaran Pabean', NULL, 'beacukai/pengeluaran_pabean', 2, '', NULL, NULL, '', 0),
('955ae49b75df4f678ec2324489ae3d', 'admin', '2022-10-19 22:47:58', 'admin', '2022-10-26 11:42:14', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Location Items', NULL, 'warehouse/rm_location_items', 3, '', NULL, NULL, '', 0),
('9c04c7dfcf6c44cc9a0ec44b1c7691', 'admin', '2022-10-13 16:06:35', 'admin', '2022-10-24 11:23:51', 0, '', NULL, 'Warehouse RM', NULL, '', 7, '', NULL, NULL, 'closed', 0),
('a015669f743f49d18be7541f4e9e4f', 'admin', '2022-10-07 17:23:39', NULL, NULL, 0, 'e9d8310044d3481f81834d14f91bd7', NULL, 'Options', NULL, 'master/item_options', 7, '', NULL, NULL, '', 0),
('a5d9db23534f44fa9d58a7f996ce2a', 'admin', '2022-10-07 18:05:36', NULL, NULL, 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Suppliers', NULL, 'master/suppliers', 6, '', NULL, NULL, '', 0),
('a98a56d8e07944d4a5a3d628358f03', 'admin', '2022-10-07 18:06:19', 'admin', '2022-10-07 18:07:49', 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Customer Items', NULL, 'master/customer_items', 5, '', NULL, NULL, '', 0),
('ae9dda6c8d9e4366a241528c7390ff', 'admin', '2022-10-16 12:42:17', 'admin', '2023-07-09 19:31:01', 0, '', NULL, 'Customs', NULL, '', 12, '', NULL, NULL, 'closed', 0),
('b3caa83cf1534926a614edc17ed055', 'admin', '2022-10-12 04:55:22', NULL, NULL, 0, 'c10a8ffd0165455989b58f9090798c', NULL, 'Purchase Requests', NULL, 'purchase/purchase_requests', 1, '', NULL, NULL, '', 0),
('b679033b3256414b8f916c69f17674', 'admin', '2022-09-29 16:22:01', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Approval', NULL, 'admin/approvals', 1, '', NULL, NULL, '', 0),
('b7f1f62e48c34c73ba97bef9e22ffb', 'admin', '2022-11-12 23:20:20', NULL, NULL, 0, 'f0546fc46bf54b9385492a0da1fbf2', NULL, 'Outstanding WO', NULL, 'planning/report_outstanding_wo', 2, '', NULL, NULL, '', 0),
('c10a8ffd0165455989b58f9090798c', 'admin', '2022-10-12 04:54:07', 'admin', '2022-10-24 11:23:23', 0, '', NULL, 'Purchasing', NULL, '', 5, '', NULL, NULL, 'closed', 0),
('c5e3a7bb003a42309c42907778173d', 'admin', '2022-10-13 15:38:32', 'admin', '2023-01-13 21:02:09', 0, '93d8c32f689c41399f7ccb5ce434c3', NULL, 'Non Supply Sheet', NULL, 'production/supply_materials', 5, '', NULL, NULL, '', 0),
('c8f8362a5f6c432ab27d37213f15d4', 'admin', '2022-09-29 16:35:49', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Setting Users', NULL, 'admin/setting_users', 6, '', NULL, NULL, '', 0),
('c98376ce677644eea31e91bcb49438', 'admin', '2022-10-07 18:06:31', 'admin', '2022-10-07 18:08:03', 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Supplier Items', NULL, 'master/supplier_items', 7, '', NULL, NULL, '', 0),
('cf98f97766f6405590b26daa586e00', 'admin', '2022-09-29 16:05:52', NULL, NULL, 0, '', NULL, 'Administrator', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('d13439e3f2324450a69b4e0e50159a', 'admin', '2022-09-29 16:15:42', 'admin', '2022-09-29 16:36:50', 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Menu', NULL, 'admin/menus', 3, '', NULL, NULL, '', 0),
('d188047ab21d45979c64c990a91e23', 'admin', '2022-10-07 18:04:43', 'admin', '2022-10-07 18:20:27', 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Currencies', NULL, 'master/currencies', 8, '', NULL, NULL, '', 0),
('da7ff9a6b87a4b77bf7d89ef1f0c62', 'admin', '2022-10-18 08:55:12', 'admin', '2022-10-24 11:24:04', 0, '', NULL, 'Warehouse FG', NULL, '', 8, '', NULL, NULL, 'closed', 0),
('de3f6855009e49deb7fd2fdd0f3b3d', 'admin', '2022-09-29 16:32:23', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Logs', NULL, 'admin/logs', 4, '', NULL, NULL, '', 0),
('de6779722f6347d798b07350eee0ef', 'admin', '2022-11-14 22:26:21', 'admin', '2023-01-13 21:02:27', 0, '93d8c32f689c41399f7ccb5ce434c3', NULL, 'Material Requesition', NULL, 'planning/supply_requestions', 6, '', NULL, NULL, '', 0),
('e0f3586f91ee4ab2952b6a8ad0f5db', 'admin', '2022-10-07 17:21:14', 'admin', '2022-10-24 11:22:48', 0, '', NULL, 'Master Data', NULL, '', 3, '', NULL, NULL, 'closed', 0),
('e3c31e10b6c64e119b068ae4b73be6', 'admin', '2022-09-29 16:35:33', 'admin', '2022-09-29 16:36:31', 0, 'cf98f97766f6405590b26daa586e00', NULL, 'Setting Menu', NULL, 'admin/setting_menus', 5, '', NULL, NULL, '', 0),
('e505e5e5d2564aa280b3d568c8dd6c', 'admin', '2022-11-14 20:57:28', 'admin', '2022-11-14 20:59:55', 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', NULL, 'Adjusment **', NULL, '', 8, '', NULL, NULL, '', 0),
('e780677ba5764a128f4ae77e3b720e', 'admin', '2022-10-07 21:37:33', 'admin', '2023-01-13 21:01:17', 0, '93d8c32f689c41399f7ccb5ce434c3', NULL, 'Sales Orders', NULL, 'planning/sales_orders', 2, '', NULL, NULL, '', 0),
('e7f55074baa54065bca03b77c62574', 'admin', '2022-10-16 12:41:56', 'admin', '2022-10-24 11:24:12', 0, '', NULL, 'Shipping', NULL, '', 9, '', NULL, NULL, 'closed', 0),
('e9d8310044d3481f81834d14f91bd7', 'admin', '2022-10-07 17:21:33', 'admin', '2022-10-24 11:42:19', 0, '76e9ec30b2274d13b6fd78bd9771b0', NULL, 'Items', NULL, '', 1, '', NULL, NULL, 'closed', 0),
('ec2cdc9e44cb4ccba8f21e9dac5a9e', 'admin', '2022-10-16 13:56:46', NULL, NULL, 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'BC Kind', NULL, 'master/bc_kind', 12, '', NULL, NULL, '', 0),
('ec436420c2734ec3a864eeedbe53c6', 'admin', '2022-10-07 17:25:13', 'admin', '2022-10-26 11:21:27', 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', NULL, 'Categories', NULL, 'master/item_categories', 2, '', NULL, NULL, '', 0),
('f0546fc46bf54b9385492a0da1fbf2', 'admin', '2022-11-04 20:50:31', 'admin', '2023-01-13 21:02:42', 0, '93d8c32f689c41399f7ccb5ce434c3', NULL, 'Report', NULL, '', 7, '', NULL, NULL, 'closed', 0),
('fd04621539994742b73c6f4cbfa63e', 'admin', '2022-10-26 11:54:57', 'admin', '2022-11-05 23:22:17', 0, '834ee81f71d14a1ba35708d85f7526', NULL, 'Outstanding PO', NULL, 'purchase/report_outstanding_po', 1, '', NULL, NULL, '', 0);

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
-- Struktur dari tabel `packing_lists`
--

CREATE TABLE `packing_lists` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `dn_number` varchar(50) DEFAULT NULL,
  `customer_po` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pallet_no` decimal(10,2) DEFAULT 0.00,
  `carton` decimal(10,2) DEFAULT 0.00,
  `net_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `measure` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `plants`
--

CREATE TABLE `plants` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `production_schedules`
--

CREATE TABLE `production_schedules` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `line_id` varchar(30) DEFAULT NULL,
  `so_number` varchar(30) DEFAULT NULL,
  `so_date` date DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `period` varchar(6) DEFAULT NULL,
  `month` varchar(2) DEFAULT NULL,
  `year` varchar(4) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_credits`
--

CREATE TABLE `purchase_credits` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `pr_no` varchar(30) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `uom` varchar(10) DEFAULT NULL,
  `price` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `returned` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_idr` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_sub` decimal(20,2) NOT NULL DEFAULT 0.00,
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `family_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pph` int(11) NOT NULL DEFAULT 0,
  `payment_term` int(10) NOT NULL DEFAULT 0,
  `currency` varchar(10) DEFAULT NULL,
  `total_sub` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_discount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_vat` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_pph` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_grand` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_dp` decimal(20,2) NOT NULL DEFAULT 0.00,
  `type` varchar(10) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `voucher` varchar(20) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `por_no` varchar(50) DEFAULT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `item_name` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(20) DEFAULT NULL,
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_idr` decimal(20,2) NOT NULL DEFAULT 0.00,
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `po_name` varchar(50) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `qty` decimal(20,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `taxes` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_vat` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_dp` decimal(20,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `revision` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_labels`
--

CREATE TABLE `purchase_order_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `receipt_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_others`
--

CREATE TABLE `purchase_order_others` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `po_date` date DEFAULT NULL,
  `po_name` varchar(50) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `qty` decimal(20,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `taxes` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_dp` decimal(20,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `revision` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_order_receipts`
--

CREATE TABLE `purchase_order_receipts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `receipt_date` date DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(30) DEFAULT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `bc_kind` varchar(30) DEFAULT NULL,
  `bc_aju` varchar(30) DEFAULT NULL,
  `bc_document` varchar(50) DEFAULT NULL,
  `bc_date` date DEFAULT NULL,
  `awb_no` varchar(50) DEFAULT NULL,
  `awb_date` date DEFAULT NULL,
  `qty_po` decimal(10,2) DEFAULT 0.00,
  `qty_os` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_receipt` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_mpq` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_label` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `expected_date` date DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `purchase_returns`
--

CREATE TABLE `purchase_returns` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `return_no` varchar(30) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_name` varchar(50) DEFAULT NULL,
  `po_no` varchar(30) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `qty` decimal(11,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `return_materials`
--

CREATE TABLE `return_materials` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `return_no` varchar(30) DEFAULT NULL,
  `return_id` varchar(30) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `return_name` varchar(50) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `return_material_labels`
--

CREATE TABLE `return_material_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `return_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` int(11) DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sales_invoices`
--

CREATE TABLE `sales_invoices` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `customer_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `taxes` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_term` int(10) NOT NULL DEFAULT 0,
  `currency` varchar(10) DEFAULT NULL,
  `total_sub` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_discount` decimal(20,2) NOT NULL DEFAULT 0.00,
  `total_vat` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_pph` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_grand` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `total_local` decimal(20,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `voucher` varchar(20) DEFAULT NULL,
  `so_number` varchar(50) DEFAULT NULL,
  `dn_number` varchar(50) DEFAULT NULL,
  `customer_po` varchar(30) DEFAULT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `item_name` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(20) DEFAULT NULL,
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `account_number` varchar(30) DEFAULT NULL,
  `account_type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `item_id` varchar(30) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `customer_po` varchar(100) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `vat` varchar(10) DEFAULT NULL,
  `delivery` date DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan_item_receipts`
--

CREATE TABLE `scan_item_receipts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_no` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `receipt_id` varchar(50) DEFAULT NULL,
  `label_no` varchar(50) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scan_item_receipts_fg`
--

CREATE TABLE `scan_item_receipts_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) NOT NULL,
  `checksheet_label` varchar(30) NOT NULL,
  `so_number` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `scraps`
--

CREATE TABLE `scraps` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `document` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `type` varchar(30) DEFAULT 'IN',
  `qty` decimal(11,2) NOT NULL DEFAULT 0.00,
  `uom` varchar(10) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `serial_faktur`
--

CREATE TABLE `serial_faktur` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `start_1` varchar(5) NOT NULL,
  `start_2` varchar(5) NOT NULL,
  `start_3` varchar(5) NOT NULL,
  `start_4` varchar(10) NOT NULL,
  `end_1` varchar(5) NOT NULL,
  `end_2` varchar(5) NOT NULL,
  `end_3` varchar(5) NOT NULL,
  `end_4` varchar(10) NOT NULL,
  `start_no` varchar(30) NOT NULL,
  `end_no` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('009202e6989647abb9d7c3c322d87a', 'admin', '2022-10-07 18:09:41', NULL, NULL, 0, '8a5ae39e16a54354bd0ea87c1ff963', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('04a7682cc50247a8a75f609d17e14a', 'admin', '2022-09-29 17:03:33', NULL, NULL, 0, 'b679033b3256414b8f916c69f17674', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('050203b9cf5e45a5a685fe9f68ec9f', 'admin', '2022-11-14 20:57:53', NULL, NULL, 0, 'fd04621539994742b73c6f4cbfa63e', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('0c364be3fdae42b6b59c01389251d8', 'admin', '2022-11-14 20:58:21', NULL, NULL, 0, 'e505e5e5d2564aa280b3d568c8dd6c', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('0ca3131a3aef48e3ba23c07612b771', 'admin', '2022-10-13 15:38:59', NULL, NULL, 0, '4f841c89e8274fd88335b2346d1ee2', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('0cf3c3b165dc492a9cfae284708dd1', 'admin', '2022-10-13 16:15:05', NULL, NULL, 0, '5ad1d62d08a3408d9ab3a84c105d15', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('0d0af664535841848285de80437691', 'admin', '2022-11-13 22:38:37', NULL, NULL, 0, '5cf9545d619a497ab20036d1c587c6', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('0fddaa1405bf4a6081704dba2da56b', 'admin', '2022-09-29 17:01:01', NULL, NULL, 0, 'cf98f97766f6405590b26daa586e00', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('12787772bb2b4d7a8aa6b10559edc5', 'admin', '2022-10-16 12:48:32', NULL, NULL, 0, '7e92720f68204d2b948412828a116b', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('18944e423a144c35b0c76050a4d74d', 'admin', '2022-09-29 17:04:17', NULL, NULL, 0, 'c8f8362a5f6c432ab27d37213f15d4', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20221123000001', 'admin', '2022-11-23 00:00:39', NULL, NULL, 0, '865cff3b613848cebcc7cb9667fbb1', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20221129000001', 'admin', '2022-11-29 11:10:58', NULL, NULL, 0, '7b3fd71d12d04dfdbfe4323903def5', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20221129000002', 'admin', '2022-11-29 11:11:07', NULL, NULL, 0, 'c5e3a7bb003a42309c42907778173d', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20221130000001', 'admin', '2022-11-30 01:02:11', NULL, NULL, 0, '20221130000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20221130000002', 'admin', '2022-11-30 01:02:16', NULL, NULL, 0, '20221130000002', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20221204000001', 'admin', '2022-12-04 20:58:54', NULL, NULL, 0, '20221204000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20221204000002', 'admin', '2022-12-04 22:41:28', NULL, NULL, 0, '20221204000002', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20221204000003', 'admin', '2022-12-04 22:44:17', NULL, NULL, 0, '20221204000003', 'on', NULL, 'on', NULL, NULL, 'on', 'on', 'on', 0),
('20221204000004', 'admin', '2022-12-04 22:45:09', NULL, NULL, 0, '20221204000004', 'on', NULL, 'on', NULL, NULL, 'on', 'on', 'on', 0),
('20221204000005', 'admin', '2022-12-04 22:46:26', NULL, NULL, 0, '20221204000005', 'on', 'on', NULL, NULL, NULL, 'on', 'on', 'on', 0),
('20221225000001', 'admin', '2022-12-25 15:37:08', NULL, NULL, 0, '3257e9a11a8543b8afd8e27c0a21d2', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230102000001', 'admin', '2023-01-02 13:30:37', NULL, NULL, 0, '23ce7975fd3a45abaf71ef4a805056', 'on', 'on', NULL, 'on', NULL, NULL, 'on', 'on', 0),
('20230102000002', 'admin', '2023-01-02 13:30:49', NULL, NULL, 0, '20230102000001', 'on', 'on', NULL, 'on', NULL, NULL, 'on', 'on', 0),
('20230105000001', 'admin', '2023-01-05 16:54:40', NULL, NULL, 0, '50db640c2a0f47ab8958d00958d1b7', 'on', 'on', NULL, 'on', NULL, NULL, 'on', 'on', 0),
('20230106000001', 'admin', '2023-01-06 00:29:55', NULL, NULL, 0, '20230106000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230108000001', 'admin', '2023-01-08 12:24:58', NULL, NULL, 0, '20230108000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230109000001', 'admin', '2023-01-09 00:20:33', NULL, NULL, 0, '20230109000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230112000001', 'admin', '2023-01-12 22:52:56', NULL, NULL, 0, '20230111000002', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230112000002', 'admin', '2023-01-12 23:49:21', NULL, NULL, 0, '37f8809007bf4f16aa993f6c0548e9', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230113000002', 'admin', '2023-01-13 21:03:10', NULL, NULL, 0, '20230113000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230114000001', 'admin', '2023-01-14 11:45:13', NULL, NULL, 0, '20230114000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230114000005', 'admin', '2023-01-14 11:57:25', NULL, NULL, 0, '20230114000005', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230202000002', 'admin', '2023-02-02 23:48:59', NULL, NULL, 0, '20230114000006', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230202000003', 'admin', '2023-02-02 23:49:31', NULL, NULL, 0, '20230114000003', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230205000001', 'admin', '2023-02-05 21:20:57', NULL, NULL, 0, '3265ca2128e841a9ba488846de25f7', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230205000002', 'admin', '2023-02-05 21:21:01', NULL, NULL, 0, '42c5d26116ee453e9225b91d3c827d', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230206000001', 'admin', '2023-02-06 23:07:06', NULL, NULL, 0, '20230206000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230206000002', 'admin', '2023-02-06 23:07:20', NULL, NULL, 0, '30baffda74154b428e3effd069d09e', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230214000002', 'admin', '2023-02-14 23:27:47', NULL, NULL, 0, '20221205000002', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230214000003', 'admin', '2023-02-14 23:27:53', NULL, NULL, 0, '20221205000003', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230221000001', 'admin', '2023-02-21 00:57:20', NULL, NULL, 0, '93d9f074b9e142eaa1103045d07273', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230221000002', 'admin', '2023-02-21 00:57:28', NULL, NULL, 0, '5214613be76e4e08ab01a260c8ef55', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230221000003', 'admin', '2023-02-21 00:57:32', NULL, NULL, 0, '2d0a643865084c4ba9fbc7869370fa', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230221000004', 'admin', '2023-02-21 00:57:36', NULL, NULL, 0, '0bf49f569bb04892bda649b91bf21d', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230221000005', 'admin', '2023-02-21 00:57:41', NULL, NULL, 0, '7097c7632da5440994c39f828678ab', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230221000006', 'admin', '2023-02-21 00:57:53', NULL, NULL, 0, '687d6466b56c4d0f926dd655ae7ab9', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230302000001', 'admin', '2023-03-02 22:24:05', NULL, NULL, 0, '20230215000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230515000001', 'admin', '2023-05-15 21:36:33', NULL, NULL, 0, '20230515000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230515000002', 'admin', '2023-05-15 21:36:48', NULL, NULL, 0, '20230515000002', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230515000005', 'admin', '2023-05-15 21:36:59', NULL, NULL, 0, '20230515000005', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230517000003', 'admin', '2023-05-17 16:12:20', NULL, NULL, 0, '1f0d78d3e80a457bb0914831b94c7b', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230517000004', 'admin', '2023-05-17 21:15:40', NULL, NULL, 0, '20230515000003', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230517000005', 'admin', '2023-05-17 21:16:01', NULL, NULL, 0, '20230515000004', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230517000006', 'admin', '2023-05-17 21:16:11', NULL, NULL, 0, '20230517000002', 'on', NULL, 'on', NULL, 'on', 'on', 'on', 'on', 0),
('20230517000007', 'admin', '2023-05-17 21:16:15', NULL, NULL, 0, '20230517000001', 'on', NULL, 'on', NULL, 'on', 'on', 'on', 'on', 0),
('20230517000008', 'admin', '2023-05-17 21:28:33', NULL, NULL, 0, '20230517000003', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230517000009', 'admin', '2023-05-17 21:28:40', NULL, NULL, 0, '20230517000005', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230517000010', 'admin', '2023-05-17 21:28:42', NULL, NULL, 0, '20230517000004', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230523000001', 'admin', '2023-05-23 22:06:25', NULL, NULL, 0, '20230518000001', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('20230601000001', 'admin', '2023-06-01 23:21:24', NULL, NULL, 0, '20230601000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230611000001', 'admin', '2023-06-11 22:38:28', NULL, NULL, 0, '20230611000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230614000001', 'admin', '2023-06-14 23:31:19', NULL, NULL, 0, '20230614000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230621000001', 'admin', '2023-06-21 14:25:28', NULL, NULL, 0, '20230113000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230709000001', 'admin', '2023-07-09 19:31:38', NULL, NULL, 0, '20230709000002', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230709000002', 'admin', '2023-07-09 19:32:02', NULL, NULL, 0, '20230709000001', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230710000001', 'admin', '2023-07-10 10:45:47', NULL, NULL, 0, '20230710000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230710000002', 'admin', '2023-07-10 10:46:04', NULL, NULL, 0, '20230710000002', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230710000003', 'admin', '2023-07-10 10:46:17', NULL, NULL, 0, '20230710000003', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230710000004', 'admin', '2023-07-10 10:46:25', NULL, NULL, 0, '20230710000004', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230710000005', 'admin', '2023-07-10 22:15:01', NULL, NULL, 0, '20230710000005', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230711000001', 'admin', '2023-07-11 10:10:21', NULL, NULL, 0, '20230711000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230717000001', 'admin', '2023-07-17 09:22:18', NULL, NULL, 0, '20230717000002', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('20230717000002', 'admin', '2023-07-17 09:22:27', NULL, NULL, 0, '20230717000003', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('20230717000003', 'admin', '2023-07-17 09:22:37', NULL, NULL, 0, '20230717000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230718000001', 'admin', '2023-07-18 11:51:52', NULL, NULL, 0, '20230718000001', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20a062c23b1d4c17aa1c2708fb69ca', 'admin', '2022-10-07 17:31:18', NULL, NULL, 0, 'e0f3586f91ee4ab2952b6a8ad0f5db', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('24638db76e3e4eb7bde1daae897752', 'admin', '2022-10-26 11:43:25', NULL, NULL, 0, '2c33a59b7b6c41be80df4129f90b71', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('265903ce55984c108b379833f53dc0', 'admin', '2022-10-16 13:56:59', NULL, NULL, 0, 'ec2cdc9e44cb4ccba8f21e9dac5a9e', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('29a1fdd336ee4779b0cb9a1bd76f94', 'admin', '2022-11-04 20:57:22', NULL, NULL, 0, 'f0546fc46bf54b9385492a0da1fbf2', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('2b87f0b4f4fb4fb9b0134f49b6769a', 'admin', '2022-10-19 22:49:12', NULL, NULL, 0, '1bae0b2618ae47b5b7884c40519b61', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('3355d1d6a3fb4ccb86c18bd5911c89', 'admin', '2022-10-07 18:20:45', NULL, NULL, 0, 'd188047ab21d45979c64c990a91e23', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('3491bbaf4b1f4d5385ae97a4dd6704', 'admin', '2022-10-16 12:49:23', NULL, NULL, 0, 'e7f55074baa54065bca03b77c62574', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('36cf6d128e674c2e969c97e28e1f09', 'admin', '2022-10-07 18:51:46', NULL, NULL, 0, 'a5d9db23534f44fa9d58a7f996ce2a', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('3a2ee69b825044838f568c6e84c974', 'admin', '2022-10-24 11:49:10', NULL, NULL, 0, '1d9918c716514cdfac598ba366aaef', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('3a642618e5ee477f89cdebf20d7030', 'admin', '2022-10-16 16:07:31', NULL, NULL, 0, '059428c1b43c4e8ca99d7314a7575b', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('3c2f11cdd7834c78b64566a64e1015', 'admin', '2022-10-27 09:44:08', NULL, NULL, 0, 'e780677ba5764a128f4ae77e3b720e', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('3cc2624214d244aa8f41d7657e005c', 'admin', '2022-10-20 05:08:32', NULL, NULL, 0, '0e4bb0451f1d4a64a4f7be6be13708', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('462223b9d3234e1dbfa96cd8963b1f', 'admin', '2022-10-28 00:12:44', NULL, NULL, 0, 'da7ff9a6b87a4b77bf7d89ef1f0c62', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('4755d360c4cf45259dfc75d2c607b0', 'admin', '2022-11-13 22:39:09', NULL, NULL, 0, '834ee81f71d14a1ba35708d85f7526', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('4bd526d8b21543838779825c0484ea', 'admin', '2022-10-17 22:25:38', NULL, NULL, 0, '3562627a9d084609a7dea66faf8598', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('4d7eea6d7e404774980591dd58541c', 'admin', '2022-10-13 16:15:03', NULL, NULL, 0, '3f423b73a7b04367853767d7de4bc5', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('53f9a3dfd3094c909a6db12186e7bc', 'admin', '2022-10-07 18:12:35', NULL, NULL, 0, '3038d96df22048608bbbb8762bbe24', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('61b6723f47044f729ae6d30c3547f9', 'admin', '2022-10-07 20:14:11', NULL, NULL, 0, 'a98a56d8e07944d4a5a3d628358f03', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('632d22f50ae04ae48859dd86e35a8f', 'admin', '2022-10-26 08:34:22', NULL, NULL, 0, '8f2e2ea5fa0649619863e4a30f1cbc', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('6504ca4e927944a4824d7d86c3fe26', 'admin', '2022-11-13 22:39:04', NULL, NULL, 0, '5f52889797034e918456da3c3a7971', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('65359b84216a4160b69f7ce8cc5e5f', 'admin', '2022-10-07 17:31:56', NULL, NULL, 0, '3ca074598cf94973b83bb1facb1190', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('65bdf777a5564d5c94068feb0edcb9', 'admin', '2022-09-29 17:03:57', NULL, NULL, 0, 'de3f6855009e49deb7fd2fdd0f3b3d', 'on', NULL, NULL, 'on', NULL, NULL, 'on', 'on', 0),
('69f4089980ed47e7b01958512bd6c0', 'admin', '2022-11-14 22:29:07', NULL, NULL, 0, 'de6779722f6347d798b07350eee0ef', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('836ff9fa6650482fbf81e4f49bb255', 'admin', '2022-09-29 17:03:46', NULL, NULL, 0, '6ccd20c54d1d415189120ec5cc6c81', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('880ea658785d44929df3fe128c6617', 'admin', '2022-10-13 16:14:50', NULL, NULL, 0, '9c04c7dfcf6c44cc9a0ec44b1c7691', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('8cf9e31a44fd40efba4124fd293640', 'admin', '2022-10-07 17:32:06', NULL, NULL, 0, 'e9d8310044d3481f81834d14f91bd7', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('8e712c0bec164331b1f2131b410425', 'admin', '2022-10-07 21:01:29', NULL, NULL, 0, '8001792212e640eb8e67b13a16a26d', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('9353391666bb4d81bc4c678771b98b', 'admin', '2022-10-12 04:57:35', NULL, NULL, 0, 'c10a8ffd0165455989b58f9090798c', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('962311b4039448699f25c8ef64f470', 'admin', '2022-09-29 17:04:22', NULL, NULL, 0, '44964312f0264429978158ada88843', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('972a76a5a04e416e92a9b4e225c19c', 'admin', '2022-09-29 17:04:02', NULL, NULL, 0, 'd13439e3f2324450a69b4e0e50159a', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('9e02556ff6a6452e8298a7800eee7c', 'admin', '2022-11-16 14:28:37', NULL, NULL, 0, '670141b0c0ac4f61bba46f0c85acd6', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('a4dc58ea1d774f15a3d24f7e9f87bc', 'admin', '2022-10-26 11:38:39', NULL, NULL, 0, '4436260df6074dfb8676de448a1a89', 'on', 'on', 'on', 'on', NULL, NULL, NULL, NULL, 0),
('a88be7a83e364c4eb29cd72be852a9', 'admin', '2022-10-28 00:12:04', NULL, NULL, 0, '76e9ec30b2274d13b6fd78bd9771b0', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('ac40bf63b16d4aeab224d7d7dfb130', 'admin', '2022-10-07 18:38:19', NULL, NULL, 0, '255bcee4fb4c45d8aa938b8279893c', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('b0050a24404c432d9116944bb3bc8c', 'admin', '2022-10-07 17:31:50', NULL, NULL, 0, 'a015669f743f49d18be7541f4e9e4f', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('b0e891c3fe6a447f97150702d185d0', 'admin', '2022-10-07 17:31:35', NULL, NULL, 0, '3dd1a46ef2834b79a8da5080c496e4', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('c150682d482f4c25b613172ba9b880', 'admin', '2022-09-29 17:04:13', NULL, NULL, 0, 'e3c31e10b6c64e119b068ae4b73be6', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('c47b2d67ddb444edb8e1ade2ff78f0', 'admin', '2022-10-07 17:31:48', NULL, NULL, 0, '612df21b8cd34fb5869011b0e92f81', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('ca93e2509fc54771b5ec614a066d85', 'admin', '2022-10-26 11:51:55', NULL, NULL, 0, '50f02a4e5e0845f4aab2b43bf63047', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('d555a830ee91408880789f70d488a2', 'admin', '2022-10-19 22:49:08', NULL, NULL, 0, '5666bae5c6254bab977fcd4fd7129e', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('db5b12161cd041f9a980fb3114e7c3', 'admin', '2022-10-16 12:49:30', NULL, NULL, 0, 'ae9dda6c8d9e4366a241528c7390ff', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('dc02c9c9237e4f3fad8b8787691f63', 'admin', '2022-10-07 17:31:43', NULL, NULL, 0, '62309884bfe74e5696f10f32243d97', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('dd43c9f7bdb549138bdcec6fa0fc62', 'admin', '2022-11-12 23:20:29', NULL, NULL, 0, 'b7f1f62e48c34c73ba97bef9e22ffb', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('e1c69b08bcc5424889afde8cd16ad5', 'admin', '2022-10-16 12:49:28', NULL, NULL, 0, '8344f4bb673644908cf7f59e61f84d', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('e6df2154c7544bee9028c45ee2c732', 'admin', '2022-10-07 17:31:52', NULL, NULL, 0, '39894a92c9e9456997bb6af3b21bd3', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('eb0c375a4dfc4f6b9bfc9f782126b4', 'admin', '2022-10-19 22:49:05', NULL, NULL, 0, '955ae49b75df4f678ec2324489ae3d', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('eba912bd48844e1dbe5689737f6ea9', 'admin', '2022-10-07 21:39:06', NULL, NULL, 0, '93d8c32f689c41399f7ccb5ce434c3', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('edae5d40129f4bd2b4ac567c020497', 'admin', '2022-10-07 18:18:26', NULL, NULL, 0, '3c5e75290ca947eab000aa09269753', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('ef8730f9fdf14c57a7c4aed9870c09', 'admin', '2022-11-14 20:57:58', NULL, NULL, 0, '2a4389152cc646148f199267b768d0', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('eff15051ce0149beb6b5dc8acd36b8', 'admin', '2022-10-07 18:25:23', NULL, NULL, 0, '7d7f997ca58f425c8783ff7a7f994c', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('f10a5347cb5e4f38be8339258b589a', 'admin', '2022-10-07 17:31:32', NULL, NULL, 0, 'ec436420c2734ec3a864eeedbe53c6', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('f6d82192e5e5435584db2ba80b9f03', 'admin', '2022-10-07 17:31:54', NULL, NULL, 0, '90af5528e9a4458585b2d74bb73b05', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('f85929619f53420ea32a0eb9049e1d', 'admin', '2022-11-14 20:58:02', NULL, NULL, 0, '62482f126fe940939924a187d617ea', 'on', NULL, NULL, NULL, NULL, NULL, 'on', 'on', 0),
('fa602303fbd14b4ea23a3c84588123', 'admin', '2022-10-07 20:42:07', NULL, NULL, 0, 'c98376ce677644eea31e91bcb49438', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0),
('fefa2dbad50f425c99fb1a9c9523e6', 'admin', '2022-10-19 22:49:10', NULL, NULL, 0, '48be2218f3134d0d900c14b875cd4b', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 'on', 0);

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
('008d68ea392946b0a647a91eae2ef0', 'admin', '2022-10-07 18:18:29', 'admin', '2022-10-07 18:18:36', 0, 'admin', '3c5e75290ca947eab000aa09269753', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('00e168e4811c41ccaf1b6ffcefaef0', 'admin', '2022-10-07 20:14:15', 'admin', '2022-10-07 20:14:23', 0, 'admin', 'a98a56d8e07944d4a5a3d628358f03', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('0160c0915f6044468c58cebed25158', 'admin', '2022-10-07 20:42:10', 'admin', '2022-10-07 20:42:16', 0, 'admin', 'c98376ce677644eea31e91bcb49438', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('0229a2465c934b579f9126f42bd2fe', 'admin', '2022-10-19 22:49:16', 'admin', '2022-10-19 22:49:47', 0, 'admin', '48be2218f3134d0d900c14b875cd4b', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('0362a1a4d2484b3db9ee8623617182', 'admin', '2022-10-07 21:39:55', 'admin', '2022-11-29 11:11:38', 0, 'admin', '7b3fd71d12d04dfdbfe4323903def5', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('06ba5a67d8e64d378d558138d06610', 'admin', '2022-10-16 13:57:04', 'admin', '2022-10-16 13:57:12', 0, 'admin', 'ec2cdc9e44cb4ccba8f21e9dac5a9e', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('0aa14f5cfbd14fde974cef9eeadc65', 'admin', '2022-10-16 12:50:57', 'admin', '2022-10-16 12:50:58', 0, 'admin', '8344f4bb673644908cf7f59e61f84d', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('1461f733ff9b45e5aea449d22811db', 'admin', '2022-10-12 04:57:16', 'admin', '2023-02-02 07:58:40', 0, 'admin', 'b3caa83cf1534926a614edc17ed055', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('14fc2f3379ab452f89c588532e8b01', 'admin', '2022-10-16 12:50:57', 'admin', '2022-10-16 12:50:58', 0, 'admin', 'e7f55074baa54065bca03b77c62574', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('18534dacb48a4d419b8fd6bdcef8bc', 'admin', '2022-10-16 12:50:57', 'admin', '2023-05-17 09:12:40', 0, 'admin', '1f0d78d3e80a457bb0914831b94c7b', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('19a9dd8462fa4e05846bfef56ec7d5', 'admin', '2022-10-16 12:50:57', 'admin', '2022-10-16 12:51:03', 0, 'admin', '5f52889797034e918456da3c3a7971', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('1b6ba97e74b84b289dfda76069a836', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:47', 0, 'admin', '90af5528e9a4458585b2d74bb73b05', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('1f94e867dde6422f95462d9f3c1afc', 'admin', '2022-11-04 21:07:03', 'admin', '2022-11-14 20:58:16', 0, 'admin', '2a4389152cc646148f199267b768d0', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20221130000001', 'admin', '2022-11-30 01:02:21', 'admin', '2022-11-30 01:02:27', 0, 'admin', '20221130000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20221130000002', 'admin', '2022-11-30 01:02:21', 'admin', '2022-11-30 01:02:35', 0, 'admin', '20221130000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20221204000001', 'admin', '2022-12-04 20:59:21', 'admin', '2022-12-04 20:59:36', 0, 'admin', '20221204000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20221204000002', 'admin', '2022-12-04 22:41:46', 'admin', '2022-12-04 22:41:50', 0, 'admin', '20221204000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20221204000003', 'admin', '2022-12-04 22:47:05', 'admin', '2022-12-04 22:47:22', 0, 'admin', '20221204000003', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20221204000004', 'admin', '2022-12-04 22:47:05', 'admin', '2022-12-04 22:47:26', 0, 'admin', '20221204000004', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20221204000005', 'admin', '2022-12-04 22:47:05', 'admin', '2022-12-04 22:47:29', 0, 'admin', '20221204000005', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20221205000002', 'admin', '2022-12-05 08:39:59', 'admin', '2023-02-14 16:28:27', 0, 'admin', '20221205000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20221205000006', 'admin', '2022-12-05 08:41:52', 'admin', '2023-02-14 16:28:43', 0, 'admin', '20221205000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230102000001', 'admin', '2023-01-02 13:31:55', 'admin', '2023-01-02 13:32:02', 0, 'admin', '20230102000001', 1, 1, 0, 1, 0, 0, 1, 1, 0),
('20230106000001', 'admin', '2023-01-06 00:30:07', 'admin', '2023-01-06 00:30:12', 0, 'admin', '20230106000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230108000001', 'admin', '2023-01-08 12:25:10', 'admin', '2023-01-08 12:25:17', 0, 'admin', '20230108000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230108000010', 'admin', '2023-01-08 17:20:44', 'admin', '2023-01-08 17:20:57', 0, 'admin', '20230109000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230110000002', 'admin', '2023-01-10 23:58:57', 'admin', '2023-01-12 15:53:17', 0, 'admin', '20230111000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230112000001', 'admin', '2023-01-12 16:49:26', 'admin', '2023-01-12 16:49:32', 0, 'admin', '37f8809007bf4f16aa993f6c0548e9', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20230112000002', 'admin', '2023-01-12 18:08:06', 'admin', '2023-01-12 18:08:12', 0, 'admin', '20230113000001', 1, 1, 0, 1, 0, 0, 1, 1, 0),
('20230113000027', 'admin', '2023-01-13 14:03:19', 'admin', '2023-01-13 14:03:31', 0, 'admin', '20230113000002', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230114000001', 'admin', '2023-01-14 04:45:22', 'admin', '2023-01-14 04:45:27', 0, 'admin', '20230114000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230114000009', 'admin', '2023-01-14 04:49:46', 'admin', '2023-02-02 16:49:40', 0, 'admin', '20230114000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230114000016', 'admin', '2023-01-14 04:57:59', 'admin', '2023-01-14 05:01:52', 0, 'admin', '20230114000005', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230114000021', 'admin', '2023-01-14 05:01:46', 'admin', '2023-02-02 16:49:18', 0, 'admin', '20230114000006', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230206000001', 'admin', '2023-02-06 03:01:21', 'admin', '2023-02-06 16:07:50', 0, 'admin', '20230206000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230215000009', 'admin', '2023-02-15 07:34:33', 'admin', '2023-03-02 15:24:17', 0, 'admin', '20230215000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230515000001', 'admin', '2023-05-15 14:37:05', 'admin', '2023-05-15 14:37:08', 0, 'admin', '20230515000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230515000002', 'admin', '2023-05-15 14:37:05', 'admin', '2023-05-15 14:37:19', 0, 'admin', '20230515000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230515000003', 'admin', '2023-05-15 14:37:05', 'admin', '2023-05-17 14:16:35', 0, 'admin', '20230515000003', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230515000004', 'admin', '2023-05-15 14:37:05', 'admin', '2023-05-17 14:16:36', 0, 'admin', '20230515000004', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230515000005', 'admin', '2023-05-15 14:37:05', 'admin', '2023-05-15 14:37:21', 0, 'admin', '20230515000005', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230516000016', 'admin', '2023-05-16 17:15:17', 'admin', '2023-05-17 14:16:38', 0, 'admin', '20230517000001', 1, 0, 1, 0, 1, 1, 1, 1, 0),
('20230516000017', 'admin', '2023-05-16 17:15:17', 'admin', '2023-05-17 14:16:38', 0, 'admin', '20230517000002', 1, 0, 1, 0, 1, 1, 1, 1, 0),
('20230517000001', 'admin', '2023-05-17 14:28:49', 'admin', '2023-05-17 14:28:54', 0, 'admin', '20230517000003', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230517000002', 'admin', '2023-05-17 14:28:49', 'admin', '2023-05-17 14:29:02', 0, 'admin', '20230517000004', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230517000003', 'admin', '2023-05-17 14:28:49', 'admin', '2023-05-17 14:29:02', 0, 'admin', '20230517000005', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230518000001', 'admin', '2023-05-18 16:37:20', 'admin', '2023-05-23 15:07:30', 0, 'admin', '20230518000001', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('20230601000012', 'admin', '2023-06-01 23:21:29', 'admin', '2023-06-01 23:21:36', 0, 'admin', '20230601000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230611000001', 'admin', '2023-06-11 22:38:33', 'admin', '2023-06-11 22:38:42', 0, 'admin', '20230611000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230614000001', 'admin', '2023-06-14 23:31:25', 'admin', '2023-06-14 23:31:33', 0, 'admin', '20230614000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230709000001', 'admin', '2023-07-09 14:31:51', 'admin', '2023-07-09 14:32:18', 0, 'admin', '20230709000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230709000002', 'admin', '2023-07-09 14:32:10', 'admin', '2023-07-09 14:32:13', 0, 'admin', '20230709000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230710000001', 'admin', '2023-07-10 05:46:29', 'admin', '2023-07-10 05:46:45', 0, 'admin', '20230710000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230710000002', 'admin', '2023-07-10 05:46:30', 'admin', '2023-07-10 05:46:45', 0, 'admin', '20230710000002', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230710000003', 'admin', '2023-07-10 05:46:30', 'admin', '2023-07-10 05:46:46', 0, 'admin', '20230710000003', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230710000004', 'admin', '2023-07-10 05:46:30', 'admin', '2023-07-10 05:46:46', 0, 'admin', '20230710000004', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20230710000005', 'admin', '2023-07-10 17:15:06', 'admin', '2023-07-10 17:15:17', 0, 'admin', '20230710000005', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230711000001', 'admin', '2023-07-11 05:11:12', 'admin', '2023-07-11 05:11:26', 0, 'admin', '20230711000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230717000026', 'admin', '2023-07-17 04:22:50', 'admin', '2023-07-17 04:23:08', 0, 'admin', '20230717000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230717000027', 'admin', '2023-07-17 04:22:50', 'admin', '2023-07-17 04:22:55', 0, 'admin', '20230717000002', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230717000028', 'admin', '2023-07-17 04:22:51', 'admin', '2023-07-17 04:22:59', 0, 'admin', '20230717000003', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('20230718000001', 'admin', '2023-07-18 06:51:58', 'admin', '2023-07-18 06:52:08', 0, 'admin', '20230718000001', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('28a44740a9144404a62818c2c9b3c3', 'admin', '2022-10-13 15:39:16', 'admin', '2022-11-14 21:19:39', 0, 'admin', '4f841c89e8274fd88335b2346d1ee2', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('29d380dad4434a93a9295df1e38e5c', 'admin', '2022-10-07 18:25:25', 'admin', '2022-10-07 18:25:32', 0, 'admin', '7d7f997ca58f425c8783ff7a7f994c', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('29d98c205c7a49bb8957c57fcf2194', 'admin', '2022-10-07 18:12:39', 'admin', '2022-10-07 18:12:45', 0, 'admin', '3038d96df22048608bbbb8762bbe24', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('2c192616c6374feebbbc2778dd4443', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:46', 0, 'admin', '44964312f0264429978158ada88843', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('2c6c2377963e456f850413c1ec0699', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-20 17:58:08', 0, 'admin', '0bf49f569bb04892bda649b91bf21d', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('3411e9a22a0d459e8ff184ead36799', 'admin', '2022-11-04 20:56:12', 'admin', '2022-11-04 20:57:37', 0, 'admin', 'f0546fc46bf54b9385492a0da1fbf2', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('3a6ce215d82d45a190bdc8720c9d81', 'admin', '2022-11-04 21:16:20', 'admin', '2022-11-13 22:38:48', 0, 'admin', '5cf9545d619a497ab20036d1c587c6', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('3fdb778ddfa0463d9a08b70509f6a6', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:19', 0, 'admin', 'e0f3586f91ee4ab2952b6a8ad0f5db', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('431f79867ae34d9bab255dc79795ab', 'admin', '2022-10-16 12:50:57', 'admin', '2022-10-16 16:07:42', 0, 'admin', '059428c1b43c4e8ca99d7314a7575b', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('462cae650bbe4beb986000e812d3ef', 'admin', '2022-10-19 22:49:16', 'admin', '2022-10-19 22:49:34', 0, 'admin', '955ae49b75df4f678ec2324489ae3d', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('47181f41a6a24012b6537101a63550', 'admin', '2022-10-16 12:50:57', 'admin', '2022-10-16 12:51:02', 0, 'admin', 'ae9dda6c8d9e4366a241528c7390ff', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('4d8d2cf02c21472889b7a69e574171', 'admin', '2022-10-24 11:50:35', 'admin', '2022-10-24 11:50:43', 0, 'admin', '1d9918c716514cdfac598ba366aaef', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('52af4273642244679c1df3379fe104', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:46', 0, 'admin', '62309884bfe74e5696f10f32243d97', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('5374cfce8d1142f6b5bb30b12e34fb', 'admin', '2022-10-07 18:51:51', 'admin', '2022-10-07 18:51:56', 0, 'admin', 'a5d9db23534f44fa9d58a7f996ce2a', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('53c297fff8664397a72ff7b0baf06c', 'admin', '2022-11-14 22:29:26', 'admin', '2022-11-14 22:29:39', 0, 'admin', 'de6779722f6347d798b07350eee0ef', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('56c39e081a4d4d4c8db20e988f14cc', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:52', 0, 'admin', 'de3f6855009e49deb7fd2fdd0f3b3d', 1, 0, 0, 1, 0, 0, 1, 1, 0),
('59582a43dcb7490ea280e1843780ef', 'admin', '2022-10-16 12:50:57', 'admin', '2023-01-02 13:32:06', 0, 'admin', '23ce7975fd3a45abaf71ef4a805056', 1, 1, 0, 1, 0, 0, 1, 1, 0),
('5df58bcc43c544deaa0190dae5b3a5', 'admin', '2022-10-19 22:49:16', 'admin', '2022-10-19 22:49:47', 0, 'admin', '1bae0b2618ae47b5b7884c40519b61', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('603008c1e1744e99a7b11c808bb0b0', 'admin', '2022-10-17 22:26:01', 'admin', '2022-10-17 22:26:10', 0, 'admin', '3562627a9d084609a7dea66faf8598', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('62aa172fcf7c443aba135013fbcc54', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:26', 0, 'admin', 'cf98f97766f6405590b26daa586e00', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('659bcd71097540bbb17f2f45093a90', 'admin', '2022-11-14 21:19:28', 'admin', '2023-02-06 16:07:53', 0, 'admin', '30baffda74154b428e3effd069d09e', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('67b92be10bca45438aff838eebab55', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:49', 0, 'admin', '3dd1a46ef2834b79a8da5080c496e4', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('67bf89a8259942d799ead773394497', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:31', 0, 'admin', 'c8f8362a5f6c432ab27d37213f15d4', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('690424ca1c514334a403531b2c42cd', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-05 14:21:21', 0, 'admin', '42c5d26116ee453e9225b91d3c827d', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('69a9a95aa074429da6b3324109a0e5', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:48', 0, 'admin', '3ca074598cf94973b83bb1facb1190', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('6aab43a0a59d44a994de6689c6e8d0', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-20 17:58:07', 0, 'admin', '93d9f074b9e142eaa1103045d07273', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('6b0dc36bbeb64bab914af204aa64ef', 'admin', '2022-10-26 11:54:10', 'admin', '2022-11-13 22:39:31', 0, 'admin', '834ee81f71d14a1ba35708d85f7526', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('731edfc14709428a86de81615c5bab', 'admin', '2022-10-24 11:28:38', 'admin', '2022-10-28 00:12:21', 0, 'admin', '76e9ec30b2274d13b6fd78bd9771b0', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('746d6989a790471ca2e4de24a9f871', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:48', 0, 'admin', 'b679033b3256414b8f916c69f17674', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('74fc770978514b5eb1d7774a3995a7', 'admin', '2022-11-14 22:24:52', 'admin', '2022-12-25 15:37:20', 0, 'admin', '3257e9a11a8543b8afd8e27c0a21d2', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('75ab83a3d56647f898f3dbaf951b46', 'admin', '2022-10-07 18:20:50', 'admin', '2022-10-07 18:20:57', 0, 'admin', 'd188047ab21d45979c64c990a91e23', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('7a45ef2342ad477f8f2bde15d3cfe6', 'admin', '2022-10-26 11:55:24', 'admin', '2022-11-14 20:58:15', 0, 'admin', 'fd04621539994742b73c6f4cbfa63e', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('7e6d84cc420e4ece880dcf2ba1cd62', 'admin', '2022-11-14 20:58:27', 'admin', '2023-04-04 04:17:53', 0, 'admin', 'e505e5e5d2564aa280b3d568c8dd6c', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('7e99a42af43943efabea7257e51917', 'admin', '2022-10-07 21:39:55', 'admin', '2022-10-27 09:44:22', 0, 'admin', 'e780677ba5764a128f4ae77e3b720e', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('7f24376a19b949a387d53e5d6a8a02', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-20 17:58:10', 0, 'admin', '687d6466b56c4d0f926dd655ae7ab9', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('84ca9c1ad91b4a50a5ba9893763a73', 'admin', '2022-10-13 16:22:35', 'admin', '2022-10-13 16:22:39', 0, 'admin', '9c04c7dfcf6c44cc9a0ec44b1c7691', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('9b830bdcd7f5457980742d0349d411', 'admin', '2022-10-13 16:22:35', 'admin', '2022-10-13 16:22:38', 0, 'admin', '5ad1d62d08a3408d9ab3a84c105d15', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('9b9c36848ff4460eb505761f37efaf', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:51', 0, 'admin', 'd13439e3f2324450a69b4e0e50159a', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('a07ec23d04c949ce98b1accd15aaab', 'admin', '2022-10-19 22:49:16', 'admin', '2022-10-19 22:49:34', 0, 'admin', '5666bae5c6254bab977fcd4fd7129e', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('a209beb99b2f45d78dccf0f59e1ce8', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:19', 0, 'admin', 'e9d8310044d3481f81834d14f91bd7', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('a20e49562df146b8943abf056dc579', 'admin', '2022-10-18 09:06:57', 'admin', '2022-10-28 00:12:56', 0, 'admin', 'da7ff9a6b87a4b77bf7d89ef1f0c62', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('a67db1f75b28415a8499145f3f2556', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:49', 0, 'admin', '39894a92c9e9456997bb6af3b21bd3', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('a6b14fcb4bf547ff89694b2155cc4b', 'admin', '2022-10-12 04:57:16', 'admin', '2022-11-17 09:59:59', 0, 'admin', '670141b0c0ac4f61bba46f0c85acd6', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('ac542dc754354f7a8c20cf48a747f1', 'admin', '2022-10-07 18:09:55', 'admin', '2022-10-07 18:10:00', 0, 'admin', '8a5ae39e16a54354bd0ea87c1ff963', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('acf7366679934d009014bac1e6b5b3', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-20 17:58:09', 0, 'admin', '5214613be76e4e08ab01a260c8ef55', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('ae95699aa9e74cb290b815d3297258', 'admin', '2022-10-12 04:57:16', 'admin', '2022-10-26 08:34:56', 0, 'admin', '8f2e2ea5fa0649619863e4a30f1cbc', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('af473d04fefb479e967d32fd497e2e', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:53', 0, 'admin', 'e3c31e10b6c64e119b068ae4b73be6', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('b2aca648fc1b4149b9a93d686f0711', 'admin', '2022-10-26 11:38:50', 'admin', '2022-10-26 11:38:58', 0, 'admin', '4436260df6074dfb8676de448a1a89', 1, 1, 1, 1, 0, 0, 0, 0, 0),
('b337fe5f063e487cbce292508e599b', 'admin', '2022-10-26 11:43:31', 'admin', '2023-04-04 04:17:40', 0, 'admin', '2c33a59b7b6c41be80df4129f90b71', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('b8a570e8d8a346abbbfaafa5bd6306', 'admin', '2022-11-12 23:20:33', 'admin', '2022-11-12 23:20:39', 0, 'admin', 'b7f1f62e48c34c73ba97bef9e22ffb', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('b9e388553d05480abfc24ff1f0626e', 'admin', '2022-10-16 12:50:57', 'admin', '2022-11-23 00:00:48', 0, 'admin', '865cff3b613848cebcc7cb9667fbb1', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('be9ffc3fd04247ab96d34ce8698539', 'admin', '2022-10-12 04:57:42', 'admin', '2022-10-12 04:57:45', 0, 'admin', 'c10a8ffd0165455989b58f9090798c', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('bf6aa4a180e349099e8a29456a7e26', 'admin', '2022-10-13 15:39:16', 'admin', '2023-01-05 05:56:33', 0, 'admin', 'c5e3a7bb003a42309c42907778173d', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('bf6d450d82614b77808762eb096776', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-05 14:21:19', 0, 'admin', '3265ca2128e841a9ba488846de25f7', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('c488dab278b9442990b38123488c17', 'admin', '2022-11-04 20:56:12', 'admin', '2022-11-14 20:57:43', 0, 'admin', '62482f126fe940939924a187d617ea', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('c8ddef0df0764958b9d1b106c75761', 'admin', '2022-10-07 21:39:55', 'admin', '2022-10-07 21:39:57', 0, 'admin', '93d8c32f689c41399f7ccb5ce434c3', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('c93d58aadd4b46ce94ac9e8af4f42c', 'admin', '2022-09-29 17:18:39', 'admin', '2022-09-29 17:19:30', 0, 'admin', '6ccd20c54d1d415189120ec5cc6c81', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('cae14167836c4678a4c5b7501ab132', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:50', 0, 'admin', 'ec436420c2734ec3a864eeedbe53c6', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('cd05cdd05cba4139943c629a73ce9a', 'admin', '2022-10-13 16:22:35', 'admin', '2022-10-13 16:22:39', 0, 'admin', '3f423b73a7b04367853767d7de4bc5', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('d27854e569df46aea40d4371dcd448', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:48', 0, 'admin', '612df21b8cd34fb5869011b0e92f81', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('d46f5c5e7d7f43188e2cbf8daefff6', 'admin', '2022-10-07 17:32:17', 'admin', '2022-10-07 17:32:50', 0, 'admin', 'a015669f743f49d18be7541f4e9e4f', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('d6ef8aba57ff4ea29a9d9fa4e823ec', 'admin', '2022-10-16 12:50:57', 'admin', '2023-01-05 16:55:06', 0, 'admin', '50db640c2a0f47ab8958d00958d1b7', 1, 1, 0, 1, 0, 0, 1, 1, 0),
('d76163e3d2974f20905bc81b376fae', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-20 17:58:09', 0, 'admin', '7097c7632da5440994c39f828678ab', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('deb0339279f24b51ab5f8ff91bef81', 'admin', '2022-10-26 11:52:02', 'admin', '2023-04-04 04:17:46', 0, 'admin', '50f02a4e5e0845f4aab2b43bf63047', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('e5bfae8df613456e9404831a6f7fe2', 'admin', '2022-10-07 18:38:22', 'admin', '2022-10-07 18:38:30', 0, 'admin', '255bcee4fb4c45d8aa938b8279893c', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('f0514e257a2745ab9c0df38da7f7ec', 'admin', '2022-10-16 12:50:57', 'admin', '2023-02-20 17:58:08', 0, 'admin', '2d0a643865084c4ba9fbc7869370fa', 1, 0, 0, 0, 0, 0, 1, 1, 0),
('f748fa0619a349a798ba30d0494951', 'admin', '2022-10-07 21:39:55', 'admin', '2022-10-20 05:09:07', 0, 'admin', '0e4bb0451f1d4a64a4f7be6be13708', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('f800beac22864c05983d0ef492ee10', 'admin', '2022-10-07 21:01:34', 'admin', '2022-10-07 21:01:41', 0, 'admin', '8001792212e640eb8e67b13a16a26d', 1, 1, 1, 1, 1, 1, 1, 1, 0),
('fc60a2c4d6294a8887f29faf12f117', 'admin', '2022-10-16 12:50:57', 'admin', '2022-10-16 12:51:04', 0, 'admin', '7e92720f68204d2b948412828a116b', 1, 0, 0, 0, 0, 0, 0, 0, 0);

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
  `do_number` varchar(30) NOT NULL,
  `checksheet_label` varchar(30) DEFAULT NULL,
  `so_number` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `delivery` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `signatures`
--

CREATE TABLE `signatures` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `po_prepared` varchar(30) NOT NULL,
  `po_checked` varchar(30) NOT NULL,
  `po_approved` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_fg`
--

CREATE TABLE `sto_fg` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  `item_id` varchar(30) NOT NULL,
  `process_id` varchar(30) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT 0.00,
  `pic` varchar(30) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_rm`
--

CREATE TABLE `sto_rm` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  `item_id` varchar(30) NOT NULL,
  `process_id` varchar(30) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL,
  `stock` decimal(20,2) NOT NULL DEFAULT 0.00,
  `qty` decimal(10,2) DEFAULT 0.00,
  `balance` decimal(20,2) NOT NULL DEFAULT 0.00,
  `remark` text DEFAULT NULL,
  `pic` varchar(30) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `sto_wip`
--

CREATE TABLE `sto_wip` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT 0,
  `item_id` varchar(30) NOT NULL,
  `process_id` varchar(30) NOT NULL,
  `trans_date` date DEFAULT NULL,
  `departement` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) DEFAULT 0.00,
  `pic` varchar(30) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `number` varchar(20) NOT NULL,
  `account_number` varchar(30) DEFAULT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `attention` varchar(100) DEFAULT NULL,
  `telp` varchar(20) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `payment_term` int(11) NOT NULL DEFAULT 0,
  `incoterm` varchar(30) DEFAULT NULL,
  `vat` int(11) NOT NULL DEFAULT 0,
  `vat_status` varchar(30) DEFAULT NULL,
  `tax` varchar(30) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `type` varchar(10) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `supplier_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `mpq` decimal(10,2) DEFAULT NULL,
  `moq` decimal(10,2) DEFAULT NULL,
  `price` decimal(20,4) NOT NULL DEFAULT 0.0000,
  `remarks` text DEFAULT NULL,
  `calculate` tinyint(1) NOT NULL DEFAULT 0,
  `purchase` int(11) NOT NULL DEFAULT 0,
  `safety_stock` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supply_materials`
--

CREATE TABLE `supply_materials` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_no` varchar(20) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `period` varchar(10) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supply_requestions`
--

CREATE TABLE `supply_requestions` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_no` varchar(20) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `period` varchar(10) DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `supply_sheets`
--

CREATE TABLE `supply_sheets` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `component_id` varchar(30) NOT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `request_no` varchar(20) DEFAULT NULL,
  `request_name` varchar(50) DEFAULT NULL,
  `mpq` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qty_req` decimal(20,2) NOT NULL DEFAULT 0.00,
  `qty_act` decimal(20,2) NOT NULL DEFAULT 0.00,
  `qty_issued` decimal(20,2) NOT NULL DEFAULT 0.00,
  `qty_bal` decimal(20,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaction_types`
--

CREATE TABLE `transaction_types` (
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

-- --------------------------------------------------------

--
-- Struktur dari tabel `umh`
--

CREATE TABLE `umh` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `main_process_id` varchar(30) NOT NULL,
  `main_process_sub_id` varchar(30) NOT NULL,
  `customer_id` varchar(30) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `circuit` varchar(10) DEFAULT NULL,
  `cycle_time` decimal(20,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL
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
  `actived` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `created_by`, `created_date`, `updated_by`, `updated_date`, `approved`, `approved_to`, `approved_by`, `approved_date`, `deleted`, `number`, `name`, `description`, `username`, `password`, `email`, `phone`, `position`, `avatar`, `actived`, `status`) VALUES
('86f9f296025243ed953fe6014ff765', 'admin', '2021-12-26 11:24:58', 'admin', '2022-11-15 13:27:53', 0, NULL, NULL, NULL, 0, '1', 'Administrator', '', 'admin', 'Login@190320', 'admin@aeconsys.com', '88888888888', 'Admin System', NULL, 0, 0);

-- --------------------------------------------------------

--
-- Struktur dari tabel `warehouse_locations`
--

CREATE TABLE `warehouse_locations` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `type` varchar(10) NOT NULL,
  `number` varchar(30) DEFAULT NULL,
  `location` varchar(30) NOT NULL,
  `area` varchar(30) NOT NULL,
  `rack` varchar(30) NOT NULL,
  `level` varchar(30) NOT NULL,
  `level_sub` varchar(30) NOT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `warehouse_location_items`
--

CREATE TABLE `warehouse_location_items` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `type` varchar(10) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `location` varchar(30) DEFAULT NULL,
  `area` varchar(30) DEFAULT NULL,
  `rack` varchar(30) DEFAULT NULL,
  `level` varchar(30) DEFAULT NULL,
  `level_sub` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_balances`
--

CREATE TABLE `wip_balances` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `item_id` varchar(30) NOT NULL,
  `request_no` varchar(30) DEFAULT NULL,
  `begin` decimal(10,2) NOT NULL DEFAULT 0.00,
  `need` decimal(10,2) NOT NULL DEFAULT 0.00,
  `issued` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `warehouse` decimal(10,2) DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipts`
--

CREATE TABLE `wip_receipts` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `workorder` varchar(30) DEFAULT NULL,
  `trans_date` date DEFAULT NULL,
  `wp` varchar(10) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `lot_label` int(11) NOT NULL DEFAULT 0,
  `lot_box` int(11) NOT NULL DEFAULT 0,
  `label` int(11) NOT NULL DEFAULT 0,
  `label_box` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipt_boxs`
--

CREATE TABLE `wip_receipt_boxs` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `checksheet_label` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `wip_receipt_labels`
--

CREATE TABLE `wip_receipt_labels` (
  `id` varchar(30) NOT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL,
  `checksheet_number` varchar(30) DEFAULT NULL,
  `checksheet_label` varchar(30) DEFAULT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `account_balance_customers`
--
ALTER TABLE `account_balance_customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customer_id` (`customer_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_balance_suppliers`
--
ALTER TABLE `account_balance_suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `supplier_id` (`supplier_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_banks`
--
ALTER TABLE `account_banks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bank_account` (`bank_account`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_coa`
--
ALTER TABLE `account_coa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `account_group_detail_id` (`account_group_detail_id`);

--
-- Indeks untuk tabel `account_groups`
--
ALTER TABLE `account_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `account_group_details`
--
ALTER TABLE `account_group_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `account_group_id` (`account_group_id`),
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
-- Indeks untuk tabel `ap_payments`
--
ALTER TABLE `ap_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indeks untuk tabel `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `barcode_divides`
--
ALTER TABLE `barcode_divides`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `reff` (`reff`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `bc_kind`
--
ALTER TABLE `bc_kind`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customers_number` (`customer_id`),
  ADD KEY `items_number` (`item_id`),
  ADD KEY `component_number` (`component_id`);

--
-- Indeks untuk tabel `checksheets`
--
ALTER TABLE `checksheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number` (`number`);

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
  ADD UNIQUE KEY `item_id` (`item_id`),
  ADD KEY `uom_id` (`uom_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `customer_items`
--
ALTER TABLE `customer_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customers_number` (`customer_id`),
  ADD KEY `items_number` (`item_id`);

--
-- Indeks untuk tabel `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `do_number` (`do_number`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `number` (`number`);

--
-- Indeks untuk tabel `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `issued_materials`
--
ALTER TABLE `issued_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `component_id` (`component_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `issued_material_details`
--
ALTER TABLE `issued_material_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `request_no` (`request_no`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `number_2` (`number`),
  ADD KEY `item_familys_id` (`item_family_id`),
  ADD KEY `uom_id` (`uom_id`);

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
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_familys`
--
ALTER TABLE `item_familys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_makers`
--
ALTER TABLE `item_makers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_ng`
--
ALTER TABLE `item_ng`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `workorder` (`workorder`);

--
-- Indeks untuk tabel `item_options`
--
ALTER TABLE `item_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_products`
--
ALTER TABLE `item_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_sizes`
--
ALTER TABLE `item_sizes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_specifications`
--
ALTER TABLE `item_specifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `item_types`
--
ALTER TABLE `item_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `job_orders`
--
ALTER TABLE `job_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customers_number` (`customer_id`),
  ADD KEY `items_number` (`item_id`);

--
-- Indeks untuk tabel `job_order_labels`
--
ALTER TABLE `job_order_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `lines`
--
ALTER TABLE `lines`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

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
-- Indeks untuk tabel `main_process`
--
ALTER TABLE `main_process`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `main_process_subs`
--
ALTER TABLE `main_process_subs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `main_process_id` (`main_process_id`),
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
-- Indeks untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `approvals_id` (`approvals_id`);

--
-- Indeks untuk tabel `packing_lists`
--
ALTER TABLE `packing_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `dn_number` (`dn_number`);

--
-- Indeks untuk tabel `plants`
--
ALTER TABLE `plants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `production_schedules`
--
ALTER TABLE `production_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customers_number` (`customer_id`),
  ADD KEY `items_number` (`item_id`),
  ADD KEY `line_id` (`line_id`),
  ADD KEY `workorder` (`workorder`);

--
-- Indeks untuk tabel `purchase_credits`
--
ALTER TABLE `purchase_credits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `pr_no` (`pr_no`);

--
-- Indeks untuk tabel `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`,`item_id`),
  ADD KEY `purchase_invoices_ibfk_4` (`item_id`),
  ADD KEY `family_id` (`family_id`);

--
-- Indeks untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `request_no` (`request_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_labels`
--
ALTER TABLE `purchase_order_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `receipt_no` (`receipt_id`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `purchase_order_others`
--
ALTER TABLE `purchase_order_others`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_order_receipts`
--
ALTER TABLE `purchase_order_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `receipt_no` (`receipt_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `request_no` (`return_no`),
  ADD KEY `po_no` (`po_no`);

--
-- Indeks untuk tabel `return_materials`
--
ALTER TABLE `return_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `return_no` (`return_no`),
  ADD KEY `return_id` (`return_id`);

--
-- Indeks untuk tabel `return_material_labels`
--
ALTER TABLE `return_material_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `label_no` (`label_no`);

--
-- Indeks untuk tabel `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `supplier_id` (`customer_id`,`item_id`),
  ADD KEY `sales_invoices_ibfk_4` (`item_id`);

--
-- Indeks untuk tabel `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `customer_id` (`customer_id`,`item_id`),
  ADD KEY `sales_orders_ibfk_4` (`item_id`);

--
-- Indeks untuk tabel `scan_item_receipts`
--
ALTER TABLE `scan_item_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `scan_item_receipts_fg`
--
ALTER TABLE `scan_item_receipts_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheet_number` (`checksheet_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `scraps`
--
ALTER TABLE `scraps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `serial_faktur`
--
ALTER TABLE `serial_faktur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `start_no` (`start_no`),
  ADD UNIQUE KEY `finish_no` (`end_no`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `menus_id` (`menus_id`);

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
  ADD KEY `do_number` (`do_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `signatures`
--
ALTER TABLE `signatures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `sto_fg`
--
ALTER TABLE `sto_fg`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `process_id` (`process_id`);

--
-- Indeks untuk tabel `sto_rm`
--
ALTER TABLE `sto_rm`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `sto_wip`
--
ALTER TABLE `sto_wip`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `process_id` (`process_id`);

--
-- Indeks untuk tabel `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `suppliers_number` (`supplier_id`),
  ADD KEY `items_number` (`item_id`);

--
-- Indeks untuk tabel `supply_materials`
--
ALTER TABLE `supply_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `supply_requestions`
--
ALTER TABLE `supply_requestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `supply_sheets`
--
ALTER TABLE `supply_sheets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `components_number` (`component_id`),
  ADD KEY `items_number` (`item_id`),
  ADD KEY `workorder` (`workorder`),
  ADD KEY `request_no` (`request_no`);

--
-- Indeks untuk tabel `transaction_types`
--
ALTER TABLE `transaction_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `umh`
--
ALTER TABLE `umh`
  ADD PRIMARY KEY (`id`),
  ADD KEY `main_process_id` (`main_process_id`),
  ADD KEY `main_process_sub_id` (`main_process_sub_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `uom`
--
ALTER TABLE `uom`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
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
-- Indeks untuk tabel `warehouse_locations`
--
ALTER TABLE `warehouse_locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `warehouse_location_items`
--
ALTER TABLE `warehouse_location_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `wip_balances`
--
ALTER TABLE `wip_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `item_id` (`item_id`) USING BTREE;

--
-- Indeks untuk tabel `wip_receipts`
--
ALTER TABLE `wip_receipts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheet_number` (`checksheet_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `wip_receipt_boxs`
--
ALTER TABLE `wip_receipt_boxs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheet_number` (`checksheet_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `wip_receipt_labels`
--
ALTER TABLE `wip_receipt_labels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checksheet_number` (`checksheet_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `logins`
--
ALTER TABLE `logins`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `account_balance_customers`
--
ALTER TABLE `account_balance_customers`
  ADD CONSTRAINT `account_balance_customers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_balance_customers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_balance_customers_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_balance_suppliers`
--
ALTER TABLE `account_balance_suppliers`
  ADD CONSTRAINT `account_balance_suppliers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_balance_suppliers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_balance_suppliers_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_banks`
--
ALTER TABLE `account_banks`
  ADD CONSTRAINT `account_banks_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_banks_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_coa`
--
ALTER TABLE `account_coa`
  ADD CONSTRAINT `account_coa_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_coa_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_group_detail_id` FOREIGN KEY (`account_group_detail_id`) REFERENCES `account_group_details` (`id`);

--
-- Ketidakleluasaan untuk tabel `account_groups`
--
ALTER TABLE `account_groups`
  ADD CONSTRAINT `account_groups_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_groups_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `account_group_details`
--
ALTER TABLE `account_group_details`
  ADD CONSTRAINT `account_group_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_group_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `account_group_details_ibfk_3` FOREIGN KEY (`account_group_id`) REFERENCES `account_groups` (`id`);

--
-- Ketidakleluasaan untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ap_payments`
--
ALTER TABLE `ap_payments`
  ADD CONSTRAINT `ap_payments_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ap_payments_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ap_payments_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD CONSTRAINT `asset_categories_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `asset_categories_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `barcode_divides`
--
ALTER TABLE `barcode_divides`
  ADD CONSTRAINT `barcode_divides_ibafk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `barcode_divides_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `bc_kind`
--
ALTER TABLE `bc_kind`
  ADD CONSTRAINT `bc_kind_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bc_kind_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `bom`
--
ALTER TABLE `bom`
  ADD CONSTRAINT `bom_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bom_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `bom_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `bom_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `bom_ibfk_5` FOREIGN KEY (`component_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `checksheets`
--
ALTER TABLE `checksheets`
  ADD CONSTRAINT `checksheets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `checksheets_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `convertions_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `convertions_ibfk_4` FOREIGN KEY (`uom_id`) REFERENCES `uom` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Ketidakleluasaan untuk tabel `customer_items`
--
ALTER TABLE `customer_items`
  ADD CONSTRAINT `customer_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `customer_items_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `customer_items_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `delivery_notes`
--
ALTER TABLE `delivery_notes`
  ADD CONSTRAINT `delivery_notes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_notes_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_notes_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `delivery_notes_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `delivery_notes_ibfk_5` FOREIGN KEY (`do_number`) REFERENCES `delivery_orders` (`number`);

--
-- Ketidakleluasaan untuk tabel `delivery_orders`
--
ALTER TABLE `delivery_orders`
  ADD CONSTRAINT `delivery_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_orders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `delivery_orders_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `delivery_orders_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `exchange_rates`
--
ALTER TABLE `exchange_rates`
  ADD CONSTRAINT `exchange_rates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`),
  ADD CONSTRAINT `exchange_rates_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`);

--
-- Ketidakleluasaan untuk tabel `issued_materials`
--
ALTER TABLE `issued_materials`
  ADD CONSTRAINT `issued_material_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `issued_material_ibfk_4` FOREIGN KEY (`component_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `issued_material_details`
--
ALTER TABLE `issued_material_details`
  ADD CONSTRAINT `issued_material_details_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_details_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `issued_material_details_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `issued_material_details_ibfk_4` FOREIGN KEY (`request_no`) REFERENCES `issued_materials` (`request_no`);

--
-- Ketidakleluasaan untuk tabel `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `items_ibfk_3` FOREIGN KEY (`item_family_id`) REFERENCES `item_familys` (`id`),
  ADD CONSTRAINT `items_ibfk_4` FOREIGN KEY (`uom_id`) REFERENCES `uom` (`id`);

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
  ADD CONSTRAINT `item_familys_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_makers`
--
ALTER TABLE `item_makers`
  ADD CONSTRAINT `item_makers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_makers_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_ng`
--
ALTER TABLE `item_ng`
  ADD CONSTRAINT `item_ng_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_ng_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_ng_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_ng_ibfk_4` FOREIGN KEY (`workorder`) REFERENCES `production_schedules` (`workorder`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_options`
--
ALTER TABLE `item_options`
  ADD CONSTRAINT `item_options_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_options_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_products`
--
ALTER TABLE `item_products`
  ADD CONSTRAINT `item_products_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_products_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_sizes`
--
ALTER TABLE `item_sizes`
  ADD CONSTRAINT `item_sizes_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_sizes_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_specifications`
--
ALTER TABLE `item_specifications`
  ADD CONSTRAINT `item_specifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_specifications_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `item_types`
--
ALTER TABLE `item_types`
  ADD CONSTRAINT `item_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `item_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `job_orders`
--
ALTER TABLE `job_orders`
  ADD CONSTRAINT `job_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_orders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_orders_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `job_orders_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `job_order_labels`
--
ALTER TABLE `job_order_labels`
  ADD CONSTRAINT `job_order_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `job_order_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `lines`
--
ALTER TABLE `lines`
  ADD CONSTRAINT `lines_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lines_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Ketidakleluasaan untuk tabel `main_process`
--
ALTER TABLE `main_process`
  ADD CONSTRAINT `main_process_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `main_process_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `main_process_subs`
--
ALTER TABLE `main_process_subs`
  ADD CONSTRAINT `main_process_subs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `main_process_subs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `main_process_subs_ibfk_3` FOREIGN KEY (`main_process_id`) REFERENCES `main_process` (`id`);

--
-- Ketidakleluasaan untuk tabel `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`approvals_id`) REFERENCES `approvals` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `packing_lists`
--
ALTER TABLE `packing_lists`
  ADD CONSTRAINT `packing_lists_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `packing_lists_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `packing_lists_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `packing_lists_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `packing_lists_ibfk_5` FOREIGN KEY (`dn_number`) REFERENCES `delivery_notes` (`number`);

--
-- Ketidakleluasaan untuk tabel `plants`
--
ALTER TABLE `plants`
  ADD CONSTRAINT `plants_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `plants_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `production_schedules`
--
ALTER TABLE `production_schedules`
  ADD CONSTRAINT `production_schedule_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `production_schedule_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `production_schedule_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `production_schedule_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `production_schedule_ibfk_5` FOREIGN KEY (`line_id`) REFERENCES `lines` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_credits`
--
ALTER TABLE `purchase_credits`
  ADD CONSTRAINT `purchase_credits_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_credits_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_credits_ibfk_3` FOREIGN KEY (`pr_no`) REFERENCES `purchase_returns` (`return_no`),
  ADD CONSTRAINT `purchase_credits_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `purchase_credits_ibfk_5` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD CONSTRAINT `purchase_invoices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_invoices_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_invoices_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_invoices_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_order_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `purchase_order_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_order_ibfk_5` FOREIGN KEY (`request_no`) REFERENCES `purchase_requests` (`request_no`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_labels`
--
ALTER TABLE `purchase_order_labels`
  ADD CONSTRAINT `purchase_order_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_labels_ibfk_4` FOREIGN KEY (`receipt_id`) REFERENCES `purchase_order_receipts` (`receipt_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `purchase_order_others`
--
ALTER TABLE `purchase_order_others`
  ADD CONSTRAINT `purchase_order_others_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_others_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_others_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `purchase_order_others_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_order_receipts`
--
ALTER TABLE `purchase_order_receipts`
  ADD CONSTRAINT `purchase_order_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_order_receipts_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_order_receipts_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `purchase_order_receipts_ibfk_5` FOREIGN KEY (`po_no`) REFERENCES `purchase_orders` (`po_no`);

--
-- Ketidakleluasaan untuk tabel `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `purchase_requests_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_requests_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_requests_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `purchase_returns`
--
ALTER TABLE `purchase_returns`
  ADD CONSTRAINT `purchase_returns_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_returns_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_returns_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `purchase_returns_ibfk_4` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `purchase_returns_ibfk_5` FOREIGN KEY (`po_no`) REFERENCES `purchase_orders` (`po_no`);

--
-- Ketidakleluasaan untuk tabel `return_materials`
--
ALTER TABLE `return_materials`
  ADD CONSTRAINT `return_materials_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_materials_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_materials_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `return_material_labels`
--
ALTER TABLE `return_material_labels`
  ADD CONSTRAINT `return_material_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_material_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `return_material_labels_ibfk_3` FOREIGN KEY (`return_id`) REFERENCES `return_materials` (`return_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD CONSTRAINT `sales_invoices_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_invoices_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_invoices_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_invoices_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `sales_orders`
--
ALTER TABLE `sales_orders`
  ADD CONSTRAINT `sales_orders_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sales_orders_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  ADD CONSTRAINT `sales_orders_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `scan_item_receipts`
--
ALTER TABLE `scan_item_receipts`
  ADD CONSTRAINT `scan_item_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scan_item_receipts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `scan_item_receipts_fg`
--
ALTER TABLE `scan_item_receipts_fg`
  ADD CONSTRAINT `scan_item_receipts_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scan_item_receipts_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scan_item_receipts_fg_ibfk_3` FOREIGN KEY (`checksheet_number`) REFERENCES `wip_receipts` (`checksheet_number`);

--
-- Ketidakleluasaan untuk tabel `scraps`
--
ALTER TABLE `scraps`
  ADD CONSTRAINT `scraps_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scraps_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scraps_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `serial_faktur`
--
ALTER TABLE `serial_faktur`
  ADD CONSTRAINT `serial_faktur_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `serial_faktur_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `setting_menus`
--
ALTER TABLE `setting_menus`
  ADD CONSTRAINT `setting_menus_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `setting_menus_ibfk_3` FOREIGN KEY (`menus_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
  ADD CONSTRAINT `shipping_orders_fg_ibfk_3` FOREIGN KEY (`do_number`) REFERENCES `delivery_orders` (`number`);

--
-- Ketidakleluasaan untuk tabel `signatures`
--
ALTER TABLE `signatures`
  ADD CONSTRAINT `signatures_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `signatures_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `sto_fg`
--
ALTER TABLE `sto_fg`
  ADD CONSTRAINT `sto_fg_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_fg_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_fg_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `sto_rm`
--
ALTER TABLE `sto_rm`
  ADD CONSTRAINT `sto_rm_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_rm_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_rm_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `sto_wip`
--
ALTER TABLE `sto_wip`
  ADD CONSTRAINT `sto_wip_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_wip_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sto_wip_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

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
  ADD CONSTRAINT `supplier_items_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `supplier_items_ibfk_4` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `supply_materials`
--
ALTER TABLE `supply_materials`
  ADD CONSTRAINT `supply_materials_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_materials_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_materials_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `supply_requestions`
--
ALTER TABLE `supply_requestions`
  ADD CONSTRAINT `supply_requestions_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_requestions_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_requestions_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `supply_sheets`
--
ALTER TABLE `supply_sheets`
  ADD CONSTRAINT `supply_sheets_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_sheets_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `supply_sheets_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `supply_sheets_ibfk_4` FOREIGN KEY (`component_id`) REFERENCES `items` (`id`),
  ADD CONSTRAINT `supply_sheets_ibfk_5` FOREIGN KEY (`workorder`) REFERENCES `production_schedules` (`workorder`);

--
-- Ketidakleluasaan untuk tabel `transaction_types`
--
ALTER TABLE `transaction_types`
  ADD CONSTRAINT `transaction_types_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_types_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `umh`
--
ALTER TABLE `umh`
  ADD CONSTRAINT `umh_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `umh_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `umh_ibfk_3` FOREIGN KEY (`main_process_id`) REFERENCES `main_process` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `umh_ibfk_4` FOREIGN KEY (`main_process_sub_id`) REFERENCES `main_process_subs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `umh_ibfk_5` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `umh_ibfk_6` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Ketidakleluasaan untuk tabel `warehouse_locations`
--
ALTER TABLE `warehouse_locations`
  ADD CONSTRAINT `warehouse_locations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `warehouse_locations_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `warehouse_location_items`
--
ALTER TABLE `warehouse_location_items`
  ADD CONSTRAINT `warehouse_location_items_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `warehouse_location_items_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `warehouse_location_items_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `wip_balances`
--
ALTER TABLE `wip_balances`
  ADD CONSTRAINT `wip_balances_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_balances_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_balances_ibfk_3` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `wip_receipts`
--
ALTER TABLE `wip_receipts`
  ADD CONSTRAINT `wip_receipts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipts_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipts_ibfk_3` FOREIGN KEY (`checksheet_number`) REFERENCES `checksheets` (`number`);

--
-- Ketidakleluasaan untuk tabel `wip_receipt_boxs`
--
ALTER TABLE `wip_receipt_boxs`
  ADD CONSTRAINT `wip_receipt_boxs_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_boxs_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_boxs_ibfk_3` FOREIGN KEY (`checksheet_number`) REFERENCES `wip_receipts` (`checksheet_number`);

--
-- Ketidakleluasaan untuk tabel `wip_receipt_labels`
--
ALTER TABLE `wip_receipt_labels`
  ADD CONSTRAINT `wip_receipt_labels_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_labels_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wip_receipt_labels_ibfk_3` FOREIGN KEY (`checksheet_number`) REFERENCES `wip_receipts` (`checksheet_number`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
