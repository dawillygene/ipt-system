-- phpMyAdmin SQL Dump
-- version 5.2.1deb1+deb12u1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 15, 2025 at 03:44 PM
-- Server version: 10.11.11-MariaDB-0+deb12u1
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `IPT-SYSTEM`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_qualification`
--

CREATE TABLE `academic_qualification` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `qualification` varchar(255) NOT NULL,
  `institution` varchar(255) NOT NULL,
  `year_of_completion` int(11) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `experience_national_exam` int(11) DEFAULT NULL,
  `experience_work` int(11) DEFAULT NULL,
  `level_of_education` varchar(50) DEFAULT NULL,
  `level_teach` varchar(50) DEFAULT NULL,
  `subject_teach` varchar(50) DEFAULT NULL,
  `school_teach` varchar(100) DEFAULT NULL,
  `subject_study` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_qualification`
--

INSERT INTO `academic_qualification` (`id`, `user_id`, `qualification`, `institution`, `year_of_completion`, `position`, `experience_national_exam`, `experience_work`, `level_of_education`, `level_teach`, `subject_teach`, `school_teach`, `subject_study`) VALUES
(1, 1, 'ud', 'mzumbe', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 1, '', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 1, '', '', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 1, 'ud', 'mzumbe', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 4, 'SUPERVISOR', 'KARUME ', 1999, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 4, 'SUPERVISOR', 'KARUME ', 1999, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 6, 'MKUUU', 'MKUU', 1999, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 9, 'mkuu', 'suza', 1888, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 12, 'MKUUU', 'mzumbe', 1999, 'hhh', 1999, 3, 'PHD', 'MKUU', 'ENG', 'BUBUBU', 'NULL');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password`) VALUES
(1, 'admin@iptsystem.com', '$2y$12$NsfvHghaYD./7FN7S3flYe8NH5OIxiSrzA/d0mfuQwMdfv7kNVUnS'),
(2, 'admin@admin.com', '$2y$10$dqdx.dc7KX8zYgzzFH/VWeOm9/z4R3Zv57UOKeE5bP/F00zMA97ji');

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `reg_number` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `industrial` varchar(100) NOT NULL,
  `application_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('draft','submitted','approved','rejected','in_review','pending') DEFAULT 'draft',
  `company_name` varchar(255) DEFAULT NULL,
  `company_location` varchar(255) DEFAULT NULL,
  `position_title` varchar(255) DEFAULT NULL,
  `training_duration` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `training_area` varchar(255) DEFAULT NULL,
  `skills_to_acquire` text DEFAULT NULL,
  `motivation_letter` text DEFAULT NULL,
  `preferred_company1` varchar(255) DEFAULT NULL,
  `preferred_company2` varchar(255) DEFAULT NULL,
  `preferred_company3` varchar(255) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`id`, `user_id`, `student_id`, `full_name`, `phone`, `reg_number`, `department`, `industrial`, `application_date`, `created_at`, `status`, `company_name`, `company_location`, `position_title`, `training_duration`, `start_date`, `end_date`, `training_area`, `skills_to_acquire`, `motivation_letter`, `preferred_company1`, `preferred_company2`, `preferred_company3`, `submitted_at`, `updated_at`) VALUES
(14, 24, NULL, '0623641759', 'Lulu Ibrah', '12345678910', 'ICT', 'Sheria House,Airport,Bandarini', '2025-06-07', '2025-06-06 12:15:29', 'submitted', 'Sheria House,Airport,Bandarini', 'Unknown Location', 'Internship Position', 12, '2025-06-07', '2025-08-30', 'General', NULL, 'Migrated from old system', NULL, NULL, NULL, '2025-06-06 12:15:29', '2025-06-15 13:22:42');

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
-- Table structure for table `colleges`
--

CREATE TABLE `colleges` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `colleges`
--

INSERT INTO `colleges` (`id`, `name`, `address`, `contact_info`, `created_at`, `updated_at`) VALUES
(1, 'Dar Institute of Technology', 'P.O. Box 2958, Dar es Salaam', 'info@dit.ac.tz | +255713000101', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(2, 'University of Dodoma', 'P.O. Box 259, Dodoma', 'info@udom.ac.tz | +255713000102', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(3, 'Arusha Technical College', 'P.O. Box 296, Arusha', 'info@atc.ac.tz | +255713000103', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(4, 'Mbeya University of Science and Technology', 'P.O. Box 131, Mbeya', 'info@must.ac.tz | +255713000104', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(5, 'Nelson Mandela Institution', 'P.O. Box 447, Arusha', 'info@nm-aist.ac.tz | +255713000105', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(6, 'St Joseph University', 'P.O. Box 11007, Dar es Salaam', 'info@sjuit.ac.tz | +255713000106', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(7, 'Mzumbe University', 'P.O. Box 1, Morogoro', 'info@mzumbe.ac.tz | +255713000107', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(8, 'University of Dar es Salaam', 'P.O. Box 35091, Dar es Salaam', 'info@udsm.ac.tz | +255713000108', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(9, 'State University of Zanzibar', 'P.O. Box 146, Zanzibar', 'info@suza.ac.tz | +255713000109', '2025-05-04 15:04:25', '2025-05-04 15:04:25'),
(10, 'Tumaini University Makumira', 'P.O. Box 55, Usa River', 'info@tumaini.ac.tz | +255713000110', '2025-05-04 15:04:25', '2025-05-04 15:04:25');

-- --------------------------------------------------------

--
-- Table structure for table `contact_details`
--

CREATE TABLE `contact_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bank_no` varchar(50) NOT NULL,
  `bank_name` varchar(100) NOT NULL,
  `zan_id` varchar(50) NOT NULL,
  `zssf_no` varchar(50) NOT NULL,
  `upload_zan_id_photo` varchar(255) DEFAULT NULL,
  `license_no` varchar(50) NOT NULL,
  `vol_no` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_details`
--

INSERT INTO `contact_details` (`id`, `user_id`, `bank_no`, `bank_name`, `zan_id`, `zssf_no`, `upload_zan_id_photo`, `license_no`, `vol_no`, `created_at`) VALUES
(1, 1, '67735', 'n,mb', '636373', '67667', '', '773737', '7727', '2024-06-06 21:51:33'),
(2, 1, '67735', 'n,mb', '636373', '67667', '', '773737', '7727', '2024-06-06 21:51:38'),
(3, 4, '12256646', 'AMANA BANK', '5566666', '67666', '', '664664', '325366', '2024-06-06 22:43:53'),
(4, 6, 'n6556', 'AMANA', 'Z664688992', '6467Q', '', '5647', '6655', '2024-06-07 05:59:43'),
(6, 9, '1224221', 'nmb', 'z64461781', 'zsw256677', '1717838864_3ae3110a-90ee-403a-b842-6763ca7842da.jpg', '1462711', '2424', '2024-06-08 09:27:44'),
(7, 9, '1224221', 'nmb', 'z64461781', 'zsw256677', '1717838919_3ae3110a-90ee-403a-b842-6763ca7842da.jpg', '1462711', '2424', '2024-06-08 09:28:39'),
(8, 9, '1224221', 'nmb', 'z64461781', 'zsw256677', '1717839013_3ae3110a-90ee-403a-b842-6763ca7842da.jpg', '1462711', '2424', '2024-06-08 09:30:13'),
(9, 12, '67735', 'AMANA', 'Z664688992', '6467Q', '022e8d2c-3e61-4c66-b92a-d0a4acdc4880-fotor-bg-remover-2023092421488.png', '773737', '7727', '2024-06-25 08:09:22'),
(10, 12, '67735', 'AMANA', 'Z664688992', '67667', 'abdalla.png', '5647', '7727', '2024-06-25 08:11:08');

-- --------------------------------------------------------

--
-- Table structure for table `evaluations`
--

CREATE TABLE `evaluations` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `evaluation_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `evaluation_score` int(11) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `evaluations`
--

INSERT INTO `evaluations` (`id`, `student_id`, `supervisor_id`, `evaluation_date`, `evaluation_score`, `comments`, `created_at`, `updated_at`) VALUES
(6, 8, 101, '2025-04-14 21:00:00', 85, 'Excellent performance in software development tasks.', '2025-05-04 15:19:27', '2025-05-04 15:19:27'),
(7, 9, 102, '2025-04-09 21:00:00', 90, 'Great understanding of electrical systems, needs minor improvement in practicals.', '2025-05-04 15:19:27', '2025-05-04 15:19:27'),
(8, 12, 103, '2025-03-27 21:00:00', 88, 'Strong research skills, showed good initiative in the health sector.', '2025-05-04 15:19:27', '2025-05-04 15:19:27'),
(9, 16, 104, '2025-03-31 21:00:00', 92, 'Exceptional leadership and communication skills in marketing projects.', '2025-05-04 15:19:27', '2025-05-04 15:19:27'),
(10, 17, 105, '2025-04-11 21:00:00', 80, 'Solid progress in medical research, but some areas need more focus.', '2025-05-04 15:19:27', '2025-05-04 15:19:27');

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
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `feedback` text NOT NULL,
  `rating` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `report_id`, `supervisor_id`, `feedback`, `rating`, `created_at`, `updated_at`) VALUES
(1, 1, 101, 'The report is comprehensive and well-organized, but some sections need further clarification.', 4, '2025-05-04 15:21:47', '2025-05-04 15:21:47'),
(2, 2, 102, 'Excellent work on the project report, well-detailed with insightful analysis.', 5, '2025-05-04 15:21:47', '2025-05-04 15:21:47'),
(3, 3, 103, 'Good work, but there were some errors in data interpretation that need to be addressed.', 3, '2025-05-04 15:21:47', '2025-05-04 15:21:47'),
(4, 4, 104, 'The report met expectations, though a few points could be expanded for clarity.', 4, '2025-05-04 15:21:47', '2025-05-04 15:21:47'),
(5, 5, 105, 'The report was incomplete and lacked critical information on key topics.', 2, '2025-05-04 15:21:47', '2025-05-04 15:21:47');

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

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_03_24_092230_create_sessions_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 8, 'Your training assignment has been updated. Please review the new details.', 0, '2025-05-04 15:35:50', '2025-05-04 15:35:50'),
(2, 9, 'You have received a new feedback on your report. Check it in the system.', 1, '2025-05-04 15:35:50', '2025-05-04 15:35:50'),
(3, 12, 'Reminder: Your training report is due in 3 days. Please submit it on time.', 0, '2025-05-04 15:35:50', '2025-05-04 15:35:50'),
(4, 16, 'Your application for the internship has been approved. Congratulations!', 1, '2025-05-04 15:35:50', '2025-05-04 15:35:50'),
(5, 17, 'New updates are available for your training program. Please review the changes.', 0, '2025-05-04 15:35:50', '2025-05-04 15:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `other_attachments`
--

CREATE TABLE `other_attachments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `attachment_name` varchar(255) NOT NULL,
  `attachment_url` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `other_attachments`
--

INSERT INTO `other_attachments` (`id`, `user_id`, `attachment_name`, `attachment_url`, `file_path`) VALUES
(8, 6, 'NIDA', '', 'uploads/CHARLES MARKO.pdf'),
(9, 6, 'mtanzania', '', 'uploads/ALMAS.pdf'),
(10, 6, 'kitambulisho', '', 'uploads/ADAM(1) (1).pdf'),
(14, 9, 'kitambulisho', '', 'uploads/4_5865962394668240131.docx'),
(15, 9, 'kimoja', '', 'uploads/ALMAS.pdf'),
(16, 9, 'mdfano', '', 'uploads/ADAM(1) (1).pdf'),
(17, 12, 'CV', '', 'uploads/022e8d2c-3e61-4c66-b92a-d0a4acdc4880-fotor-bg-remover-2023092421488.png'),
(18, 12, 'Application Letter', '', 'uploads/6b1674ec-f759-4446-ac56-a8d0b18e1913-fotor-bg-remover-20230926153817.png'),
(19, 12, 'Passport Size Photo', '', 'uploads/2082dedb-3430-43b7-88ad-c714a37f64d9-fotor-bg-remover-2023092616435.png'),
(20, 12, 'Certification 1', '', 'uploads/2082dedb-3430-43b7-88ad-c714a37f64d9-fotor-bg-remover-2023092616435.png'),
(21, 12, 'Certification 2', '', 'uploads/4_5865962394668240131.docx'),
(22, 12, 'Certification 3', '', 'uploads/ABEL.png'),
(23, 12, 'Other Document', '', 'uploads/022e8d2c-3e61-4c66-b92a-d0a4acdc4880-fotor-bg-remover-2023092421488.png'),
(24, 12, 'CV', '', 'uploads/022e8d2c-3e61-4c66-b92a-d0a4acdc4880-fotor-bg-remover-2023092421488.png'),
(25, 12, 'Application Letter', '', 'uploads/6b1674ec-f759-4446-ac56-a8d0b18e1913-fotor-bg-remover-20230926153817.png'),
(26, 12, 'Passport Size Photo', '', 'uploads/6b1674ec-f759-4446-ac56-a8d0b18e1913-fotor-bg-remover-20230926153817.png'),
(27, 12, 'Certification 1', '', 'uploads/4_5865962394668240131.docx'),
(28, 12, 'Certification 2', '', 'uploads/2082dedb-3430-43b7-88ad-c714a37f64d9-fotor-bg-remover-2023092616435.png'),
(29, 12, 'Certification 3', '', 'uploads/6b1674ec-f759-4446-ac56-a8d0b18e1913-fotor-bg-remover-20230926153817.png'),
(30, 12, 'Other Document', '', 'uploads/022e8d2c-3e61-4c66-b92a-d0a4acdc4880-fotor-bg-remover-2023092421488.png');

-- --------------------------------------------------------

--
-- Table structure for table `personal_details`
--

CREATE TABLE `personal_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `place_of_birth` varchar(100) NOT NULL,
  `resident_region` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `bank_no` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `zan_id` varchar(255) DEFAULT NULL,
  `zssf_no` varchar(255) DEFAULT NULL,
  `license_no` varchar(255) DEFAULT NULL,
  `vol_no` varchar(255) DEFAULT NULL,
  `zan_id_front_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `personal_details`
--

INSERT INTO `personal_details` (`id`, `user_id`, `address`, `phone`, `email`, `place_of_birth`, `resident_region`, `district`, `bank_no`, `bank_name`, `zan_id`, `zssf_no`, `license_no`, `vol_no`, `zan_id_front_photo`) VALUES
(1, 1, 'P.O BOX 2052', '0698120948', 'agizanasi11@gmail.com', '11/11/1999', 'mnm', 'MAGHARIBI (A)', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 4, 'mbuzini', '+255719401489', 'test@mail.com', '12/12/1999', 'unguja', 'magharibi a', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 6, 'bububu', '0719644674', 'honesthatibu@GMAIL.COM', '12/12/1888', 'mjini', 'kitongoji', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 9, 'mkoani ', '0752526626', 'majaribio5@gmail.com', '12/10/1999', 'znz', 'ungujqa', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 12, 'kinuni', '078825367', 'yamatangazo11@gmail.com', '12/12/1855', 'mjini', 'kitongoji', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `week_number` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text NOT NULL,
  `skills_gained` text NOT NULL,
  `challenges_faced` text DEFAULT NULL,
  `report_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `week_number`, `start_date`, `end_date`, `description`, `skills_gained`, `challenges_faced`, `report_file`, `created_at`) VALUES
(1, 24, 1, '2025-06-06', '2025-06-13', 'We did this that', 'Coding', 'We face this that', '1749221473_drawing.jpg', '2025-06-06 14:51:13');

-- --------------------------------------------------------

--
-- Table structure for table `report_reviews`
--

CREATE TABLE `report_reviews` (
  `review_id` int(11) NOT NULL,
  `report_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `review_status` enum('pending','reviewed','approved','needs_revision') DEFAULT 'pending',
  `feedback_content` text DEFAULT NULL,
  `grade` varchar(10) DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ctLcgOgyoPuDTZUg0cBJxMQtkIgoGFD2GD5ad0FP', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYkFXdFBGeVhwdDR5ZUlzT3YyVzFEZFFUb2w2bVY1WFZNaXJFc1YwYSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sb2NhbGhvc3QvaXB0LXN5c3RlbS9wdWJsaWMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1746461481),
('ESZouMWKhkWjTlE6oTjcrTH0lHt4nWCOLNE30OBf', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN3VNaUdaaUJlcXQ1Z3hBSUx1RE1RWUx3cXVrMEtNbmNWeEJ3ZTRSWCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sb2NhbGhvc3QvaXB0LXN5c3RlbS9wdWJsaWMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1748152991),
('HxQ9zq0Vr23BsXEBc8j6oDsv0Yt3I9CzMjA206d5', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWHRYMDdONkg3ek4yMnYwbGU1Vm81NEF2MnI3UGw3M1FWYkM5cWdQSyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly9sb2NhbGhvc3QvaXB0LXN5c3RlbS9wdWJsaWMvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1746450741),
('LZbops3XzrICtv4j7rCVqnnUwf3ODD9XpokQn3ZW', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMkhPNGVnZ2dVakl1eEYwVGVpZkV3ZzRwRDJwa3R1b2NOcFhBNHhqSSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzQ6Imh0dHA6Ly9sb2NhbGhvc3QvaXB0LXN5c3RlbS9wdWJsaWMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1746536261),
('UGeuKZUxf8OJzY6yplGyvUd1OCBU5ULSSciRVbWD', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHhXZnh2VUNiSDBjQlF2UktiYXdRSGx1V3pZSzd6VlYyVTRKQUVEdCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly9sb2NhbGhvc3QvaXB0LXN5c3RlbS9wdWJsaWMvZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1746516647);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `reg_number` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `college_name` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `course_name` varchar(255) DEFAULT NULL,
  `program` varchar(100) DEFAULT NULL,
  `level` varchar(50) DEFAULT NULL,
  `year_of_study` tinyint(3) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_photo` varchar(255) DEFAULT NULL,
  `academic_transcript` varchar(500) DEFAULT NULL,
  `id_document` varchar(500) DEFAULT NULL,
  `cv_document` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `full_name`, `reg_number`, `gender`, `college_name`, `department`, `course_name`, `program`, `level`, `year_of_study`, `phone_number`, `address`, `email`, `created_at`, `updated_at`, `profile_photo`, `academic_transcript`, `id_document`, `cv_document`) VALUES
(1, 24, 'Lulu Ibrahim', 'XYZ123456', 'Female', 'KIST', 'ICT', 'ICT', 'IT', '6', 4, '0623641759', '73201', 'hamis2r@gmail.com', '2025-06-06 13:25:50', '2025-06-13 18:50:48', 'uploads/profiles/1749217082_Aisha_approvedPhoto_resized.jpg', NULL, NULL, NULL),
(2, NULL, 'Raya Boyle', '123433322008', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'kifyqegixe@mailinator.com', '2025-06-13 18:51:45', '2025-06-13 18:51:45', NULL, NULL, NULL, NULL),
(3, NULL, 'Felicia Mcbride', 'Ea ab esse ut venia', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'gunem@mailinator.com', '2025-06-13 18:51:54', '2025-06-13 18:51:54', NULL, NULL, NULL, NULL),
(4, NULL, 'ELIA WILLIAM MARIKI', 'T22-03-13063', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'dawillygene@gmail.com', '2025-06-13 18:59:15', '2025-06-13 18:59:15', NULL, NULL, NULL, NULL),
(5, NULL, 'Test Student', 'TEST123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test@example.com', '2025-06-13 19:02:12', '2025-06-13 19:02:12', NULL, NULL, NULL, NULL),
(6, NULL, 'Test Profile User', 'PROFILE123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'testprofile@example.com', '2025-06-13 19:47:15', '2025-06-13 19:47:15', NULL, NULL, NULL, NULL),
(7, NULL, 'HEAVENLIGHT MARIKI', 'venli1221', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'venlit@gmail.com', '2025-06-14 13:45:57', '2025-06-14 13:45:57', NULL, NULL, NULL, NULL),
(8, NULL, 'HEAVENLIGHT ELIA MARIKI', 'student123', 'Female', 'DODOMA UNIVERSITY', 'Computer Science', 'SOFTWARE ENGINEEERING', 'Bachelor', 'LEVEL 7', 4, '255753225961', 'DODOMA TANZANIA', 'student@student.com', '2025-06-14 13:50:20', '2025-06-15 11:01:44', 'uploads/students/profile_8_1749933941.png', 'uploads/students/transcript_8_1749933900.pdf', 'uploads/students/id_8_1749933931.pdf', 'uploads/students/cv_8_1749933931.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `students_auth`
--

CREATE TABLE `students_auth` (
  `auth_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students_auth`
--

INSERT INTO `students_auth` (`auth_id`, `student_id`, `email`, `password`, `created_at`, `updated_at`) VALUES
(1, 2, 'kifyqegixe@mailinator.com', '$2y$10$H6aFOSIXTXRkWemVMqSvz.bL3Xptq0GJbp1oSSWeTyzmokvtMJsV2', '2025-06-13 18:51:45', '2025-06-13 18:51:45'),
(2, 3, 'gunem@mailinator.com', '$2y$10$oOE7od38EciFZtgCYqckGuUx347dvXMlxK6vefr7SPmkPkbRKJDai', '2025-06-13 18:51:54', '2025-06-13 18:51:54'),
(3, 4, 'dawillygene@gmail.com', '$2y$10$qi9XgB6d0XIG2OxNuxI2Dukta6NUIykvPkkdeStMZH1Ho8berLdiO', '2025-06-13 18:59:15', '2025-06-13 18:59:15'),
(4, 5, 'test@example.com', '$2y$10$uILBi1Z0u97p004bmxFeZOF3Plgqe0T0BSHkluNzDG8Bl9NQ0Z8Wm', '2025-06-13 19:02:12', '2025-06-13 19:02:12'),
(5, 6, 'testprofile@example.com', '$2y$10$b7H/EEio6FnmNsqiqmEtDuwd2f6dMN0cT6LYxgJXA9RCp/u5Hx1pi', '2025-06-13 19:47:15', '2025-06-13 19:47:15'),
(6, 7, 'venlit@gmail.com', '$2y$10$8YFdtUscDRjqF8yn.M5bCOzfINOhS.NMiXQqpWuFdAJfostvm.hV.', '2025-06-14 13:45:57', '2025-06-14 13:45:57'),
(7, 8, 'student@student.com', '$2y$10$zOCsy6EqGgrDhyHzEUBvGOnJ4Caiey9Q2MYiwHtqNqXef3HqZAUC6', '2025-06-14 13:50:20', '2025-06-14 13:50:20');

-- --------------------------------------------------------

--
-- Table structure for table `student_documents`
--

CREATE TABLE `student_documents` (
  `document_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `document_type` enum('application_letter','resume','cover_letter','report','certificate','other') NOT NULL,
  `document_title` varchar(255) NOT NULL,
  `document_description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `is_generated` tinyint(1) DEFAULT 0,
  `status` enum('draft','final','submitted','approved') DEFAULT 'draft',
  `generated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_evaluations`
--

CREATE TABLE `student_evaluations` (
  `evaluation_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `evaluation_type` enum('mid_term','final','monthly','custom') NOT NULL,
  `evaluation_period` varchar(100) NOT NULL,
  `technical_skills_score` int(11) DEFAULT NULL CHECK (`technical_skills_score` >= 0 and `technical_skills_score` <= 100),
  `communication_score` int(11) DEFAULT NULL CHECK (`communication_score` >= 0 and `communication_score` <= 100),
  `teamwork_score` int(11) DEFAULT NULL CHECK (`teamwork_score` >= 0 and `teamwork_score` <= 100),
  `punctuality_score` int(11) DEFAULT NULL CHECK (`punctuality_score` >= 0 and `punctuality_score` <= 100),
  `initiative_score` int(11) DEFAULT NULL CHECK (`initiative_score` >= 0 and `initiative_score` <= 100),
  `overall_grade` varchar(10) DEFAULT NULL,
  `strengths` text DEFAULT NULL,
  `areas_for_improvement` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `evaluation_date` date NOT NULL,
  `status` enum('draft','submitted','approved') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_feedback`
--

CREATE TABLE `student_feedback` (
  `feedback_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `feedback_type` enum('supervisor','industrial','academic','system') NOT NULL DEFAULT 'supervisor',
  `feedback_title` varchar(255) NOT NULL,
  `feedback_content` text NOT NULL,
  `supervisor_name` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `feedback_date` date NOT NULL,
  `rating` int(1) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `status` enum('pending','read','responded') DEFAULT 'pending',
  `response_content` text DEFAULT NULL,
  `response_date` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_notifications`
--

CREATE TABLE `student_notifications` (
  `notification_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `notification_type` enum('feedback','report','application','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `related_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_reports`
--

CREATE TABLE `student_reports` (
  `report_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `report_type` enum('daily','weekly','monthly') NOT NULL DEFAULT 'daily',
  `report_title` varchar(255) NOT NULL,
  `report_content` text NOT NULL,
  `report_date` date NOT NULL,
  `week_number` int(11) DEFAULT NULL,
  `month_number` int(11) DEFAULT NULL,
  `activities_completed` text DEFAULT NULL,
  `skills_acquired` text DEFAULT NULL,
  `challenges_faced` text DEFAULT NULL,
  `supervisor_comments` text DEFAULT NULL,
  `status` enum('draft','submitted','reviewed','approved','needs_revision') DEFAULT 'draft',
  `attachment_path` varchar(500) DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_reports`
--

INSERT INTO `student_reports` (`report_id`, `student_id`, `report_type`, `report_title`, `report_content`, `report_date`, `week_number`, `month_number`, `activities_completed`, `skills_acquired`, `challenges_faced`, `supervisor_comments`, `status`, `attachment_path`, `submitted_at`, `created_at`, `updated_at`) VALUES
(3, 8, 'daily', 'history of compay', 'none', '2025-06-14', NULL, NULL, '0', 'devops', 'none', NULL, 'submitted', 'uploads/reports/report_8_1749930018.pdf', '2025-06-14 19:40:18', '2025-06-14 14:40:31', '2025-06-14 19:40:18'),
(4, 8, 'daily', 'CHECK THE APPOINTMENT', 'okay its good actually', '2025-06-14', NULL, NULL, 'it shows the all appointments', 'nothing i gained', 'no challenges', NULL, 'submitted', 'uploads/reports/report_8_1749916557.png', '2025-06-14 15:55:57', '2025-06-14 15:55:57', '2025-06-14 15:55:57'),
(5, 8, 'daily', 'http://localhost/ipt-system/student_reports.php', 'http://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.php', '2025-06-14', NULL, NULL, 'http://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.php', 'http://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.php', 'http://localhost/ipt-system/student_reports.phphttp://localhost/ipt-system/student_reports.php', NULL, 'submitted', 'uploads/reports/report_8_1749928386.png', '2025-06-14 19:13:06', '2025-06-14 19:13:06', '2025-06-14 19:13:06'),
(6, 8, 'daily', 'Eventbrite is an American live events marketplace and website.', 'Eventbrite is an American live events marketplace and website. The service allows users to browse, create, and promote local events. The service charges a fee to event organizers in exchange for online ticketing services, unless the event is free. In September or October 2023, Eventbrite changed their pricing plans to limit free events to 25 tickets before they would begin to charge organizers fees. Launched in 2006 and headquartered in San Francisco, Eventbrite opened their first international office in the United Kingdom in 2012', '2025-06-14', NULL, NULL, 'Eventbrite is an American live events marketplace and website. The service allows users to browse, create, and promote local events. The service charges a fee to event organizers in exchange for online ticketing services, unless the event is free. In September or October 2023, Eventbrite changed their pricing plans to limit free events to 25 tickets before they would begin to charge organizers fees. Launched in 2006 and headquartered in San Francisco, Eventbrite opened their first international office in the United Kingdom in 2012', 'Eventbrite is an American live events marketplace and website. The service allows users to browse, create, and promote local events. The service charges a fee to event organizers in exchange for online ticketing services, unless the event is free. In September or October 2023, Eventbrite changed their pricing plans to limit free events to 25 tickets before they would begin to charge organizers fees. Launched in 2006 and headquartered in San Francisco, Eventbrite opened their first international office in the United Kingdom in 2012', 'Eventbrite is an American live events marketplace and website. The service allows users to browse, create, and promote local events. The service charges a fee to event organizers in exchange for online ticketing services, unless the event is free. In September or October 2023, Eventbrite changed their pricing plans to limit free events to 25 tickets before they would begin to charge organizers fees. Launched in 2006 and headquartered in San Francisco, Eventbrite opened their first international office in the United Kingdom in 2012', NULL, 'submitted', NULL, '2025-06-14 19:14:46', '2025-06-14 19:14:46', '2025-06-14 19:14:46'),
(7, 8, 'daily', 'Database Management', 'Eventbrite is an American live events marketplace and website. The service allows users to browse, create, and promote local events. The service charges a fee to event organizers in exchange for online ticketing services, unless the event is free. In September or October 2023, Eventbrite changed their pricing plans to limit free events to 25 tickets before they would begin to charge organizers fees. Launched in 2006 and headquartered in San Francisco, Eventbrite opened their first international office in the United Kingdom in 2012', '2025-06-14', NULL, NULL, '0', 'Eventbrite is an American live events marketplace and website. The service allows users to browse, create, and promote local events. The service charges a fee to event organizers in exchange for online ticketing services, unless the event is free. In September or October 2023, Eventbrite changed their pricing plans to limit free events to 25 tickets before they would begin to charge organizers fees. Launched in 2006 and headquartered in San Francisco, Eventbrite opened their first international office in the United Kingdom in 2012', 'Eventbrite is an American live events marketplace and website. The service allows users to browse, create, and promote local events. The service charges a fee to event organizers in exchange for online ticketing services, unless the event is free. In September or October 2023, Eventbrite changed their pricing plans to limit free events to 25 tickets before they would begin to charge organizers fees. Launched in 2006 and headquartered in San Francisco, Eventbrite opened their first international office in the United Kingdom in 2012', NULL, 'submitted', 'uploads/reports/report_8_1749929990.pdf', '2025-06-14 19:40:08', '2025-06-14 19:15:20', '2025-06-14 19:40:08');

-- --------------------------------------------------------

--
-- Table structure for table `student_training_assignments`
--

CREATE TABLE `student_training_assignments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `organization_id` int(11) NOT NULL,
  `training_area_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','assigned','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_training_assignments`
--

INSERT INTO `student_training_assignments` (`id`, `student_id`, `organization_id`, `training_area_id`, `start_date`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(6, 8, 1, 1, '2025-04-01', '2025-06-30', '', '2025-05-04 15:34:36', '2025-05-04 15:34:36'),
(7, 9, 2, 2, '2025-04-05', '2025-07-15', 'completed', '2025-05-04 15:34:36', '2025-05-04 15:34:36'),
(8, 12, 3, 3, '2025-03-20', '2025-06-10', '', '2025-05-04 15:34:36', '2025-05-04 15:34:36'),
(9, 16, 4, 4, '2025-04-10', '2025-08-01', 'pending', '2025-05-04 15:34:36', '2025-05-04 15:34:36'),
(10, 17, 5, 5, '2025-04-12', '2025-07-12', '', '2025-05-04 15:34:36', '2025-05-04 15:34:36');

-- --------------------------------------------------------

--
-- Table structure for table `supervisors`
--

CREATE TABLE `supervisors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `department` varchar(255) DEFAULT NULL,
  `contact_info` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supervisors`
--

INSERT INTO `supervisors` (`id`, `user_id`, `department`, `contact_info`, `created_at`, `updated_at`) VALUES
(21, 2, 'Information Technology', 'it@university.ac.tz | +255714000112', '2025-05-04 15:02:59', '2025-05-04 15:02:59'),
(22, 11, 'Computer Engineering', 'ce@university.ac.tz | +255714000211', '2025-05-04 15:02:59', '2025-05-04 15:02:59'),
(23, 14, 'Cyber Security', 'cyber@tech.ac.tz | +255714000214', '2025-05-04 15:02:59', '2025-05-04 15:02:59'),
(24, 18, 'Software Engineering', 'se@college.ac.tz | +255714000218', '2025-05-04 15:02:59', '2025-05-04 15:02:59'),
(25, 29, 'Computer Science', 'Computer Science | +255123456789', '2025-06-14 14:12:26', '2025-06-14 14:12:26'),
(26, 30, 'Computer Science', 'Computer Science | 255123456789', '2025-06-14 14:14:54', '2025-06-14 14:14:54'),
(27, 31, 'Computer Science', 'Computer Science | 0753225961', '2025-06-14 14:18:32', '2025-06-14 14:18:32');

-- --------------------------------------------------------

--
-- Table structure for table `supervisor_assignments`
--

CREATE TABLE `supervisor_assignments` (
  `assignment_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assignment_type` enum('academic','industrial') NOT NULL DEFAULT 'academic',
  `assigned_date` date NOT NULL,
  `status` enum('active','completed','terminated') DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supervisor_assignments`
--

INSERT INTO `supervisor_assignments` (`assignment_id`, `supervisor_id`, `student_id`, `assignment_type`, `assigned_date`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 31, 8, 'academic', '2025-06-14', 'active', 'my student', '2025-06-14 14:19:43', '2025-06-14 14:19:43');

-- --------------------------------------------------------

--
-- Table structure for table `supervisor_meetings`
--

CREATE TABLE `supervisor_meetings` (
  `meeting_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `meeting_title` varchar(255) NOT NULL,
  `meeting_description` text DEFAULT NULL,
  `meeting_date` date NOT NULL,
  `meeting_time` time NOT NULL,
  `duration_minutes` int(11) DEFAULT 60,
  `location` varchar(255) DEFAULT NULL,
  `meeting_type` enum('physical','virtual','phone') DEFAULT 'physical',
  `meeting_link` varchar(500) DEFAULT NULL,
  `status` enum('scheduled','completed','cancelled','rescheduled') DEFAULT 'scheduled',
  `agenda` text DEFAULT NULL,
  `meeting_notes` text DEFAULT NULL,
  `action_items` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supervisor_messages`
--

CREATE TABLE `supervisor_messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `sender_type` enum('supervisor','student','admin') NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `recipient_type` enum('supervisor','student','admin') NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message_content` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_urgent` tinyint(1) DEFAULT 0,
  `parent_message_id` int(11) DEFAULT NULL,
  `attachment_path` varchar(500) DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_areas`
--

CREATE TABLE `training_areas` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `training_areas`
--

INSERT INTO `training_areas` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Software Development', 'Focuses on coding, software design, and development using various programming languages and frameworks.', '2025-05-04 15:33:26', '2025-05-04 15:33:26'),
(2, 'Electrical Systems', 'Involves working with electrical circuits, systems design, and maintenance of electrical equipment.', '2025-05-04 15:33:26', '2025-05-04 15:33:26'),
(3, 'Public Health', 'Training in the field of healthcare systems, public health policy, and epidemiology.', '2025-05-04 15:33:26', '2025-05-04 15:33:26'),
(4, 'Marketing & Sales', 'Training on market research, product promotion, and sales strategies to increase business revenue.', '2025-05-04 15:33:26', '2025-05-04 15:33:26'),
(5, 'Medical Research', 'Covers various medical research techniques, focusing on clinical studies and healthcare innovations.', '2025-05-04 15:33:26', '2025-05-04 15:33:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Supervisor','Invigilator') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending',
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `status`, `phone`, `address`, `profile_photo`) VALUES
(1, 'SINGIDA BS ', 'HACKER255@gmail.com', '$2y$10$7h7P0f5XEZ1oYgzuObH9bOulKYAQmleMbPqiocwaW.4VVdtdIMytW', 'Supervisor', '2024-06-06 19:35:28', 'Pending', NULL, NULL, NULL),
(2, 'SINGIDA BS m', 'yamatangazo@gmail.com', '$2y$10$1Fu/QyZoSDuI77M4.9M9BuAOwxCSRw1eG8nO0Nxit5mEpfHAIBSaC', 'Supervisor', '2024-06-06 22:34:42', 'Pending', NULL, NULL, NULL),
(4, 'MR HONEST', 'test@mail.com', '$2y$10$YtFurkdOSaQMPnKrL510YeGfb9F3v8kbmq4PHVsype3.lML1fzpha', 'Invigilator', '2024-06-06 22:36:47', 'Pending', NULL, NULL, NULL),
(6, 'honest', 'honesthatibu@GMAIL.COM', '$2y$10$vWnTv6VGpMp4q5oAtbPPXOnFwusgEiZYGPl5tI0YEqCMB.KtJ3wEO', 'Invigilator', '2024-06-07 05:57:15', 'Pending', NULL, NULL, NULL),
(7, 'KHATIBU ALI KHATIBU', 'yamatangazo1@gmail.com', '$2y$10$C1630dtPMDsamIDQruoBku5EroNRLBpyxYEdyQtL8O9vUrqymGA.W', 'Supervisor', '2024-06-08 08:46:11', 'Pending', NULL, NULL, ''),
(9, 'mr honest', 'majaribio5@gmail.com', '$2y$10$p1Ab07EEL09wRsQE3IImHOg.WhfNOYWf5GWXUmCEM8jyxd/cEaVzu', 'Supervisor', '2024-06-08 08:51:08', 'Pending', NULL, NULL, '1717836668_3ae3110a-90ee-403a-b842-6763ca7842da.jpg'),
(11, 'naadhifa alli', 'nadhi@gmail.com', '$2y$10$1UJ0abpVBSM.id5IR0/DqOaROCQnVkyzwPAybq6EuPfudm8FMWbEK', 'Supervisor', '2024-06-11 20:42:34', 'Pending', NULL, NULL, '1718138554_6b1674ec-f759-4446-ac56-a8d0b18e1913-fotor-bg-remover-20230926153817.png'),
(12, 'naadhifa alli', 'yamatangazo11@gmail.com', '$2y$10$.3mFUBDIRD0aPifuogpm1Oc4WZUxadaID2NYDPwbyIB3Brv/r7VHK', 'Supervisor', '2024-06-25 07:36:28', 'Pending', NULL, NULL, 'uploads/022e8d2c-3e61-4c66-b92a-d0a4acdc4880-fotor-bg-remover-2023092421488.png'),
(13, 'user', 'user@bmz.com', '$2y$10$gkRmP/jRuWp7Ww/gnqFpDOCZImM1zqZljRH/Moht5WDPIRy83cLZ2', 'Invigilator', '2024-06-25 08:54:36', 'Pending', NULL, NULL, 'uploads/6b1674ec-f759-4446-ac56-a8d0b18e1913-fotor-bg-remover-20230926153817.png'),
(18, 'Khamis Juma', 'khamisj@gmail.com', '$2y$10$k1IOQFgn41SIe.NBDvDQZeIgYi61rlJzgvQtrFz7nbRJ2KMiU6G02', 'Supervisor', '2024-07-21 10:01:32', 'Pending', NULL, NULL, 'uploads/450445711_432286263130497_77923197080337703_n.jpg'),
(19, 'Jecha Makame', 'jecham@gmail.com', '$2y$10$k1IOQFgn41SIe.NBDvDQZeIgYi61rlJzgvQtrFz7nbRJ2KMiU6G02', 'Supervisor', '2024-07-21 10:01:32', 'Pending', NULL, NULL, 'uploads/450445711_432286263130497_77923197080337703_n.jpg'),
(20, 'Mcha Khamis', 'khmcha@gmail.com', '$2y$10$k1IOQFgn41SIe.NBDvDQZeIgYi61rlJzgvQtrFz7nbRJ2KMiU6G02', 'Supervisor', '2024-07-21 10:01:32', 'Pending', NULL, NULL, 'uploads/450445711_432286263130497_77923197080337703_n.jpg'),
(22, 'Pandu Jecha', 'pandujecha@gmail.com', '$2y$10$DSX0vRqNOuJMcMDEa30nCeN9TLa1fz6hOWZDU.fsbkTgAWkd23uyW', 'Supervisor', '2024-07-29 05:32:13', 'Pending', NULL, NULL, 'uploads/461637240508.jpg'),
(23, 'Khamis Omar', 'hamis2r@gmail.com', '$2y$10$P/iVjHzistuuuN5nHtYdyOMXHHmeoxSIH3DoqkrPUhfS1gV6vGUFC', 'Invigilator', '2025-04-19 12:36:54', 'Pending', NULL, NULL, 'uploads/placeholder.png'),
(24, 'Kist Test', 'kist@iptsystem.com', '$2y$10$Oc9qr9d9Y/oHIG.WhVSfwe2Vxito8VvY/RKmFN9wpFkqB88tH0vya', 'Supervisor', '2025-05-25 07:02:22', 'Pending', NULL, NULL, 'uploads/istockphoto-1214977097-612x612.jpg'),
(25, 'Dr. John Smith', 'supervisor@test.com', '$2y$12$WFreiLzqzvDh3naT6z7yiOtnGzNWOjEsBaEJjELTIczsprwaXt4tC', 'Supervisor', '2025-06-13 21:25:05', 'Pending', NULL, NULL, NULL),
(26, 'Prof. Sarah Johnson', 'sarah.supervisor@test.com', '$2y$12$fQ7mgKhSzK04CMWwSOYu0OJy7uRue3sYYTMYbApwep39YGGws41Ey', 'Supervisor', '2025-06-13 21:25:05', 'Pending', NULL, NULL, NULL),
(29, 'Test Supervisor', 'test_supervisor@example.com', '$2y$10$klqMsV6A/RZGV7J7mUBT3uoaKKndgnAPUUm1LOElpwslEgoc5pu7W', 'Supervisor', '2025-06-14 14:12:26', 'active', '+255123456789', NULL, NULL),
(30, 'Web Test Supervisor', 'webtest@example.com', '$2y$10$y8X8LioyCeyRGaCHjll89.XsOm5jezPrU.JZD37CHI5q5v6HEVfF.', 'Supervisor', '2025-06-14 14:14:54', 'active', '255123456789', NULL, NULL),
(31, 'ELIA WILLIAM MARIKI', 'supervisor@supervisor.com', '$2y$10$sG6uphuAJW/GQ2yn4s45eOor9EClPSpWmLZwKLrwmo.WGZcfgy6AG', 'Supervisor', '2025-06-14 14:18:32', 'active', '0753225961', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_requests`
--

CREATE TABLE `user_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_text` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_qualification`
--
ALTER TABLE `academic_qualification`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_student_id` (`student_id`);

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
-- Indexes for table `colleges`
--
ALTER TABLE `colleges`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_details`
--
ALTER TABLE `contact_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `evaluations`
--
ALTER TABLE `evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `report_id` (`report_id`),
  ADD KEY `supervisor_id` (`supervisor_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `other_attachments`
--
ALTER TABLE `other_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `personal_details`
--
ALTER TABLE `personal_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `report_reviews`
--
ALTER TABLE `report_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `report_id` (`report_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `reg_number` (`reg_number`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `students_auth`
--
ALTER TABLE `students_auth`
  ADD PRIMARY KEY (`auth_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  ADD PRIMARY KEY (`evaluation_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_feedback`
--
ALTER TABLE `student_feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_reports`
--
ALTER TABLE `student_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `supervisors`
--
ALTER TABLE `supervisors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supervisor_assignments`
--
ALTER TABLE `supervisor_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD UNIQUE KEY `unique_assignment` (`supervisor_id`,`student_id`,`assignment_type`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `supervisor_meetings`
--
ALTER TABLE `supervisor_meetings`
  ADD PRIMARY KEY (`meeting_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `supervisor_messages`
--
ALTER TABLE `supervisor_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `parent_message_id` (`parent_message_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_qualification`
--
ALTER TABLE `academic_qualification`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `contact_details`
--
ALTER TABLE `contact_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `other_attachments`
--
ALTER TABLE `other_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `personal_details`
--
ALTER TABLE `personal_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `report_reviews`
--
ALTER TABLE `report_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `students_auth`
--
ALTER TABLE `students_auth`
  MODIFY `auth_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `student_documents`
--
ALTER TABLE `student_documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  MODIFY `evaluation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_feedback`
--
ALTER TABLE `student_feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_notifications`
--
ALTER TABLE `student_notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_reports`
--
ALTER TABLE `student_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `supervisors`
--
ALTER TABLE `supervisors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `supervisor_assignments`
--
ALTER TABLE `supervisor_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `supervisor_meetings`
--
ALTER TABLE `supervisor_meetings`
  MODIFY `meeting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supervisor_messages`
--
ALTER TABLE `supervisor_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `academic_qualification`
--
ALTER TABLE `academic_qualification`
  ADD CONSTRAINT `academic_qualification_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_details`
--
ALTER TABLE `contact_details`
  ADD CONSTRAINT `contact_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `other_attachments`
--
ALTER TABLE `other_attachments`
  ADD CONSTRAINT `other_attachments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `personal_details`
--
ALTER TABLE `personal_details`
  ADD CONSTRAINT `personal_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_reviews`
--
ALTER TABLE `report_reviews`
  ADD CONSTRAINT `report_reviews_ibfk_1` FOREIGN KEY (`report_id`) REFERENCES `student_reports` (`report_id`) ON DELETE CASCADE;

--
-- Constraints for table `students_auth`
--
ALTER TABLE `students_auth`
  ADD CONSTRAINT `students_auth_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_documents`
--
ALTER TABLE `student_documents`
  ADD CONSTRAINT `student_documents_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_evaluations`
--
ALTER TABLE `student_evaluations`
  ADD CONSTRAINT `student_evaluations_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_feedback`
--
ALTER TABLE `student_feedback`
  ADD CONSTRAINT `student_feedback_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_notifications`
--
ALTER TABLE `student_notifications`
  ADD CONSTRAINT `student_notifications_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `student_reports`
--
ALTER TABLE `student_reports`
  ADD CONSTRAINT `student_reports_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `supervisor_assignments`
--
ALTER TABLE `supervisor_assignments`
  ADD CONSTRAINT `supervisor_assignments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `supervisor_meetings`
--
ALTER TABLE `supervisor_meetings`
  ADD CONSTRAINT `supervisor_meetings_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE;

--
-- Constraints for table `supervisor_messages`
--
ALTER TABLE `supervisor_messages`
  ADD CONSTRAINT `supervisor_messages_ibfk_1` FOREIGN KEY (`parent_message_id`) REFERENCES `supervisor_messages` (`message_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
