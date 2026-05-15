ALTER TABLE `laporan`
  ADD COLUMN IF NOT EXISTS `tanggapan_ojk` TEXT NULL AFTER `id_admin_penanggung_jawab`,
  ADD COLUMN IF NOT EXISTS `tanggal_tanggapan` DATETIME NULL AFTER `tanggapan_ojk`;
