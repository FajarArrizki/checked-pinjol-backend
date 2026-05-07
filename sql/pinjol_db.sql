-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Apr 30, 2026 at 03:15 AM
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
-- Database: `pinjol_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` bigint(20) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `nama`, `email`, `username`, `password_hash`, `role`, `no_hp`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin Utama', 'admin@mail.com', 'admin', '123456', 'superadmin', NULL, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `artikel_edukasi`
--

CREATE TABLE `artikel_edukasi` (
  `id_artikel` bigint(20) NOT NULL,
  `id_admin` bigint(20) DEFAULT NULL,
  `judul` varchar(255) DEFAULT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `isi_artikel` text DEFAULT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lampiran_laporan`
--

CREATE TABLE `lampiran_laporan` (
  `id_lampiran` bigint(20) NOT NULL,
  `id_laporan` bigint(20) DEFAULT NULL,
  `nama_file` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `tipe_file` varchar(50) DEFAULT NULL,
  `ukuran_file` int(11) DEFAULT NULL,
  `uploaded_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan`
--

CREATE TABLE `laporan` (
  `id_laporan` bigint(20) NOT NULL,
  `id_user` bigint(20) DEFAULT NULL,
  `kode_laporan` varchar(100) DEFAULT NULL,
  `judul_laporan` varchar(255) DEFAULT NULL,
  `isi_laporan` text DEFAULT NULL,
  `nama_pelapor` varchar(255) DEFAULT NULL,
  `kontak_pelapor` varchar(100) DEFAULT NULL,
  `email_pelapor` varchar(255) DEFAULT NULL,
  `tautan_aplikasi` varchar(255) DEFAULT NULL,
  `foto_bukti` varchar(255) DEFAULT NULL,
  `status_laporan` varchar(50) DEFAULT NULL,
  `tanggal_lapor` datetime DEFAULT NULL,
  `id_pinjol` bigint(20) DEFAULT NULL,
  `id_admin_penanggung_jawab` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `laporan_regulasi`
--

CREATE TABLE `laporan_regulasi` (
  `id_laporan_regulasi` bigint(20) NOT NULL,
  `id_laporan` bigint(20) DEFAULT NULL,
  `id_regulasi` bigint(20) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturan_admin`
--

CREATE TABLE `pengaturan_admin` (
  `id_pengaturan` bigint(20) NOT NULL,
  `id_admin` bigint(20) DEFAULT NULL,
  `email_alert_darurat` tinyint(1) DEFAULT NULL,
  `ringkasan_laporan` tinyint(1) DEFAULT NULL,
  `two_factor_enabled` tinyint(1) DEFAULT NULL,
  `last_password_changed_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pinjol`
--

CREATE TABLE `pinjol` (
  `id_pinjol` bigint(20) NOT NULL,
  `nama_pinjol` varchar(255) DEFAULT NULL,
  `tahun_berdiri` year(4) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `status_pinjol` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pinjol`
--

INSERT INTO `pinjol` (`id_pinjol`, `nama_pinjol`, `tahun_berdiri`, `alamat`, `website`, `status_pinjol`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Pinjol Aman', NULL, NULL, NULL, 'legal', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `regulasi_filter`
--

CREATE TABLE `regulasi_filter` (
  `id_regulasi` bigint(20) NOT NULL,
  `nama_kriteria` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `simulasi_pinjaman`
--

CREATE TABLE `simulasi_pinjaman` (
  `id_simulasi` bigint(20) NOT NULL,
  `id_user` bigint(20) DEFAULT NULL,
  `jumlah_pinjaman` decimal(15,2) DEFAULT NULL,
  `tenor_hari` int(11) DEFAULT NULL,
  `bunga_per_hari` decimal(5,2) DEFAULT NULL,
  `biaya_admin` decimal(10,2) DEFAULT NULL,
  `cicilan_per_bulan` decimal(15,2) DEFAULT NULL,
  `total_bayar` decimal(15,2) DEFAULT NULL,
  `apr_tahunan` decimal(5,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id_ulasan` bigint(20) NOT NULL,
  `id_user` bigint(20) DEFAULT NULL,
  `id_pinjol` bigint(20) DEFAULT NULL,
  `nama_pengulas` varchar(255) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `komentar` text DEFAULT NULL,
  `screenshot` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` bigint(20) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `no_hp` varchar(20) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nama`, `email`, `no_hp`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'user', 'user@mail.com', '08123456789', '123456', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `artikel_edukasi`
--
ALTER TABLE `artikel_edukasi`
  ADD PRIMARY KEY (`id_artikel`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `lampiran_laporan`
--
ALTER TABLE `lampiran_laporan`
  ADD PRIMARY KEY (`id_lampiran`),
  ADD KEY `id_laporan` (`id_laporan`);

--
-- Indexes for table `laporan`
--
ALTER TABLE `laporan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_pinjol` (`id_pinjol`),
  ADD KEY `id_admin_penanggung_jawab` (`id_admin_penanggung_jawab`);

--
-- Indexes for table `laporan_regulasi`
--
ALTER TABLE `laporan_regulasi`
  ADD PRIMARY KEY (`id_laporan_regulasi`),
  ADD KEY `id_laporan` (`id_laporan`),
  ADD KEY `id_regulasi` (`id_regulasi`);

--
-- Indexes for table `pengaturan_admin`
--
ALTER TABLE `pengaturan_admin`
  ADD PRIMARY KEY (`id_pengaturan`),
  ADD KEY `id_admin` (`id_admin`);

--
-- Indexes for table `pinjol`
--
ALTER TABLE `pinjol`
  ADD PRIMARY KEY (`id_pinjol`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `regulasi_filter`
--
ALTER TABLE `regulasi_filter`
  ADD PRIMARY KEY (`id_regulasi`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `simulasi_pinjaman`
--
ALTER TABLE `simulasi_pinjaman`
  ADD PRIMARY KEY (`id_simulasi`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id_ulasan`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_pinjol` (`id_pinjol`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `artikel_edukasi`
--
ALTER TABLE `artikel_edukasi`
  MODIFY `id_artikel` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lampiran_laporan`
--
ALTER TABLE `lampiran_laporan`
  MODIFY `id_lampiran` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan`
--
ALTER TABLE `laporan`
  MODIFY `id_laporan` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laporan_regulasi`
--
ALTER TABLE `laporan_regulasi`
  MODIFY `id_laporan_regulasi` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pengaturan_admin`
--
ALTER TABLE `pengaturan_admin`
  MODIFY `id_pengaturan` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pinjol`
--
ALTER TABLE `pinjol`
  MODIFY `id_pinjol` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `regulasi_filter`
--
ALTER TABLE `regulasi_filter`
  MODIFY `id_regulasi` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `simulasi_pinjaman`
--
ALTER TABLE `simulasi_pinjaman`
  MODIFY `id_simulasi` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id_ulasan` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `artikel_edukasi`
--
ALTER TABLE `artikel_edukasi`
  ADD CONSTRAINT `artikel_edukasi_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);

--
-- Constraints for table `lampiran_laporan`
--
ALTER TABLE `lampiran_laporan`
  ADD CONSTRAINT `lampiran_laporan_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`);

--
-- Constraints for table `laporan`
--
ALTER TABLE `laporan`
  ADD CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_pinjol`) REFERENCES `pinjol` (`id_pinjol`),
  ADD CONSTRAINT `laporan_ibfk_3` FOREIGN KEY (`id_admin_penanggung_jawab`) REFERENCES `admin` (`id_admin`);

--
-- Constraints for table `laporan_regulasi`
--
ALTER TABLE `laporan_regulasi`
  ADD CONSTRAINT `laporan_regulasi_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`),
  ADD CONSTRAINT `laporan_regulasi_ibfk_2` FOREIGN KEY (`id_regulasi`) REFERENCES `regulasi_filter` (`id_regulasi`);

--
-- Constraints for table `pengaturan_admin`
--
ALTER TABLE `pengaturan_admin`
  ADD CONSTRAINT `pengaturan_admin_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`);

--
-- Constraints for table `pinjol`
--
ALTER TABLE `pinjol`
  ADD CONSTRAINT `pinjol_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id_admin`);

--
-- Constraints for table `regulasi_filter`
--
ALTER TABLE `regulasi_filter`
  ADD CONSTRAINT `regulasi_filter_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id_admin`);

--
-- Constraints for table `simulasi_pinjaman`
--
ALTER TABLE `simulasi_pinjaman`
  ADD CONSTRAINT `simulasi_pinjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Constraints for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`id_pinjol`) REFERENCES `pinjol` (`id_pinjol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
