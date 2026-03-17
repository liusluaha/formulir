-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2026 at 05:01 PM
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
-- Database: `form`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_admin`
--

CREATE TABLE `tb_admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_admin`
--

INSERT INTO `tb_admin` (`id`, `username`, `password`, `nama_lengkap`, `role`) VALUES
(16, 'admin', '$2y$10$L5ovm0T1qTPpn4olSKUyZuWexeQfQP06aiPJzGmdff6R1CBnOjq8K', 'Administrator', 'super_admin'),
(18, 'wr2', '$2y$10$mpmhc9CRr01hX0Uo3NMHWeftha5WNCwbphDFnMmEjzAHxWELigB36', 'wr2', 'admin'),
(19, 'spmb', '$2y$10$2xqP5SK/exFA5GQWBkshLeLmJlPDqkP.5YXM2MhNX5wBvLiRdsT2S', 'spmb', 'admin'),
(20, 'wr3', '$2y$10$5xtSw5ucz/oZfK/pXx259OqinXW4qgnkd3ses7N/H1k1YxuFobV6u', 'wr3', 'admin'),
(21, 'cdac', '$2y$10$eUQ41pADvS17PIbnYIchQuGrUr44UFvBpoHCeyFh4UarNbfUt9qE6', 'cdac', 'admin'),
(22, 'wr1', '$2y$10$la.rpY0OiR1QQUCf39N9J.LwxU4RLHpAmSQPWsaUtKGC6DdSwEGJe', 'wr1', 'admin'),
(23, 'wr4', '$2y$10$KEqfQ5IPfCtobkCXFbhbHOvNVQ.sqeB7iO3B/6cjPhl7NSnYpSTh2', 'wr4', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `tb_link_form`
--

CREATE TABLE `tb_link_form` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `unique_link_id` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `form_schema_json` longtext NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `limit_one_response` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_submissions`
--

CREATE TABLE `tb_submissions` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `submission_data_json` longtext NOT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_admin`
--
ALTER TABLE `tb_admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tb_link_form`
--
ALTER TABLE `tb_link_form`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_link_id` (`unique_link_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `tb_submissions`
--
ALTER TABLE `tb_submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_admin`
--
ALTER TABLE `tb_admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `tb_link_form`
--
ALTER TABLE `tb_link_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `tb_submissions`
--
ALTER TABLE `tb_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tb_link_form`
--
ALTER TABLE `tb_link_form`
  ADD CONSTRAINT `tb_link_form_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `tb_admin` (`id`);

--
-- Constraints for table `tb_submissions`
--
ALTER TABLE `tb_submissions`
  ADD CONSTRAINT `tb_submissions_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `tb_link_form` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
