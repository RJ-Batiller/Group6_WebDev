-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 01:27 PM
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
-- Database: `group6`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `created_at`) VALUES
(1, 'Arjaypwetmalu', 'r.batiller40810@mcc.edu.ph', '$2y$10$jWpVQDUZ/.7c12oY./YBqOj82adnwoEobkwSEDX1kIZ3kJObg2GEG', 'Arjay', 'Batiller', '2025-06-06 12:34:50'),
(3, 'Batiller', 'batillerarjay4@gmail.com', '$2y$10$ZMVSK8d823AMmb/8lm8u2.2M5KShGNbj4Qqj1usVn2dc3Hwm88xJu', 'RJ', 'BATILLER', '2025-06-12 08:56:18');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(100) NOT NULL,
  `isbn` varchar(20) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `publication_year` int(4) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `category`, `description`, `quantity`, `publication_year`, `cover_image`, `created_at`, `updated_at`) VALUES
(1, 'To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'Fiction', 'A classic of modern American literature', 5, 1960, 'assets/images/books/684ab035a2339.jpg', '2025-06-06 11:17:50', '2025-06-12 10:47:17'),
(2, 'The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Fiction', 'A classic of American literature', 3, 1925, 'assets/images/books/684aaed498049.png', '2025-06-06 11:17:50', '2025-06-12 10:41:24'),
(3, 'Solo Leveling', 'Chugong', '1975319281', 'Fantasy', 'This Story is based in present-day Seoul, South Korea, In a world where Mysterious \"Portals\" started appearing, each containing connected to dungeons that are filled with monsters from our nightmares, and if not dealt with, these monsters would begin pouring out of these portals to destroy the earth, to challenge these monsters people with superpowers started awakening these came to be known as hunters.', 4, 2018, 'assets/images/books/684ab020e1407.jpg', '2025-06-06 11:17:50', '2025-06-12 10:46:56');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_books`
--

CREATE TABLE `borrowed_books` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` timestamp NULL DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrowings`
--

CREATE TABLE `borrowings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `due_date` timestamp NULL DEFAULT NULL,
  `return_date` timestamp NULL DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') NOT NULL DEFAULT 'borrowed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowings`
--

INSERT INTO `borrowings` (`id`, `user_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `status`, `created_at`, `updated_at`) VALUES
(17, 6, 3, '2025-06-12 08:59:02', '2025-06-26 08:59:02', '2025-06-12 08:59:03', 'returned', '2025-06-12 08:59:02', '2025-06-12 08:59:03'),
(18, 6, 3, '2025-06-12 08:59:04', '2025-06-26 08:59:04', '2025-06-12 08:59:04', 'returned', '2025-06-12 08:59:04', '2025-06-12 08:59:04'),
(19, 6, 3, '2025-06-12 08:59:05', '2025-06-26 08:59:05', '2025-06-12 08:59:05', 'returned', '2025-06-12 08:59:05', '2025-06-12 08:59:05'),
(20, 6, 3, '2025-06-12 08:59:07', '2025-06-26 08:59:07', '2025-06-12 08:59:08', 'returned', '2025-06-12 08:59:07', '2025-06-12 08:59:08'),
(21, 6, 3, '2025-06-12 08:59:11', '2025-06-26 08:59:11', '2025-06-12 08:59:11', 'returned', '2025-06-12 08:59:11', '2025-06-12 08:59:11'),
(22, 6, 3, '2025-06-12 08:59:12', '2025-06-26 08:59:12', '2025-06-12 08:59:17', 'returned', '2025-06-12 08:59:12', '2025-06-12 08:59:17'),
(23, 6, 3, '2025-06-12 08:59:31', '2025-06-26 08:59:31', '2025-06-12 09:00:41', 'returned', '2025-06-12 08:59:31', '2025-06-12 09:00:41'),
(24, 6, 3, '2025-06-12 09:03:23', '2025-06-26 09:03:23', '2025-06-12 09:03:30', 'returned', '2025-06-12 09:03:23', '2025-06-12 09:03:30'),
(25, 6, 3, '2025-06-12 09:04:55', '2025-06-26 09:04:55', '2025-06-12 09:05:10', 'returned', '2025-06-12 09:04:55', '2025-06-12 09:05:10'),
(26, 6, 3, '2025-06-08 16:00:00', '2025-06-19 16:00:00', '2025-06-12 09:13:55', 'returned', '2025-06-12 09:13:39', '2025-06-12 09:13:55'),
(27, 6, 3, '2025-06-15 16:00:00', '2025-06-23 16:00:00', NULL, 'borrowed', '2025-06-12 09:55:45', '2025-06-12 09:55:45');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `type` enum('borrowing','inventory','user_activity') NOT NULL,
  `content` text NOT NULL,
  `generated_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'admin', '2025-06-06 11:10:17'),
(2, 'user', '2025-06-06 11:10:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `status` enum('active','pending','suspended') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `remember_token` varchar(64) DEFAULT NULL,
  `role_id` int(11) DEFAULT 2,
  `birthday` date DEFAULT NULL,
  `mobile_number` varchar(20) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `terms_accepted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `first_name`, `last_name`, `status`, `created_at`, `updated_at`, `remember_token`, `role_id`, `birthday`, `mobile_number`, `gender`, `terms_accepted`) VALUES
(6, 'ARRIANE', '$2y$10$XQ0beEYDR1ZCX7zLvQp1mOyIzoS0HODcJPVQXxBRsNG6izZ6THTjy', 'hush123@dsaaaaaa.com', 'Aira', 'sibal', 'active', '2025-06-12 08:52:49', '2025-06-12 10:52:59', NULL, 2, '2025-06-17', '09163339322', 'male', 1),
(7, 'BatillerArjay', '$2y$10$2e7Pe1py3DxIrCNz5XKccu0Y/KsD.k7zmb.L9PnaKf3SklmpQA.lK', 'aira@gmail.com', 'Arianne', 'Sibal', 'active', '2025-06-12 09:52:04', '2025-06-12 10:52:58', NULL, 2, '2025-06-08', '09212121475', 'male', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_activities`
--

CREATE TABLE `user_activities` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `activity_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bb_user` (`user_id`),
  ADD KEY `fk_bb_book` (`book_id`);

--
-- Indexes for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_borrowings_user` (`user_id`),
  ADD KEY `fk_borrowings_book` (`book_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_report_user` (`generated_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `remember_token_idx` (`remember_token`),
  ADD KEY `fk_role_id` (`role_id`);

--
-- Indexes for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_activity_user` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrowings`
--
ALTER TABLE `borrowings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_activities`
--
ALTER TABLE `user_activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD CONSTRAINT `fk_bb_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bb_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrowings`
--
ALTER TABLE `borrowings`
  ADD CONSTRAINT `fk_borrowings_book` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_borrowings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `fk_report_user` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_activities`
--
ALTER TABLE `user_activities`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
