<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Lantai;
use Illuminate\Database\Seeder;

/**
 * Data Fasilitas real sesuai denah gedung BITC — 64 ruangan total.
 * Aturan kode: "-K" = Co-Working Space, "-R" = Working Space, "HALL" = Convention Hall.
 * Kondisi awal sistem: seluruh ruangan berstatus AKTIF dan siap dipesan.
 */
class FasilitasSeeder extends Seeder
{
    /** nomor_lantai => daftar kode_fasilitas. */
    private const DENAH = [
        '1' => ['L1-R1', 'L1-R2', 'L1-R3'],
        '2' => ['L2-R1', 'L2-R2', 'L2-R3', 'L2-R4', 'L2-R5', 'L2-R6', 'L2-R7', 'L2-R8'],
        '3A' => [
            '3A-K1', '3A-K2', '3A-K3', '3A-K4', '3A-K5', '3A-K6', '3A-K7', '3A-K8', '3A-K9', '3A-K10',
            '3A-K11', '3A-K12', '3A-K13', '3A-K14', '3A-K15', '3A-K16', '3A-K17', '3A-K18', '3A-K19', '3A-K20',
            '3A-K21', '3A-K22', '3A-K23', '3A-K24', '3A-K25', '3A-K26', '3A-K27',
            '3A-R1', '3A-R2', '3A-R3', '3A-R4', '3A-R5', '3A-R6',
        ],
        '3B' => [
            '3B-K1', '3B-K2', '3B-K3', '3B-K4', '3B-K5', '3B-K6', '3B-K7', '3B-K8',
            '3B-K9', '3B-K10', '3B-K11', '3B-K12', '3B-K13', '3B-K14', '3B-K15', '3B-K16',
            '3B-R1', '3B-R2', '3B-R3',
        ],
        '5' => ['L5-HALL'],
    ];

    /** Kode lama → kode baru. Aman dijalankan berulang (skip kalau target sudah ada). */
    private const RENAME = ['L2-RK' => 'L2-R8', '3A-RK' => '3A-R4', '3B-RUANGKACA' => '3B-R3'];

    /**
     * Foto per kelompok ruangan (file di public/images/, 1 foto = 1 elemen array).
     * Kelompok dipilih ulang-alik (round-robin) per ruangan, kecuali 'l5' (Convention
     * Hall) yang selalu memakai SEMUA foto sekaligus sebagai galeri.
     */
    private const FOTO = [
        'l1'   => ['lt1.png', 'lt1 (2).png', 'lt1 (3).png'],
        'l2'   => ['lt2.png', 'lt2 (2).png', 'lt2_ruangrapat (1).png', 'lt2_ruangrapat (2).png', 'lt2_ruangrapat (3).png'],
        '3a_k' => ['lt3a.png', 'lt3a (2).png', 'lt3a (3).png', 'lt3a (4).png', 'lt3a (5).png'],
        '3b_k' => ['lt3b.png', 'lt3b (2).png', 'lt3b (3).png', 'lt3b (4).png'],
        'l5'   => ['lt5.png', 'lt5 (2).png', 'lt5 (3).png', 'lt5 (4).png', 'lt5 (5).png'],
    ];

    public function run(): void
    {
        // Rename kode lama ke format standar; tarif & reservasi mengikuti otomatis
        // karena terikat id_fasilitas, bukan kode.
        $renameMap = self::RENAME;
        foreach (range(1, 27) as $n) {
            $renameMap["3A-M{$n}"] = "3A-K{$n}";
        }
        foreach (range(1, 16) as $n) {
            $renameMap["3B-M{$n}"] = "3B-K{$n}";
        }

        foreach ($renameMap as $lama => $baru) {
            if (! Fasilitas::where('kode_fasilitas', $baru)->exists()) {
                Fasilitas::where('kode_fasilitas', $lama)->update(['kode_fasilitas' => $baru]);
            }
        }

        $fotoIndex = []; // nama kelompok foto => posisi berikutnya (ulang-alik)

        foreach (self::DENAH as $nomor => $kodeList) {
            $lantai = Lantai::where('nomor_lantai', $nomor)->firstOrFail();

            foreach ($kodeList as $kode) {
                $kategori = $this->kategori($kode);
                $kapasitas = $this->kapasitas($nomor, $kategori);
                $luas = $this->luas($kode, $kategori);

                $grupFoto = $this->grupFoto($nomor, $kategori);
                $urutan = $fotoIndex[$grupFoto] ??= 0;
                $fotoIndex[$grupFoto]++;

                Fasilitas::updateOrCreate(
                    ['kode_fasilitas' => $kode],
                    [
                        'id_lantai'          => $lantai->id_lantai,
                        'nama_fasilitas'     => $this->nama($kode),
                        'kategori_fasilitas' => $kategori,
                        'kapasitas'          => $kapasitas,
                        'luas'               => $luas,
                        'foto'               => $this->foto($grupFoto, $urutan),
                        'deskripsi'          => "{$kategori} {$kode}.",
                        'status_aktif'       => 'Aktif',
                    ],
                );
            }
        }
    }

    /** Kelompok foto per ruangan: L1/L2 pakai fotonya sendiri; ruangan "-R" di lantai 3 pakai set foto L2. */
    private function grupFoto(string $nomor, string $kategori): string
    {
        return match (true) {
            $kategori === 'Convention Hall'                  => 'l5',
            $nomor === '1'                                    => 'l1',
            $nomor === '2'                                    => 'l2',
            $nomor === '3A' && $kategori === 'Co-Working Space' => '3a_k',
            $nomor === '3B' && $kategori === 'Co-Working Space' => '3b_k',
            default                                           => 'l2', // Working Space "-R" di 3A/3B
        };
    }

    /** Foto untuk satu ruangan dalam kelompok $grup, di urutan ulang-alik ke-$urutan. */
    private function foto(string $grup, int $urutan): array
    {
        $set = self::FOTO[$grup];

        // Convention Hall: semua foto sekaligus, ditampilkan sebagai galeri.
        if ($grup === 'l5') {
            return $set;
        }

        return [$set[$urutan % count($set)]];
    }

    /** Kategori murni dari kode: "-K" Co-Working, "HALL" Convention Hall, selain itu ("-R") Working Space. */
    private function kategori(string $kode): string
    {
        return match (true) {
            str_contains($kode, 'HALL') => 'Convention Hall',
            str_contains($kode, '-K')   => 'Co-Working Space',
            default                     => 'Working Space',
        };
    }

    private function nama(string $kode): string
    {
        return match (true) {
            str_contains($kode, 'HALL') => 'Convention Hall',
            str_contains($kode, '-K')   => "Kubikal {$kode}",
            default                     => "Ruang {$kode}",
        };
    }

    /** Kapasitas per kategori (db-spec-fasilitas-final.md bagian 1), Co-Working seragam per lantai. */
    private function kapasitas(string $nomor, string $kategori): int
    {
        return match (true) {
            $kategori === 'Working Space'    => 10,
            $kategori === 'Convention Hall'  => 75,
            $nomor === '3A'                  => 2, // Co-Working Space kubikal kecil
            $nomor === '3B'                  => 4, // Co-Working Space kubikal besar
            default                          => 10,
        };
    }

    /**
     * Luas per ruangan — master data dari docs/hargasewa.md (config/harga-sewa-master.php),
     * bukan lagi nilai seragam per kategori. Convention Hall tidak ada di master data itu,
     * jadi tetap pakai default lama (600 m²).
     */
    private function luas(string $kode, string $kategori): float
    {
        if ($kategori === 'Convention Hall') {
            return 600.00;
        }

        return config("harga-sewa-master.{$kode}.luas");
    }
}
