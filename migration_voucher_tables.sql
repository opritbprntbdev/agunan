/*
 Migration: Tabel Voucher Capture
 Database: agunan_capture (localhost)
 Date: 13/11/2025
 Description: Tabel untuk capture voucher transaksi dari IBS
*/

USE agunan_capture;

-- ============================================================
-- 1. TABEL voucher_data (Master Voucher)
-- ============================================================
DROP TABLE IF EXISTS `voucher_data`;
CREATE TABLE `voucher_data` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  
  -- Data dari IBS (READ ONLY - tidak ada insert/update ke IBS)
  `trans_id` VARCHAR(50) NOT NULL COMMENT 'ID Transaksi dari IBS',
  `no_bukti` VARCHAR(100) NULL COMMENT 'Nomor Bukti Voucher dari IBS',
  `tgl_trans` DATE NULL COMMENT 'Tanggal Transaksi dari IBS',
  `uraian` TEXT NULL COMMENT 'Uraian Transaksi dari IBS',
  `kode_jurnal` VARCHAR(50) NULL COMMENT 'Kode Jurnal dari IBS',
  `kode_kantor_ibs` VARCHAR(20) NULL COMMENT 'Kode Kantor dari IBS',
  `nama_kantor` VARCHAR(255) NULL COMMENT 'Nama Kantor dari app_kode_kantor IBS',
  `alamat_kantor` VARCHAR(255) NULL COMMENT 'Alamat Kantor dari IBS',
  `kota_kantor` VARCHAR(255) NULL COMMENT 'Kota Kantor dari IBS',
  `nama_pimpinan` VARCHAR(255) NULL COMMENT 'Nama Pimpinan dari IBS',
  `total_debet` DECIMAL(18,2) NULL DEFAULT 0 COMMENT 'Total Debet dari transaksi_detail',
  `total_kredit` DECIMAL(18,2) NULL DEFAULT 0 COMMENT 'Total Kredit dari transaksi_detail',
  
  -- Flag verifikasi
  `verified_from_ibs` TINYINT(1) DEFAULT 1 COMMENT '1=verified dari IBS, 0=manual',
  `verified_at` DATETIME NULL COMMENT 'Waktu verifikasi ke IBS',
  `verified_by` VARCHAR(50) NULL COMMENT 'Username yang melakukan verifikasi',
  
  -- Info fotografer/user yang capture
  `user_id` INT(11) NOT NULL COMMENT 'FK ke tabel user',
  `photo_taken_by` VARCHAR(50) NOT NULL COMMENT 'Username fotografer',
  `photo_taken_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu mulai foto',
  `kode_kantor` VARCHAR(20) NOT NULL COMMENT 'Kode Kantor user yang capture (untuk isolasi data)',
  `nama_kc` VARCHAR(100) NOT NULL COMMENT 'Nama KC user yang capture',
  
  -- PDF info
  `pdf_filename` VARCHAR(255) NULL COMMENT 'Nama file PDF',
  `pdf_path` VARCHAR(500) NULL COMMENT 'Path file PDF',
  `total_foto` INT(11) DEFAULT 0 COMMENT 'Jumlah foto',
  
  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trans_id` (`trans_id`) USING BTREE,
  INDEX `idx_no_bukti` (`no_bukti`) USING BTREE,
  INDEX `idx_kode_kantor` (`kode_kantor`) USING BTREE,
  INDEX `idx_tgl_trans` (`tgl_trans`) USING BTREE,
  INDEX `idx_user_id` (`user_id`) USING BTREE,
  INDEX `idx_photo_taken_by` (`photo_taken_by`) USING BTREE,
  INDEX `idx_verified` (`verified_from_ibs`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Data Voucher yang di-capture';

-- ============================================================
-- 2. TABEL voucher_foto (Detail Foto Voucher)
-- ============================================================
DROP TABLE IF EXISTS `voucher_foto`;
CREATE TABLE `voucher_foto` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `voucher_data_id` INT(11) NOT NULL COMMENT 'FK ke voucher_data',
  
  -- File info
  `foto_filename` VARCHAR(255) NOT NULL COMMENT 'Nama file foto',
  `foto_path` VARCHAR(500) NOT NULL COMMENT 'Path file foto',
  `file_size` INT(11) DEFAULT 0 COMMENT 'Ukuran file (bytes)',
  `foto_order` INT(11) DEFAULT 1 COMMENT 'Urutan foto',
  
  -- KETERANGAN PER FOTO (bedanya dengan agunan!)
  `keterangan` TEXT NULL COMMENT 'Keterangan spesifik per foto (misal: Halaman 1, Lampiran, TTD, dll)',
  
  -- Info upload
  `uploaded_by` VARCHAR(50) NULL COMMENT 'Username yang upload foto',
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload foto',
  
  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_voucher_data_id` (`voucher_data_id`) USING BTREE,
  INDEX `idx_foto_order` (`foto_order`) USING BTREE,
  INDEX `idx_uploaded_by` (`uploaded_by`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=latin1 COMMENT='Foto-foto Voucher';

-- ============================================================
-- 3. VIEW vw_voucher_with_photos (untuk reporting)
-- ============================================================
DROP VIEW IF EXISTS `vw_voucher_with_photos`;
CREATE VIEW `vw_voucher_with_photos` AS
SELECT 
  vd.id,
  vd.trans_id,
  vd.no_bukti,
  vd.tgl_trans,
  vd.uraian,
  vd.kode_jurnal,
  vd.kode_kantor,
  vd.nama_kantor,
  vd.total_debet,
  vd.total_kredit,
  vd.photo_taken_by,
  vd.photo_taken_at,
  vd.pdf_filename,
  vd.pdf_path,
  vd.total_foto,
  COUNT(vf.id) AS jumlah_foto_aktual,
  vd.created_at,
  vd.updated_at
FROM voucher_data vd
LEFT JOIN voucher_foto vf ON vd.id = vf.voucher_data_id
GROUP BY vd.id
ORDER BY vd.created_at DESC;

-- ============================================================
-- 4. Sample Data (untuk testing - hapus jika tidak perlu)
-- ============================================================
-- INSERT INTO voucher_data (trans_id, no_bukti, tgl_trans, uraian, kode_kantor, nama_kc, user_id, photo_taken_by)
-- VALUES ('TEST001', 'VCH/001/2025', '2025-11-12', 'Test Voucher', '002', 'KC GERUNG', 3, 'gerung@bprntb.co.id');

-- ============================================================
-- CEK HASIL
-- ============================================================
SHOW TABLES LIKE 'voucher%';
DESC voucher_data;
DESC voucher_foto;
SELECT * FROM vw_voucher_with_photos;