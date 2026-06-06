-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 06, 2026 at 02:48 PM
-- Server version: 8.4.7
-- PHP Version: 8.2.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tatkaldoctor_listing`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

DROP TABLE IF EXISTS `api_logs`;
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint UNSIGNED DEFAULT NULL,
  `api_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `request_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_headers` json DEFAULT NULL,
  `response_status` smallint UNSIGNED DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT '0',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `api_logs_client_id_foreign` (`client_id`),
  KEY `api_logs_api_key_index` (`api_key`),
  KEY `api_logs_success_index` (`success`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `api_logs`
--

INSERT INTO `api_logs` (`id`, `client_id`, `api_key`, `endpoint`, `method`, `request_ip`, `request_headers`, `response_status`, `success`, `error_message`, `created_at`) VALUES
(1, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/countries', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"c33e7dcd337a4380848039d930b5251c\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"connection\": [\"Keep-Alive\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697262\"]}', 200, 1, NULL, '2026-06-05 22:07:42'),
(2, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/cities/IND', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"757159f10b6043e1bb2a65d68a1426b4\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697262\"]}', 200, 1, NULL, '2026-06-05 22:07:42'),
(3, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/locations/1', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"e0ec7c72df2a471abf5c387c92167d43\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697262\"]}', 200, 1, NULL, '2026-06-05 22:07:42'),
(4, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/services', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"5cb7d1ffdb944c9c9c0c4436a2d908b7\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697262\"]}', 200, 1, NULL, '2026-06-05 22:07:42'),
(5, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/qualifications', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"9b5a82158240478a860fb4f35317ba0a\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697262\"]}', 200, 1, NULL, '2026-06-05 22:07:42'),
(6, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/settings/public', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"e804aad6ea26460c82de845b208010b7\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697262\"]}', 200, 1, NULL, '2026-06-05 22:07:42'),
(7, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/listings/search', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"b3f231ab9a9340bf8b16da9cf89acd5d\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697262\"]}', 200, 1, NULL, '2026-06-05 22:07:42'),
(8, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/countries', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"2ec764c12f7f4a4cac981060d775f037\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"connection\": [\"Keep-Alive\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697390\"]}', 200, 1, NULL, '2026-06-05 22:09:50'),
(9, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/cities/IND', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"441d1a5d202542928bb7b8c784a847a7\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697390\"]}', 200, 1, NULL, '2026-06-05 22:09:50'),
(10, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/locations/1', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"036c8c3050bc496ab0c8073de0e5397f\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697390\"]}', 200, 1, NULL, '2026-06-05 22:09:50'),
(11, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/services', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"8c69c7c6db2a4c59aa4791b501c28c15\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697390\"]}', 200, 1, NULL, '2026-06-05 22:09:50'),
(12, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/qualifications', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"5dadcf55f1fe4b54a68da69077b9a5e8\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697390\"]}', 200, 1, NULL, '2026-06-05 22:09:50'),
(13, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/settings/public', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"7b197d62c9544f08860a31a720d490a6\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697390\"]}', 200, 1, NULL, '2026-06-05 22:09:51'),
(14, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/listings/search', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"004b3467c365475daa680a9585bc794f\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697391\"]}', 200, 1, NULL, '2026-06-05 22:09:51'),
(15, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/listings/d60fea83-0a1c-4758-8e45-ae06c28f4f2e', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"f18e0095d8f341e6834f6bae40c7805a\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780697391\"]}', 200, 1, NULL, '2026-06-05 22:09:51'),
(16, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/countries', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"0f524c2542924232818e233a4898058b\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"connection\": [\"Keep-Alive\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698507\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(17, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/cities/IND', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"66126b09a16c4d7793d632a2b5fde040\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(18, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/locations/1', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"d025c6e0a5bd4248b52e4d5fde9e4872\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(19, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/services', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"082ea8953c0442ccb609b390f658a32b\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(20, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/qualifications', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"6b5bf0bda9fd44c8aa454be0fc8a0e14\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(21, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/settings/public', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"d28828a62b8248699b977c234a568e8a\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(22, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/listings/search', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"2e567d2d1f384e588b0ecb8dc1b4678a\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(23, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/listings/d60fea83-0a1c-4758-8e45-ae06c28f4f2e', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"9a596745a2254d138e445ca260a68a52\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28'),
(24, 1, '672151f61e9a9a64bbfbc8369121ec5a', '/api/v1/listings/slug/dr-ravi-kumar-d60fea83', 'GET', '127.0.0.1', '{\"host\": [\"127.0.0.1:8000\"], \"x-nonce\": [\"4f2f82a2eb9d47febf94d0c0a1d7d4b7\"], \"x-api-key\": [\"672151f61e9a9a64bbfbc8369121ec5a\"], \"user-agent\": [\"Mozilla/5.0 (Windows NT; Windows NT 10.0; en-US) WindowsPowerShell/5.1.19041.6456\"], \"x-signature\": [\"[masked]\"], \"x-timestamp\": [\"1780698508\"]}', 200, 1, NULL, '2026-06-05 22:28:28');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `api_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret_key` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `avail_from_date` date DEFAULT NULL,
  `avail_to_date` date DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clients_uuid_unique` (`uuid`),
  UNIQUE KEY `clients_api_key_unique` (`api_key`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `uuid`, `name`, `api_key`, `secret_key`, `avail_from_date`, `avail_to_date`, `status`, `created_at`, `updated_at`) VALUES
(1, '71cb5fc8-e7c5-4738-8bf4-81405a6b92c6', 'Prividhi India', '672151f61e9a9a64bbfbc8369121ec5a', 'eyJpdiI6ImVOTWkzaWhSbTh6SWxFTGgvMXNXa3c9PSIsInZhbHVlIjoib0tmZ1h4eGphMDJ2bklObGduVENFcG5HVk9QZ0pBZHJPclFIV3VJRy9TMENrbVIwNVl5VmtTb2NORmJSMUpSMWhPV3dtTFNidWxEVVU5a2trbEhJaWRwY25SekM2SUZyMWhRUWJPbDJRbjA9IiwibWFjIjoiMDkwYTBjZDAyMjY3ZTNmN2E3YTY1MGFlMDFjMjM5YzY1NzgwMmI5MDY3OGJmYmJhZjNkMDdkNGI1NDExNTc5MCIsInRhZyI6IiJ9', NULL, NULL, 'active', '2026-06-05 11:27:03', '2026-06-05 11:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `client_subscriptions`
--

DROP TABLE IF EXISTS `client_subscriptions`;
CREATE TABLE IF NOT EXISTS `client_subscriptions` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `client_id` bigint UNSIGNED NOT NULL,
  `subscription_plan_id` bigint UNSIGNED NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_subscriptions_client_id_foreign` (`client_id`),
  KEY `client_subscriptions_subscription_plan_id_foreign` (`subscription_plan_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctor_documents`
--

DROP TABLE IF EXISTS `doctor_documents`;
CREATE TABLE IF NOT EXISTS `doctor_documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_id` bigint UNSIGNED NOT NULL,
  `document_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_size` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `doctor_documents_listing_id_foreign` (`listing_id`),
  KEY `doctor_documents_verified_by_foreign` (`verified_by`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
CREATE TABLE IF NOT EXISTS `listings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `country_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `master_city_id` bigint UNSIGNED NOT NULL,
  `master_location_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hospital_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci,
  `personal_contact_no` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appointment_no` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qualifications` json DEFAULT NULL,
  `services` json DEFAULT NULL,
  `meta_data` json DEFAULT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `average_rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `verification_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `verified_at` timestamp NULL DEFAULT NULL,
  `verified_by` bigint UNSIGNED DEFAULT NULL,
  `rejection_reason` text COLLATE utf8mb4_unicode_ci,
  `qr_slug` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `public_profile_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_code_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `listings_uuid_unique` (`uuid`),
  UNIQUE KEY `listings_qr_slug_unique` (`qr_slug`),
  KEY `listings_country_code_foreign` (`country_code`),
  KEY `listings_master_city_id_foreign` (`master_city_id`),
  KEY `listings_master_location_id_foreign` (`master_location_id`),
  KEY `listings_verified_by_foreign` (`verified_by`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listings`
--

INSERT INTO `listings` (`id`, `uuid`, `category_id`, `country_code`, `master_city_id`, `master_location_id`, `name`, `hospital_name`, `address`, `description`, `personal_contact_no`, `appointment_no`, `qualifications`, `services`, `meta_data`, `latitude`, `longitude`, `average_rating`, `status`, `verification_status`, `verified_at`, `verified_by`, `rejection_reason`, `qr_slug`, `public_profile_url`, `qr_code_path`, `qr_generated_at`, `created_at`, `updated_at`) VALUES
(1, 'd60fea83-0a1c-4758-8e45-ae06c28f4f2e', NULL, 'IND', 1, 1, 'Dr Ravi Kumar', 'Ravi Crinic', 'Shahdara', 'Shahdara', '1234567898', '4567896321', '[\"6\"]', '[\"10\"]', NULL, -1.0000000, -2.0000000, 0.00, 1, 'approved', '2026-06-05 16:38:58', 1, NULL, 'dr-ravi-kumar-d60fea83', 'https://tatkaldoctors.com/d/dr-ravi-kumar-d60fea83', NULL, '2026-06-05 16:55:50', '2026-06-05 14:42:47', '2026-06-05 16:55:50');

-- --------------------------------------------------------

--
-- Table structure for table `listing_audit_logs`
--

DROP TABLE IF EXISTS `listing_audit_logs`;
CREATE TABLE IF NOT EXISTS `listing_audit_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_id` bigint UNSIGNED NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `changed_by` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `listing_audit_logs_changed_by_foreign` (`changed_by`),
  KEY `listing_audit_logs_listing_id_action_index` (`listing_id`,`action`),
  KEY `listing_audit_logs_created_at_index` (`created_at`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `listing_audit_logs`
--

INSERT INTO `listing_audit_logs` (`id`, `listing_id`, `action`, `old_values`, `new_values`, `remarks`, `changed_by`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 1, 'qr_generated', NULL, '{\"qr_slug\": \"dr-ravi-kumar-d60fea83\", \"public_profile_url\": \"https://tatkaldoctors.com/d/dr-ravi-kumar-d60fea83\"}', NULL, 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', '2026-06-05 16:55:50', '2026-06-05 16:55:50');

-- --------------------------------------------------------

--
-- Table structure for table `master_cities`
--

DROP TABLE IF EXISTS `master_cities`;
CREATE TABLE IF NOT EXISTS `master_cities` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_cities_country_code_foreign` (`country_code`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_cities`
--

INSERT INTO `master_cities` (`id`, `country_code`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'IND', 'Delhi NCR', 1, '2026-06-05 11:48:31', '2026-06-05 11:48:31');

-- --------------------------------------------------------

--
-- Table structure for table `master_countries`
--

DROP TABLE IF EXISTS `master_countries`;
CREATE TABLE IF NOT EXISTS `master_countries` (
  `code` char(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_countries`
--

INSERT INTO `master_countries` (`code`, `name`, `created_at`, `updated_at`) VALUES
('IND', 'India', '2026-06-05 11:37:47', '2026-06-05 11:37:47');

-- --------------------------------------------------------

--
-- Table structure for table `master_locations`
--

DROP TABLE IF EXISTS `master_locations`;
CREATE TABLE IF NOT EXISTS `master_locations` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `master_city_id` bigint UNSIGNED NOT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `master_locations_master_city_id_foreign` (`master_city_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_locations`
--

INSERT INTO `master_locations` (`id`, `master_city_id`, `location`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Shahdara', 1, '2026-06-05 13:39:51', '2026-06-05 13:39:51');

-- --------------------------------------------------------

--
-- Table structure for table `master_qualifications`
--

DROP TABLE IF EXISTS `master_qualifications`;
CREATE TABLE IF NOT EXISTS `master_qualifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `qualification` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_qualifications`
--

INSERT INTO `master_qualifications` (`id`, `qualification`, `status`, `created_at`, `updated_at`) VALUES
(1, 'MBBS', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(2, 'MD', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(3, 'MS', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(4, 'BDS', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(5, 'MDS', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(6, 'BAMS', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(7, 'BHMS', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(8, 'DNB', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(9, 'DM', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(10, 'MCh', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11');

-- --------------------------------------------------------

--
-- Table structure for table `master_services`
--

DROP TABLE IF EXISTS `master_services`;
CREATE TABLE IF NOT EXISTS `master_services` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` bigint UNSIGNED NOT NULL DEFAULT '0',
  `service` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_services`
--

INSERT INTO `master_services` (`id`, `parent_id`, `service`, `status`, `created_at`, `updated_at`) VALUES
(1, 0, 'General Physician', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(13, 0, 'Cardiologist', 1, '2026-06-05 13:57:17', '2026-06-05 13:57:17'),
(3, 0, 'Dermatologist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(4, 0, 'Orthopedic', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(5, 0, 'Pediatrician', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(6, 0, 'Gynecologist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(7, 0, 'Neurologist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(8, 0, 'Psychiatrist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(9, 0, 'Ophthalmologist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(10, 0, 'ENT Specialist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(11, 0, 'Dentist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11'),
(12, 0, 'Urologist', 1, '2026-06-05 13:52:11', '2026-06-05 13:52:11');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_05_161123_create_personal_access_tokens_table', 1),
(5, '2026_06_05_163224_add_role_is_active_to_users_table', 1),
(6, '2026_06_05_163312_create_clients_table', 1),
(7, '2026_06_05_170225_create_master_countries_table', 2),
(8, '2026_06_05_171235_create_master_cities_table', 3),
(9, '2026_06_06_000001_create_master_locations_table', 4),
(10, '2026_06_06_000002_create_master_qualifications_table', 5),
(11, '2026_06_06_000003_create_master_services_table', 5),
(12, '2026_06_06_000004_create_listings_table', 6),
(13, '2026_06_06_000005_add_category_id_to_listings_table', 7),
(14, '2026_06_06_000006_create_api_logs_table', 8),
(15, '2026_06_06_000007_create_subscription_plans_table', 9),
(16, '2026_06_06_000008_create_client_subscriptions_table', 9),
(17, '2026_06_06_000009_create_settings_table', 9),
(18, '2026_06_06_000010_add_verification_fields_to_listings_table', 9),
(19, '2026_06_06_000011_create_doctor_documents_table', 10),
(20, '2026_06_06_000012_create_listing_audit_logs_table', 10),
(21, '2026_06_06_000013_add_qr_fields_to_listings_table', 10);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('wIvyV7aCi7Y9rFBUea1nEFguUDSACxRJ21RaJJQs', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoidm8zbk01a1ZMVGhwM1NwejQzWHlGeHFIRElsVVZGckY3bFNRS0pIRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9saXN0aW5ncy8xIjtzOjU6InJvdXRlIjtzOjEzOiJsaXN0aW5ncy5zaG93Ijt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE3OiJwYXNzd29yZF9oYXNoX3dlYiI7czo2NDoiZGE5ZTRjZWIwNTEwNmIxZWQ5NDRjNjQyZWRhNzMyZTAwYTYwNmM4YWMxZmNhOTZhN2E3MjlhNTYzYmUwOWM0MiI7fQ==', 1780698370);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
  `group` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`),
  KEY `settings_group_index` (`group`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `group`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'TatkalDoctor', 'string', 'general', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(2, 'site_tagline', 'Find Doctors Near You', 'string', 'general', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(3, 'timezone', 'Asia/Kolkata', 'string', 'general', 0, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(4, 'maintenance_mode', '0', 'boolean', 'general', 0, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(5, 'support_email', 'support@tatkaldoctor.com', 'string', 'contact', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(6, 'whatsapp_no', '', 'string', 'contact', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(7, 'logo', '', 'string', 'appearance', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(8, 'primary_color', '#2563eb', 'string', 'appearance', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(9, 'api_rate_limit', '100', 'integer', 'api', 0, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(10, 'hmac_tolerance_sec', '300', 'integer', 'api', 0, '2026-06-05 16:26:06', '2026-06-05 16:26:06');

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

DROP TABLE IF EXISTS `subscription_plans`;
CREATE TABLE IF NOT EXISTS `subscription_plans` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `duration_days` int UNSIGNED NOT NULL DEFAULT '30',
  `max_staff` int UNSIGNED DEFAULT NULL,
  `max_locations` int UNSIGNED DEFAULT NULL,
  `max_appointments` int UNSIGNED DEFAULT NULL,
  `features` json DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscription_plans_slug_unique` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `slug`, `description`, `price`, `duration_days`, `max_staff`, `max_locations`, `max_appointments`, `features`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Free', 'free', 'Basic listing plan for individual doctors. No cost, limited features.', 0.00, 365, 1, 1, NULL, '[\"1 doctor profile\", \"1 location\", \"Basic listing visibility\", \"No appointment booking\"]', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(2, 'Starter', 'starter', 'For small clinics. Includes appointment management and multi-location support.', 499.00, 30, 3, 2, 100, '[\"Up to 3 staff profiles\", \"2 locations\", \"Appointment booking (100/month)\", \"WhatsApp notifications\", \"Priority listing\"]', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(3, 'Professional', 'professional', 'For growing clinics and hospitals. Full features with higher limits.', 1499.00, 30, 10, 5, 500, '[\"Up to 10 staff profiles\", \"5 locations\", \"Appointment booking (500/month)\", \"WhatsApp & AI booking\", \"Priority listing\", \"Analytics dashboard\", \"API access\"]', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06'),
(4, 'Enterprise', 'enterprise', 'Unlimited plan for hospital chains and large healthcare organisations.', 4999.00, 30, NULL, NULL, NULL, '[\"Unlimited staff profiles\", \"Unlimited locations\", \"Unlimited appointments\", \"WhatsApp & AI booking\", \"Dedicated support\", \"Custom integrations\", \"Full API access\", \"SLA guarantee\"]', 1, '2026-06-05 16:26:06', '2026-06-05 16:26:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `is_active`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'superadmin@tatkaldoctor.com', NULL, '$2y$12$W5yYsbNcJgwtc2BMDCKpyOMLhLtexhj5YTYZPdKwzMexGlMhXdyLe', 'super_admin', 1, NULL, '2026-06-05 11:08:11', '2026-06-05 16:26:06');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
