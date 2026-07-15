<?php

namespace App\Console\Commands;

use App\Models\TarifSewa;
use App\Support\HargaTarif;
use Illuminate\Console\Command;

class TarifHitungUlangBulanan extends Command
{
    /** @var string */
    protected $signature = 'tarif:hitung-ulang-bulanan';

    /** @var string */
    protected $description = 'Hitung ulang seluruh harga Tarif_Sewa berjenis Bulan (Rp 150.000 × luas terbaru di tabel Fasilitas)';

    public function handle(): int
    {
        $tarifBulan = TarifSewa::whereHas('jenisSewa', fn ($q) => $q->where('satuan', 'Bulan'))
            ->with('fasilitas')
            ->get();

        $diubah = 0;
        $pakaiDefault = [];

        foreach ($tarifBulan as $tarif) {
            [$harga, $default] = HargaTarif::hargaBulan($tarif->fasilitas);

            if ($default) {
                $pakaiDefault[] = $tarif->fasilitas->kode_fasilitas;
            }
            if ((float) $tarif->harga !== $harga) {
                $tarif->update(['harga' => $harga]);
                $diubah++;
            }
        }

        $this->info("Selesai. {$tarifBulan->count()} tarif Bulan diperiksa, {$diubah} diperbarui (Rp ".number_format(HargaTarif::BULAN_PER_M2, 0, ',', '.').'/m²).');
        if ($pakaiDefault !== []) {
            $this->warn('Masih memakai DEFAULT luas: '.implode(', ', $pakaiDefault));
        }

        return self::SUCCESS;
    }
}
