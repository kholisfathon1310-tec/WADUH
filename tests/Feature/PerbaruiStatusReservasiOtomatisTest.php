<?php

namespace Tests\Feature;

use App\Enums\StatusReservasi;
use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\Pemesan;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Uji command reservasi:perbarui-status-otomatis — batas kadaluwarsa berbeda per jenis
 * sewa (Jam/Hari/Bulan) dan transisi Disetujui -> Selesai.
 */
class PerbaruiStatusReservasiOtomatisTest extends TestCase
{
    use DatabaseTransactions;

    private function tarifSatuan(string $satuan): TarifSewa
    {
        $jenis = JenisSewa::where('satuan', $satuan)->firstOrFail();
        $fasilitas = Fasilitas::factory()->create(['status_aktif' => 'Aktif']);

        return TarifSewa::factory()
            ->create([
                'id_fasilitas'  => $fasilitas->id_fasilitas,
                'id_jenis_sewa' => $jenis->id_jenis_sewa,
                'status_aktif'  => 'Aktif',
            ])
            ->load('fasilitas', 'jenisSewa');
    }

    private function buatReservasi(TarifSewa $tarif, array $overrides): Reservasi
    {
        return Reservasi::create(array_merge([
            'id_pemesan'       => Pemesan::factory()->create()->id_pemesan,
            'id_tarif_sewa'    => $tarif->id_tarif_sewa,
            'id_admin'         => null,
            'kode_reservasi'   => 'RSV-'.strtoupper(Str::random(10)),
            'kode_transaksi'   => 'TRX-'.strtoupper(Str::random(10)),
            'jumlah_pengguna'  => 2,
            'keperluan'        => 'Uji status otomatis',
            'harga_satuan'     => 100000,
            'total_biaya'      => 100000,
            'status_reservasi' => StatusReservasi::Menunggu->value,
            'lock_status'      => 'pending_approval',
        ], $overrides));
    }

    public function test_per_jam_kadaluwarsa_setelah_jam_selesai_lewat_dan_belum_disetujui(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(12, 0));

        try {
            $tarif = $this->tarifSatuan('Jam');
            $tgl = Carbon::today()->toDateString();

            // Jam selesai 11.00 sudah lewat (sekarang 12.00) → kadaluwarsa.
            $lewat = $this->buatReservasi($tarif, [
                'tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
                'jam_mulai' => '09:00', 'jam_selesai' => '11:00', 'durasi' => 2,
            ]);

            // Jam selesai 14.00 belum lewat → tetap Menunggu.
            $belumLewat = $this->buatReservasi($tarif, [
                'tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
                'jam_mulai' => '13:00', 'jam_selesai' => '14:00', 'durasi' => 1,
            ]);

            $this->artisan('reservasi:perbarui-status-otomatis')->assertExitCode(0);

            $this->assertSame('Kadaluwarsa', $lewat->fresh()->status_reservasi->value);
            $this->assertSame('released', $lewat->fresh()->lock_status->value);
            $this->assertSame('Menunggu', $belumLewat->fresh()->status_reservasi->value);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_per_hari_kadaluwarsa_jika_sampai_jam_16_di_tanggal_mulai_belum_disetujui(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(17, 0)); // Sudah lewat jam tutup 16.00 hari ini.

        try {
            $tarif = $this->tarifSatuan('Hari');
            $tgl = Carbon::today()->toDateString();
            $selesai = Carbon::today()->addDays(2)->toDateString();

            $reservasi = $this->buatReservasi($tarif, [
                'tanggal_mulai' => $tgl, 'tanggal_selesai' => $selesai, 'durasi' => 3,
            ]);

            $this->artisan('reservasi:perbarui-status-otomatis')->assertExitCode(0);

            $this->assertSame('Kadaluwarsa', $reservasi->fresh()->status_reservasi->value);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_per_hari_belum_kadaluwarsa_sebelum_jam_16_di_tanggal_mulai(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(10, 0)); // Masih pagi, belum jam tutup.

        try {
            $tarif = $this->tarifSatuan('Hari');
            $tgl = Carbon::today()->toDateString();
            $selesai = Carbon::today()->addDays(2)->toDateString();

            $reservasi = $this->buatReservasi($tarif, [
                'tanggal_mulai' => $tgl, 'tanggal_selesai' => $selesai, 'durasi' => 3,
            ]);

            $this->artisan('reservasi:perbarui-status-otomatis')->assertExitCode(0);

            $this->assertSame('Menunggu', $reservasi->fresh()->status_reservasi->value);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_per_bulan_kadaluwarsa_jika_tanggal_mulai_sudah_terlewati_dan_belum_disetujui(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(9, 0));

        try {
            $tarif = $this->tarifSatuan('Bulan');

            // tanggal_mulai KEMARIN → sudah terlewati → kadaluwarsa.
            $lewat = $this->buatReservasi($tarif, [
                'tanggal_mulai'   => Carbon::yesterday()->toDateString(),
                'tanggal_selesai' => Carbon::yesterday()->addMonths(3)->toDateString(),
                'durasi'          => 3,
            ]);

            // tanggal_mulai HARI INI → belum terlewati → tetap Menunggu.
            $belumLewat = $this->buatReservasi($tarif, [
                'tanggal_mulai'   => Carbon::today()->toDateString(),
                'tanggal_selesai' => Carbon::today()->addMonths(3)->toDateString(),
                'durasi'          => 3,
            ]);

            $this->artisan('reservasi:perbarui-status-otomatis')->assertExitCode(0);

            $this->assertSame('Kadaluwarsa', $lewat->fresh()->status_reservasi->value);
            $this->assertSame('Menunggu', $belumLewat->fresh()->status_reservasi->value);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_disetujui_menjadi_selesai_setelah_masa_penggunaan_berakhir(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(12, 0));

        try {
            $tarif = $this->tarifSatuan('Jam');
            $tgl = Carbon::today()->toDateString();

            $reservasi = $this->buatReservasi($tarif, [
                'tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
                'jam_mulai' => '09:00', 'jam_selesai' => '11:00', 'durasi' => 2,
                'status_reservasi' => StatusReservasi::Disetujui->value,
                'lock_status' => 'confirmed',
            ]);

            $this->artisan('reservasi:perbarui-status-otomatis')->assertExitCode(0);

            $this->assertSame('Selesai', $reservasi->fresh()->status_reservasi->value);
        } finally {
            Carbon::setTestNow();
        }
    }
}
