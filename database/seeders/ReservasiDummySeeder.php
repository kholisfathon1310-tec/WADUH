<?php

namespace Database\Seeders;

use App\Enums\StatusReservasi;
use App\Enums\StatusVerifikasi;
use App\Models\Admin;
use App\Models\DokumenPersyaratan;
use App\Models\Pemesan;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Contoh Reservasi dengan status bervariasi (Menunggu/Disetujui/Ditolak/Dibatalkan)
 * untuk keperluan testing alur Admin di Stage 3. Fasilitas & tarif berasal dari
 * FasilitasSeeder + TarifSewaSeeder (data real), bukan dummy.
 */
class ReservasiDummySeeder extends Seeder
{
    public function run(): void
    {
        if (Reservasi::count() > 0) {
            return; // idempoten — jangan tumpuk dummy.
        }

        $admin = Admin::first();
        $pemesan = Pemesan::factory()->count(4)->create();

        $specs = [
            ['Jam', StatusReservasi::Menunggu],
            ['Jam', StatusReservasi::Disetujui],
            ['Hari', StatusReservasi::Disetujui],
            ['Hari', StatusReservasi::Ditolak],
            ['Jam', StatusReservasi::Dibatalkan],
            ['Hari', StatusReservasi::Menunggu],
            ['Bulan', StatusReservasi::Disetujui],
            ['Hari', StatusReservasi::Disetujui],
        ];

        foreach ($specs as $i => [$satuan, $status]) {
            $tarif = $this->tarifBySatuan($satuan);
            if (! $tarif) {
                continue;
            }

            $reservasi = Reservasi::create($this->buildReservasi($tarif, $satuan, $status, $pemesan[$i % $pemesan->count()], $admin, $i));

            if ($satuan === 'Bulan') {
                DokumenPersyaratan::create([
                    'id_reservasi'      => $reservasi->id_reservasi,
                    'jenis_dokumen'     => 'Company Profile',
                    'nama_file'         => 'company-profile-dummy.pdf',
                    'lokasi_file'       => 'dokumen/dummy/company-profile.pdf',
                    'tanggal_upload'    => now(),
                    'status_verifikasi' => StatusVerifikasi::Menunggu->value,
                ]);
            }
        }
    }

    private function buildReservasi(TarifSewa $tarif, string $satuan, StatusReservasi $status, Pemesan $pemesan, ?Admin $admin, int $i): array
    {
        $mulai = Carbon::today()->addDays(3 + $i);
        $harga = (float) $tarif->harga;
        $adminId = in_array($status, [StatusReservasi::Disetujui, StatusReservasi::Ditolak], true) ? $admin?->id_admin : null;

        [$tanggalSelesai, $jamMulai, $jamSelesai, $durasi] = match ($satuan) {
            'Jam'   => [$mulai->toDateString(), '09:00', '12:00', 3],
            'Bulan' => [(clone $mulai)->addMonths(3)->toDateString(), null, null, 3],
            default => [(clone $mulai)->addDays(1)->toDateString(), null, null, 2], // Hari
        };

        return [
            'id_pemesan'       => $pemesan->id_pemesan,
            'id_tarif_sewa'    => $tarif->id_tarif_sewa,
            'id_admin'         => $adminId,
            'kode_reservasi'   => sprintf('RSV-DUMMY%03d', $i + 1),
            'kode_transaksi'   => sprintf('TRX-DUMMY%03d', $i + 1),
            'tanggal_mulai'    => $mulai->toDateString(),
            'tanggal_selesai'  => $tanggalSelesai,
            'jam_mulai'        => $jamMulai,
            'jam_selesai'      => $jamSelesai,
            'durasi'           => $durasi,
            'jumlah_pengguna'  => fake()->numberBetween(1, 10),
            'keperluan'        => fake()->sentence(5),
            'harga_satuan'     => $harga,
            'total_biaya'      => $harga * $durasi,
            'status_reservasi' => $status->value,
            'lock_status'      => match ($status) {
                StatusReservasi::Disetujui => 'confirmed',
                StatusReservasi::Ditolak, StatusReservasi::Dibatalkan => 'released',
                default => 'pending_approval',
            },
            'lock_expires_at'  => null,
            'tanggal_diproses' => $adminId ? now() : null,
        ];
    }

    private function tarifBySatuan(string $satuan): ?TarifSewa
    {
        return TarifSewa::query()
            ->where('status_aktif', 'Aktif')
            ->whereHas('jenisSewa', fn ($q) => $q->where('satuan', $satuan))
            ->inRandomOrder()
            ->first();
    }
}
