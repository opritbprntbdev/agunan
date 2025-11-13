/*
 Navicat Premium Data Transfer

 Source Server         : localhost wamp server
 Source Server Type    : MySQL
 Source Server Version : 50736 (5.7.36)
 Source Host           : localhost:3308
 Source Schema         : agunan_capture

 Target Server Type    : MySQL
 Target Server Version : 50736 (5.7.36)
 File Encoding         : 65001

 Date: 13/11/2025 15:57:47
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for agunan
-- ----------------------------
DROP TABLE IF EXISTS `agunan`;
CREATE TABLE `agunan`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `alamat` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `jenis_agunan` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `foto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of agunan
-- ----------------------------

-- ----------------------------
-- Table structure for agunan_data
-- ----------------------------
DROP TABLE IF EXISTS `agunan_data`;
CREATE TABLE `agunan_data`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_agunan` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `agunan_id_ibs` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'ID Agunan dari Core Banking IBS',
  `kode_jenis_agunan` char(3) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Kode jenis agunan dari IBS (5=SHM, 6=SHGB, dll)',
  `cif` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'CIF Nasabah dari IBS',
  `nama_nasabah_ibs` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nama Nasabah dari tabel nasabah IBS',
  `alamat_nasabah_ibs` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Alamat Nasabah dari IBS',
  `deskripsi_ringkas` varchar(254) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Deskripsi agunan dari IBS',
  `tanah_no_shm` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nomor Sertifikat Hak Milik',
  `tanah_no_shgb` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nomor Sertifikat Hak Guna Bangunan',
  `tanah_tgl_sertifikat` date NULL DEFAULT NULL COMMENT 'Tanggal Sertifikat',
  `tanah_luas` decimal(18, 2) NULL DEFAULT NULL COMMENT 'Luas Tanah (m2)',
  `tanah_nama_pemilik` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nama Pemilik Tanah',
  `tanah_lokasi` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Lokasi: Desa, Kecamatan, Kabupaten',
  `kend_jenis` varchar(80) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Jenis Kendaraan',
  `kend_merk` varchar(80) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Merk Kendaraan',
  `kend_tahun` char(4) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Tahun Pembuatan',
  `kend_no_polisi` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nomor Polisi Kendaraan',
  `verified_from_ibs` tinyint(1) NULL DEFAULT 0 COMMENT '0=manual, 1=verified dari IBS',
  `verified_at` datetime NULL DEFAULT NULL COMMENT 'Waktu verifikasi ke IBS',
  `verified_by` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Username yang melakukan verifikasi',
  `photo_taken_by` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Username fotografer',
  `photo_taken_at` datetime NULL DEFAULT NULL COMMENT 'Waktu mulai foto',
  `photo_device` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Device info (user agent)',
  `nama_nasabah` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `no_rek` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_by` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama_kc` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kode_kantor` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT 'POKP',
  `pdf_filename` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `pdf_path` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `total_foto` int(11) NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE,
  INDEX `idx_id_agunan`(`id_agunan`) USING BTREE,
  INDEX `idx_nama_nasabah`(`nama_nasabah`) USING BTREE,
  INDEX `idx_no_rek`(`no_rek`) USING BTREE,
  INDEX `idx_created_at`(`created_at`) USING BTREE,
  INDEX `idx_agunan_id_ibs`(`agunan_id_ibs`) USING BTREE,
  INDEX `idx_verified`(`verified_from_ibs`) USING BTREE,
  INDEX `idx_photo_taken_by`(`photo_taken_by`) USING BTREE,
  INDEX `idx_cif`(`cif`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 24 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of agunan_data
-- ----------------------------
INSERT INTO `agunan_data` VALUES (1, '527103', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'suherli', '0280987768', 1, 'admin', 'kpno', 'POKP', 'agunan_527103_20251030033828.pdf', 'pdf/2025/10/agunan_527103_20251030033828.pdf', 2, '2025-10-30 11:38:28', '2025-10-30 11:38:28');
INSERT INTO `agunan_data` VALUES (2, '1234', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Andi', '123', 1, 'admin', 'kpno', 'POKP', 'agunan_1234_20251030034251.pdf', 'pdf/2025/10/agunan_1234_20251030034251.pdf', 2, '2025-10-30 11:42:51', '2025-10-30 11:42:51');
INSERT INTO `agunan_data` VALUES (3, '345', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Yulia', '3r5', 1, 'admin', 'kpno', 'POKP', 'agunan_345_20251030035441.pdf', 'pdf/2025/10/agunan_345_20251030035441.pdf', 1, '2025-10-30 11:54:41', '2025-10-30 11:54:41');
INSERT INTO `agunan_data` VALUES (4, '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'LOL', '123r', 1, 'admin', 'kpno', 'POKP', 'agunan_123_20251030035556.pdf', 'pdf/2025/10/agunan_123_20251030035556.pdf', 1, '2025-10-30 11:55:56', '2025-10-30 11:55:56');
INSERT INTO `agunan_data` VALUES (5, '12455', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Andi', '625182929', 1, 'admin', 'kpno', 'POKP', 'agunan_12455_20251030042845.pdf', 'pdf/2025/10/agunan_12455_20251030042845.pdf', 1, '2025-10-30 12:28:45', '2025-10-30 12:28:45');
INSERT INTO `agunan_data` VALUES (6, '789', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Lukman', '789', 1, 'admin', 'kpno', 'POKP', 'agunan_789_20251030042926.pdf', 'pdf/2025/10/agunan_789_20251030042926.pdf', 1, '2025-10-30 12:29:26', '2025-10-30 12:29:26');
INSERT INTO `agunan_data` VALUES (7, '12234', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Denis', '625152728', 1, 'admin', 'kpno', 'POKP', 'agunan_12234_20251030043230.pdf', 'pdf/2025/10/agunan_12234_20251030043230.pdf', 1, '2025-10-30 12:32:30', '2025-10-30 12:32:30');
INSERT INTO `agunan_data` VALUES (8, '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'dfsdas', 'a3213', 1, 'admin', 'kpno', 'POKP', 'agunan_123_20251030080011.pdf', 'pdf/2025/10/agunan_123_20251030080011.pdf', 1, '2025-10-30 16:00:11', '2025-10-30 16:00:11');
INSERT INTO `agunan_data` VALUES (9, '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Shsk', '123t5', 1, 'admin', 'kpno', 'POKP', 'agunan_123_20251030081219.pdf', 'pdf/2025/10/agunan_123_20251030081219.pdf', 1, '2025-10-30 16:12:19', '2025-10-30 16:12:19');
INSERT INTO `agunan_data` VALUES (10, '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Asd', '1355', 1, 'admin', 'kpno', 'POKP', 'agunan_123_20251030084559.pdf', 'pdf/2025/10/agunan_123_20251030084559.pdf', 2, '2025-10-30 16:45:59', '2025-10-30 16:46:01');
INSERT INTO `agunan_data` VALUES (11, '1233', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Andi', '1245676', 1, 'admin', 'kpno', 'POKP', 'agunan_1233_20251030090032.pdf', 'pdf/2025/10/agunan_1233_20251030090032.pdf', 5, '2025-10-30 17:00:31', '2025-10-30 17:00:32');
INSERT INTO `agunan_data` VALUES (12, '1234', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Narti', '135381910', 1, 'admin', 'kpno', 'POKP', 'agunan_1234_20251031031017.pdf', 'pdf/2025/10/agunan_1234_20251031031017.pdf', 4, '2025-10-31 11:10:16', '2025-10-31 11:10:17');
INSERT INTO `agunan_data` VALUES (13, '1223', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Mahsgsg', '1363849', 1, 'admin', 'kpno', 'POKP', 'agunan_1223_20251031032240.pdf', 'pdf/2025/10/agunan_1223_20251031032240.pdf', 4, '2025-10-31 11:22:39', '2025-10-31 11:22:40');
INSERT INTO `agunan_data` VALUES (14, '123', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, 'Susii', '123', 1, 'admin', 'kpno', 'POKP', 'agunan_123_20251031033118.pdf', 'pdf/2025/10/agunan_123_20251031033118.pdf', 3, '2025-10-31 11:31:17', '2025-10-31 11:31:18');
INSERT INTO `agunan_data` VALUES (15, '001000014', '001000014', '5', NULL, NULL, NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', NULL, NULL, NULL, 0.00, NULL, NULL, 'SP-MOTOR', 'HONDA', '2003', 'DR 5245 AN', 1, '2025-11-06 07:44:16', 'admin', 'admin', '2025-11-06 15:44:23', NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', '-', 1, 'admin', 'kpno', 'POKP', 'agunan_001000014_20251106074423.pdf', 'pdf/2025/11/agunan_001000014_20251106074423.pdf', 1, '2025-11-06 15:44:23', '2025-11-06 15:44:23');
INSERT INTO `agunan_data` VALUES (16, '001000014', '001000014', '5', NULL, NULL, NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', NULL, NULL, NULL, 0.00, NULL, NULL, 'SP-MOTOR', 'HONDA', '2003', 'DR 5245 AN', 1, '2025-11-06 07:42:04', 'admin', 'admin', '2025-11-06 15:44:47', NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', '-', 1, 'admin', 'kpno', 'POKP', 'agunan_001000014_20251106074447.pdf', 'pdf/2025/11/agunan_001000014_20251106074447.pdf', 1, '2025-11-06 15:44:47', '2025-11-06 15:44:47');
INSERT INTO `agunan_data` VALUES (17, '001000014', '001000014', '5', NULL, NULL, NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', NULL, NULL, NULL, 0.00, NULL, NULL, 'SP-MOTOR', 'HONDA', '2003', 'DR 5245 AN', 1, '2025-11-06 07:45:29', 'admin', 'admin', '2025-11-06 15:45:36', NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', '-', 1, 'admin', 'kpno', 'POKP', 'agunan_001000014_20251106074536.pdf', 'pdf/2025/11/agunan_001000014_20251106074536.pdf', 1, '2025-11-06 15:45:36', '2025-11-06 15:45:36');
INSERT INTO `agunan_data` VALUES (18, '001000014', '001000014', '5', NULL, NULL, NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', NULL, NULL, NULL, 0.00, NULL, NULL, 'SP-MOTOR', 'HONDA', '2003', 'DR 5245 AN', 1, '2025-11-06 07:52:51', 'admin', 'admin', '2025-11-06 15:53:04', NULL, 'BPKB NO.4548597 O  TAHUN 2003 AN.SOFWAN, SH.M.HUM', '-', 1, 'admin', 'kpno', 'POKP', 'agunan_001000014_20251106075305.pdf', 'pdf/2025/11/agunan_001000014_20251106075305.pdf', 3, '2025-11-06 15:53:04', '2025-11-06 15:53:05');
INSERT INTO `agunan_data` VALUES (19, '001000015', '001000015', '5', '001000006', 'ROBBY HERMAWAN', 'RUMADIS TNI-AL TANJUNG KARANG AMPENAN', 'BPKB NO.2094162.N  TAHUN 2002 AN.RAI TITI PIDADA, SH', NULL, NULL, NULL, 0.00, NULL, NULL, 'SEPEDA MOTOR', 'KAWASAKI', '2002', 'DR 3472 AZ', 1, '2025-11-06 08:05:00', 'admin', 'admin', '2025-11-06 16:05:10', NULL, 'BPKB NO.2094162.N  TAHUN 2002 AN.RAI TITI PIDADA, SH', '0', 1, 'admin', 'kpno', 'POKP', 'agunan_001000015_20251106080511.pdf', 'pdf/2025/11/agunan_001000015_20251106080511.pdf', 5, '2025-11-06 16:05:10', '2025-11-06 16:05:11');
INSERT INTO `agunan_data` VALUES (23, '019004999', '019004999', NULL, NULL, NULL, NULL, NULL, '70', '', '1985-04-15', 189.00, 'RAODAH', 'RENSING, SAKRA, LOMBOK TIMUR', '', '', '', '', 1, NULL, NULL, 'selong@bprntb.co.id', '2025-11-13 15:52:56', NULL, 'RAODAH', '1912101028', 19, 'selong@bprntb.co.id', 'KC SELONG', '018', 'agunan_019004999_20251113075257.pdf', 'pdf/2025/11/agunan_019004999_20251113075257.pdf', 1, '2025-11-13 15:52:56', '2025-11-13 15:52:57');
INSERT INTO `agunan_data` VALUES (21, '002000338', '002000338', '6', '002002621', 'JASMANIK SPD', 'DASAN GERES SELATAN DESA DASAN GERES', 'SERTIFIKAT TANAH PEKARANGAN', '', '', NULL, NULL, 'JASMANIK', NULL, '', '', '', '', 1, '2025-11-13 05:43:02', 'gerung@bprntb.co.id', 'gerung@bprntb.co.id', '2025-11-13 13:43:36', NULL, 'JASMANIK', '0', 3, 'gerung@bprntb.co.id', 'KC GERUNG', '002', 'agunan_002000338_20251113054338.pdf', 'pdf/2025/11/agunan_002000338_20251113054338.pdf', 5, '2025-11-13 13:43:36', '2025-11-13 13:43:38');

-- ----------------------------
-- Table structure for agunan_foto
-- ----------------------------
DROP TABLE IF EXISTS `agunan_foto`;
CREATE TABLE `agunan_foto`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agunan_data_id` int(11) NOT NULL,
  `foto_filename` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `foto_path` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `keterangan` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `file_size` int(11) NULL DEFAULT 0,
  `foto_order` int(11) NULL DEFAULT 1,
  `uploaded_by` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Username yang upload foto',
  `uploaded_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload foto',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_agunan_data_id`(`agunan_data_id`) USING BTREE,
  INDEX `idx_foto_order`(`foto_order`) USING BTREE,
  INDEX `idx_uploaded_by`(`uploaded_by`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 54 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of agunan_foto
-- ----------------------------
INSERT INTO `agunan_foto` VALUES (1, 1, 'foto_1_1_1761795508.jpg', 'uploads/2025/10/foto_1_1_1761795508.jpg', 'lanjut', 1813744, 1, NULL, '2025-11-06 12:11:41', '2025-10-30 11:38:28');
INSERT INTO `agunan_foto` VALUES (2, 1, 'foto_1_2_1761795508.jpg', 'uploads/2025/10/foto_1_2_1761795508.jpg', 'lanjut 2', 2040238, 2, NULL, '2025-11-06 12:11:41', '2025-10-30 11:38:28');
INSERT INTO `agunan_foto` VALUES (3, 4, 'foto_4_1_1761796556.jpg', 'uploads/2025/10/foto_4_1_1761796556.jpg', 'meja', 1564031, 1, NULL, '2025-11-06 12:11:41', '2025-10-30 11:55:56');
INSERT INTO `agunan_foto` VALUES (4, 5, 'foto_5_1_1761798525.jpg', 'uploads/2025/10/foto_5_1_1761798525.jpg', 'Test foto', 1085814, 1, NULL, '2025-11-06 12:11:41', '2025-10-30 12:28:45');
INSERT INTO `agunan_foto` VALUES (5, 8, 'foto_8_1_1761811211.jpg', 'uploads/2025/10/foto_8_1_1761811211.jpg', 'Foto 1', 29356, 1, NULL, '2025-11-06 12:11:41', '2025-10-30 16:00:11');
INSERT INTO `agunan_foto` VALUES (6, 9, 'foto_9_1_1761811939.jpg', 'uploads/2025/10/foto_9_1_1761811939.jpg', 'Foto 1', 547612, 1, NULL, '2025-11-06 12:11:41', '2025-10-30 16:12:19');
INSERT INTO `agunan_foto` VALUES (7, 10, 'foto_10_1_1761813959.jpg', 'uploads/2025/10/foto_10_1_1761813959.jpg', 'Foto 1', 49849, 1, NULL, '2025-11-06 12:11:41', '2025-10-30 16:45:59');
INSERT INTO `agunan_foto` VALUES (8, 10, 'foto_10_2_1761813961.jpg', 'uploads/2025/10/foto_10_2_1761813961.jpg', 'Foto 2', 49849, 2, NULL, '2025-11-06 12:11:41', '2025-10-30 16:46:01');
INSERT INTO `agunan_foto` VALUES (9, 11, 'foto_11_1_1761814831.jpg', 'uploads/2025/10/foto_11_1_1761814831.jpg', '', 59249, 1, NULL, '2025-11-06 12:11:41', '2025-10-30 17:00:31');
INSERT INTO `agunan_foto` VALUES (10, 11, 'foto_11_2_1761814831.jpg', 'uploads/2025/10/foto_11_2_1761814831.jpg', '', 76563, 2, NULL, '2025-11-06 12:11:41', '2025-10-30 17:00:31');
INSERT INTO `agunan_foto` VALUES (11, 11, 'foto_11_3_1761814831.jpg', 'uploads/2025/10/foto_11_3_1761814831.jpg', '', 77592, 3, NULL, '2025-11-06 12:11:41', '2025-10-30 17:00:31');
INSERT INTO `agunan_foto` VALUES (12, 11, 'foto_11_4_1761814831.jpg', 'uploads/2025/10/foto_11_4_1761814831.jpg', '', 85467, 4, NULL, '2025-11-06 12:11:41', '2025-10-30 17:00:31');
INSERT INTO `agunan_foto` VALUES (13, 11, 'foto_11_5_1761814832.jpg', 'uploads/2025/10/foto_11_5_1761814832.jpg', '', 84410, 5, NULL, '2025-11-06 12:11:41', '2025-10-30 17:00:32');
INSERT INTO `agunan_foto` VALUES (14, 12, 'foto_12_1_1761880216.jpg', 'uploads/2025/10/foto_12_1_1761880216.jpg', '', 69012, 1, NULL, '2025-11-06 12:11:41', '2025-10-31 11:10:16');
INSERT INTO `agunan_foto` VALUES (15, 12, 'foto_12_2_1761880217.jpg', 'uploads/2025/10/foto_12_2_1761880217.jpg', '', 72934, 2, NULL, '2025-11-06 12:11:41', '2025-10-31 11:10:17');
INSERT INTO `agunan_foto` VALUES (16, 12, 'foto_12_3_1761880217.jpg', 'uploads/2025/10/foto_12_3_1761880217.jpg', '', 67425, 3, NULL, '2025-11-06 12:11:41', '2025-10-31 11:10:17');
INSERT INTO `agunan_foto` VALUES (17, 12, 'foto_12_4_1761880217.jpg', 'uploads/2025/10/foto_12_4_1761880217.jpg', '', 67632, 4, NULL, '2025-11-06 12:11:41', '2025-10-31 11:10:17');
INSERT INTO `agunan_foto` VALUES (18, 13, 'foto_13_1_1761880959.jpg', 'uploads/2025/10/foto_13_1_1761880959.jpg', '', 76148, 1, NULL, '2025-11-06 12:11:41', '2025-10-31 11:22:39');
INSERT INTO `agunan_foto` VALUES (19, 13, 'foto_13_2_1761880959.jpg', 'uploads/2025/10/foto_13_2_1761880959.jpg', '', 82154, 2, NULL, '2025-11-06 12:11:41', '2025-10-31 11:22:39');
INSERT INTO `agunan_foto` VALUES (20, 13, 'foto_13_3_1761880960.jpg', 'uploads/2025/10/foto_13_3_1761880960.jpg', '', 81525, 3, NULL, '2025-11-06 12:11:41', '2025-10-31 11:22:40');
INSERT INTO `agunan_foto` VALUES (21, 13, 'foto_13_4_1761880960.jpg', 'uploads/2025/10/foto_13_4_1761880960.jpg', '', 82614, 4, NULL, '2025-11-06 12:11:41', '2025-10-31 11:22:40');
INSERT INTO `agunan_foto` VALUES (22, 14, 'foto_14_1_1761881477.jpg', 'uploads/2025/10/foto_14_1_1761881477.jpg', '', 41509, 1, NULL, '2025-11-06 12:11:41', '2025-10-31 11:31:17');
INSERT INTO `agunan_foto` VALUES (23, 14, 'foto_14_2_1761881477.jpg', 'uploads/2025/10/foto_14_2_1761881477.jpg', '', 43055, 2, NULL, '2025-11-06 12:11:41', '2025-10-31 11:31:17');
INSERT INTO `agunan_foto` VALUES (24, 14, 'foto_14_3_1761881478.jpg', 'uploads/2025/10/foto_14_3_1761881478.jpg', '', 42043, 3, NULL, '2025-11-06 12:11:41', '2025-10-31 11:31:18');
INSERT INTO `agunan_foto` VALUES (25, 15, 'foto_15_1_1762415063.jpg', 'uploads/2025/11/foto_15_1_1762415063.jpg', '', 76459, 1, 'admin', '2025-11-06 15:44:23', '2025-11-06 15:44:23');
INSERT INTO `agunan_foto` VALUES (26, 16, 'foto_16_1_1762415087.jpg', 'uploads/2025/11/foto_16_1_1762415087.jpg', '', 44555, 1, 'admin', '2025-11-06 15:44:47', '2025-11-06 15:44:47');
INSERT INTO `agunan_foto` VALUES (27, 17, 'foto_17_1_1762415136.jpg', 'uploads/2025/11/foto_17_1_1762415136.jpg', '', 59423, 1, 'admin', '2025-11-06 15:45:36', '2025-11-06 15:45:36');
INSERT INTO `agunan_foto` VALUES (28, 18, 'foto_18_1_1762415584.jpg', 'uploads/2025/11/foto_18_1_1762415584.jpg', '', 75343, 1, 'admin', '2025-11-06 15:53:04', '2025-11-06 15:53:04');
INSERT INTO `agunan_foto` VALUES (29, 18, 'foto_18_2_1762415585.jpg', 'uploads/2025/11/foto_18_2_1762415585.jpg', '', 54275, 2, 'admin', '2025-11-06 15:53:05', '2025-11-06 15:53:05');
INSERT INTO `agunan_foto` VALUES (30, 18, 'foto_18_3_1762415585.jpg', 'uploads/2025/11/foto_18_3_1762415585.jpg', '', 61238, 3, 'admin', '2025-11-06 15:53:05', '2025-11-06 15:53:05');
INSERT INTO `agunan_foto` VALUES (31, 19, 'foto_19_1_1762416310.jpg', 'uploads/2025/11/foto_19_1_1762416310.jpg', '', 84113, 1, 'admin', '2025-11-06 16:05:10', '2025-11-06 16:05:10');
INSERT INTO `agunan_foto` VALUES (32, 19, 'foto_19_2_1762416310.jpg', 'uploads/2025/11/foto_19_2_1762416310.jpg', '', 74590, 2, 'admin', '2025-11-06 16:05:10', '2025-11-06 16:05:10');
INSERT INTO `agunan_foto` VALUES (33, 19, 'foto_19_3_1762416310.jpg', 'uploads/2025/11/foto_19_3_1762416310.jpg', '', 78677, 3, 'admin', '2025-11-06 16:05:10', '2025-11-06 16:05:10');
INSERT INTO `agunan_foto` VALUES (34, 19, 'foto_19_4_1762416311.jpg', 'uploads/2025/11/foto_19_4_1762416311.jpg', '', 73364, 4, 'admin', '2025-11-06 16:05:11', '2025-11-06 16:05:11');
INSERT INTO `agunan_foto` VALUES (35, 19, 'foto_19_5_1762416311.jpg', 'uploads/2025/11/foto_19_5_1762416311.jpg', '', 79009, 5, 'admin', '2025-11-06 16:05:11', '2025-11-06 16:05:11');
INSERT INTO `agunan_foto` VALUES (53, 23, 'foto_23_1_1763020376.jpg', 'uploads/2025/11/foto_23_1_1763020376.jpg', '', 45452, 1, 'selong@bprntb.co.id', '2025-11-13 15:52:56', '2025-11-13 15:52:56');
INSERT INTO `agunan_foto` VALUES (43, 21, 'foto_21_1_1763012616.jpg', 'uploads/2025/11/foto_21_1_1763012616.jpg', '', 53690, 1, 'gerung@bprntb.co.id', '2025-11-13 13:43:36', '2025-11-13 13:43:36');
INSERT INTO `agunan_foto` VALUES (44, 21, 'foto_21_2_1763012617.jpg', 'uploads/2025/11/foto_21_2_1763012617.jpg', '', 53553, 2, 'gerung@bprntb.co.id', '2025-11-13 13:43:37', '2025-11-13 13:43:37');
INSERT INTO `agunan_foto` VALUES (45, 21, 'foto_21_3_1763012617.jpg', 'uploads/2025/11/foto_21_3_1763012617.jpg', '', 52171, 3, 'gerung@bprntb.co.id', '2025-11-13 13:43:37', '2025-11-13 13:43:37');
INSERT INTO `agunan_foto` VALUES (46, 21, 'foto_21_4_1763012618.jpg', 'uploads/2025/11/foto_21_4_1763012618.jpg', '', 56031, 4, 'gerung@bprntb.co.id', '2025-11-13 13:43:38', '2025-11-13 13:43:38');
INSERT INTO `agunan_foto` VALUES (47, 21, 'foto_21_5_1763012618.jpg', 'uploads/2025/11/foto_21_5_1763012618.jpg', '', 56645, 5, 'gerung@bprntb.co.id', '2025-11-13 13:43:38', '2025-11-13 13:43:38');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `password` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `nama_kc` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `kode_kantor` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `last_login_at` datetime NULL DEFAULT NULL COMMENT 'Terakhir login',
  `last_login_ip` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'IP address terakhir login',
  `login_count` int(11) NULL DEFAULT 0 COMMENT 'Jumlah total login',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Tanggal user dibuat',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_last_login`(`last_login_at`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 46 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'admin', 'admin', 'kpno', '000', '2025-11-13 14:42:13', '::1', 26, '2025-11-06 12:11:41', '2025-11-13 14:42:13');
INSERT INTO `user` VALUES (2, 'pokp@bprntb.co.id', 'bprntb!', 'POKP', '001', '2025-11-13 15:30:56', '::1', NULL, '2025-11-07 12:11:41', '2025-11-13 15:30:56');
INSERT INTO `user` VALUES (3, 'gerung@bprntb.co.id', 'bprntb!', 'KC GERUNG', '002', '2025-11-13 13:40:38', '::1', NULL, '2025-11-08 12:11:41', '2025-11-13 13:40:38');
INSERT INTO `user` VALUES (4, 'narmada@bprntb.co.id', 'bprntb!', 'KC NARMADA', '003', '2025-11-10 09:17:19', NULL, NULL, '2025-11-09 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (5, 'labuapi@bprntb.co.id', 'bprntb!', 'KC LABUAPI', '004', '2025-11-11 09:17:19', NULL, NULL, '2025-11-10 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (6, 'kuripan@bprntb.co.id', 'bprntb!', 'KC KURIPAN', '005', '2025-11-12 09:17:19', NULL, NULL, '2025-11-11 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (7, 'gunungsari@bprntb.co.id', 'bprntb!', 'KC GUNUNG SARI', '006', '2025-11-13 09:17:19', NULL, NULL, '2025-11-12 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (8, 'kayangan@bprntb.co.id', 'bprntb!', 'KC KAYANGAN', '007', '2025-11-14 09:17:19', NULL, NULL, '2025-11-13 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (9, 'bayan@bprntb.co.id', 'bprntb!', 'KC BAYAN', '008', '2025-11-15 09:17:19', NULL, NULL, '2025-11-14 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (10, 'praya@bprntb.co.id', 'bprntb!', 'KC PARAYA', '009', '2025-11-16 09:17:19', NULL, NULL, '2025-11-15 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (11, 'prayatimur@bprntb.co.id', 'bprntb!', 'KC PRAYA TIMUR', '010', '2025-11-17 09:17:19', NULL, NULL, '2025-11-16 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (12, 'janapria@bprntb.co.id', 'bprntb!', 'KC JANAPRIA', '011', '2025-11-18 09:17:19', NULL, NULL, '2025-11-17 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (13, 'batukliang@bprntb.co.id', 'bprntb!', 'KC BATUKLIANG', '012', '2025-11-19 09:17:19', NULL, NULL, '2025-11-18 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (14, 'pujut@bprntb.co.id', 'bprntb!', 'KC PUJUT', '013', '2025-11-20 09:17:19', NULL, NULL, '2025-11-19 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (15, 'jonggat@bprntb.co.id', 'bprntb!', 'KC JONGGAT', '014', '2025-11-21 09:17:19', NULL, NULL, '2025-11-20 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (16, 'kopang@bprntb.co.id', 'bprntb!', 'KC KOPANG', '015', '2025-11-22 09:17:19', NULL, NULL, '2025-11-21 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (17, 'prayabarat@bprntb.co.id', 'bprntb!', 'KC PRAYABARAT', '016', '2025-11-23 09:17:19', NULL, NULL, '2025-11-22 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (18, 'pringgarata@bprntb.co.id', 'bprntb!', 'KC PRINGGARATA', '017', '2025-11-24 09:17:19', NULL, NULL, '2025-11-23 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (19, 'selong@bprntb.co.id', 'bprntb!', 'KC SELONG', '018', '2025-11-13 15:52:28', '::1', NULL, '2025-11-24 12:11:41', '2025-11-13 15:52:28');
INSERT INTO `user` VALUES (20, 'montongbetok@bprntb.co.id', 'bprntb!', 'KC MONTONG BETOK', '019', '2025-11-13 15:31:26', '::1', NULL, '2025-11-25 12:11:41', '2025-11-13 15:31:26');
INSERT INTO `user` VALUES (21, 'kotaraja@bprntb.co.id', 'bprntb!', 'KC KOTARAJA', '020', '2025-11-27 09:17:19', NULL, NULL, '2025-11-26 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (22, 'paokmotong@bprntb.co.id', 'bprntb!', 'KC PAOKMOTONG', '021', '2025-11-28 09:17:19', NULL, NULL, '2025-11-27 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (23, 'dasanlekong@bprntb.co.id', 'bprntb!', 'KC DASAN LEKONG', '022', '2025-11-29 09:17:19', NULL, NULL, '2025-11-28 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (24, 'aikmel@bprntb.co.id', 'bprntb!', 'KC AIKMEL', '023', '2025-11-30 09:17:19', NULL, NULL, '2025-11-29 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (25, 'labuhanlombok@bprntb.co.id', 'bprntb!', 'KC LABUHAN LOMBOK', '024', '2025-12-01 09:17:19', NULL, NULL, '2025-11-30 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (26, 'sambelia@bprntb.co.id', 'bprntb!', 'KC SAMBELIA', '025', '2025-12-02 09:17:19', NULL, NULL, '2025-12-01 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (27, 'taliwang@bprntb.co.id', 'bprntb!', 'KC TALIWANG', '026', '2025-12-03 09:17:19', NULL, NULL, '2025-12-02 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (28, 'seteluk@bprntb.co.id', 'bprntb!', 'KC SETELUK', '027', '2025-12-04 09:17:19', NULL, NULL, '2025-12-03 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (29, 'sumbawa@bprntb.co.id', 'bprntb!', 'KC SUMBAWA', '028', '2025-12-05 09:17:19', NULL, NULL, '2025-12-04 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (30, 'empang@bprntb.co.id', 'bprntb!', 'KC EMPANG', '029', '2025-12-06 09:17:19', NULL, NULL, '2025-12-05 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (31, 'plampang@bprntb.co.id', 'bprntb!', 'KC PLAMPANG', '030', '2025-12-07 09:17:19', NULL, NULL, '2025-12-06 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (32, 'lopok@bprntb.co.id', 'bprntb!', 'KC LOPOK', '031', '2025-12-08 09:17:19', NULL, NULL, '2025-12-07 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (33, 'moyo@bprntb.co.id', 'bprntb!', 'KC MOYO', '032', '2025-12-09 09:17:19', NULL, NULL, '2025-12-08 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (34, 'lenangguar@bprntb.co.id', 'bprntb!', 'KC LENANGGUAR', '033', '2025-12-10 09:17:19', NULL, NULL, '2025-12-09 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (35, 'labuhansumbawa@bprntb.co.id', 'bprntb!', 'KC LABUHAN SUMBAWA', '034', '2025-12-11 09:17:19', NULL, NULL, '2025-12-10 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (36, 'utan@bprntb.co.id', 'bprntb!', 'KC UTAN', '035', '2025-12-12 09:17:19', NULL, NULL, '2025-12-11 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (37, 'alas@bprntb.co.id', 'bprntb!', 'KC ALAS', '036', '2025-11-10 11:58:07', '::1', NULL, '2025-12-12 12:11:41', '2025-11-10 11:58:07');
INSERT INTO `user` VALUES (38, 'dompu@bprntb.co.id', 'bprntb!', 'KC DOMPU', '037', '2025-12-14 09:17:19', NULL, NULL, '2025-12-13 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (39, 'montabaru@bprntb.co.id', 'bprntb!', 'KC MONTABARU', '038', '2025-12-15 09:17:19', NULL, NULL, '2025-12-14 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (40, 'soriutu@bprntb.co.id', 'bprntb!', 'KC SORIUTU', '039', '2025-12-16 09:17:19', NULL, NULL, '2025-12-15 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (41, 'rasabou@bprntb.co.id', 'bprntb!', 'KC RASABOU', '040', '2025-12-17 09:17:19', NULL, NULL, '2025-12-16 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (42, 'bima@bprntb.co.id', 'bprntb!', 'KC BIMA', '041', '2025-12-18 09:17:19', NULL, NULL, '2025-12-17 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (43, 'woha@bprntb.co.id', 'bprntb!', 'KC WOHA', '042', '2025-12-19 09:17:19', NULL, NULL, '2025-12-18 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (44, 'bolo@bprntb.co.id', 'bprntb!', 'KC BOLO', '043', '2025-12-20 09:17:19', NULL, NULL, '2025-12-19 12:11:41', '2025-11-07 10:01:21');
INSERT INTO `user` VALUES (45, 'sape@bprntb.co.id', 'bprntb!', 'KC SAPE', '044', '2025-12-21 09:17:19', NULL, NULL, '2025-12-20 12:11:41', '2025-11-07 10:01:21');

-- ----------------------------
-- Table structure for user_login_history
-- ----------------------------
DROP TABLE IF EXISTS `user_login_history`;
CREATE TABLE `user_login_history`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT 'FK ke tabel user',
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `login_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu login',
  `logout_at` datetime NULL DEFAULT NULL COMMENT 'Waktu logout (jika ada)',
  `ip_address` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'IP Address',
  `user_agent` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Browser/Device info',
  `login_status` enum('success','failed') CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT 'success' COMMENT 'Status login',
  `session_duration` int(11) NULL DEFAULT NULL COMMENT 'Durasi session (detik)',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_user_id`(`user_id`) USING BTREE,
  INDEX `idx_login_at`(`login_at`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 58 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = 'Log history login user' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user_login_history
-- ----------------------------
INSERT INTO `user_login_history` VALUES (1, 1, 'admin', '2025-11-06 12:16:32', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (2, 1, 'admin', '2025-11-06 12:20:42', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (3, 1, 'admin', '2025-11-06 12:26:51', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (4, 1, 'admin', '2025-11-06 12:27:02', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (5, 1, 'admin', '2025-11-06 12:28:02', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (6, 1, 'admin', '2025-11-06 15:40:24', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (7, 1, 'admin', '2025-11-06 15:41:39', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (8, 1, 'admin', '2025-11-06 15:52:33', '2025-11-06 16:06:45', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', 852);
INSERT INTO `user_login_history` VALUES (9, 1, 'admin', '2025-11-07 08:54:53', '2025-11-07 08:55:22', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', 29);
INSERT INTO `user_login_history` VALUES (10, 1, 'admin', '2025-11-07 09:00:23', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (11, 1, 'admin', '2025-11-07 09:10:15', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (12, 0, 'agunan', '2025-11-07 09:17:06', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (13, 1, 'admin', '2025-11-07 09:17:19', '2025-11-07 09:26:27', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 548);
INSERT INTO `user_login_history` VALUES (14, 2, 'pokp@bprntb.co.id', '2025-11-07 09:26:35', '2025-11-07 10:05:17', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 2322);
INSERT INTO `user_login_history` VALUES (15, 2, 'pokp@bprntb.co.id', '2025-11-07 10:05:28', '2025-11-07 10:34:48', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 1760);
INSERT INTO `user_login_history` VALUES (16, 1, 'admin', '2025-11-07 11:32:49', '2025-11-07 11:34:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'success', 129);
INSERT INTO `user_login_history` VALUES (17, 1, 'admin', '2025-11-07 15:15:07', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) SamsungBrowser/28.0 Chrome/130.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (18, 1, 'admin', '2025-11-10 11:07:07', '2025-11-10 11:24:14', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 1027);
INSERT INTO `user_login_history` VALUES (19, 1, 'admin', '2025-11-10 11:21:24', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.105.1 Chrome/138.0.7204.251 Electron/37.6.0 Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (20, 1, 'admin', '2025-11-10 11:21:38', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.105.1 Chrome/138.0.7204.251 Electron/37.6.0 Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (21, 1, 'admin', '2025-11-10 11:25:56', '2025-11-10 11:26:05', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 9);
INSERT INTO `user_login_history` VALUES (22, 1, 'admin', '2025-11-10 11:45:33', '2025-11-10 11:46:04', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 31);
INSERT INTO `user_login_history` VALUES (23, 1, 'admin', '2025-11-10 11:56:03', '2025-11-10 11:56:06', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 3);
INSERT INTO `user_login_history` VALUES (24, 0, 'kcalas@bprntb.co.id', '2025-11-10 11:56:23', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (25, 0, 'kcalas@bprntb.co.id', '2025-11-10 11:56:37', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (26, 0, 'alas@bprntb.co.id', '2025-11-10 11:57:55', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (27, 37, 'alas@bprntb.co.id', '2025-11-10 11:58:07', '2025-11-10 11:58:14', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 7);
INSERT INTO `user_login_history` VALUES (28, 0, 'admin', '2025-11-13 08:01:13', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (29, 1, 'admin', '2025-11-13 08:01:19', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (30, 1, 'admin', '2025-11-13 10:11:53', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (31, 3, 'gerung@bprntb.co.id', '2025-11-13 11:27:33', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (32, 1, 'admin', '2025-11-13 11:43:01', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (33, 3, 'gerung@bprntb.co.id', '2025-11-13 11:46:20', '2025-11-13 11:53:25', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 425);
INSERT INTO `user_login_history` VALUES (34, 3, 'gerung@bprntb.co.id', '2025-11-13 11:56:13', '2025-11-13 12:11:50', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', 937);
INSERT INTO `user_login_history` VALUES (35, 3, 'gerung@bprntb.co.id', '2025-11-13 11:56:26', '2025-11-13 12:10:18', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 832);
INSERT INTO `user_login_history` VALUES (36, 1, 'admin', '2025-11-13 12:10:25', '2025-11-13 12:14:23', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 238);
INSERT INTO `user_login_history` VALUES (37, 3, 'gerung@bprntb.co.id', '2025-11-13 12:11:57', '2025-11-13 12:14:16', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', 139);
INSERT INTO `user_login_history` VALUES (38, 3, 'gerung@bprntb.co.id', '2025-11-13 12:14:26', '2025-11-13 12:14:38', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 12);
INSERT INTO `user_login_history` VALUES (39, 2, 'pokp@bprntb.co.id', '2025-11-13 12:14:43', '2025-11-13 12:19:27', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 284);
INSERT INTO `user_login_history` VALUES (40, 0, 'gerung@bprnrb.co.id', '2025-11-13 13:25:59', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (41, 3, 'gerung@bprntb.co.id', '2025-11-13 13:26:25', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (42, 0, 'mailto:pokp@bprntb.co.id', '2025-11-13 13:31:56', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (43, 0, 'pokp@bprntb.co.id', '2025-11-13 13:32:19', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (44, 2, 'pokp@bprntb.co.id', '2025-11-13 13:32:46', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (45, 3, 'gerung@bprntb.co.id', '2025-11-13 13:40:38', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (46, 1, 'admin', '2025-11-13 13:48:11', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (47, 2, 'pokp@bprntb.co.id', '2025-11-13 14:23:38', '2025-11-13 14:38:14', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 876);
INSERT INTO `user_login_history` VALUES (48, 1, 'admin', '2025-11-13 14:42:13', '2025-11-13 14:44:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'success', 108);
INSERT INTO `user_login_history` VALUES (49, 19, 'selong@bprntb.co.id', '2025-11-13 14:44:17', '2025-11-13 14:47:35', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 198);
INSERT INTO `user_login_history` VALUES (50, 19, 'selong@bprntb.co.id', '2025-11-13 14:47:45', '2025-11-13 15:30:52', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 2587);
INSERT INTO `user_login_history` VALUES (51, 19, 'selong@bprntb.co.id', '2025-11-13 15:02:34', NULL, '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Mobile Safari/537.36', 'success', NULL);
INSERT INTO `user_login_history` VALUES (52, 2, 'pokp@bprntb.co.id', '2025-11-13 15:30:56', '2025-11-13 15:31:05', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 9);
INSERT INTO `user_login_history` VALUES (53, 0, 'betok@bprntb.co.id', '2025-11-13 15:31:12', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'failed', NULL);
INSERT INTO `user_login_history` VALUES (54, 20, 'montongbetok@bprntb.co.id', '2025-11-13 15:31:26', '2025-11-13 15:31:36', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 10);
INSERT INTO `user_login_history` VALUES (55, 19, 'selong@bprntb.co.id', '2025-11-13 15:31:41', '2025-11-13 15:45:38', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 837);
INSERT INTO `user_login_history` VALUES (56, 19, 'selong@bprntb.co.id', '2025-11-13 15:45:46', '2025-11-13 15:52:15', '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', 389);
INSERT INTO `user_login_history` VALUES (57, 19, 'selong@bprntb.co.id', '2025-11-13 15:52:28', NULL, '::1', 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G955U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', 'success', NULL);

-- ----------------------------
-- Table structure for voucher_data
-- ----------------------------
DROP TABLE IF EXISTS `voucher_data`;
CREATE TABLE `voucher_data`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trans_id` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'ID Transaksi dari IBS',
  `no_bukti` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nomor Bukti Voucher dari IBS',
  `tgl_trans` date NULL DEFAULT NULL COMMENT 'Tanggal Transaksi dari IBS',
  `uraian` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Uraian Transaksi dari IBS',
  `kode_jurnal` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Kode Jurnal dari IBS',
  `kode_kantor_ibs` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Kode Kantor dari IBS',
  `nama_kantor` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nama Kantor dari app_kode_kantor IBS',
  `alamat_kantor` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Alamat Kantor dari IBS',
  `kota_kantor` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Kota Kantor dari IBS',
  `nama_pimpinan` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nama Pimpinan dari IBS',
  `total_debet` decimal(18, 2) NULL DEFAULT 0.00 COMMENT 'Total Debet dari transaksi_detail',
  `total_kredit` decimal(18, 2) NULL DEFAULT 0.00 COMMENT 'Total Kredit dari transaksi_detail',
  `verified_from_ibs` tinyint(1) NULL DEFAULT 1 COMMENT '1=verified dari IBS, 0=manual',
  `verified_at` datetime NULL DEFAULT NULL COMMENT 'Waktu verifikasi ke IBS',
  `verified_by` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Username yang melakukan verifikasi',
  `user_id` int(11) NOT NULL COMMENT 'FK ke tabel user',
  `photo_taken_by` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'Username fotografer',
  `photo_taken_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu mulai foto',
  `kode_kantor` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'Kode Kantor user yang capture (untuk isolasi data)',
  `nama_kc` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'Nama KC user yang capture',
  `pdf_filename` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Nama file PDF',
  `pdf_path` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Path file PDF',
  `total_foto` int(11) NULL DEFAULT 0 COMMENT 'Jumlah foto',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_trans_id`(`trans_id`) USING BTREE,
  INDEX `idx_no_bukti`(`no_bukti`) USING BTREE,
  INDEX `idx_kode_kantor`(`kode_kantor`) USING BTREE,
  INDEX `idx_tgl_trans`(`tgl_trans`) USING BTREE,
  INDEX `idx_user_id`(`user_id`) USING BTREE,
  INDEX `idx_photo_taken_by`(`photo_taken_by`) USING BTREE,
  INDEX `idx_verified`(`verified_from_ibs`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = 'Data Voucher yang di-capture' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of voucher_data
-- ----------------------------
INSERT INTO `voucher_data` VALUES (4, '69336899', '002-11-25-1646', '2025-11-12', 'BIAYA BBM DR 4404 RB', 'GL', '002', 'Kantor Cabang Gerung', 'Jl. Selaparang No. 08 Gerung Selatan Gerung Lombok Barat', 'Lombok Barat', 'KHAIRUNNISA ARIANY ZAIN', 10000.00, 10000.00, 1, '2025-11-13 11:53:06', 'gerung@bprntb.co.id', 3, '0', '2025-11-13 11:53:06', '002', 'KC GERUNG', 'voucher_69336899_20251113035307.pdf', 'pdf/voucher/2025/11/voucher_69336899_20251113035307.pdf', 2, '2025-11-13 11:53:06', '2025-11-13 11:53:07');
INSERT INTO `voucher_data` VALUES (5, '69336799', '002-11-25-1634', '2025-11-12', 'PEMELIHARAAN KENDARAAN DR 2026 MA', 'GL', '002', 'Kantor Cabang Gerung', 'Jl. Selaparang No. 08 Gerung Selatan Gerung Lombok Barat', 'Lombok Barat', 'KHAIRUNNISA ARIANY ZAIN', 70000.00, 70000.00, 1, '2025-11-13 11:57:42', 'gerung@bprntb.co.id', 3, '0', '2025-11-13 11:57:42', '002', 'KC GERUNG', 'voucher_69336799_20251113035742.pdf', 'pdf/voucher/2025/11/voucher_69336799_20251113035742.pdf', 1, '2025-11-13 11:57:42', '2025-11-13 11:57:42');

-- ----------------------------
-- Table structure for voucher_foto
-- ----------------------------
DROP TABLE IF EXISTS `voucher_foto`;
CREATE TABLE `voucher_foto`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher_data_id` int(11) NOT NULL COMMENT 'FK ke voucher_data',
  `foto_filename` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'Nama file foto',
  `foto_path` varchar(500) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL COMMENT 'Path file foto',
  `file_size` int(11) NULL DEFAULT 0 COMMENT 'Ukuran file (bytes)',
  `foto_order` int(11) NULL DEFAULT 1 COMMENT 'Urutan foto',
  `keterangan` text CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL COMMENT 'Keterangan spesifik per foto (misal: Halaman 1, Lampiran, TTD, dll)',
  `uploaded_by` varchar(50) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL COMMENT 'Username yang upload foto',
  `uploaded_at` datetime NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Waktu upload foto',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_voucher_data_id`(`voucher_data_id`) USING BTREE,
  INDEX `idx_foto_order`(`foto_order`) USING BTREE,
  INDEX `idx_uploaded_by`(`uploaded_by`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 4 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = 'Foto-foto Voucher' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of voucher_foto
-- ----------------------------
INSERT INTO `voucher_foto` VALUES (1, 4, 'voucher_4_1_1763005986.jpg', 'uploads/voucher/2025/11/voucher_4_1_1763005986.jpg', 83240, 1, 'haaman 1 untuk lampiran 1', 'gerung@bprntb.co.id', '2025-11-13 11:53:06', '2025-11-13 11:53:06');
INSERT INTO `voucher_foto` VALUES (2, 4, 'voucher_4_2_1763005986.jpg', 'uploads/voucher/2025/11/voucher_4_2_1763005986.jpg', 83775, 2, 'HALAMAN 2 UNTUK AGUNAN2', 'gerung@bprntb.co.id', '2025-11-13 11:53:06', '2025-11-13 11:53:06');
INSERT INTO `voucher_foto` VALUES (3, 5, 'voucher_5_1_1763006262.jpg', 'uploads/voucher/2025/11/voucher_5_1_1763006262.jpg', 234934, 1, 'DR 2026CMA - FOTO CAPTURE 1', 'gerung@bprntb.co.id', '2025-11-13 11:57:42', '2025-11-13 11:57:42');

-- ----------------------------
-- View structure for vw_agunan_complete
-- ----------------------------
DROP VIEW IF EXISTS `vw_agunan_complete`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vw_agunan_complete` AS select `ad`.`id` AS `id`,`ad`.`id_agunan` AS `id_agunan`,`ad`.`nama_nasabah` AS `nama_nasabah`,`ad`.`no_rek` AS `no_rek`,`ad`.`created_by` AS `created_by`,`ad`.`nama_kc` AS `nama_kc`,`ad`.`pdf_filename` AS `pdf_filename`,`ad`.`pdf_path` AS `pdf_path`,`ad`.`total_foto` AS `total_foto`,`ad`.`created_at` AS `created_at`,count(`af`.`id`) AS `jumlah_foto_aktual` from (`agunan_data` `ad` left join `agunan_foto` `af` on((`ad`.`id` = `af`.`agunan_data_id`))) group by `ad`.`id` order by `ad`.`created_at` desc;

-- ----------------------------
-- View structure for vw_agunan_with_ibs
-- ----------------------------
DROP VIEW IF EXISTS `vw_agunan_with_ibs`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vw_agunan_with_ibs` AS select `ad`.`id` AS `id`,`ad`.`id_agunan` AS `id_agunan`,`ad`.`agunan_id_ibs` AS `agunan_id_ibs`,`ad`.`nama_nasabah` AS `nama_nasabah`,`ad`.`no_rek` AS `no_rek`,`ad`.`kode_jenis_agunan` AS `kode_jenis_agunan`,`ad`.`deskripsi_ringkas` AS `deskripsi_ringkas`,(case when (`ad`.`kode_jenis_agunan` in ('5','6')) then concat('Tanah - ',coalesce(`ad`.`tanah_no_shm`,`ad`.`tanah_no_shgb`,'-')) else concat('Kendaraan - ',coalesce(`ad`.`kend_no_polisi`,'-')) end) AS `jenis_agunan_display`,`ad`.`tanah_no_shm` AS `tanah_no_shm`,`ad`.`tanah_no_shgb` AS `tanah_no_shgb`,`ad`.`tanah_luas` AS `tanah_luas`,`ad`.`tanah_nama_pemilik` AS `tanah_nama_pemilik`,`ad`.`tanah_lokasi` AS `tanah_lokasi`,`ad`.`kend_jenis` AS `kend_jenis`,`ad`.`kend_merk` AS `kend_merk`,`ad`.`kend_no_polisi` AS `kend_no_polisi`,`ad`.`verified_from_ibs` AS `verified_from_ibs`,`ad`.`verified_at` AS `verified_at`,`ad`.`verified_by` AS `verified_by`,`ad`.`photo_taken_by` AS `photo_taken_by`,`ad`.`photo_taken_at` AS `photo_taken_at`,`ad`.`created_by` AS `created_by`,`ad`.`nama_kc` AS `nama_kc`,`ad`.`pdf_filename` AS `pdf_filename`,`ad`.`pdf_path` AS `pdf_path`,count(`af`.`id`) AS `jumlah_foto`,`ad`.`created_at` AS `created_at`,`ad`.`updated_at` AS `updated_at` from (`agunan_data` `ad` left join `agunan_foto` `af` on((`ad`.`id` = `af`.`agunan_data_id`))) group by `ad`.`id` order by `ad`.`created_at` desc;

-- ----------------------------
-- View structure for vw_voucher_with_photos
-- ----------------------------
DROP VIEW IF EXISTS `vw_voucher_with_photos`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `vw_voucher_with_photos` AS select `vd`.`id` AS `id`,`vd`.`trans_id` AS `trans_id`,`vd`.`no_bukti` AS `no_bukti`,`vd`.`tgl_trans` AS `tgl_trans`,`vd`.`uraian` AS `uraian`,`vd`.`kode_jurnal` AS `kode_jurnal`,`vd`.`kode_kantor` AS `kode_kantor`,`vd`.`nama_kantor` AS `nama_kantor`,`vd`.`total_debet` AS `total_debet`,`vd`.`total_kredit` AS `total_kredit`,`vd`.`photo_taken_by` AS `photo_taken_by`,`vd`.`photo_taken_at` AS `photo_taken_at`,`vd`.`pdf_filename` AS `pdf_filename`,`vd`.`pdf_path` AS `pdf_path`,`vd`.`total_foto` AS `total_foto`,count(`vf`.`id`) AS `jumlah_foto_aktual`,`vd`.`created_at` AS `created_at`,`vd`.`updated_at` AS `updated_at` from (`voucher_data` `vd` left join `voucher_foto` `vf` on((`vd`.`id` = `vf`.`voucher_data_id`))) group by `vd`.`id` order by `vd`.`created_at` desc;

SET FOREIGN_KEY_CHECKS = 1;
