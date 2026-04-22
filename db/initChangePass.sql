-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : db
-- Généré le : mer. 22 avr. 2026 à 14:02
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

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`categoryId`, `categoryName`, `categoryDescription`) VALUES
(1, 'Broken Authentication', 'Mauvaise gestion des mécanismes d’authentification permettant à un attaquant de compromettre des comptes (ex: session prévisible, absence de MFA).'),
(2, 'Broken Access Control', 'Mauvais contrôle des permissions permettant d’accéder à des ressources non autorisées (ex: IDOR).'),
(3, 'Privilege Escalation', 'Exploitation permettant d’augmenter ses privilèges (user → admin ou root).'),
(4, 'Authentication Bypass', 'Contournement des mécanismes de connexion sans fournir d’identifiants valides.'),
(5, 'SQL Injection', 'Injection de requêtes SQL malveillantes via des entrées utilisateur non filtrées.'),
(6, 'Command Injection', 'Exécution de commandes système arbitraires sur le serveur.'),
(7, 'Code Injection', 'Injection et exécution de code dans le contexte de l’application.'),
(8, 'Remote Code Execution', 'Exécution de code à distance, souvent critique.'),
(9, 'LDAP Injection', 'Injection dans des requêtes LDAP.'),
(10, 'XPath Injection', 'Injection dans des requêtes XPath sur XML.'),
(11, 'Stored XSS', 'Script malveillant stocké côté serveur et exécuté chez les utilisateurs.'),
(12, 'Reflected XSS', 'Script injecté via une requête HTTP et renvoyé immédiatement.'),
(13, 'DOM-Based XSS', 'XSS exécuté côté client via manipulation du DOM'),
(14, 'Cross-Site Request Forgery', 'Forcer un utilisateur authentifié à exécuter une action non voulue.'),
(15, 'Local File Inclusion', 'Inclusion de fichiers locaux via une entrée utilisateur.'),
(16, 'Remote File Inclusion', 'Inclusion de fichiers distants contrôlés par un attaquant.'),
(17, 'Path Traversal', 'Accès à des fichiers hors du répertoire prévu (../../etc/passwd).'),
(18, 'Unrestricted File Upload', 'Upload de fichiers malveillants (ex: webshell).'),
(19, 'Sensitive Data Exposure', 'Fuite de données sensibles (mauvais chiffrement, logs, etc.).'),
(20, 'Weak Cryptography', 'Utilisation d’algorithmes faibles ou obsolètes.'),
(21, 'Hardcoded Credentials', 'Identifiants stockés en dur dans le code.'),
(22, 'Insecure Storage', 'Données sensibles stockées sans protection adéquate.'),
(23, 'Server-Side Request Forgery', 'Forcer le serveur à effectuer des requêtes internes ou externes.'),
(24, 'Open Redirect', 'Redirection vers une URL malveillante contrôlée.'),
(25, 'DNS Rebinding', 'Contournement des politiques de sécurité via manipulation DNS.'),
(26, 'Security Misconfiguration', 'Mauvaise configuration (services exposés, debug actif, etc.).'),
(27, 'Default Credentials', 'Utilisation de comptes par défaut non modifiés.'),
(28, 'Exposed Admin Interface', 'Interface d’administration accessible publiquement.'),
(29, 'Improper Error Handling', 'Messages d’erreur révélant des informations sensibles.'),
(30, 'Business Logic Flaw', 'Exploitation d’un défaut dans la logique métier.'),
(31, 'Race Condition', 'Exploitation liée à des accès concurrents.'),
(32, 'Improper Input Validation', 'Absence ou faiblesse de validation des entrées.'),
(33, 'Buffer Overflow', 'Dépassement de tampon entraînant corruption mémoire.'),
(34, 'Use After Free', 'Utilisation d’une zone mémoire libérée.'),
(35, 'Integer Overflow', 'Dépassement de capacité d’un entier.'),
(36, 'Heap Spraying', 'Technique facilitant l’exploitation mémoire.'),
(37, 'Information Disclosure', 'Exposition d’informations sensibles (version, chemins, etc.).'),
(38, 'Directory Listing', 'Listing de fichiers accessible publiquement.'),
(39, 'Vulnerable Dependency', 'Utilisation de bibliothèques vulnérables.'),
(40, 'Dependency Confusion', 'Injection de paquets malveillants dans la supply chain.'),
(41, 'CORS Misconfiguration', 'Mauvaise configuration des politiques cross-origin.'),
(42, 'Clickjacking', 'Piéger un utilisateur via une interface invisible.'),
(43, 'Insecure Deserialization', 'Désérialisation de données non fiables.');

-- --------------------------------------------------------

--
-- Structure de la table `cve`
--

CREATE TABLE `cve` (
  `cveId` int(11) NOT NULL,
  `cveNumber` varchar(16) NOT NULL,
  `published` date NOT NULL,
  `lastModified` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `cveDescription` text NOT NULL,
  `exploitabilityScore` varchar(4) NOT NULL,
  `impactScore` varchar(4) NOT NULL,
  `baseScore` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cve`
--

INSERT INTO `cve` (`cveId`, `cveNumber`, `published`, `lastModified`, `status`, `cveDescription`, `exploitabilityScore`, `impactScore`, `baseScore`) VALUES
(1, 'CVE-2022-0847', '2022-10-03', '2025-06-11', 'Analysé', 'Une faille a été découverte dans la manière dont le membre « flags » de la nouvelle structure de tampon de canalisation n\'était pas correctement initialisé dans les fonctions `copy_page_to_iter_pipe` et `push_pipe` du noyau Linux, ce qui pouvait entraîner la présence de valeurs obsolètes. Un utilisateur local non privilégié pourrait exploiter cette faille pour écrire dans des pages du cache de pages correspondant à des fichiers en lecture seule et, ce faisant, élever ses privilèges sur le système.', '1.8', '5.9', '7.8'),
(2, 'CVE-2020-25213', '2020-09-09', '2025-11-07', 'Analysé', 'Le plugin File Manager (wp-file-manager) pour WordPress, dans ses versions antérieures à la 6.9, permet à des attaquants distants de télécharger et d\'exécuter du code PHP arbitraire, car il renomme un fichier de connexion elFinder non sécurisé pour lui attribuer l\'extension .php. Cela permet, par exemple, aux attaquants d\'exécuter la commande de téléchargement elFinder (ou mkfile et put) afin d\'écrire du code PHP dans le répertoire wp-content/plugins/wp-file-manager/lib/files/. Cette vulnérabilité a été exploitée en août et septembre 2020.', '3.9', '6.0', '10.0'),
(3, 'CVE-2017-0144', '2017-03-17', '2026-04-21', 'En cours d\'analyse', 'Le serveur SMBv1 sous Microsoft Windows Vista SP2 ; Windows Server 2008 SP2 et R2 SP1 ; Windows 7 SP1 ; Windows 8.1 ; Windows Server 2012 Gold et R2 ; Windows RT 8.1 ; et Windows 10 Gold, 1511 et 1607 ; ainsi que Windows Server 2016 permet à des attaquants distants d\'exécuter du code arbitraire via des paquets spécialement conçus, également appelée « vulnérabilité d\'exécution de code à distance SMB de Windows ». Cette vulnérabilité est différente de celles décrites dans les CVE-2017-0143, CVE-2017-0145, CVE-2017-0146 et CVE-2017-0148.', '2.8', '5.9', '8.8'),
(4, 'CVE-2014-0160', '2014-04-07', '2026-04-21', 'Analysé', 'Les implémentations (1) TLS et (2) DTLS dans OpenSSL 1.0.1, avant la version 1.0.1g, ne gèrent pas correctement les paquets de l\'extension Heartbeat, ce qui permet à des attaquants distants d\'obtenir des informations sensibles à partir de la mémoire du processus via des paquets spécialement conçus qui provoquent un dépassement de capacité de lecture de la mémoire tampon, comme l\'illustre la lecture de clés privées, en lien avec les fichiers d1_both.c et t1_lib.c, également connu sous le nom de bug Heartbleed.', '3.9', '3.6', '7.5'),
(5, 'CVE-2026-6771', '2026-04-21', '2026-04-22', 'En cours d\'analyse', 'Contournement des mesures de protection dans le DOM : composant de sécurité. Cette vulnérabilité a été corrigée dans Firefox 150, Firefox ESR 140.10, Thunderbird 150 et Thunderbird 140.10.', '3.9', '5.9', '9.8'),
(6, 'CVE-2026-6769', '2026-04-21', '2026-04-22', 'En cours d\'analyse', 'Élévation de privilèges dans le composant Debugger. Cette vulnérabilité a été corrigée dans Firefox 150, Firefox ESR 140.10, Thunderbird 150 et Thunderbird 140.10.', '2.8', '5.9', '8.8'),
(7, 'CVE-2026-6768', '2026-04-21', '2026-04-22', 'En cours d\'analyse', 'Contournement des mesures de protection dans le composant « Réseau : cookies ». Cette vulnérabilité a été corrigée dans Firefox 150 et Thunderbird 150.', '3.9', '5.9', '9.8'),
(8, 'CVE-2026-6757', '2026-04-21', '2026-04-22', 'En cours d\'analyse', 'Pointeur non valide dans le composant JavaScript : WebAssembly. Cette vulnérabilité a été corrigée dans Firefox 150, Firefox ESR 140.10, Thunderbird 150 et Thunderbird 140.10.', '', '', ''),
(9, 'CVE-2026-6754', '2026-04-21', '2026-04-22', 'En cours d\'analyse', 'Utilisation après libération de mémoire dans le composant du moteur JavaScript. Cette vulnérabilité a été corrigée dans Firefox 150, Firefox ESR 115.35, Firefox ESR 140.10, Thunderbird 150 et Thunderbird 140.10.', '3.9', '3.6', '7.5'),
(10, 'CVE-2026-40320', '2026-04-17', '2026-04-17', 'En cours d\'analyse', 'Giskard est un framework de test open source destiné aux modèles d\'IA. Dans les versions antérieures à la 1.0.2b1, la classe ConformityCheck rendait le paramètre de règle via le constructeur Template() par défaut de Jinja2, interprétant silencieusement les expressions de modèle lors de l\'exécution. Si des définitions de vérification sont chargées à partir d\'une source non fiable, une chaîne de règle spécialement conçue pourrait permettre l\'exécution de code arbitraire. L\'exploitation nécessite un accès en écriture à une définition de vérification, puis l’exécution de la suite de tests. Ce problème a été corrigé dans la version 1.0.2b1 de giskard-checks.', '', '', '5.4');

-- --------------------------------------------------------

--
-- Structure de la table `cve_category`
--

CREATE TABLE `cve_category` (
  `cveId` int(11) NOT NULL,
  `categoryId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `cve_category`
--

INSERT INTO `cve_category` (`cveId`, `categoryId`) VALUES
(1, 3),
(1, 32),
(1, 31),
(2, 8),
(2, 18),
(2, 2),
(2, 7),
(3, 8),
(3, 33),
(3, 32),
(4, 32),
(4, 37),
(4, 33),
(5, 4),
(5, 26),
(5, 32),
(6, 3),
(6, 32),
(7, 4),
(7, 26),
(7, 32),
(8, 33),
(8, 32),
(8, 8),
(9, 34),
(9, 33),
(9, 8),
(9, 32),
(10, 7),
(10, 8),
(10, 32);

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
  MODIFY `categoryId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT pour la table `cve`
--
ALTER TABLE `cve`
  MODIFY `cveId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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




GRANT USAGE ON *.* TO `chain-cve-user`@`%` IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE ON `chain-cve`.* TO `chain-cve-user`@`%`;