-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 09:38 AM
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
-- Database: `ucv_library`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `total_copies` int(11) NOT NULL DEFAULT 1,
  `available_copies` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `isbn`, `category`, `total_copies`, `available_copies`, `created_at`) VALUES
(1, 'Introduction to Programming', 'John Smith', '978-0001', 'Computer Science', 5, 5, '2026-04-28 07:35:35'),
(2, 'Database Systems', 'Maria Cruz', '978-0002', 'Computer Science', 3, 3, '2026-04-28 07:35:35'),
(3, 'Philippine History', 'Jose Rizal Jr.', '978-0003', 'History', 4, 4, '2026-04-28 07:35:35'),
(4, 'English Grammar', 'Ana Reyes', '978-0004', 'Language', 6, 6, '2026-04-28 07:35:35'),
(5, 'Calculus 101', 'Pedro Santos', '978-0005', 'Mathematics', 4, 4, '2026-04-28 07:35:35'),
(6, 'Web Development Basics', 'Liza Tan', '978-0006', 'Computer Science', 3, 3, '2026-04-28 07:35:35'),
(7, 'World Literature', 'Carmen Lopez', '978-0007', 'Literature', 5, 5, '2026-04-28 07:35:35'),
(8, 'Physics for Everyone', 'Mark dela Cruz', '978-0008', 'Science', 3, 3, '2026-04-28 07:35:35'),
(9, 'Data Structures and Algorithms', 'Robert Tan', '978-0009', 'Computer Science', 4, 4, '2026-04-28 07:35:35'),
(10, 'Rizal''s Life and Works', 'Teodoro Agoncillo', '978-0010', 'History', 5, 5, '2026-04-28 07:35:35'),
(11, 'General Chemistry', 'Linda Garcia', '978-0011', 'Science', 4, 4, '2026-04-28 07:35:35'),
(12, 'Algebra and Trigonometry', 'Ramon Flores', '978-0012', 'Mathematics', 5, 5, '2026-04-28 07:35:35'),
(13, 'Noli Me Tangere', 'Jose Rizal', '978-0013', 'Literature', 6, 6, '2026-04-28 07:35:35'),
(14, 'El Filibusterismo', 'Jose Rizal', '978-0014', 'Literature', 6, 6, '2026-04-28 07:35:35'),
(15, 'Object-Oriented Programming with Java', 'Michael Reyes', '978-0015', 'Computer Science', 4, 4, '2026-04-28 07:35:35'),
(16, 'Discrete Mathematics', 'Sofia Mendoza', '978-0016', 'Mathematics', 3, 3, '2026-04-28 07:35:35'),
(17, 'Filipino Sa Iba''t Ibang Disiplina', 'Andrea Bautista', '978-0017', 'Language', 5, 5, '2026-04-28 07:35:35'),
(18, 'Introduction to Sociology', 'Daniel Castro', '978-0018', 'Social Science', 4, 4, '2026-04-28 07:35:35'),
(19, 'General Psychology', 'Patricia Villanueva', '978-0019', 'Social Science', 4, 4, '2026-04-28 07:35:35'),
(20, 'Networking Fundamentals', 'Carlos Aquino', '978-0020', 'Computer Science', 3, 3, '2026-04-28 07:35:35');

-- --------------------------------------------------------

--
-- Table structure for table `borrows`
--

CREATE TABLE `borrows` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `status` enum('borrowed','returned','overdue') NOT NULL DEFAULT 'borrowed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `created_at`) VALUES
(2, 'admin', 'admin@admin.com', '$2y$10$riZ4HBmz37rdR8KzETGPM.rdKHFL34v2czLSP2CNAKMVMh93S5UPe', 'admin', 'admin', '2026-04-28 07:35:35'),
(4, 'student', 'student@gmail.com', '$2y$10$EO49rbVWOw1ZE6CfdaDbhubXCO96qsUAdfOANDVz3jz/N4usz0Ca6', 'juan dela cruz', 'user', '2026-04-28 07:37:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `borrows`
--
ALTER TABLE `borrows`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `borrows`
--
ALTER TABLE `borrows`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrows`
--
ALTER TABLE `borrows`
  ADD CONSTRAINT `borrows_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrows_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
