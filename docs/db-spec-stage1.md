# Spesifikasi Database — Sistem Informasi Reservasi Fasilitas Gedung (Stage 1: Schema)

ATURAN PALING PENTING: dokumen ini adalah SINGLE SOURCE OF TRUTH. Nama tabel, nama kolom,
dan relasi yang sudah didefinisikan TIDAK BOLEH diubah, di-rename, atau dihapus. Ada 5 delta
minimal (ditandai jelas) — implementasikan spesifikasi ini PERSIS seperti tertulis, ditambah
5 delta tersebut, tidak lebih tidak kurang. Kalau ada bagian ambigu, tanya dulu sebelum menebak.

## Info Database

- Nama Database: db_reservasi_gedung
- DBMS: MySQL 8, Storage Engine InnoDB, Charset utf8mb4, Collation utf8mb4_unicode_ci
- Timezone: Asia/Jakarta

## Standar

- Semua PK: INT AUTO_INCREMENT
- Semua tabel wajib created_at & updated_at
- Semua relasi pakai Foreign Key + INDEX
- Semua FK: ON UPDATE CASCADE, ON DELETE RESTRICT (kecuali disebutkan lain)
- Kolom email identitas: UNIQUE
- Kolom kode identitas: UNIQUE
- Nominal uang: DECIMAL(15,2)
- Password: hashing bcrypt
- Nama tabel & kolom: snake_case

## Tabel & Atribut (11 tabel)

1. **Lantai**: id_lantai (PK), nomor_lantai, gambar_denah, created_at, updated_at
   → hasMany Fasilitas

2. **Fasilitas**: id_fasilitas (PK), id_lantai (FK), kode_fasilitas (Unique), nama_fasilitas,
   kategori_fasilitas, kapasitas, luas, foto, deskripsi, status_aktif, created_at, updated_at
   → belongsTo Lantai, hasMany Tarif_Sewa

3. **Jenis_Sewa**: id_jenis_sewa (PK), satuan, durasi_minimum, created_at, updated_at
   → hasMany Tarif_Sewa
   → **DELTA #4**: satuan pakai ENUM('Jam','Hari','Bulan') — bukan cuma ENUM('Jam','Hari').
     durasi_minimum diisi 3 untuk baris satuan='Bulan' (syarat minimal 3 bulan).

4. **Tarif_Sewa**: id_tarif_sewa (PK), id_fasilitas (FK), id_jenis_sewa (FK), harga,
   status_aktif, created_at, updated_at
   → belongsTo Fasilitas, belongsTo Jenis_Sewa, hasMany Reservasi

5. **Pemesan**: id_pemesan (PK), nama_lengkap, alamat, usia, pekerjaan, no_telepon,
   email (Unique), created_at, updated_at
   → hasMany Reservasi

6. **Admin**: id_admin (PK), nama_admin, email (Unique), password, created_at, updated_at
   → hasMany Reservasi, hasMany Riwayat_Status, hasMany Laporan

7. **Reservasi**: id_reservasi (PK), id_pemesan (FK), id_tarif_sewa (FK), id_admin (FK,
   nullable sampai diproses), kode_reservasi (Unique), tanggal_mulai, tanggal_selesai,
   jam_selesai, durasi, jumlah_pengguna, keperluan, harga_satuan, total_biaya,
   status_reservasi, created_at, updated_at
   → belongsTo Pemesan, belongsTo Tarif_Sewa, belongsTo Admin (nullable)
   → hasMany Dokumen_Persyaratan, hasMany Riwayat_Status, hasOne Faktur
   → **DELTA #1**: tambah kolom jam_mulai TIME NULL (spek asli cuma punya jam_selesai;
     jam_mulai wajib untuk hitung durasi & cek bentrok pada Reservasi Per Jam)
   → **DELTA #2**: tambah kolom kode_transaksi VARCHAR(30) + INDEX (bukan unique), untuk
     mengelompokkan beberapa baris Reservasi dari satu kali checkout ketika pemesan
     memesan lebih dari satu fasilitas sekaligus. kode_reservasi tetap UNIQUE per baris.
   → **DELTA #3**: tambah kolom lock_status ENUM('temporary_hold','pending_approval',
     'confirmed','released') NOT NULL DEFAULT 'temporary_hold', dan lock_expires_at
     DATETIME NULL.
   → **DELTA #5**: tambah kolom tanggal_diproses DATETIME NULL — merepresentasikan atribut
     pada relasi "Memproses" antara Admin dan Reservasi di ERD (relasi 1:N, atribut jatuh
     ke sisi Reservasi). Diisi otomatis saat admin mengubah status_reservasi pertama kali.

8. **Dokumen_Persyaratan**: id_dokumen (PK), id_reservasi (FK), jenis_dokumen, nama_file,
   lokasi_file, tanggal_upload, status_verifikasi, created_at, updated_at
   → belongsTo Reservasi

9. **Riwayat_Status**: id_riwayat (PK), id_reservasi (FK), id_admin (FK), status_sebelumnya,
   status_baru, tanggal_perubahan, keterangan, created_at, updated_at
   → belongsTo Reservasi, belongsTo Admin

10. **Faktur**: id_faktur (PK), id_reservasi (FK, Unique), nomor_faktur (Unique),
    tanggal_faktur, created_at, updated_at
    → belongsTo Reservasi (1:1)

11. **Laporan**: id_laporan (PK), id_admin (FK), tanggal, jenis_laporan, created_at, updated_at
    → belongsTo Admin

## Kardinalitas

- Lantai(1)-N-Fasilitas
- Fasilitas(1)-N-Tarif_Sewa
- Jenis_Sewa(1)-N-Tarif_Sewa
- Pemesan(1)-N-Reservasi
- Admin(1)-N-Reservasi
- Tarif_Sewa(1)-N-Reservasi
- Reservasi(1)-N-Dokumen_Persyaratan
- Reservasi(1)-N-Riwayat_Status
- Admin(1)-N-Riwayat_Status
- Reservasi(1)-1-Faktur
- Admin(1)-N-Laporan

## Constraint / ENUM

- status_aktif: ENUM('Aktif','Tidak Aktif')
- satuan (Jenis_Sewa): ENUM('Jam','Hari','Bulan') — [DELTA #4]
- status_reservasi: ENUM('Menunggu','Disetujui','Ditolak','Selesai','Dibatalkan')
- status_verifikasi: ENUM('Menunggu','Valid','Tidak Valid')
- lock_status (Reservasi): ENUM('temporary_hold','pending_approval','confirmed','released')
  — [DELTA #3]

## Business Rules (level model/validasi, bukan hanya migration)

- Fasilitas & Tarif_Sewa berstatus tidak aktif tidak boleh dipakai untuk reservasi baru.
- Status reservasi hanya boleh berubah sesuai alur: Menunggu → Disetujui/Ditolak,
  Disetujui → Selesai/Dibatalkan, Menunggu → Dibatalkan. Transisi lain ditolak.
- Setiap perubahan status_reservasi WAJIB otomatis membuat baris baru di Riwayat_Status
  dan mengisi tanggal_diproses jika masih null (buat sebagai model event/observer,
  jangan manual di controller).
- Reservasi dengan lock_status='temporary_hold' dan lock_expires_at sudah lewat waktu
  sekarang dianggap TIDAK memblokir ketersediaan fasilitas (tercermin di query scope).

## Tugas Stage 1 (schema saja)

1. Migration Laravel untuk seluruh 11 tabel di atas plus 5 delta, urutan sesuai dependency FK.
2. Model Eloquent untuk tiap tabel dengan relationship sesuai kardinalitas di atas, termasuk:
   - Cast enum-enum di atas dengan benar
   - Scope `scopeTersedia()` di model Reservasi/Tarif_Sewa yang mengabaikan lock
     temporary_hold yang sudah expired
   - Observer/event pada model Reservasi: setiap kali status_reservasi berubah, otomatis
     insert baris ke Riwayat_Status dan isi tanggal_diproses jika masih null
3. Seeder untuk: 5 baris Lantai, beberapa baris Jenis_Sewa (Jam, Hari, Bulan dengan
   durasi_minimum sesuai — Bulan = 3), 1 admin dummy (password di-hash bcrypt).
4. Artisan command `reservasi:release-expired-locks` yang mengubah lock_status baris
   Reservasi yang temporary_hold dan sudah lewat lock_expires_at menjadi 'released'.
   Jelaskan cara menjadwalkannya di scheduler project ini.
5. Jangan buat controller, Form Request, route, atau factory dulu — itu stage berikutnya
   per role (Pemesan lalu Admin). Fokus hanya migration, model, seeder, command di atas.

Setelah selesai, jalankan migration & seeder, laporkan kalau ada error, dan tampilkan
hasil `php artisan migrate:status` sebagai bukti.