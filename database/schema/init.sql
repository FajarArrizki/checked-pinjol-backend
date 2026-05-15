-- ============================================================
-- Schema: pinjol_db
-- Checked Pinjol Backend
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

CREATE DATABASE IF NOT EXISTS `pinjol_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `pinjol_db`;

-- ────────────────────────────────────────────────────────────
-- admin
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `admin` (
  `id_admin`      BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `nama`          VARCHAR(255) DEFAULT NULL,
  `email`         VARCHAR(255) DEFAULT NULL,
  `username`      VARCHAR(100) DEFAULT NULL,
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `role`          VARCHAR(50)  DEFAULT NULL COMMENT 'admin | superadmin',
  `no_hp`         VARCHAR(20)  DEFAULT NULL,
  `is_active`     TINYINT(1)   DEFAULT 1,
  `created_at`    DATETIME     DEFAULT NULL,
  `updated_at`    DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_admin`),
  UNIQUE KEY `uq_admin_email`    (`email`),
  UNIQUE KEY `uq_admin_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- user
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `user` (
  `id_user`       BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `nama`          VARCHAR(255) DEFAULT NULL,
  `email`         VARCHAR(255) DEFAULT NULL,
  `no_hp`         VARCHAR(20)  DEFAULT NULL,
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `created_at`    DATETIME     DEFAULT NULL,
  `updated_at`    DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- pinjol
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pinjol` (
  `id_pinjol`     BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `nama_pinjol`   VARCHAR(255) DEFAULT NULL,
  `tahun_berdiri` YEAR(4)      DEFAULT NULL,
  `alamat`        TEXT         DEFAULT NULL,
  `website`       VARCHAR(255) DEFAULT NULL,
  `status_pinjol` VARCHAR(100) DEFAULT NULL COMMENT 'legal | ilegal | dalam_pengawasan',
  `created_by`    BIGINT(20)   DEFAULT NULL,
  `created_at`    DATETIME     DEFAULT NULL,
  `updated_at`    DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_pinjol`),
  KEY `fk_pinjol_admin` (`created_by`),
  CONSTRAINT `pinjol_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- regulasi_filter
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `regulasi_filter` (
  `id_regulasi`   BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `nama_kriteria` VARCHAR(255) DEFAULT NULL,
  `deskripsi`     TEXT         DEFAULT NULL,
  `is_active`     TINYINT(1)   DEFAULT 1,
  `created_by`    BIGINT(20)   DEFAULT NULL,
  `created_at`    DATETIME     DEFAULT NULL,
  `updated_at`    DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_regulasi`),
  KEY `fk_regulasi_admin` (`created_by`),
  CONSTRAINT `regulasi_filter_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- laporan
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `laporan` (
  `id_laporan`               BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `id_user`                  BIGINT(20)   DEFAULT NULL,
  `kode_laporan`             VARCHAR(100) DEFAULT NULL,
  `judul_laporan`            VARCHAR(255) DEFAULT NULL,
  `isi_laporan`              TEXT         DEFAULT NULL,
  `nama_pelapor`             VARCHAR(255) DEFAULT NULL,
  `kontak_pelapor`           VARCHAR(100) DEFAULT NULL,
  `email_pelapor`            VARCHAR(255) DEFAULT NULL,
  `tautan_aplikasi`          VARCHAR(255) DEFAULT NULL,
  `foto_bukti`               VARCHAR(255) DEFAULT NULL,
  `status_laporan`           VARCHAR(50)  DEFAULT 'menunggu' COMMENT 'menunggu | diproses | selesai | ditolak',
  `tanggal_lapor`            DATETIME     DEFAULT NULL,
  `id_pinjol`                BIGINT(20)   DEFAULT NULL,
  `id_admin_penanggung_jawab` BIGINT(20)  DEFAULT NULL,
  `created_at`               DATETIME     DEFAULT NULL,
  `updated_at`               DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_laporan`),
  UNIQUE KEY `uq_kode_laporan` (`kode_laporan`),
  KEY `fk_laporan_user`  (`id_user`),
  KEY `fk_laporan_pinjol`(`id_pinjol`),
  KEY `fk_laporan_admin` (`id_admin_penanggung_jawab`),
  CONSTRAINT `laporan_ibfk_1` FOREIGN KEY (`id_user`)  REFERENCES `user`  (`id_user`)  ON DELETE SET NULL,
  CONSTRAINT `laporan_ibfk_2` FOREIGN KEY (`id_pinjol`) REFERENCES `pinjol` (`id_pinjol`) ON DELETE SET NULL,
  CONSTRAINT `laporan_ibfk_3` FOREIGN KEY (`id_admin_penanggung_jawab`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- lampiran_laporan
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `lampiran_laporan` (
  `id_lampiran`  BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `id_laporan`   BIGINT(20)   DEFAULT NULL,
  `nama_file`    VARCHAR(255) DEFAULT NULL,
  `file_path`    VARCHAR(255) DEFAULT NULL,
  `tipe_file`    VARCHAR(50)  DEFAULT NULL,
  `ukuran_file`  INT(11)      DEFAULT NULL,
  `uploaded_at`  DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_lampiran`),
  KEY `fk_lampiran_laporan` (`id_laporan`),
  CONSTRAINT `lampiran_laporan_ibfk_1` FOREIGN KEY (`id_laporan`) REFERENCES `laporan` (`id_laporan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- laporan_regulasi
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `laporan_regulasi` (
  `id_laporan_regulasi` BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `id_laporan`          BIGINT(20)   DEFAULT NULL,
  `id_regulasi`         BIGINT(20)   DEFAULT NULL,
  `catatan`             VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id_laporan_regulasi`),
  KEY `fk_lapreg_laporan`  (`id_laporan`),
  KEY `fk_lapreg_regulasi` (`id_regulasi`),
  CONSTRAINT `laporan_regulasi_ibfk_1` FOREIGN KEY (`id_laporan`)  REFERENCES `laporan`         (`id_laporan`)  ON DELETE CASCADE,
  CONSTRAINT `laporan_regulasi_ibfk_2` FOREIGN KEY (`id_regulasi`) REFERENCES `regulasi_filter` (`id_regulasi`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- ulasan
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `ulasan` (
  `id_ulasan`     BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `id_user`       BIGINT(20)   DEFAULT NULL,
  `id_pinjol`     BIGINT(20)   DEFAULT NULL,
  `nama_pengulas` VARCHAR(255) DEFAULT NULL,
  `rating`        INT(11)      DEFAULT NULL COMMENT '1-5',
  `komentar`      TEXT         DEFAULT NULL,
  `screenshot`    VARCHAR(255) DEFAULT NULL,
  `created_at`    DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_ulasan`),
  KEY `fk_ulasan_user`   (`id_user`),
  KEY `fk_ulasan_pinjol` (`id_pinjol`),
  CONSTRAINT `ulasan_ibfk_1` FOREIGN KEY (`id_user`)   REFERENCES `user`   (`id_user`)   ON DELETE SET NULL,
  CONSTRAINT `ulasan_ibfk_2` FOREIGN KEY (`id_pinjol`) REFERENCES `pinjol` (`id_pinjol`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- artikel_edukasi
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `artikel_edukasi` (
  `id_artikel`  BIGINT(20)   NOT NULL AUTO_INCREMENT,
  `id_admin`    BIGINT(20)   DEFAULT NULL,
  `judul`       VARCHAR(255) DEFAULT NULL,
  `slug`        VARCHAR(255) DEFAULT NULL,
  `kategori`    VARCHAR(100) DEFAULT NULL,
  `author`      VARCHAR(255) DEFAULT NULL,
  `summary`     TEXT         DEFAULT NULL,
  `isi_artikel` TEXT         DEFAULT NULL,
  `gambar`      VARCHAR(255) DEFAULT NULL,
  `status`      VARCHAR(50)  DEFAULT 'draft' COMMENT 'draft | published | archived',
  `published_at` DATETIME    DEFAULT NULL,
  `created_at`  DATETIME     DEFAULT NULL,
  `updated_at`  DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id_artikel`),
  UNIQUE KEY `uq_artikel_slug` (`slug`),
  KEY `fk_artikel_admin` (`id_admin`),
  CONSTRAINT `artikel_edukasi_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- simulasi_pinjaman
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `simulasi_pinjaman` (
  `id_simulasi`       BIGINT(20)     NOT NULL AUTO_INCREMENT,
  `id_user`           BIGINT(20)     DEFAULT NULL,
  `jumlah_pinjaman`   DECIMAL(15,2)  DEFAULT NULL,
  `tenor_hari`        INT(11)        DEFAULT NULL,
  `bunga_per_hari`    DECIMAL(5,2)   DEFAULT NULL,
  `biaya_admin`       DECIMAL(10,2)  DEFAULT NULL,
  `cicilan_per_bulan` DECIMAL(15,2)  DEFAULT NULL,
  `total_bayar`       DECIMAL(15,2)  DEFAULT NULL,
  `apr_tahunan`       DECIMAL(5,2)   DEFAULT NULL,
  `created_at`        DATETIME       DEFAULT NULL,
  PRIMARY KEY (`id_simulasi`),
  KEY `fk_simulasi_user` (`id_user`),
  CONSTRAINT `simulasi_pinjaman_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ────────────────────────────────────────────────────────────
-- pengaturan_admin
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `pengaturan_admin` (
  `id_pengaturan`          BIGINT(20) NOT NULL AUTO_INCREMENT,
  `id_admin`               BIGINT(20) DEFAULT NULL,
  `email_alert_darurat`    TINYINT(1) DEFAULT 1,
  `ringkasan_laporan`      TINYINT(1) DEFAULT 1,
  `two_factor_enabled`     TINYINT(1) DEFAULT 0,
  `last_password_changed_at` DATETIME DEFAULT NULL,
  `updated_at`             DATETIME   DEFAULT NULL,
  PRIMARY KEY (`id_pengaturan`),
  UNIQUE KEY `uq_pengaturan_admin` (`id_admin`),
  CONSTRAINT `pengaturan_admin_ibfk_1` FOREIGN KEY (`id_admin`) REFERENCES `admin` (`id_admin`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;