<?php

namespace App\Console\Commands;

use App\Enums\LockStatus;
use App\Enums\SatuanSewa;
use App\Enums\StatusReservasi;
use App\Models\Reservasi;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class PerbaruiStatusReservasiOtomatis extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reservasi:perbarui-status-otomatis';

    /**
     * @var string
     */
    protected $description = 'Ubah status Reservasi otomatis berdasarkan waktu: Disetujui -> Selesai setelah masa penggunaan berakhir, dan Menunggu (belum diproses admin) -> Kadaluwarsa setelah batas persetujuan terlewati (beda per jenis sewa, dievaluasi per ruangan/baris)';

    /**
     * Jam tutup operasional gedung — sama dengan batas jam_selesai di TambahKeranjangRequest
     * (08.00–16.00). Reservasi Harian/Bulanan tidak punya jam_selesai, jadi waktu penggunaannya
     * dianggap berakhir begitu gedung tutup, bukan tengah malam.
     */
    private const JAM_TUTUP_OPERASIONAL = '16:00:00';

    public function handle(): int
    {
        $jadiSelesai = $this->transisi(
            dari: StatusReservasi::Disetujui,
            ke: StatusReservasi::Selesai,
            keterangan: 'Waktu penggunaan reservasi ini sudah berakhir.',
            scope: $this->scopeMasaPenggunaanBerakhir(...),
        );

        $jadiKadaluwarsa = $this->kadaluwarsakanRuanganTerlambat();

        $this->info("Selesai. {$jadiSelesai} reservasi diubah menjadi 'Selesai', {$jadiKadaluwarsa} reservasi diubah menjadi 'Kadaluwarsa'.");

        return self::SUCCESS;
    }

    /**
     * Ambil baris berstatus $dari yang cocok $scope, lalu ubah satu per satu ke $ke lewat
     * save() — BUKAN query()->update() massal — supaya ReservasiObserver tetap mencatat
     * baris Riwayat_Status untuk tiap perubahan.
     */
    private function transisi(StatusReservasi $dari, StatusReservasi $ke, string $keterangan, callable $scope, bool $releaseLock = false): int
    {
        $items = Reservasi::query()
            ->where('status_reservasi', $dari->value)
            ->where($scope)
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
     * Disetujui -> Selesai: masa penggunaan berakhir begitu tanggal_selesai (+ jam_selesai
     * kalau Per Jam, atau jam tutup operasional untuk Harian/Bulanan) sudah lewat. Dievaluasi
     * per baris (bukan per pemesanan) karena tiap ruangan genuinely selesai dipakai pada
     * tanggalnya masing-masing, walau disetujui bersamaan dalam satu pemesanan.
     */
    private function scopeMasaPenggunaanBerakhir(Builder $query): void
    {
        $query->whereRaw(
            'TIMESTAMP(tanggal_selesai, COALESCE(jam_selesai, ?)) <= ?',
            [self::JAM_TUTUP_OPERASIONAL, now()],
        );
    }

    /**
     * Menunggu -> Kadaluwarsa, dievaluasi per RUANGAN/BARIS — bukan digabung per pemesanan.
     * Kalau 1 pemesanan berisi 2+ ruangan berbeda, tiap ruangan kadaluwarsa mengikuti batas
     * waktunya sendiri-sendiri (sama seperti transisi Disetujui -> Selesai di atas, yang juga
     * per baris): ruangan yang batasnya sudah lewat jadi Kadaluwarsa duluan, sementara ruangan
     * lain di pemesanan yang sama yang belum lewat batasnya tetap Menunggu.
     *
     * Batas waktu per ruangan beda per jenis sewa:
     *   - Per Jam   : begitu jam_selesai (pada tanggal_mulai) yang diajukan sudah lewat.
     *   - Per Hari  : begitu jam operasional (16.00) di tanggal_mulai sudah lewat.
     *   - Per Bulan : begitu tanggal_mulai sudah terlewati (jadi keesokan harinya).
     */
    private function kadaluwarsakanRuanganTerlambat(): int
    {
        $now = now();

        $menunggu = Reservasi::query()
            ->where('status_reservasi', StatusReservasi::Menunggu->value)
            ->with('tarifSewa.jenisSewa')
            ->get();

        $count = 0;

        foreach ($menunggu as $item) {
            if ($this->batasPersetujuan($item)->gt($now)) {
                continue;
            }

            $item->keteranganRiwayat = 'Tidak diproses admin sampai melewati batas waktu persetujuan ruangan ini.';
            $item->status_reservasi = StatusReservasi::Kadaluwarsa;
            $item->lock_status = LockStatus::Released;
            $item->save();
            $count++;
        }

        return $count;
    }

    private function batasPersetujuan(Reservasi $reservasi): Carbon
    {
        $satuan = $reservasi->tarifSewa->jenisSewa->satuan;
        $tanggalMulai = $reservasi->tanggal_mulai->toDateString();

        return match ($satuan) {
            SatuanSewa::Jam   => Carbon::parse($tanggalMulai.' '.($reservasi->jam_selesai ?: self::JAM_TUTUP_OPERASIONAL)),
            SatuanSewa::Hari  => Carbon::parse($tanggalMulai.' '.self::JAM_TUTUP_OPERASIONAL),
            SatuanSewa::Bulan => $reservasi->tanggal_mulai->copy()->addDay()->startOfDay(),
        };
    }
}
