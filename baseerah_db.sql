-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 14 أكتوبر 2026 الساعة 17:03
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `baseerah_db`
--

-- --------------------------------------------------------

--
-- بنية الجدول `dosage_schedules`
--

CREATE TABLE `dosage_schedules` (
  `schedule_id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `dose_amount` decimal(5,2) NOT NULL,
  `dose_unit` varchar(30) DEFAULT 'حبة',
  `dosage_time` time NOT NULL,
  `dosage_date` date NOT NULL,
  `status` enum('pending','taken','missed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `dosage_schedules`
--

INSERT INTO `dosage_schedules` (`schedule_id`, `medication_id`, `dose_amount`, `dose_unit`, `dosage_time`, `dosage_date`, `status`) VALUES
(1, 1, 1.00, 'حبة', '08:00:00', '2026-10-05', 'pending'),
(2, 1, 1.00, 'حبة', '20:00:00', '2026-10-05', 'pending'),
(3, 2, 1.00, 'حبة', '09:00:00', '2026-10-05', 'pending');

-- --------------------------------------------------------

--
-- بنية الجدول `medications`
--

CREATE TABLE `medications` (
  `medication_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medication_name` varchar(100) NOT NULL,
  `medication_image` varchar(255) DEFAULT NULL,
  `medication_description` text DEFAULT NULL,
  `medication_color` varchar(50) DEFAULT NULL,
  `medication_shape` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `medications`
--

INSERT INTO `medications` (`medication_id`, `user_id`, `medication_name`, `medication_image`, `medication_description`, `medication_color`, `medication_shape`) VALUES
(1, 1, 'Panadol', 'panadol.jpg', 'مسكن للألم', 'أبيض', 'دائري'),
(2, 1, 'Vitamin D', 'vitamin_d.jpg', 'مكمل فيتامين د', 'أصفر', 'دائري');

-- --------------------------------------------------------

--
-- بنية الجدول `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `schedule_id` int(11) DEFAULT NULL,
  `notification_type` varchar(50) DEFAULT NULL,
  `notification_text` text NOT NULL,
  `notification_time` datetime NOT NULL,
  `audio_text` text DEFAULT NULL,
  `email_status` enum('pending','sent','failed') DEFAULT 'pending',
  `notification_image` varchar(255) DEFAULT NULL,
  `notification_sound` varchar(255) DEFAULT NULL,
  `in_site_status` enum('unread','read') DEFAULT 'unread',
  `read_at` datetime DEFAULT NULL,
  `email_sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `schedule_id`, `notification_type`, `notification_text`, `notification_time`, `audio_text`, `email_status`, `notification_image`, `notification_sound`, `in_site_status`, `read_at`, `email_sent_at`) VALUES
(1, 1, 1, 'دواء', 'حان موعد تناول البانادول', '2026-10-05 08:00:00', 'حان موعد دواء بانادول، يرجى تناول حبة واحدة', 'pending', NULL, NULL, 'unread', NULL, NULL),
(2, 1, 3, 'دواء', 'حان موعد تناول فيتامين د', '2026-10-05 09:00:00', 'حان موعد تناول فيتامين د', 'pending', NULL, NULL, 'unread', NULL, NULL),
(3, 4, 1, 'دواء', 'حان موعد تناول دواء المريض', '2026-10-05 08:00:00', 'حان موعد دواء بانادول للمريض', 'pending', NULL, NULL, 'unread', NULL, NULL);

-- --------------------------------------------------------

--
-- بنية الجدول `patient_dependents`
--

CREATE TABLE `patient_dependents` (
  `id` int(11) NOT NULL,
  `patient_user_id` int(11) NOT NULL,
  `dependent_user_id` int(11) NOT NULL,
  `relationship` varchar(50) DEFAULT NULL,
  `status` enum('pending','active','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `patient_dependents`
--

INSERT INTO `patient_dependents` (`id`, `patient_user_id`, `dependent_user_id`, `relationship`, `status`, `created_at`) VALUES
(1, 1, 4, 'ابنة', 'active', '2026-10-04 20:36:39');

-- --------------------------------------------------------

--
-- بنية الجدول `subscriptions`
--

CREATE TABLE `subscriptions` (
  `subscription_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_type` enum('free','monthly','yearly') DEFAULT 'free',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `subscription_status` enum('active','expired','cancelled') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `subscriptions`
--

INSERT INTO `subscriptions` (`subscription_id`, `user_id`, `plan_type`, `start_date`, `end_date`, `subscription_status`) VALUES
(1, 1, 'free', '2026-10-04', '2026-10-18', 'active');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `account_type` enum('patient','dependent','admin') NOT NULL DEFAULT 'patient',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `phone`, `email`, `birth_date`, `account_type`, `created_at`) VALUES
(1, 'user1', 'demo_hash_1', '0500000001', 'user1@example.com', '2004-05-10', 'patient', '2026-10-04 07:44:40'),
(3, 'admin', 'demo_hash_3', '0500000003', 'admin@example.com', '1990-01-01', 'admin', '2026-10-04 07:44:40'),
(4, 'user3', 'demo_hash_4', '0500000004', 'user3@example.com', '2002-04-12', 'dependent', '2026-10-04 20:36:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `dosage_schedules`
--
ALTER TABLE `dosage_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `medication_id` (`medication_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medication_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `patient_dependents`
--
ALTER TABLE `patient_dependents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `patient_user_id` (`patient_user_id`,`dependent_user_id`),
  ADD KEY `dependent_user_id` (`dependent_user_id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`subscription_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dosage_schedules`
--
ALTER TABLE `dosage_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medication_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patient_dependents`
--
ALTER TABLE `patient_dependents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `dosage_schedules`
--
ALTER TABLE `dosage_schedules`
  ADD CONSTRAINT `dosage_schedules_ibfk_1` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`medication_id`) ON DELETE CASCADE;

--
-- قيود الجداول `medications`
--
ALTER TABLE `medications`
  ADD CONSTRAINT `medications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- قيود الجداول `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`schedule_id`) REFERENCES `dosage_schedules` (`schedule_id`) ON DELETE SET NULL;

--
-- قيود الجداول `patient_dependents`
--
ALTER TABLE `patient_dependents`
  ADD CONSTRAINT `patient_dependents_ibfk_1` FOREIGN KEY (`patient_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `patient_dependents_ibfk_2` FOREIGN KEY (`dependent_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- قيود الجداول `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
