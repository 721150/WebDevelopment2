-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Gegenereerd op: 02 mrt 2025 om 09:20
-- Serverversie: 11.7.2-MariaDB-ubu2404
-- PHP-versie: 8.2.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `web-development-2`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `applicant`
--

CREATE TABLE `applicant` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `educationId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `blog`
--

CREATE TABLE `blog` (
  `id` int(11) NOT NULL,
  `dateTime` datetime NOT NULL,
  `institutionId` int(11) NOT NULL,
  `educationId` int(11) NOT NULL,
  `subjectId` int(11) NOT NULL,
  `typeOfLawId` int(11) NOT NULL,
  `description` varchar(500) NOT NULL,
  `content` varchar(15000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `case`
--

CREATE TABLE `case` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `subjectId` int(11) NOT NULL,
  `typeOfLawId` int(11) NOT NULL,
  `content` varchar(15000) NOT NULL,
  `statusId` int(11) NOT NULL,
  `institutionId` int(11) NOT NULL,
  `educationId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `document`
--

CREATE TABLE `document` (
  `id` int(11) NOT NULL,
  `caseId` int(11) NOT NULL,
  `document` longblob NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `education`
--

CREATE TABLE `education` (
  `id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `handler`
--

CREATE TABLE `handler` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `typeOfLawId` int(11) NOT NULL,
  `subjectId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `institution`
--

CREATE TABLE `institution` (
  `id` int(11) NOT NULL,
  `name` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `reactie`
--

CREATE TABLE `reactie` (
  `id` int(11) NOT NULL,
  `blogId` int(11) NOT NULL,
  `dateTime` datetime NOT NULL,
  `content` varchar(15000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `subject`
--

CREATE TABLE `subject` (
  `id` int(11) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `typeOfLaw`
--

CREATE TABLE `typeOfLaw` (
  `id` int(11) NOT NULL,
  `description` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `firstname` varchar(500) NOT NULL,
  `lastname` varchar(500) NOT NULL,
  `email` varchar(500) NOT NULL,
  `institutionId` int(11) NOT NULL,
  `image` longblob NOT NULL,
  `phone` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `applicant`
--
ALTER TABLE `applicant`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educationId` (`educationId`),
  ADD KEY `userId` (`userId`);

--
-- Indexen voor tabel `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educationId` (`educationId`),
  ADD KEY `institutionId` (`institutionId`),
  ADD KEY `typeOfLawId` (`typeOfLawId`),
  ADD KEY `subjectId` (`subjectId`);

--
-- Indexen voor tabel `case`
--
ALTER TABLE `case`
  ADD PRIMARY KEY (`id`),
  ADD KEY `educationId` (`educationId`),
  ADD KEY `institutionId` (`institutionId`),
  ADD KEY `statusId` (`statusId`),
  ADD KEY `subjectId` (`subjectId`),
  ADD KEY `typeOfLawId` (`typeOfLawId`),
  ADD KEY `userId` (`userId`);

--
-- Indexen voor tabel `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `casusId` (`caseId`);

--
-- Indexen voor tabel `education`
--
ALTER TABLE `education`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `handler`
--
ALTER TABLE `handler`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subjectId` (`subjectId`),
  ADD KEY `typeOfLawId` (`typeOfLawId`),
  ADD KEY `userId` (`userId`);

--
-- Indexen voor tabel `institution`
--
ALTER TABLE `institution`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `reactie`
--
ALTER TABLE `reactie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blogId` (`blogId`);

--
-- Indexen voor tabel `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `typeOfLaw`
--
ALTER TABLE `typeOfLaw`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `institutionId` (`institutionId`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `applicant`
--
ALTER TABLE `applicant`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `case`
--
ALTER TABLE `case`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `document`
--
ALTER TABLE `document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `education`
--
ALTER TABLE `education`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `handler`
--
ALTER TABLE `handler`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `institution`
--
ALTER TABLE `institution`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `reactie`
--
ALTER TABLE `reactie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `typeOfLaw`
--
ALTER TABLE `typeOfLaw`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT voor een tabel `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `applicant`
--
ALTER TABLE `applicant`
  ADD CONSTRAINT `applicant_ibfk_1` FOREIGN KEY (`educationId`) REFERENCES `education` (`id`),
  ADD CONSTRAINT `applicant_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);

--
-- Beperkingen voor tabel `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`educationId`) REFERENCES `education` (`id`),
  ADD CONSTRAINT `blog_ibfk_2` FOREIGN KEY (`institutionId`) REFERENCES `institution` (`id`),
  ADD CONSTRAINT `blog_ibfk_3` FOREIGN KEY (`typeOfLawId`) REFERENCES `typeOfLaw` (`id`),
  ADD CONSTRAINT `blog_ibfk_4` FOREIGN KEY (`subjectId`) REFERENCES `subject` (`id`);

--
-- Beperkingen voor tabel `case`
--
ALTER TABLE `case`
  ADD CONSTRAINT `case_ibfk_1` FOREIGN KEY (`educationId`) REFERENCES `education` (`id`),
  ADD CONSTRAINT `case_ibfk_2` FOREIGN KEY (`institutionId`) REFERENCES `institution` (`id`),
  ADD CONSTRAINT `case_ibfk_3` FOREIGN KEY (`statusId`) REFERENCES `status` (`id`),
  ADD CONSTRAINT `case_ibfk_4` FOREIGN KEY (`subjectId`) REFERENCES `subject` (`id`),
  ADD CONSTRAINT `case_ibfk_5` FOREIGN KEY (`typeOfLawId`) REFERENCES `typeOfLaw` (`id`),
  ADD CONSTRAINT `case_ibfk_6` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);

--
-- Beperkingen voor tabel `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`caseId`) REFERENCES `case` (`id`);

--
-- Beperkingen voor tabel `handler`
--
ALTER TABLE `handler`
  ADD CONSTRAINT `handler_ibfk_1` FOREIGN KEY (`subjectId`) REFERENCES `subject` (`id`),
  ADD CONSTRAINT `handler_ibfk_2` FOREIGN KEY (`typeOfLawId`) REFERENCES `typeOfLaw` (`id`),
  ADD CONSTRAINT `handler_ibfk_3` FOREIGN KEY (`userId`) REFERENCES `user` (`id`);

--
-- Beperkingen voor tabel `reactie`
--
ALTER TABLE `reactie`
  ADD CONSTRAINT `reactie_ibfk_1` FOREIGN KEY (`blogId`) REFERENCES `blog` (`id`);

--
-- Beperkingen voor tabel `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`institutionId`) REFERENCES `institution` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
