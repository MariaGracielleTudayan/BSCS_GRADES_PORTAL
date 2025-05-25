-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 25, 2025 at 03:50 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_verification`
--

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `midterm_grade` decimal(5,2) NOT NULL,
  `final_grade` decimal(5,2) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`id`, `student_id`, `student_name`, `subject_code`, `subject_name`, `midterm_grade`, `final_grade`, `semester`, `academic_year`, `created_at`, `updated_at`) VALUES
(1, 'NLP - 22 - 00506', 'Esperanza, Mark Chester', 'CS 301', 'Programming Languages', 1.75, 1.25, '1st Semester', '2024', '2025-05-25 13:26:48', '2025-05-25 13:26:48'),
(2, 'NLP - 22 - 00028', 'Gazmen, Jenny', 'CS 306', 'Computational Science', 1.25, 1.25, '1st Semester', '2024', '2025-05-25 13:27:11', '2025-05-25 13:27:11'),
(3, 'NLP - 22 - 00755', 'Infante, Cristine Jhed', 'CS 302', 'Automata Theory and Formal Languages', 1.75, 1.75, '1st Semester', '2024', '2025-05-25 13:27:34', '2025-05-25 13:27:34'),
(4, 'NLP - 22 - 01118', 'Tudayan, Maria Gracielle', 'CS 304', 'Operating Systems', 1.75, 1.25, '1st Semester', '2024', '2025-05-25 13:28:12', '2025-05-25 13:28:12'),
(5, 'NLP - 22 - 00181', 'Valdez, Chryson Neil', 'CS 307', 'Quantitative Methods', 1.75, 1.75, '2nd Semester', '2024', '2025-05-25 13:28:44', '2025-05-25 13:28:44');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `year_level` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_id`, `first_name`, `last_name`, `year_level`, `created_at`, `updated_at`) VALUES
(1, 'NLP - 22 - 01118', 'Maria Gracielle', 'Tudayan', '3rd Year', '2025-05-25 13:00:19', '2025-05-25 13:00:19'),
(3, 'NLP - 22 - 00506', 'Mark Chester', 'Esperanza', '3rd Year', '2025-05-25 13:02:02', '2025-05-25 13:02:02'),
(4, 'NLP - 22 - 00028', 'Jenny', 'Gazmen', '3rd Year', '2025-05-25 13:04:59', '2025-05-25 13:04:59'),
(5, 'NLP - 22 - 00755', 'Cristine Jhed', 'Infante', '3rd Year', '2025-05-25 13:05:49', '2025-05-25 13:05:49'),
(6, 'NLP - 22 - 00181', 'Chryson Neil', 'Valdez', '3rd Year', '2025-05-25 13:06:18', '2025-05-25 13:06:18');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(255) NOT NULL,
  `units` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `year_level` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_name`, `units`, `description`, `year_level`, `semester`, `created_at`, `updated_at`) VALUES
(1, 'CS 101', 'Introduction to Computing', 3, NULL, '1st Year', '1st Semester', '2025-05-25 12:41:53', '2025-05-25 12:41:53'),
(2, 'CS 102', 'Fundamentals of Programming', 3, NULL, '1st Year', '1st Semester', '2025-05-25 12:42:18', '2025-05-25 12:42:18'),
(3, 'CS 103', 'Intermediate Programming', 3, NULL, '1st Year', '2nd Semester', '2025-05-25 12:42:55', '2025-05-25 12:43:42'),
(4, 'CS 104', 'Discrete Structures 1', 3, NULL, '1st Year', '2nd Semester', '2025-05-25 12:43:22', '2025-05-25 12:45:31'),
(5, 'CS 105', 'Fundamentals of Human Computer Interaction', 3, NULL, '1st Year', '2nd Semester', '2025-05-25 12:44:22', '2025-05-25 12:44:22'),
(6, 'CS 201', 'Data Structures and Algorithms', 3, NULL, '2nd Year', '1st Semester', '2025-05-25 12:45:02', '2025-05-25 12:45:02'),
(7, 'CS 202', 'Discrete Strucutres 2', 3, NULL, '2nd Year', '1st Semester', '2025-05-25 12:45:23', '2025-05-25 12:46:48'),
(8, 'CS 203', 'Social Issues and Professional Practices', 3, NULL, '2nd Year', '1st Semester', '2025-05-25 12:46:11', '2025-05-25 12:46:55'),
(9, 'CS 204', 'Parallel and Distributive Computing', 3, NULL, '2nd Year', '1st Semester', '2025-05-25 12:46:41', '2025-05-25 12:46:41'),
(10, 'CS 205', 'Object-oriented Programming', 3, NULL, '2nd Year', '1st Semester', '2025-05-25 12:47:41', '2025-05-25 12:47:41'),
(11, 'CS 206', 'System Fundamentals', 3, NULL, '2nd Year', '2nd Semester', '2025-05-25 12:48:32', '2025-05-25 12:48:32'),
(12, 'CS 207', 'Information Management', 3, NULL, '2nd Year', '2nd Semester', '2025-05-25 12:48:57', '2025-05-25 12:48:57'),
(13, 'CS 208', 'Architecture and Organization', 3, NULL, '2nd Year', '2nd Semester', '2025-05-25 12:49:25', '2025-05-25 12:49:25'),
(14, 'CS 209', 'Application Development and Emerging Technologies', 3, NULL, '2nd Year', '2nd Semester', '2025-05-25 12:50:16', '2025-05-25 12:50:16'),
(15, 'CS 301', 'Programming Languages', 3, NULL, '3rd Year', '1st Semester', '2025-05-25 12:50:47', '2025-05-25 12:50:47'),
(16, 'CS 302', 'Automata Theory and Formal Languages', 3, NULL, '3rd Year', '1st Semester', '2025-05-25 12:51:16', '2025-05-25 12:51:16'),
(17, 'CS 303', 'Networks and Communication', 3, NULL, '3rd Year', '1st Semester', '2025-05-25 12:51:52', '2025-05-25 12:51:52'),
(18, 'CS 304', 'Operating Systems', 3, NULL, '3rd Year', '1st Semester', '2025-05-25 12:52:30', '2025-05-25 12:52:30'),
(19, 'CS 305', 'Software Engineering 1', 3, NULL, '3rd Year', '1st Semester', '2025-05-25 12:53:02', '2025-05-25 12:53:02'),
(20, 'CS 306', 'Computational Science', 3, NULL, '3rd Year', '1st Semester', '2025-05-25 12:53:34', '2025-05-25 12:53:34'),
(21, 'CS 307', 'Quantitative Methods', 3, NULL, '3rd Year', '1st Semester', '2025-05-25 12:54:14', '2025-05-25 12:54:14'),
(22, 'CS 308', 'Software Engineering 2', 3, NULL, '3rd Year', '2nd Semester', '2025-05-25 12:54:47', '2025-05-25 12:54:47'),
(23, 'CS 309', 'Algorithm and Complexity', 3, NULL, '3rd Year', '2nd Semester', '2025-05-25 12:55:20', '2025-05-25 12:55:20'),
(24, 'CS 310', 'Intelligent System', 3, NULL, '3rd Year', '2nd Semester', '2025-05-25 12:56:00', '2025-05-25 12:56:00'),
(25, 'CS 311', 'Graphics and Visual Arts Computing', 3, NULL, '3rd Year', '2nd Semester', '2025-05-25 12:56:46', '2025-05-25 12:56:46'),
(26, 'CS 312', 'Research Methodology', 3, NULL, '3rd Year', '2nd Semester', '2025-05-25 12:57:17', '2025-05-25 12:57:17'),
(27, 'CS 313', 'Web Development', 3, NULL, '3rd Year', '2nd Semester', '2025-05-25 12:57:38', '2025-05-25 12:57:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `otp_expiry` datetime NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `otp_code`, `otp_expiry`, `is_verified`) VALUES
(1, 'gracielletudayan0526@gmail.com', '813425', '2025-05-25 21:39:52', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

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
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
