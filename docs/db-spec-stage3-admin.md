# Stage 3 — Alur Admin

Baca db-spec-stage1.md dan db-spec-stage2-pemesan.md dulu untuk konteks schema dan alur
pemesan yang sudah dibangun. Jangan ubah apa pun dari dua stage sebelumnya kecuali memang
perlu (jelaskan alasannya kalau ada).

## Autentikasi Admin

Tabel `Admin` terpisah dari tabel `users` bawaan Laravel (tidak punya kolom users biasa).
Buat guard & provider baru khusus untuk model Admin:
- config/auth.php: tambah guard `admin` (driver session) dengan provider yang mengarah ke
  model App\Models\Admin (eloquent, password sudah di-hash bcrypt sesuai Stage 1 seeder).
- Middleware `auth:admin` untuk melindungi seluruh route /admin/*.
- Halaman login sederhana di /admin/login (email + password), logout di /admin/logout.
- Tidak perlu halaman registrasi — akun admin dibuat lewat seeder saja.

## Dashboard (/admin/dashboard)

Tampilkan ringkasan:
- Jumlah total baris Reservasi
- Jumlah status_reservasi = 'Menunggu'
- Jumlah status_reservasi = 'Disetujui'
- Jumlah status_reservasi = 'Ditolak'
- Jumlah status_reservasi = 'Dibatalkan'
- Jumlah Fasilitas aktif per kategori_fasilitas (Working Space / Co-Working Space /
  Convention Hall)
- 10 Reservasi terbaru (order by created_at desc), tampilkan kode_reservasi, nama_lengkap
  pemesan, nama_fasilitas, status_reservasi

## Monitoring Fasilitas (/admin/monitoring)

- Pilih lantai (dropdown: 1, 2, 3A, 3B, 5) → tampilkan seluruh Fasilitas di lantai itu.
- Pilih tanggal (dan jam untuk fasilitas yang punya Tarif_Sewa jenis Jam) → untuk tiap
  Fasilitas, hitung status ketersediaan pada tanggal/jam tsb menggunakan
  AvailabilityService yang sudah dibuat di Stage 2:
  - Hijau: tidak ada Reservasi aktif (Menunggu/Disetujui) yang bentrok pada rentang itu,
    dan status_aktif Fasilitas = 'Aktif'
  - Merah: seluruh rentang bentrok dengan Reservasi aktif, ATAU status_aktif = 'Tidak Aktif'
  - Kuning: sebagian rentang bentrok (relevan untuk Harian/Bulanan saat admin melihat
    rentang tanggal lebar)
- Klik satu Fasilitas → tampilkan detail: nama, kategori, kode, lantai, kapasitas, luas
  (jika ada), jadwal Reservasi yang sedang aktif pada fasilitas itu (tanpa menampilkan
  seluruh riwayat lama, cukup yang relevan dengan tanggal yang sedang dilihat).

## Data Reservasi (/admin/reservasi)

- Tabel seluruh baris Reservasi, dikelompokkan tampilan berdasarkan kode_transaksi (baris-baris
  dengan kode_transaksi sama ditampilkan sebagai satu grup collapsible/expandable), kolom:
  kode_reservasi, nama_fasilitas (join lewat Tarif_Sewa → Fasilitas), jenis_sewa, periode
  (tanggal_mulai s/d tanggal_selesai, atau jam_mulai-jam_selesai jika per jam), total_biaya,
  status_reservasi, aksi.
- Search & filter: nama pemesan, kategori_fasilitas, nama_fasilitas, lantai, tanggal
  reservasi, jenis_sewa, status_reservasi (termasuk 'Dibatalkan').

### Detail & verifikasi satu baris Reservasi (/admin/reservasi/{kode_reservasi})

Tampilkan: identitas Pemesan lengkap, detail Fasilitas & foto, jenis sewa, periode,
durasi, harga_satuan, total_biaya, jumlah_pengguna, keperluan, daftar Dokumen_Persyaratan
(jika ada, dengan tombol lihat file & set status_verifikasi per dokumen: Menunggu/Valid/
Tidak Valid), serta daftar baris Reservasi lain dengan kode_transaksi yang sama (kalau
pemesan ini memesan lebih dari satu fasilitas di transaksi yang sama).

Validasi yang HARUS dicek sebelum admin bisa menekan tombol Setujui (tampilkan sebagai
checklist otomatis di halaman, bukan hanya validasi backend):
- Tidak bentrok dengan Reservasi lain yang statusnya Menunggu/Disetujui pada fasilitas &
  periode yang sama.
- jenis_sewa sesuai kategori_fasilitas (Convention Hall hanya boleh Hari).
- Jika jenis_sewa = Bulan: durasi >= durasi_minimum dari Jenis_Sewa (3 bulan), dan seluruh
  Dokumen_Persyaratan wajib berstatus_verifikasi = 'Valid'.
- jumlah_pengguna tidak melebihi kapasitas Fasilitas.

**Tombol Setujui**: hanya aktif kalau semua checklist di atas lolos. Saat ditekan:
- status_reservasi → 'Disetujui'
- id_admin → admin yang sedang login
- lock_status → 'confirmed'
- (tanggal_diproses & baris Riwayat_Status otomatis terisi lewat Observer dari Stage 1,
  tidak perlu ditulis manual di controller)

**Tombol Tolak**: wajib isi alasan (textarea) sebelum submit, alasan disimpan ke kolom
`keterangan` pada baris Riwayat_Status yang dibuat otomatis oleh Observer. Saat ditekan:
- status_reservasi → 'Ditolak'
- id_admin → admin yang sedang login
- lock_status → 'released'

## Pembatalan oleh pemesan, sisi admin

Ini sudah diimplementasikan sebagian di Stage 2 (endpoint pembatalan pemesan). Pastikan di
sisi admin: status 'Dibatalkan' muncul di filter Data Reservasi, dan detail Riwayat_Status
menunjukkan siapa yang membatalkan (id_admin null jika dibatalkan oleh pemesan sendiri,
bedakan dengan pembatalan oleh admin jika ada fitur itu — untuk saat ini pembatalan hanya
dari sisi pemesan sesuai Stage 2, admin cuma melihat).

## Faktur (/admin/reservasi/{kode_reservasi}/faktur)

Perhatian: skema `Faktur` adalah relasi 1:1 dengan `Reservasi` (satu Faktur per satu baris
Reservasi/fasilitas, BUKAN per kode_transaksi), karena constraint id_reservasi UNIQUE di
tabel Faktur. Jangan coba gabungkan beberapa Reservasi ke satu baris Faktur.

- Tombol "Cetak Faktur" hanya muncul untuk baris Reservasi berstatus 'Disetujui' dan yang
  belum punya Faktur.
- Saat ditekan: buat baris Faktur baru (nomor_faktur di-generate otomatis & unik, misal
  format INV/{tahun}/{urutan}), tanggal_faktur = now().
- Kalau satu kode_transaksi berisi beberapa Reservasi yang sudah disetujui dan admin
  ingin mencetak semuanya sekaligus, buat tombol "Cetak Semua Faktur di Transaksi Ini" yang
  men-generate satu baris Faktur per Reservasi (tetap terpisah di database) tapi digabung
  jadi satu file PDF multi-halaman untuk kenyamanan cetak.
- PDF berisi: nomor_faktur, kode_reservasi, identitas pemesan, nama fasilitas, jenis
  fasilitas, jenis sewa, periode, total biaya, status, tanggal_diproses, nama admin yang
  menyetujui. Gunakan package barryvdh/laravel-dompdf (install kalau belum ada) untuk
  generate PDF, atau cek dulu apakah project sudah punya package PDF lain yang terpasang
  dan pakai itu supaya konsisten.

## Laporan (/admin/laporan)

- Form filter: rentang tanggal (tanggal_mulai/tanggal_selesai reservasi), lantai,
  kategori_fasilitas, jenis_sewa, status_reservasi.
- Tabel hasil: nomor urut, nama_fasilitas, kategori_fasilitas, lantai, kapasitas, harga
  (dari Tarif_Sewa aktif), jumlah reservasi pada fasilitas itu dalam periode filter,
  jumlah yang dibatalkan, status penggunaan saat ini (pakai AvailabilityService).
- Tombol "Ekspor PDF": generate PDF dengan tampilan sama seperti tabel di halaman, DAN
  insert satu baris ke tabel `Laporan` (id_admin = admin login, tanggal = now(),
  jenis_laporan = deskripsi singkat filter yang dipakai, misal "Rekap Lantai 3A —
  Jan 2026").

## Yang TIDAK dikerjakan di stage ini

- Tidak ada fitur admin menambah/mengedit Fasilitas atau Tarif_Sewa lewat UI (data sudah
  final dari seeder di db-spec-fasilitas-seeder.md). Kalau nanti dibutuhkan CRUD Fasilitas,
  itu jadi stage terpisah.
- Tidak ada manajemen akun admin lain (tambah/hapus admin) lewat UI.

Setelah selesai, tunjukkan `php artisan route:list --path=admin` dan jalankan aplikasi
untuk memastikan halaman login admin bisa diakses tanpa error. Laporkan kalau ada error.