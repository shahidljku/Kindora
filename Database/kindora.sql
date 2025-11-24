-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 22, 2025 at 12:12 PM
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
-- Database: `kindora`
--
CREATE DATABASE IF NOT EXISTS `kindora` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `kindora`;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `travel_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `guests` int(11) DEFAULT 1,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `payment_status` enum('pending','paid','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` varchar(50) DEFAULT NULL,
  `special_requests` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `destination_id`, `booking_date`, `travel_date`, `return_date`, `guests`, `total_amount`, `status`, `payment_status`, `created_at`, `updated_at`, `payment_method`, `special_requests`) VALUES
(1, 4, 159, '2024-12-01', '2024-12-15', '2024-12-22', 2, 2199.00, 'confirmed', 'paid', '2025-10-12 14:48:06', '2025-10-12 14:48:06', NULL, NULL),
(2, 5, 17, '2024-12-02', '2024-12-20', '2024-12-28', 1, 1899.00, 'pending', 'pending', '2025-10-12 14:48:06', '2025-10-12 14:48:06', NULL, NULL),
(3, 6, 30, '2024-11-28', '2024-12-10', '2024-12-17', 4, 4598.00, 'confirmed', 'paid', '2025-10-12 14:48:06', '2025-10-12 14:48:06', NULL, NULL),
(4, 7, 217, '2024-12-03', '2024-12-25', '2025-01-02', 2, 3299.00, 'confirmed', 'paid', '2025-10-12 14:48:06', '2025-10-12 14:48:06', NULL, NULL),
(5, 8, 160, '2024-12-01', '2024-12-18', '2024-12-23', 3, 2847.00, 'completed', 'pending', '2025-10-12 14:48:06', '2025-11-20 15:55:35', NULL, NULL),
(9, 20, 24, '2025-11-14', '2025-11-14', NULL, 3, 1798.00, 'pending', 'pending', '2025-11-14 11:42:46', '2025-11-14 11:42:46', 'upi', ''),
(10, 20, 233, '2025-11-14', '2025-11-14', NULL, 1, 1599.00, 'pending', 'pending', '2025-11-14 11:48:42', '2025-11-14 11:48:42', 'upi', ''),
(11, 20, 236, '2025-11-14', '2025-11-14', NULL, 1, 1399.00, 'pending', 'pending', '2025-11-14 11:51:38', '2025-11-14 11:51:38', 'upi', ''),
(12, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'pending', 'pending', '2025-11-14 11:53:40', '2025-11-14 11:53:40', 'upi', ''),
(13, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'pending', 'pending', '2025-11-14 12:09:44', '2025-11-14 12:09:44', 'upi', ''),
(14, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'pending', 'pending', '2025-11-14 12:13:41', '2025-11-14 12:13:41', 'upi', ''),
(15, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'pending', 'pending', '2025-11-14 12:26:27', '2025-11-14 12:26:27', 'card', ''),
(16, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'confirmed', '', '2025-11-14 12:29:47', '2025-11-14 12:30:40', 'upi', ''),
(17, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'pending', 'pending', '2025-11-14 15:56:34', '2025-11-14 15:56:34', 'upi', ''),
(18, 20, 236, '2025-11-14', '2025-11-14', NULL, 1, 1399.00, 'pending', 'pending', '2025-11-14 15:57:36', '2025-11-14 15:57:36', 'upi', ''),
(19, 20, 236, '2025-11-14', '2025-11-14', NULL, 1, 1399.00, 'pending', 'pending', '2025-11-14 15:58:36', '2025-11-14 15:58:36', 'upi', ''),
(20, 20, 236, '2025-11-14', '2025-11-14', NULL, 1, 1399.00, 'pending', 'pending', '2025-11-14 16:03:50', '2025-11-14 16:03:50', 'upi', ''),
(21, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'pending', 'pending', '2025-11-14 16:05:02', '2025-11-14 16:05:02', 'upi', ''),
(22, 20, 236, '2025-11-14', '2025-11-27', '2025-11-30', 4, 1399.00, 'pending', 'pending', '2025-11-14 16:14:34', '2025-11-21 04:42:25', 'upi', ''),
(23, 20, 236, '2025-11-14', '2025-11-27', NULL, 1, 1399.00, 'pending', 'pending', '2025-11-14 16:19:28', '2025-11-14 16:19:28', 'upi', ''),
(24, 20, 42, '2025-11-14', '2025-11-14', NULL, 1, 2399.00, 'pending', 'pending', '2025-11-14 16:23:58', '2025-11-14 16:23:58', 'card', ''),
(25, 20, 208, '2025-11-16', '2025-11-16', NULL, 1, 2399.00, 'confirmed', '', '2025-11-16 15:23:44', '2025-11-16 15:23:54', 'upi', ''),
(26, 20, 236, '2025-11-16', '2025-11-16', NULL, 3, 2798.00, 'pending', 'pending', '2025-11-16 15:32:04', '2025-11-16 15:32:04', 'upi', ''),
(27, 20, 236, '2025-11-16', '2025-11-29', NULL, 1, 1399.00, 'cancelled', '', '2025-11-16 15:46:41', '2025-11-20 14:12:05', 'upi', ''),
(28, 20, 200, '2025-11-17', '2025-11-17', NULL, 1, 1999.00, 'confirmed', '', '2025-11-17 05:26:35', '2025-11-17 05:26:47', 'wallet', ''),
(29, 20, 208, '2025-11-17', '2025-11-17', NULL, 1, 2399.00, 'confirmed', '', '2025-11-17 06:42:07', '2025-11-17 06:42:13', 'upi', ''),
(30, 20, 18, '2025-11-17', '2025-11-17', NULL, 1, 799.00, 'confirmed', '', '2025-11-17 13:03:53', '2025-11-17 13:04:03', 'upi', ''),
(31, 20, 241, '2025-11-17', '2025-11-17', NULL, 3, 3198.00, 'confirmed', 'pending', '2025-11-17 14:04:07', '2025-11-17 16:54:12', 'upi', ''),
(32, 11, 297, '2025-11-17', '2025-11-17', NULL, 1, 500.00, 'confirmed', '', '2025-11-17 17:24:36', '2025-11-17 17:24:46', 'upi', ''),
(33, 20, 170, '2025-11-20', '2025-11-25', NULL, 7, 18805.05, 'completed', '', '2025-11-20 05:22:38', '2025-11-20 14:13:46', 'upi', ''),
(34, 20, 10, '2025-11-20', '2025-11-20', NULL, 1, 12000.00, 'completed', 'pending', '2025-11-20 13:21:33', '2025-11-20 14:13:35', 'upi', ''),
(35, 20, 10, '2025-11-20', '2025-12-26', NULL, 1, 12000.00, 'completed', '', '2025-11-20 14:50:43', '2025-11-20 14:51:26', 'upi', ''),
(36, 20, 264, '2025-11-21', '2025-11-21', NULL, 1, 2299.00, 'cancelled', '', '2025-11-21 04:37:39', '2025-11-21 04:38:06', 'upi', '');

-- --------------------------------------------------------

--
-- Table structure for table `deals`
--

CREATE TABLE `deals` (
  `deal_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `discount` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `destination_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deals`
--

INSERT INTO `deals` (`deal_id`, `title`, `description`, `image_url`, `original_price`, `sale_price`, `discount`, `start_date`, `end_date`, `destination_id`, `is_active`, `created_at`) VALUES
(1, 'Romantic Europe Escape', 'Explore Paris, Venice & Santorini for couples', 'assets/images/deals/europe_romantic.avif', 1999.00, 1399.00, 30, '2025-11-01', '2025-12-31', 1, 1, '2025-11-10 13:22:31'),
(2, 'Asian Adventure Trail', 'Thailand, Japan & Bali – culture, food & adventure', 'assets/images/deals/asia_adventure.avif', 1599.00, 1199.00, 25, '2025-11-01', '2025-12-31', 2, 1, '2025-11-10 13:22:31'),
(3, 'African Safari Adventure', 'Safari in Kenya & South Africa – wildlife & nature', 'assets/images/deals/africa_safari.avif', 2299.00, 1839.00, 20, '2025-11-01', '2025-12-31', 3, 1, '2025-11-10 13:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE `destinations` (
  `destination_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  `featured` tinyint(1) DEFAULT 0,
  `difficulty_level` enum('easy','moderate','challenging','extreme') DEFAULT 'moderate',
  `best_season` varchar(100) DEFAULT NULL,
  `suggested_duration_days` int(11) DEFAULT 5
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `destinations`
--

INSERT INTO `destinations` (`destination_id`, `name`, `type`, `description`, `image_url`, `video_url`, `created_by`, `updated_at`, `is_active`, `featured`, `difficulty_level`, `best_season`, `suggested_duration_days`) VALUES
(1, 'Asia', 'Continent', 'The largest and most populous continent, offering diverse cultures, ancient history, and stunning natural landscapes.', '/Kindora/assets/images/banner/asia.avif', '/Kindora/assets/videos/asia-intro.webm', NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(2, 'Europe', 'Continent', 'A continent known for its rich history, diverse cultures, iconic landmarks, and vibrant cities.', '/Kindora/assets/images/banner/europe.avif', '/Kindora/assets/videos/europe_intro.webm', NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(3, 'Africa', 'Continent', 'The second-largest continent, famous for its wildlife, vast deserts, ancient civilizations, and diverse ecosystems.', '/Kindora/assets/images/banner/africa.avif', '/Kindora/assets/videos/africa_intro.webm', NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(4, 'North America', 'Continent', 'A continent with a wide range of climates and geographies, from the Arctic to tropical regions, and home to diverse cultures.', '/Kindora/assets/images/banner/north_america.avif', '/Kindora/assets/videos/north_america_intro.webm', NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(5, 'South America', 'Continent', 'A continent renowned for its Amazon rainforest, Andes mountains, vibrant carnivals, and ancient ruins.', '/Kindora/assets/images/banner/south_america.avif', '/Kindora/assets/videos/south_america_intro.webm', NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(6, 'Australia', 'Continent', 'A continent and country known for its unique wildlife, vast outback, and stunning coastal areas.', '/Kindora/assets/images/banner/australia.avif', '/Kindora/assets/videos/australia_intro.webm', NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(7, 'Antarctica', 'Continent', 'The Earth\'s southernmost continent, almost entirely covered by ice, known for its extreme cold and unique wildlife.', '/Kindora/assets/images/banner/antarctica.avif', '/Kindora/assets/videos/antarctica_intro.webm', NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 7),
(9, 'Taj Mahal', '7 wonders', 'The Taj Mahal, located in Agra, India, is one of the world’s most iconic monuments and a UNESCO World Heritage Site.', '/Kindora/assets/images/7wonders/Taj-Mahal.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(10, 'Petra', '7 wonders', 'Petra is an ancient archaeological city in southern Jordan, famous for its rock-cut architecture and water conduit system. Known as the \"Rose City\" due to the color of the stone, Petra is one of the most iconic archaeological sites in the world.', '/Kindora/assets/images/7wonders/petra.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(11, 'Machu Picchu', '7 wonders', 'Machu Picchu is a 15th-century Incan citadel located in the Andes Mountains of Peru. It is one of the most famous archaeological sites in the world and a UNESCO World Heritage Site.', '/Kindora/assets/images/7wonders/machu-picchu.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(12, 'Great Wall of China', '7 wonders', 'The Great Wall of China is a series of fortifications made of stone, brick, tamped earth, wood, and other materials. Built along the northern borders of China, it was designed to protect Chinese states and empires against invasions and raids.', '/Kindora/assets/images/7wonders/Great Wall of China.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(13, 'Colosseum', '7 wonders', 'The Colosseum, also known as the Flavian Amphitheatre, is a massive stone arena in the center of Rome. It is one of the most iconic symbols of Imperial Rome and a major tourist attraction in Italy.', '/Kindora/assets/images/7wonders/colosseum.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(14, 'Christ the Redeemer', '7 wonders', 'Christ the Redeemer is an iconic Art Deco statue of Jesus Christ located in Rio de Janeiro, Brazil. It stands atop the Corcovado mountain and is one of the most recognized religious monuments in the world.', '/Kindora/assets/images/7wonders/christ-redeemer.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(15, 'Chichen Itza', '7 wonders', 'Chichén Itzá is a large pre-Columbian archaeological site built by the Maya civilization. It is one of the most visited tourist destinations in Mexico and a UNESCO World Heritage Site.', '/Kindora/assets/images/7wonders/Chichen Itza (Mexico).avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(16, 'Tian Shan, Kyrgyzstan', 'asia', 'Experience breathtaking mountain ranges with alpine lakes and nomadic culture.', '/Kindora/assets/images/destinations/asia/02 - tian-shan-kyrgyzstan.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'challenging', 'Summer', 7),
(17, 'Japan', 'asia', 'Tokyo (Shibuya, Senso-ji), Kyoto (Fushimi Inari, Arashiyama), Mount Fuji.', '/Kindora/assets/images/destinations/asia/03 - japan.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(18, 'Altai Mountains', 'asia', 'Discover rugged landscapes, wild rivers, and untouched beauty across Central Asia.', '/Kindora/assets/images/destinations/asia/04 - altai-mountains.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'challenging', 'Summer', 7),
(19, 'China', 'asia', 'Great Wall, Forbidden City, The Bund.', '/Kindora/assets/images/destinations/asia/05 - china.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(20, 'Myanmar & Sri Lanka', 'asia', 'From Bagan\'s plains to Sri Lanka\'s tea country and coastlines.', '/Kindora/assets/images/destinations/asia/06 - myanmar - sri-lanka.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(21, 'Indonesia & Philippines', 'asia', 'Island-hop through turquoise waters, beaches, and coral reefs.', '/Kindora/assets/images/destinations/asia/07 - indonesia - philippines.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(22, 'South Korea', 'asia', 'Seoul\'s palaces, Busan\'s beaches, and volcanic Jeju.', '/Kindora/assets/images/destinations/asia/08 - south-korea.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(23, 'Mongolia', 'asia', 'Vast steppes, Gobi Desert adventures, and nomadic culture.', '/Kindora/assets/images/destinations/asia/09 - mongolia.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(24, 'India', 'asia', 'From iconic monuments to vibrant cities, deserts, and beaches.', '/Kindora/assets/images/destinations/asia/10 - india - nepal.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(25, 'Nepal', 'asia', 'Himalayan panoramas, trekking trails, and mountain culture.', '/Kindora/assets/images/destinations/asia/nepal.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'challenging', 'Summer', 7),
(26, 'Kyrgyzstan', 'asia', 'Issyk-Kul shores, alpine meadows, and high passes.', '/Kindora/assets/images/destinations/asia/11 - kyrgyzstan.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'challenging', 'Summer', 7),
(27, 'Palawan, Philippines', 'asia', 'Karst lagoons, secret beaches, and emerald waters.', '/Kindora/assets/images/destinations/asia/12 - palawan-philippines.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(28, 'Sri Lanka', 'asia', 'Sigiriya, golden coasts, tea hills, and wildlife.', '/Kindora/assets/images/destinations/asia/13 - sri-lanka.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(29, 'Vietnam', 'asia', 'Hạ Long Bay sails, lantern-lit Hoi An, and Saigon vibes.', '/Kindora/assets/images/destinations/asia/14 - vietnam.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(30, 'Bali & Raja Ampat', 'asia', 'Wave-filled shores and biodiverse reefs.', '/Kindora/assets/images/destinations/asia/15 - bali - raja-ampat.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(31, 'Hong Kong', 'asia', 'Skyline vistas, harbor ferries, and hillside trails.', '/Kindora/assets/images/destinations/asia/17 - hong-kong.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(32, 'Shanghai & Guangzhou', 'asia', 'Neon skylines, riverfronts, and culinary scenes.', '/Kindora/assets/images/destinations/asia/18 - shanghai - guangzhou.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(33, 'Singapore', 'asia', 'Gardens by the Bay, Marina Bay Sands, and Sentosa.', '/Kindora/assets/images/destinations/asia/19 - singapore.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(34, 'Taiwan', 'asia', 'Taipei 101, Taroko Gorge, and mountain towns.', '/Kindora/assets/images/destinations/asia/20 - taiwan.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(35, 'Indonesia', 'asia', 'Bali temples, Java heritage, and Komodo islands.', '/Kindora/assets/images/destinations/asia/21 - china.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(36, 'Vietnam & Laos', 'asia', 'Mekong journeys, karst valleys, and quiet towns.', '/Kindora/assets/images/destinations/asia/25 - vietnam - laos.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(37, 'Ishigaki Island, Japan', 'asia', 'Coral reefs, clear seas, and island charm.', '/Kindora/assets/images/destinations/asia/26 - ishigaki-island-japan.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(38, 'Yardang, China', 'asia', 'Wind-sculpted rock forms in stark deserts.', '/Kindora/assets/images/destinations/asia/30 - yardang-china.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(39, 'Thailand', 'asia', 'Bangkok canals, northern hills, and island bays.', '/Kindora/assets/images/destinations/asia/32 - thailand.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(40, 'Cambodia & Myanmar', 'asia', 'From Angkor’s temples to lake villages and plains.', '/Kindora/assets/images/destinations/asia/33 - cambodia - myanmar.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(41, 'Shanghai', 'asia', 'Riverside walks, skyline lights, and historic lanes.', '/Kindora/assets/images/destinations/asia/34 - shanghai.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(42, 'Across Asia', 'asia', 'A sweeping montage across cities, coasts, and peaks.', '/Kindora/assets/images/destinations/asia/35 - across-asia.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'moderate', 'All Year', 10),
(43, 'My Trip Planner', 'asia', 'Review your selected destinations and plan your perfect Asian adventure.', '/Kindora/assets/images/destinations/asia/trip-planner.avif', NULL, NULL, '2025-11-17 15:05:21', 0, 0, 'moderate', 'All Year', 5),
(210, 'Tauranga', 'australia', 'Bay of Plenty city with beaches and Mt Maunganui.', '/Kindora/assets/images/destinations/australia/tauranga.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(209, 'Tasmania', 'australia', 'Wild wilderness, national parks and historic sites.', '/Kindora/assets/images/destinations/australia/tasmania.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(208, 'Sydney', 'australia', 'Iconic Opera House, Harbour Bridge and beaches.', '/Kindora/assets/images/destinations/australia/sydney.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(206, 'Rotorua', 'australia', 'Geothermal springs and Maori culture.', '/Kindora/assets/images/destinations/australia/rotorua.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(207, 'Samoa', 'australia', 'Polynesian islands with culture and beaches.', '/Kindora/assets/images/destinations/australia/samao.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(205, 'Queenstown', 'australia', 'Adventure capital of New Zealand and lake views.', '/Kindora/assets/images/destinations/australia/queenstown.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(204, 'Perth', 'australia', 'Western Australia capital, Swan River and beaches.', '/Kindora/assets/images/destinations/australia/perth.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(203, 'Newcastle', 'australia', 'Coastal city with beaches and arts festivals.', '/Kindora/assets/images/destinations/australia/newcastle.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(202, 'New Caledonia', 'australia', 'French Pacific islands with lagoon UNESCO site.', '/Kindora/assets/images/destinations/australia/new_caledonia.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(201, 'Melbourne', 'australia', 'Cultural capital, laneways, and sporting events.', '/Kindora/assets/images/destinations/australia/mebourne.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(200, 'Hobart', 'australia', 'Tasmania’s capital, historic waterfront and MONA museum.', '/Kindora/assets/images/destinations/australia/hobart.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(199, 'Hamilton', 'australia', 'New Zealand city on the Waikato River and Hamilton Gardens.', '/Kindora/assets/images/destinations/australia/hamilton.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(198, 'Great Barrier Reef', 'australia', 'The world’s largest coral reef system.', '/Kindora/assets/images/destinations/australia/great_barrier_reef.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(196, 'Gold Coast', 'australia', 'Surf beaches, theme parks, and nightlife.', '/Kindora/assets/images/destinations/australia/gold coast.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(197, 'Gold Coast Hinterland', 'australia', 'Rainforest, waterfalls, and mountain views.', '/Kindora/assets/images/destinations/australia/gold_cost_hinderland.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(195, 'Fiji', 'australia', 'South Pacific archipelago with beaches and coral reefs.', '/Kindora/assets/images/destinations/australia/fiji.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(194, 'Dunedin', 'australia', 'Scottish heritage and Otago Peninsula wildlife.', '/Kindora/assets/images/destinations/australia/dunedin.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(193, 'Christchurch', 'australia', 'New Zealand’s garden city with Avon River punting.', '/Kindora/assets/images/destinations/australia/christchurch.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(192, 'Canberra', 'australia', 'Australia’s capital, national institutions and parks.', '/Kindora/assets/images/destinations/australia/canbera.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(191, 'Cairns', 'australia', 'Gateway to the Great Barrier Reef and rainforest.', '/Kindora/assets/images/destinations/australia/cairns.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(64, 'Antarctic Peninsula', 'antarctica', 'Most accessible region, penguin colonies, dramatic landscapes, and gateway to Antarctica.', '/Kindora/assets/images/destinations/antarctica/1.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(65, 'South Shetland Islands', 'antarctica', 'Warmer climate, abundant wildlife, volcanic activity, and first Antarctic stop.', '/Kindora/assets/images/destinations/antarctica/2.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(66, 'Deception Island', 'antarctica', 'Active volcano, horseshoe-shaped caldera, geothermal beaches, and unique landscape.', '/Kindora/assets/images/destinations/antarctica/3.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(67, 'Port Lockroy', 'antarctica', 'Historic British base, museum and post office, gentoo penguin colonies, and Antarctic heritage.', '/Kindora/assets/images/destinations/antarctica/4.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(68, 'Lemaire Channel', 'antarctica', 'Kodak Gap, dramatic cliffs, iceberg-flanked passage, and breathtaking scenery.', '/Kindora/assets/images/destinations/antarctica/5.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(69, 'Paradise Harbor', 'antarctica', 'Glacial calving, blue icebergs, wildlife on ice floes, and pristine Antarctic bay.', '/Kindora/assets/images/destinations/antarctica/6.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(70, 'Drake Passage', 'antarctica', 'Roughest sea crossing, convergence of oceans, albatross sightings, and Antarctic gateway.', '/Kindora/assets/images/destinations/antarctica/7.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'challenging', 'Summer', 12),
(71, 'South Georgia Island', 'antarctica', '“Galapagos of the Antarctic,” king penguins, elephant seals, and biodiversity hotspot.', '/Kindora/assets/images/destinations/antarctica/8.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(72, 'McMurdo Station', 'antarctica', 'Largest Antarctic community, research hub, scientific facilities, and logistics center.', '/Kindora/assets/images/destinations/antarctica/9.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(73, 'Ross Sea', 'antarctica', 'Pristine marine ecosystem, emperor penguins, Ross Ice Shelf, and remote wilderness.', '/Kindora/assets/images/destinations/antarctica/10.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(74, 'South Pole', 'antarctica', 'Geographic South Pole, Amundsen-Scott Station, ceremonial pole, and ultimate destination.', '/Kindora/assets/images/destinations/antarctica/11.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(75, 'King George Island', 'antarctica', 'Multiple research stations, international presence, chinstrap penguins, and scientific cooperation.', '/Kindora/assets/images/destinations/antarctica/12.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(76, 'Weddell Sea', 'antarctica', 'Emperor penguin colonies, tabular icebergs, pristine ice conditions, and Shackleton history.', '/Kindora/assets/images/destinations/antarctica/13.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(77, 'Antarctic Circle', 'antarctica', '66°33\'S latitude, midnight sun, exclusive crossing experience, and polar achievement.', '/Kindora/assets/images/destinations/antarctica/14.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(78, 'Elephant Island', 'antarctica', 'Shackleton’s survival story, chinstrap penguins, dramatic cliffs, and exploration history.', '/Kindora/assets/images/destinations/antarctica/15.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(79, 'Palmer Station', 'antarctica', 'US research station, marine research, Adelie penguins, and Antarctic Peninsula location.', '/Kindora/assets/images/destinations/antarctica/16.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'extreme', 'Summer', 12),
(80, 'Livingston Island', 'antarctica', 'Research stations, fur seals, glacial landscapes, and South Shetlands location.', '/Kindora/assets/images/destinations/antarctica/17.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(81, 'Paulet Island', 'antarctica', 'Adelie penguin colony, volcanic origin, stone hut ruins, and historic expeditions.', '/Kindora/assets/images/destinations/antarctica/18.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(82, 'Cuverville Island', 'antarctica', 'Largest gentoo penguin colony, scenic mountains, pristine snow fields, and wildlife viewing.', '/Kindora/assets/images/destinations/antarctica/19.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'easy', 'All Year', 12),
(240, 'Durban, South Africa', 'africa', 'Golden beaches, Indian culture, curry cuisine, and uShaka Marine World.', '/Kindora/assets/images/destinations/africa/26.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(239, 'Lagos, Nigeria', 'africa', 'Nollywood film industry, bustling markets, Lagos Island, and energetic nightlife.', '/Kindora/assets/images/destinations/africa/25.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(238, 'Dakar, Senegal', 'africa', 'Gore Island history, vibrant music scene, African Renaissance Monument, and coastal beauty.', '/Kindora/assets/images/destinations/africa/24.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(237, 'Tunis, Tunisia', 'africa', 'Ancient Carthage ruins, medina markets, Sidi Bou Said, and Mediterranean culture.', '/Kindora/assets/images/destinations/africa/23.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(236, 'Accra, Ghana', 'africa', 'Vibrant markets, colonial forts, Highlife music, and Gateway to West Africa.', '/Kindora/assets/images/destinations/africa/22.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(235, 'Kigali, Rwanda', 'africa', 'Genocide memorials, mountain gorilla treks, clean cities, and remarkable recovery story.', '/Kindora/assets/images/destinations/africa/21.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(234, 'Essaouira, Morocco', 'africa', 'Coastal fortress city, Atlantic winds, surfing, and Portuguese-influenced architecture.', '/Kindora/assets/images/destinations/africa/20.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(233, 'Addis Ababa, Ethiopia', 'africa', 'Ethiopian coffee culture, ancient churches, National Museum, and highland culture.', '/Kindora/assets/images/destinations/africa/19.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(232, 'Okavango Delta, Botswana', 'africa', 'Mokoro canoe safaris, pristine wetlands, abundant wildlife, and luxury camps.', '/Kindora/assets/images/destinations/africa/18.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(231, 'Sossusvlei, Namibia', 'africa', 'Red sand dunes, dead tree landscapes, desert photography, and stunning sunrises.', '/Kindora/assets/images/destinations/africa/17.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(230, 'Ngorongoro Crater, Tanzania', 'africa', 'World\'s largest volcanic caldera, diverse wildlife, Masai culture, and crater floor safaris.', '/Kindora/assets/images/destinations/africa/16.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(229, 'Mount Kilimanjaro, Tanzania', 'africa', 'Africa\'s highest peak, challenging climbs, diverse ecosystems, and stunning views.', '/Kindora/assets/images/destinations/africa/15.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'challenging', 'Summer', 7),
(228, 'Johannesburg, South Africa', 'africa', 'Apartheid Museum, Soweto tours, gold mine history, and vibrant urban culture.', '/Kindora/assets/images/destinations/africa/14.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(227, 'Sahara Desert, Morocco', 'africa', 'Camel trekking, desert camps, sand dunes, and stargazing under clear skies.', '/Kindora/assets/images/destinations/africa/13.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(226, 'Masai Mara, Kenya', 'africa', 'Great Migration, Masai culture, balloon safaris, and abundant wildlife.', '/Kindora/assets/images/destinations/africa/12.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'moderate', 'Winter', 6),
(225, 'Fez, Morocco', 'africa', 'Ancient medina, traditional crafts, Al Quaraouiyine University, and authentic culture.', '/Kindora/assets/images/destinations/africa/11.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(224, 'Luxor, Egypt', 'africa', 'Valley of the Kings, Karnak Temple, ancient tombs, and hot air balloon rides.', '/Kindora/assets/images/destinations/africa/10.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(223, 'Nairobi, Kenya', 'africa', 'Wildlife orphanages, urban national park, vibrant culture, and gateway to safaris.', '/Kindora/assets/images/destinations/africa/9.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(222, 'Casablanca, Morocco', 'africa', 'Hassan II Mosque, art deco architecture, Atlantic coastline, and modern Moroccan culture.', '/Kindora/assets/images/destinations/africa/8.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(221, 'Kruger National Park, South Africa', 'africa', 'Big Five safaris, diverse ecosystems, self-drive adventures, and wildlife photography.', '/Kindora/assets/images/destinations/africa/7.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'Winter', 5),
(220, 'Zanzibar, Tanzania', 'africa', 'Pristine beaches, Stone Town heritage, spice plantations, and turquoise waters.', '/Kindora/assets/images/destinations/africa/6.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(219, 'Victoria Falls, Zambia/Zimbabwe', 'africa', 'Mighty waterfalls, bungee jumping, white-water rafting, and helicopter flights.', '/Kindora/assets/images/destinations/africa/5.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(291, 'South America Trip Planner', 'southamerica', 'Review and plan your perfect South American itinerary.', '/Kindora/assets/images/destinations/southamerica/trip-planner.avif', NULL, NULL, '2025-11-17 14:47:36', 0, 0, 'moderate', 'All Year', 5),
(290, 'San Pedro de Atacama', 'southamerica', 'Village in northern Chile famous for salt flats and moon valley.', '/Kindora/assets/images/destinations/southamerica/27.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(289, 'Manaus', 'southamerica', 'City in the Amazon known for the Opera House and rainforest tourism.', '/Kindora/assets/images/destinations/southamerica/26.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(288, 'Cuzco', 'southamerica', 'Former capital of the Inca Empire, archaeological sites, and colonial architecture.', '/Kindora/assets/images/destinations/southamerica/25.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(287, 'Amazon River', 'southamerica', 'World’s largest river by volume, flowing through Peru and Brazil.', '/Kindora/assets/images/destinations/southamerica/24.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(286, 'Punta Arenas', 'southamerica', 'Southernmost city in Chile, gateway to Antarctica expeditions.', '/Kindora/assets/images/destinations/southamerica/23.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(285, 'Uyuni Train Cemetery', 'southamerica', 'Abandoned trains and desert landscapes near Salar de Uyuni.', '/Kindora/assets/images/destinations/southamerica/22.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(284, 'Valparaiso', 'southamerica', 'Colorful Chilean port city, UNESCO World Heritage Site.', '/Kindora/assets/images/destinations/southamerica/21.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(283, 'Brasilia', 'southamerica', 'Brazil’s planned capital city, known for modernist architecture.', '/Kindora/assets/images/destinations/southamerica/20.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(282, 'Medellin', 'southamerica', 'City in Colombia known for its transformation, culture, and festivals.', '/Kindora/assets/images/destinations/southamerica/19.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(281, 'Fernando de Noronha', 'southamerica', 'Brazilian archipelago known for marine life and pristine beaches.', '/Kindora/assets/images/destinations/southamerica/18.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(280, 'Colca Canyon', 'southamerica', 'One of the world’s deepest canyons, home to Andean condors.', '/Kindora/assets/images/destinations/southamerica/17.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(279, 'Bariloche', 'southamerica', 'Resort town with Swiss alpine architecture, lakes, and skiing in Argentina.', '/Kindora/assets/images/destinations/southamerica/16.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(278, 'Quito', 'southamerica', 'Capital of Ecuador, known for its well-preserved historic center.', '/Kindora/assets/images/destinations/southamerica/15.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(277, 'Cartagena', 'southamerica', 'Historic Caribbean port city with colonial architecture and vibrant street life.', '/Kindora/assets/images/destinations/southamerica/14.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(276, 'Pantanal', 'southamerica', 'World’s largest tropical wetland area, famous for jaguars and birdwatching.', '/Kindora/assets/images/destinations/southamerica/13.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(275, 'Easter Island', 'southamerica', 'Remote island known for its mysterious moai statues and Polynesian culture.', '/Kindora/assets/images/destinations/southamerica/12.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(274, 'Lake Titicaca', 'southamerica', 'Highest navigable lake in the world, shared by Peru and Bolivia.', '/Kindora/assets/images/destinations/southamerica/11.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(273, 'Angel Falls', 'southamerica', 'World’s highest uninterrupted waterfall, located in Venezuela’s Canaima National Park.', '/Kindora/assets/images/destinations/southamerica/10.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(272, 'Patagonia', 'southamerica', 'Remote region spanning Chile and Argentina, famed for glaciers, mountains, and wildlife.', '/Kindora/assets/images/destinations/southamerica/9.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(271, 'Atacama Desert', 'southamerica', 'World’s driest non-polar desert with unique landscapes and salt lakes.', '/Kindora/assets/images/destinations/southamerica/8.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(270, 'Buenos Aires', 'southamerica', 'Capital of Argentina, known for tango, historic architecture, and lively culture.', '/Kindora/assets/images/destinations/southamerica/7.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(269, 'Rio de Janeiro', 'southamerica', 'Famous for its Christ the Redeemer statue, beaches, and vibrant carnival.', '/Kindora/assets/images/destinations/southamerica/6.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(268, 'Salar de Uyuni', 'southamerica', 'World’s largest salt flat in Bolivia, with stunning white landscapes and reflections.', '/Kindora/assets/images/destinations/southamerica/5.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(263, 'North America Trip Planner', 'northamerica', 'Review and plan your perfect North American itinerary.', '/Kindora/assets/images/destinations/northamerica/trip-planner.avif', NULL, NULL, '2025-11-17 14:44:19', 0, 0, 'moderate', 'All Year', 5),
(262, 'Montreal, Canada', 'northamerica', 'French heritage, festivals, art, and culinary experiences.', '/Kindora/assets/images/destinations/northamerica/20.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(261, 'Sedona, Arizona', 'northamerica', 'Red rock formations, spiritual retreats, and hiking.', '/Kindora/assets/images/destinations/northamerica/19.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(260, 'Monterrey, Mexico', 'northamerica', 'Industrial city with mountains, modern architecture, and cultural venues.', '/Kindora/assets/images/destinations/northamerica/18.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(259, 'Tulum, Mexico', 'northamerica', 'Caribbean beaches, Mayan ruins, and eco-tourism.', '/Kindora/assets/images/destinations/northamerica/17.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(258, 'San Francisco', 'northamerica', 'Golden Gate Bridge, Alcatraz, hilly streets, and progressive culture.', '/Kindora/assets/images/destinations/northamerica/16.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(257, 'Jasper National Park', 'northamerica', 'Mountains, glaciers, forests, and remote wilderness in Alberta, Canada.', '/Kindora/assets/images/destinations/northamerica/15.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'Winter', 5),
(256, 'Orlando, Florida', 'northamerica', 'Theme parks, entertainment complexes, and family attractions.', '/Kindora/assets/images/destinations/northamerica/14.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(255, 'Quebec City, Canada', 'northamerica', 'Historic French colonial architecture, fortified city walls, and culture.', '/Kindora/assets/images/destinations/northamerica/13.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(254, 'Yellowknife, Canada', 'northamerica', 'Northern city known for aurora borealis views and indigenous culture.', '/Kindora/assets/images/destinations/northamerica/12.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(253, 'Washington D.C.', 'northamerica', 'US capital with historic monuments, museums, and political landmarks.', '/Kindora/assets/images/destinations/northamerica/11.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(252, 'Cancun, Mexico', 'northamerica', 'Popular resort city known for white sandy beaches and vibrant nightlife.', '/Kindora/assets/images/destinations/northamerica/10.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(251, 'Los Angeles, California', 'northamerica', 'Entertainment hubs, beaches, Hollywood, and multicultural neighborhoods.', '/Kindora/assets/images/destinations/northamerica/9.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(250, 'Vancouver, Canada', 'northamerica', 'Coastal city with mountains, urban parks, and diverse culture.', '/Kindora/assets/images/destinations/northamerica/8.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(249, 'Miami, Florida', 'northamerica', 'Sunny beaches, nightlife, Latin culture, and Art Deco architecture.', '/Kindora/assets/images/destinations/northamerica/7.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(248, 'Mexico City', 'northamerica', 'Vibrant cultural capital with Aztec ruins, museums, and lively street life.', '/Kindora/assets/images/destinations/northamerica/6.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(247, 'Yellowstone National Park', 'northamerica', 'First national park globally, geothermal wonders, wildlife encounters.', '/Kindora/assets/images/destinations/northamerica/5.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'Winter', 5),
(159, 'Paris, France', 'europe', 'Eiffel Tower, Louvre Museum, charming cafés, and romantic Seine riverbanks.', '/Kindora/assets/images/destinations/europe/1.avif', NULL, NULL, '2025-11-20 14:43:20', 1, 0, 'easy', 'Spring', 3),
(160, 'Rome, Italy', 'europe', 'Colosseum, Vatican City, ancient Roman Forum, and authentic Italian cuisine.', '/Kindora/assets/images/destinations/europe/2.avif', NULL, NULL, '2025-11-20 14:43:20', 1, 0, 'easy', 'All Year', 3),
(161, 'London, United Kingdom', 'europe', 'Big Ben, Tower Bridge, British Museum, and royal palaces.', '/Kindora/assets/images/destinations/europe/3.avif', NULL, NULL, '2025-11-20 14:43:20', 1, 0, 'easy', 'All Year', 3),
(162, 'Barcelona, Spain', 'europe', 'Sagrada Família, Park Güell, Gothic Quarter, and Mediterranean beaches.', '/Kindora/assets/images/destinations/europe/4.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(163, 'Amsterdam, Netherlands', 'europe', 'Canal cruises, Anne Frank House, vibrant nightlife, and bike-friendly streets.', '/Kindora/assets/images/destinations/europe/5.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(164, 'Vienna, Austria', 'europe', 'Schönbrunn Palace, classical music heritage, coffeehouse culture, and imperial architecture.', '/Kindora/assets/images/destinations/europe/6.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(165, 'Prague, Czech Republic', 'europe', 'Prague Castle, Charles Bridge, Old Town Square, and medieval charm.', '/Kindora/assets/images/destinations/europe/7.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(166, 'Santorini, Greece', 'europe', 'Whitewashed buildings, stunning sunsets, volcanic beaches, and Greek island life.', '/Kindora/assets/images/destinations/europe/8.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(167, 'Swiss Alps, Switzerland', 'europe', 'Matterhorn, Jungfraujoch, alpine skiing, and breathtaking mountain scenery.', '/Kindora/assets/images/destinations/europe/9.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'challenging', 'Summer', 7),
(168, 'Florence, Italy', 'europe', 'Renaissance art, Uffizi Gallery, Duomo cathedral, and Tuscan countryside.', '/Kindora/assets/images/destinations/europe/10.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(169, 'Venice, Italy', 'europe', 'Grand Canal, St. Mark\'s Square, gondola rides, and unique floating city.', '/Kindora/assets/images/destinations/europe/11.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(170, 'Edinburgh, Scotland', 'europe', 'Edinburgh Castle, Royal Mile, Scottish Highland culture, and historic festivals.', '/Kindora/assets/images/destinations/europe/12.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(171, 'Lisbon, Portugal', 'europe', 'Colorful neighborhoods, historic trams, fado music, and Atlantic coastline.', '/Kindora/assets/images/destinations/europe/13.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(172, 'Berlin, Germany', 'europe', 'Brandenburg Gate, Berlin Wall history, vibrant art scene, and modern culture.', '/Kindora/assets/images/destinations/europe/14.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(173, 'Stockholm, Sweden', 'europe', 'Archipelago islands, Gamla Stan old town, ABBA Museum, and Nordic design.', '/Kindora/assets/images/destinations/europe/15.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(174, 'Budapest, Hungary', 'europe', 'Danube River views, thermal baths, Parliament building, and ruin bars.', '/Kindora/assets/images/destinations/europe/16.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(175, 'Copenhagen, Denmark', 'europe', 'Nyhavn harbor, Tivoli Gardens, bike culture, and Danish hygge lifestyle.', '/Kindora/assets/images/destinations/europe/17.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(176, 'Dubrovnik, Croatia', 'europe', 'Medieval city walls, Adriatic Sea views, Game of Thrones filming locations.', '/Kindora/assets/images/destinations/europe/18.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(177, 'Reykjavik, Iceland', 'europe', 'Northern Lights, Blue Lagoon, geysers, glaciers, and volcanic landscapes.', '/Kindora/assets/images/destinations/europe/19.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(178, 'Oslo, Norway', 'europe', 'Viking Ship Museum, fjords, midnight sun, and Scandinavian design.', '/Kindora/assets/images/destinations/europe/20.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(179, 'Milan, Italy', 'europe', 'Fashion capital, Gothic Duomo, La Scala opera house, and designer shopping.', '/Kindora/assets/images/destinations/europe/21.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(180, 'Athens, Greece', 'europe', 'Acropolis, Parthenon, ancient history, Mediterranean cuisine, and Greek islands nearby.', '/Kindora/assets/images/destinations/europe/22.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(181, 'Bruges, Belgium', 'europe', 'Medieval canals, chocolate shops, historic market squares, and fairy-tale architecture.', '/Kindora/assets/images/destinations/europe/23.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(182, 'Monaco', 'europe', 'Monte Carlo Casino, luxury yachts, Formula 1 circuit, and French Riviera glamour.', '/Kindora/assets/images/destinations/europe/24.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(183, 'Krakow, Poland', 'europe', 'Historic Old Town, Wawel Castle, Jewish Quarter, and Polish cultural heritage.', '/Kindora/assets/images/destinations/europe/25.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(184, 'Helsinki, Finland', 'europe', 'Baltic Sea archipelago, saunas, design districts, and Finnish minimalism.', '/Kindora/assets/images/destinations/europe/26.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(185, 'Zurich, Switzerland', 'europe', 'Lake Zurich, financial district, nearby Alps, and Swiss precision culture.', '/Kindora/assets/images/destinations/europe/27.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(244, 'Grand Canyon', 'northamerica', 'Expansive canyon carved by the Colorado River, with hiking and rafting.', '/Kindora/assets/images/destinations/northamerica/2.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(245, 'Niagara Falls', 'northamerica', 'Famous waterfalls straddling the US-Canada border, boat tours and parks.', '/Kindora/assets/images/destinations/northamerica/3.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(246, 'Banff National Park', 'northamerica', 'Rocky Mountain scenery, glaciers, turquoise lakes, and wildlife.', '/Kindora/assets/images/destinations/northamerica/4.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'Winter', 5),
(264, 'Amazon Rainforest', 'southamerica', 'The world’s largest tropical rainforest with immense biodiversity and indigenous cultures.', '/Kindora/assets/images/destinations/southamerica/1.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'moderate', 'All Year', 10),
(265, 'Machu Picchu, Peru', 'southamerica', '15th-century Incan citadel set high in the Andes Mountains with stunning archaeological ruins.', '/Kindora/assets/images/destinations/southamerica/2.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(266, 'Iguazu Falls', 'southamerica', 'Massive waterfalls on the border between Argentina and Brazil, surrounded by national parks.', '/Kindora/assets/images/destinations/southamerica/3.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(267, 'Galápagos Islands', 'southamerica', 'Volcanic archipelago known for unique wildlife and Darwin’s studies on evolution.', '/Kindora/assets/images/destinations/southamerica/4.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(215, 'Cairo, Egypt', 'africa', 'Great Pyramids of Giza, Sphinx, ancient Egyptian temples, and Nile River cruises.', '/Kindora/assets/images/destinations/africa/1.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(216, 'Marrakech, Morocco', 'africa', 'Vibrant souks, Jemaa el-Fnaa square, Atlas Mountains, and traditional riads.', '/Kindora/assets/images/destinations/africa/2.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(217, 'Cape Town, South Africa', 'africa', 'Table Mountain, wine estates, penguins at Boulders Beach, and stunning coastlines.', '/Kindora/assets/images/destinations/africa/3.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'easy', 'All Year', 5),
(218, 'Serengeti, Tanzania', 'africa', 'Great Migration, Big Five wildlife, endless savannas, and luxury safari lodges.', '/Kindora/assets/images/destinations/africa/4.avif', NULL, NULL, '2025-11-20 14:39:20', 1, 0, 'moderate', 'Winter', 6),
(187, 'Alice Springs', 'australia', 'Gateway to Australia’s Red Centre and Uluru.', '/Kindora/assets/images/destinations/australia/alice_springs.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(188, 'Auckland', 'australia', 'New Zealand’s largest city, waterfront and Sky Tower.', '/Kindora/assets/images/destinations/australia/auckland.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(189, 'Brisbane', 'australia', 'River city with lively arts scene and South Bank Parklands.', '/Kindora/assets/images/destinations/australia/brisbane.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(190, 'Broome', 'australia', 'Pearl Beach and Cable Beach sunsets.', '/Kindora/assets/images/destinations/australia/broome.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(243, 'New York City, USA', 'northamerica', 'Iconic skyline, metropolitan culture, Broadway, and Central Park.', '/Kindora/assets/images/destinations/northamerica/1.avif', NULL, NULL, '2025-11-20 14:43:20', 1, 0, 'easy', 'All Year', 3),
(211, 'Tonga', 'australia', 'South Pacific kingdom with islands and reefs.', '/Kindora/assets/images/destinations/australia/tonga.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(212, 'Uluru', 'australia', 'Massive sandstone monolith at sunset.', '/Kindora/assets/images/destinations/australia/uluru.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(213, 'Vanuatu', 'australia', 'Melanesian islands with volcanoes and culture.', '/Kindora/assets/images/destinations/australia/vanuata.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(214, 'Wellington', 'australia', 'New Zealand’s capital, harbor and Te Papa museum.', '/Kindora/assets/images/destinations/australia/wellington.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(241, 'Alexandria, Egypt', 'africa', 'Ancient lighthouse site, Mediterranean coastline, Bibliotheca Alexandrina, and Greek heritage.', '/Kindora/assets/images/destinations/africa/27.avif', NULL, NULL, '2025-11-16 12:57:18', 1, 0, 'moderate', 'All Year', 5),
(242, 'African Trip Planner', 'africa', 'Review and plan your perfect African itinerary.', '/Kindora/assets/images/destinations/africa/7.avif', NULL, NULL, '2025-11-17 15:04:45', 0, 0, 'moderate', 'All Year', 5),
(300, 'temp', 'asia', 'new', 'no', 'no', NULL, '2025-11-17 17:21:28', 1, 0, 'moderate', NULL, 5),
(297, '12345', 'asia', 'abcde', 'no image', 'no video', NULL, '2025-11-17 17:14:46', 1, 0, 'moderate', NULL, 5),
(301, 'Summer Escape', 'packages', 'Enjoy sunny beaches, tropical islands, and exotic adventures under the warm sun.', 'assets/images/packages/summer.avif', NULL, NULL, '2025-11-20 10:56:18', 1, 0, 'moderate', 'Summer', 5),
(302, 'Winter Wonderland', 'packages', 'Experience snowy mountains, skiing adventures, and cozy winter retreats.', 'assets/images/packages/winter.avif', NULL, NULL, '2025-11-20 10:56:18', 1, 0, 'moderate', 'Winter', 5),
(303, 'Monsoon Magic', 'packages', 'Explore lush greenery, breathtaking waterfalls, and refreshing monsoon experiences.', 'assets/images/packages/monsoon.avif', NULL, NULL, '2025-11-20 10:56:18', 1, 0, 'moderate', 'Monsoon', 5),
(304, 'dghj', 'africa', 'fgjnb', NULL, NULL, NULL, '2025-11-21 04:40:03', 1, 0, 'moderate', NULL, 5);

-- --------------------------------------------------------

--
-- Table structure for table `destination_pricing`
--

CREATE TABLE `destination_pricing` (
  `pricing_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `season` varchar(50) DEFAULT 'standard',
  `price_economy` decimal(10,2) NOT NULL,
  `price_standard` decimal(10,2) NOT NULL,
  `price_luxury` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `adults_base_count` int(11) DEFAULT 1,
  `child_discount_percent` decimal(5,2) DEFAULT 50.00,
  `group_discount_percent` decimal(5,2) DEFAULT 10.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `destination_pricing`
--

INSERT INTO `destination_pricing` (`pricing_id`, `destination_id`, `season`, `price_economy`, `price_standard`, `price_luxury`, `currency`, `adults_base_count`, `child_discount_percent`, `group_discount_percent`, `created_at`, `updated_at`) VALUES
(1, 16, 'standard', 399.00, 699.00, 1299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(2, 17, 'standard', 1899.00, 3299.00, 5999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(3, 18, 'standard', 449.00, 799.00, 1499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(4, 19, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(5, 20, 'standard', 599.00, 1099.00, 1999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(6, 21, 'standard', 699.00, 1299.00, 2399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(7, 22, 'standard', 1199.00, 2199.00, 3999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(8, 23, 'standard', 599.00, 1099.00, 1999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(9, 24, 'standard', 499.00, 899.00, 1699.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(10, 25, 'standard', 599.00, 1099.00, 1999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(11, 26, 'standard', 399.00, 699.00, 1299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(12, 27, 'standard', 799.00, 1499.00, 2799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(13, 28, 'standard', 599.00, 1099.00, 1999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(14, 29, 'standard', 699.00, 1299.00, 2399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(15, 30, 'standard', 999.00, 1899.00, 3599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(16, 31, 'standard', 1299.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(17, 32, 'standard', 899.00, 1699.00, 3199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(18, 33, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(19, 34, 'standard', 799.00, 1499.00, 2799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(20, 35, 'standard', 699.00, 1299.00, 2399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(21, 36, 'standard', 699.00, 1299.00, 2399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(22, 37, 'standard', 1599.00, 2899.00, 5299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(23, 38, 'standard', 599.00, 1099.00, 1999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(24, 39, 'standard', 599.00, 1099.00, 1999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(25, 40, 'standard', 499.00, 899.00, 1699.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(26, 41, 'standard', 899.00, 1699.00, 3199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(27, 42, 'standard', 1299.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(28, 43, 'standard', 0.00, 0.00, 0.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(29, 64, 'standard', 8999.00, 14999.00, 24999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(30, 65, 'standard', 7999.00, 12999.00, 21999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(31, 66, 'standard', 9999.00, 15999.00, 26999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(32, 67, 'standard', 8499.00, 13999.00, 23999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(33, 68, 'standard', 9499.00, 15499.00, 25999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(34, 69, 'standard', 8999.00, 14999.00, 24999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(35, 70, 'standard', 7499.00, 12499.00, 20999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(36, 71, 'standard', 11999.00, 18999.00, 31999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(37, 72, 'standard', 12999.00, 19999.00, 33999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(38, 73, 'standard', 13999.00, 21999.00, 36999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(39, 74, 'standard', 15999.00, 24999.00, 41999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(40, 75, 'standard', 9999.00, 15999.00, 26999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(41, 76, 'standard', 13499.00, 20999.00, 35999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(42, 77, 'standard', 10999.00, 17999.00, 29999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(43, 78, 'standard', 9499.00, 15499.00, 25999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(44, 79, 'standard', 10499.00, 16999.00, 28999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(45, 80, 'standard', 8999.00, 14999.00, 24999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(46, 81, 'standard', 9299.00, 15299.00, 25499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(47, 82, 'standard', 8799.00, 14799.00, 24799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(48, 159, 'standard', 1299.00, 2199.00, 3999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(49, 160, 'standard', 1199.00, 1999.00, 3699.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(50, 161, 'standard', 1399.00, 2399.00, 4299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(51, 162, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(52, 163, 'standard', 1199.00, 2099.00, 3799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(53, 164, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(54, 165, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(55, 166, 'standard', 1199.00, 2099.00, 3899.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(56, 167, 'standard', 1699.00, 2999.00, 5499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(57, 168, 'standard', 1199.00, 1999.00, 3699.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(58, 169, 'standard', 1299.00, 2199.00, 3999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(59, 170, 'standard', 1199.00, 2099.00, 3799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(60, 171, 'standard', 999.00, 1699.00, 3199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(61, 172, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(62, 173, 'standard', 1399.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(63, 174, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(64, 175, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(65, 176, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(66, 177, 'standard', 1599.00, 2799.00, 5099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(67, 178, 'standard', 1699.00, 2999.00, 5499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(68, 179, 'standard', 1299.00, 2199.00, 3999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(69, 180, 'standard', 999.00, 1699.00, 3199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(70, 181, 'standard', 1199.00, 2099.00, 3799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(71, 182, 'standard', 2999.00, 4999.00, 8999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(72, 183, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(73, 184, 'standard', 1399.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(74, 185, 'standard', 1799.00, 3199.00, 5799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(75, 187, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(76, 188, 'standard', 1199.00, 2099.00, 3899.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(77, 189, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(78, 190, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(79, 191, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(80, 192, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(81, 193, 'standard', 1099.00, 1999.00, 3699.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(82, 194, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(83, 195, 'standard', 1899.00, 3299.00, 5999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(84, 196, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(85, 197, 'standard', 1099.00, 1999.00, 3699.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(86, 198, 'standard', 1599.00, 2899.00, 5299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(87, 199, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(88, 200, 'standard', 1099.00, 1999.00, 3699.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(89, 201, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(90, 202, 'standard', 1799.00, 3199.00, 5799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(91, 203, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(92, 204, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(93, 205, 'standard', 1399.00, 2499.00, 4599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(94, 206, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(95, 207, 'standard', 1699.00, 2999.00, 5499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(96, 208, 'standard', 1299.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(97, 209, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(98, 210, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(99, 211, 'standard', 1599.00, 2799.00, 5099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(100, 212, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(101, 213, 'standard', 1799.00, 3199.00, 5799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(102, 214, 'standard', 1199.00, 2099.00, 3899.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(103, 215, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(104, 216, 'standard', 699.00, 1199.00, 2199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(105, 217, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(106, 218, 'standard', 1899.00, 3299.00, 5999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(107, 219, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(108, 220, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(109, 221, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(110, 222, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(111, 223, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(112, 224, 'standard', 999.00, 1699.00, 3199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(113, 225, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(114, 226, 'standard', 1599.00, 2899.00, 5299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(115, 227, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(116, 228, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(117, 229, 'standard', 1399.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(118, 230, 'standard', 1799.00, 3199.00, 5799.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(119, 231, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(120, 232, 'standard', 1999.00, 3499.00, 6299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(121, 233, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(122, 234, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(123, 235, 'standard', 1199.00, 2099.00, 3899.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(124, 236, 'standard', 799.00, 1399.00, 2699.00, 'USD', 1, 30.00, 5.00, '2025-10-12 17:34:22', '2025-11-17 17:15:14'),
(125, 237, 'standard', 699.00, 1199.00, 2199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(126, 238, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(127, 239, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(128, 240, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(129, 241, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(130, 242, 'standard', 0.00, 0.00, 0.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(131, 243, 'standard', 1699.00, 2999.00, 5499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(132, 244, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(133, 245, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(134, 246, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(135, 247, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(136, 248, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(137, 249, 'standard', 1299.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(138, 250, 'standard', 1399.00, 2499.00, 4599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(139, 251, 'standard', 1599.00, 2899.00, 5299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(140, 252, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(141, 253, 'standard', 1399.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(142, 254, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(143, 255, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(144, 256, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(145, 257, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(146, 258, 'standard', 1599.00, 2899.00, 5299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(147, 259, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(148, 260, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(149, 261, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(150, 262, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(151, 263, 'standard', 0.00, 0.00, 0.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(152, 264, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(153, 265, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(154, 266, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(155, 267, 'standard', 2299.00, 3999.00, 7299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(156, 268, 'standard', 699.00, 1199.00, 2199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(157, 269, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(158, 270, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(159, 271, 'standard', 1199.00, 2099.00, 3899.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(160, 272, 'standard', 1599.00, 2799.00, 5099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(161, 273, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(162, 274, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(163, 275, 'standard', 1899.00, 3299.00, 5999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(164, 276, 'standard', 1699.00, 2999.00, 5499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(165, 277, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(166, 278, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(167, 279, 'standard', 1099.00, 1899.00, 3499.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(168, 280, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(169, 281, 'standard', 1399.00, 2399.00, 4399.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(170, 282, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(171, 283, 'standard', 899.00, 1599.00, 2999.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(172, 284, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(173, 285, 'standard', 699.00, 1199.00, 2199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(174, 286, 'standard', 1199.00, 2199.00, 4099.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(175, 287, 'standard', 1299.00, 2299.00, 4199.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(176, 288, 'standard', 799.00, 1399.00, 2599.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(177, 289, 'standard', 1199.00, 2099.00, 3899.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(178, 290, 'standard', 999.00, 1799.00, 3299.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(179, 291, 'standard', 0.00, 0.00, 0.00, 'USD', 1, 50.00, 10.00, '2025-10-12 17:34:22', '2025-10-12 17:34:22'),
(180, 296, 'standard', 1000.00, 2000.00, 3000.00, 'USD', 1, 50.00, 10.00, '2025-11-17 14:20:52', '2025-11-17 14:20:52'),
(181, 297, 'standard', 1.00, 2.00, 3.00, 'USD', 1, 1.00, 10.00, '2025-11-17 17:04:51', '2025-11-20 15:26:30'),
(182, 298, 'standard', 1.00, 10.00, 20.00, 'USD', 2, 30.00, 10.00, '2025-11-17 17:11:04', '2025-11-17 17:11:04'),
(183, 299, 'standard', 1.00, 23.00, 3.00, 'USD', NULL, NULL, NULL, '2025-11-17 17:17:52', '2025-11-17 17:17:52'),
(184, 300, 'standard', 1.00, 2.00, 4.00, 'USD', NULL, NULL, NULL, '2025-11-17 17:21:28', '2025-11-17 17:21:28'),
(193, 11, 'standard', 6000.00, 12000.00, 20000.00, 'USD', 1, 10.00, 10.00, '2025-11-20 13:17:24', '2025-11-20 13:17:24'),
(192, 10, 'standard', 6000.00, 12000.00, 18000.00, 'USD', 1, 20.00, 10.00, '2025-11-20 13:17:24', '2025-11-20 13:17:24'),
(191, 9, 'standard', 5000.00, 10000.00, 15000.00, 'INR', 1, 50.00, 10.00, '2025-11-20 13:17:24', '2025-11-20 13:17:24'),
(188, 301, 'standard', 50000.00, 100000.00, 150000.00, 'INR', 1, 30.00, 10.00, '2025-11-20 11:00:42', '2025-11-20 11:00:42'),
(189, 302, 'standard', 50000.00, 100000.00, 150000.00, 'INR', 1, 30.00, 10.00, '2025-11-20 11:00:42', '2025-11-20 11:00:42'),
(190, 303, 'standard', 50000.00, 100000.00, 150000.00, 'INR', 1, 50.00, 10.00, '2025-11-20 11:00:42', '2025-11-20 11:00:42'),
(200, 304, 'standard', 10.00, 10.00, 20.00, 'INR', NULL, NULL, NULL, '2025-11-21 04:40:03', '2025-11-21 04:40:03');

-- --------------------------------------------------------

--
-- Table structure for table `faq`
--

CREATE TABLE `faq` (
  `faq_id` int(11) NOT NULL,
  `question` varchar(255) NOT NULL,
  `answer` longtext NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faq`
--

INSERT INTO `faq` (`faq_id`, `question`, `answer`, `sort_order`, `is_active`, `created_at`) VALUES
(1, 'What is Kindora?', 'Kindora is a comprehensive travel platform that helps you explore the world with a focus on authentic experiences, cultural immersion, and sustainable tourism.', 1, 1, '2025-11-10 13:22:31'),
(2, 'Is Kindora free to use?', 'Yes! Our travel guides and basic features are completely free. We only charge for bookings and premium services.', 2, 1, '2025-11-10 13:22:31'),
(3, 'Can I suggest places to add?', 'Absolutely! We love hearing from our community. You can suggest new destinations and share your travel experiences.', 3, 1, '2025-11-10 13:22:31'),
(4, 'What services does Kindora provide?', 'We offer destination guides, booking services, activity reservations, transportation options, and 24/7 customer support.', 4, 1, '2025-11-10 13:22:31'),
(5, 'How do I book a trip?', 'Browse destinations, select your package, choose dates, and complete the booking process with our team.', 5, 1, '2025-11-10 13:22:31'),
(6, 'What if I need to cancel?', 'Our cancellation policy varies by package. Contact our support team as soon as possible to discuss your options.', 6, 1, '2025-11-10 13:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `subscriber_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subscribed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `type` enum('booking','payment','review','system','admin') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `admin_id`, `type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, NULL, 1, 'booking', 'New booking received', 'Ketan Parmar booked Paris, France for 2 guests', 0, '2025-10-12 14:48:06'),
(2, NULL, 1, 'payment', 'Payment confirmed', 'Pavan Prajapati completed payment for Bali & Raja Ampat trip', 1, '2025-10-12 14:48:06'),
(3, NULL, 1, 'review', 'New review posted', 'Vansh Soni left a 4-star review for Cape Town, South Africa', 0, '2025-10-12 14:48:06'),
(4, NULL, 1, 'booking', 'Booking pending', 'Shreyas Visnagara booking for Tokyo, Japan requires attention', 0, '2025-10-12 14:48:06'),
(5, NULL, 1, 'system', 'Weekly report', 'Total bookings this week: 12, Revenue: $18,439', 1, '2025-10-12 14:48:06'),
(6, NULL, 1, 'system', 'New Destination Added', 'Added new destination: temp', 0, '2025-10-12 15:37:10'),
(7, NULL, 1, 'system', 'Destination Deleted', 'Deleted destination ID: 293', 0, '2025-10-12 15:42:07');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `destination_id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `rating` int(1) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `title` varchar(255) DEFAULT NULL,
  `review_text` text NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `user_id`, `destination_id`, `booking_id`, `rating`, `title`, `review_text`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 159, 1, 5, 'Absolutely magical!', 'Paris exceeded all expectations. The Eiffel Tower at night, the Louvre, and the romantic Seine cruise were unforgettable. Highly recommend this destination!', 'approved', '2025-10-12 14:48:06', '2025-10-12 14:48:06'),
(2, 6, 30, 3, 5, 'Paradise found', 'Bali and Raja Ampat were incredible! The diving was world-class and the beaches pristine. Perfect for adventure seekers and relaxation alike.', 'approved', '2025-10-12 14:48:06', '2025-10-12 14:48:06'),
(3, 7, 217, 4, 4, 'Amazing safari experience', 'Cape Town was fantastic! Table Mountain views, wine estates, and seeing penguins at Boulders Beach. Only wish we had more time!', 'approved', '2025-10-12 14:48:06', '2025-10-12 14:48:06'),
(5, 20, 236, NULL, 5, NULL, 'bahot badhiya maja aagaya', 'rejected', '2025-11-20 14:01:06', '2025-11-20 15:51:50'),
(6, 20, 24, NULL, 5, NULL, 'very good experience', 'rejected', '2025-11-20 15:01:49', '2025-11-20 15:51:40'),
(7, 20, 24, NULL, 1, NULL, 'qwertyuiopzasdfgh', 'rejected', '2025-11-20 15:54:05', '2025-11-20 15:54:45'),
(8, 20, 208, NULL, 5, NULL, 'aadfjajdajsakakasdkksa', 'approved', '2025-11-21 04:37:08', '2025-11-21 04:39:25');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `testimonial_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `text` longtext NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`testimonial_id`, `user_id`, `name`, `image_url`, `text`, `rating`, `title`, `is_active`, `created_at`) VALUES
(1, NULL, 'Sarah Johnson', 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=150&h=150&fit=crop&crop=face', 'Kindora made our dream trip to Santorini absolutely perfect! The attention to detail and local recommendations were spot on.', 5, 'Travel Blogger', 1, '2025-11-10 13:22:31'),
(2, NULL, 'Michael Chen', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&h=150&fit=crop&crop=face', 'The African safari package exceeded all expectations. We saw the Big Five and had the most incredible wildlife encounters!', 5, 'Wildlife Photographer', 1, '2025-11-10 13:22:31'),
(3, NULL, 'Emma Rodriguez', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=150&h=150&fit=crop&crop=face', 'From booking to the actual trip, everything was seamless. The local guides were knowledgeable and friendly.', 5, 'Adventure Seeker', 1, '2025-11-10 13:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `travel_packages`
--

CREATE TABLE `travel_packages` (
  `package_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `badge` varchar(100) DEFAULT NULL,
  `features` longtext DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `travel_packages`
--

INSERT INTO `travel_packages` (`package_id`, `name`, `description`, `image_url`, `price`, `duration_days`, `badge`, `features`, `is_active`, `created_at`) VALUES
(1, 'Summer Escape', 'Enjoy sunny beaches, tropical islands, and exotic adventures under the warm sun.', 'assets/images/packages/summer.avif', 599.00, 7, 'Best Seller', 'Beach Destinations,Water Activities,Nightlife', 1, '2025-11-10 13:22:31'),
(2, 'Winter Wonderland', 'Experience snowy mountains, skiing adventures, and cozy winter retreats.', 'assets/images/packages/winter.avif', 799.00, 5, 'New', 'Skiing,Cozy Lodges,Snow Activities', 1, '2025-11-10 13:22:31'),
(3, 'Monsoon Magic', 'Explore lush greenery, breathtaking waterfalls, and refreshing monsoon experiences.', 'assets/images/packages/monsoon.avif', 399.00, 6, 'Eco-Friendly', 'Green Landscapes,Waterfalls,Eco Tourism', 1, '2025-11-10 13:22:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_image` varchar(500) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `role` enum('user','admin','guide') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `email_verified` tinyint(1) DEFAULT 0,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `reward_points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `full_name`, `phone`, `profile_image`, `date_of_birth`, `nationality`, `role`, `is_active`, `email_verified`, `failed_login_attempts`, `locked_until`, `last_login`, `reward_points`, `created_at`, `updated_at`) VALUES
(1, 'kindora.explore@gmail.com', '$2y$10$7lOA6nbQEgRmaxtC.AuI2.az/0Vh98sWHHP0zQFSN4B9D2trxYG6i', 'kindora.explore@gmail.com', 'Admin Kindora', NULL, NULL, NULL, NULL, 'admin', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(4, 'ketan_parmar', 'Ketan@123', 'ketan@gmail.com', 'Ketan Parmar', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(5, 'Shreyas_visnagara', 'Shreyas@123', 'shreyas@gmail.com', 'Shreyas Visnagara', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(6, 'pavan_prajapati', 'Pavan@123', 'pavanprajapati@gmail.com', 'Pavan Prajapati', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(7, 'vansh_soni', 'Vansh@123', 'vanshsoni@gmail.com', 'Vansh Soni', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(8, 'meet_soni', 'Meet@123', 'meetsoni@gmail.com', 'Meet Soni', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(9, 'newuser@gmail.com', '$2y$10$KCbL/0roxfDFL.XebykdlO0LFdiROSj4Eql8p56PnM9gRKgIMKiHO', 'newuser@gmail.com', 'new user', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(11, 'shahidljku@gmail.com', '$2y$10$zmEsDwhLhrfS.KQ.0JB1V.p0N/9e3V6Oyv4JCIO2n.0ckD4iVHqUK', 'shahidljku@gmail.com', 'Shahid', NULL, NULL, NULL, NULL, 'admin', 1, 0, 0, NULL, NULL, 50, '2025-10-12 15:35:45', '2025-11-17 17:24:36'),
(12, 'abdulbasitljku@gmail.com', '$2y$10$OVVkrygl2v7GFeHUEZv2rOA4pKnoOOG8xYb5qOpgYKk43I3cU3QqG', 'abdulbasitljku@gmail.com', 'Abdulbasit', NULL, NULL, NULL, NULL, 'admin', 1, 0, 0, NULL, NULL, 0, '2025-10-12 15:35:45', '2025-10-12 17:03:23'),
(13, 'ayush@gmail.com', '$2y$10$C9w3zQn0WrAq/grY1kveV.x87m3t2GhlvgR6R9BY/Jz0pgbfXVZDK', 'ayush@gmail.com', 'Ayush DIC', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 300, '2025-10-12 15:47:29', '2025-10-12 18:11:13'),
(18, 'abcd205', '$2y$10$WMPfsD2/ApZvx/4ED1zb9uH.WSP347h2t3vka7Lz3BNvMDu2ZUsFS', 'abcd@gmail.com', 'abcd', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 17:43:48', '2025-10-12 17:43:48'),
(19, 'karanvirsinh353', '$2y$10$RsqVVw.n0m..pe41IEq3R.3cVi9i/ax57f.jH5H.Pb7QF8rNhtph6', 'kaRanveersinghshekhawat13@gmail.com', 'KARANVIRSINH', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 0, '2025-10-12 17:45:47', '2025-10-12 17:45:47'),
(20, '123456@gmail.com', '$2y$10$99LlIEZXRcSfWR5DxgqlaOlPMh/JB4R5oFq.ija2A5Pa9RAr/33C.', '123456@gmail.com', '123456', NULL, NULL, NULL, NULL, 'user', 1, 0, 0, NULL, NULL, 9086, '2025-11-14 11:28:49', '2025-11-21 04:37:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `deals`
--
ALTER TABLE `deals`
  ADD PRIMARY KEY (`deal_id`);

--
-- Indexes for table `destinations`
--
ALTER TABLE `destinations`
  ADD PRIMARY KEY (`destination_id`),
  ADD UNIQUE KEY `name` (`name`(191)),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_suggested_duration` (`suggested_duration_days`);

--
-- Indexes for table `destination_pricing`
--
ALTER TABLE `destination_pricing`
  ADD PRIMARY KEY (`pricing_id`),
  ADD KEY `destination_id` (`destination_id`);

--
-- Indexes for table `faq`
--
ALTER TABLE `faq`
  ADD PRIMARY KEY (`faq_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`subscriber_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `destination_id` (`destination_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`testimonial_id`);

--
-- Indexes for table `travel_packages`
--
ALTER TABLE `travel_packages`
  ADD PRIMARY KEY (`package_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_email_verified` (`email_verified`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `deals`
--
ALTER TABLE `deals`
  MODIFY `deal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `destinations`
--
ALTER TABLE `destinations`
  MODIFY `destination_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=305;

--
-- AUTO_INCREMENT for table `destination_pricing`
--
ALTER TABLE `destination_pricing`
  MODIFY `pricing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT for table `faq`
--
ALTER TABLE `faq`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `subscriber_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `testimonial_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `travel_packages`
--
ALTER TABLE `travel_packages`
  MODIFY `package_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
