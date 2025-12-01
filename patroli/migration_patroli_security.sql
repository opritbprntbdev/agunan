-- ============================================
-- DATABASE: PATROLI SECURITY
-- Sistem Patroli Penjaga Malam
-- Terpisah dari agunan_capture
-- ============================================

CREATE DATABASE IF NOT EXISTS patroli_security 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE patroli_security;

-- ============================================
-- TABLE: users_security
-- User untuk sistem patroli (penjaga + admin pusat)
-- Password: PLAIN TEXT (no encryption/hash)
-- ============================================
CREATE TABLE IF NOT EXISTS users_security (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(100) NOT NULL COMMENT 'Plain text password - no encryption',
  role ENUM('penjaga', 'admin_pusat') NOT NULL DEFAULT 'penjaga',
  nama_lengkap VARCHAR(200),
  kode_kantor VARCHAR(10),
  nama_kc VARCHAR(200),
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME NULL,
  INDEX idx_username (username),
  INDEX idx_kode_kantor (kode_kantor),
  INDEX idx_role (role)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Insert default admin pusat
INSERT INTO users_security (username, password, role, nama_lengkap, kode_kantor, nama_kc) 
VALUES ('admin', 'admin123', 'admin_pusat', 'Administrator Pusat', '000', 'Kantor Pusat');

-- ============================================
-- TABLE: ruangan
-- Master data ruangan per cabang
-- ============================================
CREATE TABLE IF NOT EXISTS ruangan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_kantor VARCHAR(10) NOT NULL,
  kode_ruangan VARCHAR(20) NOT NULL,
  nama_ruangan VARCHAR(200),
  qr_code_content VARCHAR(100) NOT NULL,
  qr_code_image_path VARCHAR(255),
  is_active TINYINT(1) DEFAULT 1,
  created_by VARCHAR(100),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_ruangan (kode_kantor, kode_ruangan),
  INDEX idx_kode_kantor (kode_kantor)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: jadwal_patroli
-- Jadwal patroli per cabang
-- ============================================
CREATE TABLE IF NOT EXISTS jadwal_patroli (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode_kantor VARCHAR(10) NOT NULL UNIQUE,
  jam_patroli JSON NOT NULL COMMENT 'Array jam: ["21:00","00:00","03:00","06:00"]',
  toleransi_menit INT DEFAULT 30 COMMENT 'Toleransi waktu (tidak dipakai untuk validasi, hanya info)',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_kode_kantor (kode_kantor)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- Insert default jadwal untuk KC 028
INSERT INTO jadwal_patroli (kode_kantor, jam_patroli, toleransi_menit) 
VALUES ('028', '["21:00","00:00","03:00","06:00"]', 30);

-- ============================================
-- TABLE: patroli_log
-- Log hasil scan patroli
-- ============================================
CREATE TABLE IF NOT EXISTS patroli_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  record_id VARCHAR(100) NOT NULL UNIQUE COMMENT 'Format: 028-27/11/2025-21:30:45.123',
  user_id INT NOT NULL,
  username VARCHAR(100),
  kode_kantor VARCHAR(10) NOT NULL,
  kode_ruangan VARCHAR(20) NOT NULL,
  nama_ruangan VARCHAR(200),
  periode_waktu VARCHAR(10) NOT NULL COMMENT 'Jam periode: 21:00, 00:00, 03:00, 06:00',
  scan_datetime DATETIME NOT NULL COMMENT 'Waktu actual scan',
  foto_path VARCHAR(255),
  foto_watermarked_path VARCHAR(255),
  latitude DECIMAL(10, 8),
  longitude DECIMAL(11, 8),
  accuracy FLOAT,
  device_info TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_kode_kantor (kode_kantor),
  INDEX idx_periode (periode_waktu),
  INDEX idx_scan_date (scan_datetime),
  INDEX idx_record_id (record_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: notifikasi_log
-- Log notifikasi yang dikirim ke penjaga
-- ============================================
CREATE TABLE IF NOT EXISTS notifikasi_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  kode_kantor VARCHAR(10),
  periode_waktu VARCHAR(10) NOT NULL COMMENT 'Jam periode notif',
  notif_sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  status ENUM('sent', 'dismissed', 'completed') DEFAULT 'sent',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_user (user_id),
  INDEX idx_periode (periode_waktu),
  INDEX idx_sent_at (notif_sent_at)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ============================================
-- VIEWS untuk Report (Optional)
-- ============================================

-- View: Summary patroli per periode per cabang
CREATE OR REPLACE VIEW v_patroli_summary AS
SELECT 
  DATE(scan_datetime) as tanggal,
  kode_kantor,
  periode_waktu,
  COUNT(DISTINCT kode_ruangan) as total_ruangan_scan,
  COUNT(*) as total_scan,
  GROUP_CONCAT(DISTINCT username) as list_penjaga
FROM patroli_log
GROUP BY DATE(scan_datetime), kode_kantor, periode_waktu;

-- View: Detail patroli dengan info lengkap
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
  pl.scan_datetime,
  DATE_FORMAT(pl.scan_datetime, '%d/%m/%Y %H:%i:%s') as scan_datetime_formatted,
  pl.foto_watermarked_path,
  pl.latitude,
  pl.longitude,
  pl.created_at
FROM patroli_log pl
LEFT JOIN users_security us ON pl.user_id = us.id
ORDER BY pl.scan_datetime DESC;

-- ============================================
-- SAMPLE DATA untuk Testing
-- ============================================

-- Sample user penjaga untuk KC 028
INSERT INTO users_security (username, password, role, nama_lengkap, kode_kantor, nama_kc) 
VALUES 
('penjaga028', 'penjaga123', 'penjaga', 'Budi Santoso', '028', 'KC Paraya'),
('penjaga029', 'penjaga123', 'penjaga', 'Ahmad Rizki', '029', 'KC Cabang 2');

-- Sample ruangan untuk KC 028 (7 ruangan)
INSERT INTO ruangan (kode_kantor, kode_ruangan, nama_ruangan, qr_code_content, created_by) 
VALUES 
('028', 'R01', 'Ruang Teller', '028-R01', 'admin'),
('028', 'R02', 'Ruang Customer Service', '028-R02', 'admin'),
('028', 'R03', 'Ruang Kredit', '028-R03', 'admin'),
('028', 'R04', 'Ruang Manager', '028-R04', 'admin'),
('028', 'R05', 'Ruang Server', '028-R05', 'admin'),
('028', 'R06', 'Ruang Arsip', '028-R06', 'admin'),
('028', 'R07', 'Ruang Meeting', '028-R07', 'admin');

-- ============================================
-- DONE
-- ============================================
