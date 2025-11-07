/*
 Migration: Add Kode Kantor for Multi-Branch Isolation
 Date: 2025-11-07
 Description: Menambah kode_kantor untuk isolasi data per cabang
*/

-- ============================================================
-- 1. TAMBAH KOLOM kode_kantor DI TABEL user
-- ============================================================
ALTER TABLE user 
ADD COLUMN kode_kantor CHAR(3) NOT NULL DEFAULT '000' COMMENT 'Kode Kantor (000=Pusat, 001-044=Cabang)' AFTER nama_kc;

-- Tambah index
CREATE INDEX idx_kode_kantor ON user(kode_kantor);

-- ============================================================
-- 2. UPDATE EXISTING USERS dengan Kode Kantor
-- ============================================================
UPDATE user SET kode_kantor = '000' WHERE id = 1;  -- admin kpno
UPDATE user SET kode_kantor = '001' WHERE id = 2;  -- pokp@bprntb.co.id
UPDATE user SET kode_kantor = '002' WHERE id = 3;  -- gerung@bprntb.co.id
UPDATE user SET kode_kantor = '003' WHERE id = 4;  -- narmada@bprntb.co.id
UPDATE user SET kode_kantor = '004' WHERE id = 5;  -- labuapi@bprntb.co.id
UPDATE user SET kode_kantor = '005' WHERE id = 6;  -- kuripan@bprntb.co.id
UPDATE user SET kode_kantor = '006' WHERE id = 7;  -- gunungsari@bprntb.co.id
UPDATE user SET kode_kantor = '007' WHERE id = 8;  -- kayangan@bprntb.co.id
UPDATE user SET kode_kantor = '008' WHERE id = 9;  -- bayan@bprntb.co.id
UPDATE user SET kode_kantor = '009' WHERE id = 10; -- praya@bprntb.co.id
UPDATE user SET kode_kantor = '010' WHERE id = 11; -- prayatimur@bprntb.co.id
UPDATE user SET kode_kantor = '011' WHERE id = 12; -- janapria@bprntb.co.id
UPDATE user SET kode_kantor = '012' WHERE id = 13; -- batukliang@bprntb.co.id
UPDATE user SET kode_kantor = '013' WHERE id = 14; -- pujut@bprntb.co.id
UPDATE user SET kode_kantor = '014' WHERE id = 15; -- jonggat@bprntb.co.id
UPDATE user SET kode_kantor = '015' WHERE id = 16; -- kopang@bprntb.co.id
UPDATE user SET kode_kantor = '016' WHERE id = 17; -- prayabarat@bprntb.co.id
UPDATE user SET kode_kantor = '017' WHERE id = 18; -- pringgarata@bprntb.co.id
UPDATE user SET kode_kantor = '018' WHERE id = 19; -- selong@bprntb.co.id
UPDATE user SET kode_kantor = '019' WHERE id = 20; -- montongbetok@bprntb.co.id
UPDATE user SET kode_kantor = '020' WHERE id = 21; -- kotaraja@bprntb.co.id
UPDATE user SET kode_kantor = '021' WHERE id = 22; -- paokmotong@bprntb.co.id
UPDATE user SET kode_kantor = '022' WHERE id = 23; -- dasanlekong@bprntb.co.id
UPDATE user SET kode_kantor = '023' WHERE id = 24; -- aikmel@bprntb.co.id
UPDATE user SET kode_kantor = '024' WHERE id = 25; -- labuhanlombok@bprntb.co.id
UPDATE user SET kode_kantor = '025' WHERE id = 26; -- sambelia@bprntb.co.id
UPDATE user SET kode_kantor = '026' WHERE id = 27; -- taliwang@bprntb.co.id
UPDATE user SET kode_kantor = '027' WHERE id = 28; -- seteluk@bprntb.co.id
UPDATE user SET kode_kantor = '028' WHERE id = 29; -- sumbawa@bprntb.co.id
UPDATE user SET kode_kantor = '029' WHERE id = 30; -- empang@bprntb.co.id
UPDATE user SET kode_kantor = '030' WHERE id = 31; -- plampang@bprntb.co.id
UPDATE user SET kode_kantor = '031' WHERE id = 32; -- lopok@bprntb.co.id
UPDATE user SET kode_kantor = '032' WHERE id = 33; -- moyo@bprntb.co.id
UPDATE user SET kode_kantor = '033' WHERE id = 34; -- lenangguar@bprntb.co.id
UPDATE user SET kode_kantor = '034' WHERE id = 35; -- labuhansumbawa@bprntb.co.id
UPDATE user SET kode_kantor = '035' WHERE id = 36; -- utan@bprntb.co.id
UPDATE user SET kode_kantor = '036' WHERE id = 37; -- alas@bprntb.co.id
UPDATE user SET kode_kantor = '037' WHERE id = 38; -- dompu@bprntb.co.id
UPDATE user SET kode_kantor = '038' WHERE id = 39; -- montabaru@bprntb.co.id
UPDATE user SET kode_kantor = '039' WHERE id = 40; -- soriutu@bprntb.co.id
UPDATE user SET kode_kantor = '040' WHERE id = 41; -- rasabou@bprntb.co.id
UPDATE user SET kode_kantor = '041' WHERE id = 42; -- bima@bprntb.co.id
UPDATE user SET kode_kantor = '042' WHERE id = 43; -- woha@bprntb.co.id
UPDATE user SET kode_kantor = '043' WHERE id = 44; -- bolo@bprntb.co.id
UPDATE user SET kode_kantor = '044' WHERE id = 45; -- sape@bprntb.co.id

-- ============================================================
-- 3. TAMBAH KOLOM kode_kantor DI TABEL agunan_data
-- ============================================================
ALTER TABLE agunan_data 
ADD COLUMN kode_kantor CHAR(3) NOT NULL DEFAULT '000' COMMENT 'Kode Kantor pemilik data' AFTER nama_kc;

-- Tambah index
CREATE INDEX idx_agunan_kode_kantor ON agunan_data(kode_kantor);

-- ============================================================
-- 4. UPDATE DATA EXISTING (opsional - sesuaikan dengan kondisi real)
-- ============================================================
-- Jika ada data lama, bisa di-set ke pusat dulu:
-- UPDATE agunan_data SET kode_kantor = '000' WHERE kode_kantor = '';

-- Atau match dengan created_by:
-- UPDATE agunan_data ad 
-- INNER JOIN user u ON ad.created_by = u.username 
-- SET ad.kode_kantor = u.kode_kantor;

-- ============================================================
-- 5. CEK HASIL
-- ============================================================
SELECT id, username, nama_kc, kode_kantor FROM user ORDER BY id;
SHOW COLUMNS FROM agunan_data LIKE 'kode_kantor';
