<?php

namespace Database\Factories;

use App\Enums\LockStatus;
use App\Enums\StatusReservasi;
use App\Models\Pemesan;
use App\Models\TarifSewa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservasi>
 */
class ReservasiFactory extends Factory
{
    public function definition(): array
    {
        $mulai = Carbon::today()->addDays(fake()->numberBetween(1, 30));
        $durasi = fake()->numberBetween(1, 3);
        $selesai = (clone $mulai)->addDays($durasi - 1);
        $harga = fake()->numberBetween(100_000, 1_500_000);

        return [
            'id_pemesan'       => Pemesan::factory(),
            'id_tarif_sewa'    => TarifSewa::factory(),
            'id_admin'         => null,
            'kode_reservasi'   => 'RSV-'.strtoupper(Str::random(10)),
            'kode_transaksi'   => 'TRX-'.strtoupper(Str::random(10)),
            'tanggal_mulai'    => $mulai->toDateString(),
            'tanggal_selesai'  => $selesai->toDateString(),
            'jam_mulai'        => null,
            'jam_selesai'      => null,
            'durasi'           => $durasi,
            'jumlah_pengguna'  => fake()->numberBetween(1, 50),
            'keperluan'        => fake()->sentence(6),
            'harga_satuan'     => $harga,
            'total_biaya'      => $harga * $durasi,
            'status_reservasi' => StatusReservasi::Menunggu->value,
            'lock_status'      => LockStatus::PendingApproval->value,
            'lock_expires_at'  => null,
            'tanggal_diproses' => null,
        ];
    }

    /** Slot per jam pada satu hari. */
    public function perJam(): static
    {
        return $this->state(function () {
            $mulai = Carbon::today()->addDays(fake()->numberBetween(1, 30));
            $jamMulai = fake()->numberBetween(8, 15);
            $durasi = fake()->numberBetween(1, 3);

            return [
                'tanggal_mulai'   => $mulai->toDateString(),
                'tanggal_selesai' => $mulai->toDateString(),
                'jam_mulai'       => sprintf('%02d:00', $jamMulai),
                'jam_selesai'     => sprintf('%02d:00', $jamMulai + $durasi),
                'durasi'          => $durasi,
            ];
        });
    }

    public function status(StatusReservasi $status, ?int $idAdmin = null): static
    {
        return $this->state(fn () => [
            'status_reservasi' => $status->value,
            'id_admin'         => $idAdmin,
            'tanggal_diproses' => $idAdmin ? now() : null,
            'lock_status'      => match ($status) {
                StatusReservasi::Disetujui => LockStatus::Confirmed->value,
                StatusReservasi::Ditolak, StatusReservasi::Dibatalkan => LockStatus::Released->value,
                default => LockStatus::PendingApproval->value,
            },
        ]);
    }
}
