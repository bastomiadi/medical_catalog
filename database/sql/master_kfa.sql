-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 06, 2026 at 02:32 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `master_kfa`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_ingredients`
--

CREATE TABLE `active_ingredients` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `zat_aktif` text DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `kekuatan_zat_aktif` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dosage_forms`
--

CREATE TABLE `dosage_forms` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `farmalkes_types`
--

CREATE TABLE `farmalkes_types` (
  `id` int(11) NOT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `group` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fornas`
--

CREATE TABLE `fornas` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `is_fornas` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `klasifikasi_izins`
--

CREATE TABLE `klasifikasi_izins` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `klasifikasi_izin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `med_devs`
--

CREATE TABLE `med_devs` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `jenis` varchar(255) DEFAULT NULL,
  `subkategori` varchar(255) DEFAULT NULL,
  `kategori` varchar(255) DEFAULT NULL,
  `kelas_risiko` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `paket_obats`
--

CREATE TABLE `paket_obats` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `uom_name` varchar(50) DEFAULT NULL,
  `ucum_cs_code` varchar(50) DEFAULT NULL,
  `ucum_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) NOT NULL,
  `name` text DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `produksi_buatan` varchar(50) DEFAULT NULL,
  `nie` varchar(100) DEFAULT NULL,
  `nama_dagang` text DEFAULT NULL,
  `manufacturer` text DEFAULT NULL,
  `registrar` text DEFAULT NULL,
  `generik` tinyint(1) DEFAULT NULL,
  `rxterm` int(11) DEFAULT NULL,
  `dose_per_unit` int(11) DEFAULT NULL,
  `fix_price` bigint(20) DEFAULT NULL,
  `het_price` bigint(20) DEFAULT NULL,
  `farmalkes_hscode` varchar(50) DEFAULT NULL,
  `tayang_lkpp` tinyint(1) DEFAULT NULL,
  `kode_lkpp` varchar(255) DEFAULT NULL,
  `net_weight` decimal(15,5) DEFAULT NULL,
  `net_weight_uom_name` varchar(50) DEFAULT NULL,
  `volume` decimal(15,5) DEFAULT NULL,
  `volume_uom_name` varchar(50) DEFAULT NULL,
  `uom_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_dosage_forms`
--

CREATE TABLE `product_dosage_forms` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `dosage_form_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_farmalkes`
--

CREATE TABLE `product_farmalkes` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `farmalkes_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_fornas`
--

CREATE TABLE `product_fornas` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `fornas_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_klasifikasi_izins`
--

CREATE TABLE `product_klasifikasi_izins` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `klasifikasi_izin_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_med_devs`
--

CREATE TABLE `product_med_devs` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `med_dev_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_templates`
--

CREATE TABLE `product_templates` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `name` text DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `bmhp` tinyint(1) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `display_name` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_templates_link`
--

CREATE TABLE `product_templates_link` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `product_template_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_uoms`
--

CREATE TABLE `product_uoms` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `uom_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `replacements`
--

CREATE TABLE `replacements` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `product_kfa_code` varchar(50) DEFAULT NULL,
  `product_reason` text DEFAULT NULL,
  `template_kfa_code` varchar(50) DEFAULT NULL,
  `template_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `id` int(11) NOT NULL,
  `kfa_code` varchar(50) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uoms`
--

CREATE TABLE `uoms` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_ingredients`
--
ALTER TABLE `active_ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kfa_code` (`kfa_code`);

--
-- Indexes for table `dosage_forms`
--
ALTER TABLE `dosage_forms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `farmalkes_types`
--
ALTER TABLE `farmalkes_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `fornas`
--
ALTER TABLE `fornas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kfa_code` (`kfa_code`);

--
-- Indexes for table `klasifikasi_izins`
--
ALTER TABLE `klasifikasi_izins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kfa_code` (`kfa_code`);

--
-- Indexes for table `med_devs`
--
ALTER TABLE `med_devs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kfa_code` (`kfa_code`);

--
-- Indexes for table `paket_obats`
--
ALTER TABLE `paket_obats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kfa_code` (`kfa_code`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kfa_code` (`kfa_code`),
  ADD KEY `idx_kfa_code` (`kfa_code`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_state` (`state`);

--
-- Indexes for table `product_dosage_forms`
--
ALTER TABLE `product_dosage_forms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kfa_code` (`kfa_code`),
  ADD KEY `dosage_form_id` (`dosage_form_id`);

--
-- Indexes for table `product_farmalkes`
--
ALTER TABLE `product_farmalkes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kfa_code` (`kfa_code`),
  ADD KEY `farmalkes_type_id` (`farmalkes_type_id`);

--
-- Indexes for table `product_fornas`
--
ALTER TABLE `product_fornas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kfa_code` (`kfa_code`),
  ADD KEY `fornas_id` (`fornas_id`);

--
-- Indexes for table `product_klasifikasi_izins`
--
ALTER TABLE `product_klasifikasi_izins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kfa_code` (`kfa_code`),
  ADD KEY `klasifikasi_izin_id` (`klasifikasi_izin_id`);

--
-- Indexes for table `product_med_devs`
--
ALTER TABLE `product_med_devs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kfa_code` (`kfa_code`),
  ADD KEY `med_dev_id` (`med_dev_id`);

--
-- Indexes for table `product_templates`
--
ALTER TABLE `product_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kfa_code` (`kfa_code`);

--
-- Indexes for table `product_templates_link`
--
ALTER TABLE `product_templates_link`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kfa_code` (`kfa_code`),
  ADD KEY `product_template_id` (`product_template_id`);

--
-- Indexes for table `product_uoms`
--
ALTER TABLE `product_uoms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kfa_code` (`kfa_code`),
  ADD KEY `uom_id` (`uom_id`);

--
-- Indexes for table `replacements`
--
ALTER TABLE `replacements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_kfa_code` (`kfa_code`);

--
-- Indexes for table `uoms`
--
ALTER TABLE `uoms`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_ingredients`
--
ALTER TABLE `active_ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dosage_forms`
--
ALTER TABLE `dosage_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `farmalkes_types`
--
ALTER TABLE `farmalkes_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fornas`
--
ALTER TABLE `fornas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `klasifikasi_izins`
--
ALTER TABLE `klasifikasi_izins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `med_devs`
--
ALTER TABLE `med_devs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paket_obats`
--
ALTER TABLE `paket_obats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_dosage_forms`
--
ALTER TABLE `product_dosage_forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_farmalkes`
--
ALTER TABLE `product_farmalkes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_fornas`
--
ALTER TABLE `product_fornas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_klasifikasi_izins`
--
ALTER TABLE `product_klasifikasi_izins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_med_devs`
--
ALTER TABLE `product_med_devs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_templates`
--
ALTER TABLE `product_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_templates_link`
--
ALTER TABLE `product_templates_link`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_uoms`
--
ALTER TABLE `product_uoms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `replacements`
--
ALTER TABLE `replacements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `uoms`
--
ALTER TABLE `uoms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product_dosage_forms`
--
ALTER TABLE `product_dosage_forms`
  ADD CONSTRAINT `product_dosage_forms_ibfk_1` FOREIGN KEY (`kfa_code`) REFERENCES `products` (`kfa_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_dosage_forms_ibfk_2` FOREIGN KEY (`dosage_form_id`) REFERENCES `dosage_forms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_farmalkes`
--
ALTER TABLE `product_farmalkes`
  ADD CONSTRAINT `product_farmalkes_ibfk_1` FOREIGN KEY (`kfa_code`) REFERENCES `products` (`kfa_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_farmalkes_ibfk_2` FOREIGN KEY (`farmalkes_type_id`) REFERENCES `farmalkes_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_fornas`
--
ALTER TABLE `product_fornas`
  ADD CONSTRAINT `product_fornas_ibfk_1` FOREIGN KEY (`kfa_code`) REFERENCES `products` (`kfa_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_fornas_ibfk_2` FOREIGN KEY (`fornas_id`) REFERENCES `fornas` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_klasifikasi_izins`
--
ALTER TABLE `product_klasifikasi_izins`
  ADD CONSTRAINT `product_klasifikasi_izins_ibfk_1` FOREIGN KEY (`kfa_code`) REFERENCES `products` (`kfa_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_klasifikasi_izins_ibfk_2` FOREIGN KEY (`klasifikasi_izin_id`) REFERENCES `klasifikasi_izins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_med_devs`
--
ALTER TABLE `product_med_devs`
  ADD CONSTRAINT `product_med_devs_ibfk_1` FOREIGN KEY (`kfa_code`) REFERENCES `products` (`kfa_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_med_devs_ibfk_2` FOREIGN KEY (`med_dev_id`) REFERENCES `med_devs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_templates_link`
--
ALTER TABLE `product_templates_link`
  ADD CONSTRAINT `product_templates_link_ibfk_1` FOREIGN KEY (`kfa_code`) REFERENCES `products` (`kfa_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_templates_link_ibfk_2` FOREIGN KEY (`product_template_id`) REFERENCES `product_templates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_uoms`
--
ALTER TABLE `product_uoms`
  ADD CONSTRAINT `product_uoms_ibfk_1` FOREIGN KEY (`kfa_code`) REFERENCES `products` (`kfa_code`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_uoms_ibfk_2` FOREIGN KEY (`uom_id`) REFERENCES `uoms` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
