<?php

namespace Database\Seeders;

use App\Models\Fasilitas;
use App\Models\Lantai;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use Illuminate\Database\Seeder;

/**
 * Data Fasilitas real sesuai denah gedung BITC (menggantikan dummy generik Stage 2).
 * Ruangan yang disewa tenant existing tetap di-seed sebagai baris TIDAK AKTIF,
 * tanpa menyimpan nama penyewa di mana pun.
 */
class FasilitasSeeder extends Seeder
{
    /** nomor_lantai => [kategori, [kode => status_aktif]]. */
    private const DENAH = [
        '1' => ['Working Space', [
            'L1-R1' => 'Tidak Aktif', 'L1-R2' => 'Tidak Aktif', 'L1-R3' => 'Tidak Aktif',
        ]],
        '2' => ['Working Space', [
            'L2-R1' => 'Tidak Aktif', 'L2-R2' => 'Aktif', 'L2-R3' => 'Tidak Aktif',
            'L2-R4' => 'Tidak Aktif', 'L2-R5' => 'Tidak Aktif', 'L2-R6' => 'Aktif',
            'L2-R7' => 'Aktif', 'L2-R8' => 'Aktif',
        ]],
        '3A' => ['Co-Working Space', [
            '3A-M1' => 'Tidak Aktif', '3A-M2' => 'Aktif', '3A-M3' => 'Tidak Aktif', '3A-M4' => 'Aktif',
            '3A-M5' => 'Aktif', '3A-M6' => 'Tidak Aktif', '3A-M7' => 'Tidak Aktif', '3A-M8' => 'Aktif',
            '3A-M9' => 'Tidak Aktif', '3A-M10' => 'Aktif', '3A-M11' => 'Aktif', '3A-M12' => 'Tidak Aktif',
            '3A-M13' => 'Tidak Aktif', '3A-M14' => 'Tidak Aktif', '3A-M15' => 'Tidak Aktif', '3A-M16' => 'Tidak Aktif',
            '3A-M17' => 'Tidak Aktif', '3A-M18' => 'Tidak Aktif', '3A-M19' => 'Tidak Aktif', '3A-M20' => 'Tidak Aktif',
            '3A-M21' => 'Aktif', '3A-M22' => 'Aktif', '3A-M23' => 'Aktif', '3A-M24' => 'Aktif',
            '3A-M25' => 'Aktif', '3A-M26' => 'Aktif', '3A-M27' => 'Tidak Aktif',
            '3A-R1' => 'Tidak Aktif', '3A-R2' => 'Tidak Aktif', '3A-R3' => 'Tidak Aktif', '3A-R4' => 'Aktif',
        ]],
        // 3B: 16 kubikal aktif + 2 ruangan tenant (Tidak Aktif) + 3B-R3 eks Ruang Kaca
        // (Working Space, bisa dipesan) — total 19 unit.
        '3B' => ['Co-Working Space', [
            '3B-M1' => 'Aktif', '3B-M2' => 'Aktif', '3B-M3' => 'Aktif', '3B-M4' => 'Aktif',
            '3B-M5' => 'Aktif', '3B-M6' => 'Aktif', '3B-M7' => 'Aktif', '3B-M8' => 'Aktif',
            '3B-M9' => 'Aktif', '3B-M10' => 'Aktif', '3B-M11' => 'Aktif', '3B-M12' => 'Aktif',
            '3B-M13' => 'Aktif', '3B-M14' => 'Aktif', '3B-M15' => 'Aktif', '3B-M16' => 'Aktif',
            '3B-R1' => 'Tidak Aktif', '3B-R2' => 'Tidak Aktif',
            '3B-R3' => 'Aktif',
        ]],
        '5' => ['Convention Hall', [
            'L5-HALL' => 'Aktif',
        ]],
    ];

    /**
     * Kode 3B versi lama yang digantikan denah baru. Kosong: pembersihan R3/R4 lama
     * sudah selesai, dan kode 3B-R3 kini DIPAKAI ULANG untuk eks Ruang Kaca — jangan
     * dimasukkan lagi ke daftar ini.
     */
    private const OBSOLETE_3B = [];

    /** Kode lama → kode baru (format nama ruangan standar, tanpa "RK"/"Ruang Kaca"). */
    private const RENAME = ['L2-RK' => 'L2-R8', '3A-RK' => '3A-R4', '3B-RUANGKACA' => '3B-R3'];

    /**
     * Ruangan Working Space yang berada di lantai berkategori lain (bisa dipesan
     * sebagai Working Space): eks-RK lantai 2 & 3A, eks-Ruang Kaca lantai 3B.
     */
    private const RUANG_WS_KHUSUS = ['L2-R8', '3A-R4', '3B-R3'];

    public function run(): void
    {
        // Rename kode lama (RK / RUANGKACA) ke format ruangan standar; tarif & reservasi
        // mengikuti otomatis karena terikat id_fasilitas, bukan kode.
        foreach (self::RENAME as $lama => $baru) {
            if (! Fasilitas::where('kode_fasilitas', $baru)->exists()) {
                Fasilitas::where('kode_fasilitas', $lama)->update(['kode_fasilitas' => $baru]);
            }
        }

        $this->bersihkan3BLama();

        foreach (self::DENAH as $nomor => [$kategori, $rooms]) {
            $lantai = Lantai::where('nomor_lantai', $nomor)->firstOrFail();

            foreach ($rooms as $kode => $status) {
                // RK & Ruang Kaca adalah ruangan Working Space walau lantainya kategori lain.
                $khususWS = in_array($kode, self::RUANG_WS_KHUSUS, true);
                $kategoriRoom = $khususWS ? 'Working Space' : $kategori;
                $kapasitas = $khususWS ? 10 : $this->kapasitas($nomor, $kode);
                $luas = $khususWS ? 25.00 : $this->luas($nomor, $kapasitas);

                Fasilitas::updateOrCreate(
                    ['kode_fasilitas' => $kode],
                    [
                        'id_lantai'          => $lantai->id_lantai,
                        'nama_fasilitas'     => $this->nama($kode),
                        'kategori_fasilitas' => $kategoriRoom,
                        'kapasitas'          => $kapasitas,
                        'luas'               => $luas,
                        'foto'               => null,
                        'deskripsi'          => $status === 'Aktif'
                            ? "{$kategoriRoom} {$kode}."
                            : 'Sedang tidak tersedia untuk reservasi.',
                        'status_aktif'       => $status,
                    ],
                );
            }
        }
    }

    private function nama(string $kode): string
    {
        return match (true) {
            str_contains($kode, 'HALL') => 'Convention Hall',
            str_contains($kode, '-M')   => "Kubikal {$kode}",
            default                     => "Ruang {$kode}",
        };
    }

    /**
     * Hapus unit 3B versi lama (R3, R4, RUANGKACA) yang tidak ada di denah baru.
     * Aman dijalankan berulang: reservasi DUMMY yang menunjuk tarif lama dialihkan ke
     * tarif aktif satuan sama; reservasi ASLI membuat unit di-skip (hanya warning).
     * Tarif milik 3B-R1/R2 (kini Tidak Aktif) ikut dirapikan.
     */
    private function bersihkan3BLama(): void
    {
        foreach (self::OBSOLETE_3B as $kode) {
            $f = Fasilitas::where('kode_fasilitas', $kode)->first();
            if (! $f) {
                continue;
            }

            $tarifIds = $f->tarifSewa()->pluck('id_tarif_sewa');
            if ($tarifIds->isNotEmpty()) {
                // Jangan hapus kalau ada reservasi ASLI (bukan dummy seeder) yang menunjuk ke sini.
                $adaAsli = Reservasi::whereIn('id_tarif_sewa', $tarifIds)
                    ->where('kode_reservasi', 'not like', 'RSV-DUMMY%')
                    ->exists();
                if ($adaAsli) {
                    $this->command?->warn("Lewati hapus {$kode}: masih dirujuk reservasi asli.");
                    continue;
                }

                // Alihkan reservasi dummy ke tarif aktif lain dengan satuan sewa sama.
                Reservasi::whereIn('id_tarif_sewa', $tarifIds)->get()->each(function (Reservasi $res) {
                    $satuan = $res->tarifSewa->jenisSewa->satuan->value;
                    $pengganti = TarifSewa::where('status_aktif', 'Aktif')
                        ->whereNotIn('id_tarif_sewa', [$res->id_tarif_sewa])
                        ->whereHas('jenisSewa', fn ($q) => $q->where('satuan', $satuan))
                        ->whereHas('fasilitas', fn ($q) => $q->where('status_aktif', 'Aktif')
                            ->whereNotIn('kode_fasilitas', self::OBSOLETE_3B))
                        ->first();
                    if ($pengganti) {
                        $res->update(['id_tarif_sewa' => $pengganti->id_tarif_sewa]);
                    }
                });

                TarifSewa::whereIn('id_tarif_sewa', $tarifIds)->delete();
            }

            $f->delete();
            $this->command?->info("Unit lama {$kode} dihapus (digantikan denah 3B baru).");
        }

        // 3B-R1 & 3B-R2 kini Tidak Aktif → tarif lamanya dilepas (hapus bila tak dirujuk).
        Fasilitas::whereIn('kode_fasilitas', ['3B-R1', '3B-R2'])->get()->each(function (Fasilitas $f) {
            $f->tarifSewa->each(function (TarifSewa $t) {
                Reservasi::where('id_tarif_sewa', $t->id_tarif_sewa)->exists()
                    ? $t->update(['status_aktif' => 'Tidak Aktif'])
                    : $t->delete();
            });
        });
    }

    /** Kapasitas SERAGAM per lantai (db-spec-fasilitas-final.md bagian 1). */
    private function kapasitas(string $nomor, string $kode): int
    {
        return match ($nomor) {
            '1', '2' => 10, // Working Space
            '3A'     => 2,  // Co-Working Space kubikal kecil
            '3B'     => 4,  // Co-Working Space kubikal besar
            '5'      => 75, // Convention Hall
            default  => 10,
        };
    }

    /**
     * Data luas per fasilitas BELUM final — pakai default sementara
     * (kubikal 3A/3B = 6 m², ruangan L1/L2 = 25 m², Convention Hall = 600 m²).
     * Kalau data real sudah ada: update kolom luas lalu jalankan
     * `php artisan tarif:hitung-ulang-bulanan`.
     */
    private function luas(string $nomor, int $kapasitas): float
    {
        return match ($nomor) {
            '3A', '3B' => 6.00,
            '5'        => 600.00,
            default    => 25.00, // L1/L2
        };
    }
}
