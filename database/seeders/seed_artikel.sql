-- ============================================================
-- Seed: artikel_edukasi
-- ============================================================

USE `pinjol_db`;

-- Insert sample articles
INSERT INTO `artikel_edukasi` 
  (`id_admin`, `judul`, `slug`, `kategori`, `author`, `summary`, `isi_artikel`, `gambar`, `status`, `published_at`, `created_at`, `updated_at`)
VALUES
  (
    10,
    'Cara Memilih Pinjaman Online yang Aman dan Legal',
    'cara-memilih-pinjaman-online-yang-aman-dan-legal',
    'Tips & Panduan',
    'Admin Checked Pinjol',
    'Panduan lengkap untuk memilih pinjaman online yang aman, legal, dan terdaftar di OJK. Pelajari ciri-ciri pinjol legal dan cara menghindari penipuan.',
    '<h2>Mengapa Penting Memilih Pinjol Legal?</h2><p>Pinjaman online (pinjol) yang legal dan terdaftar di OJK memberikan perlindungan kepada konsumen. Pinjol ilegal sering kali melakukan praktik tidak etis seperti bunga tinggi, akses data pribadi berlebihan, dan penagihan yang melanggar privasi.</p><h2>Ciri-ciri Pinjol Legal</h2><ul><li>Terdaftar dan diawasi oleh OJK</li><li>Memiliki izin usaha resmi</li><li>Bunga dan biaya transparan</li><li>Tidak meminta akses kontak dan galeri</li><li>Penagihan sesuai aturan</li></ul><h2>Cara Cek Legalitas Pinjol</h2><p>Anda dapat mengecek legalitas pinjol melalui website resmi OJK atau menggunakan fitur Cek Legalitas di platform Checked Pinjol. Pastikan nama perusahaan dan aplikasi terdaftar resmi.</p><h2>Tips Memilih Pinjol yang Tepat</h2><ol><li>Bandingkan bunga dan biaya admin</li><li>Baca syarat dan ketentuan dengan teliti</li><li>Pastikan tenor sesuai kemampuan bayar</li><li>Hindari pinjol yang menawarkan proses terlalu mudah</li><li>Cek review dan rating pengguna lain</li></ol>',
    '/images/articles/pinjol-legal.jpg',
    'published',
    '2026-05-10 10:00:00',
    '2026-05-10 10:00:00',
    '2026-05-10 10:00:00'
  ),
  (
    10,
    'Bahaya Pinjaman Online Ilegal dan Cara Menghindarinya',
    'bahaya-pinjaman-online-ilegal-dan-cara-menghindarinya',
    'Edukasi',
    'Admin Checked Pinjol',
    'Kenali bahaya pinjol ilegal seperti bunga mencekik, teror penagihan, dan penyalahgunaan data pribadi. Pelajari cara melindungi diri dari pinjol ilegal.',
    '<h2>Apa itu Pinjol Ilegal?</h2><p>Pinjol ilegal adalah layanan pinjaman online yang tidak terdaftar dan tidak diawasi oleh Otoritas Jasa Keuangan (OJK). Mereka beroperasi tanpa izin resmi dan sering melanggar hak konsumen.</p><h2>Bahaya Pinjol Ilegal</h2><ul><li><strong>Bunga Sangat Tinggi:</strong> Bisa mencapai 0.8% per hari atau lebih</li><li><strong>Akses Data Berlebihan:</strong> Mengambil kontak, foto, dan data pribadi tanpa izin</li><li><strong>Teror Penagihan:</strong> Menghubungi kontak darurat, menyebarkan foto, dan ancaman</li><li><strong>Denda Tidak Wajar:</strong> Denda keterlambatan yang sangat besar</li><li><strong>Penyalahgunaan Data:</strong> Data pribadi dijual atau disalahgunakan</li></ul><h2>Cara Menghindari Pinjol Ilegal</h2><ol><li>Selalu cek legalitas di website OJK atau Checked Pinjol</li><li>Jangan tergiur proses cepat tanpa verifikasi</li><li>Baca izin aplikasi sebelum install</li><li>Hindari pinjol yang meminta akses kontak dan galeri</li><li>Laporkan pinjol ilegal ke OJK atau Satgas Waspada Investasi</li></ol><h2>Apa yang Harus Dilakukan Jika Sudah Terlanjur?</h2><p>Jika Anda sudah terlanjur meminjam di pinjol ilegal, segera laporkan ke OJK, Polisi, atau gunakan fitur Laporan di Checked Pinjol. Jangan takut dengan ancaman, karena penagihan yang melanggar privasi adalah tindakan ilegal.</p>',
    '/images/articles/bahaya-pinjol-ilegal.jpg',
    'published',
    '2026-05-11 14:30:00',
    '2026-05-11 14:30:00',
    '2026-05-11 14:30:00'
  ),
  (
    11,
    'Simulasi Pinjaman: Hitung Cicilan Sebelum Mengajukan',
    'simulasi-pinjaman-hitung-cicilan-sebelum-mengajukan',
    'Tips & Panduan',
    'Tim Checked Pinjol',
    'Gunakan simulasi pinjaman untuk menghitung cicilan bulanan, total bunga, dan APR tahunan sebelum mengajukan pinjol. Hindari kejutan biaya tersembunyi.',
    '<h2>Mengapa Simulasi Penting?</h2><p>Sebelum mengajukan pinjaman online, penting untuk mengetahui berapa cicilan yang harus dibayar setiap bulan dan total biaya yang akan dikeluarkan. Simulasi pinjaman membantu Anda membuat keputusan finansial yang lebih baik.</p><h2>Komponen Biaya Pinjol</h2><ul><li><strong>Pokok Pinjaman:</strong> Jumlah uang yang dipinjam</li><li><strong>Bunga:</strong> Biaya pinjaman, biasanya per hari atau per bulan</li><li><strong>Biaya Admin:</strong> Biaya sekali bayar di awal</li><li><strong>Denda Keterlambatan:</strong> Biaya jika terlambat bayar</li></ul><h2>Cara Menggunakan Simulasi Pinjaman</h2><ol><li>Masukkan jumlah pinjaman yang diinginkan</li><li>Pilih tenor (jangka waktu) pinjaman</li><li>Masukkan bunga per hari (biasanya 0.1% - 0.4%)</li><li>Masukkan biaya admin jika ada</li><li>Klik hitung untuk melihat hasil</li></ol><h2>Memahami Hasil Simulasi</h2><p>Hasil simulasi akan menampilkan cicilan per bulan, total yang harus dibayar, dan APR (Annual Percentage Rate) tahunan. APR membantu Anda membandingkan biaya pinjaman dari berbagai penyedia.</p><h2>Tips Menggunakan Simulasi</h2><ul><li>Bandingkan beberapa skenario tenor berbeda</li><li>Pastikan cicilan tidak lebih dari 30% penghasilan</li><li>Perhatikan total bayar, bukan hanya cicilan bulanan</li><li>Gunakan fitur Simulasi Pinjaman di Checked Pinjol</li></ul>',
    '/images/articles/simulasi-pinjaman.jpg',
    'published',
    '2026-05-12 09:15:00',
    '2026-05-12 09:15:00',
    '2026-05-12 09:15:00'
  ),
  (
    10,
    'Hak dan Kewajiban Peminjam Pinjaman Online',
    'hak-dan-kewajiban-peminjam-pinjaman-online',
    'Edukasi',
    'Admin Checked Pinjol',
    'Ketahui hak dan kewajiban Anda sebagai peminjam pinjol. Lindungi diri dari praktik tidak etis dan pahami tanggung jawab Anda.',
    '<h2>Hak Peminjam Pinjol</h2><ul><li>Mendapat informasi bunga dan biaya yang jelas</li><li>Data pribadi dilindungi dan tidak disalahgunakan</li><li>Penagihan yang etis dan tidak melanggar privasi</li><li>Mendapat salinan perjanjian pinjaman</li><li>Melunasi lebih awal tanpa penalti berlebihan</li><li>Mengajukan keluhan ke OJK jika ada pelanggaran</li></ul><h2>Kewajiban Peminjam Pinjol</h2><ul><li>Membayar cicilan tepat waktu sesuai perjanjian</li><li>Memberikan informasi yang benar saat pengajuan</li><li>Membaca dan memahami syarat dan ketentuan</li><li>Menjaga komunikasi dengan penyedia pinjol</li><li>Melaporkan jika ada perubahan data kontak</li></ul><h2>Apa yang Harus Dilakukan Jika Kesulitan Bayar?</h2><p>Jika mengalami kesulitan membayar cicilan, segera hubungi penyedia pinjol untuk mencari solusi seperti restrukturisasi atau perpanjangan tenor. Jangan menghindari komunikasi karena akan memperburuk situasi.</p><h2>Cara Melaporkan Pelanggaran</h2><p>Jika penyedia pinjol melakukan pelanggaran seperti teror penagihan atau penyalahgunaan data, Anda dapat melaporkan ke OJK melalui website resmi atau menggunakan fitur Laporan di Checked Pinjol.</p>',
    '/images/articles/hak-kewajiban.jpg',
    'draft',
    NULL,
    '2026-05-13 16:45:00',
    '2026-05-13 16:45:00'
  ),
  (
    11,
    'Cara Melaporkan Pinjol Ilegal ke Otoritas',
    'cara-melaporkan-pinjol-ilegal-ke-otoritas',
    'Tips & Panduan',
    'Tim Checked Pinjol',
    'Panduan lengkap melaporkan pinjol ilegal ke OJK, Polisi, dan Satgas Waspada Investasi. Lindungi diri dan bantu orang lain terhindar dari pinjol ilegal.',
    '<h2>Mengapa Harus Melaporkan?</h2><p>Melaporkan pinjol ilegal membantu otoritas menindak pelaku dan melindungi masyarakat dari praktik tidak etis. Laporan Anda sangat berharga untuk penegakan hukum.</p><h2>Ke Mana Harus Melaporkan?</h2><ul><li><strong>OJK (Otoritas Jasa Keuangan):</strong> Melalui website, email, atau call center</li><li><strong>Polisi:</strong> Lapor ke Polda atau Polres setempat</li><li><strong>Satgas Waspada Investasi:</strong> Untuk pinjol ilegal yang beroperasi tanpa izin</li><li><strong>Checked Pinjol:</strong> Gunakan fitur Laporan untuk dokumentasi dan bantuan</li></ul><h2>Dokumen yang Perlu Disiapkan</h2><ul><li>Screenshot aplikasi atau website pinjol</li><li>Screenshot percakapan atau ancaman</li><li>Bukti transfer atau pembayaran</li><li>Screenshot izin aplikasi yang diminta</li><li>Data kontak pinjol (nomor telepon, email, dll)</li></ul><h2>Langkah-langkah Melaporkan</h2><ol><li>Kumpulkan semua bukti dan dokumen</li><li>Buat laporan tertulis yang jelas dan detail</li><li>Kirim laporan ke OJK, Polisi, atau Satgas</li><li>Simpan nomor laporan untuk follow-up</li><li>Gunakan fitur Laporan di Checked Pinjol untuk tracking</li></ol><h2>Apa yang Terjadi Setelah Laporan?</h2><p>Setelah laporan diterima, otoritas akan melakukan investigasi. Proses ini bisa memakan waktu, tapi laporan Anda akan membantu menghentikan operasi pinjol ilegal dan melindungi korban lain.</p>',
    '/images/articles/cara-melaporkan.jpg',
    'draft',
    NULL,
    '2026-05-14 11:20:00',
    '2026-05-14 11:20:00'
  );
