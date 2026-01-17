-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 12, 2026 at 03:44 PM
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
-- Database: `gsm_health_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_notification_reads`
--

CREATE TABLE `admin_notification_reads` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `kind` varchar(32) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sanitary_permit_steps`
--

CREATE TABLE `sanitary_permit_steps` (
  `id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `step` enum('form_filing','submission','payment','inspection','issuance') NOT NULL,
  `status` enum('pending','completed','rejected','rescheduled') DEFAULT 'pending',
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notification_reads`
--

INSERT INTO `admin_notification_reads` (`id`, `user_id`, `kind`, `item_id`, `created_at`) VALUES
(1, 6, 'service_request', 1, '2026-01-12 13:27:20');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `birth_date` date NOT NULL,
  `gender` enum('male','female','other','prefer-not-to-say') NOT NULL,
  `civil_status` enum('single','married','divorced','widowed') NOT NULL,
  `address` text NOT NULL,
  `appointment_type` varchar(100) NOT NULL,
  `preferred_date` date NOT NULL,
  `health_concerns` text NOT NULL,
  `medical_history` text NOT NULL,
  `current_medications` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `emergency_contact_name` varchar(100) NOT NULL,
  `emergency_contact_phone` varchar(20) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `role`, `action`, `details`, `ip`, `user_agent`, `created_at`) VALUES
(1, 7, 'citizen', 'logout', 'Logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-12 13:28:24'),
(2, 8, 'doctor', 'profile_update', '{\"first_name\":\"Doctor\",\"last_name\":\"Numberone\",\"email\":\"gonfreecs01192000@gmail.com\",\"phone\":\"09298987413\",\"address\":\"Quezon City\",\"date_of_birth\":\"2000-01-12\",\"gender\":\"male\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-12 14:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','read','archived') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `login_otps`
--

CREATE TABLE `login_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int(11) DEFAULT 0,
  `verified_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_otps`
--

INSERT INTO `login_otps` (`id`, `email`, `otp_hash`, `expires_at`, `attempts`, `verified_at`, `created_at`) VALUES
(38, 'gonfreecs01192000@gmail.com', '$2y$10$KYoOgdI/N0Rfzz8m4rjt/Oc/IBp6kDAFh8nJCwuE0Ks4fgTBMsNWG', '2026-01-12 15:08:02', 0, '2026-01-12 21:58:27', '2026-01-12 13:58:02');

-- --------------------------------------------------------

--
-- Table structure for table `login_table`
--

CREATE TABLE `login_table` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `account_type` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `login_table`
--

INSERT INTO `login_table` (`id`, `email`, `password`, `account_type`, `created_at`) VALUES
(1, 'admin@test.com', 'admin123', 1, '2025-12-30 06:05:06'),
(2, 'doctor@test.com', 'doctor123', 2, '2025-12-30 06:05:06'),
(3, 'nurse@test.com', 'nurse123', 3, '2025-12-30 06:05:06');

-- --------------------------------------------------------

--
-- Table structure for table `service_requests`
--

CREATE TABLE `service_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `service_details` text NOT NULL,
  `preferred_date` date DEFAULT NULL,
  `urgency` enum('low','medium','high','emergency') DEFAULT 'medium',
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `service_requests`
--

INSERT INTO `service_requests` (`id`, `user_id`, `service_type`, `full_name`, `email`, `phone`, `address`, `service_details`, `preferred_date`, `urgency`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 7, 'vaccination', 'Lebron James', 'xlelouchlamperouge2024@gmail.com', '123123123', 'QC', 'qweqwe\n\nAdditional Information:\n- Vaccine Type: Childhood Immunization\n- Age: 22\n- Captcha Input: JVYZ3J', '2026-01-12', 'medium', 'pending', '2026-01-12 13:27:07', '2026-01-12 13:27:07', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','nurse','inspector','citizen') DEFAULT 'citizen',
  `status` enum('active','blocked','inactive','pending') DEFAULT 'active',
  `profile_picture` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `verification_status` enum('unverified','pending','verified','rejected') DEFAULT 'unverified',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `role`, `status`, `profile_picture`, `phone`, `address`, `date_of_birth`, `gender`, `verification_status`, `created_at`, `updated_at`, `last_login`) VALUES
(4, 'pitsss', 'santos', 'petersantos39653965@gmail.com', '$2y$10$owddJv1JEM1/ZV9J9834NuQncCZ/K5TwK.w8ADABv0M17.zdsur1y', 'citizen', 'active', NULL, '09708212069', '90 area 2 republic ave. brgy. holy spirit q.c', NULL, NULL, 'unverified', '2025-12-30 16:45:32', '2025-12-30 06:45:32', NULL),
(5, 'vixet', 'vivi', 'yehoto1845@dubokutv.com', '$2y$10$sLRRfEnhPaFPNq1LGXvjGOI3ZZDy.gFWBzds4cCzReoMQRVMaav4G', 'citizen', 'active', NULL, '09708212069', '123 brgy biglangliko', NULL, NULL, 'unverified', '2026-01-03 13:23:25', '2026-01-03 20:23:25', NULL),
(6, 'Admin', 'User', 'health.sanitation1@gmail.com', '$2y$10$esMbm8VDNeCOu.xAH.0a8.9HdYiXKQaPrMU5DqR6prWrNfFa91f5y', 'admin', 'active', NULL, NULL, NULL, NULL, NULL, 'unverified', '2026-01-12 12:59:31', '2026-01-12 12:59:31', NULL),
(7, 'Kelvin', 'Bustalinio', 'yasuocancer1989@gmail.com', '$2y$10$D17FmeqaPDPtSroW3j4sHuFZkslMgxQZ59TNOxzx2nqZ3Ks1fVyDC', 'citizen', 'active', NULL, '12312313', 'Quezon City', NULL, NULL, 'verified', '2026-01-12 06:02:47', '2026-01-12 13:24:06', NULL),
(8, 'Doctor', 'Numberone', 'gonfreecs01192000@gmail.com', '$2y$10$de2PVoXNuktR8jo9zJ/g7uENPPfmsm9QSXmkFPghwiGzN/QbLklG6', 'doctor', 'active', NULL, '09298987413', 'Quezon City', '2000-01-12', 'male', 'unverified', '2026-01-12 13:56:05', '2026-01-12 14:27:11', NULL),
(10, 'Nurse', 'Numberone', 'donfreecs01192000@gmail.com', '$2y$10$D0C4bZql4MCKwoE7qqP.6OXf8X8kk6UZKcz4pA3Fv27CMzToSYocO', 'nurse', 'active', NULL, NULL, NULL, NULL, NULL, 'unverified', '2026-01-12 14:30:30', '2026-01-12 14:30:30', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_verifications`
--

CREATE TABLE `user_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_verifications`
--

INSERT INTO `user_verifications` (`id`, `user_id`, `document_type`, `file_path`, `status`, `notes`, `reviewed_by`, `reviewed_at`, `created_at`, `updated_at`) VALUES
(1, 7, 'national_id', 'uploads/verifications/uid7_20260112_142345_7fdcc84c.pdf', 'verified', 'Verified by admin', 6, '2026-01-12 13:27:18', '2026-01-12 13:23:45', '2026-01-12 13:27:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_notification_reads`
--
ALTER TABLE `admin_notification_reads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_user_item` (`user_id`,`kind`,`item_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_appointments_deleted_at` (`deleted_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `role` (`role`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_otps`
--
ALTER TABLE `login_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_verified_at` (`verified_at`),
  ADD KEY `idx_email_expires` (`email`,`expires_at`);

--
-- Indexes for table `login_table`
--
ALTER TABLE `login_table`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_service_requests_deleted_at` (`deleted_at`);

-- --------------------------------------------------------

--
-- Table structure for table `sanitary_permit_applications`
--

CREATE TABLE `sanitary_permit_applications` (
  `id` int(11) NOT NULL,
  `service_request_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `app_type` varchar(50) DEFAULT NULL,
  `industry` varchar(50) DEFAULT NULL,
  `sub_industry` varchar(100) DEFAULT NULL,
  `business_line` varchar(150) DEFAULT NULL,
  `establishment_name` varchar(255) NOT NULL,
  `establishment_address` text DEFAULT NULL,
  `owner_name` varchar(150) DEFAULT NULL,
  `mayor_permit` varchar(100) DEFAULT NULL,
  `total_employees` int(11) DEFAULT NULL,
  `employees_with_health_cert` int(11) DEFAULT NULL,
  `employees_without_health_cert` int(11) DEFAULT NULL,
  `ppe_personnel` int(11) DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

-- Indexes for table `sanitary_permit_applications`
ALTER TABLE `sanitary_permit_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `service_request_id` (`service_request_id`);

-- Indexes for table `sanitary_permit_steps`
ALTER TABLE `sanitary_permit_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `step` (`step`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_notification_reads`
--
ALTER TABLE `admin_notification_reads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `login_otps`
--
ALTER TABLE `login_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `login_table`
--
ALTER TABLE `login_table`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `service_requests`
--
ALTER TABLE `service_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- AUTO_INCREMENT for table `sanitary_permit_applications`
ALTER TABLE `sanitary_permit_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `sanitary_permit_steps`
ALTER TABLE `sanitary_permit_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_verifications`
--
ALTER TABLE `user_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_requests`
--
ALTER TABLE `service_requests`
  ADD CONSTRAINT `service_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

-- Constraints for table `sanitary_permit_applications`
ALTER TABLE `sanitary_permit_applications`
  ADD CONSTRAINT `sanitary_permit_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sanitary_permit_applications_ibfk_2` FOREIGN KEY (`service_request_id`) REFERENCES `service_requests` (`id`) ON DELETE SET NULL;

-- Constraints for table `sanitary_permit_steps`
ALTER TABLE `sanitary_permit_steps`
  ADD CONSTRAINT `sanitary_permit_steps_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `sanitary_permit_applications` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sanitary_permit_steps_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_verifications`
--
ALTER TABLE `user_verifications`
  ADD CONSTRAINT `user_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
