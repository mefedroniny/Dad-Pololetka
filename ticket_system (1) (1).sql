-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Pon 12. kvě 2025, 21:32
-- Verze serveru: 10.4.32-MariaDB
-- Verze PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `ticket_system`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `category` enum('software','hardware','ucet','jine') NOT NULL,
  `priority` enum('nizka','stredni','vysoka') NOT NULL,
  `problem` text NOT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `status` enum('otevreny','uzavreny') NOT NULL DEFAULT 'otevreny',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `admin_reply` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `tickets`
--

INSERT INTO `tickets` (`id`, `fullname`, `email`, `category`, `priority`, `problem`, `attachment`, `status`, `created_at`, `updated_at`, `admin_reply`, `assigned_to`) VALUES
(1, 'Adam', 'Parno@seznam.cz', 'software', 'nizka', 'cs', '', 'uzavreny', '2025-04-28 07:59:13', '2025-04-28 08:14:11', '', 0),
(2, 'parno', 'parno@seznam.cz', 'software', 'nizka', 'c', NULL, 'uzavreny', '2025-04-28 08:12:07', '2025-04-28 08:22:07', 'cs', 0),
(3, ',cscscscs;;', 'okpl@seznam.cz', 'software', 'nizka', 'cs', NULL, 'uzavreny', '2025-04-28 08:16:39', '2025-04-28 08:22:00', '', 0),
(4, 'sc', 'h.lavrikovova@seznam.cz', 'software', 'nizka', 'cs', NULL, 'uzavreny', '2025-05-12 16:42:27', '2025-05-12 17:01:54', NULL, 0),
(5, 'cx', 'h.lavrikovova@seznam.cz', 'software', 'nizka', '555', NULL, 'uzavreny', '2025-05-12 17:02:13', '2025-05-12 17:21:55', NULL, 0);

-- --------------------------------------------------------

--
-- Struktura tabulky `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Vypisuji data pro tabulku `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '', 'admin', 'user', '2025-04-28 07:54:44'),
(2, 'adminPOPO', '', '$2y$10$I5vwfzvE77MNd/CbuyIxbOTQdm51XXYEYSu9JXX3Ojtnyp8JA.tWu', 'admin', '2025-05-12 17:50:02'),
(3, 'admin9999', 'h.lavrikovova@seznam.cz', '$2y$10$kkm6rZksrzokvwmIvQAN/uzdmA71yHftQPMesY4JpNaR23RcEc5Z6', 'admin', '2025-05-12 17:57:17');

--
-- Indexy pro exportované tabulky
--

--
-- Indexy pro tabulku `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- Indexy pro tabulku `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pro tabulku `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
