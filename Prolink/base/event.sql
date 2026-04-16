-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 16 avr. 2026 à 03:38
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `projet`
--

-- --------------------------------------------------------

--
-- Structure de la table `event`
--

CREATE TABLE `event` (
  `id_event` int(11) NOT NULL,
  `titre_event` varchar(255) DEFAULT NULL,
  `description_event` text DEFAULT NULL,
  `type_event` enum('conference','formation','atelier') DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `lieu_event` varchar(255) DEFAULT NULL,
  `capacite_max` int(11) DEFAULT NULL,
  `statut` enum('en attente','confirme','annule') DEFAULT NULL,
  `id_org` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `event`
--

INSERT INTO `event` (`id_event`, `titre_event`, `description_event`, `type_event`, `date_debut`, `date_fin`, `lieu_event`, `capacite_max`, `statut`, `id_org`) VALUES
(6, ' asdfghjkl', 'SDFGHJKLasd', 'atelier', '5555-04-04', '5555-05-07', 'asdfgh', 55, 'confirme', 55),
(8, '5555', 'sdfghjk', 'conference', '5555-04-04', '5555-05-07', '5', 6623, '', 999),
(10, 'sdfghjsdfg', 'sdfghjksdfghjk', 'atelier', '2026-04-26', '2026-04-30', 'sdfghjkl', 55, '', 999),
(11, '5555', 'zxcvbnm,cxzsdfghjkl', 'atelier', '2026-04-25', '2026-04-30', '5555', 4555, '', 85555555),
(12, 'asdfghj', 'ASDFGHJKL;SDFGHJKL;', 'conference', '0000-00-00', '2026-05-03', 'sdfghj', 5555, 'en attente', 999),
(14, 'SDFGHJ', 'SDFGHJKL;LKJHGFDSA', 'conference', '2026-04-30', '2026-05-07', 'XCVBHJ', 66, 'confirme', 85555555),
(15, 'SDFGH', 'asdfghjkl;kjhgfdsa', 'conference', '2026-04-30', '2026-05-05', 'Jsdfghj', 4555, 'confirme', 85555555),
(16, 'sdgj', 'asdfghfgkkl', 'conference', '2026-05-29', '4444-04-04', 'zxcvb', 4, 'confirme', 999);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id_event`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `event`
--
ALTER TABLE `event`
  MODIFY `id_event` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
