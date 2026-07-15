<?php

namespace App\Support;

use App\Models\Fasilitas;

/**
 * Aturan harga Tarif_Sewa (db-spec-fasilitas-final.md) — satu sumber untuk
 * seeder dan command tarif:hitung-ulang-bulanan.
 */
class HargaTarif
{
    /** Tarif flat per kategori (Rp). Hari dihitung 8 jam. */
    public const FLAT = [
        'Working Space'    => ['Jam' => 50_000, 'Hari' => 400_000],
        'Co-Working Space' => ['Jam' => 40_000, 'Hari' => 250_000],
        'Convention Hall'  => ['Hari' => 1_800_000], // tanpa Jam & Bulan
    ];

    /** Tarif Bulan = tarif per m² × luas fasilitas. */
    public const BULAN_PER_M2 = 150_000;

    /** Default luas sementara (m²) bila kolom luas belum terisi data real. */
    public const DEFAULT_LUAS = [
        'Co-Working Space' => 6,   // kubikal 3A/3B
        'Working Space'    => 25,  // ruangan L1/L2
    ];

    /** Kategori yang mendapat tarif Bulan. */
    public const PUNYA_BULAN = ['Working Space', 'Co-Working Space'];

    /**
     * Luas efektif untuk perhitungan tarif Bulan.
     *
     * @return array{0: float, 1: bool} [luas, memakaiDefault]
     */
    public static function luasEfektif(Fasilitas $fasilitas): array
    {
        $luas = (float) ($fasilitas->luas ?? 0);
        if ($luas > 0) {
            return [$luas, false];
        }

        return [(float) (self::DEFAULT_LUAS[$fasilitas->kategori_fasilitas] ?? 0), true];
    }

    /**
     * Harga tarif Bulan untuk sebuah fasilitas.
     *
     * @return array{0: float, 1: bool} [harga, memakaiDefaultLuas]
     */
    public static function hargaBulan(Fasilitas $fasilitas): array
    {
        [$luas, $default] = self::luasEfektif($fasilitas);

        return [self::BULAN_PER_M2 * $luas, $default];
    }
}
