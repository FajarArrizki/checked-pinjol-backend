-- ============================================================
-- Seeder: pinjol_db
-- Fresh seed: hanya admin dan superadmin
-- User, laporan, pinjol, regulasi dikelola manual via app
-- ============================================================

USE `pinjol_db`;

SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM `lampiran_laporan`;
DELETE FROM `laporan_regulasi`;
DELETE FROM `laporan`;
DELETE FROM `ulasan`;
DELETE FROM `simulasi_pinjaman`;
DELETE FROM `user`;
DELETE FROM `pinjol`;
DELETE FROM `regulasi_filter`;
DELETE FROM `admin`;
SET FOREIGN_KEY_CHECKS = 1;

ALTER TABLE `admin` AUTO_INCREMENT = 1;
ALTER TABLE `user` AUTO_INCREMENT = 1;
ALTER TABLE `pinjol` AUTO_INCREMENT = 1;
ALTER TABLE `regulasi_filter` AUTO_INCREMENT = 1;
ALTER TABLE `laporan` AUTO_INCREMENT = 1;
ALTER TABLE `ulasan` AUTO_INCREMENT = 1;

INSERT INTO `admin` (`id_admin`, `nama`, `email`, `username`, `password_hash`, `role`, `no_hp`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin Satu', 'admin1@pinjol.id', 'admin1', '$2y$12$XrvINoK2nMibzqaq91BSr.qGYmTLPaUmpELY.19ExfZ1Lg6921G7q', 'admin', '08111000001', 1, NOW(), NOW()),
(2, 'Super Admin', 'superadmin@pinjol.id', 'superadmin', '$2y$12$dryiSe3aA6D1eCME39mld.JF0uyDwMdF05AWJU/glVrNUFYCrBPfK', 'superadmin', '08111000003', 1, NOW(), NOW());
