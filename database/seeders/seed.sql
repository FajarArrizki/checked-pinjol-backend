-- ============================================================
-- Seeder: pinjol_db
-- Admin & Superadmin only — tidak menyentuh tabel lain
-- ============================================================

USE `pinjol_db`;

REPLACE INTO `admin` (`id_admin`, `nama`, `email`, `username`, `password_hash`, `role`, `no_hp`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Admin Satu', 'admin1@pinjol.id', 'admin1', '$2y$12$Av8DPjrygcaWyhIIo8soEu8VqbNX79u0A0hvtxkojVcJ5tK7mRmom', 'admin', '08111000001', 1, NOW(), NOW()),
(2, 'Super Admin', 'superadmin@pinjol.id', 'superadmin', '$2y$12$Av8DPjrygcaWyhIIo8soEu8VqbNX79u0A0hvtxkojVcJ5tK7mRmom', 'superadmin', '08111000003', 1, NOW(), NOW());
