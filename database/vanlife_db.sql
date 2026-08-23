-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 07:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vanlife_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `begeni`
--

CREATE TABLE `begeni` (
  `begeni_id` varchar(50) NOT NULL,
  `kullanici_id` int(11) NOT NULL,
  `van_id` int(11) NOT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `begeni`
--

INSERT INTO `begeni` (`begeni_id`, `kullanici_id`, `van_id`, `olusturma_tarihi`) VALUES
('2e0a48a0-474d-11f0-a2b2-3e46a4640b07', 6, 32, '2025-06-12 05:22:05'),
('5c6b6434-474d-11f0-a2b2-3e46a4640b07', 7, 36, '2025-06-12 05:23:23'),
('626b1a76-474d-11f0-a2b2-3e46a4640b07', 7, 40, '2025-06-12 05:23:33'),
('99dc5e54-474d-11f0-a2b2-3e46a4640b07', 9, 33, '2025-06-12 05:25:06'),
('a7a612d3-474d-11f0-a2b2-3e46a4640b07', 9, 39, '2025-06-12 05:25:29'),
('bb4c83b0-474d-11f0-a2b2-3e46a4640b07', 5, 39, '2025-06-12 05:26:02'),
('d6b25d0c-474d-11f0-a2b2-3e46a4640b07', 6, 33, '2025-06-12 05:26:48'),
('da5f25ef-474d-11f0-a2b2-3e46a4640b07', 6, 39, '2025-06-12 05:26:55'),
('e9b210d5-474d-11f0-a2b2-3e46a4640b07', 8, 33, '2025-06-12 05:27:20'),
('ed273064-474c-11f0-a2b2-3e46a4640b07', 5, 33, '2025-06-12 05:20:17'),
('ed43f31f-474d-11f0-a2b2-3e46a4640b07', 8, 39, '2025-06-12 05:27:26'),
('fcc5cfea-474d-11f0-a2b2-3e46a4640b07', 8, 32, '2025-06-12 05:27:52');

-- --------------------------------------------------------

--
-- Table structure for table `destek_talepleri`
--

CREATE TABLE `destek_talepleri` (
  `talep_id` int(11) NOT NULL,
  `musteri_id` int(11) NOT NULL,
  `talep_turu` enum('kiralik_iptal','kiralik_sorunu','van_sorunu','diger') NOT NULL,
  `kiralik_id` int(11) DEFAULT NULL,
  `van_id` int(11) DEFAULT NULL,
  `baslik` varchar(100) NOT NULL,
  `aciklama` text NOT NULL,
  `durum` enum('beklemede','cevaplandı','çözüldü') DEFAULT 'beklemede',
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `destek_talepleri`
--

INSERT INTO `destek_talepleri` (`talep_id`, `musteri_id`, `talep_turu`, `kiralik_id`, `van_id`, `baslik`, `aciklama`, `durum`, `olusturma_tarihi`) VALUES
(1, 5, 'kiralik_iptal', 23, NULL, 'pahalidir', 'istemiyorum', 'beklemede', '2025-06-01 08:39:18'),
(2, 5, 'kiralik_iptal', 23, NULL, 'pahalidir', 'nkjhhk', 'beklemede', '2025-06-01 14:58:41'),
(3, 22, 'kiralik_iptal', 27, NULL, 'djogjsdo', 'jddsioejhdfklndv ndiov ijosd', 'beklemede', '2025-06-04 14:47:56');

-- --------------------------------------------------------

--
-- Table structure for table `kiralik`
--

CREATE TABLE `kiralik` (
  `kiralik_id` int(11) NOT NULL,
  `van_id` int(50) NOT NULL,
  `satici_id` int(50) NOT NULL,
  `musteri_id` int(50) NOT NULL,
  `kiralama_baslangıç_tarihi` date NOT NULL,
  `kiralama_bitiş_tarihi` date NOT NULL,
  `kira_tutari` varchar(50) NOT NULL,
  `kiralama_durumu` enum('beklemede','onaylandi','reddedildi','aktif','tamamlandi') NOT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kiralik`
--

INSERT INTO `kiralik` (`kiralik_id`, `van_id`, `satici_id`, `musteri_id`, `kiralama_baslangıç_tarihi`, `kiralama_bitiş_tarihi`, `kira_tutari`, `kiralama_durumu`, `olusturma_tarihi`) VALUES
(21, 36, 20, 16, '2025-05-31', '2025-06-03', '7199.61', 'reddedildi', '2025-05-31 15:04:16'),
(22, 36, 20, 6, '2025-05-31', '2025-06-02', '4799.74', 'aktif', '2025-05-31 15:08:14'),
(23, 37, 20, 5, '2025-06-01', '2025-06-03', '9000', 'aktif', '2025-05-31 16:13:44'),
(24, 40, 21, 10, '2025-06-01', '2025-06-03', '6999.96', 'reddedildi', '2025-06-01 17:50:36'),
(25, 34, 19, 5, '2025-06-01', '2025-06-03', '6800', 'reddedildi', '2025-06-01 20:05:56'),
(26, 39, 21, 5, '2025-06-05', '2025-06-05', '4000', 'reddedildi', '2025-06-03 15:05:43'),
(27, 34, 19, 22, '2025-06-05', '2025-06-06', '3400', 'reddedildi', '2025-06-04 14:47:05'),
(28, 32, 18, 7, '2025-06-12', '2025-06-15', '4500', 'reddedildi', '2025-06-12 05:23:03');

-- --------------------------------------------------------

--
-- Table structure for table `kullanici`
--

CREATE TABLE `kullanici` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL,
  `soyad` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `sifre` varchar(255) NOT NULL,
  `rol` enum('musteri','satici','admin') NOT NULL,
  `adres` varchar(255) NOT NULL,
  `sehir` varchar(255) DEFAULT NULL,
  `telefon` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kullanici`
--

INSERT INTO `kullanici` (`id`, `ad`, `soyad`, `email`, `sifre`, `rol`, `adres`, `sehir`, `telefon`) VALUES
(2, '', '', 'test2@gmail.com', '$2y$10$Rq6atoUkcizLxTndvQFmIuUH0CN7ccW1Eo/2yQlqNf6cR.K4YrWA6', 'satici', '', '', ''),
(3, 'Süleyman', 'Çalışkan', 'satici1@gmail.com', '$2y$10$6krOWLaO9o3SSarkrxTGTeZzbBmIFr.l0lNdugEbWQwTkGCmhe.Nu', 'satici', 'Bartın  merkez', 'Bartin', '5969472784'),
(4, 'Ağır', ' Abi ', 'satici2@gmail.com', '$2y$10$eYLQXcLVHKvoUYgFO8uaUOlpalPmCX548EqhrGM39o8pa0o.KEgfS', 'satici', 'Ankara Merkez', 'Ankara', '7887448272'),
(5, 'khalid ', 'noorullah', 'khalid@gmail.com', '$2y$10$w1xuTX3XolcZFgdlNUgO/Ok8XTccBxiEcUTcFTzw.JiQD5wFENvlW', 'musteri', 'alnasim', 'jeddah', '0551107316'),
(6, 'Emir', 'Yilmaz', 'emiryilmaz.musteri@gmail.com', '$2y$10$LV45yxqKQMLIft7hyqohIekXu5eCzX8Cr7wlR1MDUGEeTEoe71qJ.', 'musteri', 'zonguldak merkez', 'zonguldak', '8574820402'),
(7, 'Aylin', 'Demir', 'aylindemir.musteri@gmail.com', '$2y$10$cZMIqdr.EWMyc.wyNaVa.u4G0nkpkIwcyLQC6BknqpuDnORPQNixy', 'musteri', 'Bartin merkez', 'Bartin', '8492494857'),
(8, 'Kerem ', 'Şahin', 'kerem.musteri@gmail.com', '$2y$10$TWMtSlpmhyHA3udwUS/8pOiUPP0Q4dp2u.KGO5OKNt0qcs7iqioX.', 'musteri', 'Ankara koy', 'Ankara', '8574420402'),
(9, 'Selin', 'Kaya', 'selin.musteri@gmail.com', '$2y$10$LsYuObUbRyMaqY6bvXHeI./dufixr.oRa5IMvlVAl/hpLIJsmLDHy', 'musteri', 'konya merkez ', 'Konya', '8492494845'),
(10, 'Baris', 'Öztürk', 'baris.musteri@gmail.com', '$2y$10$x3BATPtV9s4/cRF4pAwaLOEGmtfn.UbGhTSTjSVqNOf5Du3MzsWBy', 'musteri', 'izmir koyda', 'izmir', '6782037849'),
(12, 'Merve', 'Aydin', 'merve.musteri@gmail.com', '$2y$10$0GhnA3zNCZK3V7m2zQjD5O8HA38NM.Xb13gYQxZ5V6ZAYYXBC8B/a', 'musteri', 'Konya yali park', 'Konya', '6748392034'),
(13, 'Caner', 'Kılıç', 'caner.musteri@gmail.com', '$2y$10$1xQ9N.NY29fxrUjOztsTl.8Y8jWw60pwWhVTzkiogI618bxqk8O6C', 'musteri', 'bartin toki', 'Bartin', '7589345602'),
(14, 'Derya', 'Aksoy', 'derya.musteri@gmail.com', '$2y$10$tdPBraoXT.kXHHx15FA0Du73xZZLkj84o9hNbbCqyuBIl5dsUJI3.', 'musteri', 'zonguldak toki', 'zonguldak', '7538202838'),
(15, 'Ozan ', 'Çelik', 'ozan.musteri@gmail.com', '$2y$10$jcZ6y2gzHK5T5At5fI3W7e3V5rSdAXD8YsiHleduAfA1fI9XcTOV2', 'musteri', 'Gazientep merkez', 'Gazientep', '5738920493'),
(16, 'Esra', ' Karaca', 'esra.musteri@gmail.com', '$2y$10$jvDS5tKowsSsc6GRFeTJoeJet855QP3kYi.i8b2nwUoL4IPfmg2JW', 'musteri', 'ankara merkez', 'Ankara', '5637929949'),
(17, 'Ahmet ', 'Yılmaz', 'ahmet.kiralayan@gmail.com', '$2y$10$Gx4V4K.J6hsmMA4OdsFSVOdKpWVSwEs5nJeZQt1gjKw51Uv5RNED6', 'satici', 'zonguldak', 'zonguldak', '57389340'),
(18, 'Demir', 'Demir', 'elif.kiralayan@gmail.com', '$2y$10$eCr4jGtVawY4Govo1uEQQ.sVd44i7Xoa1zk01oVTh657y4O4NF8ae', 'satici', 'Bartin', 'Bartin', '5738940028'),
(19, 'Murat ', 'Kaya', 'murat.kiralayan@gmail.com', '$2y$10$0W4u6zYI8474oCOba78U3OQ4dKdZc2HsROxIhdMlrEuN/66vN/XWC', 'satici', 'Ankara', 'Ankara', '47484939234'),
(20, 'Zeynep ', 'Çelik', 'zeynep.kiralayan@gmail.com', '$2y$10$.ss3yJJDfJ0TSzyxK4LjK.moQffjswwADikqLYZh9GN0EAP7XOjfS', 'satici', 'konya', 'Konya', '57585393940'),
(21, 'Ali ', 'Şahin', 'ali.kiralayan@gmail.com', '$2y$10$C9CuzHXPBR5v.DAKAxpTTOSbyg0iGjS.IFkSuioNEek8dl9b8wdeG', 'satici', 'izmir', 'izmir', '4849559966'),
(22, 'benim', 'senin', 'benimadim@gmail.com', '$2y$10$r9igHUv7CamnpSUQ0lSpkOItmwV7fVSSVEgJat3/l8HeAQUiDVwly', 'musteri', 'bartin', 'Bartin', '60234567676');

-- --------------------------------------------------------

--
-- Table structure for table `musteri`
--

CREATE TABLE `musteri` (
  `id` int(11) NOT NULL,
  `ad` varchar(100) NOT NULL,
  `soyad` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `sifre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `van_id` int(11) NOT NULL,
  `satici_id` int(11) NOT NULL,
  `musteri_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `talep_iptal`
--

CREATE TABLE `talep_iptal` (
  `iptal_id` int(11) NOT NULL,
  `kiralik_id` int(11) NOT NULL,
  `musteri_id` int(11) NOT NULL,
  `neden` varchar(255) DEFAULT NULL,
  `diger_aciklama` text DEFAULT NULL,
  `iptal_tarihi` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `talep_iptal`
--

INSERT INTO `talep_iptal` (`iptal_id`, `kiralik_id`, `musteri_id`, `neden`, `diger_aciklama`, `iptal_tarihi`) VALUES
(8, 21, 16, 'plan_degisti', '', '2025-05-31 15:04:28'),
(9, 25, 5, 'plan_degisti', '', '2025-06-02 14:47:26'),
(10, 26, 5, 'fiyat_yuksek', '', '2025-06-03 15:05:57'),
(11, 27, 22, 'fiyat_yuksek', '', '2025-06-04 14:47:24'),
(12, 28, 7, 'plan_degisti', '', '2025-06-12 05:23:13');

-- --------------------------------------------------------

--
-- Table structure for table `vans`
--

CREATE TABLE `vans` (
  `van_id` int(11) NOT NULL,
  `satici_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `marka` varchar(50) DEFAULT NULL,
  `model` varchar(50) NOT NULL,
  `kira_fiyat` decimal(10,2) NOT NULL,
  `plate` varchar(20) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_sold` tinyint(1) DEFAULT 0,
  `ilan_numara` varchar(50) NOT NULL,
  `yil` varchar(20) NOT NULL,
  `motor_gucu` varchar(50) NOT NULL,
  `renk` varchar(50) NOT NULL,
  `vites` enum('manuel','otomatik') NOT NULL,
  `yakit` enum('petrol','dizel') NOT NULL,
  `durum` enum('bosta','kirada') NOT NULL DEFAULT 'bosta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vans`
--

INSERT INTO `vans` (`van_id`, `satici_id`, `title`, `marka`, `model`, `kira_fiyat`, `plate`, `image_path`, `is_sold`, `ilan_numara`, `yil`, `motor_gucu`, `renk`, `vites`, `yakit`, `durum`) VALUES
(30, 17, 'YENI Mercedes', 'Mercedes', 'Sprinter', 2500.00, 'DIDKO345', NULL, 0, '', '2014', '303030', 'Beyaz', 'otomatik', 'petrol', 'bosta'),
(31, 17, 'Ford', 'Ford', 'Transpoter', 2999.97, '98-83-mz', NULL, 0, '', '2020', '45000', 'SIYAH', 'otomatik', 'petrol', ''),
(32, 18, 'VOLKSWAGEN', 'volkswagen', 'Transit', 1500.00, '74-HXC-89', NULL, 0, '', '2016', '45000', 'WHITE', 'otomatik', 'petrol', 'bosta'),
(33, 19, 'Mercedes Sprinter', 'Mercedes', 'Sprinter', 5400.00, '90-JKH-8J', NULL, 0, '', '2020', '78299393', 'siyah', 'otomatik', 'petrol', 'bosta'),
(34, 19, 'NISAN-2025', 'Nissan', 'NV350', 3400.00, 'DI-89-D45', NULL, 0, '', '2025', '4900', 'beyaz', 'otomatik', 'dizel', 'bosta'),
(36, 20, 'Toyota-2021', 'Toyota', 'Transporter', 2399.87, '98-83-mz', NULL, 0, '', '2020', '45000', 'beyaz', 'otomatik', 'petrol', 'kirada'),
(37, 20, 'Mercedes Benz', 'Mercedes', 'Transit', 4500.00, '89-GHV-HJ9', NULL, 0, '', '2014', '45000', 'GRI', 'manuel', 'petrol', 'kirada'),
(38, 20, 'Ford', 'Ford', 'Transporter', 2300.00, '98-83K-mz', NULL, 0, '', '2019', '303030', 'WHITE', 'otomatik', 'dizel', 'bosta'),
(39, 21, 'Volkswagen', 'Volkswagen', 'Sprinter', 4000.00, '98-DI-D45', NULL, 0, '', '2020', '303030', 'SIYAH', 'otomatik', 'petrol', 'bosta'),
(40, 21, 'Mercedes Benz', 'Mercedes', 'Sprinter', 3499.98, '78-G=GJH-HJ0', NULL, 0, '', '2025', '45000', 'beyaz', 'otomatik', 'petrol', 'kirada');

-- --------------------------------------------------------

--
-- Table structure for table `van_images`
--

CREATE TABLE `van_images` (
  `id` int(11) NOT NULL,
  `van_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `van_images`
--

INSERT INTO `van_images` (`id`, `van_id`, `image_path`, `is_primary`) VALUES
(46, 30, 'uploads/vans/van_30_683b03e4e7ff4.png', 1),
(47, 30, 'uploads/vans/van_30_683b03e4ea174.png', 0),
(48, 30, 'uploads/vans/van_30_683b03e4eadc2.png', 0),
(49, 31, 'uploads/vans/van_31_683b04bb40171.png', 1),
(50, 31, 'uploads/vans/van_31_683b04bb417c8.png', 0),
(51, 31, 'uploads/vans/Screenshot 2025-05-31 142820.png', 0),
(52, 32, 'uploads/vans/van_32_683b067726df2.png', 1),
(53, 32, 'uploads/vans/van_32_683b067728792.png', 0),
(54, 32, 'uploads/vans/van_32_683b067728d9b.png', 0),
(55, 33, 'uploads/vans/van_33_683b07756e638.png', 1),
(56, 33, 'uploads/vans/van_33_683b07756ed76.png', 0),
(57, 34, 'uploads/vans/van_34_683b082fca1a2.png', 1),
(58, 34, 'uploads/vans/van_34_683b082fcbbd5.png', 0),
(59, 34, 'uploads/vans/van_34_683b082fcc3ee.png', 0),
(66, 36, 'uploads/vans/van_36_683b0baa941e9.png', 1),
(67, 36, 'uploads/vans/van_36_683b0baa945d6.png', 0),
(69, 37, 'uploads/vans/van_37_683b0c934bcb9.png', 1),
(70, 37, 'uploads/vans/van_37_683b0c934d72d.png', 0),
(71, 37, 'uploads/vans/van_37_683b0c934da11.png', 0),
(73, 38, 'uploads/vans/van_38_683b0d3168bdf.png', 0),
(74, 38, 'uploads/vans/van_38_683b0d3168e8f.png', 0),
(75, 39, 'uploads/vans/van_39_683b0faca6472.png', 1),
(76, 39, 'uploads/vans/van_39_683b0faca7895.png', 0),
(77, 39, 'uploads/vans/van_39_683b0faca7ca1.png', 0),
(78, 40, 'uploads/vans/van_40_683b1010ad73e.png', 1),
(79, 40, 'uploads/vans/van_40_683b1010adb5d.png', 0),
(80, 40, 'uploads/vans/van_40_683b1010af024.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `yorumlar`
--

CREATE TABLE `yorumlar` (
  `yorum_id` char(36) NOT NULL DEFAULT uuid(),
  `kullanici_id` int(11) NOT NULL,
  `van_id` int(11) NOT NULL,
  `yorum_metni` text NOT NULL,
  `olusturma_tarihi` timestamp NOT NULL DEFAULT current_timestamp(),
  `rating` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `yorumlar`
--

INSERT INTO `yorumlar` (`yorum_id`, `kullanici_id`, `van_id`, `yorum_metni`, `olusturma_tarihi`, `rating`) VALUES
('0a474fe4-3e30-11f0-be51-b10f37e4d57f', 15, 30, 'iyi', '2025-05-31 15:00:50', 3),
('16646044-3e2d-11f0-be51-b10f37e4d57f', 14, 32, 'Çok memnun kaldım! Araba temizdi, teslimat hızlı ve pratikti', '2025-05-31 14:39:41', 5),
('1c1f8038-3e30-11f0-be51-b10f37e4d57f', 15, 36, 'Vanın içi çok geniş ve rahat. Yolda çok iyi performans sergiledi, kesinlikle yeniden kiralayacağım.', '2025-05-31 15:01:19', 5),
('2c2ae468-3e2e-11f0-be51-b10f37e4d57f', 8, 39, 'Harika bir kiralama deneyimi, her şey mükemmeldi. Çok rahat ettik, teşekkürler!\r\n\r\n', '2025-05-31 14:47:27', 5),
('3a911e4c-3e2d-11f0-be51-b10f37e4d57f', 14, 31, 'Yolculuk sırasında hiçbir problemle karşılaşmadık. Çok memnun kaldık.\r\n\r\n', '2025-05-31 14:40:42', 4),
('47e42237-3e30-11f0-be51-b10f37e4d57f', 16, 36, 'Bu fiyata bu kadar kaliteli bir van kiralamak büyük şans! Hem konforlu hem de güvenliydi', '2025-05-31 15:02:33', 5),
('60c1e3f9-3e2e-11f0-be51-b10f37e4d57f', 9, 39, 'Kesinlikle mükemmel bir kiralama deneyimi! Van çok rahat, satıcı ise çok yardımcı oldu', '2025-05-31 14:48:56', 5),
('650955bb-3e30-11f0-be51-b10f37e4d57f', 16, 33, 'guzeldir', '2025-05-31 15:03:22', 4),
('829b5031-3e2c-11f0-be51-b10f37e4d57f', 10, 40, 'Van harika, yolculuğumuz çok rahat geçti. Kesinlikle tavsiye ederim!', '2025-05-31 14:35:33', 5),
('833c2de4-3e2e-11f0-be51-b10f37e4d57f', 12, 39, 'Van harika bir şekilde temizlenmiş ve her şey yolunda. Satıcı da çok ilgiliydi.', '2025-05-31 14:49:53', 5),
('9f604c09-3e2c-11f0-be51-b10f37e4d57f', 10, 31, 'Çok memnun kaldım! Araba temizdi, teslimat hızlı ve pratikti.', '2025-05-31 14:36:22', 5),
('ab2a455d-3e2f-11f0-be51-b10f37e4d57f', 14, 36, 'Van çok iyi durumda, her şey düşünülmüş. Yolda keyifli bir deneyim yaşadık.\r\n\r\n', '2025-05-31 14:58:10', 5),
('af2d67c3-3e2e-11f0-be51-b10f37e4d57f', 13, 39, 'Bu kadar konforlu ve geniş bir van beklemiyordum, çok beğendim. Güvenle kiralayabilirsiniz!', '2025-05-31 14:51:07', 5),
('b054be39-3e2c-11f0-be51-b10f37e4d57f', 10, 32, 'Satıcı çok yardımcıydı, van temiz ve konforluydu. Teşekkürler!', '2025-05-31 14:36:50', 3),
('bc9d2817-3e2f-11f0-be51-b10f37e4d57f', 14, 40, 'iyi', '2025-05-31 14:58:39', 5),
('c8060e00-3f23-11f0-ba70-ca4c1ba3e387', 5, 34, 'iyi\r\n', '2025-06-01 20:05:36', 4),
('c9518a26-3e30-11f0-be51-b10f37e4d57f', 5, 33, 'Kesinlikle tavsiye ediyorum. Van geniş, ferah ve oldukça kullanışlı', '2025-05-31 15:06:10', 4),
('d3cb1e88-3e2c-11f0-be51-b10f37e4d57f', 10, 38, 'Çok memnun kaldım! Araba temizdi, teslimat hızlı ve pratikti', '2025-05-31 14:37:50', 2),
('dce9395e-3e2f-11f0-be51-b10f37e4d57f', 14, 34, 'Van eskiydi ve pek konforlu değildi. Yolda biraz zorlandık.\r\n\r\n', '2025-05-31 14:59:33', 1),
('f3605a6c-414f-11f0-b99d-121d5c763bc5', 5, 36, 'iyidir', '2025-06-04 14:26:49', 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `begeni`
--
ALTER TABLE `begeni`
  ADD PRIMARY KEY (`begeni_id`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `van_id` (`van_id`);

--
-- Indexes for table `destek_talepleri`
--
ALTER TABLE `destek_talepleri`
  ADD PRIMARY KEY (`talep_id`),
  ADD KEY `musteri_id` (`musteri_id`),
  ADD KEY `kiralik_id` (`kiralik_id`),
  ADD KEY `van_id` (`van_id`);

--
-- Indexes for table `kiralik`
--
ALTER TABLE `kiralik`
  ADD PRIMARY KEY (`kiralik_id`),
  ADD KEY `van_id` (`van_id`),
  ADD KEY `satici_id` (`satici_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Indexes for table `kullanici`
--
ALTER TABLE `kullanici`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `musteri`
--
ALTER TABLE `musteri`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `van_id` (`van_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Indexes for table `talep_iptal`
--
ALTER TABLE `talep_iptal`
  ADD PRIMARY KEY (`iptal_id`),
  ADD KEY `kiralik_id` (`kiralik_id`),
  ADD KEY `musteri_id` (`musteri_id`);

--
-- Indexes for table `vans`
--
ALTER TABLE `vans`
  ADD PRIMARY KEY (`van_id`),
  ADD KEY `satici_id` (`satici_id`);

--
-- Indexes for table `van_images`
--
ALTER TABLE `van_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `van_id` (`van_id`);

--
-- Indexes for table `yorumlar`
--
ALTER TABLE `yorumlar`
  ADD PRIMARY KEY (`yorum_id`),
  ADD KEY `kullanici_id` (`kullanici_id`),
  ADD KEY `yorumlar_ibfk_2` (`van_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `destek_talepleri`
--
ALTER TABLE `destek_talepleri`
  MODIFY `talep_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kiralik`
--
ALTER TABLE `kiralik`
  MODIFY `kiralik_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `kullanici`
--
ALTER TABLE `kullanici`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `musteri`
--
ALTER TABLE `musteri`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `talep_iptal`
--
ALTER TABLE `talep_iptal`
  MODIFY `iptal_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `vans`
--
ALTER TABLE `vans`
  MODIFY `van_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `van_images`
--
ALTER TABLE `van_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `begeni`
--
ALTER TABLE `begeni`
  ADD CONSTRAINT `begeni_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanici` (`id`),
  ADD CONSTRAINT `begeni_ibfk_2` FOREIGN KEY (`van_id`) REFERENCES `vans` (`van_id`) ON DELETE CASCADE;

--
-- Constraints for table `destek_talepleri`
--
ALTER TABLE `destek_talepleri`
  ADD CONSTRAINT `destek_talepleri_ibfk_1` FOREIGN KEY (`musteri_id`) REFERENCES `kullanici` (`id`),
  ADD CONSTRAINT `destek_talepleri_ibfk_2` FOREIGN KEY (`kiralik_id`) REFERENCES `kiralik` (`kiralik_id`),
  ADD CONSTRAINT `destek_talepleri_ibfk_3` FOREIGN KEY (`van_id`) REFERENCES `vans` (`van_id`);

--
-- Constraints for table `kiralik`
--
ALTER TABLE `kiralik`
  ADD CONSTRAINT `kiralik_ibfk_1` FOREIGN KEY (`van_id`) REFERENCES `vans` (`van_id`),
  ADD CONSTRAINT `kiralik_ibfk_2` FOREIGN KEY (`satici_id`) REFERENCES `kullanici` (`id`),
  ADD CONSTRAINT `kiralik_ibfk_3` FOREIGN KEY (`musteri_id`) REFERENCES `kullanici` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`van_id`) REFERENCES `vans` (`van_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`musteri_id`) REFERENCES `musteri` (`id`);

--
-- Constraints for table `talep_iptal`
--
ALTER TABLE `talep_iptal`
  ADD CONSTRAINT `talep_iptal_ibfk_1` FOREIGN KEY (`kiralik_id`) REFERENCES `kiralik` (`kiralik_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `talep_iptal_ibfk_2` FOREIGN KEY (`musteri_id`) REFERENCES `kullanici` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vans`
--
ALTER TABLE `vans`
  ADD CONSTRAINT `vans_ibfk_1` FOREIGN KEY (`satici_id`) REFERENCES `kullanici` (`id`);

--
-- Constraints for table `van_images`
--
ALTER TABLE `van_images`
  ADD CONSTRAINT `van_images_ibfk_1` FOREIGN KEY (`van_id`) REFERENCES `vans` (`van_id`) ON DELETE CASCADE;

--
-- Constraints for table `yorumlar`
--
ALTER TABLE `yorumlar`
  ADD CONSTRAINT `yorumlar_ibfk_1` FOREIGN KEY (`kullanici_id`) REFERENCES `kullanici` (`id`),
  ADD CONSTRAINT `yorumlar_ibfk_2` FOREIGN KEY (`van_id`) REFERENCES `vans` (`van_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
