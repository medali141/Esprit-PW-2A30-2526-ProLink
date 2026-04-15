-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 15, 2026 at 04:08 PM
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
-- Database: `prolink`
--

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `iduser` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `type` enum('admin','candidat','entrepreneur') NOT NULL,
  `age` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`iduser`, `nom`, `prenom`, `email`, `mdp`, `type`, `age`, `created_at`) VALUES
(5, 'chihaoui', 'mohammed ali', 'chihaouidali443@gmail.com', '$2y$10$legH0oYVvjIhe4ZLne4sB.c9YW8gctbNfWe5jGmRNTfPkckJp.zAe', 'admin', 75, '2026-04-15 13:30:47'),
(7, 'chihaoui', 'mohammed ali', 'chihaouidali44@gmail.com', '$2y$10$NTalhxoL.rH.uSh8sjUJlu1yhfRbg4moZpn3M3qGey03UQ8zUfmwe', 'candidat', 24, '2026-04-15 13:41:01'),
(8, 'aissa', 'idouni', 'issado@gmail.com', '$2y$10$7rT5hKUUaUBeuQUYeTkqXuVdu5nIHRn85NU9fjLAsorNAC.P3fdCy', 'candidat', 75, '2026-04-15 13:44:12');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`iduser`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `iduser` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
