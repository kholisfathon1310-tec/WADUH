# Fasilitas Bawaan, Kapasitas, dan Aturan Harga — Final

baca db-spec-fasilitas-final.md. pakai spesifikasi gabungan di file ini sebagai acuan seeder Fasilitas & Tarif_Sewa.

## 1. Kapasitas maksimal (isi kolom `kapasitas` di tabel Fasilitas)

Seragam per lantai/kategori (bukan per ruangan individual):
- Lantai 1 & Lantai 2 (Working Space): kapasitas = 10
- Lantai 3A (Co-Working Space): kapasitas = 2
- Lantai 3B (Co-Working Space): kapasitas = 4
- Lantai 5 (Convention Hall): kapasitas = 75

## 2. Fasilitas bawaan (amenities) — SIMPAN SEBAGAI CONFIG, BUKAN diulang di tiap baris

Buat `config/fasilitas_bawaan.php`:
```php
return [
    'umum' => ['Pemakaian bersama Pantry', 'Toilet per lantai', 'Mushola', 'Area Parkir'],
    'internet' => [
        'Co-Working Space' => '10 Mbps',
        'Working Space' => '20 Mbps',
        'Convention Hall' => null, // tidak disebutkan, jangan tampilkan baris internet
    ],
    'per_kategori' => [
        'Convention Hall' => ['Meja', 'Kursi', 'TV', 'Soundsystem', 'AC', 'Proyektor'],
        'Co-Working Space' => ['Meja', 'Kursi', 'Listrik', 'Wifi', 'AC'],
        'Working Space' => ['AC'], // Meja & Kursi kondisional, lihat aturan di bawah
    ],
];
```

Buat helper/service `app/Services/FasilitasBawaanService.php` dengan method
`untuk(Fasilitas $fasilitas, ?string $jenisSewa = null): array` yang menggabungkan:
`umum` + `per_kategori[kategori_fasilitas]` + baris internet sesuai kecepatan, DAN untuk
kategori "Working Space": tambahkan "Meja" & "Kursi" ke daftar HANYA JIKA `$jenisSewa`
adalah 'Jam' atau 'Hari'; jangan tambahkan kalau 'Bulan' (ruangan diserahkan kosong).

Pakai service ini di halaman detail fasilitas sisi pemesan (Stage 2) dan halaman detail
reservasi sisi admin (Stage 3) untuk menampilkan daftar fasilitas yang didapat — JANGAN
duplikasi teks ini secara manual di kolom `deskripsi` tiap baris Fasilitas. Kolom
`deskripsi` tetap ada di database untuk catatan bebas lain kalau perlu, tapi daftar
amenities selalu dihitung lewat service ini supaya satu sumber kebenaran.

## 3. Aturan harga (Tarif_Sewa)

| Kategori | Jenis Sewa | Harga |
|---|---|---|
| Working Space (L1, L2) & Co-Working Space (3A, 3B) | Bulan | Rp 150.000 × luas (m²) fasilitas |
| Co-Working Space (3A, 3B) | Jam | Rp 40.000 flat |
| Co-Working Space (3A, 3B) | Hari (dihitung 8 jam) | Rp 250.000 flat |
| Working Space (L1, L2) | Jam | Rp 50.000 flat |
| Working Space (L1, L2) | Hari (dihitung 8 jam) | Rp 400.000 flat |
| Convention Hall (L5) | Hari (dihitung 8 jam) | Rp 1.800.000 flat |

Convention Hall TIDAK punya tarif Jam maupun Bulan (konsisten dengan aturan sejak awal).

### Perhitungan tarif Bulan berdasarkan luas

Kolom `harga` di Tarif_Sewa tetap angka datar (bukan formula tersimpan). Untuk baris
Jenis_Sewa='Bulan', hitung `harga = 150000 * luas` SAAT SEEDING, ambil nilai `luas` dari
kolom `luas` yang sudah ada di baris Fasilitas terkait.

PENTING — data `luas` per fasilitas individual BELUM final. Untuk fasilitas yang kolom
`luas`-nya NULL atau belum terisi data real, pakai nilai default sementara supaya seeder
tetap jalan tanpa error:
- Kubikal (3A, 3B): default luas = 6 m²
- Ruangan/Working Space (L1, L2): default luas = 25 m²
- Convention Hall: tidak relevan (tidak ada tarif Bulan)

Tandai baris yang pakai default ini (misal lewat log seeder atau komentar), supaya nanti
kalau data luas real dari kamu sudah lengkap, tinggal update kolom `luas` di Fasilitas dan
jalankan ulang bagian penghitungan tarif Bulan ini (buat sebagai method/command terpisah
`php artisan tarif:hitung-ulang-bulanan` supaya bisa dijalankan ulang kapan saja tanpa
harus migrate:fresh seluruh database).

## 4. Tugas

1. Update seeder Fasilitas: isi kolom `kapasitas` sesuai bagian 1.
2. Buat config/fasilitas_bawaan.php dan FasilitasBawaanService sesuai bagian 2, pasang di
   view detail fasilitas (pemesan) & detail reservasi (admin).
3. Update/buat seeder Tarif_Sewa sesuai bagian 3, dengan logic perhitungan Bulan sesuai
   bagian 4 (pakai default luas kalau kolom luas kosong).
4. Buat artisan command `tarif:hitung-ulang-bulanan` yang menghitung ulang seluruh harga
   Tarif_Sewa berjenis Bulan berdasarkan luas terbaru di tabel Fasilitas.
5. Jalankan seeder, tunjukkan beberapa contoh hasil (`php artisan tinker` atau query biasa)
   untuk 2A/3B/L1/L2/L5 masing-masing satu fasilitas, perlihatkan kapasitas dan harga per
   jenis sewa yang ter-generate, supaya saya bisa cek kebenarannya.