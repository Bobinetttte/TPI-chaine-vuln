-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : mar. 21 avr. 2026 à 14:07
-- Version du serveur : 10.11.16-MariaDB-ubu2204
-- Version de PHP : 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `chain-cve`
--
CREATE DATABASE IF NOT EXISTS `chain-cve` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `chain-cve`;

-- --------------------------------------------------------

--
-- Structure de la table `association`
--

CREATE TABLE `association` (
  `associationId` int(11) NOT NULL,
  `score` int(11) NOT NULL,
  `explanation` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `association_cve`
--

CREATE TABLE `association_cve` (
  `associationId` int(11) NOT NULL,
  `cveId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
  `categoryId` int(11) NOT NULL,
  `categoryName` varchar(64) NOT NULL,
  `categoryDescription` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cve`
--

CREATE TABLE `cve` (
  `cveId` int(11) NOT NULL,
  `published` date NOT NULL,
  `lastModified` date NOT NULL,
  `status` varchar(12) NOT NULL,
  `cveDescription` text NOT NULL,
  `exploitabilityScore` varchar(4) NOT NULL,
  `impactScore` varchar(4) NOT NULL,
  `baseScore` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cve_category`
--

CREATE TABLE `cve_category` (
  `cveId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cve_version`
--

CREATE TABLE `cve_version` (
  `versionId` int(11) NOT NULL,
  `cveId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `software`
--

CREATE TABLE `software` (
  `softwareId` int(11) NOT NULL,
  `company` text NOT NULL,
  `softwareName` text NOT NULL,
  `cpeMatchWithoutVersion` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `version`
--

CREATE TABLE `version` (
  `versionId` int(11) NOT NULL,
  `cpeName` text NOT NULL,
  `version` text NOT NULL,
  `softwareId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `association`
--
ALTER TABLE `association`
  ADD PRIMARY KEY (`associationId`);

--
-- Index pour la table `association_cve`
--
ALTER TABLE `association_cve`
  ADD KEY `associationId` (`associationId`),
  ADD KEY `cveId` (`cveId`);

--
-- Index pour la table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`categoryId`);

--
-- Index pour la table `cve`
--
ALTER TABLE `cve`
  ADD PRIMARY KEY (`cveId`);

--
-- Index pour la table `cve_category`
--
ALTER TABLE `cve_category`
  ADD KEY `cveId` (`cveId`),
  ADD KEY `categoryId` (`categoryId`);

--
-- Index pour la table `cve_version`
--
ALTER TABLE `cve_version`
  ADD KEY `versionId` (`versionId`),
  ADD KEY `cveId` (`cveId`);

--
-- Index pour la table `software`
--
ALTER TABLE `software`
  ADD PRIMARY KEY (`softwareId`);

--
-- Index pour la table `version`
--
ALTER TABLE `version`
  ADD PRIMARY KEY (`versionId`),
  ADD UNIQUE KEY `softwareId` (`softwareId`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `association`
--
ALTER TABLE `association`
  MODIFY `associationId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cve`
--
ALTER TABLE `cve`
  MODIFY `cveId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `software`
--
ALTER TABLE `software`
  MODIFY `softwareId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `version`
--
ALTER TABLE `version`
  MODIFY `versionId` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `association_cve`
--
ALTER TABLE `association_cve`
  ADD CONSTRAINT `associationId` FOREIGN KEY (`associationId`) REFERENCES `association` (`associationId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cveId` FOREIGN KEY (`cveId`) REFERENCES `cve` (`cveId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cve_category`
--
ALTER TABLE `cve_category`
  ADD CONSTRAINT `categoryId` FOREIGN KEY (`categoryId`) REFERENCES `category` (`categoryId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `cveIDFkCategory` FOREIGN KEY (`cveId`) REFERENCES `cve` (`cveId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `cve_version`
--
ALTER TABLE `cve_version`
  ADD CONSTRAINT `cveIdFk` FOREIGN KEY (`cveId`) REFERENCES `cve` (`cveId`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `versionId` FOREIGN KEY (`versionId`) REFERENCES `version` (`versionId`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Contraintes pour la table `version`
--
ALTER TABLE `version`
  ADD CONSTRAINT `software_version` FOREIGN KEY (`softwareId`) REFERENCES `software` (`softwareId`) ON DELETE NO ACTION ON UPDATE NO ACTION;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
