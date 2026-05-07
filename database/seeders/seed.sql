-- ============================================================
-- Seeder: pinjol_db
-- Jalankan setelah init.sql
-- ============================================================

USE `pinjol_db`;

-- Admin (password: admin123 - di-hash dengan bcrypt saat production)
-- Untuk development, password_hash diisi plain text dan APP_ENV=development
INSERT INTO `admin` (`nama`, `email`, `username`, `password_hash`, `role`, `no_hp`, `is_active`, `created_at`, `updated_at`) VALUES
('Admin Utama',   'admin@pinjol.id',       'admin',      'admin123',       'superadmin', '08111000001', 1, NOW(), NOW()),
('Admin Moderator','moderator@pinjol.id',  'moderator',  'moderator123',   'admin',      '08111000002', 1, NOW(), NOW());

-- User
INSERT INTO `user` (`nama`, `email`, `no_hp`, `password_hash`, `created_at`, `updated_at`) VALUES
('Budi Santoso',  'budi@mail.com',   '08123456789', 'user123', NOW(), NOW()),
('Siti Rahayu',   'siti@mail.com',   '08234567890', 'user123', NOW(), NOW());

-- Pinjol
INSERT INTO `pinjol` (`nama_pinjol`, `tahun_berdiri`, `alamat`, `website`, `status_pinjol`, `created_by`, `created_at`, `updated_at`) VALUES
('AdaKami',           2016, 'Jakarta Selatan', 'https://adakami.id',         'legal',            1, NOW(), NOW()),
('Kredivo',           2015, 'Jakarta',         'https://kredivo.com',        'legal',            1, NOW(), NOW()),
('Akulaku',           2016, 'Jakarta',         'https://akulaku.com',        'legal',            1, NOW(), NOW()),
('KoinWorks',         2016, 'Jakarta',         'https://koinworks.com',      'legal',            1, NOW(), NOW()),
('Pinjam Cepat Pro',  NULL,  NULL,             NULL,                         'ilegal',           1, NOW(), NOW()),
('Dana Kilat 24Jam',  NULL,  NULL,             NULL,                         'ilegal',           1, NOW(), NOW()),
('UangMe',            2017, 'Jakarta',         'https://uangme.id',          'dalam_pengawasan', 1, NOW(), NOW());

-- Regulasi Filter
INSERT INTO `regulasi_filter` (`nama_kriteria`, `deskripsi`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
('Tidak terdaftar OJK',         'Pinjol tidak memiliki izin resmi dari Otoritas Jasa Keuangan (OJK)',                            1, 1, NOW(), NOW()),
('Bunga melebihi batas',        'Bunga harian melebihi 0.4% per hari yang ditetapkan AFPI',                                     1, 1, NOW(), NOW()),
('Akses kontak tidak sah',      'Aplikasi mengakses seluruh kontak HP peminjam tanpa izin yang sesuai',                         1, 1, NOW(), NOW()),
('Penagihan dengan ancaman',    'Debt collector melakukan penagihan dengan cara mengancam, mempermalukan, atau intimidasi',      1, 1, NOW(), NOW()),
('Data pribadi disalahgunakan', 'Data pribadi peminjam disebarkan atau digunakan untuk tujuan selain penagihan',                 1, 1, NOW(), NOW()),
('Biaya tersembunyi',           'Terdapat biaya yang tidak diinformasikan di awal seperti biaya administrasi tidak wajar',      1, 1, NOW(), NOW()),
('Tidak ada informasi perusahaan','Identitas, alamat, dan informasi perusahaan tidak transparan kepada peminjam',               1, 1, NOW(), NOW());

-- Artikel Edukasi
INSERT INTO `artikel_edukasi` (`id_admin`, `judul`, `kategori`, `isi_artikel`, `created_at`, `updated_at`) VALUES
(1, 'Cara Membedakan Pinjol Legal dan Ilegal', 'Edukasi', 
 'Pinjaman online (pinjol) legal wajib terdaftar di OJK. Ciri utama pinjol legal: memiliki izin OJK, bunga transparan maksimal 0.4%/hari, tidak mengakses semua kontak HP, dan penagihan sesuai etika. Sebaliknya, pinjol ilegal biasanya menawarkan proses sangat cepat tanpa verifikasi, bunga sangat tinggi, dan mengancam saat penagihan.',
 NOW(), NOW()),
(1, 'Waspadai Modus Pinjol Ilegal 2026', 'Peringatan',
 'Modus baru pinjol ilegal semakin beragam. Mereka kini menggunakan nama yang mirip pinjol legal, membuat aplikasi palsu di luar Play Store, dan menawarkan pinjaman melalui SMS/WhatsApp. Selalu cek daftar pinjol legal di website resmi OJK sebelum meminjam.',
 NOW(), NOW()),
(1, 'Tips Bijak Menggunakan Pinjaman Online', 'Tips',
 'Sebelum mengajukan pinjaman online: 1) Pastikan pinjol terdaftar OJK, 2) Hitung kemampuan cicilan (maksimal 30% penghasilan), 3) Baca syarat dan ketentuan dengan teliti, 4) Jangan pinjam untuk kebutuhan konsumtif, 5) Bayar tepat waktu untuk menghindari denda.',
 NOW(), NOW());

-- Sample Ulasan
INSERT INTO `ulasan` (`id_user`, `id_pinjol`, `nama_pengulas`, `rating`, `komentar`, `created_at`) VALUES
(1, 1, 'Budi S.', 4, 'Proses cepat dan transparan. Bunga sesuai yang dijanjikan. Recommended!', NOW()),
(2, 1, 'Siti R.', 5, 'Sudah 3x pinjam, selalu lancar. CS responsif dan profesional.', NOW()),
(1, 2, 'Budi S.', 3, 'Proses agak lama tapi aman. Bunga masih reasonable.', NOW());