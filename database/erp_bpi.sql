-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 03 Agu 2023 pada 13.18
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
-- Database: `erp_bpi`
--

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
('4c9a9e62-3ff6-11ed-a526-7085c2', 'admin', '2022-10-01 06:31:13', NULL, NULL, 0, 'ERP', 'PT BANSHU PLASTIC INDONESIA', 'ENTERPRISE RESOURCE PLANNING', '-', 'http://localhost/erp_bpi/assets/image/config/logo/1691061477.png', 'http://localhost/erp_bpi/assets/image/config/favicon/1691061477.png', 'http://localhost/erp_bri/assets/image/config/login/1689741200.jpg', 'metro-blue', 11, 0, 0);

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
(1, NULL, '2023-08-02 09:12:16', 0, '::1', 'A8-1E-84-13-37-56', 'admin', '', 0);

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
(2, 'admin', '2023-07-18 17:31:05', 0, '::1', 'Delete', 'item_categories', '{\"id\":\"20230718000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-18 17:30:59\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"number\":\"1\",\"name\":\"FINISH GOOD\",\"description\":\"\",\"status\":\"0\"}'),
(3, NULL, '2023-08-02 09:12:16', 0, '::1', 'Login', 'Login', '{\"id\":\"86f9f296025243ed953fe6014ff765\",\"departement_id\":null,\"number\":\"1\",\"name\":\"Administrator\",\"username\":\"admin\",\"position\":\"Admin System\"}'),
(4, 'admin', '2023-08-02 16:00:51', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"20230517000003\",\"name\":\"Process\",\"link\":\"engineering\\/process\",\"sort\":\"6\",\"state\":\"\",\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 16:00:51\"}'),
(5, 'admin', '2023-08-02 16:02:12', 0, '::1', 'Create', 'setting_menus', '{\"menus_id\":\"20230802000001\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_upload\":\"on\",\"m_download\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 16:02:12\"}'),
(6, 'admin', '2023-08-02 11:02:17', 0, '::1', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\"}'),
(7, 'admin', '2023-08-02 11:02:23', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(8, 'admin', '2023-08-02 11:02:23', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:23\"}'),
(9, 'admin', '2023-08-02 11:02:24', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:23\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(10, 'admin', '2023-08-02 11:02:24', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:24\"}'),
(11, 'admin', '2023-08-02 11:02:25', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:24\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(12, 'admin', '2023-08-02 11:02:25', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:25\"}'),
(13, 'admin', '2023-08-02 11:02:26', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:25\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(14, 'admin', '2023-08-02 11:02:26', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:26\"}'),
(15, 'admin', '2023-08-02 11:02:27', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:26\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(16, 'admin', '2023-08-02 11:02:27', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:27\"}'),
(17, 'admin', '2023-08-02 11:02:27', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:27\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(18, 'admin', '2023-08-02 11:02:28', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:27\"}'),
(19, 'admin', '2023-08-02 11:02:28', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:27\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(20, 'admin', '2023-08-02 11:02:28', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:28\"}'),
(21, 'admin', '2023-08-02 11:02:29', 0, '::1', 'Update Before', 'setting_users', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 11:02:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:28\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230802000001\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(22, 'admin', '2023-08-02 11:02:29', 0, '::1', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-02 11:02:29\"}'),
(23, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"76e9ec30b2274d13b6fd78bd9771b0\",\"created_by\":\"admin\",\"created_date\":\"2022-10-24 11:20:53\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:31:04\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Engineering\",\"description\":null,\"link\":\"\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(24, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"77b2347e87ce44baab3508ea740325\",\"created_by\":\"admin\",\"created_date\":\"2022-11-04 21:26:32\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-04 21:37:15\",\"deleted\":\"0\",\"menus_id\":\"1288d74e8d834fd69abd1b7460dc15\",\"number\":null,\"name\":\"Stock Value  FG **\",\"description\":null,\"link\":\"\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(25, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:21:14\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:22:48\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Master Data\",\"description\":null,\"link\":\"\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(26, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 21:37:16\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:23:03\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Planning\",\"description\":null,\"link\":\"\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(27, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"c10a8ffd0165455989b58f9090798c\",\"created_by\":\"admin\",\"created_date\":\"2022-10-12 04:54:07\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:23:23\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Purchasing\",\"description\":null,\"link\":\"\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(28, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221204000002\",\"created_by\":\"admin\",\"created_date\":\"2022-12-04 22:40:27\",\"updated_by\":\"admin\",\"updated_date\":\"2022-12-08 09:40:13\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"MRP\",\"description\":null,\"link\":\"\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(29, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230709000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-09 19:30:57\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Assets\",\"description\":null,\"link\":\"\",\"sort\":\"11\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(30, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"created_by\":\"admin\",\"created_date\":\"2022-10-18 08:55:12\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:24:04\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Warehouse FG\",\"description\":null,\"link\":\"\",\"sort\":\"8\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(31, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"created_by\":\"admin\",\"created_date\":\"2022-10-13 16:06:35\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:23:51\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Warehouse RM\",\"description\":null,\"link\":\"\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(32, 'admin', '2023-08-03 17:21:27', 0, '::1', 'Delete', 'menus', '{\"id\":\"8344f4bb673644908cf7f59e61f84d\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:42:09\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:24:23\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Finance\",\"description\":null,\"link\":\"\",\"sort\":\"10\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(33, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"4f841c89e8274fd88335b2346d1ee2\",\"created_by\":\"admin\",\"created_date\":\"2022-10-13 15:34:01\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:23:35\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Production\",\"description\":null,\"link\":\"\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(34, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"e7f55074baa54065bca03b77c62574\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:41:56\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:24:12\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Shipping\",\"description\":null,\"link\":\"\",\"sort\":\"9\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(35, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:42:17\",\"updated_by\":\"admin\",\"updated_date\":\"2023-07-09 19:31:01\",\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Customs\",\"description\":null,\"link\":\"\",\"sort\":\"12\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(36, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230114000003\",\"created_by\":\"admin\",\"created_date\":\"2023-01-14 11:49:12\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-31 22:52:47\",\"deleted\":\"0\",\"menus_id\":\"20230114000001\",\"number\":null,\"name\":\"Purchase Invoicing\",\"description\":null,\"link\":\"finance\\/purchase_invoices\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(37, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230711000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-11 10:09:46\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230114000001\",\"number\":null,\"name\":\"Purchase Credit Note\",\"description\":null,\"link\":\"finance\\/purchase_credits\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(38, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"3265ca2128e841a9ba488846de25f7\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:44:03\",\"updated_by\":\"admin\",\"updated_date\":\"2023-02-05 21:20:38\",\"deleted\":\"0\",\"menus_id\":\"20230114000001\",\"number\":null,\"name\":\"AP Aging Schedules\",\"description\":null,\"link\":\"finance\\/ap_schedules\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(39, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230717000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-17 09:19:30\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230114000005\",\"number\":null,\"name\":\"Sales Invoice Tax\",\"description\":null,\"link\":\"finance\\/sales_invoice_taxs\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(40, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230114000006\",\"created_by\":\"admin\",\"created_date\":\"2023-01-14 11:58:33\",\"updated_by\":\"admin\",\"updated_date\":\"2023-02-02 23:48:50\",\"deleted\":\"0\",\"menus_id\":\"20230114000005\",\"number\":null,\"name\":\"Sales Invoicing\",\"description\":null,\"link\":\"finance\\/sales_invoices\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(41, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230614000001\",\"created_by\":\"admin\",\"created_date\":\"2023-06-14 23:31:10\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230114000001\",\"number\":null,\"name\":\"AP Payments\",\"description\":null,\"link\":\"finance\\/ap_payments\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(42, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230718000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-18 11:51:39\",\"updated_by\":\"admin\",\"updated_date\":\"2023-07-18 11:52:55\",\"deleted\":\"0\",\"menus_id\":\"20230114000005\",\"number\":null,\"name\":\"Cash Bank Receipts\",\"description\":null,\"link\":\"finance\\/ar_receipts\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(43, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"42c5d26116ee453e9225b91d3c827d\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:43:53\",\"updated_by\":\"admin\",\"updated_date\":\"2023-07-18 11:52:31\",\"deleted\":\"0\",\"menus_id\":\"20230114000005\",\"number\":null,\"name\":\"AR Aging Schedules\",\"description\":null,\"link\":\"finance\\/ar_schedules\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(44, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230709000002\",\"created_by\":\"admin\",\"created_date\":\"2023-07-09 19:31:20\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230709000001\",\"number\":null,\"name\":\"Categories\",\"description\":null,\"link\":\"assets\\/categories\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(45, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230710000001\",\"created_by\":\"admin\",\"created_date\":\"2023-07-10 10:42:44\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230709000001\",\"number\":null,\"name\":\"Fixed Assets\",\"description\":null,\"link\":\"assets\\/fixeds\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(46, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230710000002\",\"created_by\":\"admin\",\"created_date\":\"2023-07-10 10:43:32\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230709000001\",\"number\":null,\"name\":\"Disposed Fixed Assets\",\"description\":null,\"link\":\"assets\\/disposeds\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(47, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230710000003\",\"created_by\":\"admin\",\"created_date\":\"2023-07-10 10:44:07\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230709000001\",\"number\":null,\"name\":\"Transfer Assets\",\"description\":null,\"link\":\"assets\\/transfers\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(48, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230710000004\",\"created_by\":\"admin\",\"created_date\":\"2023-07-10 10:45:12\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230709000001\",\"number\":null,\"name\":\"Report Assets\",\"description\":null,\"link\":\"assets\\/reports\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(49, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"865cff3b613848cebcc7cb9667fbb1\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:45:08\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"number\":null,\"name\":\"Pemasukan Pabean\",\"description\":null,\"link\":\"beacukai\\/pemasukan_pabean\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(50, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"93d9f074b9e142eaa1103045d07273\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:45:20\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"number\":null,\"name\":\"Pengeluaran Pabean\",\"description\":null,\"link\":\"beacukai\\/pengeluaran_pabean\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(51, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"0bf49f569bb04892bda649b91bf21d\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:46:14\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-14 12:16:06\",\"deleted\":\"0\",\"menus_id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"number\":null,\"name\":\"LPJ Barang Jadi\",\"description\":null,\"link\":\"beacukai\\/pertanggung_jawaban_barang_jadi\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(52, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"2d0a643865084c4ba9fbc7869370fa\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:45:54\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-14 12:15:46\",\"deleted\":\"0\",\"menus_id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"number\":null,\"name\":\"LPJ Bahan Penolong\",\"description\":null,\"link\":\"beacukai\\/pertanggung_jawaban_penolong\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(53, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"7097c7632da5440994c39f828678ab\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:46:32\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-14 12:16:24\",\"deleted\":\"0\",\"menus_id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"number\":null,\"name\":\"LPJ Barang Scrap\",\"description\":null,\"link\":\"beacukai\\/pertanggung_jawaban_barang_scrap\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(54, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"5214613be76e4e08ab01a260c8ef55\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:46:49\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-14 12:16:45\",\"deleted\":\"0\",\"menus_id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"number\":null,\"name\":\"LPJ  Mesin Dan Kantor\",\"description\":null,\"link\":\"beacukai\\/pertanggung_jawaban_mesin_kantor\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(55, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"e9d8310044d3481f81834d14f91bd7\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:21:33\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:42:19\",\"deleted\":\"0\",\"menus_id\":\"76e9ec30b2274d13b6fd78bd9771b0\",\"number\":null,\"name\":\"Items\",\"description\":null,\"link\":\"\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(56, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"687d6466b56c4d0f926dd655ae7ab9\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:47:04\",\"updated_by\":\"admin\",\"updated_date\":\"2022-12-27 02:21:48\",\"deleted\":\"0\",\"menus_id\":\"ae9dda6c8d9e4366a241528c7390ff\",\"number\":null,\"name\":\"Laporan Posisi WIP\",\"description\":null,\"link\":\"beacukai\\/posisi_wip\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(57, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230517000003\",\"created_by\":\"admin\",\"created_date\":\"2023-05-17 21:26:51\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"76e9ec30b2274d13b6fd78bd9771b0\",\"number\":null,\"name\":\"Master Data\",\"description\":null,\"link\":\"\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(58, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230515000001\",\"created_by\":\"admin\",\"created_date\":\"2023-05-15 21:35:15\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"8344f4bb673644908cf7f59e61f84d\",\"number\":null,\"name\":\"Master Data\",\"description\":null,\"link\":\"\",\"sort\":\"0\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(59, 'admin', '2023-08-03 17:21:28', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230114000001\",\"created_by\":\"admin\",\"created_date\":\"2023-01-14 11:44:54\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"8344f4bb673644908cf7f59e61f84d\",\"number\":null,\"name\":\"Account Payable\",\"description\":null,\"link\":\"\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(60, 'admin', '2023-08-03 17:21:29', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230114000005\",\"created_by\":\"admin\",\"created_date\":\"2023-01-14 11:56:20\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"8344f4bb673644908cf7f59e61f84d\",\"number\":null,\"name\":\"Account Receivable\",\"description\":null,\"link\":\"\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(61, 'admin', '2023-08-03 17:21:29', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230717000002\",\"created_by\":\"admin\",\"created_date\":\"2023-07-17 09:20:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-07-17 09:20:44\",\"deleted\":\"0\",\"menus_id\":\"8344f4bb673644908cf7f59e61f84d\",\"number\":null,\"name\":\"Report\",\"description\":null,\"link\":\"\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(62, 'admin', '2023-08-03 17:21:29', 0, '::1', 'Delete', 'menus', '{\"id\":\"90af5528e9a4458585b2d74bb73b05\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:22:38\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-24 11:44:25\",\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Specifications\",\"description\":null,\"link\":\"master\\/item_specifications\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(63, 'admin', '2023-08-03 17:21:29', 0, '::1', 'Delete', 'menus', '{\"id\":\"62309884bfe74e5696f10f32243d97\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:22:15\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Item Cards\",\"description\":null,\"link\":\"master\\/items\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(64, 'admin', '2023-08-03 17:21:38', 0, '::1', 'Delete', 'menus', '{\"id\":\"1bae0b2618ae47b5b7884c40519b61\",\"created_by\":\"admin\",\"created_date\":\"2022-10-19 22:47:40\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"number\":null,\"name\":\"Locations\",\"description\":null,\"link\":\"warehouse\\/fg_locations\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(65, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"1f0d78d3e80a457bb0914831b94c7b\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:42:52\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e7f55074baa54065bca03b77c62574\",\"number\":null,\"name\":\"Delivery Orders\",\"description\":null,\"link\":\"shipping\\/delivery_orders\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(66, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221204000003\",\"created_by\":\"admin\",\"created_date\":\"2022-12-04 22:43:14\",\"updated_by\":\"admin\",\"updated_date\":\"2023-04-03 13:26:37\",\"deleted\":\"0\",\"menus_id\":\"20221204000002\",\"number\":null,\"name\":\"Generate MRP\",\"description\":null,\"link\":\"\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(67, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230113000002\",\"created_by\":\"admin\",\"created_date\":\"2023-01-13 21:01:05\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"number\":null,\"name\":\"Forecast\",\"description\":null,\"link\":\"\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(68, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230515000002\",\"created_by\":\"admin\",\"created_date\":\"2023-05-15 21:35:49\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Account Groups\",\"description\":null,\"link\":\"finance\\/account_groups\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(69, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"2c33a59b7b6c41be80df4129f90b71\",\"created_by\":\"admin\",\"created_date\":\"2022-10-26 11:41:41\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-15 10:14:43\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Warehouse **\",\"description\":null,\"link\":\"\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(70, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"7d7f997ca58f425c8783ff7a7f994c\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:04:20\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-13 22:44:34\",\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Product Familys\",\"description\":null,\"link\":\"master\\/item_familys\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(71, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"1d9918c716514cdfac598ba366aaef\",\"created_by\":\"admin\",\"created_date\":\"2022-10-24 11:48:12\",\"updated_by\":\"admin\",\"updated_date\":\"2023-05-17 21:28:15\",\"deleted\":\"0\",\"menus_id\":\"20230517000003\",\"number\":null,\"name\":\"Job Order Parameter\",\"description\":null,\"link\":\"engineering\\/job_orders\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(72, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221204000004\",\"created_by\":\"admin\",\"created_date\":\"2022-12-04 22:44:48\",\"updated_by\":\"admin\",\"updated_date\":\"2023-04-03 13:26:45\",\"deleted\":\"0\",\"menus_id\":\"20221204000002\",\"number\":null,\"name\":\"MRP Result\",\"description\":null,\"link\":\"\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(73, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230717000003\",\"created_by\":\"admin\",\"created_date\":\"2023-07-17 09:21:44\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230717000002\",\"number\":null,\"name\":\"Foreign Currrency Revaluation\",\"description\":null,\"link\":\"finance\\/foreign_currencies\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(74, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"b3caa83cf1534926a614edc17ed055\",\"created_by\":\"admin\",\"created_date\":\"2022-10-12 04:55:22\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"c10a8ffd0165455989b58f9090798c\",\"number\":null,\"name\":\"Purchase Requests\",\"description\":null,\"link\":\"purchase\\/purchase_requests\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(75, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230111000002\",\"created_by\":\"admin\",\"created_date\":\"2023-01-11 06:57:14\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-12 22:52:45\",\"deleted\":\"0\",\"menus_id\":\"e7f55074baa54065bca03b77c62574\",\"number\":null,\"name\":\"Shipping Order\",\"description\":null,\"link\":\"shipping\\/shipping_orders\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(76, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"8001792212e640eb8e67b13a16a26d\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:02:48\",\"updated_by\":\"admin\",\"updated_date\":\"2023-05-17 21:28:26\",\"deleted\":\"0\",\"menus_id\":\"20230517000003\",\"number\":null,\"name\":\"Bill Of Materials\",\"description\":null,\"link\":\"master\\/bom\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(77, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230515000003\",\"created_by\":\"admin\",\"created_date\":\"2023-05-15 21:35:56\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Account Group Details\",\"description\":null,\"link\":\"finance\\/account_group_details\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(78, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"30baffda74154b428e3effd069d09e\",\"created_by\":\"admin\",\"created_date\":\"2022-11-14 21:19:04\",\"updated_by\":\"admin\",\"updated_date\":\"2023-02-06 23:06:21\",\"deleted\":\"0\",\"menus_id\":\"4f841c89e8274fd88335b2346d1ee2\",\"number\":null,\"name\":\"Item NG Transaction\",\"description\":null,\"link\":\"production\\/item_ng\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(79, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221205000003\",\"created_by\":\"admin\",\"created_date\":\"2022-12-05 08:39:27\",\"updated_by\":\"admin\",\"updated_date\":\"2023-02-14 23:26:29\",\"deleted\":\"0\",\"menus_id\":\"4f841c89e8274fd88335b2346d1ee2\",\"number\":null,\"name\":\"Upload STO WIP\",\"description\":null,\"link\":\"production\\/sto_wip\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(80, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"48be2218f3134d0d900c14b875cd4b\",\"created_by\":\"admin\",\"created_date\":\"2022-10-19 22:47:47\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"number\":null,\"name\":\"Location Items\",\"description\":null,\"link\":\"warehouse\\/fg_location_items\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(81, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"5666bae5c6254bab977fcd4fd7129e\",\"created_by\":\"admin\",\"created_date\":\"2022-10-19 22:48:06\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:42:06\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Locations\",\"description\":null,\"link\":\"warehouse\\/rm_locations\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(82, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"8f2e2ea5fa0649619863e4a30f1cbc\",\"created_by\":\"admin\",\"created_date\":\"2022-10-12 04:55:32\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"c10a8ffd0165455989b58f9090798c\",\"number\":null,\"name\":\"Purchase Orders\",\"description\":null,\"link\":\"purchase\\/purchase_orders\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(83, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"e780677ba5764a128f4ae77e3b720e\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 21:37:33\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-13 21:01:17\",\"deleted\":\"0\",\"menus_id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"number\":null,\"name\":\"Sales Orders\",\"description\":null,\"link\":\"planning\\/sales_orders\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(84, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"ec436420c2734ec3a864eeedbe53c6\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:25:13\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:21:27\",\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Categories\",\"description\":null,\"link\":\"master\\/item_categories\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(85, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"0e4bb0451f1d4a64a4f7be6be13708\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 21:38:10\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-13 21:01:33\",\"deleted\":\"0\",\"menus_id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"number\":null,\"name\":\"Production Schedules\",\"description\":null,\"link\":\"planning\\/production_schedules\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(86, 'admin', '2023-08-03 17:21:39', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230515000004\",\"created_by\":\"admin\",\"created_date\":\"2023-05-15 21:36:08\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Account Banks\",\"description\":null,\"link\":\"finance\\/account_banks\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(87, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221204000005\",\"created_by\":\"admin\",\"created_date\":\"2022-12-04 22:46:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-04-03 13:26:51\",\"deleted\":\"0\",\"menus_id\":\"20221204000002\",\"number\":null,\"name\":\"Convert MRP to PR\",\"description\":null,\"link\":\"\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(88, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230517000004\",\"created_by\":\"admin\",\"created_date\":\"2023-05-17 21:27:34\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230517000003\",\"number\":null,\"name\":\"Main Process\",\"description\":null,\"link\":\"engineering\\/main_process\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(89, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230601000001\",\"created_by\":\"admin\",\"created_date\":\"2023-06-01 23:21:01\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"c10a8ffd0165455989b58f9090798c\",\"number\":null,\"name\":\"Purchase Order Misc\",\"description\":null,\"link\":\"purchase\\/purchase_order_others\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(90, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"4436260df6074dfb8676de448a1a89\",\"created_by\":\"admin\",\"created_date\":\"2022-10-26 11:23:34\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-14 20:56:50\",\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Transaction Type\",\"description\":null,\"link\":\"master\\/transaction_types\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(91, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"50db640c2a0f47ab8958d00958d1b7\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:43:01\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-11 07:01:11\",\"deleted\":\"0\",\"menus_id\":\"e7f55074baa54065bca03b77c62574\",\"number\":null,\"name\":\"Delivery Notes\",\"description\":null,\"link\":\"shipping\\/delivery_notes\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(92, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"670141b0c0ac4f61bba46f0c85acd6\",\"created_by\":\"admin\",\"created_date\":\"2022-10-12 04:55:52\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"c10a8ffd0165455989b58f9090798c\",\"number\":null,\"name\":\"Purchase Order Receipts\",\"description\":null,\"link\":\"purchase\\/purchase_order_receipts\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(93, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"23ce7975fd3a45abaf71ef4a805056\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:38:59\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-19 22:48:18\",\"deleted\":\"0\",\"menus_id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"number\":null,\"name\":\"Wip Receipts\",\"description\":null,\"link\":\"warehouse\\/wip_receipts\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(94, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"955ae49b75df4f678ec2324489ae3d\",\"created_by\":\"admin\",\"created_date\":\"2022-10-19 22:47:58\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:42:14\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Location Items\",\"description\":null,\"link\":\"warehouse\\/rm_location_items\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(95, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"612df21b8cd34fb5869011b0e92f81\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:22:46\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Makers\",\"description\":null,\"link\":\"master\\/item_makers\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(96, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230102000001\",\"created_by\":\"admin\",\"created_date\":\"2023-01-02 13:30:11\",\"updated_by\":\"admin\",\"updated_date\":\"2023-02-06 10:39:04\",\"deleted\":\"0\",\"menus_id\":\"4f841c89e8274fd88335b2346d1ee2\",\"number\":null,\"name\":\"Final Check Sheets\",\"description\":null,\"link\":\"production\\/checksheets\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(97, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230108000001\",\"created_by\":\"admin\",\"created_date\":\"2023-01-08 12:23:19\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-12 20:37:06\",\"deleted\":\"0\",\"menus_id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"number\":null,\"name\":\"Scan Receiving FG\",\"description\":null,\"link\":\"warehouse\\/item_receipts_fg\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(98, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230113000001\",\"created_by\":\"admin\",\"created_date\":\"2023-01-13 01:07:37\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e7f55074baa54065bca03b77c62574\",\"number\":null,\"name\":\"Packing Lists\",\"description\":null,\"link\":\"shipping\\/packing_lists\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(99, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230515000005\",\"created_by\":\"admin\",\"created_date\":\"2023-05-15 21:36:21\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Chart of Account\",\"description\":null,\"link\":\"finance\\/account_coa\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(100, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230517000005\",\"created_by\":\"admin\",\"created_date\":\"2023-05-17 21:27:39\",\"updated_by\":\"admin\",\"updated_date\":\"2023-05-17 21:27:47\",\"deleted\":\"0\",\"menus_id\":\"20230517000003\",\"number\":null,\"name\":\"Main Process Subs\",\"description\":null,\"link\":\"engineering\\/main_process_subs\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(101, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"3ca074598cf94973b83bb1facb1190\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:22:58\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Types\",\"description\":null,\"link\":\"master\\/item_types\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(102, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230215000001\",\"created_by\":\"admin\",\"created_date\":\"2023-02-15 14:33:49\",\"updated_by\":\"admin\",\"updated_date\":\"2023-03-02 23:46:01\",\"deleted\":\"0\",\"menus_id\":\"c10a8ffd0165455989b58f9090798c\",\"number\":null,\"name\":\"Purchase Return\",\"description\":null,\"link\":\"purchase\\/purchase_returns\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(103, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"50f02a4e5e0845f4aab2b43bf63047\",\"created_by\":\"admin\",\"created_date\":\"2022-10-26 11:48:34\",\"updated_by\":\"admin\",\"updated_date\":\"2022-12-04 20:53:12\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Stock Transfer **\",\"description\":null,\"link\":\"\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(104, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"7b3fd71d12d04dfdbfe4323903def5\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 21:38:23\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-13 21:01:50\",\"deleted\":\"0\",\"menus_id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"number\":null,\"name\":\"Supply Sheets\",\"description\":null,\"link\":\"planning\\/supply_sheets\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(105, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"255bcee4fb4c45d8aa938b8279893c\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:05:21\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-07 18:07:44\",\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Customers\",\"description\":null,\"link\":\"master\\/customers\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(106, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"834ee81f71d14a1ba35708d85f7526\",\"created_by\":\"admin\",\"created_date\":\"2022-10-26 11:53:43\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:57:10\",\"deleted\":\"0\",\"menus_id\":\"c10a8ffd0165455989b58f9090798c\",\"number\":null,\"name\":\"Report \",\"description\":null,\"link\":\"\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(107, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"39894a92c9e9456997bb6af3b21bd3\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:23:06\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Sizes\",\"description\":null,\"link\":\"master\\/item_sizes\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(108, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230517000001\",\"created_by\":\"admin\",\"created_date\":\"2023-05-17 00:14:42\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Balance Supplier\",\"description\":null,\"link\":\"finance\\/balance_suppliers\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(109, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"5ad1d62d08a3408d9ab3a84c105d15\",\"created_by\":\"admin\",\"created_date\":\"2022-10-13 16:14:00\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:48:52\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Issued Materials\",\"description\":null,\"link\":\"warehouse\\/issued_materials\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(110, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"a98a56d8e07944d4a5a3d628358f03\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:06:19\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-07 18:07:49\",\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Customer Items\",\"description\":null,\"link\":\"master\\/customer_items\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}');
INSERT INTO `logs` (`id`, `created_by`, `created_date`, `deleted`, `ip_address`, `action`, `menu`, `description`) VALUES
(111, 'admin', '2023-08-03 17:21:40', 0, '::1', 'Delete', 'menus', '{\"id\":\"c5e3a7bb003a42309c42907778173d\",\"created_by\":\"admin\",\"created_date\":\"2022-10-13 15:38:32\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-13 21:02:09\",\"deleted\":\"0\",\"menus_id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"number\":null,\"name\":\"Non Supply Sheet\",\"description\":null,\"link\":\"production\\/supply_materials\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(112, 'admin', '2023-08-03 17:21:41', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230518000001\",\"created_by\":\"admin\",\"created_date\":\"2023-05-18 23:37:01\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230517000003\",\"number\":null,\"name\":\"Unit Man Hour\",\"description\":null,\"link\":\"engineering\\/umh\",\"sort\":\"5\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(113, 'admin', '2023-08-03 17:21:53', 0, '::1', 'Delete', 'menus', '{\"id\":\"fd04621539994742b73c6f4cbfa63e\",\"created_by\":\"admin\",\"created_date\":\"2022-10-26 11:54:57\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-05 23:22:17\",\"deleted\":\"0\",\"menus_id\":\"834ee81f71d14a1ba35708d85f7526\",\"number\":null,\"name\":\"Outstanding PO\",\"description\":null,\"link\":\"purchase\\/report_outstanding_po\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(114, 'admin', '2023-08-03 17:21:53', 0, '::1', 'Delete', 'menus', '{\"id\":\"2a4389152cc646148f199267b768d0\",\"created_by\":\"admin\",\"created_date\":\"2022-11-04 21:06:23\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-05 23:22:26\",\"deleted\":\"0\",\"menus_id\":\"834ee81f71d14a1ba35708d85f7526\",\"number\":null,\"name\":\"Outstanding PR\",\"description\":null,\"link\":\"purchase\\/report_outstanding_pr\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(115, 'admin', '2023-08-03 17:21:53', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221130000002\",\"created_by\":\"admin\",\"created_date\":\"2022-11-30 01:01:57\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"WIP Balances\",\"description\":null,\"link\":\"warehouse\\/wip_balances\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(116, 'admin', '2023-08-03 17:21:53', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221205000002\",\"created_by\":\"admin\",\"created_date\":\"2022-12-05 08:37:45\",\"updated_by\":\"admin\",\"updated_date\":\"2023-02-14 23:27:15\",\"deleted\":\"0\",\"menus_id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"number\":null,\"name\":\"Upload STO FG\",\"description\":null,\"link\":\"warehouse\\/sto_fg\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(117, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"7e92720f68204d2b948412828a116b\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:38:34\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:49:04\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Item Receipts\",\"description\":null,\"link\":\"warehouse\\/item_receipts\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(118, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"a5d9db23534f44fa9d58a7f996ce2a\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:05:36\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Suppliers\",\"description\":null,\"link\":\"master\\/suppliers\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(119, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"3dd1a46ef2834b79a8da5080c496e4\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:23:26\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-07 17:47:28\",\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Colors\",\"description\":null,\"link\":\"master\\/item_colors\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(120, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230206000001\",\"created_by\":\"admin\",\"created_date\":\"2023-02-06 10:00:11\",\"updated_by\":\"admin\",\"updated_date\":\"2023-04-04 10:56:35\",\"deleted\":\"0\",\"menus_id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"number\":null,\"name\":\"Scrap Transaction\",\"description\":null,\"link\":\"production\\/scraps\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(121, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230802000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-02 16:00:51\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230517000003\",\"number\":null,\"name\":\"Process\",\"description\":null,\"link\":\"engineering\\/process\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(122, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"de6779722f6347d798b07350eee0ef\",\"created_by\":\"admin\",\"created_date\":\"2022-11-14 22:26:21\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-13 21:02:27\",\"deleted\":\"0\",\"menus_id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"number\":null,\"name\":\"Material Requesition\",\"description\":null,\"link\":\"planning\\/supply_requestions\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(123, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230517000002\",\"created_by\":\"admin\",\"created_date\":\"2023-05-17 00:14:50\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Balance Customer\",\"description\":null,\"link\":\"finance\\/balance_customers\",\"sort\":\"6\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(124, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"3f423b73a7b04367853767d7de4bc5\",\"created_by\":\"admin\",\"created_date\":\"2022-10-13 16:14:28\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-26 11:49:14\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Barcode Divides\",\"description\":null,\"link\":\"warehouse\\/barcode_divides\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(125, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230106000001\",\"created_by\":\"admin\",\"created_date\":\"2023-01-06 00:29:26\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-08 12:24:34\",\"deleted\":\"0\",\"menus_id\":\"da7ff9a6b87a4b77bf7d89ef1f0c62\",\"number\":null,\"name\":\"Report\",\"description\":null,\"link\":\"\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(126, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"a015669f743f49d18be7541f4e9e4f\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 17:23:39\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Options\",\"description\":null,\"link\":\"master\\/item_options\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(127, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230611000001\",\"created_by\":\"admin\",\"created_date\":\"2023-06-11 22:00:16\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Exchange Rates\",\"description\":null,\"link\":\"finance\\/exchange_rates\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(128, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"c98376ce677644eea31e91bcb49438\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:06:31\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-07 18:08:03\",\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Supplier Items\",\"description\":null,\"link\":\"master\\/supplier_items\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(129, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"f0546fc46bf54b9385492a0da1fbf2\",\"created_by\":\"admin\",\"created_date\":\"2022-11-04 20:50:31\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-13 21:02:42\",\"deleted\":\"0\",\"menus_id\":\"93d8c32f689c41399f7ccb5ce434c3\",\"number\":null,\"name\":\"Report\",\"description\":null,\"link\":\"\",\"sort\":\"7\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(130, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230710000005\",\"created_by\":\"admin\",\"created_date\":\"2023-07-10 22:14:47\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"20230515000001\",\"number\":null,\"name\":\"Serial Faktur Pajak\",\"description\":null,\"link\":\"finance\\/serial_faktur\",\"sort\":\"8\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(131, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"3562627a9d084609a7dea66faf8598\",\"created_by\":\"admin\",\"created_date\":\"2022-10-17 22:24:53\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-17 22:25:25\",\"deleted\":\"0\",\"menus_id\":\"e9d8310044d3481f81834d14f91bd7\",\"number\":null,\"name\":\"Products\",\"description\":null,\"link\":\"master\\/item_products\",\"sort\":\"8\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(132, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"d188047ab21d45979c64c990a91e23\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:04:43\",\"updated_by\":\"admin\",\"updated_date\":\"2022-10-07 18:20:27\",\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Currencies\",\"description\":null,\"link\":\"master\\/currencies\",\"sort\":\"8\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(133, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"e505e5e5d2564aa280b3d568c8dd6c\",\"created_by\":\"admin\",\"created_date\":\"2022-11-14 20:57:28\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-14 20:59:55\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Adjusment **\",\"description\":null,\"link\":\"\",\"sort\":\"8\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(134, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"3038d96df22048608bbbb8762bbe24\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:06:43\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Unit Of Measure\",\"description\":null,\"link\":\"master\\/uom\",\"sort\":\"9\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(135, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"3257e9a11a8543b8afd8e27c0a21d2\",\"created_by\":\"admin\",\"created_date\":\"2022-11-14 22:24:23\",\"updated_by\":\"admin\",\"updated_date\":\"2022-12-25 15:36:53\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Return Material\",\"description\":null,\"link\":\"warehouse\\/return_materials\",\"sort\":\"9\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(136, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"3c5e75290ca947eab000aa09269753\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:07:01\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Line Productions\",\"description\":null,\"link\":\"master\\/lines\",\"sort\":\"10\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(137, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221204000001\",\"created_by\":\"admin\",\"created_date\":\"2022-12-04 20:58:28\",\"updated_by\":\"admin\",\"updated_date\":\"2023-02-14 23:27:24\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Upload STO RM\",\"description\":null,\"link\":\"warehouse\\/sto_rm\",\"sort\":\"11\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(138, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"8a5ae39e16a54354bd0ea87c1ff963\",\"created_by\":\"admin\",\"created_date\":\"2022-10-07 18:07:11\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Plants\",\"description\":null,\"link\":\"master\\/plants\",\"sort\":\"11\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(139, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"ec2cdc9e44cb4ccba8f21e9dac5a9e\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 13:56:46\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"BC Kind\",\"description\":null,\"link\":\"master\\/bc_kind\",\"sort\":\"12\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(140, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"20221130000001\",\"created_by\":\"admin\",\"created_date\":\"2022-11-30 01:01:15\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"e0f3586f91ee4ab2952b6a8ad0f5db\",\"number\":null,\"name\":\"Conversions\",\"description\":null,\"link\":\"master\\/convertions\",\"sort\":\"13\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(141, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"5f52889797034e918456da3c3a7971\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:39:34\",\"updated_by\":\"admin\",\"updated_date\":\"2022-12-04 21:01:52\",\"deleted\":\"0\",\"menus_id\":\"9c04c7dfcf6c44cc9a0ec44b1c7691\",\"number\":null,\"name\":\"Reports\",\"description\":null,\"link\":\"\",\"sort\":\"14\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"closed\",\"status\":\"0\"}'),
(142, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"37f8809007bf4f16aa993f6c0548e9\",\"created_by\":\"admin\",\"created_date\":\"2022-11-04 21:26:14\",\"updated_by\":\"admin\",\"updated_date\":\"2023-01-06 23:41:46\",\"deleted\":\"0\",\"menus_id\":\"20230106000001\",\"number\":null,\"name\":\"Historical Transaction FG\",\"description\":null,\"link\":\"warehouse\\/report_history_transactions_fg\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(143, 'admin', '2023-08-03 17:21:54', 0, '::1', 'Delete', 'menus', '{\"id\":\"62482f126fe940939924a187d617ea\",\"created_by\":\"admin\",\"created_date\":\"2022-11-04 20:55:41\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-05 23:22:08\",\"deleted\":\"0\",\"menus_id\":\"f0546fc46bf54b9385492a0da1fbf2\",\"number\":null,\"name\":\"Outstanding SO\",\"description\":null,\"link\":\"planning\\/report_outstanding_so\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(144, 'admin', '2023-08-03 17:21:55', 0, '::1', 'Delete', 'menus', '{\"id\":\"b7f1f62e48c34c73ba97bef9e22ffb\",\"created_by\":\"admin\",\"created_date\":\"2022-11-12 23:20:20\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"f0546fc46bf54b9385492a0da1fbf2\",\"number\":null,\"name\":\"Outstanding WO\",\"description\":null,\"link\":\"planning\\/report_outstanding_wo\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(145, 'admin', '2023-08-03 17:21:55', 0, '::1', 'Delete', 'menus', '{\"id\":\"5cf9545d619a497ab20036d1c587c6\",\"created_by\":\"admin\",\"created_date\":\"2022-11-04 21:15:14\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-14 19:39:53\",\"deleted\":\"0\",\"menus_id\":\"5f52889797034e918456da3c3a7971\",\"number\":null,\"name\":\"Check Serial No\",\"description\":null,\"link\":\"warehouse\\/report_check_serialno\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(146, 'admin', '2023-08-03 17:21:55', 0, '::1', 'Delete', 'menus', '{\"id\":\"059428c1b43c4e8ca99d7314a7575b\",\"created_by\":\"admin\",\"created_date\":\"2022-10-16 12:40:26\",\"updated_by\":\"admin\",\"updated_date\":\"2022-11-22 23:52:16\",\"deleted\":\"0\",\"menus_id\":\"5f52889797034e918456da3c3a7971\",\"number\":null,\"name\":\"Historical Transactions\",\"description\":null,\"link\":\"warehouse\\/report_history_transactions\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(147, 'admin', '2023-08-03 17:22:49', 0, '::1', 'Create', 'menus', '{\"menus_id\":\"\",\"name\":\"Master Data\",\"link\":\"\",\"sort\":\"2\",\"state\":\"closed\",\"id\":\"20230803000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:22:49\"}'),
(148, 'admin', '2023-08-03 17:25:00', 0, '111.94.116.234', 'Create', 'menus', '{\"menus_id\":\"\",\"name\":\"Categories\",\"link\":\"master\\/categories\",\"sort\":\"1\",\"state\":\"\",\"id\":20230803000002,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:00\"}'),
(149, 'admin', '2023-08-03 17:25:23', 0, '111.94.116.234', 'Create', 'menus', '{\"menus_id\":\"\",\"name\":\"Product Families\",\"link\":\"master\\/item_familys\",\"sort\":\"2\",\"state\":\"\",\"id\":20230803000003,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:23\"}'),
(150, 'admin', '2023-08-03 17:25:31', 0, '111.94.116.234', 'Create', 'menus', '{\"menus_id\":\"\",\"name\":\"Customers\",\"link\":\"master\\/customers\",\"sort\":\"3\",\"state\":\"\",\"id\":20230803000004,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:31\"}'),
(151, 'admin', '2023-08-03 17:25:38', 0, '111.94.116.234', 'Create', 'menus', '{\"menus_id\":\"\",\"name\":\"Suppliers\",\"link\":\"master\\/suppliers\",\"sort\":\"4\",\"state\":\"\",\"id\":20230803000005,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:38\"}'),
(152, 'admin', '2023-08-03 17:25:49', 0, '111.94.116.234', 'Update Before', 'menus', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:00\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Categories\",\"description\":null,\"link\":\"master\\/categories\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(153, 'admin', '2023-08-03 17:25:49', 0, '111.94.116.234', 'Update New', 'menus', '{\"menus_id\":\"20230803000001\",\"name\":\"Categories\",\"link\":\"master\\/categories\",\"sort\":\"1\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:25:49\"}'),
(154, 'admin', '2023-08-03 17:25:54', 0, '111.94.116.234', 'Update Before', 'menus', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:23\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Product Families\",\"description\":null,\"link\":\"master\\/item_familys\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(155, 'admin', '2023-08-03 17:25:54', 0, '111.94.116.234', 'Update New', 'menus', '{\"menus_id\":\"20230803000001\",\"name\":\"Product Families\",\"link\":\"master\\/item_familys\",\"sort\":\"2\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:25:54\"}'),
(156, 'admin', '2023-08-03 17:25:59', 0, '111.94.116.234', 'Update Before', 'menus', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:31\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Customers\",\"description\":null,\"link\":\"master\\/customers\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(157, 'admin', '2023-08-03 17:25:59', 0, '111.94.116.234', 'Update New', 'menus', '{\"menus_id\":\"20230803000001\",\"name\":\"Customers\",\"link\":\"master\\/customers\",\"sort\":\"3\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:25:59\"}'),
(158, 'admin', '2023-08-03 17:26:05', 0, '111.94.116.234', 'Update Before', 'menus', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:38\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"menus_id\":\"\",\"number\":null,\"name\":\"Suppliers\",\"description\":null,\"link\":\"master\\/suppliers\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(159, 'admin', '2023-08-03 17:26:05', 0, '111.94.116.234', 'Update New', 'menus', '{\"menus_id\":\"20230803000001\",\"name\":\"Suppliers\",\"link\":\"master\\/suppliers\",\"sort\":\"4\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:26:05\"}'),
(160, 'admin', '2023-08-03 17:28:54', 0, '111.94.116.234', 'Create', 'setting_menus', '{\"menus_id\":\"20230803000001\",\"m_view\":\"on\",\"id\":\"20230803000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:28:54\"}'),
(161, 'admin', '2023-08-03 17:29:01', 0, '111.94.116.234', 'Create', 'setting_menus', '{\"menus_id\":\"20230803000002\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20230803000002,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:29:01\"}'),
(162, 'admin', '2023-08-03 17:29:05', 0, '111.94.116.234', 'Create', 'setting_menus', '{\"menus_id\":\"20230803000003\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20230803000003,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:29:05\"}'),
(163, 'admin', '2023-08-03 17:29:08', 0, '111.94.116.234', 'Create', 'setting_menus', '{\"menus_id\":\"20230803000004\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_upload\":\"on\",\"m_download\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20230803000004,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:29:08\"}'),
(164, 'admin', '2023-08-03 17:29:11', 0, '111.94.116.234', 'Create', 'setting_menus', '{\"menus_id\":\"20230803000005\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_upload\":\"on\",\"m_download\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20230803000005,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:29:11\"}'),
(165, 'admin', '2023-08-03 10:29:16', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230803000001\",\"id\":\"20230803000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\"}'),
(166, 'admin', '2023-08-03 10:29:16', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230803000002\",\"id\":20230803000002,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\"}'),
(167, 'admin', '2023-08-03 10:29:16', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230803000003\",\"id\":20230803000003,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\"}'),
(168, 'admin', '2023-08-03 10:29:16', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"id\":20230803000004,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\"}'),
(169, 'admin', '2023-08-03 10:29:16', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"id\":20230803000005,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\"}'),
(170, 'admin', '2023-08-03 10:29:18', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000001\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(171, 'admin', '2023-08-03 10:29:18', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:18\"}'),
(172, 'admin', '2023-08-03 10:29:19', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000002\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(173, 'admin', '2023-08-03 10:29:19', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:19\"}'),
(174, 'admin', '2023-08-03 10:29:19', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000003\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(175, 'admin', '2023-08-03 10:29:19', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:19\"}'),
(176, 'admin', '2023-08-03 10:29:19', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(177, 'admin', '2023-08-03 10:29:19', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:19\"}'),
(178, 'admin', '2023-08-03 10:29:20', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(179, 'admin', '2023-08-03 10:29:20', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:20\"}'),
(180, 'admin', '2023-08-03 10:29:21', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:19\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(181, 'admin', '2023-08-03 10:29:21', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:21\"}'),
(182, 'admin', '2023-08-03 10:29:22', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:19\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(183, 'admin', '2023-08-03 10:29:22', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:22\"}'),
(184, 'admin', '2023-08-03 10:29:22', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:19\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(185, 'admin', '2023-08-03 10:29:22', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:22\"}'),
(186, 'admin', '2023-08-03 10:29:23', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:20\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(187, 'admin', '2023-08-03 10:29:23', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:23\"}'),
(188, 'admin', '2023-08-03 10:29:24', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:21\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(189, 'admin', '2023-08-03 10:29:24', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:24\"}'),
(190, 'admin', '2023-08-03 10:29:24', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:22\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(191, 'admin', '2023-08-03 10:29:24', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:24\"}'),
(192, 'admin', '2023-08-03 10:29:24', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:22\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(193, 'admin', '2023-08-03 10:29:24', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:24\"}'),
(194, 'admin', '2023-08-03 10:29:25', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:23\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(195, 'admin', '2023-08-03 10:29:25', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:25\"}'),
(196, 'admin', '2023-08-03 10:29:25', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:24\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(197, 'admin', '2023-08-03 10:29:25', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:25\"}'),
(198, 'admin', '2023-08-03 10:29:26', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:24\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(199, 'admin', '2023-08-03 10:29:26', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:26\"}'),
(200, 'admin', '2023-08-03 10:29:26', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:24\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(201, 'admin', '2023-08-03 10:29:26', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:26\"}'),
(202, 'admin', '2023-08-03 10:29:26', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:25\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(203, 'admin', '2023-08-03 10:29:26', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:26\"}'),
(204, 'admin', '2023-08-03 10:29:27', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:26\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(205, 'admin', '2023-08-03 10:29:27', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:27\"}'),
(206, 'admin', '2023-08-03 10:29:28', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:26\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(207, 'admin', '2023-08-03 10:29:28', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:28\"}'),
(208, 'admin', '2023-08-03 10:29:28', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:28\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(209, 'admin', '2023-08-03 10:29:28', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:28\"}'),
(210, 'admin', '2023-08-03 10:29:29', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:27\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(211, 'admin', '2023-08-03 10:29:29', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:29\"}'),
(212, 'admin', '2023-08-03 10:29:30', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:25\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(213, 'admin', '2023-08-03 10:29:30', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:30\"}'),
(214, 'admin', '2023-08-03 10:29:30', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:26\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(215, 'admin', '2023-08-03 10:29:30', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:30\"}'),
(216, 'admin', '2023-08-03 10:29:31', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:28\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(217, 'admin', '2023-08-03 10:29:31', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:31\"}'),
(218, 'admin', '2023-08-03 10:29:31', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:29\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(219, 'admin', '2023-08-03 10:29:31', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:31\"}'),
(220, 'admin', '2023-08-03 10:29:32', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:30\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(221, 'admin', '2023-08-03 10:29:32', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:32\"}'),
(222, 'admin', '2023-08-03 10:29:32', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:30\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(223, 'admin', '2023-08-03 10:29:32', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:32\"}'),
(224, 'admin', '2023-08-03 10:29:33', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:31\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(225, 'admin', '2023-08-03 10:29:33', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:33\"}'),
(226, 'admin', '2023-08-03 10:29:33', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:29:16\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:31\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(227, 'admin', '2023-08-03 10:29:33', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:29:33\"}'),
(228, 'admin', '2023-08-03 17:31:56', 0, '111.94.116.234', 'Create', 'users', '{\"number\":\"2\",\"name\":\"Nanda Ayu Insani\",\"username\":\"nanda\",\"password\":\"nanda@123#\",\"position\":\"\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\",\"email\":\"nana@gmail.com\",\"phone\":\"2319232\",\"actived\":\"0\",\"avatar\":null,\"id\":\"20230803000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:31:56\"}'),
(229, 'admin', '2023-08-03 17:32:10', 0, '111.94.116.234', 'Update Before', 'users', '{\"id\":\"20230803000001\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:31:56\",\"updated_by\":null,\"updated_date\":null,\"approved\":\"1\",\"approved_to\":\"admin\",\"approved_by\":\"admin\",\"approved_date\":null,\"deleted\":\"0\",\"number\":\"2\",\"name\":\"Nanda Ayu Insani\",\"description\":null,\"username\":\"nanda\",\"password\":\"nanda@123#\",\"email\":\"nana@gmail.com\",\"phone\":\"2319232\",\"position\":\"\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\\u25cf\",\"avatar\":null,\"actived\":\"0\",\"status\":\"0\"}'),
(230, 'admin', '2023-08-03 17:32:10', 0, '111.94.116.234', 'Update New', 'users', '{\"name\":\"Nanda Ayu Insani\",\"position\":\"Implementator\",\"email\":\"nana@gmail.com\",\"phone\":\"2319232\",\"actived\":\"0\",\"avatar\":null,\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:32:10\"}'),
(231, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"20230109000001\",\"id\":20230803000006,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(232, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"20230803000001\",\"id\":20230803000007,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(233, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"20230803000002\",\"id\":20230803000008,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(234, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"20230803000003\",\"id\":20230803000009,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}');
INSERT INTO `logs` (`id`, `created_by`, `created_date`, `deleted`, `ip_address`, `action`, `menu`, `description`) VALUES
(235, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"id\":20230803000010,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(236, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"id\":20230803000011,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(237, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"44964312f0264429978158ada88843\",\"id\":20230803000012,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(238, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"6ccd20c54d1d415189120ec5cc6c81\",\"id\":20230803000013,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(239, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"b679033b3256414b8f916c69f17674\",\"id\":20230803000014,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(240, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"c8f8362a5f6c432ab27d37213f15d4\",\"id\":20230803000015,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(241, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"id\":20230803000016,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(242, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"d13439e3f2324450a69b4e0e50159a\",\"id\":20230803000017,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(243, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"id\":20230803000018,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(244, 'admin', '2023-08-03 10:32:35', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"nanda\",\"menus_id\":\"e3c31e10b6c64e119b068ae4b73be6\",\"id\":20230803000019,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\"}'),
(245, 'admin', '2023-08-03 10:32:37', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000016\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"cf98f97766f6405590b26daa586e00\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(246, 'admin', '2023-08-03 10:32:37', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:37\"}'),
(247, 'admin', '2023-08-03 10:32:37', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000007\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000001\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(248, 'admin', '2023-08-03 10:32:37', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:37\"}'),
(249, 'admin', '2023-08-03 10:32:41', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000012\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"44964312f0264429978158ada88843\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(250, 'admin', '2023-08-03 10:32:41', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:41\"}'),
(251, 'admin', '2023-08-03 10:32:42', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000012\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:41\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"44964312f0264429978158ada88843\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(252, 'admin', '2023-08-03 10:32:42', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:42\"}'),
(253, 'admin', '2023-08-03 10:32:42', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000012\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:42\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"44964312f0264429978158ada88843\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(254, 'admin', '2023-08-03 10:32:42', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:42\"}'),
(255, 'admin', '2023-08-03 10:32:43', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000012\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:42\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"44964312f0264429978158ada88843\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(256, 'admin', '2023-08-03 10:32:43', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:43\"}'),
(257, 'admin', '2023-08-03 10:32:43', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000012\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:43\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"44964312f0264429978158ada88843\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(258, 'admin', '2023-08-03 10:32:43', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:43\"}'),
(259, 'admin', '2023-08-03 10:32:44', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000012\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:43\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"44964312f0264429978158ada88843\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(260, 'admin', '2023-08-03 10:32:44', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:44\"}'),
(261, 'admin', '2023-08-03 10:32:46', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000015\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"c8f8362a5f6c432ab27d37213f15d4\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(262, 'admin', '2023-08-03 10:32:46', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:46\"}'),
(263, 'admin', '2023-08-03 10:32:49', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(264, 'admin', '2023-08-03 10:32:49', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:49\"}'),
(265, 'admin', '2023-08-03 10:32:50', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:49\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(266, 'admin', '2023-08-03 10:32:50', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:50\"}'),
(267, 'admin', '2023-08-03 10:32:50', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:50\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(268, 'admin', '2023-08-03 10:32:50', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:50\"}'),
(269, 'admin', '2023-08-03 10:32:51', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000018\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:50\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"de3f6855009e49deb7fd2fdd0f3b3d\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(270, 'admin', '2023-08-03 10:32:51', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:51\"}'),
(271, 'admin', '2023-08-03 10:32:52', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000008\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000002\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(272, 'admin', '2023-08-03 10:32:52', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:52\"}'),
(273, 'admin', '2023-08-03 10:32:52', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000009\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000003\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(274, 'admin', '2023-08-03 10:32:52', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:52\"}'),
(275, 'admin', '2023-08-03 10:32:53', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(276, 'admin', '2023-08-03 10:32:53', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:53\"}'),
(277, 'admin', '2023-08-03 10:32:53', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(278, 'admin', '2023-08-03 10:32:53', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:53\"}'),
(279, 'admin', '2023-08-03 10:32:54', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000008\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:52\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(280, 'admin', '2023-08-03 10:32:54', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:54\"}'),
(281, 'admin', '2023-08-03 10:32:54', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000009\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:52\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(282, 'admin', '2023-08-03 10:32:54', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:54\"}'),
(283, 'admin', '2023-08-03 10:32:55', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:53\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(284, 'admin', '2023-08-03 10:32:55', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:55\"}'),
(285, 'admin', '2023-08-03 10:32:55', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:53\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(286, 'admin', '2023-08-03 10:32:55', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:55\"}'),
(287, 'admin', '2023-08-03 10:32:56', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000008\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:54\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(288, 'admin', '2023-08-03 10:32:56', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:56\"}'),
(289, 'admin', '2023-08-03 10:32:56', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000009\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:54\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(290, 'admin', '2023-08-03 10:32:56', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:56\"}'),
(291, 'admin', '2023-08-03 10:32:57', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:55\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(292, 'admin', '2023-08-03 10:32:57', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:57\"}'),
(293, 'admin', '2023-08-03 10:32:57', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:55\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(294, 'admin', '2023-08-03 10:32:57', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:57\"}'),
(295, 'admin', '2023-08-03 10:32:58', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000008\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:56\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(296, 'admin', '2023-08-03 10:32:58', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:58\"}'),
(297, 'admin', '2023-08-03 10:32:58', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000009\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:56\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(298, 'admin', '2023-08-03 10:32:58', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:58\"}'),
(299, 'admin', '2023-08-03 10:32:59', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:57\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(300, 'admin', '2023-08-03 10:32:59', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:59\"}'),
(301, 'admin', '2023-08-03 10:33:01', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:59\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(302, 'admin', '2023-08-03 10:33:01', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:01\"}'),
(303, 'admin', '2023-08-03 10:33:02', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:57\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(304, 'admin', '2023-08-03 10:33:02', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:02\"}'),
(305, 'admin', '2023-08-03 10:33:03', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:02\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(306, 'admin', '2023-08-03 10:33:03', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:03\"}'),
(307, 'admin', '2023-08-03 10:33:04', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:03\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(308, 'admin', '2023-08-03 10:33:04', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:04\"}'),
(309, 'admin', '2023-08-03 10:33:04', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000008\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:58\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(310, 'admin', '2023-08-03 10:33:04', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:04\"}'),
(311, 'admin', '2023-08-03 10:33:05', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000009\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:32:58\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(312, 'admin', '2023-08-03 10:33:05', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:05\"}'),
(313, 'admin', '2023-08-03 10:33:05', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:01\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(314, 'admin', '2023-08-03 10:33:05', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:05\"}'),
(315, 'admin', '2023-08-03 10:33:05', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:04\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(316, 'admin', '2023-08-03 10:33:05', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:05\"}'),
(317, 'admin', '2023-08-03 10:33:06', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:05\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(318, 'admin', '2023-08-03 10:33:06', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:06\"}'),
(319, 'admin', '2023-08-03 10:33:07', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000008\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:04\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000002\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(320, 'admin', '2023-08-03 10:33:07', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:07\"}'),
(321, 'admin', '2023-08-03 10:33:07', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000009\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:05\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000003\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(322, 'admin', '2023-08-03 10:33:07', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:07\"}'),
(323, 'admin', '2023-08-03 10:33:07', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000010\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:06\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000004\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(324, 'admin', '2023-08-03 10:33:07', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:07\"}'),
(325, 'admin', '2023-08-03 10:33:08', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000011\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:32:35\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:05\",\"deleted\":\"0\",\"users_id\":\"nanda\",\"menus_id\":\"20230803000005\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(326, 'admin', '2023-08-03 10:33:08', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"1\",\"v_download\":\"1\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:33:08\"}'),
(327, 'admin', '2023-08-03 17:41:47', 0, '111.94.116.234', 'Update Before', 'menus', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:25:49\",\"deleted\":\"0\",\"menus_id\":\"20230803000001\",\"number\":null,\"name\":\"Categories\",\"description\":null,\"link\":\"master\\/categories\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(328, 'admin', '2023-08-03 17:41:47', 0, '111.94.116.234', 'Update New', 'menus', '{\"menus_id\":\"20230803000001\",\"name\":\"Categories\",\"link\":\"master\\/item_categories\",\"sort\":\"1\",\"state\":\"\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:41:47\"}'),
(329, 'admin', '2023-08-03 17:50:05', 0, '111.94.116.234', 'Create', 'menus', '{\"menus_id\":\"20230803000001\",\"name\":\"Currencies\",\"link\":\"master\\/currencies\",\"sort\":\"5\",\"state\":\"\",\"id\":20230803000006,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:50:05\"}'),
(330, 'admin', '2023-08-03 17:50:29', 0, '111.94.116.234', 'Create', 'setting_menus', '{\"menus_id\":\"20230803000006\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20230803000006,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:50:29\"}'),
(331, 'admin', '2023-08-03 17:52:41', 0, '111.94.116.234', 'Create', 'menus', '{\"menus_id\":\"20230803000001\",\"name\":\"Unit of Meaasure\",\"link\":\"master\\/uom\",\"sort\":\"6\",\"state\":\"\",\"id\":20230803000007,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:52:41\"}'),
(332, 'admin', '2023-08-03 17:52:55', 0, '111.94.116.234', 'Create', 'setting_menus', '{\"menus_id\":\"20230803000007\",\"m_view\":\"on\",\"m_add\":\"on\",\"m_edit\":\"on\",\"m_delete\":\"on\",\"m_print\":\"on\",\"m_excel\":\"on\",\"id\":20230803000007,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:52:55\"}'),
(333, 'admin', '2023-08-03 10:53:00', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230803000006\",\"id\":20230803000020,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\"}'),
(334, 'admin', '2023-08-03 10:53:00', 0, '111.94.116.234', 'Create', 'setting_users', '{\"users_id\":\"admin\",\"menus_id\":\"20230803000007\",\"id\":20230803000021,\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\"}'),
(335, 'admin', '2023-08-03 10:53:03', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000020\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000006\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(336, 'admin', '2023-08-03 10:53:03', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:03\"}'),
(337, 'admin', '2023-08-03 10:53:03', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000021\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":null,\"updated_date\":null,\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000007\",\"v_view\":\"0\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(338, 'admin', '2023-08-03 10:53:03', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:03\"}'),
(339, 'admin', '2023-08-03 10:53:03', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000020\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:03\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000006\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(340, 'admin', '2023-08-03 10:53:03', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:03\"}'),
(341, 'admin', '2023-08-03 10:53:04', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000021\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:03\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000007\",\"v_view\":\"1\",\"v_add\":\"0\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(342, 'admin', '2023-08-03 10:53:04', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:04\"}'),
(343, 'admin', '2023-08-03 10:53:04', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000020\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:03\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000006\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(344, 'admin', '2023-08-03 10:53:04', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:04\"}'),
(345, 'admin', '2023-08-03 10:53:05', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000021\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:04\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000007\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"0\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(346, 'admin', '2023-08-03 10:53:05', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:05\"}'),
(347, 'admin', '2023-08-03 10:53:06', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000020\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:04\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000006\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(348, 'admin', '2023-08-03 10:53:06', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:06\"}'),
(349, 'admin', '2023-08-03 10:53:07', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000021\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:05\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000007\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"0\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(350, 'admin', '2023-08-03 10:53:07', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:07\"}'),
(351, 'admin', '2023-08-03 10:53:08', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000020\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:06\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000006\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(352, 'admin', '2023-08-03 10:53:08', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:08\"}'),
(353, 'admin', '2023-08-03 10:53:08', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000021\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:07\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000007\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"0\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(354, 'admin', '2023-08-03 10:53:08', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:08\"}'),
(355, 'admin', '2023-08-03 10:53:09', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000020\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:08\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000006\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(356, 'admin', '2023-08-03 10:53:09', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:09\"}'),
(357, 'admin', '2023-08-03 10:53:10', 0, '111.94.116.234', 'Update Before', 'setting_users', '{\"id\":\"20230803000021\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 10:53:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:08\",\"deleted\":\"0\",\"users_id\":\"admin\",\"menus_id\":\"20230803000007\",\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"0\",\"status\":\"0\"}'),
(358, 'admin', '2023-08-03 10:53:10', 0, '111.94.116.234', 'Update New', 'setting_users', '{\"v_view\":\"1\",\"v_add\":\"1\",\"v_edit\":\"1\",\"v_delete\":\"1\",\"v_upload\":\"0\",\"v_download\":\"0\",\"v_print\":\"1\",\"v_excel\":\"1\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 10:53:10\"}'),
(359, 'admin', '2023-08-03 18:17:14', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230803000002\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:00\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:41:47\",\"deleted\":\"0\",\"menus_id\":\"20230803000001\",\"number\":null,\"name\":\"Categories\",\"description\":null,\"link\":\"master\\/item_categories\",\"sort\":\"1\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(360, 'admin', '2023-08-03 18:17:14', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230803000004\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:31\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:25:59\",\"deleted\":\"0\",\"menus_id\":\"20230803000001\",\"number\":null,\"name\":\"Customers\",\"description\":null,\"link\":\"master\\/customers\",\"sort\":\"3\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(361, 'admin', '2023-08-03 18:17:14', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230803000003\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:23\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:25:54\",\"deleted\":\"0\",\"menus_id\":\"20230803000001\",\"number\":null,\"name\":\"Product Families\",\"description\":null,\"link\":\"master\\/item_familys\",\"sort\":\"2\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}'),
(362, 'admin', '2023-08-03 18:17:14', 0, '::1', 'Delete', 'menus', '{\"id\":\"20230803000005\",\"created_by\":\"admin\",\"created_date\":\"2023-08-03 17:25:38\",\"updated_by\":\"admin\",\"updated_date\":\"2023-08-03 17:26:05\",\"deleted\":\"0\",\"menus_id\":\"20230803000001\",\"number\":null,\"name\":\"Suppliers\",\"description\":null,\"link\":\"master\\/suppliers\",\"sort\":\"4\",\"icon\":\"\",\"flag\":null,\"color\":null,\"state\":\"\",\"status\":\"0\"}');

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
('20230803000006', 'admin', '2023-08-03 17:50:05', NULL, NULL, 0, '20230803000001', NULL, 'Currencies', NULL, 'master/currencies', 5, '', NULL, NULL, '', 0),
('20230803000007', 'admin', '2023-08-03 17:52:41', NULL, NULL, 0, '20230803000001', NULL, 'Unit of Meaasure', NULL, 'master/uom', 6, '', NULL, NULL, '', 0),
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
('20230803000006', 'admin', '2023-08-03 17:50:29', NULL, NULL, 0, '20230803000006', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('20230803000007', 'admin', '2023-08-03 17:52:55', NULL, NULL, 0, '20230803000007', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('65bdf777a5564d5c94068feb0edcb9', 'admin', '2022-09-29 17:03:57', NULL, NULL, 0, 'de3f6855009e49deb7fd2fdd0f3b3d', 'on', NULL, NULL, 'on', NULL, NULL, 'on', 'on', 0),
('836ff9fa6650482fbf81e4f49bb255', 'admin', '2022-09-29 17:03:46', NULL, NULL, 0, '6ccd20c54d1d415189120ec5cc6c81', 'on', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
('962311b4039448699f25c8ef64f470', 'admin', '2022-09-29 17:04:22', NULL, NULL, 0, '44964312f0264429978158ada88843', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('972a76a5a04e416e92a9b4e225c19c', 'admin', '2022-09-29 17:04:02', NULL, NULL, 0, 'd13439e3f2324450a69b4e0e50159a', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0),
('c150682d482f4c25b613172ba9b880', 'admin', '2022-09-29 17:04:13', NULL, NULL, 0, 'e3c31e10b6c64e119b068ae4b73be6', 'on', 'on', 'on', 'on', NULL, NULL, 'on', 'on', 0);

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
('20230803000006', 'admin', '2023-08-03 10:32:35', NULL, NULL, 0, 'nanda', '20230109000001', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000007', 'admin', '2023-08-03 10:32:35', 'admin', '2023-08-03 10:32:37', 0, 'nanda', '20230803000001', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000012', 'admin', '2023-08-03 10:32:35', 'admin', '2023-08-03 10:32:44', 0, 'nanda', '44964312f0264429978158ada88843', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230803000013', 'admin', '2023-08-03 10:32:35', NULL, NULL, 0, 'nanda', '6ccd20c54d1d415189120ec5cc6c81', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000014', 'admin', '2023-08-03 10:32:35', NULL, NULL, 0, 'nanda', 'b679033b3256414b8f916c69f17674', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000015', 'admin', '2023-08-03 10:32:35', 'admin', '2023-08-03 10:32:46', 0, 'nanda', 'c8f8362a5f6c432ab27d37213f15d4', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000016', 'admin', '2023-08-03 10:32:35', 'admin', '2023-08-03 10:32:37', 0, 'nanda', 'cf98f97766f6405590b26daa586e00', 1, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000017', 'admin', '2023-08-03 10:32:35', NULL, NULL, 0, 'nanda', 'd13439e3f2324450a69b4e0e50159a', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000018', 'admin', '2023-08-03 10:32:35', 'admin', '2023-08-03 10:32:51', 0, 'nanda', 'de3f6855009e49deb7fd2fdd0f3b3d', 1, 0, 0, 1, 0, 0, 1, 1, 0),
('20230803000019', 'admin', '2023-08-03 10:32:35', NULL, NULL, 0, 'nanda', 'e3c31e10b6c64e119b068ae4b73be6', 0, 0, 0, 0, 0, 0, 0, 0, 0),
('20230803000020', 'admin', '2023-08-03 10:53:00', 'admin', '2023-08-03 10:53:09', 0, 'admin', '20230803000006', 1, 1, 1, 1, 0, 0, 1, 1, 0),
('20230803000021', 'admin', '2023-08-03 10:53:00', 'admin', '2023-08-03 10:53:10', 0, 'admin', '20230803000007', 1, 1, 1, 1, 0, 0, 1, 1, 0),
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
('20230803000001', 'admin', '2023-08-03 17:31:56', 'admin', '2023-08-03 17:32:10', 2, '', 'admin', '2023-08-03 10:32:16', 0, '2', 'Nanda Ayu Insani', NULL, 'nanda', 'nanda@123#', 'nana@gmail.com', '2319232', 'Implementator', NULL, 0, 0),
('86f9f296025243ed953fe6014ff765', 'admin', '2021-12-26 11:24:58', 'admin', '2022-11-15 13:27:53', 0, NULL, NULL, NULL, 0, '1', 'Administrator', '', 'admin', 'Login@190320', 'admin@aeconsys.com', '88888888888', 'Admin System', NULL, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indeks untuk tabel `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
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
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `logins`
--
ALTER TABLE `logins`
  MODIFY `id` bigint(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `logs`
--
ALTER TABLE `logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=363;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `approvals`
--
ALTER TABLE `approvals`
  ADD CONSTRAINT `approvals_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `approvals_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `config`
--
ALTER TABLE `config`
  ADD CONSTRAINT `config_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `config_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `currencies`
--
ALTER TABLE `currencies`
  ADD CONSTRAINT `currencies_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `currencies_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
