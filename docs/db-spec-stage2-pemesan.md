# Stage 2 — Alur Pemesan (Public, tanpa login)

Baca dulu `db-spec-stage1.md` di root project ini untuk konteks schema, model, dan 5 delta
yang sudah diimplementasikan di Stage 1. Jangan ubah migration/model yang sudah ada di Stage 1
kecuali memang perlu (jelaskan alasannya kalau ada perubahan).

## Konteks alur (ringkas dari dokumen alur cerita)

Pemesan mengakses situs tanpa login. Alur: pilih jenis fasilitas (kategori_fasilitas) →
pilih jenis sewa (Jam/Hari/Bulan, tergantung fasilitas — Convention Hall hanya Hari) →
pilih lantai → lihat denah fasilitas per lantai dengan indikator warna → pilih fasilitas
tersedia → isi jadwal → fasilitas masuk "keranjang" → bisa tambah fasilitas lain →
checkout: isi data diri sekali untuk semua fasilitas di keranjang → submit → dapat
kode_transaksi + kode_reservasi per fasilitas, status Menunggu.

## Keputusan desain penting: locking pra-checkout pakai Cache, bukan tabel

Karena `Reservasi.id_pemesan` wajib diisi (FK NOT NULL) dan data pemesan baru ada di tahap
checkout, hold sementara SEBELUM checkout tidak boleh berupa baris `Reservasi` sungguhan.
Gunakan Laravel Cache untuk hold pra-checkout:

- Key format: `hold:{id_fasilitas}:{id_tarif_sewa}:{tanggal_mulai}:{jam_mulai}` (sertakan
  tanggal_selesai/jam_selesai di value jika relevan untuk Harian/Bulanan).
- Value: identifier unik per browser session (pakai Laravel session ID), supaya hold milik
  session lain dianggap terkunci tapi hold milik session sendiri tetap bisa dilanjut/diedit.
- TTL: 15 menit, otomatis expired oleh Cache driver sendiri (tidak perlu artisan command
  terpisah untuk fase ini — command `reservasi:release-expired-locks` dari Stage 1 tetap
  dipakai khusus untuk baris `Reservasi` yang sudah lock_status='temporary_hold' di DB,
  yaitu SETELAH checkout tapi belum tentu applicable di sini karena checkout langsung set
  'pending_approval'. Sebutkan di komentar kode bahwa kolom lock_status='temporary_hold'
  saat ini disediakan untuk kemungkinan pengembangan lanjutan, alur utama Stage 2 langsung
  pending_approval saat submit).
- Keranjang (daftar fasilitas + jadwal yang sudah dipilih tapi belum submit) disimpan di
  Laravel session, BUKAN di database.

Saat checkout (submit form data diri):
1. Dalam DB transaction: cari atau buat baris `Pemesan` berdasarkan email (jika email sudah
   ada, update data lain / atau tolak jika kebijakan kamu ingin satu email = satu identitas
   tetap — pilih: UPDATE data pemesan yang sudah ada, jangan buat duplikat).
2. Untuk setiap item di keranjang, insert satu baris `Reservasi`:
   - id_pemesan = pemesan di atas
   - id_tarif_sewa, tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, durasi,
     jumlah_pengguna, keperluan, harga_satuan, total_biaya sesuai item
   - kode_reservasi = unik per baris (generate, misal prefix + random/ulid)
   - kode_transaksi = SAMA untuk semua baris dari checkout ini (generate sekali di awal
     transaction)
   - status_reservasi = 'Menunggu'
   - lock_status = 'pending_approval'
   - lock_expires_at = null (tidak dipakai untuk status ini)
   - id_admin = null, tanggal_diproses = null
3. Jika ada fasilitas dengan jenis sewa Bulan di keranjang, wajib ada minimal 1 dokumen
   diupload per baris Reservasi tersebut sebelum submit dianggap valid (Company Profile,
   legalitas perusahaan, fotokopi KTP penanggung jawab) → insert ke Dokumen_Persyaratan
   dengan status_verifikasi = 'Menunggu'.
4. Hapus semua cache hold terkait item-item ini setelah baris Reservasi berhasil dibuat.
5. Hapus keranjang dari session.
6. Return kode_transaksi + daftar kode_reservasi ke halaman notifikasi sukses.

## Indikator warna ketersediaan (dipakai di denah per lantai)

Untuk kombinasi fasilitas + rentang tanggal/jam yang sedang dilihat:
- **Hijau**: tidak ada baris Reservasi aktif (status_reservasi in ['Menunggu','Disetujui'])
  yang bentrok, DAN tidak ada cache hold aktif dari session lain yang bentrok.
- **Merah**: seluruh rentang yang diminta bentrok dengan Reservasi aktif atau cache hold
  session lain.
- **Kuning**: sebagian dari rentang tanggal/jam yang diminta tersedia, sebagian lagi bentrok
  (relevan untuk Reservasi Harian/Bulanan saat pemesan melihat rentang tanggal lebar).

Tempatkan logika ini di service/helper terpisah (misal `app/Services/AvailabilityService.php`)
supaya bisa dipakai ulang, jangan taruh langsung di controller.

## Yang harus dibuat di Stage 2

1. **Routes** (routes/web.php, group prefix `reservasi`, tanpa middleware auth):
   - GET  /reservasi                         → pilih jenis fasilitas
   - GET  /reservasi/{kategori}/jenis-sewa    → pilih jenis sewa yang tersedia utk kategori itu
   - GET  /reservasi/{kategori}/lantai        → pilih lantai
   - GET  /reservasi/{kategori}/denah/{lantai}→ tampilkan denah + status warna
   - GET  /reservasi/fasilitas/{fasilitas}    → detail fasilitas + form jadwal
   - POST /reservasi/keranjang                → tambah item ke keranjang (buat cache hold)
   - DELETE /reservasi/keranjang/{index}      → hapus item dari keranjang (lepas cache hold)
   - GET  /reservasi/checkout                 → form data diri + ringkasan keranjang
   - POST /reservasi/checkout                 → proses submit (insert Reservasi dkk)
   - GET  /cek-status                         → form input kode
   - POST /cek-status                         → tampilkan hasil (cari by kode_transaksi ATAU
     kode_reservasi tunggal)
   - POST /reservasi/{kode_reservasi}/batalkan→ ubah status_reservasi jadi 'Dibatalkan'
     (hanya boleh dari status 'Menunggu' atau 'Disetujui' dgn tanggal_mulai belum lewat),
     catat di Riwayat_Status

2. **Controllers**: `ReservasiController` (browsing, keranjang, checkout, batalkan),
   `CekStatusController` (cek status). Method pendek, delegasikan logic availability ke
   `AvailabilityService`.

3. **Form Request Validation**:
   - `TambahKeranjangRequest`: validasi id_fasilitas, id_tarif_sewa, tanggal, jam sesuai
     jenis sewa (Jam wajib jam_mulai+jam_selesai, Harian/Bulan wajib tanggal_mulai+selesai,
     Bulan wajib durasi >= 3 bulan sesuai durasi_minimum di Jenis_Sewa)
   - `CheckoutRequest`: validasi seluruh field Pemesan (email format, no_telepon format),
     dan file dokumen (mimes:pdf,jpg,png) wajib jika ada item Bulan di keranjang

4. **Factory** untuk Fasilitas, Tarif_Sewa, Pemesan, Reservasi (dipakai untuk dummy data
   dan nantinya testing).

5. **Dummy data / Seeder tambahan**: minimal 5 Fasilitas per lantai (variasi kategori),
   Tarif_Sewa untuk tiap kombinasi Fasilitas x Jenis_Sewa yang relevan (Convention Hall
   cuma dapat tarif Harian), dan 5-10 contoh Reservasi dummy dengan status bervariasi
   (Menunggu, Disetujui, Ditolak, Dibatalkan) untuk keperluan testing Stage 3 nanti.

6. Views boleh sederhana (Blade tanpa styling rumit dulu) — fokus alur & logic benar dulu,
   styling menyusul.

Jangan buat apa pun untuk sisi Admin (approve/reject, faktur, laporan) — itu Stage 3.

Setelah selesai, jalankan seeder tambahan, tunjukkan hasil `php artisan route:list --path=reservasi`
dan `--path=cek-status`, dan laporkan kalau ada error.