-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 09:29 AM
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
-- Database: `behavior_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_behavior_logs`
--

CREATE TABLE `tb_behavior_logs` (
  `id` bigint(20) NOT NULL,
  `behavior_report_id` bigint(20) NOT NULL,
  `action_type` enum('create','update','delete') NOT NULL,
  `performed_by` bigint(20) NOT NULL,
  `before_change` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_change`)),
  `after_change` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_change`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_behavior_reports`
--

CREATE TABLE `tb_behavior_reports` (
  `reports_id` bigint(20) NOT NULL,
  `student_id` bigint(20) NOT NULL,
  `teacher_id` bigint(20) NOT NULL,
  `violation_id` bigint(20) NOT NULL,
  `reports_description` text DEFAULT NULL,
  `reports_evidence_path` varchar(255) DEFAULT NULL,
  `reports_report_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_classes`
--

CREATE TABLE `tb_classes` (
  `classes_id` bigint(20) NOT NULL,
  `classes_level` varchar(10) DEFAULT NULL,
  `classes_room_number` varchar(10) DEFAULT NULL,
  `classes_academic_year` varchar(10) DEFAULT NULL,
  `teachers_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_guardians`
--

CREATE TABLE `tb_guardians` (
  `guardians_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `guardians_relationship_to_student` varchar(50) DEFAULT NULL,
  `guardians_phone` varchar(20) DEFAULT NULL,
  `guardians_email` varchar(150) DEFAULT NULL,
  `guardians_line_id` varchar(100) DEFAULT NULL,
  `guardians_preferred_contact_method` enum('phone','email','line') DEFAULT NULL,
  `guardians_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_guardian_student`
--

CREATE TABLE `tb_guardian_student` (
  `guardian_student_id` bigint(20) NOT NULL,
  `guardian_id` bigint(20) DEFAULT NULL,
  `student_id` bigint(20) DEFAULT NULL,
  `guardian_student_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_notifications`
--

CREATE TABLE `tb_notifications` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_students`
--

CREATE TABLE `tb_students` (
  `students_id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `students_student_code` varchar(20) DEFAULT NULL,
  `class_id` bigint(20) DEFAULT NULL,
  `students_academic_year` varchar(10) DEFAULT NULL,
  `students_current_score` int(11) DEFAULT NULL,
  `students_status` enum('active','suspended','expelled','graduated','transferred') DEFAULT NULL,
  `students_gender` enum('male','female','other') DEFAULT NULL,
  `students_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_teachers`
--

CREATE TABLE `tb_teachers` (
  `teachers_id` bigint(20) NOT NULL,
  `users_id` bigint(20) NOT NULL,
  `teachers_employee_code` varchar(20) DEFAULT NULL,
  `teachers_position` varchar(50) DEFAULT NULL,
  `teachers_department` varchar(100) DEFAULT NULL,
  `teachers_major` varchar(100) DEFAULT NULL,
  `teachers_is_homeroom_teacher` tinyint(1) DEFAULT NULL,
  `assigned_class_id` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_users`
--

CREATE TABLE `tb_users` (
  `users_id` bigint(20) NOT NULL,
  `users_name_prefix` varchar(20) DEFAULT NULL,
  `users_first_name` varchar(100) DEFAULT NULL,
  `users_last_name` varchar(100) DEFAULT NULL,
  `users_email` varchar(150) DEFAULT NULL,
  `users_phone_number` varchar(20) DEFAULT NULL,
  `users_password` varchar(255) DEFAULT NULL,
  `users_role` enum('admin','teacher','student','guardian') DEFAULT NULL,
  `users_profile_image` varchar(255) DEFAULT NULL,
  `users_birthdate` date DEFAULT NULL,
  `users_created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `users_updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_violations`
--

CREATE TABLE `tb_violations` (
  `violations_id` bigint(20) NOT NULL,
  `violations_name` varchar(150) DEFAULT NULL,
  `violations_description` text DEFAULT NULL,
  `violations_category` enum('light','medium','severe') DEFAULT NULL,
  `violations_points_deducted` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `tb_behavior_logs`
--
ALTER TABLE `tb_behavior_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `behavior_report_id` (`behavior_report_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `tb_behavior_reports`
--
ALTER TABLE `tb_behavior_reports`
  ADD PRIMARY KEY (`reports_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `violation_id` (`violation_id`);

--
-- Indexes for table `tb_classes`
--
ALTER TABLE `tb_classes`
  ADD PRIMARY KEY (`classes_id`),
  ADD KEY `fk_class_teacher` (`teachers_id`);

--
-- Indexes for table `tb_guardians`
--
ALTER TABLE `tb_guardians`
  ADD PRIMARY KEY (`guardians_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tb_guardian_student`
--
ALTER TABLE `tb_guardian_student`
  ADD PRIMARY KEY (`guardian_student_id`),
  ADD KEY `guardian_id` (`guardian_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `tb_notifications`
--
ALTER TABLE `tb_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tb_students`
--
ALTER TABLE `tb_students`
  ADD PRIMARY KEY (`students_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `class_id` (`class_id`);

--
-- Indexes for table `tb_teachers`
--
ALTER TABLE `tb_teachers`
  ADD PRIMARY KEY (`teachers_id`),
  ADD KEY `user_id` (`users_id`),
  ADD KEY `assigned_class_id` (`assigned_class_id`);

--
-- Indexes for table `tb_users`
--
ALTER TABLE `tb_users`
  ADD PRIMARY KEY (`users_id`),
  ADD UNIQUE KEY `email` (`users_email`);

--
-- Indexes for table `tb_violations`
--
ALTER TABLE `tb_violations`
  ADD PRIMARY KEY (`violations_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_behavior_logs`
--
ALTER TABLE `tb_behavior_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_behavior_reports`
--
ALTER TABLE `tb_behavior_reports`
  MODIFY `reports_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_classes`
--
ALTER TABLE `tb_classes`
  MODIFY `classes_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_guardians`
--
ALTER TABLE `tb_guardians`
  MODIFY `guardians_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_guardian_student`
--
ALTER TABLE `tb_guardian_student`
  MODIFY `guardian_student_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_notifications`
--
ALTER TABLE `tb_notifications`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_students`
--
ALTER TABLE `tb_students`
  MODIFY `students_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_teachers`
--
ALTER TABLE `tb_teachers`
  MODIFY `teachers_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_users`
--
ALTER TABLE `tb_users`
  MODIFY `users_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_violations`
--
ALTER TABLE `tb_violations`
  MODIFY `violations_id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_behavior_logs`
--
ALTER TABLE `tb_behavior_logs`
  ADD CONSTRAINT `tb_behavior_logs_ibfk_1` FOREIGN KEY (`behavior_report_id`) REFERENCES `tb_behavior_reports` (`reports_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_behavior_logs_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `tb_users` (`users_id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_behavior_reports`
--
ALTER TABLE `tb_behavior_reports`
  ADD CONSTRAINT `tb_behavior_reports_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `tb_students` (`students_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_behavior_reports_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `tb_teachers` (`teachers_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_behavior_reports_ibfk_3` FOREIGN KEY (`violation_id`) REFERENCES `tb_violations` (`violations_id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_classes`
--
ALTER TABLE `tb_classes`
  ADD CONSTRAINT `fk_class_teacher` FOREIGN KEY (`teachers_id`) REFERENCES `tb_teachers` (`teachers_id`) ON DELETE SET NULL;

--
-- Constraints for table `tb_guardians`
--
ALTER TABLE `tb_guardians`
  ADD CONSTRAINT `tb_guardians_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tb_users` (`users_id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_guardian_student`
--
ALTER TABLE `tb_guardian_student`
  ADD CONSTRAINT `tb_guardian_student_ibfk_1` FOREIGN KEY (`guardian_id`) REFERENCES `tb_guardians` (`guardians_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_guardian_student_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `tb_students` (`students_id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_notifications`
--
ALTER TABLE `tb_notifications`
  ADD CONSTRAINT `tb_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tb_users` (`users_id`) ON DELETE CASCADE;

--
-- Constraints for table `tb_students`
--
ALTER TABLE `tb_students`
  ADD CONSTRAINT `tb_students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tb_users` (`users_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_students_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `tb_classes` (`classes_id`) ON DELETE SET NULL;

--
-- Constraints for table `tb_teachers`
--
ALTER TABLE `tb_teachers`
  ADD CONSTRAINT `tb_teachers_ibfk_1` FOREIGN KEY (`users_id`) REFERENCES `tb_users` (`users_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tb_teachers_ibfk_2` FOREIGN KEY (`assigned_class_id`) REFERENCES `tb_classes` (`classes_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
