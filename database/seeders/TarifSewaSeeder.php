<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\TarifSewa;
use App\Support\HargaTarif;
use Illuminate\Database\Seeder;

/**
 * Tarif sesuai db-spec-fasilitas-final.md bagian 3:
 * - Flat: WS Jam 50rb/Hari 400rb; CWS Jam 40rb/Hari 250rb; CH Hari 1,8jt (tanpa Jam/Bulan).
 * - Bulan (WS & CWS): Rp 150.000 × luas fasilitas — dihitung saat seeding; kalau luas
 *   masih default sementara, jalankan ulang `php artisan tarif:hitung-ulang-bulanan`
 *   setelah data luas real diisi.
 * Hanya untuk fasilitas AKTIF.
 */
class TarifSewaSeeder extends Seeder
{
    public function run(): void
    {
        $jenisMap = JenisSewa::all()->keyBy(fn (JenisSewa $j) => $j->satuan->value); // 'Jam'|'Hari'|'Bulan'
        $pakaiDefault = [];

        Fasilitas::where('status_aktif', 'Aktif')->get()->each(function (Fasilitas $fasilitas) use ($jenisMap, &$pakaiDefault) {
            $kategori = $fasilitas->kategori_fasilitas;

            // Tarif flat (Jam/Hari) sesuai kategori.
            foreach (HargaTarif::FLAT[$kategori] ?? [] as $satuan => $harga) {
                TarifSewa::updateOrCreate(
                    ['id_fasilitas' => $fasilitas->id_fasilitas, 'id_jenis_sewa' => $jenisMap[$satuan]->id_jenis_sewa],
                    ['harga' => $harga, 'status_aktif' => 'Aktif'],
                );
            }

            // Tarif Bulan = 150.000 × luas (WS & CWS saja).
            if (in_array($kategori, HargaTarif::PUNYA_BULAN, true)) {
                [$harga, $default] = HargaTarif::hargaBulan($fasilitas);
                if ($default) {
                    $pakaiDefault[] = $fasilitas->kode_fasilitas;
                }

                TarifSewa::updateOrCreate(
                    ['id_fasilitas' => $fasilitas->id_fasilitas, 'id_jenis_sewa' => $jenisMap['Bulan']->id_jenis_sewa],
                    ['harga' => $harga, 'status_aktif' => 'Aktif'],
                );
            }
        });

        if ($pakaiDefault !== []) {
            $this->command?->warn('Tarif Bulan memakai DEFAULT luas untuk: '.implode(', ', $pakaiDefault)
                .' — update kolom luas lalu jalankan `php artisan tarif:hitung-ulang-bulanan`.');
        }
    }
}
