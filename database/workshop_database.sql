-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 10, 2026 at 04:48 PM
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
-- Database: `workshop_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `check_in_time` timestamp NULL DEFAULT NULL,
  `check_in_method` enum('qr_code','manual') DEFAULT 'manual',
  `attendance_status` enum('absent','present','late') DEFAULT 'absent',
  `marked_by` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `booking_id`, `workshop_id`, `user_id`, `check_in_time`, `check_in_method`, `attendance_status`, `marked_by`, `notes`) VALUES
(37, 34, 14, 24, '2026-01-03 21:35:52', 'manual', 'present', NULL, NULL),
(39, 37, 18, 27, '2026-01-03 20:26:58', 'manual', 'present', NULL, NULL),
(46, 38, 14, 27, '2026-01-03 21:35:52', 'manual', 'present', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('qr_code','bank_transfer') DEFAULT NULL,
  `payment_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_amount` decimal(10,2) DEFAULT 0.00,
  `payment_date` datetime DEFAULT current_timestamp(),
  `payment_verified_at` datetime DEFAULT NULL,
  `payment_verified_by` varchar(50) NOT NULL DEFAULT 'ADMIN',
  `booking_status` enum('registered','confirmed','cancelled') DEFAULT 'registered'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `workshop_id`, `created_at`, `payment_method`, `payment_status`, `payment_reference`, `payment_amount`, `payment_date`, `payment_verified_at`, `payment_verified_by`, `booking_status`) VALUES
(34, 24, 14, '2025-12-27 03:50:01', 'qr_code', 'verified', 'A1234567', 300.00, '2025-12-27 11:50:01', '2025-12-27 11:57:15', '28', 'confirmed'),
(35, 24, 15, '2025-12-27 03:50:31', 'bank_transfer', 'pending', 'A4567891', 150.00, '2025-12-27 11:50:31', NULL, '28', 'cancelled'),
(36, 27, 15, '2025-12-27 03:57:41', 'qr_code', 'rejected', 'A2301989', 150.00, '2025-12-27 11:57:41', NULL, '28', 'cancelled'),
(37, 27, 18, '2025-12-27 03:57:54', 'bank_transfer', 'verified', 'A5672934', 200.00, '2025-12-27 11:57:54', '2025-12-27 11:59:30', '28', 'confirmed'),
(38, 27, 14, '2025-12-27 04:53:11', 'qr_code', 'verified', 'A2341544', 300.00, '2025-12-27 12:53:11', '2025-12-27 12:54:58', '28', 'confirmed'),
(39, 24, 18, '2025-12-27 05:00:44', 'qr_code', 'rejected', 'A1234567', 200.00, '2025-12-27 13:00:44', NULL, '28', 'cancelled'),
(40, 29, 19, '2025-12-27 11:07:27', 'bank_transfer', 'verified', '123323432222', 70.00, '2025-12-27 19:07:27', '2025-12-27 21:56:31', '31', 'confirmed'),
(41, 27, 19, '2026-01-02 16:01:44', 'bank_transfer', 'verified', 'A1234578', 70.00, '2026-01-03 00:01:44', '2026-01-03 00:04:52', '28', 'confirmed'),
(42, 24, 19, '2026-01-03 16:13:25', 'bank_transfer', 'rejected', 'A3456271', 70.00, '2026-01-04 00:13:25', NULL, '28', 'cancelled'),
(43, 32, 15, '2026-01-03 16:18:45', 'bank_transfer', 'rejected', 'A3097852', 150.00, '2026-01-04 00:18:45', NULL, '28', 'cancelled'),
(44, 32, 20, '2026-01-03 18:33:00', 'bank_transfer', 'verified', 'RA3983976', 50.00, '2026-01-04 02:33:00', '2026-01-04 02:55:16', '28', 'confirmed'),
(45, 32, 21, '2026-01-03 21:42:24', 'qr_code', 'pending', 'RA5365251', 125.00, '2026-01-04 05:42:24', NULL, '', 'registered'),
(46, 29, 18, '2026-01-07 05:48:53', 'bank_transfer', 'pending', '12313123', 200.00, '2026-01-07 13:48:53', NULL, 'ADMIN', 'registered'),
(47, 29, 14, '2026-01-07 07:29:10', 'bank_transfer', 'pending', '12313123', 300.00, '2026-01-07 15:29:10', NULL, 'ADMIN', 'registered'),
(48, 33, 19, '2026-01-07 07:31:22', 'bank_transfer', 'verified', '123323432222', 70.00, '2026-01-07 15:31:22', '2026-01-08 16:25:14', '31', 'confirmed'),
(49, 29, 15, '2026-01-08 08:24:35', 'bank_transfer', 'pending', '123131', 150.00, '2026-01-08 16:24:35', NULL, 'ADMIN', 'registered'),
(50, 34, 19, '2026-01-09 15:33:48', 'bank_transfer', 'pending', '12313123', 70.00, '2026-01-09 23:33:48', NULL, 'ADMIN', 'registered'),
(51, 35, 19, '2026-01-09 15:50:07', 'bank_transfer', 'pending', '123131', 70.00, '2026-01-09 23:50:07', NULL, 'ADMIN', 'registered'),
(52, 36, 19, '2026-01-09 15:53:20', 'bank_transfer', 'pending', '123323432222', 70.00, '2026-01-09 23:53:20', NULL, 'ADMIN', 'registered'),
(53, 37, 19, '2026-01-09 15:58:33', 'bank_transfer', 'pending', '12313123', 70.00, '2026-01-09 23:58:33', NULL, 'ADMIN', 'registered');

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `certificate_code` varchar(50) DEFAULT NULL,
  `issue_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `user_id`, `workshop_id`, `certificate_code`, `issue_date`, `generated_at`) VALUES
(1, 29, 19, 'CERT-FFFAC4A486', '2025-12-27 16:44:52', '2025-12-27 16:44:52');

-- --------------------------------------------------------

--
-- Table structure for table `discussions`
--

CREATE TABLE `discussions` (
  `id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discussions`
--

INSERT INTO `discussions` (`id`, `workshop_id`, `user_id`, `message`, `created_at`) VALUES
(1, 18, 29, '13113131333', '2025-12-27 10:42:46'),
(2, 14, 29, 'hii', '2025-12-27 15:25:02'),
(3, 14, 29, 'hmmmmmm', '2025-12-27 15:25:07'),
(4, 19, 29, 'aaaa', '2025-12-27 15:40:53'),
(5, 19, 30, 'hii', '2025-12-27 15:41:09'),
(6, 19, 31, 'hii', '2025-12-27 15:41:33'),
(9, 19, 29, 'adsa', '2026-01-08 08:21:41');

-- --------------------------------------------------------

--
-- Table structure for table `discussion_mutes`
--

CREATE TABLE `discussion_mutes` (
  `id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `muted_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discussion_mutes`
--

INSERT INTO `discussion_mutes` (`id`, `workshop_id`, `user_id`, `muted_by`, `created_at`) VALUES
(2, 19, 29, 30, '2026-01-10 11:11:26');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `feedback_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `workshop_id`, `rating`, `comment`, `created_at`, `feedback_date`) VALUES
(1, 29, 19, 4, 'nicee', '2025-12-27 16:44:52', '2025-12-27 16:44:52');

-- --------------------------------------------------------

--
-- Table structure for table `learning_materials`
--

CREATE TABLE `learning_materials` (
  `material_id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `learning_materials`
--

INSERT INTO `learning_materials` (`material_id`, `workshop_id`, `title`, `file_path`, `uploaded_by`, `created_at`) VALUES
(2, 19, 'testslide', 'UPLOAD/materials/1766834212_Lab_Test3_Individual (3).pdf', 30, '2025-12-27 11:16:52');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 29, 'Your assignment for lintest was received successfully.', 1, '2025-12-27 16:35:23'),
(2, 29, 'Your assignment for lintest was received successfully.', 1, '2025-12-27 16:36:14'),
(3, 29, 'You received feedback for your project in lintest', 1, '2025-12-27 16:37:41'),
(4, 29, 'Your assignment for lintest was received successfully.', 1, '2025-12-27 16:46:06'),
(5, 29, 'Your assignment for lintest was received successfully.', 1, '2025-12-27 16:47:14'),
(6, 30, '📄 New Submission: ling submitted an assignment for lintest.', 1, '2025-12-27 16:47:14'),
(7, 21, '📢 New Workshop Alert: Creative Presentation Design with Canva is now open for registration!', 0, '2026-01-03 18:28:14'),
(8, 24, '📢 New Workshop Alert: Creative Presentation Design with Canva is now open for registration!', 0, '2026-01-03 18:28:14'),
(9, 27, '📢 New Workshop Alert: Creative Presentation Design with Canva is now open for registration!', 0, '2026-01-03 18:28:14'),
(10, 29, '📢 New Workshop Alert: Creative Presentation Design with Canva is now open for registration!', 1, '2026-01-03 18:28:14'),
(11, 32, '📢 New Workshop Alert: Creative Presentation Design with Canva is now open for registration!', 1, '2026-01-03 18:28:14'),
(12, 28, '💰 Payment Resubmitted: A participant has resubmitted payment for Creative Presentation Design with Canva', 1, '2026-01-03 18:54:34'),
(13, 21, '📢 New Workshop Alert: Canva Design Basics for Students is now open for registration!', 0, '2026-01-03 21:41:50'),
(14, 24, '📢 New Workshop Alert: Canva Design Basics for Students is now open for registration!', 0, '2026-01-03 21:41:50'),
(15, 27, '📢 New Workshop Alert: Canva Design Basics for Students is now open for registration!', 0, '2026-01-03 21:41:50'),
(16, 29, '📢 New Workshop Alert: Canva Design Basics for Students is now open for registration!', 1, '2026-01-03 21:41:50'),
(17, 32, '📢 New Workshop Alert: Canva Design Basics for Students is now open for registration!', 1, '2026-01-03 21:41:50'),
(18, 28, '💰 Payment Resubmitted: A participant has resubmitted payment for Canva Design Basics for Students', 0, '2026-01-03 21:45:36'),
(19, 29, 'Your assignment for lintest was received successfully.', 1, '2026-01-05 13:16:27'),
(20, 30, '📄 New Submission: ling submitted an assignment for lintest.', 1, '2026-01-05 13:16:27'),
(21, 29, 'Your assignment for Digital Skills Bootcamp: ChatGPT was received successfully.', 1, '2026-01-07 10:00:49'),
(22, 25, '📄 New Submission: ling submitted an assignment for Digital Skills Bootcamp: ChatGPT.', 0, '2026-01-07 10:00:49'),
(23, 29, 'You received feedback for your project in lintest', 1, '2026-01-07 10:07:56'),
(24, 29, 'You received feedback for your project in lintest', 1, '2026-01-08 08:27:03'),
(25, 30, '📝 New Submission: ling submitted a project for lintest.', 1, '2026-01-09 08:37:23'),
(26, 30, '📝 New Submission: ling submitted a project for lintest.', 1, '2026-01-09 08:37:32'),
(27, 30, '📝 New Submission: ling submitted a project for lintest.', 1, '2026-01-09 08:38:20'),
(28, 30, '📝 New Submission: ling submitted a project for lintest.', 1, '2026-01-09 08:38:39'),
(29, 30, '📝 New Submission: ling submitted a project for lintest.', 1, '2026-01-09 08:38:46'),
(30, 30, '📝 New Submission: ling submitted a project for lintest.', 1, '2026-01-09 08:38:52'),
(31, 30, '📝 New Submission: ling (Team: team1) submitted a project for lintest.', 1, '2026-01-09 08:48:33'),
(32, 23, '👤 New User Registration: ling3 has registered as Participant.', 1, '2026-01-09 15:30:15'),
(33, 23, '👤 New User Registration: ling has registered as Participant.', 0, '2026-01-09 15:49:53'),
(34, 23, '👤 New User Registration: ling has registered as Participant.', 0, '2026-01-09 15:53:06'),
(35, 23, '👤 New User Registration: ling has registered as Participant.', 0, '2026-01-09 15:58:23'),
(36, 31, '💰 Payment Pending: ling has registered for lintest. Please verify payment.', 1, '2026-01-09 15:58:33');

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `submission_id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `participant_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `submission_content` varchar(255) NOT NULL,
  `feedback_text` text DEFAULT NULL,
  `status` enum('Pending','Reviewed') DEFAULT 'Pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `submissions`
--

INSERT INTO `submissions` (`submission_id`, `workshop_id`, `participant_id`, `team_id`, `submission_content`, `feedback_text`, `status`, `submitted_at`) VALUES
(2, 19, 29, NULL, 'UPLOAD/submissions/1766853323_Lab_Test3_Individual.pdf', NULL, 'Pending', '2025-12-27 16:35:23'),
(3, 19, 29, NULL, 'UPLOAD/submissions/1766853374_Lab_Test3_Individual (1).pdf', 'aaa', 'Reviewed', '2025-12-27 16:36:14'),
(4, 19, 29, NULL, 'UPLOAD/submissions/1766853966_Lecture 9 (GE32003)_cdbdef6277a5070b59282ca6f0c2a312.pdf', NULL, 'Pending', '2025-12-27 16:46:06'),
(5, 19, 29, NULL, 'UPLOAD/submissions/1766854034_Network project management report (1) (2).pdf', '123123', 'Reviewed', '2025-12-27 16:47:14'),
(6, 19, 29, 2, 'UPLOAD/submissions/1767618987_Individual Assignment GC30303 (1).pdf', 'niv', 'Reviewed', '2026-01-05 13:16:27'),
(7, 14, 29, NULL, 'UPLOAD/submissions/1767780049_Assignment_Change_Request_Network_Project (6).docx', NULL, 'Pending', '2026-01-07 10:00:49'),
(8, 19, 29, 2, 'UPLOAD/submissions/1767947843_GE32003 FINAL EXAM QUESTION SEM 1, 2025,2026.pdf', NULL, 'Pending', '2026-01-09 08:37:23'),
(9, 19, 29, 2, 'UPLOAD/submissions/1767947852_PMI EXAM.pdf', NULL, 'Pending', '2026-01-09 08:37:32'),
(10, 19, 29, 2, 'UPLOAD/submissions/1767947900_PMI EXAM.pdf', NULL, 'Pending', '2026-01-09 08:38:20'),
(11, 19, 29, 2, 'UPLOAD/submissions/1767947919_PMI EXAM.pdf', NULL, 'Pending', '2026-01-09 08:38:39'),
(12, 19, 29, 2, 'UPLOAD/submissions/1767947926_PMI EXAM.pdf', NULL, 'Pending', '2026-01-09 08:38:46'),
(13, 19, 29, 2, 'UPLOAD/submissions/1767947932_PMI EXAM.pdf', NULL, 'Pending', '2026-01-09 08:38:52'),
(14, 19, 29, 2, 'UPLOAD/submissions/1767948513_PMI EXAM.pdf', NULL, 'Pending', '2026-01-09 08:48:33');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `workshop_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `workshop_id`, `name`, `created_by`, `created_at`) VALUES
(1, 14, 'team1', 29, '2025-12-27 10:43:14'),
(2, 19, 'team1', 29, '2025-12-27 11:00:09'),
(7, 19, 'team 2', 30, '2026-01-05 12:57:44');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `team_id`, `user_id`, `joined_at`) VALUES
(4, 1, 29, '2026-01-07 16:20:11'),
(6, 7, 29, '2026-01-10 00:54:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `country` varchar(50) NOT NULL,
  `role` enum('admin','organizer','trainer','participant') DEFAULT 'participant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `phone`, `country`, `role`, `created_at`, `avatar`) VALUES
(21, 'elsa', 'iloveolaf@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '0129876543', 'Malaysia', 'participant', '2025-12-22 08:33:47', NULL),
(23, 'admin', 'admin@smartworkshop.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '0123456789', 'Malaysia', 'admin', '2025-12-22 22:13:55', NULL),
(24, 'Haha', 'user1@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '60127898899', 'Malaysia', 'participant', '2025-12-25 07:32:52', NULL),
(25, 'skrr', 'user2@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '60167889888', 'Malaysia', 'trainer', '2025-12-25 08:06:26', NULL),
(26, 'John', 'user3@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '60123456789', 'United Kingdom', 'trainer', '2025-12-25 08:12:03', NULL),
(27, 'minion', 'user4@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '60124567890', 'Malaysia', 'participant', '2025-12-26 04:59:06', NULL),
(28, 'pineapple', 'user5@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '60176869988', 'Malaysia', 'organizer', '2025-12-26 19:50:40', NULL),
(29, 'ling', 'user6@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'participant', '2025-12-27 08:24:30', NULL),
(30, 'ling', 'user7@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'trainer', '2025-12-27 08:25:26', NULL),
(31, 'linorgan', 'user8@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'organizer', '2025-12-27 09:55:11', NULL),
(32, 'apple', 'user9@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '60123456782', 'Malaysia', 'participant', '2026-01-03 16:18:19', NULL),
(33, 'ling', 'user10@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'participant', '2026-01-07 07:31:09', NULL),
(34, 'ling3', 'user11@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'participant', '2026-01-09 15:30:15', NULL),
(35, 'ling', 'user12@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'participant', '2026-01-09 15:49:53', NULL),
(36, 'ling', 'user13@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'participant', '2026-01-09 15:53:06', NULL),
(37, 'ling', 'user14@example.com', '$2y$10$C6l2lduDtfE0y7JVteB3he0CWrTJ3ykSPhEn1FrKl7BCixqLA3PtC', '12312321', 'Malaysia', 'participant', '2026-01-09 15:58:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `workshops`
--

CREATE TABLE `workshops` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `trainer` varchar(100) DEFAULT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `location` varchar(100) NOT NULL,
  `fee` decimal(10,2) DEFAULT 0.00,
  `organizer_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `workshops`
--

INSERT INTO `workshops` (`id`, `title`, `description`, `trainer`, `trainer_id`, `date`, `time`, `location`, `fee`, `organizer_id`, `created_at`) VALUES
(14, 'Digital Skills Bootcamp: ChatGPT', 'This workshop focuses on creating professional designs, generating content efficiently, and boosting productivity using AI tools.', 'skrr', 25, '2025-12-29', '13:15:00', 'Multimedia Lab', 300.00, 28, '2025-12-27 03:14:16'),
(15, 'Content Creation with CapCut', 'Participants will learn how to design social media content and edit engaging videos for marketing and personal branding.', 'John', 26, '2026-01-03', '14:15:00', 'Google Meet', 150.00, 28, '2025-12-27 03:16:24'),
(18, 'AI and Digital Tools for Productivity', 'This hands-on session helps participants automate tasks, improve workflow, and create digital assets efficiently using modern tools.', 'skrr', 25, '2025-12-30', '15:30:00', 'Seminar Room B', 200.00, 28, '2025-12-27 03:36:52'),
(19, 'lintest', '123', 'ling', 30, '2026-01-02', '19:55:00', 'Google meet', 70.00, 31, '2025-12-27 10:55:17'),
(20, 'Creative Presentation Design with Canva', 'This workshop introduces participants to the use of Canva for designing visually appealing and effective presentation slides. It focuses on basic design principles and practical techniques to improve the clarity and creativity of academic and professional presentations.', 'ling', 30, '2026-01-08', '16:30:00', 'Google Meet', 50.00, 28, '2026-01-03 18:28:14'),
(21, 'Canva Design Basics for Students', 'This workshop is designed to introduce students to the fundamentals of Canva for creating visually appealing designs. Participants will learn how to use Canva’s tools and features to produce engaging graphics for presentations, projects, and social media content.', 'John', 26, '2026-01-14', '12:45:00', 'Computer Lab', 125.00, 28, '2026-01-03 21:41:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD UNIQUE KEY `unique_attendance` (`workshop_id`,`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `workshop_id` (`workshop_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `marked_by` (`marked_by`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`workshop_id`),
  ADD KEY `workshop_id` (`workshop_id`);

--
-- Indexes for table `discussions`
--
ALTER TABLE `discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workshop_id` (`workshop_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `discussion_mutes`
--
ALTER TABLE `discussion_mutes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_mute` (`workshop_id`,`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`workshop_id`),
  ADD KEY `workshop_id` (`workshop_id`);

--
-- Indexes for table `learning_materials`
--
ALTER TABLE `learning_materials`
  ADD PRIMARY KEY (`material_id`),
  ADD KEY `workshop_id` (`workshop_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`submission_id`),
  ADD KEY `workshop_id` (`workshop_id`),
  ADD KEY `participant_id` (`participant_id`),
  ADD KEY `submissions_team_fk` (`team_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `workshop_id` (`workshop_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_member` (`team_id`,`user_id`),
  ADD UNIQUE KEY `one_team_per_workshop` (`user_id`,`team_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `workshops`
--
ALTER TABLE `workshops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organizer_id` (`organizer_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discussions`
--
ALTER TABLE `discussions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `discussion_mutes`
--
ALTER TABLE `discussion_mutes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `learning_materials`
--
ALTER TABLE `learning_materials`
  MODIFY `material_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `submission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `workshops`
--
ALTER TABLE `workshops`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_booking_fk` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_marked_by_fk` FOREIGN KEY (`marked_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `attendance_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendance_workshop_fk` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `certificates`
--
ALTER TABLE `certificates`
  ADD CONSTRAINT `certificates_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `certificates_ibfk_2` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `discussions`
--
ALTER TABLE `discussions`
  ADD CONSTRAINT `discussions_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `discussions_workshop_fk` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `learning_materials`
--
ALTER TABLE `learning_materials`
  ADD CONSTRAINT `learning_materials_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `learning_materials_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_ibfk_1` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_ibfk_2` FOREIGN KEY (`participant_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `submissions_team_fk` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_creator_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_workshop_fk` FOREIGN KEY (`workshop_id`) REFERENCES `workshops` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `members_team_fk` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `members_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `workshops`
--
ALTER TABLE `workshops`
  ADD CONSTRAINT `workshops_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
