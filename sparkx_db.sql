-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 19, 2026 at 01:37 PM
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
-- Database: `sparkx_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL UNIQUE,
  `email` varchar(150) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) NOT NULL DEFAULT 'admin',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `role`, `last_login`, `created_at`) VALUES
(1, 'admin', 'admin@gmail.com', '$2y$10$DGlqdChSEWZ8pyfyZ389MOEcfMivmoR7eeRzVtdqm5Xp1aiECDPai', 'admin', NULL, '2026-06-06 12:40:00');

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `method` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `proof_image` varchar(255) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `user_id`, `amount`, `method`, `status`, `created_at`, `proof_image`, `reviewed_by`, `reviewed_at`) VALUES
(1, 1, 250.00, 'Crypto Wallet', 'approved', '2026-05-17 13:17:16', 'TRX789456', NULL, NULL),
(2, 1, 250.00, 'Crypto Wallet', 'approved', '2026-05-17 13:17:58', 'TRX789456', NULL, NULL),
(3, 1, 100.00, 'Easy Paisa Wallet', 'approved', '2026-05-17 15:22:01', 'TRX789456', NULL, NULL),
(4, 1, 30.00, 'Jazz Cash Wallet', 'approved', '2026-05-17 16:32:36', 'Your high-speed mining hardware has been allocated and activated!', NULL, NULL),
(5, 1, 250.00, 'Crypto Wallet', 'approved', '2026-05-17 17:06:07', 'TRX789456', NULL, NULL),
(6, 8, 500.00, 'Crypto Wallet', 'approved', '2026-05-18 06:18:23', 'Warning: Undefined array key \"rank\" in C:\\xampp\\htdocs\\sparkx1\\user\\dashboard\\index.php on line 154', NULL, NULL),
(7, 1, 30.00, 'Crypto Wallet', 'rejected', '2026-05-18 08:26:57', '765767', NULL, NULL),
(8, 1, 100.00, 'Jazz Cash Wallet', 'approved', '2026-05-18 08:51:45', '76765', NULL, NULL),
(9, 1, 100.00, 'Jazz Cash Wallet', 'approved', '2026-05-18 08:59:53', '7676565', NULL, NULL),
(10, 1, 30.00, 'Jazz Cash Wallet', 'pending', '2026-05-18 09:04:44', '434535', NULL, NULL),
(11, 1, 250.00, 'Jazz Cash Wallet', 'pending', '2026-05-18 15:09:36', '2342342', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `investments`
--

CREATE TABLE `investments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `daily_roi` decimal(5,2) NOT NULL,
  `hourly_rate` decimal(10,6) NOT NULL,
  `status` enum('pending','active','completed','rejected') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `investments`
--

INSERT INTO `investments` (`id`, `user_id`, `plan_id`, `amount`, `daily_roi`, `hourly_rate`, `status`, `created_at`) VALUES
(1, 1, 2, 5.00, 4.50, 3.000000, 'completed', '2026-05-17 16:17:09'),
(2, 1, 4, 55.00, 3.00, 0.120000, 'completed', '2026-05-17 16:31:02'),
(3, 1, 4, 5.01, 3.00, 0.120000, 'completed', '2026-05-17 16:31:58'),
(4, 1, 2, 78.00, 4.50, 3.000000, 'completed', '2026-05-17 17:03:39'),
(5, 1, 4, 100.00, 3.00, 0.120000, 'completed', '2026-05-17 17:08:59'),
(6, 1, 4, 50.00, 3.00, 0.120000, 'completed', '2026-05-17 17:13:15'),
(7, 8, 4, 200.00, 3.00, 20.000000, 'active', '2026-05-18 06:18:39'),
(8, 8, 4, 15.00, 3.00, 20.000000, 'active', '2026-05-18 06:54:42'),
(9, 1, 4, 5.00, 3.00, 20.000000, 'completed', '2026-05-18 08:21:37'),
(10, 1, 4, 4.00, 3.00, 20.000000, 'active', '2026-05-18 08:22:44'),
(11, 1, 4, 5.00, 3.00, 20.000000, 'active', '2026-05-18 08:24:21'),
(12, 1, 4, 45.00, 3.00, 20.000000, 'pending', '2026-05-18 09:04:54');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 'Plan Activated!', 'Your investment of $5.00 in plan \'Gold\' has been approved and activated. Daily returns have begun!', 1, '2026-05-18 08:24:43'),
(2, 1, 'Plan Activated!', 'Your investment of $4.00 in plan \'Gold\' has been approved and activated. Daily returns have begun!', 1, '2026-05-18 08:25:19'),
(3, 1, 'Recharge Rejected', 'Your deposit request of $30.00 via Crypto Wallet (Trx ID: 765767) was rejected by administrator.', 1, '2026-05-18 08:27:30'),
(4, 1, 'New Profit Payout!', 'You have received a daily profit return of $12.50 on your active Gold investment plan.', 1, '2026-05-18 08:46:45'),
(5, 1, 'Recharge Approved', 'Your deposit of $100.00 via Jazz Cash Wallet (Trx ID: 76765) has been approved and credited.', 1, '2026-05-18 08:52:00'),
(6, 1, 'Recharge Approved', 'Your deposit of $100.00 via Jazz Cash Wallet (Trx ID: 7676565) has been approved and credited.', 1, '2026-05-18 09:00:02'),
(7, 1, 'Salary Claim Approved! 🎉', 'Congratulations! Your salary payout of $5.00 for V1 Manager has been approved and credited to your earning balance.', 1, '2026-05-18 11:25:52'),
(8, 1, 'Payout Released', 'Your withdrawal request of $10.00 via Jazz Cash Wallet (A/C: 1234567889) has been approved and processed.', 1, '2026-05-18 15:07:13');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` varchar(100) NOT NULL,
  `identifier` varchar(100) NOT NULL,
  `gateway_slug` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `charges` decimal(15,2) NOT NULL DEFAULT 0.00,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_url` text DEFAULT NULL,
  `gateway_message` varchar(255) DEFAULT NULL,
  `gateway_response` text DEFAULT NULL,
  `request_payload` text DEFAULT NULL,
  `metadata` text DEFAULT NULL,
  `credited_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `order_id`, `identifier`, `gateway_slug`, `amount`, `currency`, `charges`, `transaction_id`, `status`, `payment_url`, `gateway_message`, `gateway_response`, `request_payload`, `metadata`, `credited_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'TCPB1779187320E1BB2BEE', 'TCPB1779187320E1BB2BEE', 'easypaisa_payboost', 10.00, 'USD', 0.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:42:00', '2026-05-19 12:42:00'),
(2, 1, 'TCPB1779187343B3BBC26B', 'TCPB1779187343B3BBC26B', 'easypaisa_payboost', 10.00, 'USD', 0.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-19 12:42:23', '2026-05-19 12:42:23'),
(3, 1, 'TCNP1779189129236EC5E4', 'TCNP1779189129236EC5E4', 'nowpayments_bep20', 250.00, 'USD', 0.00, NULL, 'pending', '', '0x1F317dcFA38634BfC02bbD942B90E25A382f02D7', '{\"payment_id\":\"5255830047\",\"payment_status\":\"waiting\",\"pay_address\":\"0x1F317dcFA38634BfC02bbD942B90E25A382f02D7\",\"price_amount\":250,\"price_currency\":\"usd\",\"pay_amount\":250.16203353,\"amount_received\":248.8743117,\"pay_currency\":\"usdtbsc\",\"order_id\":\"TCNP1779189129236EC5E4\",\"order_description\":null,\"payin_extra_id\":null,\"ipn_callback_url\":\"http:\\/\\/localhost\\/sparkx1\\/ipn_nowpayments.php\",\"customer_email\":null,\"created_at\":\"2026-05-19T11:12:18.998Z\",\"updated_at\":\"2026-05-19T11:12:18.998Z\",\"purchase_id\":\"4621956979\",\"smart_contract\":null,\"network\":\"bsc\",\"network_precision\":null,\"time_limit\":null,\"burning_percent\":null,\"expiration_estimate_date\":\"2026-05-19T11:32:18.998Z\",\"is_fixed_rate\":false,\"is_fee_paid_by_user\":false,\"valid_until\":\"2026-05-26T11:12:18.998Z\",\"type\":\"crypto2crypto\",\"product\":\"api\",\"origin_ip\":\"43.246.227.98\",\"http_code\":201}', NULL, '{\"pay_amount\":250.16203353}', NULL, '2026-05-19 13:12:09', '2026-05-19 13:12:09'),
(4, 1, 'TCNP1779189144C6AA633A', 'TCNP1779189144C6AA633A', 'nowpayments_bep20', 250.00, 'USD', 0.00, NULL, 'pending', '', '0xf74335285f3814EF6852902056c8779F2a9B04a6', '{\"payment_id\":\"4689145038\",\"payment_status\":\"waiting\",\"pay_address\":\"0xf74335285f3814EF6852902056c8779F2a9B04a6\",\"price_amount\":250,\"price_currency\":\"usd\",\"pay_amount\":250.16203353,\"amount_received\":248.8743178,\"pay_currency\":\"usdtbsc\",\"order_id\":\"TCNP1779189144C6AA633A\",\"order_description\":null,\"payin_extra_id\":null,\"ipn_callback_url\":\"http:\\/\\/localhost\\/sparkx1\\/ipn_nowpayments.php\",\"customer_email\":null,\"created_at\":\"2026-05-19T11:12:33.458Z\",\"updated_at\":\"2026-05-19T11:12:33.458Z\",\"purchase_id\":\"6358374970\",\"smart_contract\":null,\"network\":\"bsc\",\"network_precision\":null,\"time_limit\":null,\"burning_percent\":null,\"expiration_estimate_date\":\"2026-05-19T11:32:33.458Z\",\"is_fixed_rate\":false,\"is_fee_paid_by_user\":false,\"valid_until\":\"2026-05-26T11:12:33.458Z\",\"type\":\"crypto2crypto\",\"product\":\"api\",\"origin_ip\":\"43.246.227.98\",\"http_code\":201}', NULL, '{\"pay_amount\":250.16203353}', NULL, '2026-05-19 13:12:24', '2026-05-19 13:12:24'),
(5, 1, 'TCPB17791891501E9D79C2', 'TCPB17791891501E9D79C2', 'easypaisa_payboost', 250.00, 'USD', 0.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-19 13:12:30', '2026-05-19 13:12:30'),
(6, 1, 'TCPB17791896810FAE116A', 'TCPB17791896810FAE116A', 'easypaisa_payboost', 250.00, 'USD', 0.00, NULL, 'pending', NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-19 13:21:21', '2026-05-19 13:21:21'),
(7, 1, 'TCPB1779189945F0E882', 'TCPB1779189945F0E882', 'easypaisa_payboost', 250.00, 'USD', 0.00, NULL, 'pending', 'https://paybost.com/initiate/payment/checkout?payment_id=eyJpdiI6Im9kYXdDcVdBclFlY1hqS1g1MkVhVnc9PSIsInZhbHVlIjoiM3RGenlhVXhQVVQ4ZkJDMnlsS0FWTEFoRUZvTlpOUmU2Q1BZWkhkWWxKST0iLCJtYWMiOiIwZGVjN2E1NTBhOTI0N2JlMzc5MjgxMWQzODc1Mzc2YjhmZGY3ZTc5MWVmN2M4NTQwZTE1MW', NULL, NULL, NULL, NULL, NULL, '2026-05-19 13:25:45', '2026-05-19 13:25:45'),
(8, 1, 'TCPB17791903535555FB', 'TCPB17791903535555FB', 'easypaisa_payboost', 500.00, 'USD', 0.00, NULL, 'pending', 'https://paybost.com/initiate/payment/checkout?payment_id=eyJpdiI6ImFHVVdwd09vMW1hTlY5c0JFZnVOZ2c9PSIsInZhbHVlIjoid1lZQytNaE15OFlmczVFZkFNT3J0L3VuTEJ4M0Q5eHd5Z1dQUnlCaDU0TT0iLCJtYWMiOiI2ODBhZjRhYjcwNmNkNjI1ZjBjNDg1NjJiNDJmYTNiOTFjNzE2NzYzOTM3N2U4NWY1ODQ5ZT', NULL, NULL, NULL, NULL, NULL, '2026-05-19 13:32:33', '2026-05-19 13:32:33'),
(9, 1, 'TCNP17791903834543EA', 'TCNP17791903834543EA', 'nowpayments_bep20', 500.00, 'USD', 0.00, NULL, 'pending', '', '0x8b75e1d374cbA4731a739e204F00938C59E64d24', '{\"payment_id\":\"5002420916\",\"payment_status\":\"waiting\",\"pay_address\":\"0x8b75e1d374cbA4731a739e204F00938C59E64d24\",\"price_amount\":500,\"price_currency\":\"usd\",\"pay_amount\":499.74620565,\"amount_received\":497.2106055,\"pay_currency\":\"usdtbsc\",\"order_id\":\"TCNP17791903834543EA\",\"order_description\":null,\"payin_extra_id\":null,\"ipn_callback_url\":\"http:\\/\\/localhost\\/sparkx1\\/ipn_nowpayments.php\",\"customer_email\":null,\"created_at\":\"2026-05-19T11:33:12.343Z\",\"updated_at\":\"2026-05-19T11:33:12.343Z\",\"purchase_id\":\"5783362044\",\"smart_contract\":null,\"network\":\"bsc\",\"network_precision\":null,\"time_limit\":null,\"burning_percent\":null,\"expiration_estimate_date\":\"2026-05-19T11:53:12.343Z\",\"is_fixed_rate\":false,\"is_fee_paid_by_user\":false,\"valid_until\":\"2026-05-26T11:33:12.343Z\",\"type\":\"crypto2crypto\",\"product\":\"api\",\"origin_ip\":\"43.246.227.98\",\"http_code\":201}', NULL, '{\"pay_amount\":499.74620565}', NULL, '2026-05-19 13:33:03', '2026-05-19 13:33:03');

-- --------------------------------------------------------

--
-- Table structure for table `payment_gateways`
--

CREATE TABLE `payment_gateways` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` enum('automatic','manual') DEFAULT 'manual',
  `is_active` tinyint(1) DEFAULT 1,
  `is_deposit` tinyint(1) DEFAULT 1,
  `is_withdrawal` tinyint(1) DEFAULT 1,
  `min_deposit` decimal(15,2) DEFAULT 0.00,
  `max_deposit` decimal(15,2) DEFAULT 0.00,
  `min_withdrawal` decimal(15,2) DEFAULT 0.00,
  `max_withdrawal` decimal(15,2) DEFAULT 0.00,
  `fee_deposit_pct` decimal(5,2) DEFAULT 0.00,
  `fee_withdraw_pct` decimal(5,2) DEFAULT 0.00,
  `api_key` text DEFAULT NULL,
  `api_secret` text DEFAULT NULL,
  `api_merchant_id` varchar(255) DEFAULT NULL,
  `instructions` text DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_gateways`
--

INSERT INTO `payment_gateways` (`id`, `name`, `slug`, `type`, `is_active`, `is_deposit`, `is_withdrawal`, `min_deposit`, `max_deposit`, `min_withdrawal`, `max_withdrawal`, `fee_deposit_pct`, `fee_withdraw_pct`, `api_key`, `api_secret`, `api_merchant_id`, `instructions`, `sort_order`, `image`) VALUES
(5, 'NOWPayments (USDT BEP20)', 'nowpayments_bep20', 'automatic', 1, 1, 1, 10.00, 10000.00, 0.00, 0.00, 0.00, 0.00, '0D77JXB-3ZK487W-GC97DAB-MWCP9EP', 'h7JD95Tg8T6q5g6TPh3GMF1rbI4ACQZt', 'afdd76ba-26d6-4e0f-9e94-ba92cf8dffc2', 'Pay via automatic USDT BEP20 (Binance Smart Chain). Your deposit will be credited automatically once the transaction is confirmed on the blockchain.', 1, 'assets/admin/images/payment-method/g6yypk5aiU8N209KTxqMkNE2j5wUeWtGlKtBCeeu.png'),
(6, 'Easypaisa (PayBost)', 'easypaisa_payboost', 'automatic', 1, 1, 1, 10.00, 10000.00, 0.00, 0.00, 0.00, 0.00, 'el7m4kt3kq44xuyfk8lz78eggt1ze5vxpe9mpivvm1tjhfa7k1', 'wbjvqx9gnvbdqhs3t37c3ymhle857mdnl0x3xmy0tyt2xgxu7m', '', 'Deposit PKR automatically via Easypaisa using PayBost. You will be redirected to the secure gateway checkout page to complete the payment.', 2, 'assets/admin/images/payment-method/qN6UJJsGCI0e5il6PfxTiDHvD0IKt2vWfCH74XIr.png'),
(7, 'JazzCash (PayBost)', 'jazzcash_payboost', 'automatic', 1, 1, 1, 10.00, 10000.00, 0.00, 0.00, 0.00, 0.00, 'el7m4kt3kq44xuyfk8lz78eggt1ze5vxpe9mpivvm1tjhfa7k1', 'wbjvqx9gnvbdqhs3t37c3ymhle857mdnl0x3xmy0tyt2xgxu7m', '', 'Deposit PKR automatically via JazzCash using PayBost. You will be redirected to the secure gateway checkout page to complete the payment.', 3, 'assets/admin/images/payment-method/nJrY0ThQILfw50p7cRYAgoHmtLooVQGnpbzqrgVj.png');

-- --------------------------------------------------------

--
-- Table structure for table `payout_banks`
--

CREATE TABLE `payout_banks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `min_limit` decimal(15,2) DEFAULT 0.00,
  `max_limit` decimal(15,2) DEFAULT 0.00,
  `fee_pct` decimal(5,2) DEFAULT 0.00,
  `fixed_fee` decimal(15,2) DEFAULT 0.00,
  `instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payout_banks`
--

INSERT INTO `payout_banks` (`id`, `name`, `slug`, `is_active`, `min_limit`, `max_limit`, `fee_pct`, `fixed_fee`, `instructions`) VALUES
(1, 'Easypaisa', 'easypaisa', 1, 500.00, 50000.00, 0.00, 0.00, NULL),
(2, 'JazzCash', 'jazzcash', 1, 500.00, 50000.00, 0.00, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payout_merchants`
--

CREATE TABLE `payout_merchants` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payout_merchants`
--

INSERT INTO `payout_merchants` (`id`, `name`, `is_active`, `created_at`) VALUES
(1, 'Binance (USDT)', 1, '2026-05-15 17:21:42'),
(2, 'Trust Wallet', 1, '2026-05-15 17:21:42');

-- --------------------------------------------------------

--
-- Table structure for table `plans`
--

CREATE TABLE `plans` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `min_investment` decimal(15,2) NOT NULL,
  `max_investment` decimal(15,2) NOT NULL,
  `daily_roi_min` decimal(5,2) NOT NULL,
  `daily_roi_max` decimal(5,2) NOT NULL,
  `hourly_rate` decimal(10,6) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plans`
--

INSERT INTO `plans` (`id`, `name`, `min_investment`, `max_investment`, `daily_roi_min`, `daily_roi_max`, `hourly_rate`, `status`) VALUES
(2, 'Platinum', 5.00, 500.00, 4.50, 5.50, 3.000000, 'active'),
(3, 'Titanium', 500.00, 5000.00, 5.50, 6.50, 0.229166, 'active'),
(4, 'Gold', 2.00, 300.00, 3.00, 1.00, 20.000000, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referee_id` int(11) NOT NULL,
  `level` int(11) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `referrer_id`, `referee_id`, `level`, `created_at`) VALUES
(7, 1, 8, 1, '2026-05-18 06:16:34');

-- --------------------------------------------------------

--
-- Table structure for table `referral_settings`
--

CREATE TABLE `referral_settings` (
  `id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  `commission_pct` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referral_settings`
--

INSERT INTO `referral_settings` (`id`, `level`, `commission_pct`) VALUES
(11, 1, 10.00),
(12, 2, 5.00),
(13, 3, 3.00),
(14, 4, 2.00),
(15, 5, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `salary_claims`
--

CREATE TABLE `salary_claims` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rank_id` int(11) NOT NULL,
  `rank_name` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_claims`
--

INSERT INTO `salary_claims` (`id`, `user_id`, `rank_id`, `rank_name`, `amount`, `status`, `admin_remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'V1 Manager', 5.00, 'approved', 'Approved', '2026-05-18 11:14:36', '2026-05-18 11:20:43'),
(2, 1, 1, 'V1 Manager', 5.00, 'approved', '', '2026-05-18 11:21:02', '2026-05-18 11:25:52');

-- --------------------------------------------------------

--
-- Table structure for table `salary_ranks`
--

CREATE TABLE `salary_ranks` (
  `id` int(11) NOT NULL,
  `rank_name` varchar(50) NOT NULL,
  `salary_amount` decimal(10,2) NOT NULL,
  `self_invest` decimal(10,2) NOT NULL,
  `direct_active` int(11) NOT NULL,
  `indirect_active` int(11) NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_ranks`
--

INSERT INTO `salary_ranks` (`id`, `rank_name`, `salary_amount`, `self_invest`, `direct_active`, `indirect_active`, `updated_at`) VALUES
(1, 'V1 Manager', 5.00, 5.00, 1, 0, '2026-05-18 11:14:25'),
(2, 'V2 Manager', 80.00, 10.00, 20, 60, '2026-05-17 15:15:29'),
(3, 'V3 Manager', 150.00, 20.00, 35, 80, '2026-05-17 14:54:15'),
(4, 'V4 Manager', 250.00, 30.00, 508, 150, '2026-05-17 15:15:29'),
(5, 'V5 Manager', 800.00, 40.00, 100, 300, '2026-05-17 15:15:29'),
(6, 'V6 Manager', 1500.00, 50.00, 10, 500, '2026-05-17 15:15:29');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`) VALUES
(1, 'site_name', 'Trade Cycle'),
(2, 'site_tagline', ''),
(3, 'currency_symbol', 'RS'),
(4, 'currency_mode', 'usdt'),
(5, 'usdt_pkr_rate', '120'),
(6, 'deposit_fee_pct', '0'),
(7, 'withdrawal_fee_pct', '0'),
(8, 'node_cancel_fee_pct', '25'),
(9, 'profit_distribution_mode', 'everyday'),
(10, 'withdraw_restriction', 'disabled'),
(11, 'withdrawal_day', 'Saturday'),
(12, 'deposit_restriction', 'disabled'),
(13, 'deposit_day', 'Monday'),
(14, 'deposit_instructions', 'Deposit 400b rs \r\nnow click\r\non left'),
(15, 'system_status_min_deposit_value_text', '300 PKR'),
(16, 'system_status_min_payout_value_text', '30 PKR'),
(17, 'system_status_withdraw_fee_value_text', '3%'),
(18, 'system_status_withdraw_time_value_text', '1 Hour To 24 Hour'),
(19, 'system_status_referral_bonus_value_text', 'Upto 15%'),
(20, 'system_status_referral_earning_bonus_value_text', 'Upto 19%'),
(21, 'home_show_deposit_button', 'enabled'),
(22, 'home_show_withdraw_button', 'enabled'),
(23, 'home_show_deposit_logs', 'enabled'),
(24, 'home_show_withdraw_logs', 'enabled'),
(25, 'home_show_transactions', 'enabled'),
(26, 'home_show_team', 'enabled'),
(27, 'home_show_pools', 'enabled'),
(28, 'home_show_fbr_verified', 'enabled'),
(29, 'home_show_scap_verified', 'enabled'),
(30, 'home_show_live_chat', 'enabled'),
(31, 'home_show_logout', 'enabled'),
(32, 'home_show_whatsapp_channel', 'enabled'),
(33, 'home_show_whatsapp_admin', 'enabled'),
(34, 'home_show_whatsapp_group', 'enabled'),
(35, 'whatsapp_channel_url', ''),
(36, 'whatsapp_group_url', ''),
(37, 'whatsapp_admin_url', ''),
(38, 'whatsapp_support_link', ''),
(39, 'salary_days', '15'),
(40, 'salary_guidelines', 'Salary is distributed every [DAYS] days based on your active rank at the time of payout.\r\nBoth direct and indirect active members must remain active throughout the [DAYS]-day period.\r\nSelf investment must be maintained at the required level to stay eligible for the rank salary.\r\nIf any condition is not met at the time of payout, the salary for that cycle will not be credited.\r\nRanks are re-evaluated at the start of every new [DAYS]-day cycle.\r\nHigher ranks include all benefits of lower ranks and unlock greater salary rewards.'),
(41, 'site_logo', 'assets/images/logoIcon/logo_1779119074.jpg'),
(42, 'approval_required_deposit', '1'),
(43, 'approval_required_withdrawal', '1'),
(44, 'approval_required_investment', '1'),
(45, 'user_site_name', 'Spark X'),
(46, 'user_site_logo', 'assets/images/logoIcon/user_logo_1779119074.jpg'),
(47, 'gateway_env_paybost', 'live'),
(49, 'gateway_env_nowpayments', 'live'),
(51, 'whatsapp_number', ''),
(52, 'support_phone', ''),
(53, 'facebook_page_url', ''),
(54, 'facebook_contact_url', ''),
(55, 'system_status_rows', '[{\"title\":\"Min Deposit\",\"value\":\"300 PKR\"},{\"title\":\"Min Withdraw\",\"value\":\"30 PKR\"},{\"title\":\"Withdraw Fee\",\"value\":\"3%\"},{\"title\":\"Withdraw Time\",\"value\":\"1 Hour To 24 Hour\"},{\"title\":\"Referral Bonus\",\"value\":\"Upto 15%\"},{\"title\":\"Referral Earning Bonus\",\"value\":\"Upto 19%\"}]'),
(56, 'profit_distribution_day', 'Friday'),
(57, 'announcement_line_1', 'The previous channel has been deleted ⚠️'),
(58, 'announcement_line_2', 'Join the new official channel to stay updated 🚀'),
(59, 'announcement_btn_text', '👉 Join Now Channel 🎁'),
(60, 'announcement_footer', 'Join the new channel & claim your bonus 🎁');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL DEFAULT 1,
  `site_name` varchar(100) DEFAULT 'Spark X',
  `min_deposit` decimal(15,2) DEFAULT 3.00,
  `min_withdrawal` decimal(15,2) DEFAULT 1.00,
  `referral_bonus_percent` decimal(5,2) DEFAULT 10.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('deposit','withdrawal','investment','profit','referral_bonus') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `status`, `description`, `reference_id`, `created_at`) VALUES
(1, 1, 'deposit', 250.00, 'completed', 'Deposited via Crypto Wallet (Trx: TRX789456)', NULL, '2026-05-17 13:17:16'),
(2, 1, 'deposit', 250.00, 'completed', 'Deposited via Crypto Wallet (Trx: TRX789456)', NULL, '2026-05-17 13:17:58'),
(3, 1, 'deposit', 100.00, 'completed', 'Deposited via Easy Paisa Wallet (Trx: TRX789456)', NULL, '2026-05-17 15:22:01'),
(4, 1, 'investment', 5.00, 'completed', 'Invested $5.00 in Platinum', NULL, '2026-05-17 16:17:09'),
(5, 1, 'investment', 55.00, 'completed', 'Invested $55.00 in Gold', NULL, '2026-05-17 16:31:02'),
(6, 1, 'investment', 5.01, 'completed', 'Invested $5.01 in Gold', NULL, '2026-05-17 16:31:58'),
(7, 1, 'deposit', 30.00, 'completed', 'Deposited via Jazz Cash Wallet (Trx: Your high-speed mining hardware has been allocated and activated!)', NULL, '2026-05-17 16:32:36'),
(8, 1, 'investment', 78.00, 'completed', 'Invested $78.00 in Platinum', NULL, '2026-05-17 17:03:39'),
(9, 1, 'deposit', 250.00, 'completed', 'Deposited via Crypto Wallet (Trx: TRX789456)', NULL, '2026-05-17 17:06:07'),
(10, 1, 'investment', 100.00, 'completed', 'Invested $100.00 in Gold', NULL, '2026-05-17 17:08:59'),
(11, 1, 'investment', 50.00, 'completed', 'Invested $50.00 in Gold', NULL, '2026-05-17 17:13:15'),
(14, 1, 'profit', 0.00, 'completed', '1-Minute profit from Platinum ($5.00)', NULL, '2026-05-18 05:39:59'),
(15, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($55.00)', NULL, '2026-05-18 05:39:59'),
(16, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($5.01)', NULL, '2026-05-18 05:39:59'),
(17, 1, 'profit', 0.04, 'completed', '1-Minute profit from Platinum ($78.00)', NULL, '2026-05-18 05:39:59'),
(18, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($100.00)', NULL, '2026-05-18 05:39:59'),
(19, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($50.00)', NULL, '2026-05-18 05:39:59'),
(20, 1, 'profit', 0.00, 'completed', '1-Minute profit from Platinum ($5.00)', NULL, '2026-05-18 05:40:08'),
(21, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($55.00)', NULL, '2026-05-18 05:40:08'),
(22, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($5.01)', NULL, '2026-05-18 05:40:08'),
(23, 1, 'profit', 0.04, 'completed', '1-Minute profit from Platinum ($78.00)', NULL, '2026-05-18 05:40:08'),
(24, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($100.00)', NULL, '2026-05-18 05:40:08'),
(25, 1, 'profit', 0.00, 'completed', '1-Minute profit from Gold ($50.00)', NULL, '2026-05-18 05:40:08'),
(26, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 05:42:36'),
(27, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 05:42:36'),
(28, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 05:42:36'),
(29, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 05:42:36'),
(30, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 05:42:36'),
(31, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 05:42:36'),
(32, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:09:38'),
(33, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:09:38'),
(34, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:09:38'),
(35, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:09:38'),
(36, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:09:38'),
(37, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:09:38'),
(38, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:10:43'),
(39, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:10:43'),
(40, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:10:43'),
(41, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:10:43'),
(42, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:10:43'),
(43, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:10:43'),
(44, 8, 'deposit', 500.00, 'completed', 'Deposited via Crypto Wallet (Trx: Warning: Undefined array key \\\"rank\\\" in C:\\\\xampp\\\\htdocs\\\\sparkx1\\\\user\\\\dashboard\\\\index.php on line 154)', NULL, '2026-05-18 06:18:23'),
(45, 8, 'investment', 200.00, 'completed', 'Invested $200.00 in Gold', NULL, '2026-05-18 06:18:39'),
(46, 1, 'referral_bonus', 20.00, 'completed', 'Referral Bonus of $20.00 from Level 1 referral (Uriah Valentine) investing in Gold', NULL, '2026-05-18 06:18:39'),
(47, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:18:58'),
(48, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:18:58'),
(49, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:18:58'),
(50, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:18:58'),
(51, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:18:58'),
(52, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:18:58'),
(53, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:18:58'),
(54, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:20:03'),
(55, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:20:03'),
(56, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:20:03'),
(57, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:20:03'),
(58, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:20:03'),
(59, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:20:03'),
(60, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:20:03'),
(61, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:21:08'),
(62, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:21:08'),
(63, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:21:08'),
(64, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:21:08'),
(65, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:21:08'),
(66, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:21:08'),
(67, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:21:08'),
(68, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:39:43'),
(69, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:39:43'),
(70, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:39:43'),
(71, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:39:43'),
(72, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:39:43'),
(73, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:39:43'),
(74, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:39:43'),
(75, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:40:45'),
(76, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:40:45'),
(77, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:40:45'),
(78, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:40:45'),
(79, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:40:45'),
(80, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:40:45'),
(81, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:40:45'),
(82, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:41:48'),
(83, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:41:48'),
(84, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:41:48'),
(85, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:41:48'),
(86, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:41:48'),
(87, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:41:48'),
(88, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:41:48'),
(89, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:42:51'),
(90, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:42:51'),
(91, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:42:51'),
(92, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:42:51'),
(93, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:42:51'),
(94, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:42:51'),
(95, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:42:51'),
(96, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:43:53'),
(97, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:43:54'),
(98, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:43:54'),
(99, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:43:54'),
(100, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:43:54'),
(101, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:43:54'),
(102, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:43:54'),
(103, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:44:57'),
(104, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:44:57'),
(105, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:44:57'),
(106, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:44:57'),
(107, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:44:57'),
(108, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:44:57'),
(109, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:44:57'),
(110, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:46:00'),
(111, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:46:00'),
(112, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:46:00'),
(113, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:46:00'),
(114, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:46:00'),
(115, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:46:00'),
(116, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:46:00'),
(117, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:47:02'),
(118, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:47:03'),
(119, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:47:03'),
(120, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:47:03'),
(121, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:47:03'),
(122, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:47:03'),
(123, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:47:03'),
(124, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:48:05'),
(125, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:48:05'),
(126, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:48:05'),
(127, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:48:05'),
(128, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:48:05'),
(129, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:48:05'),
(130, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:48:05'),
(131, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:49:10'),
(132, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:49:10'),
(133, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:49:10'),
(134, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:49:10'),
(135, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:49:10'),
(136, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:49:10'),
(137, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:49:10'),
(138, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:50:15'),
(139, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:50:15'),
(140, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:50:15'),
(141, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:50:15'),
(142, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:50:15'),
(143, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:50:15'),
(144, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:50:15'),
(145, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:51:17'),
(146, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:51:17'),
(147, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:51:17'),
(148, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:51:17'),
(149, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:51:17'),
(150, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:51:17'),
(151, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:51:17'),
(152, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:52:19'),
(153, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:52:19'),
(154, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:52:19'),
(155, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:52:19'),
(156, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:52:19'),
(157, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:52:19'),
(158, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:52:19'),
(159, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:53:23'),
(160, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:53:23'),
(161, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:53:23'),
(162, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:53:23'),
(163, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:53:23'),
(164, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:53:23'),
(165, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:53:23'),
(166, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 06:54:26'),
(167, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 06:54:26'),
(168, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 06:54:26'),
(169, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 06:54:26'),
(170, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 06:54:26'),
(171, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 06:54:26'),
(172, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 06:54:26'),
(173, 8, 'investment', 15.00, 'completed', 'Invested $15.00 in Gold', NULL, '2026-05-18 06:54:42'),
(174, 1, 'referral_bonus', 1.50, 'completed', 'Referral Bonus of $1.50 from Level 1 referral (Uriah Valentine) investing in Gold', NULL, '2026-05-18 06:54:42'),
(175, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:20:18'),
(176, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:20:18'),
(177, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:20:18'),
(178, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:20:18'),
(179, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:20:18'),
(180, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:20:18'),
(181, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:20:18'),
(182, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:20:18'),
(183, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:21:20'),
(184, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:21:20'),
(185, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:21:20'),
(186, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:21:20'),
(187, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:21:20'),
(188, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:21:20'),
(189, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:21:20'),
(190, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:21:20'),
(191, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:22:22'),
(192, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:22:22'),
(193, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:22:22'),
(194, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:22:22'),
(195, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:22:22'),
(196, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:22:22'),
(197, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:22:22'),
(198, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:22:22'),
(199, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:23:24'),
(200, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:23:24'),
(201, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:23:24'),
(202, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:23:24'),
(203, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:23:24'),
(204, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:23:24'),
(205, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:23:24'),
(206, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:23:24'),
(207, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:24:26'),
(208, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:24:26'),
(209, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:24:26'),
(210, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:24:26'),
(211, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:24:26'),
(212, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:24:26'),
(213, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:24:26'),
(214, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:24:26'),
(215, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:25:29'),
(216, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:25:29'),
(217, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:25:29'),
(218, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:25:29'),
(219, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:25:29'),
(220, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:25:29'),
(221, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:25:29'),
(222, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:25:29'),
(223, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:26:32'),
(224, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:26:32'),
(225, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:26:32'),
(226, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:26:32'),
(227, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:26:32'),
(228, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:26:32'),
(229, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:26:32'),
(230, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:26:32'),
(231, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:27:34'),
(232, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:27:34'),
(233, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:27:34'),
(234, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:27:34'),
(235, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:27:34'),
(236, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:27:34'),
(237, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:27:34'),
(238, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:27:34'),
(239, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:28:38'),
(240, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:28:38'),
(241, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:28:38'),
(242, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:28:38'),
(243, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:28:38'),
(244, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:28:38'),
(245, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:28:38'),
(246, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:28:38'),
(247, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:29:41'),
(248, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:29:42'),
(249, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:29:42'),
(250, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:29:42'),
(251, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:29:42'),
(252, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:29:42'),
(253, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:29:42'),
(254, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:29:42'),
(255, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:30:44'),
(256, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:30:45'),
(257, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:30:45'),
(258, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:30:45'),
(259, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:30:45'),
(260, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:30:45'),
(261, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:30:45'),
(262, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:30:45'),
(263, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:31:47'),
(264, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:31:47'),
(265, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:31:47'),
(266, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:31:47'),
(267, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:31:47'),
(268, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:31:47'),
(269, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:31:47'),
(270, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:31:47'),
(271, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:32:49'),
(272, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:32:49'),
(273, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:32:49'),
(274, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:32:49'),
(275, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:32:49'),
(276, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:32:49'),
(277, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:32:49'),
(278, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:32:49'),
(279, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:33:51'),
(280, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:33:51'),
(281, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:33:51'),
(282, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:33:51'),
(283, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:33:51'),
(284, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:33:51'),
(285, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:33:51'),
(286, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:33:51'),
(287, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:34:53'),
(288, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:34:53'),
(289, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:34:53'),
(290, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:34:53'),
(291, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:34:53'),
(292, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:34:53'),
(293, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:34:53'),
(294, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:34:53'),
(295, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:35:55'),
(296, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:35:55'),
(297, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:35:55'),
(298, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:35:55'),
(299, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:35:55'),
(300, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:35:55'),
(301, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:35:55'),
(302, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:35:55'),
(303, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:36:57'),
(304, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:36:57'),
(305, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:36:57'),
(306, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:36:57'),
(307, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:36:57'),
(308, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:36:57'),
(309, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:36:57'),
(310, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:36:57'),
(311, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:37:59'),
(312, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:37:59'),
(313, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:37:59'),
(314, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:37:59'),
(315, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:37:59'),
(316, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:37:59'),
(317, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:37:59'),
(318, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:37:59'),
(319, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:39:01'),
(320, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:39:01'),
(321, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:39:01'),
(322, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:39:01'),
(323, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:39:01'),
(324, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:39:01'),
(325, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:39:01'),
(326, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:39:01'),
(327, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:40:04'),
(328, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:40:04'),
(329, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:40:04'),
(330, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:40:04'),
(331, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:40:04'),
(332, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:40:04'),
(333, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:40:04'),
(334, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:40:04'),
(335, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:41:06'),
(336, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:41:06'),
(337, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:41:06'),
(338, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:41:06'),
(339, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:41:06'),
(340, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:41:06'),
(341, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:41:06'),
(342, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:41:06'),
(343, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:42:10'),
(344, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:42:10'),
(345, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:42:10'),
(346, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:42:10'),
(347, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:42:10'),
(348, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:42:10'),
(349, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:42:10'),
(350, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:42:10'),
(351, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:43:12'),
(352, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:43:12'),
(353, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:43:12'),
(354, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:43:12'),
(355, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:43:12'),
(356, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:43:12'),
(357, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:43:12'),
(358, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:43:12'),
(359, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:44:14'),
(360, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:44:14'),
(361, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:44:14'),
(362, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:44:14'),
(363, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:44:14'),
(364, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:44:14'),
(365, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:44:14'),
(366, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:44:14'),
(367, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:45:16'),
(368, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:45:16'),
(369, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:45:16'),
(370, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:45:16'),
(371, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:45:16'),
(372, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:45:16'),
(373, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:45:16'),
(374, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:45:16'),
(375, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:46:18'),
(376, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:46:18'),
(377, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:46:18'),
(378, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:46:18'),
(379, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:46:18'),
(380, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:46:18'),
(381, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:46:18'),
(382, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:46:18'),
(383, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:47:22'),
(384, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:47:22'),
(385, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:47:22'),
(386, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:47:22'),
(387, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:47:22'),
(388, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:47:22'),
(389, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:47:22'),
(390, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:47:22'),
(391, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:48:24'),
(392, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:48:24'),
(393, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:48:24'),
(394, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:48:24'),
(395, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:48:24'),
(396, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:48:24'),
(397, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:48:24'),
(398, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:48:24'),
(399, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:49:26'),
(400, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:49:26'),
(401, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:49:26'),
(402, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:49:26'),
(403, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:49:26'),
(404, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:49:26'),
(405, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:49:26'),
(406, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:49:26'),
(407, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:50:28'),
(408, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:50:28'),
(409, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:50:28'),
(410, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:50:28'),
(411, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:50:28'),
(412, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:50:28'),
(413, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:50:28'),
(414, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:50:28'),
(415, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:51:30'),
(416, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:51:30'),
(417, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:51:30'),
(418, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:51:30'),
(419, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:51:30'),
(420, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:51:30'),
(421, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:51:30'),
(422, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:51:30'),
(423, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:52:33'),
(424, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:52:33'),
(425, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:52:34'),
(426, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:52:34'),
(427, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:52:34'),
(428, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:52:34'),
(429, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:52:34'),
(430, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:52:34'),
(431, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:53:36'),
(432, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:53:36'),
(433, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:53:36'),
(434, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:53:36'),
(435, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:53:36'),
(436, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:53:36'),
(437, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:53:36'),
(438, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:53:36'),
(439, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:54:38'),
(440, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:54:38'),
(441, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:54:38'),
(442, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:54:38'),
(443, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:54:38'),
(444, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:54:38'),
(445, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:54:38'),
(446, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:54:38'),
(447, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:55:40'),
(448, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:55:40'),
(449, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:55:40'),
(450, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:55:40'),
(451, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:55:40'),
(452, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:55:40'),
(453, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:55:40'),
(454, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:55:40'),
(455, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:56:44'),
(456, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:56:44'),
(457, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:56:44'),
(458, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:56:44'),
(459, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:56:44'),
(460, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:56:44'),
(461, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:56:44'),
(462, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:56:44'),
(463, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:57:47'),
(464, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:57:47'),
(465, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:57:47'),
(466, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:57:47'),
(467, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:57:47'),
(468, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:57:47'),
(469, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:57:47'),
(470, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:57:47');
INSERT INTO `transactions` (`id`, `user_id`, `type`, `amount`, `status`, `description`, `reference_id`, `created_at`) VALUES
(471, 1, 'profit', 0.15, 'completed', 'Simulated profit from Platinum ($5.00)', NULL, '2026-05-18 07:58:50'),
(472, 1, 'profit', 0.07, 'completed', 'Simulated profit from Gold ($55.00)', NULL, '2026-05-18 07:58:50'),
(473, 1, 'profit', 0.01, 'completed', 'Simulated profit from Gold ($5.01)', NULL, '2026-05-18 07:58:50'),
(474, 1, 'profit', 2.34, 'completed', 'Simulated profit from Platinum ($78.00)', NULL, '2026-05-18 07:58:50'),
(475, 1, 'profit', 0.12, 'completed', 'Simulated profit from Gold ($100.00)', NULL, '2026-05-18 07:58:50'),
(476, 1, 'profit', 0.06, 'completed', 'Simulated profit from Gold ($50.00)', NULL, '2026-05-18 07:58:50'),
(477, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:58:50'),
(478, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:58:50'),
(479, 1, 'deposit', 78.00, 'completed', 'Investment Plan #2 cancelled by admin. Capital refunded to Main Wallet.', NULL, '2026-05-18 07:58:59'),
(480, 1, 'deposit', 5.00, 'completed', 'Investment Plan #2 cancelled by admin. Capital refunded to Main Wallet.', NULL, '2026-05-18 07:59:10'),
(481, 1, 'deposit', 50.00, 'completed', 'Investment Plan #4 cancelled by admin. Capital refunded to Main Wallet.', NULL, '2026-05-18 07:59:14'),
(482, 1, 'deposit', 100.00, 'completed', 'Investment Plan #4 cancelled by admin. Capital refunded to Main Wallet.', NULL, '2026-05-18 07:59:18'),
(483, 1, 'deposit', 5.01, 'completed', 'Investment Plan #4 cancelled by admin. Capital refunded to Main Wallet.', NULL, '2026-05-18 07:59:21'),
(484, 1, 'deposit', 55.00, 'completed', 'Investment Plan #4 cancelled by admin. Capital refunded to Main Wallet.', NULL, '2026-05-18 07:59:25'),
(485, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 07:59:53'),
(486, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 07:59:53'),
(487, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:00:55'),
(488, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:00:55'),
(489, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:01:57'),
(490, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:01:57'),
(491, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:02:59'),
(492, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:02:59'),
(493, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:04:01'),
(494, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:04:01'),
(495, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:05:04'),
(496, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:05:04'),
(497, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:06:07'),
(498, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:06:07'),
(499, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:07:10'),
(500, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:07:10'),
(501, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:08:13'),
(502, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:08:13'),
(503, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:09:16'),
(504, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:09:16'),
(505, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:10:18'),
(506, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:10:18'),
(507, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:11:21'),
(508, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:11:21'),
(509, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:12:24'),
(510, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:12:24'),
(511, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:13:26'),
(512, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:13:26'),
(513, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:14:28'),
(514, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:14:28'),
(515, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:15:30'),
(516, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:15:30'),
(517, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:16:32'),
(518, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:16:32'),
(519, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:17:36'),
(520, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:17:36'),
(521, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:18:38'),
(522, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:18:39'),
(523, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:19:41'),
(524, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:19:41'),
(525, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:20:44'),
(526, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:20:44'),
(527, 1, 'investment', 5.00, 'completed', 'Invested $5.00 in Gold', NULL, '2026-05-18 08:21:37'),
(528, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:21:50'),
(529, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:21:50'),
(530, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:21:50'),
(531, 1, 'investment', 4.00, 'completed', 'Invested $5.00 in Gold', NULL, '2026-05-18 08:22:44'),
(532, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:22:56'),
(533, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:22:56'),
(534, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:22:56'),
(535, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:24:02'),
(536, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:24:02'),
(537, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:24:02'),
(538, 1, 'deposit', 5.00, 'completed', 'Investment Plan #4 cancelled by admin. Capital refunded to Main Wallet.', NULL, '2026-05-18 08:24:10'),
(539, 1, 'investment', 5.00, 'completed', 'Invested $5.00 in Gold', NULL, '2026-05-18 08:24:21'),
(540, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:25:08'),
(541, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:25:08'),
(542, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:25:08'),
(543, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:26:12'),
(544, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:26:12'),
(545, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:26:12'),
(546, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:26:12'),
(547, 1, 'deposit', 30.00, 'rejected', 'Deposit via Crypto Wallet (Trx: 765767) - Pending Approval', NULL, '2026-05-18 08:26:57'),
(548, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:27:17'),
(549, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:27:17'),
(550, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:27:17'),
(551, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:27:17'),
(552, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:28:20'),
(553, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:28:20'),
(554, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:28:20'),
(555, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:28:20'),
(556, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:29:22'),
(557, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:29:22'),
(558, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:29:22'),
(559, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:29:22'),
(560, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:30:28'),
(561, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:30:28'),
(562, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:30:28'),
(563, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:30:28'),
(564, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:31:31'),
(565, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:31:31'),
(566, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:31:31'),
(567, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:31:31'),
(568, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:32:34'),
(569, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:32:34'),
(570, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:32:34'),
(571, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:32:34'),
(572, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:33:39'),
(573, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:33:39'),
(574, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:33:39'),
(575, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:33:39'),
(576, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:34:42'),
(577, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:34:42'),
(578, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:34:42'),
(579, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:34:42'),
(580, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:35:45'),
(581, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:35:45'),
(582, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:35:45'),
(583, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:35:45'),
(584, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:36:48'),
(585, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:36:48'),
(586, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:36:48'),
(587, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:36:48'),
(588, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:37:50'),
(589, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:37:50'),
(590, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:37:50'),
(591, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:37:50'),
(592, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:38:52'),
(593, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:38:52'),
(594, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:38:52'),
(595, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:38:52'),
(596, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:39:54'),
(597, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:39:54'),
(598, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:39:54'),
(599, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:39:54'),
(600, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:40:56'),
(601, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:40:56'),
(602, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:40:56'),
(603, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:40:56'),
(604, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:42:05'),
(605, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:42:05'),
(606, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:42:05'),
(607, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:42:05'),
(608, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:43:07'),
(609, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:43:07'),
(610, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:43:07'),
(611, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:43:07'),
(612, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:44:10'),
(613, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:44:10'),
(614, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:44:10'),
(615, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:44:10'),
(616, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:45:15'),
(617, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:45:15'),
(618, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:45:15'),
(619, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:45:15'),
(620, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:46:18'),
(621, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:46:18'),
(622, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:46:18'),
(623, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:46:18'),
(624, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:47:20'),
(625, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:47:20'),
(626, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:47:20'),
(627, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:47:20'),
(628, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:48:23'),
(629, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:48:23'),
(630, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:48:23'),
(631, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:48:23'),
(632, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:49:25'),
(633, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:49:25'),
(634, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:49:25'),
(635, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:49:25'),
(636, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:50:28'),
(637, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:50:28'),
(638, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:50:28'),
(639, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:50:28'),
(640, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:51:33'),
(641, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:51:33'),
(642, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:51:33'),
(643, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:51:33'),
(644, 1, 'deposit', 100.00, 'completed', 'Deposit via Jazz Cash Wallet (Trx: 76765) - Pending Approval', NULL, '2026-05-18 08:51:45'),
(645, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:52:36'),
(646, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:52:36'),
(647, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:52:36'),
(648, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:52:36'),
(649, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:53:38'),
(650, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:53:38'),
(651, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:53:38'),
(652, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:53:38'),
(653, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:54:41'),
(654, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:54:41'),
(655, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:54:41'),
(656, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:54:41'),
(657, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:55:43'),
(658, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:55:43'),
(659, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:55:43'),
(660, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:55:43'),
(661, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:56:46'),
(662, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:56:46'),
(663, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:56:46'),
(664, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:56:46'),
(665, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:57:48'),
(666, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:57:48'),
(667, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:57:48'),
(668, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:57:48'),
(669, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:58:50'),
(670, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:58:50'),
(671, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:58:50'),
(672, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:58:50'),
(673, 1, 'deposit', 100.00, 'completed', 'Deposit via Jazz Cash Wallet (Trx: 7676565) - Pending Approval', NULL, '2026-05-18 08:59:53'),
(674, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 08:59:54'),
(675, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 08:59:54'),
(676, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 08:59:54'),
(677, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 08:59:54'),
(678, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 09:00:57'),
(679, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 09:00:57'),
(680, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 09:00:57'),
(681, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 09:00:57'),
(682, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 09:01:59'),
(683, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 09:01:59'),
(684, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 09:01:59'),
(685, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 09:01:59'),
(686, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 09:03:01'),
(687, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 09:03:01'),
(688, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 09:03:01'),
(689, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 09:03:01'),
(690, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 09:04:05'),
(691, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 09:04:05'),
(692, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 09:04:05'),
(693, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 09:04:05'),
(694, 1, 'deposit', 30.00, 'pending', 'Deposit via Jazz Cash Wallet (Trx: 434535) - Pending Approval', NULL, '2026-05-18 09:04:44'),
(695, 1, 'investment', 45.00, 'pending', 'Invested $45.00 in Gold - Pending Admin Approval', NULL, '2026-05-18 09:04:54'),
(696, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 09:05:09'),
(697, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 09:05:09'),
(698, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 09:05:09'),
(699, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 09:05:09'),
(700, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 09:06:16'),
(701, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 09:06:16'),
(702, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 09:06:16'),
(703, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 09:06:16'),
(704, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:28:03'),
(705, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:28:03'),
(706, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:28:03'),
(707, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:28:03'),
(708, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:29:04'),
(709, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:29:04'),
(710, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:29:04'),
(711, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:29:04'),
(712, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:30:06'),
(713, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:30:06'),
(714, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:30:06'),
(715, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:30:06'),
(716, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:31:08'),
(717, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:31:08'),
(718, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:31:08'),
(719, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:31:08'),
(720, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:32:10'),
(721, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:32:10'),
(722, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:32:10'),
(723, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:32:10'),
(724, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:33:12'),
(725, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:33:12'),
(726, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:33:12'),
(727, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:33:12'),
(728, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:34:14'),
(729, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:34:14'),
(730, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:34:14'),
(731, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:34:14'),
(732, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:35:17'),
(733, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:35:17'),
(734, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:35:17'),
(735, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:35:17'),
(736, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:36:19'),
(737, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:36:19'),
(738, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:36:19'),
(739, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:36:19'),
(740, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:37:22'),
(741, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:37:22'),
(742, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:37:22'),
(743, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:37:22'),
(744, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:38:24'),
(745, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:38:24'),
(746, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:38:24'),
(747, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:38:24'),
(748, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:39:29'),
(749, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:39:29'),
(750, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:39:29'),
(751, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:39:29'),
(752, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:52:23'),
(753, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:52:23'),
(754, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:52:23'),
(755, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:52:23'),
(756, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:53:25'),
(757, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:53:26'),
(758, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:53:26'),
(759, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:53:26'),
(760, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:54:30'),
(761, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:54:30'),
(762, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:54:30'),
(763, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:54:30'),
(764, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:55:33'),
(765, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:55:33'),
(766, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:55:33'),
(767, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:55:33'),
(768, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:56:36'),
(769, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:56:36'),
(770, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:56:36'),
(771, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:56:36'),
(772, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:57:39'),
(773, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:57:39'),
(774, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:57:39'),
(775, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:57:39'),
(776, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:58:42'),
(777, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:58:42'),
(778, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:58:42'),
(779, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:58:42'),
(780, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 10:59:45'),
(781, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 10:59:45'),
(782, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 10:59:45'),
(783, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 10:59:45'),
(784, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:00:49'),
(785, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:00:49'),
(786, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:00:49'),
(787, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:00:49'),
(788, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:01:51'),
(789, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:01:51'),
(790, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:01:51'),
(791, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:01:51'),
(792, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:02:55'),
(793, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:02:55'),
(794, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:02:55'),
(795, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:02:55'),
(796, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:03:58'),
(797, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:03:58'),
(798, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:03:58'),
(799, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:03:58'),
(800, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:05:00'),
(801, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:05:00'),
(802, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:05:00'),
(803, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:05:00'),
(804, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:06:02'),
(805, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:06:02'),
(806, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:06:02'),
(807, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:06:02'),
(808, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:07:04'),
(809, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:07:04'),
(810, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:07:04'),
(811, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:07:04'),
(812, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:08:06'),
(813, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:08:06'),
(814, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:08:06'),
(815, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:08:06'),
(816, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:09:09'),
(817, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:09:09'),
(818, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:09:09'),
(819, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:09:09'),
(820, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:10:13'),
(821, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:10:13'),
(822, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:10:13'),
(823, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:10:13'),
(824, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:11:15'),
(825, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:11:15'),
(826, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:11:15'),
(827, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:11:15'),
(828, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:12:18'),
(829, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:12:18'),
(830, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:12:18'),
(831, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:12:18'),
(832, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:13:21'),
(833, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:13:21'),
(834, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:13:21'),
(835, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:13:21'),
(836, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:14:25'),
(837, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:14:25'),
(838, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:14:25'),
(839, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:14:25'),
(840, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:15:28'),
(841, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:15:28'),
(842, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:15:28'),
(843, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:15:28'),
(844, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:16:31'),
(845, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:16:31'),
(846, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:16:31'),
(847, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:16:31'),
(848, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:17:36'),
(849, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:17:36'),
(850, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:17:36'),
(851, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:17:36'),
(852, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:18:41'),
(853, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:18:41'),
(854, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:18:41'),
(855, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:18:41'),
(856, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:19:43'),
(857, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:19:43'),
(858, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:19:43'),
(859, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:19:43'),
(860, 1, 'profit', 5.00, 'completed', 'Salary Claim Approved for V1 Manager', NULL, '2026-05-18 11:20:43'),
(861, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:20:47'),
(862, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:20:47'),
(863, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:20:47'),
(864, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:20:47'),
(865, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:21:49'),
(866, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:21:49'),
(867, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:21:49'),
(868, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:21:49'),
(869, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:22:51'),
(870, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:22:51'),
(871, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:22:51'),
(872, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:22:51'),
(873, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:23:54'),
(874, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:23:54'),
(875, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:23:54'),
(876, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:23:54'),
(877, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:24:57'),
(878, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:24:57'),
(879, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:24:57'),
(880, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:24:57'),
(881, 1, 'profit', 5.00, 'completed', 'Salary Claim Approved for V1 Manager', NULL, '2026-05-18 11:25:52'),
(882, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 11:26:02'),
(883, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 11:26:02'),
(884, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 11:26:02'),
(885, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 11:26:02'),
(886, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 14:42:30'),
(887, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 14:42:30'),
(888, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 14:42:30'),
(889, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 14:42:30'),
(890, 8, 'profit', 40.00, 'completed', 'Simulated profit from Gold ($200.00)', NULL, '2026-05-18 14:43:33'),
(891, 8, 'profit', 3.00, 'completed', 'Simulated profit from Gold ($15.00)', NULL, '2026-05-18 14:43:33'),
(892, 1, 'profit', 0.80, 'completed', 'Simulated profit from Gold ($4.00)', NULL, '2026-05-18 14:43:33'),
(893, 1, 'profit', 1.00, 'completed', 'Simulated profit from Gold ($5.00)', NULL, '2026-05-18 14:43:33'),
(894, 1, 'withdrawal', 10.00, 'completed', 'Withdrawal of $10.00 via Jazz Cash Wallet (A/C: 1234567889) - Pending Approval', 2, '2026-05-18 15:06:56'),
(895, 1, 'withdrawal', 5.00, 'pending', 'Withdrawal of $5.00 via Crypto Wallet (A/C: 22222) - Pending Approval', 3, '2026-05-18 15:09:13'),
(896, 1, 'deposit', 250.00, 'pending', 'Deposit via Jazz Cash Wallet (Trx: 2342342) - Pending Approval', NULL, '2026-05-18 15:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `referral_code` varchar(50) DEFAULT NULL,
  `referred_by` varchar(50) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive','banned') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `is_verified` tinyint(1) DEFAULT 0,
  `admin_remarks` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `profile_image`, `password`, `referral_code`, `referred_by`, `role`, `status`, `created_at`, `is_active`, `is_verified`, `admin_remarks`, `last_login`) VALUES
(1, 'Wania', 'waniashahid1818@gmail.com', '+92312256068', 'uploads/avatars/avatar_1_1779028544.png', '$2y$10$8HK2E4fIfx6W79SLHKvRMet7fGoEMZlfC0gQ5KfmE.nhvaWWL.ure', 'A2DF282E', '', 'admin', 'active', '2026-05-15 16:59:15', 1, 1, '', '2026-05-19 12:21:10'),
(6, 'Test User', 'user@gmail.com', '03001234567', NULL, '$2y$10$8mHBH.rV7/S2BUEciiCn6uUuAVYAJ725aklcj7mU2wkU/VLSdp2g6', '60CEC958', '', 'user', 'active', '2026-05-18 05:53:04', 1, 0, NULL, '2026-05-18 10:54:17'),
(7, 'Admin User', 'admin@gmail.com', '03001234568', NULL, '$2y$10$t1uJU7UHhJqayOwe9q6w2eVO6lC9qC22uQdRZHD5Mx33VOpvE9kTW', '441B754A', '', 'user', 'active', '2026-05-18 05:59:50', 0, 0, '', NULL),
(8, 'Uriah Valentine', 'rahufipoze@mailinator.com', '+1 (498) 694-6101', NULL, '$2y$10$.tKRxFLsNiuvVjwPI1GgoeJtEjjJr.BDF1.PB8u2ZKunE2.TICO1O', '23E39EDB', 'A2DF282E', 'user', 'active', '2026-05-18 06:16:34', 1, 0, NULL, '2026-05-18 11:54:04');

-- --------------------------------------------------------

--
-- Table structure for table `user_investments`
--

CREATE TABLE `user_investments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `last_earning_at` timestamp NULL DEFAULT current_timestamp(),
  `status` enum('active','completed') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `deposit_balance` decimal(15,2) DEFAULT 0.00,
  `earning_balance` decimal(15,6) DEFAULT 0.000000,
  `total_invested` decimal(15,2) DEFAULT 0.00,
  `total_withdrawn` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`id`, `user_id`, `deposit_balance`, `earning_balance`, `total_invested`, `total_withdrawn`) VALUES
(1, 1, 1026.00, 337.170108, 307.01, 15.00),
(6, 6, 0.00, 0.000000, 0.00, 0.00),
(7, 7, 0.00, 0.000000, 0.00, 0.00),
(8, 8, 285.00, 7127.000000, 215.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `withdrawals`
--

CREATE TABLE `withdrawals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `method` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `net_amount` decimal(15,2) DEFAULT NULL,
  `account_title` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `admin_remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `withdrawals`
--

INSERT INTO `withdrawals` (`id`, `user_id`, `amount`, `method`, `status`, `created_at`, `net_amount`, `account_title`, `account_number`, `reviewed_by`, `reviewed_at`, `admin_remarks`) VALUES
(2, 1, 10.00, 'Jazz Cash Wallet', 'approved', '2026-05-18 15:06:56', 10.00, 'wania', '1234567889', 1, '2026-05-18 15:07:13', NULL),
(3, 1, 5.00, 'Crypto Wallet', 'pending', '2026-05-18 15:09:13', 5.00, '22222', '22222', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `investments`
--
ALTER TABLE `investments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`),
  ADD KEY `idx_identifier` (`identifier`),
  ADD KEY `idx_order_id` (`order_id`);

--
-- Indexes for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `payout_banks`
--
ALTER TABLE `payout_banks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `payout_merchants`
--
ALTER TABLE `payout_merchants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `plans`
--
ALTER TABLE `plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrer_id` (`referrer_id`),
  ADD KEY `referee_id` (`referee_id`);

--
-- Indexes for table `referral_settings`
--
ALTER TABLE `referral_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level` (`level`);

--
-- Indexes for table `salary_claims`
--
ALTER TABLE `salary_claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `rank_id` (`rank_id`);

--
-- Indexes for table `salary_ranks`
--
ALTER TABLE `salary_ranks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rank_name` (`rank_name`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `user_investments`
--
ALTER TABLE `user_investments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `investments`
--
ALTER TABLE `investments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payment_gateways`
--
ALTER TABLE `payment_gateways`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payout_banks`
--
ALTER TABLE `payout_banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payout_merchants`
--
ALTER TABLE `payout_merchants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `plans`
--
ALTER TABLE `plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `referral_settings`
--
ALTER TABLE `referral_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `salary_claims`
--
ALTER TABLE `salary_claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `salary_ranks`
--
ALTER TABLE `salary_ranks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=897;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_investments`
--
ALTER TABLE `user_investments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallets`
--
ALTER TABLE `wallets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `withdrawals`
--
ALTER TABLE `withdrawals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deposits`
--
ALTER TABLE `deposits`
  ADD CONSTRAINT `deposits_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `investments`
--
ALTER TABLE `investments`
  ADD CONSTRAINT `investments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referee_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_claims`
--
ALTER TABLE `salary_claims`
  ADD CONSTRAINT `salary_claims_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `salary_claims_ibfk_2` FOREIGN KEY (`rank_id`) REFERENCES `salary_ranks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_investments`
--
ALTER TABLE `user_investments`
  ADD CONSTRAINT `user_investments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_investments_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `wallets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdrawals`
--
ALTER TABLE `withdrawals`
  ADD CONSTRAINT `withdrawals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
