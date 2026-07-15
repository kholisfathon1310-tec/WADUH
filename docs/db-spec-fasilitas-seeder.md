# Seeder Fasilitas — Data Real Sesuai Denah Gedung BITC

Ini MENGGANTIKAN instruksi dummy data generik ("minimal 5 Fasilitas per lantai") di
db-spec-stage2-pemesan.md. Gunakan data real di bawah ini, bukan data acak.

Konteks: Lantai 1 & 2 = kategori_fasilitas "Working Space". Lantai 3A & 3B = kategori_fasilitas
"Co-Working Space". Lantai 5 = kategori_fasilitas "Convention Hall".

Ruangan yang saat ini disewa tenant jangka panjang existing (di luar sistem reservasi ini)
TETAP di-seed sebagai baris Fasilitas, TAPI dengan status_aktif = 'Tidak Aktif' (tidak bisa
dipesan lewat sistem) dan TANPA menyimpan nama penyewa di kolom mana pun — sistem tidak
pernah menampilkan nama instansi penyewa lama. Gunakan kolom deskripsi untuk keterangan
netral seperti "Sedang tidak tersedia untuk reservasi" jika perlu, jangan sebut nama tenant.

## Data per lantai

Format: kode_fasilitas | status_aktif

**Lantai 1** (nomor_lantai = "1", kategori_fasilitas = "Working Space")
- L1-R1 | Tidak Aktif
- L1-R2 | Tidak Aktif
- L1-R3 | Tidak Aktif

**Lantai 2** (nomor_lantai = "2", kategori_fasilitas = "Working Space")
- L2-R1 | Tidak Aktif
- L2-R2 | Aktif
- L2-R3 | Tidak Aktif
- L2-R4 | Tidak Aktif
- L2-R5 | Tidak Aktif
- L2-R6 | Aktif
- L2-R7 | Aktif
- L2-RK | Tidak Aktif

**Lantai 3A** (nomor_lantai = "3A", kategori_fasilitas = "Co-Working Space", kapasitas maksimal 2 orang per kubikal M, kapasitas fleksibel untuk R/RK karena lebih besar — isi kapasitas wajar mis. 4-6 orang)
- 3A-M1 | Tidak Aktif
- 3A-M2 | Aktif
- 3A-M3 | Tidak Aktif
- 3A-M4 | Aktif
- 3A-M5 | Aktif
- 3A-M6 | Tidak Aktif
- 3A-M7 | Tidak Aktif
- 3A-M8 | Aktif
- 3A-M9 | Tidak Aktif
- 3A-M10 | Aktif
- 3A-M11 | Aktif
- 3A-M12 | Tidak Aktif
- 3A-M13 | Tidak Aktif
- 3A-M14 | Tidak Aktif
- 3A-M15 | Tidak Aktif
- 3A-M16 | Tidak Aktif
- 3A-M17 | Tidak Aktif
- 3A-M18 | Tidak Aktif
- 3A-M19 | Tidak Aktif
- 3A-M20 | Tidak Aktif
- 3A-M21 | Aktif
- 3A-M22 | Aktif
- 3A-M23 | Aktif
- 3A-M24 | Aktif
- 3A-M25 | Aktif
- 3A-M26 | Aktif
- 3A-M27 | Tidak Aktif
- 3A-R1 | Tidak Aktif
- 3A-R2 | Tidak Aktif
- 3A-R3 | Tidak Aktif
- 3A-RK | Tidak Aktif

**Lantai 3B** (nomor_lantai = "3B", kategori_fasilitas = "Co-Working Space", kubikal berukuran besar dibanding 3A — isi kapasitas lebih tinggi, mis. 8-10 orang)
- 3B-R1 | Aktif
- 3B-R2 | Aktif
- 3B-R3 | Aktif
- 3B-R4 | Aktif
- 3B-RUANGKACA | Tidak Aktif

**Lantai 5** (nomor_lantai = "5", kategori_fasilitas = "Convention Hall")
- L5-HALL | Aktif

Total: 48 baris Fasilitas (3 + 8 + 31 + 5 + 1).

## Tarif_Sewa

Untuk setiap Fasilitas dengan status_aktif = 'Aktif', buat baris Tarif_Sewa sesuai jenis
sewa yang berlaku untuk kategori_fasilitas-nya (lihat db-spec-stage1.md untuk aturan jenis
sewa per kategori: Working Space & Co-Working Space dapat Jam/Hari/Bulan, Convention Hall
hanya Hari). Isi harga dengan angka wajar (bisa berbeda per lantai/kubikal), status_aktif
Tarif_Sewa = 'Aktif'. Fasilitas dengan status_aktif = 'Tidak Aktif' TIDAK perlu diberi
Tarif_Sewa (tidak akan pernah dipakai untuk reservasi).

## Tugas

Update DatabaseSeeder (atau buat FasilitasSeeder + TarifSewaSeeder terpisah, dipanggil dari
DatabaseSeeder) sesuai data di atas. Jalankan `php artisan migrate:fresh --seed` setelah
selesai supaya data lama (dummy generik dari Stage 2 kalau sudah sempat di-seed) tergantikan
bersih, lalu tunjukkan jumlah baris di tabel Fasilitas dan Tarif_Sewa sebagai bukti
(`php artisan tinker --execute="echo App\Models\Fasilitas::count(); echo App\Models\TarifSewa::count();"`
atau cara lain yang setara).