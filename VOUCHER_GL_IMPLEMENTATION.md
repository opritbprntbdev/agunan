# Voucher GL System - Implementation Notes

## Overview
System baru untuk capture voucher GL (Jurnal Umum) dengan fitur:
- Filter by kode jurnal, date range, dan no bukti
- Display 1 row per detail (debet/kredit terpisah)
- Status badge per no_bukti (✅ Sudah X foto / ❌ Belum)
- Capture per no_bukti (1 master dengan multiple detail rows)
- Storage menggunakan JSON `verified_data` column (no migration needed)

## File Structure

### Frontend (UI)
- **`ui/voucher_list.php`** - List voucher GL dengan filter (halaman utama)
- **`ui/voucher_capture_new.php`** - Capture foto untuk voucher tertentu
- **`ui/voucher_capture.php`** - System lama (tetap ada untuk compatibility)

### Backend (API)
- **`process/get_voucher_list.php`** - Query list voucher dari IBS + status capture
- **`process/get_voucher_detail.php`** - Query detail 1 voucher by no_bukti
- **`process/upload_voucher_photo.php`** - Upload foto (updated untuk support JSON)
- **`process/finalize_voucher.php`** - Generate PDF (existing, tidak perlu diubah)

## Database Schema

### Table: `voucher_data`
Tidak ada perubahan schema - menggunakan existing columns:
- `trans_id` - IBS transaction ID (may be empty for GL)
- `no_bukti` - Voucher number (PRIMARY KEY untuk GL system)
- `tgl_trans` - Transaction date
- `kode_jurnal` - Journal code (GL, KM, KK, dll)
- `verified_data` - **JSON** dengan structure:
  ```json
  {
    "trans_id": "69336899",
    "no_bukti": "002-GL-00001",
    "tgl_trans": "2025-11-20",
    "kode_jurnal": "GL",
    "kode_kantor": "002",
    "nama_kantor": "KC Bandung",
    "total_debet": 10000,
    "total_kredit": 10000,
    "detail_rows": [
      {
        "kode_perk": "5101.001",
        "debet": 10000,
        "kredit": 0,
        "keterangan": "Biaya operasional"
      },
      {
        "kode_perk": "1101.002",
        "debet": 0,
        "kredit": 10000,
        "keterangan": "Kas kecil"
      }
    ]
  }
  ```

### Table: `voucher_foto`
Tidak ada perubahan - tetap menyimpan foto dengan keterangan per foto.

## API Endpoints

### 1. GET /process/get_voucher_list.php
**Method:** POST  
**Parameters:**
- `kode_jurnal` (string) - Default: 'GL'
- `tgl_from` (date) - Start date YYYY-MM-DD
- `tgl_to` (date) - End date YYYY-MM-DD
- `no_bukti` (string, optional) - Search filter

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "trans_id": "69336899",
      "no_bukti": "002-GL-00001",
      "tgl_trans": "2025-11-20",
      "kode_perk": "5101.001",
      "debet": 10000,
      "kredit": 0,
      "keterangan": "Biaya operasional",
      "has_photos": true,
      "photo_count": 3
    },
    {
      "trans_id": "69336899",
      "no_bukti": "002-GL-00001",
      "tgl_trans": "2025-11-20",
      "kode_perk": "1101.002",
      "debet": 0,
      "kredit": 10000,
      "keterangan": "Kas kecil",
      "has_photos": true,
      "photo_count": 3
    }
  ],
  "count": 2
}
```

### 2. GET /process/get_voucher_detail.php
**Method:** POST  
**Parameters:**
- `no_bukti` (string) - Voucher number

**Response:**
```json
{
  "success": true,
  "data": {
    "trans_id": "69336899",
    "no_bukti": "002-GL-00001",
    "tgl_trans": "2025-11-20",
    "kode_jurnal": "GL",
    "kode_kantor": "002",
    "nama_kantor": "KC Bandung",
    "total_debet": 10000,
    "total_kredit": 10000,
    "detail_rows": [
      {
        "kode_perk": "5101.001",
        "debet": 10000,
        "kredit": 0,
        "keterangan": "Biaya operasional"
      },
      {
        "kode_perk": "1101.002",
        "debet": 0,
        "kredit": 10000,
        "keterangan": "Kas kecil"
      }
    ],
    "jumlah_detail": 2
  }
}
```

### 3. POST /process/upload_voucher_photo.php
**Method:** POST (multipart/form-data)  
**Parameters:**
- `image` (file) - JPEG image
- `no_bukti` (string) - Voucher number
- `keterangan` (string) - Photo description
- `verified_data` (json string) - Full voucher data with detail_rows
- `voucher_data_id` (int, optional) - For subsequent uploads

**Response:**
```json
{
  "success": true,
  "voucher_data_id": 123,
  "foto_id": 456,
  "next_order": 2,
  "foto_path": "uploads/voucher/2025/11/voucher_123_1_1732012345.jpg"
}
```

## IBS Query

```sql
SELECT 
    a.trans_id,
    a.kode_kantor,
    a.kode_jurnal,
    a.no_bukti,
    a.tgl_trans,
    b.kode_perk,
    b.debet,
    b.kredit,
    b.keterangan
FROM transaksi_master a
INNER JOIN transaksi_detail b ON a.trans_id = b.master_id
WHERE a.tgl_trans BETWEEN ? AND ?
  AND a.kode_jurnal = 'GL'
  AND a.kode_kantor = ?
  AND a.no_bukti LIKE ?
ORDER BY a.tgl_trans DESC, a.no_bukti, b.kode_perk
```

## User Flow

1. User akses **voucher_list.php**
2. Set filter: Kode Jurnal (GL), Periode (current month), Search (optional)
3. Klik **Search** → API call ke `get_voucher_list.php`
4. Display table:
   - 1 row per detail (separate debet/kredit rows)
   - Status badge: ✅ Sudah X foto (green) atau ❌ Belum (yellow)
   - Action button: 📸 Foto (new) atau 🔄 Update (existing)
5. User klik **📸 Foto** → redirect ke `voucher_capture_new.php?no_bukti=XXX`
6. Auto load voucher detail → display info + detail table
7. Open camera → user ambil foto dengan keterangan
8. Finish → review batch → upload semua foto
9. Upload ke `upload_voucher_photo.php`:
   - First upload: create `voucher_data` record dengan `verified_data` JSON
   - Subsequent uploads: append to existing record
10. Finalize → generate PDF via `finalize_voucher.php`
11. Redirect back to **voucher_list.php** → status updated ✅

## Key Differences from Old System

| Aspect | Old System | New System |
|--------|-----------|------------|
| Entry point | Trans ID input | List with filters |
| Query source | `transaksi_master` only | `transaksi_master` JOIN `transaksi_detail` |
| Display | Summary only | Detail rows (1 row per debet/kredit) |
| Capture key | trans_id | no_bukti |
| Status | No status display | Badge per no_bukti |
| Data storage | Flat fields | JSON with detail_rows array |
| Filter | Manual trans_id | Dropdown + date + search |

## Migration Status

**NO MIGRATION NEEDED** ✅

Existing `voucher_data` table structure is sufficient:
- Use `no_bukti` as primary identifier (already indexed)
- Store detail rows in `verified_data` JSON column
- No new columns required
- Backward compatible with old system

## Testing Checklist

- [ ] Load voucher list dengan filter berbeda
- [ ] Cek status badge (belum vs sudah capture)
- [ ] Capture voucher baru (create new record)
- [ ] Update voucher existing (append photos)
- [ ] Verify JSON `verified_data` structure di database
- [ ] Generate PDF dan cek detail rows displayed
- [ ] Test date range filter (current month, custom range)
- [ ] Test no_bukti search (partial match)
- [ ] Test dengan multiple detail rows per voucher
- [ ] Verify camera switch (back/front)
- [ ] Test foto keterangan per foto
- [ ] Test upload progress bar
- [ ] Test redirect flow (list → capture → list)

## Future Enhancements

1. **Expand journal types**: Add KM (Kas Masuk), KK (Kas Keluar), BM, BK to dropdown
2. **Batch actions**: Select multiple vouchers to capture in sequence
3. **Export to Excel**: Download filtered voucher list
4. **Advanced search**: Filter by kode_perk, debet/kredit amount
5. **Statistics dashboard**: Show capture progress per period
6. **Multi-branch admin**: Admin (kode_kantor='000') can see all branches

## Notes

- IBS database is **READ ONLY** - no INSERT/UPDATE to IBS
- Local database captures photos and metadata
- PDF generation uses existing `finalize_voucher.php` (no changes needed)
- Session `kode_kantor` filters data per branch (isolation)
- Prepared statement limit reached on IBS → use `real_escape_string()` + `query()`
- JSON storage avoids data duplication (1 no_bukti = 1 master row)
- Status matching uses `no_bukti` as unique key across detail rows

## Deployment

1. Upload new files:
   - `ui/voucher_list.php`
   - `ui/voucher_capture_new.php`
   - `process/get_voucher_list.php`
   - `process/get_voucher_detail.php`
2. Update existing file:
   - `process/upload_voucher_photo.php` (add JSON support)
   - `home.php` (add menu link)
3. No database migration required
4. Test with real GL data
5. Deploy to production

---

**Created:** 2025-11-24  
**Author:** GitHub Copilot  
**Status:** ✅ Implementation Complete
