-- ============================================================
-- Seeder: pinjol_db
-- Hanya seed admin dan superadmin baru
-- ============================================================

USE `pinjol_db`;

DELETE FROM `admin`;

DELETE FROM `lampiran_laporan`;
DELETE FROM `laporan_regulasi`;
DELETE FROM `laporan`;
DELETE FROM `pinjol`;
DELETE FROM `regulasi_filter`;

INSERT INTO `admin` (`nama`, `email`, `username`, `password_hash`, `role`, `no_hp`, `is_active`, `created_at`, `updated_at`) VALUES
('Admin Satu', 'admin1@pinjol.id', 'admin1', '$2y$12$H4d2XsSPrvJ8uxrO97jWI.MYtCUdCgpLENDURnc7JJbGam8kyYJ8m', 'admin', '08111000001', 1, NOW(), NOW()),
('Super Admin', 'superadmin@pinjol.id', 'superadmin', '$2y$12$d287SqKqfL34BVqB6J1/s.swSOHlDwZXAQsK4ZLSNIRTE.2a7rVEC', 'superadmin', '08111000003', 1, NOW(), NOW());

INSERT INTO `pinjol` (`nama_pinjol`, `tahun_berdiri`, `alamat`, `website`, `status_pinjol`, `created_by`, `created_at`, `updated_at`) VALUES
('KreditPintar', 2018, 'Jakarta', 'https://kreditpintar.com', 'legal', NULL, NOW(), NOW()),
('AdaKami', 2016, 'Jakarta', 'https://adakami.id', 'legal', NULL, NOW(), NOW()),
('PinjamCepat', NULL, NULL, NULL, 'ilegal', NULL, NOW(), NOW());

INSERT INTO `regulasi_filter` (`nama_kriteria`, `deskripsi`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
('Izin OJK Tidak Ditemukan', 'Aplikasi tidak terdaftar resmi di OJK.', 1, 1, NOW(), NOW()),
('Bunga Tidak Wajar', 'Bunga pinjaman melebihi batas kewajaran.', 1, 1, NOW(), NOW());

INSERT INTO `laporan` (`id_user`, `kode_laporan`, `judul_laporan`, `isi_laporan`, `nama_pelapor`, `kontak_pelapor`, `email_pelapor`, `tautan_aplikasi`, `foto_bukti`, `status_laporan`, `tanggal_lapor`, `id_pinjol`, `id_admin_penanggung_jawab`, `tanggapan_ojk`, `tanggal_tanggapan`, `created_at`, `updated_at`) VALUES
(1, 'LAP-DEMO-001', 'Aplikasi Menagih di Luar Jam Wajar', '<p>Aplikasi melakukan penagihan di luar jam yang diperbolehkan dan mengirim pesan berulang.</p>', 'Budi Santoso', '081234567890', 'budi@example.com', 'https://play.google.com/store/apps/details?id=com.demo.pinjol', NULL, 'diproses', NOW(), 1, 1, '<p>Laporan sedang kami proses dan akan ditindaklanjuti.</p>', NOW(), NOW(), NOW()),
(1, 'LAP-DEMO-002', 'Aplikasi Tidak Punya Izin Resmi', '<p>Aplikasi tidak ditemukan pada daftar penyelenggara berizin dan menggunakan metode penagihan agresif.</p>', 'Siti Aminah', '081298765432', 'siti@example.com', 'https://play.google.com/store/apps/details?id=com.ilegal.demo', NULL, 'menunggu', NOW(), 3, NULL, NULL, NULL, NOW(), NOW());

INSERT INTO `lampiran_laporan` (`id_laporan`, `nama_file`, `file_path`, `tipe_file`, `ukuran_file`, `uploaded_at`) VALUES
(1, 'bukti-chat-1.png', 'uploads/laporan/bukti-chat-1.png', 'image', 245120, NOW()),
(1, 'bukti-penagihan.png', 'uploads/laporan/bukti-penagihan.png', 'image', 198432, NOW()),
(2, 'bukti-izin.png', 'uploads/laporan/bukti-izin.png', 'image', 153220, NOW());

INSERT INTO `laporan_regulasi` (`id_laporan`, `id_regulasi`, `catatan`) VALUES
(1, 1, 'Bukti menunjukkan pelanggaran jam penagihan.'),
(2, 1, 'Aplikasi tidak terdaftar pada daftar resmi.'),
(2, 2, 'Pengguna menerima biaya tambahan tidak jelas.');
