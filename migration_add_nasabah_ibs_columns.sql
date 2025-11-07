/*
 Migration: Add CIF, Nama Nasabah, Alamat from IBS
 Date: 2025-11-06
 Description: Menambah kolom untuk menyimpan data nasabah dari Core Banking IBS
*/

-- Tambah 3 kolom untuk data nasabah dari IBS
ALTER TABLE agunan_data 
ADD COLUMN cif VARCHAR(20) NULL COMMENT 'CIF Nasabah dari IBS' AFTER kode_jenis_agunan,
ADD COLUMN nama_nasabah_ibs VARCHAR(200) NULL COMMENT 'Nama Nasabah dari tabel nasabah IBS' AFTER cif,
ADD COLUMN alamat_nasabah_ibs TEXT NULL COMMENT 'Alamat Nasabah dari IBS' AFTER nama_nasabah_ibs;

-- Tambah index untuk performa query
CREATE INDEX idx_cif ON agunan_data(cif);

-- Cek hasil
SHOW COLUMNS FROM agunan_data LIKE '%ibs%';
SHOW COLUMNS FROM agunan_data WHERE Field IN ('cif', 'nama_nasabah_ibs', 'alamat_nasabah_ibs');
