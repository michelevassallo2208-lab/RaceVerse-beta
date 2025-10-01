-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Versione server:              10.4.32-MariaDB - mariadb.org binary distribution
-- S.O. server:                  Win64
-- HeidiSQL Versione:            12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dump della struttura del database simhub
CREATE DATABASE IF NOT EXISTS `simhub` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `simhub`;

-- Dump della struttura di tabella simhub.cars
CREATE TABLE IF NOT EXISTS `cars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `cars_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cars_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dump dei dati della tabella simhub.cars: ~18 rows (circa)
INSERT INTO `cars` (`id`, `game_id`, `category_id`, `name`, `image_path`) VALUES
	(1, 1, 1, 'Ferrari 499P', NULL),
	(2, 1, 1, 'Porsche 963', NULL),
	(3, 1, 1, 'Toyota GR010 Hybrid', NULL),
	(4, 1, 1, 'Cadillac V-Series.R', NULL),
	(5, 1, 1, 'BMW M Hybrid V8', NULL),
	(6, 1, 1, 'Peugeot 9X8', NULL),
	(7, 1, 1, 'Alpine A424', NULL),
	(8, 1, 1, 'Isotta Fraschini Tipo 6 LMH-C', NULL),
	(9, 1, 2, 'Oreca 07-Gibson', NULL),
	(10, 1, 3, 'Aston Martin Vantage AMR GT3', NULL),
	(11, 1, 3, 'BMW M4 GT3', NULL),
	(12, 1, 3, 'Chevrolet Corvette Z06 GT3.R', NULL),
	(13, 1, 3, 'Ferrari 296 GT3', NULL),
	(14, 1, 3, 'Ford Mustang GT3', NULL),
	(15, 1, 3, 'Lamborghini Huracán GT3 EVO2', NULL),
	(16, 1, 3, 'Lexus RC F GT3', NULL),
	(17, 1, 3, 'McLaren 720S GT3 Evo', NULL),
	(18, 1, 3, 'Porsche 911 GT3 R (992)', NULL);

-- Dump della struttura di tabella simhub.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dump dei dati della tabella simhub.categories: ~3 rows (circa)
INSERT INTO `categories` (`id`, `name`) VALUES
	(1, 'Hypercar'),
	(2, 'LMP2'),
	(3, 'LMGT3');

-- Dump della struttura di tabella simhub.games
CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dump dei dati della tabella simhub.games: ~1 rows (circa)
INSERT INTO `games` (`id`, `name`) VALUES
	(1, 'LMU');

-- Dump della struttura di tabella simhub.hotlaps
CREATE TABLE IF NOT EXISTS `hotlaps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `track_id` int(11) NOT NULL,
  `car_id` int(11) NOT NULL,
  `driver` varchar(120) DEFAULT NULL,
  `lap_time_ms` int(11) NOT NULL,
  `recorded_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `track_id` (`track_id`),
  KEY `car_id` (`car_id`),
  KEY `game_id` (`game_id`,`category_id`,`track_id`,`lap_time_ms`),
  CONSTRAINT `hotlaps_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hotlaps_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hotlaps_ibfk_3` FOREIGN KEY (`track_id`) REFERENCES `tracks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `hotlaps_ibfk_4` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=206 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dump dei dati della tabella simhub.hotlaps: ~205 rows (circa)
INSERT INTO `hotlaps` (`id`, `game_id`, `category_id`, `track_id`, `car_id`, `driver`, `lap_time_ms`, `recorded_at`) VALUES
	(1, 1, 1, 1, 1, 'Setup RaceVerse', 93700, '2025-10-01 11:41:47'),
	(2, 1, 1, 1, 2, 'Setup RaceVerse', 93574, '2025-10-01 11:41:47'),
	(3, 1, 1, 1, 3, 'Setup RaceVerse', 93552, '2025-10-01 11:41:47'),
	(4, 1, 1, 1, 4, 'Setup RaceVerse', 93661, '2025-10-01 11:41:47'),
	(5, 1, 1, 1, 5, 'Setup RaceVerse', 94770, '2025-10-01 11:41:47'),
	(6, 1, 1, 1, 6, 'Setup RaceVerse', 94239, '2025-10-01 11:41:47'),
	(7, 1, 1, 1, 7, 'Setup RaceVerse', 94500, '2025-10-01 11:41:47'),
	(8, 1, 1, 1, 8, 'Setup RaceVerse', 94600, '2025-10-01 11:41:47'),
	(9, 1, 1, 7, 1, 'Setup RaceVerse', 88953, '2025-10-01 11:59:33'),
	(10, 1, 1, 7, 2, 'Setup RaceVerse', 88870, '2025-10-01 11:59:33'),
	(11, 1, 1, 7, 3, 'Setup RaceVerse', 89013, '2025-10-01 11:59:33'),
	(12, 1, 1, 7, 4, 'Setup RaceVerse', 88702, '2025-10-01 11:59:33'),
	(13, 1, 1, 7, 5, 'Setup RaceVerse', 89346, '2025-10-01 11:59:33'),
	(14, 1, 1, 7, 6, 'Setup RaceVerse', 88570, '2025-10-01 11:59:33'),
	(15, 1, 1, 7, 7, 'Setup RaceVerse', 90688, '2025-10-01 11:59:33'),
	(16, 1, 1, 7, 8, 'Setup RaceVerse', 89400, '2025-10-01 11:59:33'),
	(17, 1, 1, 2, 1, 'Setup RaceVerse', 82800, '2025-10-01 12:03:20'),
	(18, 1, 1, 2, 2, 'Setup RaceVerse', 82581, '2025-10-01 12:03:20'),
	(19, 1, 1, 2, 3, 'Setup RaceVerse', 82750, '2025-10-01 12:03:20'),
	(20, 1, 1, 2, 4, 'Setup RaceVerse', 82822, '2025-10-01 12:03:20'),
	(21, 1, 1, 2, 5, 'Setup RaceVerse', 82988, '2025-10-01 12:03:20'),
	(22, 1, 1, 2, 6, 'Setup RaceVerse', 82820, '2025-10-01 12:03:20'),
	(23, 1, 1, 2, 7, 'Setup RaceVerse', 82862, '2025-10-01 12:03:20'),
	(24, 1, 1, 2, 8, 'Setup RaceVerse', 82985, '2025-10-01 12:03:20'),
	(25, 1, 1, 3, 1, 'Setup RaceVerse', 108233, '2025-10-01 12:05:27'),
	(26, 1, 1, 3, 2, 'Setup RaceVerse', 108196, '2025-10-01 12:05:27'),
	(27, 1, 1, 3, 3, 'Setup RaceVerse', 108124, '2025-10-01 12:05:27'),
	(28, 1, 1, 3, 4, 'Setup RaceVerse', 108205, '2025-10-01 12:05:27'),
	(29, 1, 1, 3, 5, 'Setup RaceVerse', 108902, '2025-10-01 12:05:27'),
	(30, 1, 1, 3, 6, 'Setup RaceVerse', 108740, '2025-10-01 12:05:27'),
	(31, 1, 1, 3, 7, 'Setup RaceVerse', 108815, '2025-10-01 12:05:27'),
	(32, 1, 1, 3, 8, 'Setup RaceVerse', 109020, '2025-10-01 12:05:27'),
	(33, 1, 1, 11, 1, 'Setup RaceVerse', 99842, '2025-10-01 12:05:42'),
	(34, 1, 1, 11, 2, 'Setup RaceVerse', 99601, '2025-10-01 12:05:42'),
	(35, 1, 1, 11, 3, 'Setup RaceVerse', 99552, '2025-10-01 12:05:42'),
	(36, 1, 1, 11, 4, 'Setup RaceVerse', 99770, '2025-10-01 12:05:42'),
	(37, 1, 1, 11, 5, 'Setup RaceVerse', 100210, '2025-10-01 12:05:42'),
	(38, 1, 1, 11, 6, 'Setup RaceVerse', 99980, '2025-10-01 12:05:42'),
	(39, 1, 1, 11, 7, 'Setup RaceVerse', 100102, '2025-10-01 12:05:42'),
	(40, 1, 1, 11, 8, 'Setup RaceVerse', 100356, '2025-10-01 12:05:42'),
	(41, 1, 1, 10, 1, 'Setup RaceVerse', 104932, '2025-10-01 12:05:47'),
	(42, 1, 1, 10, 2, 'Setup RaceVerse', 104801, '2025-10-01 12:05:47'),
	(43, 1, 1, 10, 3, 'Setup RaceVerse', 104754, '2025-10-01 12:05:47'),
	(44, 1, 1, 10, 4, 'Setup RaceVerse', 105020, '2025-10-01 12:05:47'),
	(45, 1, 1, 10, 5, 'Setup RaceVerse', 105310, '2025-10-01 12:05:47'),
	(46, 1, 1, 10, 6, 'Setup RaceVerse', 104950, '2025-10-01 12:05:47'),
	(47, 1, 1, 10, 7, 'Setup RaceVerse', 105220, '2025-10-01 12:05:47'),
	(48, 1, 1, 10, 8, 'Setup RaceVerse', 105480, '2025-10-01 12:05:47'),
	(49, 1, 1, 6, 1, 'Setup RaceVerse', 87102, '2025-10-01 12:06:01'),
	(50, 1, 1, 6, 2, 'Setup RaceVerse', 86998, '2025-10-01 12:06:01'),
	(51, 1, 1, 6, 3, 'Setup RaceVerse', 86969, '2025-10-01 12:06:01'),
	(52, 1, 1, 6, 4, 'Setup RaceVerse', 87250, '2025-10-01 12:06:01'),
	(53, 1, 1, 6, 5, 'Setup RaceVerse', 87061, '2025-10-01 12:06:01'),
	(54, 1, 1, 6, 6, 'Setup RaceVerse', 87180, '2025-10-01 12:06:01'),
	(55, 1, 1, 6, 7, 'Setup RaceVerse', 87300, '2025-10-01 12:06:01'),
	(56, 1, 1, 6, 8, 'Setup RaceVerse', 87450, '2025-10-01 12:06:01'),
	(57, 1, 1, 8, 1, 'Setup RaceVerse', 100812, '2025-10-01 12:06:17'),
	(58, 1, 1, 8, 2, 'Setup RaceVerse', 100636, '2025-10-01 12:06:17'),
	(59, 1, 1, 8, 3, 'Setup RaceVerse', 100720, '2025-10-01 12:06:17'),
	(60, 1, 1, 8, 4, 'Setup RaceVerse', 100940, '2025-10-01 12:06:17'),
	(61, 1, 1, 8, 5, 'Setup RaceVerse', 100995, '2025-10-01 12:06:17'),
	(62, 1, 1, 8, 6, 'Setup RaceVerse', 100880, '2025-10-01 12:06:17'),
	(63, 1, 1, 8, 7, 'Setup RaceVerse', 100757, '2025-10-01 12:06:17'),
	(64, 1, 1, 8, 8, 'Setup RaceVerse', 101120, '2025-10-01 12:06:17'),
	(65, 1, 1, 9, 1, 'Setup RaceVerse', 91220, '2025-10-01 12:06:29'),
	(66, 1, 1, 9, 2, 'Setup RaceVerse', 91010, '2025-10-01 12:06:30'),
	(67, 1, 1, 9, 3, 'Setup RaceVerse', 91080, '2025-10-01 12:06:30'),
	(68, 1, 1, 9, 4, 'Setup RaceVerse', 91305, '2025-10-01 12:06:30'),
	(69, 1, 1, 9, 5, 'Setup RaceVerse', 91450, '2025-10-01 12:06:30'),
	(70, 1, 1, 9, 6, 'Setup RaceVerse', 91330, '2025-10-01 12:06:30'),
	(71, 1, 1, 9, 7, 'Setup RaceVerse', 91410, '2025-10-01 12:06:30'),
	(72, 1, 1, 9, 8, 'Setup RaceVerse', 91600, '2025-10-01 12:06:30'),
	(73, 1, 1, 12, 1, 'Setup RaceVerse', 113210, '2025-10-01 12:06:48'),
	(74, 1, 1, 12, 2, 'Setup RaceVerse', 112905, '2025-10-01 12:06:48'),
	(75, 1, 1, 12, 3, 'Setup RaceVerse', 113000, '2025-10-01 12:06:48'),
	(76, 1, 1, 12, 4, 'Setup RaceVerse', 113180, '2025-10-01 12:06:48'),
	(77, 1, 1, 12, 5, 'Setup RaceVerse', 113420, '2025-10-01 12:06:48'),
	(78, 1, 1, 12, 6, 'Setup RaceVerse', 113260, '2025-10-01 12:06:48'),
	(79, 1, 1, 12, 7, 'Setup RaceVerse', 112974, '2025-10-01 12:06:48'),
	(80, 1, 1, 12, 8, 'Setup RaceVerse', 113500, '2025-10-01 12:06:48'),
	(81, 1, 1, 13, 1, 'Setup RaceVerse', 116240, '2025-10-01 12:07:02'),
	(82, 1, 1, 13, 2, 'Setup RaceVerse', 116100, '2025-10-01 12:07:02'),
	(83, 1, 1, 13, 3, 'Setup RaceVerse', 116050, '2025-10-01 12:07:02'),
	(84, 1, 1, 13, 4, 'Setup RaceVerse', 116320, '2025-10-01 12:07:02'),
	(85, 1, 1, 13, 5, 'Setup RaceVerse', 116580, '2025-10-01 12:07:02'),
	(86, 1, 1, 13, 6, 'Setup RaceVerse', 116410, '2025-10-01 12:07:02'),
	(87, 1, 1, 13, 7, 'Setup RaceVerse', 116700, '2025-10-01 12:07:02'),
	(88, 1, 1, 13, 8, 'Setup RaceVerse', 116950, '2025-10-01 12:07:02'),
	(89, 1, 3, 1, 10, 'Setup RaceVerse', 109250, '2025-10-01 12:12:17'),
	(90, 1, 3, 1, 11, 'Setup RaceVerse', 109225, '2025-10-01 12:12:17'),
	(91, 1, 3, 1, 12, 'Setup RaceVerse', 109171, '2025-10-01 12:12:17'),
	(92, 1, 3, 1, 13, 'Setup RaceVerse', 109395, '2025-10-01 12:12:17'),
	(93, 1, 3, 1, 14, 'Setup RaceVerse', 108806, '2025-10-01 12:12:17'),
	(94, 1, 3, 1, 15, 'Setup RaceVerse', 109580, '2025-10-01 12:12:17'),
	(95, 1, 3, 1, 16, 'Setup RaceVerse', 109229, '2025-10-01 12:12:17'),
	(96, 1, 3, 1, 17, 'Setup RaceVerse', 108460, '2025-10-01 12:12:17'),
	(97, 1, 3, 1, 18, 'Setup RaceVerse', 108454, '2025-10-01 12:12:17'),
	(98, 1, 3, 5, 18, 'Setup RaceVerse', 137442, '2025-10-01 12:16:37'),
	(99, 1, 3, 5, 17, 'Setup RaceVerse', 137560, '2025-10-01 12:16:37'),
	(100, 1, 3, 5, 13, 'Setup RaceVerse', 137890, '2025-10-01 12:16:37'),
	(101, 1, 3, 5, 12, 'Setup RaceVerse', 137780, '2025-10-01 12:16:37'),
	(102, 1, 3, 5, 15, 'Setup RaceVerse', 138040, '2025-10-01 12:16:37'),
	(103, 1, 3, 5, 11, 'Setup RaceVerse', 138120, '2025-10-01 12:16:37'),
	(104, 1, 3, 5, 10, 'Setup RaceVerse', 138350, '2025-10-01 12:16:37'),
	(105, 1, 3, 5, 16, 'Setup RaceVerse', 138620, '2025-10-01 12:16:37'),
	(106, 1, 3, 5, 14, 'Setup RaceVerse', 138900, '2025-10-01 12:16:37'),
	(107, 1, 3, 11, 18, 'Setup RaceVerse', 120842, '2025-10-01 12:16:50'),
	(108, 1, 3, 11, 17, 'Setup RaceVerse', 120920, '2025-10-01 12:16:50'),
	(109, 1, 3, 11, 13, 'Setup RaceVerse', 121100, '2025-10-01 12:16:50'),
	(110, 1, 3, 11, 12, 'Setup RaceVerse', 121050, '2025-10-01 12:16:50'),
	(111, 1, 3, 11, 15, 'Setup RaceVerse', 121210, '2025-10-01 12:16:50'),
	(112, 1, 3, 11, 11, 'Setup RaceVerse', 121340, '2025-10-01 12:16:50'),
	(113, 1, 3, 11, 10, 'Setup RaceVerse', 121580, '2025-10-01 12:16:50'),
	(114, 1, 3, 11, 16, 'Setup RaceVerse', 121760, '2025-10-01 12:16:50'),
	(115, 1, 3, 11, 14, 'Setup RaceVerse', 121980, '2025-10-01 12:16:50'),
	(116, 1, 3, 10, 18, 'Setup RaceVerse', 119842, '2025-10-01 12:17:03'),
	(117, 1, 3, 10, 17, 'Setup RaceVerse', 120010, '2025-10-01 12:17:03'),
	(118, 1, 3, 10, 13, 'Setup RaceVerse', 120220, '2025-10-01 12:17:03'),
	(119, 1, 3, 10, 12, 'Setup RaceVerse', 120180, '2025-10-01 12:17:03'),
	(120, 1, 3, 10, 15, 'Setup RaceVerse', 120350, '2025-10-01 12:17:03'),
	(121, 1, 3, 10, 11, 'Setup RaceVerse', 120470, '2025-10-01 12:17:03'),
	(122, 1, 3, 10, 10, 'Setup RaceVerse', 120690, '2025-10-01 12:17:03'),
	(123, 1, 3, 10, 16, 'Setup RaceVerse', 120820, '2025-10-01 12:17:03'),
	(124, 1, 3, 10, 14, 'Setup RaceVerse', 121050, '2025-10-01 12:17:03'),
	(125, 1, 3, 6, 18, 'Setup RaceVerse', 93842, '2025-10-01 12:17:18'),
	(126, 1, 3, 6, 17, 'Setup RaceVerse', 93960, '2025-10-01 12:17:18'),
	(127, 1, 3, 6, 13, 'Setup RaceVerse', 94120, '2025-10-01 12:17:18'),
	(128, 1, 3, 6, 12, 'Setup RaceVerse', 94080, '2025-10-01 12:17:18'),
	(129, 1, 3, 6, 15, 'Setup RaceVerse', 94240, '2025-10-01 12:17:18'),
	(130, 1, 3, 6, 11, 'Setup RaceVerse', 94350, '2025-10-01 12:17:18'),
	(131, 1, 3, 6, 10, 'Setup RaceVerse', 94560, '2025-10-01 12:17:18'),
	(132, 1, 3, 6, 16, 'Setup RaceVerse', 94720, '2025-10-01 12:17:18'),
	(133, 1, 3, 6, 14, 'Setup RaceVerse', 94950, '2025-10-01 12:17:18'),
	(134, 1, 3, 7, 18, 'Setup RaceVerse', 99842, '2025-10-01 12:17:30'),
	(135, 1, 3, 7, 17, 'Setup RaceVerse', 99960, '2025-10-01 12:17:30'),
	(136, 1, 3, 7, 13, 'Setup RaceVerse', 100120, '2025-10-01 12:17:30'),
	(137, 1, 3, 7, 12, 'Setup RaceVerse', 100080, '2025-10-01 12:17:30'),
	(138, 1, 3, 7, 15, 'Setup RaceVerse', 100240, '2025-10-01 12:17:30'),
	(139, 1, 3, 7, 11, 'Setup RaceVerse', 100350, '2025-10-01 12:17:30'),
	(140, 1, 3, 7, 10, 'Setup RaceVerse', 100560, '2025-10-01 12:17:30'),
	(141, 1, 3, 7, 16, 'Setup RaceVerse', 100720, '2025-10-01 12:17:30'),
	(142, 1, 3, 7, 14, 'Setup RaceVerse', 100950, '2025-10-01 12:17:30'),
	(143, 1, 3, 2, 18, 'Setup RaceVerse', 93842, '2025-10-01 12:17:57'),
	(144, 1, 3, 2, 17, 'Setup RaceVerse', 93960, '2025-10-01 12:17:57'),
	(145, 1, 3, 2, 13, 'Setup RaceVerse', 94120, '2025-10-01 12:17:57'),
	(146, 1, 3, 2, 12, 'Setup RaceVerse', 94080, '2025-10-01 12:17:57'),
	(147, 1, 3, 2, 15, 'Setup RaceVerse', 94240, '2025-10-01 12:17:57'),
	(148, 1, 3, 2, 11, 'Setup RaceVerse', 94350, '2025-10-01 12:17:57'),
	(149, 1, 3, 2, 10, 'Setup RaceVerse', 94560, '2025-10-01 12:17:57'),
	(150, 1, 3, 2, 16, 'Setup RaceVerse', 94720, '2025-10-01 12:17:57'),
	(151, 1, 3, 2, 14, 'Setup RaceVerse', 94950, '2025-10-01 12:17:57'),
	(152, 1, 3, 3, 18, 'Setup RaceVerse', 116842, '2025-10-01 12:18:10'),
	(153, 1, 3, 3, 17, 'Setup RaceVerse', 116960, '2025-10-01 12:18:10'),
	(154, 1, 3, 3, 13, 'Setup RaceVerse', 117120, '2025-10-01 12:18:10'),
	(155, 1, 3, 3, 12, 'Setup RaceVerse', 117080, '2025-10-01 12:18:10'),
	(156, 1, 3, 3, 15, 'Setup RaceVerse', 117240, '2025-10-01 12:18:10'),
	(157, 1, 3, 3, 11, 'Setup RaceVerse', 117350, '2025-10-01 12:18:10'),
	(158, 1, 3, 3, 10, 'Setup RaceVerse', 117560, '2025-10-01 12:18:10'),
	(159, 1, 3, 3, 16, 'Setup RaceVerse', 117720, '2025-10-01 12:18:10'),
	(160, 1, 3, 3, 14, 'Setup RaceVerse', 117950, '2025-10-01 12:18:10'),
	(161, 1, 3, 4, 18, 'Setup RaceVerse', 238842, '2025-10-01 12:18:23'),
	(162, 1, 3, 4, 17, 'Setup RaceVerse', 239010, '2025-10-01 12:18:23'),
	(163, 1, 3, 4, 13, 'Setup RaceVerse', 239220, '2025-10-01 12:18:23'),
	(164, 1, 3, 4, 12, 'Setup RaceVerse', 239180, '2025-10-01 12:18:23'),
	(165, 1, 3, 4, 15, 'Setup RaceVerse', 239350, '2025-10-01 12:18:23'),
	(166, 1, 3, 4, 11, 'Setup RaceVerse', 239460, '2025-10-01 12:18:23'),
	(167, 1, 3, 4, 10, 'Setup RaceVerse', 239680, '2025-10-01 12:18:23'),
	(168, 1, 3, 4, 16, 'Setup RaceVerse', 239820, '2025-10-01 12:18:23'),
	(169, 1, 3, 4, 14, 'Setup RaceVerse', 240050, '2025-10-01 12:18:23'),
	(170, 1, 3, 8, 18, 'Setup RaceVerse', 115842, '2025-10-01 12:18:41'),
	(171, 1, 3, 8, 17, 'Setup RaceVerse', 115960, '2025-10-01 12:18:41'),
	(172, 1, 3, 8, 13, 'Setup RaceVerse', 116120, '2025-10-01 12:18:41'),
	(173, 1, 3, 8, 12, 'Setup RaceVerse', 116080, '2025-10-01 12:18:41'),
	(174, 1, 3, 8, 15, 'Setup RaceVerse', 116240, '2025-10-01 12:18:41'),
	(175, 1, 3, 8, 11, 'Setup RaceVerse', 116350, '2025-10-01 12:18:41'),
	(176, 1, 3, 8, 10, 'Setup RaceVerse', 116560, '2025-10-01 12:18:41'),
	(177, 1, 3, 8, 16, 'Setup RaceVerse', 116720, '2025-10-01 12:18:41'),
	(178, 1, 3, 8, 14, 'Setup RaceVerse', 116950, '2025-10-01 12:18:41'),
	(179, 1, 3, 9, 18, 'Setup RaceVerse', 101842, '2025-10-01 12:21:49'),
	(180, 1, 3, 9, 17, 'Setup RaceVerse', 101960, '2025-10-01 12:21:49'),
	(181, 1, 3, 9, 13, 'Setup RaceVerse', 102120, '2025-10-01 12:21:49'),
	(182, 1, 3, 9, 12, 'Setup RaceVerse', 102080, '2025-10-01 12:21:49'),
	(183, 1, 3, 9, 15, 'Setup RaceVerse', 102240, '2025-10-01 12:21:49'),
	(184, 1, 3, 9, 11, 'Setup RaceVerse', 102350, '2025-10-01 12:21:49'),
	(185, 1, 3, 9, 10, 'Setup RaceVerse', 102560, '2025-10-01 12:21:49'),
	(186, 1, 3, 9, 16, 'Setup RaceVerse', 102720, '2025-10-01 12:21:49'),
	(187, 1, 3, 9, 14, 'Setup RaceVerse', 102950, '2025-10-01 12:21:49'),
	(188, 1, 3, 12, 18, 'Setup RaceVerse', 126842, '2025-10-01 12:22:02'),
	(189, 1, 3, 12, 17, 'Setup RaceVerse', 126960, '2025-10-01 12:22:02'),
	(190, 1, 3, 12, 13, 'Setup RaceVerse', 127120, '2025-10-01 12:22:02'),
	(191, 1, 3, 12, 12, 'Setup RaceVerse', 127080, '2025-10-01 12:22:02'),
	(192, 1, 3, 12, 15, 'Setup RaceVerse', 127240, '2025-10-01 12:22:02'),
	(193, 1, 3, 12, 11, 'Setup RaceVerse', 127350, '2025-10-01 12:22:02'),
	(194, 1, 3, 12, 10, 'Setup RaceVerse', 127560, '2025-10-01 12:22:02'),
	(195, 1, 3, 12, 16, 'Setup RaceVerse', 127720, '2025-10-01 12:22:02'),
	(196, 1, 3, 12, 14, 'Setup RaceVerse', 127950, '2025-10-01 12:22:02'),
	(197, 1, 3, 13, 18, 'Setup RaceVerse', 118842, '2025-10-01 12:22:43'),
	(198, 1, 3, 13, 17, 'Setup RaceVerse', 118960, '2025-10-01 12:22:43'),
	(199, 1, 3, 13, 13, 'Setup RaceVerse', 119120, '2025-10-01 12:22:43'),
	(200, 1, 3, 13, 12, 'Setup RaceVerse', 119080, '2025-10-01 12:22:43'),
	(201, 1, 3, 13, 15, 'Setup RaceVerse', 119240, '2025-10-01 12:22:43'),
	(202, 1, 3, 13, 11, 'Setup RaceVerse', 119350, '2025-10-01 12:22:43'),
	(203, 1, 3, 13, 10, 'Setup RaceVerse', 119560, '2025-10-01 12:22:43'),
	(204, 1, 3, 13, 16, 'Setup RaceVerse', 119720, '2025-10-01 12:22:43'),
	(205, 1, 3, 13, 14, 'Setup RaceVerse', 119950, '2025-10-01 12:22:43');

-- Dump della struttura di tabella simhub.tracks
CREATE TABLE IF NOT EXISTS `tracks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  CONSTRAINT `tracks_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dump dei dati della tabella simhub.tracks: ~13 rows (circa)
INSERT INTO `tracks` (`id`, `game_id`, `name`, `image_path`) VALUES
	(1, 1, 'Autodromo Nazionale di Monza', NULL),
	(2, 1, 'Autódromo José Carlos Pace – Interlagos', NULL),
	(3, 1, 'Bahrain International Circuit', NULL),
	(4, 1, 'Circuit de la Sarthe – Le Mans', NULL),
	(5, 1, 'Circuit de Spa-Francorchamps', NULL),
	(6, 1, 'Fuji Speedway', NULL),
	(7, 1, 'Imola – Autodromo Enzo e Dino Ferrari', NULL),
	(8, 1, 'Losail International Circuit – Qatar', NULL),
	(9, 1, 'Portimão – Algarve International Circuit', NULL),
	(10, 1, 'Sebring International Raceway', NULL),
	(11, 1, 'Silverstone Circuit', NULL),
	(12, 1, 'Circuit of the Americas – Austin', NULL),
	(13, 1, 'Motorland Aragón', NULL);

-- Dump della struttura di tabella simhub.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `email_verified_at` datetime DEFAULT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `subscription_plan` varchar(64) DEFAULT NULL,
  `subscription_active` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dump dei dati della tabella simhub.users: ~2 rows (circa)
INSERT INTO `users` (`id`, `email`, `password_hash`, `first_name`, `last_name`, `verification_token`, `email_verified_at`, `role`, `subscription_plan`, `subscription_active`, `created_at`) VALUES
    (1, 'admin@example.com', '$2y$10$wH5iC7R0iHq1w1e9VvbDWO9sV.8Xv1VdOZC2kQd7t0OQv3RrQqU9K', 'User', 'Raceverse', NULL, NULL, 'admin', 'RaceVerse PRO', 1, '2025-10-01 08:26:46'),
    (2, 'michelevassallo1999@gmail.com', '$2y$10$vRvRTWq7hEOegFPQlQlWCOiLOWb8e6haYRkQqBJDnJFQuDYBnDxHG', 'Michele', 'Vassallo', '38bcca10daaa0a9c2a70b95ffadaa4b09ab961cf6d0ffdca2bbaf04bd7d14614', '2025-10-01 12:55:24', 'user', 'RaceVerse BASIC', 0, '2025-10-01 10:53:30');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
