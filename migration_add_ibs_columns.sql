-- ============================================
-- Migration: Tambah Kolom untuk Integrasi IBS
-- Database: agunan_capture
-- Date: 06/11/2025
-- Description: Tambah kolom data IBS, history login, info fotografer
-- ============================================

USE agunan_capture;

-- ----------------------------
-- 1. ALTER TABLE agunan_data
-- Tambah kolom data dari IBS
-- ----------------------------

ALTER TABLE `agunan_data` 
ADD COLUMN `agunan_id_ibs` VARCHAR(20) NULL COMMENT 'ID Agunan dari Core Banking IBS' AFTER `id_agunan`,
ADD COLUMN `kode_jenis_agunan` CHAR(3) NULL COMMENT 'Kode jenis agunan dari IBS (5=SHM, 6=SHGB, dll)' AFTER `agunan_id_ibs`,
ADD COLUMN `deskripsi_ringkas` VARCHAR(254) NULL COMMENT 'Deskripsi agunan dari IBS' AFTER `kode_jenis_agunan`,
ADD COLUMN `tanah_no_shm` VARCHAR(100) NULL COMMENT 'Nomor Sertifikat Hak Milik' AFTER `deskripsi_ringkas`,
ADD COLUMN `tanah_no_shgb` VARCHAR(100) NULL COMMENT 'Nomor Sertifikat Hak Guna Bangunan' AFTER `tanah_no_shm`,
ADD COLUMN `tanah_tgl_sertifikat` DATE NULL COMMENT 'Tanggal Sertifikat' AFTER `tanah_no_shgb`,
ADD COLUMN `tanah_luas` DECIMAL(18,2) NULL COMMENT 'Luas Tanah (m2)' AFTER `tanah_tgl_sertifikat`,
ADD COLUMN `tanah_nama_pemilik` VARCHAR(200) NULL COMMENT 'Nama Pemilik Tanah' AFTER `tanah_luas`,
ADD COLUMN `tanah_lokasi` TEXT NULL COMMENT 'Lokasi: Desa, Kecamatan, Kabupaten' AFTER `tanah_nama_pemilik`,
ADD COLUMN `kend_jenis` VARCHAR(80) NULL COMMENT 'Jenis Kendaraan' AFTER `tanah_lokasi`,
ADD COLUMN `kend_merk` VARCHAR(80) NULL COMMENT 'Merk Kendaraan' AFTER `kend_jenis`,
ADD COLUMN `kend_tahun` CHAR(4) NULL COMMENT 'Tahun Pembuatan' AFTER `kend_merk`,
ADD COLUMN `kend_no_polisi` VARCHAR(100) NULL COMMENT 'Nomor Polisi Kendaraan' AFTER `kend_tahun`,
ADD COLUMN `verified_from_ibs` TINYINT(1) NULL DEFAULT 0 COMMENT '0=manual, 1=verified dari IBS' AFTER `kend_no_polisi`,
ADD COLUMN `verified_at` DATETIME NULL COMMENT 'Waktu verifikasi ke IBS' AFTER `verified_from_ibs`,
ADD COLUMN `verified_by` VARCHAR(50) NULL COMMENT 'Username yang melakukan verifikasi' AFTER `verified_at`,
ADD COLUMN `photo_taken_by` VARCHAR(50) NULL COMMENT 'Username fotografer' AFTER `verified_by`,
ADD COLUMN `photo_taken_at` DATETIME NULL COMMENT 'Waktu mulai foto' AFTER `photo_taken_by`,
ADD COLUMN `photo_device` VARCHAR(200) NULL COMMENT 'Device info (user agent)' AFTER `photo_taken_at`,
ADD INDEX `idx_agunan_id_ibs` (`agunan_id_ibs`),
ADD INDEX `idx_verified` (`verified_from_ibs`),
ADD INDEX `idx_photo_taken_by` (`photo_taken_by`);

-- ----------------------------
-- 2. ALTER TABLE user
-- Tambah history login
-- ----------------------------

ALTER TABLE `user`
ADD COLUMN `last_login_at` DATETIME NULL COMMENT 'Terakhir login' AFTER `nama_kc`,
ADD COLUMN `last_login_ip` VARCHAR(50) NULL COMMENT 'IP address terakhir login' AFTER `last_login_at`,
ADD COLUMN `login_count` INT(11) NULL DEFAULT 0 COMMENT 'Jumlah total login' AFTER `last_login_ip`,
ADD COLUMN `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal user dibuat' AFTER `login_count`,
ADD COLUMN `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`,
ADD INDEX `idx_last_login` (`last_login_at`);

-- ----------------------------
-- 3. CREATE TABLE user_login_history
-- Log detail setiap login
-- ----------------------------

DROP TABLE IF EXISTS `user_login_history`;
CREATE TABLE `user_login_history` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL COMMENT 'FK ke tabel user',
  `username` VARCHAR(50) NOT NULL,
  `login_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu login',
  `logout_at` DATETIME NULL COMMENT 'Waktu logout (jika ada)',
  `ip_address` VARCHAR(50) NULL COMMENT 'IP Address',
  `user_agent` TEXT NULL COMMENT 'Browser/Device info',
  `login_status` ENUM('success','failed') DEFAULT 'success' COMMENT 'Status login',
  `session_duration` INT(11) NULL COMMENT 'Durasi session (detik)',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id` (`user_id`),
  INDEX `idx_login_at` (`login_at`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Log history login user';

-- ----------------------------
-- 4. ALTER TABLE agunan_foto
-- Tambah info fotografer per foto
-- ----------------------------

ALTER TABLE `agunan_foto`
ADD COLUMN `uploaded_by` VARCHAR(50) NULL COMMENT 'Username yang upload foto' AFTER `foto_order`,
ADD COLUMN `uploaded_at` DATETIME NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload foto' AFTER `uploaded_by`,
ADD INDEX `idx_uploaded_by` (`uploaded_by`);

-- ----------------------------
-- 5. CREATE VIEW vw_agunan_with_ibs
-- View gabungan data lokal + data IBS
-- ----------------------------

DROP VIEW IF EXISTS `vw_agunan_with_ibs`;
CREATE VIEW `vw_agunan_with_ibs` AS
SELECT 
    ad.id,
    ad.id_agunan,
    ad.agunan_id_ibs,
    ad.nama_nasabah,
    ad.no_rek,
    ad.kode_jenis_agunan,
    ad.deskripsi_ringkas,
    CASE 
        WHEN ad.kode_jenis_agunan IN ('5','6') THEN CONCAT('Tanah - ', COALESCE(ad.tanah_no_shm, ad.tanah_no_shgb, '-'))
        ELSE CONCAT('Kendaraan - ', COALESCE(ad.kend_no_polisi, '-'))
    END AS jenis_agunan_display,
    ad.tanah_no_shm,
    ad.tanah_no_shgb,
    ad.tanah_luas,
    ad.tanah_nama_pemilik,
    ad.tanah_lokasi,
    ad.kend_jenis,
    ad.kend_merk,
    ad.kend_no_polisi,
    ad.verified_from_ibs,
    ad.verified_at,
    ad.verified_by,
    ad.photo_taken_by,
    ad.photo_taken_at,
    ad.created_by,
    ad.nama_kc,
    ad.pdf_filename,
    ad.pdf_path,
    COUNT(af.id) AS jumlah_foto,
    ad.created_at,
    ad.updated_at
FROM agunan_data ad
LEFT JOIN agunan_foto af ON ad.id = af.agunan_data_id
GROUP BY ad.id
ORDER BY ad.created_at DESC;

-- ----------------------------
-- Verifikasi hasil migration
-- ----------------------------

SELECT 'Migration completed successfully!' AS status;

-- Cek struktur tabel agunan_data
SELECT 'Struktur agunan_data:' AS info;
DESCRIBE agunan_data;

-- Cek struktur tabel user
SELECT 'Struktur user:' AS info;
DESCRIBE user;

-- Cek tabel user_login_history berhasil dibuat
SELECT 'Struktur user_login_history:' AS info;
DESCRIBE user_login_history;

-- Cek view berhasil dibuat
SELECT 'Test view vw_agunan_with_ibs:' AS info;
SELECT COUNT(*) AS total_records FROM vw_agunan_with_ibs;
