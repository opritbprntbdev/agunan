-- =====================================================
-- QUERY MONITORING: Multi-Branch Isolation
-- =====================================================

-- 1. CEK USER DAN KODE KANTOR
SELECT id, username, nama_kc, kode_kantor, last_login_at, login_count 
FROM user 
ORDER BY kode_kantor, id;

-- 2. CEK AGUNAN DATA PER KANTOR
SELECT kode_kantor, COUNT(*) as total_agunan, SUM(total_foto) as total_foto
FROM agunan_data
GROUP BY kode_kantor
ORDER BY kode_kantor;

-- 3. LIHAT DATA AGUNAN DETAIL PER KANTOR (contoh: kode 002 = Gerung)
SELECT id, id_agunan, nama_nasabah, kode_kantor, nama_kc, created_by, created_at
FROM agunan_data
WHERE kode_kantor = '002'
ORDER BY created_at DESC;

-- 4. LIHAT SEMUA AGUNAN (untuk admin 000)
SELECT id, id_agunan, nama_nasabah, kode_kantor, nama_kc, created_by, created_at
FROM agunan_data
ORDER BY kode_kantor, created_at DESC;

-- 5. COUNT AGUNAN PER USER
SELECT u.kode_kantor, u.nama_kc, u.username, COUNT(ad.id) as total_agunan
FROM user u
LEFT JOIN agunan_data ad ON u.username = ad.created_by
GROUP BY u.kode_kantor, u.nama_kc, u.username
ORDER BY u.kode_kantor;

-- 6. CEK DATA YANG VERIFIED DARI IBS PER KANTOR
SELECT kode_kantor, 
       COUNT(*) as total,
       SUM(CASE WHEN verified_from_ibs = 1 THEN 1 ELSE 0 END) as verified_ibs,
       SUM(CASE WHEN verified_from_ibs = 0 THEN 1 ELSE 0 END) as manual
FROM agunan_data
GROUP BY kode_kantor
ORDER BY kode_kantor;

-- 7. LIHAT LOGIN HISTORY PER KANTOR
SELECT ulh.username, u.kode_kantor, u.nama_kc, ulh.login_at, ulh.ip_address, 
       TIMESTAMPDIFF(MINUTE, ulh.login_at, ulh.logout_at) as duration_minutes
FROM user_login_history ulh
INNER JOIN user u ON ulh.user_id = u.id
ORDER BY ulh.login_at DESC
LIMIT 50;

-- 8. TEST ISOLATION: Simulasi query dari user cabang (contoh Gerung = 002)
SET @kode_kantor_login = '002';

SELECT * FROM agunan_data 
WHERE kode_kantor = @kode_kantor_login
ORDER BY created_at DESC;

-- 9. TEST ADMIN: Simulasi query dari admin pusat (000)
SET @kode_kantor_login = '000';

-- Admin bisa lihat semua (tidak ada WHERE kode_kantor)
SELECT * FROM agunan_data 
ORDER BY created_at DESC;
