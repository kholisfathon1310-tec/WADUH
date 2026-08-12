<?php

namespace App\Console\Commands;

use App\Models\TarifSewa;
use Illuminate\Console\Command;

class TarifHitungUlangBulanan extends Command
{
    /** @var string */
    protected $signature = 'tarif:hitung-ulang-bulanan';

    /** @var string */
    protected $description = 'Samakan ulang seluruh harga Tarif_Sewa berjenis Bulan ke master data di config/harga-sewa-master.php';

    public function handle(): int
    {
        $master = config('harga-sewa-master');

        $tarifBulan = TarifSewa::whereHas('jenisSewa', fn ($q) => $q->where('satuan', 'Bulan'))
            ->with('fasilitas')
            ->get();

        $diubah = 0;
        $tidakAdaMaster = [];

        foreach ($tarifBulan as $tarif) {
            $kode = $tarif->fasilitas->kode_fasilitas;
            $harga = $master[$kode]['bulan'] ?? null;

            if ($harga === null) {
                $tidakAdaMaster[] = $kode;
                continue;
            }

            if ((int) $tarif->harga !== $harga) {
                $tarif->update(['harga' => $harga]);
                $diubah++;
            }
        }

        $this->info("Selesai. {$tarifBulan->count()} tarif Bulan diperiksa, {$diubah} disesuaikan ke config/harga-sewa-master.php.");
        if ($tidakAdaMaster !== []) {
            $this->warn('Dilewati (kode tidak ada di master data): '.implode(', ', $tidakAdaMaster));
        }

        return self::SUCCESS;
    }
}
