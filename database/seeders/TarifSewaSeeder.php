<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\TarifSewa;
use App\Support\HargaTarif;
use Illuminate\Database\Seeder;

/**
 * Tarif sesuai docs/hargasewa.md (master data):
 * - Flat: WS Jam 50rb/Hari 400rb; CWS Jam 40rb/Hari 250rb; CH Hari 1,8jt (tanpa Jam/Bulan) —
 *   HargaTarif::FLAT, seragam per kategori, sudah cocok dengan hargasewa.md.
 * - Bulan (WS & CWS): angka PERSIS dari kolom "Harga/Bulan" di hargasewa.md
 *   (config/harga-sewa-master.php) — bukan hasil hitung ulang luas × Rp150.000, karena
 *   beberapa baris di master data tidak pas dibagi rata (lihat catatan di file config).
 * Hanya untuk fasilitas AKTIF.
 */
class TarifSewaSeeder extends Seeder
{
    public function run(): void
    {
        $jenisMap = JenisSewa::all()->keyBy(fn (JenisSewa $j) => $j->satuan->value); // 'Jam'|'Hari'|'Bulan'
        $master = config('harga-sewa-master');

        Fasilitas::where('status_aktif', 'Aktif')->get()->each(function (Fasilitas $fasilitas) use ($jenisMap, $master) {
            $kategori = $fasilitas->kategori_fasilitas;

            // Tarif flat (Jam/Hari) sesuai kategori.
            foreach (HargaTarif::FLAT[$kategori] ?? [] as $satuan => $harga) {
                TarifSewa::updateOrCreate(
                    ['id_fasilitas' => $fasilitas->id_fasilitas, 'id_jenis_sewa' => $jenisMap[$satuan]->id_jenis_sewa],
                    ['harga' => $harga, 'status_aktif' => 'Aktif'],
                );
            }

            // Tarif Bulan (WS & CWS saja) = angka persis dari master data, per kode_fasilitas.
            if (in_array($kategori, HargaTarif::PUNYA_BULAN, true)) {
                $harga = $master[$fasilitas->kode_fasilitas]['bulan']
                    ?? throw new \RuntimeException("Tidak ada harga Bulan di config/harga-sewa-master.php untuk {$fasilitas->kode_fasilitas}.");

                TarifSewa::updateOrCreate(
                    ['id_fasilitas' => $fasilitas->id_fasilitas, 'id_jenis_sewa' => $jenisMap['Bulan']->id_jenis_sewa],
                    ['harga' => $harga, 'status_aktif' => 'Aktif'],
                );
            }
        });
    }
}
