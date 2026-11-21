-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2026 at 09:51 AM
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
-- Database: `db_siacad_smk`
--

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `jenis_kelamin`, `user_id`) VALUES
(1, '198501012010011001', 'Budi Santoso, M.Kom', 'L', 2),
(2, '198803152012012002', 'Siti Rahma, S.Pd', 'P', 3),
(3, '199005202015011003', 'Ahmad Rizki, S.T', 'L', 4),
(4, '199208102018012004', 'Dewi Lestari, M.Pd', 'P', 5),
(5, '199512012020011005', 'Eko Prasetyo, S.Kom', 'L', 6),
(7, '12345678945', 'Budiman Sutono', 'L', 25),
(9, '12345678910', 'Vano Wijaya', 'L', 27);

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id` int(11) NOT NULL,
  `kode_mapel` varchar(10) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `guru_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id`, `kode_mapel`, `nama_mapel`, `guru_id`) VALUES
(1, 'MP001', 'Pemrograman Web dan Perangkat Bergerak', 1),
(2, 'MP002', 'Pemodelan Perangkat Lunak', 2),
(3, 'MP003', 'Basis Data', 3),
(4, 'MP004', 'Pemrograman Berorientasi Objek', 4),
(5, 'MP005', 'PKKWU', 5),
(7, 'MP007', 'Basis Data', 7),
(8, 'MP006', 'Pemodelan Peranglkat Lunak', 9);

-- --------------------------------------------------------

--
-- Table structure for table `mengajar`
--

CREATE TABLE `mengajar` (
  `id` int(10) NOT NULL,
  `mapel_id` int(10) NOT NULL,
  `siswa_id` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mengajar`
--

INSERT INTO `mengajar` (`id`, `mapel_id`, `siswa_id`) VALUES
(1, 1, 1),
(2, 2, 2),
(3, 3, 3),
(4, 4, 4),
(5, 5, 5),
(6, 3, 10),
(7, 3, 11),
(8, 3, 12),
(9, 1, 10),
(10, 1, 11),
(11, 1, 12),
(12, 7, 10),
(13, 7, 11),
(14, 7, 12),
(15, 8, 10),
(16, 8, 11),
(17, 8, 12),
(18, 7, 1),
(19, 7, 3);

-- --------------------------------------------------------

--
-- Table structure for table `nilai_rapor`
--

CREATE TABLE `nilai_rapor` (
  `id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `nilai_kkm` int(11) DEFAULT 75,
  `nilai_pengetahuan` int(11) DEFAULT NULL,
  `nilai_keterampilan` int(11) DEFAULT NULL,
  `Keterangan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nilai_rapor`
--

INSERT INTO `nilai_rapor` (`id`, `siswa_id`, `mapel_id`, `nilai_kkm`, `nilai_pengetahuan`, `nilai_keterampilan`, `Keterangan`) VALUES
(1, 1, 1, 75, 85, 90, 'Tuntas'),
(3, 3, 3, 75, 90, 88, 'Tuntas'),
(4, 4, 4, 75, 70, 75, 'Tidak Tuntas'),
(5, 5, 5, 75, 88, 85, 'Tuntas'),
(7, 10, 1, 75, 80, 90, 'Tuntas'),
(8, 10, 2, 75, 85, 88, 'Tuntas'),
(9, 11, 1, 75, 88, 90, 'Tuntas'),
(10, 11, 2, 75, 85, 90, 'Tuntas'),
(11, 12, 1, 75, 88, 90, 'Tuntas'),
(12, 12, 2, 75, 87, 90, 'Tuntas'),
(15, 12, 7, 75, 85, 88, 'Tuntas'),
(16, 10, 7, 75, 81, 90, 'Tuntas'),
(19, 1, 7, 75, 80, 85, 'Lulus');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(10) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nama`, `kelas`, `jenis_kelamin`, `user_id`) VALUES
(1, '2223001', 'Leo Wijaya', 'XI RPL 2', 'L', 7),
(3, '2223003', 'Candra Gunawan', 'XI RPL 2', 'L', 9),
(4, '2223004', 'Dina Maya', 'XI RPL 3', 'P', 10),
(5, '2223005', 'Eka Putra', 'XI RPL 4', 'L', 11),
(10, '1234567891', 'Salsa Cantika', 'XI RPL 1', 'P', 22),
(11, '1234567889', 'Mohan Alveric', 'XI RPL 1', 'L', 23),
(12, '123456710', 'Nicole Rossi', 'XI RPL 1', 'P', 24);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin01', 'hash_pass_1', 'admin', '2026-08-23 03:07:35'),
(2, 'guru_budi', '$2y$10$hashpassguru1', 'guru', '2026-08-23 03:12:14'),
(3, 'guru_siti', '$2y$10$hashpassguru2', 'guru', '2026-08-23 03:12:14'),
(4, 'guru_ahmad', '$2y$10$OQ4hSXr9J/uhWDnwbMTfY.sdHTq5yqGUlTUAEkTG12rS4UyjlnG9O', 'guru', '2026-08-23 03:12:14'),
(5, 'guru_dewi', '$2y$10$hashpassguru4', 'guru', '2026-08-23 03:12:14'),
(6, 'guru_eko', '$2y$10$hashpassguru5', 'guru', '2026-08-23 03:12:14'),
(7, 'siswa_andi', '$2y$10$hashpasssiswa1', 'siswa', '2026-08-23 03:12:14'),
(9, 'siswa_candra', '$2y$10$hashpasssiswa3', 'siswa', '2026-08-23 03:12:14'),
(10, 'siswa_dina', '$2y$10$hashpasssiswa4', 'siswa', '2026-08-23 03:12:14'),
(11, 'siswa_eka', '$2y$10$hashpasssiswa5', 'siswa', '2026-08-23 03:12:14'),
(18, 'liaa34', '$2y$10$6n3oCfg3oYa6SvZcSYGvYuHYb0BleDULTiNLBi/e2xXa4BQyQsLTG', 'admin', '2026-10-21 07:01:57'),
(22, '123salsa', '$2y$10$gj5wAzVtX0WuoiMmNm13KuQRd2XN1ajJ0kU3F.lalFfMAvn1C1/vO', 'siswa', '2026-10-23 13:17:32'),
(23, '12mohan', '$2y$10$y4jGRYegBmk1dUr8ZkUwcOUub0OVxsTWcoaO7LlecDPrIChznUowu', 'siswa', '2026-10-23 13:19:53'),
(24, 'nicolee54', '$2y$10$QB0ZgSVvQoYpo/xEIyPkT.OSXyn/OtUANLptMYLc7dCsvWhxXptd.', 'siswa', '2026-10-23 13:46:48'),
(25, 'budiman12', '$2y$10$66Jz6Ec6JKokY5FfrCfKZuM4fRBQ9t89SXsiFAHfSlyLYc6DE9qx.', 'guru', '2026-10-25 12:11:40'),
(27, 'Vano32', '$2y$10$0iD.GQ.7c0lWYM1D8A/Bp.w49b7gaAEruEB6nVYacbTfj/Jpe7YTa', 'guru', '2026-10-29 04:46:10'),
(35, 'Miya45', '$2y$10$iWfDA.H52/0AuPGQG4o7Auep9GM0S.WTzv3Fqv8Udthpl.LEJz1ha', 'admin', '2026-11-18 00:19:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_mapel` (`kode_mapel`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indexes for table `mengajar`
--
ALTER TABLE `mengajar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `siswa_id` (`siswa_id`),
  ADD KEY `mapel_id` (`mapel_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `mengajar`
--
ALTER TABLE `mengajar`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mapel`
--
ALTER TABLE `mapel`
  ADD CONSTRAINT `mapel_ibfk_1` FOREIGN KEY (`guru_id`) REFERENCES `guru` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `nilai_rapor`
--
ALTER TABLE `nilai_rapor`
  ADD CONSTRAINT `nilai_rapor_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `nilai_rapor_ibfk_2` FOREIGN KEY (`mapel_id`) REFERENCES `mapel` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
