-- ============================================
-- UPDATE DATABASE: PATROLI SECURITY V2
-- Perubahan: Status scan, keterlambatan, master waktu scan
-- ============================================

USE patroli_security;

-- ============================================
-- 1. ALTER TABLE patroli_log
-- Tambah kolom status_scan dan jam_keterlambatan
-- ============================================
ALTER TABLE patroli_log
ADD COLUMN status_scan ENUM('Tepat Waktu', 'Terlambat') DEFAULT 'Tepat Waktu' AFTER scan_datetime,
ADD COLUMN jam_keterlambatan INT DEFAULT 0 COMMENT 'Keterlambatan dalam menit' AFTER status_scan,
ADD COLUMN window_scan_start DATETIME COMMENT 'Waktu mulai window scan periode ini' AFTER periode_waktu,
ADD COLUMN window_scan_end DATETIME COMMENT 'Waktu akhir window scan periode ini' AFTER window_scan_start;

-- ============================================
-- 2. ALTER TABLE jadwal_patroli
-- Update untuk mendukung pola scan dinamis
-- ============================================
ALTER TABLE jadwal_patroli
DROP COLUMN jam_patroli,
DROP COLUMN toleransi_menit,
ADD COLUMN jam_mulai_shift VARCHAR(10) DEFAULT '21:00' COMMENT 'Jam mulai shift malam' AFTER kode_kantor,
ADD COLUMN durasi_scan_menit INT DEFAULT 60 COMMENT 'Durasi waktu untuk scan semua ruangan (menit)' AFTER jam_mulai_shift,
ADD COLUMN durasi_istirahat_menit INT DEFAULT 60 COMMENT 'Durasi istirahat setelah scan (menit)' AFTER durasi_scan_menit,
ADD COLUMN jumlah_periode INT DEFAULT 4 COMMENT 'Jumlah periode scan dalam 1 shift (4 = 21-22, 23-00, 01-02, 03-04)' AFTER durasi_istirahat_menit;

-- ============================================
-- 3. CREATE TABLE master_waktu_scan
-- Master setting waktu scan global (bisa override per cabang)
-- ============================================
CREATE TABLE IF NOT EXISTS master_waktu_scan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nama_setting VARCHAR(100) NOT NULL,
  kode_kantor VARCHAR(10) DEFAULT 'GLOBAL' COMMENT 'GLOBAL untuk semua cabang, atau kode_kantor spesifik',
  jam_mulai_shift VARCHAR(10) NOT NULL DEFAULT '21:00',
  durasi_scan_menit INT NOT NULL DEFAULT 60 COMMENT 'Waktu untuk scan (menit)',
  durasi_istirahat_menit INT NOT NULL DEFAULT 60 COMMENT 'Waktu istirahat (menit)',
  jumlah_periode INT NOT NULL DEFAULT 4 COMMENT 'Berapa kali periode scan dalam 1 shift',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_setting (kode_kantor),
  INDEX idx_kode_kantor (kode_kantor)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Insert default setting GLOBAL
INSERT INTO master_waktu_scan (nama_setting, kode_kantor, jam_mulai_shift, durasi_scan_menit, durasi_istirahat_menit, jumlah_periode) 
VALUES 
('Setting Global Default', 'GLOBAL', '21:00', 60, 60, 4);

-- ============================================
-- 4. UPDATE jadwal_patroli yang sudah ada
-- Update KC 028 dengan setting baru
-- ============================================
DELETE FROM jadwal_patroli WHERE kode_kantor = '028';

INSERT INTO jadwal_patroli (kode_kantor, jam_mulai_shift, durasi_scan_menit, durasi_istirahat_menit, jumlah_periode) 
VALUES ('028', '21:00', 60, 60, 4);

-- ============================================
-- 5. CREATE VIEW untuk generate periode scan
-- View untuk menghitung periode scan berdasarkan master_waktu_scan
-- ============================================
CREATE OR REPLACE VIEW v_periode_scan_schedule AS
SELECT 
  mw.kode_kantor,
  mw.jam_mulai_shift,
  mw.durasi_scan_menit,
  mw.durasi_istirahat_menit,
  mw.jumlah_periode,
  -- Perhitungan window scan (akan digunakan di aplikasi)
  CONCAT(
    'Periode 1: ', mw.jam_mulai_shift, ' - ', 
    DATE_FORMAT(ADDTIME(mw.jam_mulai_shift, SEC_TO_TIME(mw.durasi_scan_menit * 60)), '%H:%i'),
    ' | Istirahat: ',
    DATE_FORMAT(ADDTIME(mw.jam_mulai_shift, SEC_TO_TIME(mw.durasi_scan_menit * 60)), '%H:%i'),
    ' - ',
    DATE_FORMAT(ADDTIME(mw.jam_mulai_shift, SEC_TO_TIME((mw.durasi_scan_menit + mw.durasi_istirahat_menit) * 60)), '%H:%i')
  ) as contoh_periode_1
FROM master_waktu_scan mw
WHERE mw.is_active = 1;

-- ============================================
-- 6. UPDATE VIEW v_patroli_detail
-- Tambah kolom status_scan dan jam_keterlambatan
-- ============================================
DROP VIEW IF EXISTS v_patroli_detail;

CREATE OR REPLACE VIEW v_patroli_detail AS
SELECT 
  pl.id,
  pl.record_id,
  pl.kode_kantor,
  us.nama_kc,
  pl.kode_ruangan,
  pl.nama_ruangan,
  pl.username,
  us.nama_lengkap,
  pl.periode_waktu,
  pl.window_scan_start,
  pl.window_scan_end,
  pl.scan_datetime,
  pl.status_scan,
  pl.jam_keterlambatan,
  DATE_FORMAT(pl.scan_datetime, '%d/%m/%Y %H:%i:%s') as scan_datetime_formatted,
  CASE 
    WHEN pl.status_scan = 'Tepat Waktu' THEN '✅ Tepat Waktu'
    WHEN pl.status_scan = 'Terlambat' THEN CONCAT('⚠️ Terlambat ', pl.jam_keterlambatan, ' menit')
    ELSE '-'
  END as status_display,
  pl.foto_watermarked_path,
  pl.latitude,
  pl.longitude,
  pl.created_at
FROM patroli_log pl
LEFT JOIN users_security us ON pl.user_id = us.id
ORDER BY pl.scan_datetime DESC;

-- ============================================
-- CONTOH PERHITUNGAN PERIODE SCAN
-- Berdasarkan setting: 21:00 start, 60 menit scan, 60 menit istirahat, 4 periode
-- ============================================
-- Periode 1: 21:00 - 22:00 (SCAN)   | 22:00 - 23:00 (ISTIRAHAT)
-- Periode 2: 23:00 - 00:00 (SCAN)   | 00:00 - 01:00 (ISTIRAHAT)
-- Periode 3: 01:00 - 02:00 (SCAN)   | 02:00 - 03:00 (ISTIRAHAT)
-- Periode 4: 03:00 - 04:00 (SCAN)   | 04:00 - 05:00 (ISTIRAHAT)
-- Selesai shift: 05:00

-- ============================================
-- DONE - Migration V2
-- ============================================
