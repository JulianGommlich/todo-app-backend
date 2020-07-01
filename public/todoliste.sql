-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 22. Mai 2020 um 08:51
-- Server-Version: 10.4.8-MariaDB
-- PHP-Version: 7.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `todoliste`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `todoitem`
--

CREATE TABLE `todoitem` (
  `id` int(6) UNSIGNED NOT NULL,
  `itemname` varchar(30) NOT NULL,
  `listnummer` int(6) UNSIGNED NOT NULL,
  `itemdiscription` varchar(150) NOT NULL,
  `itempriority` int(2) NOT NULL,
  `dueDate` date NOT NULL,
  `itemstate` varchar(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `todolist`
--

CREATE TABLE `todolist` (
  `id` int(6) UNSIGNED NOT NULL,
  `listname` varchar(30) NOT NULL,
  `creator` int(6) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE `user` (
  `id` int(6) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL,
  `token` varchar(120) NOT NULL,
  `passwort` varchar(50) DEFAULT NULL,
  `reg_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Daten für Tabelle `user`
--

INSERT INTO `user` (`id`, `username`, `token`, `passwort`, `reg_date`) VALUES
(1, 'Picard', '4b3403665fea6', 'absolut', '2020-05-22 06:49:37'),
(2, 'Kirk', '4b3793665dxa8', 'streng', '2020-05-22 06:49:37'),
(3, 'Spock', '4237d3665a5d8', 'geheim', '2020-05-22 06:49:37');

--
-- Daten für Tabelle `todolist`
--
INSERT INTO `todolist` (`id`, `listname`, `creator`) VALUES
(1, 'Eine erste Liste', 1),
(2, 'Eine zweite Liste', 1);

--
-- Daten für Tabelle `todoitem`
--
INSERT INTO `todoitem` (`id`, `itemname`, `listnummer`, `itemdiscription`, `itempriority`, `dueDate`, `itemstate`) VALUES
(1, 'Ein erstes Item', 1, 'Dies ist ein erstes Item', 1, '2020-07-22', 1),
(2, 'Ein zweites Item', 1, 'Dies ist ein zweites Item', 3, '', 2),
(3, 'Ein drittes Item', 1, 'Dies ist ein drittes Item', 5, '2020-05-22', 4),
(4, 'Erstes Item', 2, 'Dies ist ein erstes Item', 2, '', 5),
(5, 'Zweites Item', 2, 'Dies ist ein zweites Item', 5, '2020-08-22', 3),
(6, 'Drittes Item', 2, 'Dies ist ein drittes Item', 1, '2020-05-22', 4);


--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `todoitem`
--
ALTER TABLE `todoitem`
  ADD PRIMARY KEY (`id`),
  ADD KEY `listnummer` (`listnummer`);

--
-- Indizes für die Tabelle `todolist`
--
ALTER TABLE `todolist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creator` (`creator`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `todoitem`
--
ALTER TABLE `todoitem`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `todolist`
--
ALTER TABLE `todolist`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT für Tabelle `user`
--
ALTER TABLE `user`
  MODIFY `id` int(6) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `todoitem`
--
ALTER TABLE `todoitem`
  ADD CONSTRAINT `todoitem_ibfk_1` FOREIGN KEY (`listnummer`) REFERENCES `todolist` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `todolist`
--
ALTER TABLE `todolist`
  ADD CONSTRAINT `todolist_ibfk_1` FOREIGN KEY (`creator`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
