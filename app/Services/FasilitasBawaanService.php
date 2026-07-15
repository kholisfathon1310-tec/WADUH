<?php

namespace App\Services;

use App\Models\Fasilitas;

/**
 * Daftar fasilitas bawaan (amenities) yang didapat penyewa — dihitung dari
 * config/fasilitas_bawaan.php, BUKAN disimpan per baris Fasilitas.
 */
class FasilitasBawaanService
{
    /**
     * Gabungan: per_kategori + (Meja & Kursi khusus Working Space sewa Jam/Hari)
     * + internet sesuai kecepatan + umum.
     *
     * @param  string|null  $jenisSewa  'Jam' | 'Hari' | 'Bulan' | null
     * @return string[]
     */
    public function untuk(Fasilitas $fasilitas, ?string $jenisSewa = null): array
    {
        $cfg = config('fasilitas_bawaan');
        $kategori = $fasilitas->kategori_fasilitas;

        $daftar = $cfg['per_kategori'][$kategori] ?? [];

        // Working Space: Meja & Kursi hanya untuk sewa Jam/Hari — sewa Bulan diserahkan kosong.
        if ($kategori === 'Working Space' && in_array($jenisSewa, ['Jam', 'Hari'], true)) {
            $daftar = array_merge(['Meja', 'Kursi'], $daftar);
        }

        $internet = $cfg['internet'][$kategori] ?? null;
        if ($internet !== null) {
            $daftar[] = 'Internet '.$internet;
        }

        return array_merge($daftar, $cfg['umum']);
    }
}
