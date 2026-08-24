<?php

namespace App\Console\Commands;

use App\Enums\LockStatus;
use App\Enums\StatusReservasi;
use App\Models\Reservasi;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class PerbaruiStatusReservasiOtomatis extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reservasi:perbarui-status-otomatis';

    /**
     * @var string
     */
    protected $description = 'Ubah status Reservasi otomatis berdasarkan waktu penggunaan: Disetujui -> Selesai, dan Menunggu (belum diproses admin) -> Kadaluwarsa';

    /**
     * Jam tutup operasional gedung — sama dengan batas jam_selesai di TambahKeranjangRequest
     * (08.00–16.00). Reservasi Harian/Bulanan tidak punya jam_selesai, jadi waktu penggunaannya
     * dianggap berakhir begitu gedung tutup di tanggal_selesai, bukan tengah malam.
     */
    private const JAM_TUTUP_OPERASIONAL = '16:00:00';

    public function handle(): int
    {
        $jadiSelesai = $this->transisi(
            dari: StatusReservasi::Disetujui,
            ke: StatusReservasi::Selesai,
            keterangan: 'Waktu penggunaan reservasi ini sudah berakhir.',
        );

        $jadiKadaluwarsa = $this->transisi(
            dari: StatusReservasi::Menunggu,
            ke: StatusReservasi::Kadaluwarsa,
            keterangan: 'Tidak diproses sampai melewati waktu penggunaan yang diajukan.',
            releaseLock: true,
        );

        $this->info("Selesai. {$jadiSelesai} reservasi diubah menjadi 'Selesai', {$jadiKadaluwarsa} reservasi diubah menjadi 'Kadaluwarsa'.");

        return self::SUCCESS;
    }

    /**
     * Ambil baris berstatus $dari yang waktu penggunaannya sudah lewat, lalu ubah satu per
     * satu ke $ke lewat save() — BUKAN query()->update() massal — supaya ReservasiObserver
     * tetap mencatat baris Riwayat_Status untuk tiap perubahan.
     */
    private function transisi(StatusReservasi $dari, StatusReservasi $ke, string $keterangan, bool $releaseLock = false): int
    {
        $items = Reservasi::query()
            ->where('status_reservasi', $dari->value)
            ->where($this->scopeWaktuPenggunaanLewat(...))
            ->get();

        foreach ($items as $item) {
            $item->keteranganRiwayat = $keterangan;
            $item->status_reservasi = $ke;
            if ($releaseLock) {
                $item->lock_status = LockStatus::Released;
            }
            $item->save();
        }

        return $items->count();
    }

    /**
     * Reservasi Per Jam mengikuti jam_selesai pilihan pemesan sendiri; Reservasi Harian/Bulanan
     * (jam_selesai kosong) mengikuti jam operasional gedung — dianggap berakhir jam 16.00 di
     * tanggal_selesai, bukan tengah malam.
     */
    private function scopeWaktuPenggunaanLewat(Builder $query): void
    {
        $query->whereRaw(
            "TIMESTAMP(tanggal_selesai, COALESCE(jam_selesai, ?)) <= ?",
            [self::JAM_TUTUP_OPERASIONAL, now()],
        );
    }
}
