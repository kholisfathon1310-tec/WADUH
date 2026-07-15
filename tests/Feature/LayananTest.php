<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\Pemesan;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use App\Services\AvailabilityService;
use App\Services\CartService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** Service inti (Cart, Availability) + artisan command. */
class LayananTest extends TestCase
{
    use DatabaseTransactions;

    private function tarif(string $satuan, array $fasilitasAttrs = []): TarifSewa
    {
        $jenis = JenisSewa::where('satuan', $satuan)->firstOrFail();
        $f = Fasilitas::factory()->create(array_merge(['status_aktif' => 'Aktif', 'kapasitas' => 20], $fasilitasAttrs));

        return TarifSewa::factory()
            ->create(['id_fasilitas' => $f->id_fasilitas, 'id_jenis_sewa' => $jenis->id_jenis_sewa, 'status_aktif' => 'Aktif', 'harga' => 100_000])
            ->load('fasilitas', 'jenisSewa');
    }

    private function reservasiPada(TarifSewa $tarif, array $attrs): Reservasi
    {
        return Reservasi::create(array_merge([
            'id_pemesan'       => Pemesan::factory()->create()->id_pemesan,
            'id_tarif_sewa'    => $tarif->id_tarif_sewa,
            'kode_reservasi'   => 'RSV-LYN'.fake()->unique()->numerify('###'),
            'kode_transaksi'   => 'RSV-LYNT'.fake()->unique()->numerify('###'),
            'durasi'           => 1, 'jumlah_pengguna' => 2, 'keperluan' => 'Uji layanan',
            'harga_satuan'     => 100_000, 'total_biaya' => 100_000,
            'status_reservasi' => 'Disetujui', 'lock_status' => 'confirmed',
        ], $attrs));
    }

    // ---------------- CartService ----------------

    public function test_cart_build_item_menghitung_durasi_per_satuan(): void
    {
        $svc = app(CartService::class);
        $base = ['jumlah_pengguna' => 2, 'keperluan' => 'Uji'];

        // Jam: 09:00–11:30 → dibulatkan ke atas = 3 jam.
        $jam = $svc->buildItem($this->tarif('Jam'), $base + ['tanggal_mulai' => '2026-08-01', 'jam_mulai' => '09:00', 'jam_selesai' => '11:30']);
        $this->assertSame(3, $jam['durasi']);
        $this->assertSame(300_000.0, $jam['total_biaya']);
        $this->assertSame('2026-08-01', $jam['tanggal_selesai']); // per jam = satu hari

        // Hari: 1–3 Agustus inklusif = 3 hari.
        $hari = $svc->buildItem($this->tarif('Hari'), $base + ['tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2026-08-03']);
        $this->assertSame(3, $hari['durasi']);

        // Bulan: 1 Agu – 1 Nov = 3 bulan.
        $bulan = $svc->buildItem($this->tarif('Bulan'), $base + ['tanggal_mulai' => '2026-08-01', 'tanggal_selesai' => '2026-11-01']);
        $this->assertSame(3, $bulan['durasi']);
    }

    public function test_cart_operasi_session(): void
    {
        $this->get('/reservasi'); // inisialisasi session
        $svc = app(CartService::class);

        $svc->add(['nama_fasilitas' => 'A', 'satuan' => 'Jam', 'total_biaya' => 100]);
        $svc->add(['nama_fasilitas' => 'B', 'satuan' => 'Bulan', 'total_biaya' => 250]);
        $this->assertSame(2, $svc->count());
        $this->assertSame(350.0, $svc->total());
        $this->assertTrue($svc->hasBulan());

        $dihapus = $svc->remove(0);
        $this->assertSame('A', $dihapus['nama_fasilitas']);
        $this->assertSame(1, $svc->count());

        $svc->clear();
        $this->assertTrue($svc->isEmpty());
    }

    // ---------------- AvailabilityService ----------------

    public function test_status_warna_hijau_kuning_merah(): void
    {
        $svc = app(AvailabilityService::class);
        $tarif = $this->tarif('Hari');
        $f = $tarif->fasilitas;

        // Reservasi Disetujui menempati HANYA hari-1.
        $d1 = Carbon::today()->addDays(40);
        $this->reservasiPada($tarif, ['tanggal_mulai' => $d1->toDateString(), 'tanggal_selesai' => $d1->toDateString()]);

        $slot = fn ($mulai, $selesai) => ['tanggal_mulai' => $mulai, 'tanggal_selesai' => $selesai, 'jam_mulai' => null, 'jam_selesai' => null];

        $this->assertSame('merah', $svc->statusFasilitas($f, $slot($d1->toDateString(), $d1->toDateString()), 'sess'));
        $this->assertSame('kuning', $svc->statusFasilitas($f, $slot($d1->toDateString(), $d1->copy()->addDay()->toDateString()), 'sess'));
        $this->assertSame('hijau', $svc->statusFasilitas($f, $slot($d1->copy()->addDay()->toDateString(), $d1->copy()->addDay()->toDateString()), 'sess'));
    }

    public function test_hold_milik_session_lain_memblokir_dan_bisa_dilepas(): void
    {
        $svc = app(AvailabilityService::class);
        $tarif = $this->tarif('Jam');
        $tgl = Carbon::today()->addDays(41)->toDateString();
        $item = ['id_fasilitas' => $tarif->id_fasilitas, 'id_tarif_sewa' => $tarif->id_tarif_sewa,
            'tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl, 'jam_mulai' => '09:00', 'jam_selesai' => '11:00'];
        $slot = ['tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl, 'jam_mulai' => '09:00', 'jam_selesai' => '11:00'];

        $svc->putHold($item, 'sess-pemilik');
        $this->assertFalse($svc->slotAvailable($tarif->id_fasilitas, $slot, 'sess-lain'), 'terkunci untuk session lain');
        $this->assertTrue($svc->slotAvailable($tarif->id_fasilitas, $slot, 'sess-pemilik'), 'bebas untuk pemilik hold');

        $svc->releaseHold($item);
        $this->assertTrue($svc->slotAvailable($tarif->id_fasilitas, $slot, 'sess-lain'), 'bebas setelah dilepas');
    }

    public function test_scope_tersedia_mengabaikan_temporary_hold_kedaluwarsa(): void
    {
        $tarif = $this->tarif('Hari');
        $tgl = Carbon::today()->addDays(42)->toDateString();

        $aktif = $this->reservasiPada($tarif, ['tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
            'status_reservasi' => 'Menunggu', 'lock_status' => 'temporary_hold', 'lock_expires_at' => now()->addMinutes(10)]);
        $kedaluwarsa = $this->reservasiPada($tarif, ['tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
            'status_reservasi' => 'Menunggu', 'lock_status' => 'temporary_hold', 'lock_expires_at' => now()->subMinutes(10)]);

        $ids = Reservasi::tersedia()->pluck('id_reservasi');
        $this->assertTrue($ids->contains($aktif->id_reservasi), 'hold aktif masih memblokir');
        $this->assertFalse($ids->contains($kedaluwarsa->id_reservasi), 'hold kedaluwarsa tidak memblokir');
    }

    // ---------------- Artisan command ----------------

    public function test_command_release_expired_locks(): void
    {
        $tarif = $this->tarif('Hari');
        $tgl = Carbon::today()->addDays(43)->toDateString();

        $kedaluwarsa = $this->reservasiPada($tarif, ['tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
            'status_reservasi' => 'Menunggu', 'lock_status' => 'temporary_hold', 'lock_expires_at' => now()->subHour()]);
        $masihAktif = $this->reservasiPada($tarif, ['tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
            'status_reservasi' => 'Menunggu', 'lock_status' => 'temporary_hold', 'lock_expires_at' => now()->addHour()]);

        $this->artisan('reservasi:release-expired-locks')->assertSuccessful();

        $this->assertSame('released', $kedaluwarsa->fresh()->lock_status->value);
        $this->assertSame('temporary_hold', $masihAktif->fresh()->lock_status->value);
    }

    public function test_command_tarif_hitung_ulang_bulanan(): void
    {
        $tarifBulan = $this->tarif('Bulan', ['kategori_fasilitas' => 'Working Space', 'luas' => 25]);

        // Data luas real masuk → command menghitung ulang 150rb × luas baru.
        $tarifBulan->fasilitas->update(['luas' => 40]);
        $this->artisan('tarif:hitung-ulang-bulanan')->assertSuccessful();

        $this->assertSame(150_000.0 * 40, (float) $tarifBulan->fresh()->harga);
    }
}
