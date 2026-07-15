# Layout Denah Interaktif — Koordinat Presisi

Implementasikan denah interaktif (seperti pemilihan kursi bioskop) di halaman pemesan
(pilih fasilitas per lantai) dan halaman admin (Monitoring Fasilitas). Koordinat di file ini
adalah HASIL FINAL yang sudah disetujui — gunakan PERSIS, jangan ubah, jangan "rapikan",
jangan ganti jadi grid CSS atau kotak-kotak biasa. Bentuk poligon (dinding miring, sudut
diagonal bangunan) wajib dipertahankan.

## Penyimpanan koordinat

Buat file `config/denah.php`. JANGAN tambah kolom baru di tabel Fasilitas — koordinat adalah
data statis arsitektural, cukup di config, di-map lewat `kode_fasilitas`.

Struktur:
```php
return [
    'lantai' => [
        '1' => [
            'viewbox' => '0 0 1000 470',
            'envelope' => '150,135 810,135 900,225 900,300 840,352 150,352',
            'ruangan' => [
                'L1-R1' => '195,152 435,152 435,338 195,338',
                'L1-R2' => '442,152 610,152 610,338 442,338',
                'L1-R3' => '617,152 780,152 780,338 617,338',
            ],
            'area_statis' => [
                ['label' => 'Void',   'points' => '790,150 812,150 888,228 888,296 838,344 790,344'],
                ['label' => 'Gudang', 'points' => '30,375 170,375 170,455 30,455'],
                ['label' => 'Tangga', 'points' => '300,375 395,375 395,455 300,455'],
                ['label' => 'Toilet', 'points' => '425,375 575,375 575,455 425,455'],
                ['label' => 'Lift',   'points' => '610,375 700,375 700,430 610,430'],
                ['label' => 'Pantry', 'points' => '705,375 775,375 775,455 705,455'],
            ],
        ],

        '2' => [
            'viewbox' => '0 0 1000 540',
            'envelope' => '150,140 840,140 960,238 960,302 900,385 150,385',
            'ruangan' => [
                'L2-R2' => '205,152 358,152 358,262 205,248',
                'L2-R1' => '205,288 335,302 335,382 205,382',
                'L2-R4' => '378,148 535,148 535,262 448,280 378,262',
                'L2-R3' => '408,295 552,295 552,382 408,382',
                'L2-R5' => '690,152 858,152 858,268 690,268',
                'L2-R6' => '690,292 830,292 830,378 690,378',
                'L2-R7' => '872,182 950,255 950,300 900,368 872,368',
            ],
            'area_statis' => [
                ['label' => 'Taman',   'points' => '160,152 198,152 198,382 160,382'],
                ['label' => 'RK',      'points' => '85,425 235,425 235,520 85,520'],
                ['label' => 'Koridor', 'points' => '300,400 770,400 770,422 300,422'],
            ],
        ],

        '3A' => [
            'viewbox' => '0 0 1000 520',
            'envelope' => '150,140 820,140 945,232 945,302 885,382 150,382',
            'ruangan' => [
                '3A-M17' => '205,196 255,196 255,236 205,236',
                '3A-M18' => '263,196 313,196 313,236 263,236',
                '3A-M19' => '335,155 383,155 383,199 335,199',
                '3A-M20' => '389,155 437,155 437,199 389,199',
                '3A-M21' => '443,155 491,155 491,199 443,199',
                '3A-M22' => '497,155 545,155 545,199 497,199',
                '3A-M23' => '551,155 599,155 599,199 551,199',
                '3A-M24' => '605,155 653,155 653,199 605,199',
                '3A-M25' => '659,155 707,155 707,199 659,199',
                '3A-M26' => '713,155 761,155 761,199 713,199',
                '3A-M27' => '767,155 815,155 815,199 767,199',
                '3A-M7'  => '205,244 253,244 253,286 205,286',
                '3A-M8'  => '261,244 309,244 309,286 261,286',
                '3A-M9'  => '317,244 365,244 365,286 317,286',
                '3A-M10' => '373,244 421,244 421,286 373,286',
                '3A-M11' => '429,244 477,244 477,286 429,286',
                '3A-M12' => '570,244 618,244 618,286 570,286',
                '3A-M13' => '626,244 674,244 674,286 626,286',
                '3A-M14' => '682,244 730,244 730,286 682,286',
                '3A-M15' => '738,244 786,244 786,286 738,286',
                '3A-M16' => '794,244 842,244 842,286 794,286',
                '3A-M1'  => '196,312 254,312 254,352 196,352',
                '3A-M2'  => '268,312 326,312 326,352 268,352',
                '3A-M3'  => '340,312 398,312 398,352 340,352',
                '3A-M4'  => '412,312 470,312 470,352 412,352',
                '3A-M5'  => '484,312 542,312 542,352 484,352',
                '3A-M6'  => '556,312 614,312 614,352 556,352',
                '3A-R2'  => '650,300 730,300 730,382 650,382',
                '3A-R1'  => '736,300 816,300 816,382 736,382',
                '3A-R3'  => '858,248 942,248 942,302 884,378 858,378',
            ],
            'area_statis' => [
                ['label' => 'Taman', 'points' => '160,155 190,155 190,380 160,380'],
                ['label' => 'RK',    'points' => '80,425 230,425 230,510 80,510'],
            ],
        ],

        '3B' => [
            'viewbox' => '0 0 1000 500',
            'envelope' => '150,140 840,140 960,238 960,302 900,385 150,385',
            'ruangan' => [
                '3B-M1'  => '200,158 268,158 268,203 200,203',
                '3B-M2'  => '280,158 348,158 348,203 280,203',
                '3B-M3'  => '200,215 268,215 268,260 200,260',
                '3B-M4'  => '280,215 348,215 348,260 280,260',
                '3B-M5'  => '200,272 268,272 268,317 200,317',
                '3B-M6'  => '280,272 348,272 348,317 280,317',
                '3B-M7'  => '200,329 268,329 268,374 200,374',
                '3B-M8'  => '280,329 348,329 348,374 280,374',
                '3B-M9'  => '372,158 440,158 440,203 372,203',
                '3B-M10' => '452,158 520,158 520,203 452,203',
                '3B-M11' => '372,215 440,215 440,260 372,260',
                '3B-M12' => '452,215 520,215 520,260 452,260',
                '3B-M13' => '372,272 440,272 440,317 372,317',
                '3B-M14' => '452,272 520,272 520,317 452,317',
                '3B-M15' => '372,329 440,329 440,374 372,374',
                '3B-M16' => '452,329 520,329 520,374 452,374',
                '3B-R2'  => '545,155 700,155 700,375 545,375',
                '3B-R1'  => '722,155 840,155 950,245 950,300 898,375 722,375',
            ],
            'area_statis' => [
                ['label' => 'Taman',      'points' => '160,158 190,158 190,372 160,372'],
                ['label' => 'Ruang Kaca', 'points' => '60,420 225,420 225,498 60,498'],
            ],
        ],

        '5' => [
            'viewbox' => '0 0 1000 480',
            'envelope' => '',
            'ruangan' => [
                'L5-HALL' => '140,125 755,125 755,152 835,152 900,218 900,258 950,302 950,378 140,378',
            ],
            'area_statis' => [
                ['label' => 'Gudang', 'points' => '50,405 200,405 200,470 50,470'],
                ['label' => 'Toilet', 'points' => '290,405 405,405 405,470 290,470'],
                ['label' => 'Toilet', 'points' => '430,405 545,405 545,470 430,470'],
                ['label' => 'Lift',   'points' => '610,405 710,405 710,470 610,470'],
                ['label' => 'Pantry', 'points' => '730,405 830,405 830,470 730,470'],
            ],
        ],
    ],
];
```

Kode fasilitas di config ini HARUS cocok dengan `kode_fasilitas` di tabel Fasilitas hasil
seeder. Kalau ada mismatch (kode di config tidak ketemu di DB, atau sebaliknya), tampilkan
warning saat render — jangan diam-diam skip.

## Cara render

Buat Blade component `resources/views/components/denah.blade.php` yang menerima:
- `$lantai` (nomor_lantai: '1','2','3A','3B','5')
- `$statusPerFasilitas` (array asosiatif: kode_fasilitas => 'hijau'|'merah'|'kuning')
- `$clickable` (bool — true di halaman pemesan, false/read-only kalau perlu di admin)

Render sebagai inline `<svg>` dengan `viewBox` dari config, lalu untuk tiap entri:
1. `envelope` (kalau tidak kosong) → `<polygon>` fill none, stroke abu-abu tipis 1px. Ini
   cuma garis bantu bentuk gedung, tidak bisa diklik.
2. `area_statis` → `<polygon>` fill abu-abu muda, stroke tipis, TIDAK bisa diklik, dengan
   `<text>` label di tengah poligon (hitung centroid, lihat catatan di bawah).
3. `ruangan` → `<polygon>` dengan warna sesuai `$statusPerFasilitas`, plus `<text>` kode
   fasilitas (tanpa prefix lantai, misal tampilkan "M1" bukan "3A-M1") di centroid poligon.

### Warna (samakan persis dengan konvensi sistem)
- hijau (tersedia): fill hijau muda, border hijau
- merah (terisi / status_aktif = Tidak Aktif): fill merah muda, border merah, cursor
  not-allowed, TIDAK bisa diklik
- kuning (sebagian periode terisi): fill kuning muda, border kuning — bisa diklik
- dipilih (state di frontend, bukan dari server): border biru tebal 2px, fill biru muda

Fasilitas dengan `status_aktif = 'Tidak Aktif'` SELALU merah tanpa perlu cek jadwal, dan
tetap ditampilkan di denah (jangan disembunyikan).

### Centroid untuk posisi label
Poligon tidak beraturan (yang punya sudut miring) tidak bisa pakai titik tengah bounding box —
labelnya bisa keluar dari bentuknya. Hitung centroid poligon dengan rumus area terbobot:

```js
function centroid(pointsStr) {
  const p = pointsStr.trim().split(/\s+/).map(s => s.split(',').map(Number));
  let a = 0, cx = 0, cy = 0;
  for (let i = 0; i < p.length; i++) {
    const j = (i + 1) % p.length;
    const f = p[i][0]*p[j][1] - p[j][0]*p[i][1];
    a += f; cx += (p[i][0]+p[j][0])*f; cy += (p[i][1]+p[j][1])*f;
  }
  a *= 0.5;
  if (Math.abs(a) < 0.001) {
    return [p.reduce((s,q)=>s+q[0],0)/p.length, p.reduce((s,q)=>s+q[1],0)/p.length];
  }
  return [cx/(6*a), cy/(6*a)];
}
```
Boleh dihitung di PHP (helper) saat render, atau di JS. Yang penting label selalu di dalam
poligon.

### Interaksi (halaman pemesan)
- Klik poligon hijau/kuning → toggle terpilih (border biru tebal).
- Bisa pilih lebih dari satu fasilitas (multi-select), sesuai fitur keranjang.
- Bar ringkasan di bawah denah: "N fasilitas dipilih: M1, M2, ..." + tombol lanjut ke
  keranjang/detail.
- Poligon merah tidak merespons klik sama sekali.

### Responsive
`<svg width="100%" viewBox="...">` — biarkan SVG scale mengikuti lebar container. Jangan
set width/height fixed dalam px. Label `<text>` pakai `font-size` kecil (10px untuk kubikal
M, 12px untuk ruangan R) supaya tetap terbaca saat di-scale.

## Yang TIDAK boleh dilakukan

- Jangan ganti poligon jadi `<rect>` biasa "biar rapi" — bentuk miring itu sengaja, sesuai
  denah arsitektur gedung.
- Jangan ubah/round koordinat.
- Jangan pakai CSS grid / flexbox untuk menyusun ulang posisi kotak — posisi HARUS dari
  koordinat SVG di config.
- Jangan sembunyikan fasilitas Tidak Aktif.

## Tugas

1. Buat `config/denah.php` dengan isi persis seperti di atas.
2. Buat Blade component `<x-denah>` sesuai spesifikasi render di atas.
3. Pasang di halaman pemesan (pilih fasilitas per lantai) dan halaman admin (Monitoring
   Fasilitas), dengan `$statusPerFasilitas` diisi dari AvailabilityService yang sudah ada.
4. Verifikasi tiap kode_fasilitas di config ketemu di tabel Fasilitas; laporkan kalau ada
   yang mismatch.
5. Tunjukkan screenshot/URL halaman denah lantai 3A dan 3B setelah selesai, supaya saya bisa
   cek bentuknya sudah sama persis.