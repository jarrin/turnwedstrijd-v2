-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Gegenereerd op: 15 feb 2026 om 20:34
-- Serverversie: 10.4.32-MariaDB
-- PHP-versie: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `turnwedstrijd`
--

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `deelnemer`
--

CREATE TABLE `deelnemer` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `lidnummer` int(11) NOT NULL,
  `geslacht` enum('Heren','Dames') NOT NULL,
  `groep_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `deelnemer`
--

INSERT INTO `deelnemer` (`id`, `naam`, `lidnummer`, `geslacht`, `groep_id`) VALUES
(1, 'Sven Jansen', 1022, 'Heren', 1),
(2, 'Tom Bakker', 1023, 'Heren', 2),
(3, 'Lisa de Vries', 2021, 'Dames', 3),
(4, 'Emma Smit', 2022, 'Dames', 4);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `groep`
--

CREATE TABLE `groep` (
  `id` int(11) NOT NULL,
  `naam` varchar(50) NOT NULL,
  `geslacht` enum('Heren','Dames') NOT NULL,
  `wedstrijd_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `groep`
--

INSERT INTO `groep` (`id`, `naam`, `geslacht`, `wedstrijd_id`) VALUES
(1, 'A1', 'Heren', 1),
(2, 'A2', 'Heren', 1),
(3, 'B1', 'Dames', 1),
(4, 'B2', 'Dames', 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `jury`
--

CREATE TABLE `jury` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `jury`
--

INSERT INTO `jury` (`id`, `naam`) VALUES
(1, 'Jury 1'),
(2, 'Jury 2'),
(3, 'Jury 3'),
(4, 'Jury 4');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `jury_onderdeel`
--

CREATE TABLE `jury_onderdeel` (
  `id` int(11) NOT NULL,
  `jury_id` int(11) NOT NULL,
  `onderdeel_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `jury_onderdeel`
--

INSERT INTO `jury_onderdeel` (`id`, `jury_id`, `onderdeel_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 7),
(4, 4, 8);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `onderdeel`
--

CREATE TABLE `onderdeel` (
  `id` int(11) NOT NULL,
  `naam` varchar(50) NOT NULL,
  `geslacht` enum('Heren','Dames') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `onderdeel`
--

INSERT INTO `onderdeel` (`id`, `naam`, `geslacht`) VALUES
(1, 'Vloer', 'Heren'),
(2, 'Ringen', 'Heren'),
(3, 'Brug', 'Heren'),
(4, 'Rek', 'Heren'),
(5, 'Sprong', 'Heren'),
(6, 'Vloer', 'Dames'),
(7, 'Balk', 'Dames'),
(8, 'Brug Ongelijk', 'Dames'),
(9, 'Sprong', 'Dames');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `score`
--

CREATE TABLE `score` (
  `id` int(11) NOT NULL,
  `deelnemer_id` int(11) NOT NULL,
  `onderdeel_id` int(11) NOT NULL,
  `jury_id` int(11) NOT NULL,
  `d_score` decimal(4,2) NOT NULL,
  `e_score` decimal(4,2) NOT NULL,
  `n_score` decimal(4,2) NOT NULL,
  `totaal_score` decimal(5,2) NOT NULL,
  `status_id` int(11) NOT NULL,
  `ingevoerd_op` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `score`
--

INSERT INTO `score` (`id`, `deelnemer_id`, `onderdeel_id`, `jury_id`, `d_score`, `e_score`, `n_score`, `totaal_score`, `status_id`, `ingevoerd_op`) VALUES
(1, 1, 1, 1, 6.50, 8.30, 0.40, 14.40, 2, '2026-02-15 19:30:58'),
(2, 2, 2, 2, 6.00, 8.00, 0.60, 13.40, 2, '2026-02-15 19:30:58'),
(3, 3, 7, 3, 5.80, 8.50, 0.30, 14.00, 2, '2026-02-15 19:30:58'),
(4, 4, 8, 4, 5.90, 8.20, 0.40, 13.70, 1, '2026-02-15 19:30:58');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `score_status`
--

CREATE TABLE `score_status` (
  `id` int(11) NOT NULL,
  `status` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `score_status`
--

INSERT INTO `score_status` (`id`, `status`) VALUES
(1, 'Ingevoerd'),
(2, 'Goedgekeurd'),
(3, 'Aangepast'),
(4, 'Afgekeurd');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `wedstrijd`
--

CREATE TABLE `wedstrijd` (
  `id` int(11) NOT NULL,
  `naam` varchar(100) NOT NULL,
  `datum` date NOT NULL,
  `locatie` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `wedstrijd`
--

INSERT INTO `wedstrijd` (`id`, `naam`, `datum`, `locatie`) VALUES
(1, 'Impala Clubwedstrijd', '2026-03-15', 'Sporthal Borchio');

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `deelnemer`
--
ALTER TABLE `deelnemer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lidnummer` (`lidnummer`),
  ADD KEY `groep_id` (`groep_id`);

--
-- Indexen voor tabel `groep`
--
ALTER TABLE `groep`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wedstrijd_id` (`wedstrijd_id`);

--
-- Indexen voor tabel `jury`
--
ALTER TABLE `jury`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `jury_onderdeel`
--
ALTER TABLE `jury_onderdeel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jury_id` (`jury_id`),
  ADD KEY `onderdeel_id` (`onderdeel_id`);

--
-- Indexen voor tabel `onderdeel`
--
ALTER TABLE `onderdeel`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `score`
--
ALTER TABLE `score`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deelnemer_id` (`deelnemer_id`),
  ADD KEY `onderdeel_id` (`onderdeel_id`),
  ADD KEY `jury_id` (`jury_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indexen voor tabel `score_status`
--
ALTER TABLE `score_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `wedstrijd`
--
ALTER TABLE `wedstrijd`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `deelnemer`
--
ALTER TABLE `deelnemer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `groep`
--
ALTER TABLE `groep`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `jury`
--
ALTER TABLE `jury`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `jury_onderdeel`
--
ALTER TABLE `jury_onderdeel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `onderdeel`
--
ALTER TABLE `onderdeel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT voor een tabel `score`
--
ALTER TABLE `score`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `score_status`
--
ALTER TABLE `score_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `wedstrijd`
--
ALTER TABLE `wedstrijd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `deelnemer`
--
ALTER TABLE `deelnemer`
  ADD CONSTRAINT `deelnemer_ibfk_1` FOREIGN KEY (`groep_id`) REFERENCES `groep` (`id`);

--
-- Beperkingen voor tabel `groep`
--
ALTER TABLE `groep`
  ADD CONSTRAINT `groep_ibfk_1` FOREIGN KEY (`wedstrijd_id`) REFERENCES `wedstrijd` (`id`);

--
-- Beperkingen voor tabel `jury_onderdeel`
--
ALTER TABLE `jury_onderdeel`
  ADD CONSTRAINT `jury_onderdeel_ibfk_1` FOREIGN KEY (`jury_id`) REFERENCES `jury` (`id`),
  ADD CONSTRAINT `jury_onderdeel_ibfk_2` FOREIGN KEY (`onderdeel_id`) REFERENCES `onderdeel` (`id`);

--
-- Beperkingen voor tabel `score`
--
ALTER TABLE `score`
  ADD CONSTRAINT `score_ibfk_1` FOREIGN KEY (`deelnemer_id`) REFERENCES `deelnemer` (`id`),
  ADD CONSTRAINT `score_ibfk_2` FOREIGN KEY (`onderdeel_id`) REFERENCES `onderdeel` (`id`),
  ADD CONSTRAINT `score_ibfk_3` FOREIGN KEY (`jury_id`) REFERENCES `jury` (`id`),
  ADD CONSTRAINT `score_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `score_status` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
