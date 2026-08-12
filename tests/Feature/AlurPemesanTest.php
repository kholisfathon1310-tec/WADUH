<?php

namespace Tests\Feature;

use App\Models\Pemesan;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use App\Services\AvailabilityService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Uji alur Pemesan Stage 2 memakai data hasil seeder (DatabaseTransactions → rollback,
 * data seed tetap utuh). Jalankan setelah `php artisan migrate:fresh --seed`.
 */
class AlurPemesanTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Buat fasilitas + tarif KHUSUS test (bukan ambil data seeded) supaya tidak
     * bentrok dengan reservasi asli yang mungkin sudah dibuat pengguna.
     * DatabaseTransactions me-rollback semuanya setelah test.
     */
    private function tarifSatuan(string $satuan): TarifSewa
    {
        $jenis = \App\Models\JenisSewa::where('satuan', $satuan)->firstOrFail();
        $fasilitas = \App\Models\Fasilitas::factory()->create(['status_aktif' => 'Aktif']);

        return TarifSewa::factory()
            ->create([
                'id_fasilitas'  => $fasilitas->id_fasilitas,
                'id_jenis_sewa' => $jenis->id_jenis_sewa,
                'status_aktif'  => 'Aktif',
            ])
            ->load('fasilitas', 'jenisSewa');
    }

    public function test_halaman_alur_bisa_dibuka(): void
    {
        $tarif = $this->tarifSatuan('Jam');
        $kategori = $tarif->fasilitas->kategori_fasilitas;

        $this->get('/reservasi')->assertOk();
        $this->get('/reservasi/'.rawurlencode($kategori).'/jenis-sewa')->assertOk();
        $this->get('/reservasi/'.rawurlencode($kategori).'/lantai?jenis='.$tarif->id_jenis_sewa)->assertOk();
        $this->get('/reservasi/'.rawurlencode($kategori).'/denah/'.$tarif->fasilitas->id_lantai.'?jenis='.$tarif->id_jenis_sewa)->assertOk();
        $this->get('/reservasi/fasilitas/'.$tarif->fasilitas->id_fasilitas.'?jenis='.$tarif->id_jenis_sewa)->assertOk();
        $this->get('/cek-status')->assertOk();
    }

    public function test_tambah_keranjang_dan_hold_mengunci_session_lain(): void
    {
        $tarif = $this->tarifSatuan('Jam');
        $fas = $tarif->fasilitas;
        $tgl = Carbon::today()->addDays(5)->toDateString();

        $res = $this->post('/reservasi/keranjang', [
            'id_fasilitas'    => $fas->id_fasilitas,
            'id_tarif_sewa'   => $tarif->id_tarif_sewa,
            'tanggal_mulai'   => $tgl,
            'jam_mulai'       => '09:00',
            'jam_selesai'     => '11:00',
            'jumlah_pengguna' => 2,
            'keperluan'       => 'Rapat',
        ]);

        $res->assertRedirect(route('reservasi.checkout.form'));
        $this->assertCount(1, session('reservasi_cart'));

        // Hold aktif → session lain terkunci, session pemilik tetap bebas.
        $svc = app(AvailabilityService::class);
        $slot = ['tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl, 'jam_mulai' => '09:00', 'jam_selesai' => '11:00'];
        $this->assertFalse($svc->slotAvailable($fas->id_fasilitas, $slot, 'session-lain'), 'Slot harus terkunci untuk session lain');
        $this->assertTrue($svc->slotAvailable($fas->id_fasilitas, $slot, session()->getId()), 'Slot harus bebas untuk pemilik hold');
    }

    public function test_checkout_membuat_pemesan_dan_reservasi(): void
    {
        $tarif = $this->tarifSatuan('Jam');
        $item = app(\App\Services\CartService::class)->buildItem($tarif, [
            'tanggal_mulai'   => Carbon::today()->addDays(6)->toDateString(),
            'jam_mulai'       => '13:00',
            'jam_selesai'     => '15:00',
            'jumlah_pengguna' => 3,
            'keperluan'       => 'Workshop',
        ]);

        $before = Reservasi::count();

        $res = $this->withSession(['reservasi_cart' => [$item]])->post('/reservasi/checkout', [
            'nama_lengkap' => 'Uji Pemesan',
            'email'        => 'uji.stage2@example.com',
            'no_telepon'   => '08123456789',
            'usia'         => 30,
            'pekerjaan'    => 'Staff',
            'alamat'       => 'Jl. Uji No. 1',
        ]);

        $res->assertRedirect(route('reservasi.sukses'));
        $this->assertSame($before + 1, Reservasi::count());
        $this->assertDatabaseHas('pemesan', ['email' => 'uji.stage2@example.com', 'nama_lengkap' => 'Uji Pemesan']);

        $baru = Reservasi::latest('id_reservasi')->first();
        $this->assertSame('Menunggu', $baru->status_reservasi->value);
        $this->assertSame('pending_approval', $baru->lock_status->value);
        $this->assertNull($baru->id_admin);
        // Kode simpel: RSV-XXXX, dan untuk checkout 1 item kode reservasi = kode dasar.
        $this->assertMatchesRegularExpression('/^RSV-[A-Z2-9]{4}$/', $baru->kode_transaksi);
        $this->assertSame($baru->kode_transaksi, $baru->kode_reservasi);
        $this->assertEmpty(session('reservasi_cart', []), 'Keranjang harus kosong setelah checkout');
    }

    public function test_pembatalan_oleh_pemesan_tercatat_di_riwayat_tanpa_admin(): void
    {
        $pemesan = Pemesan::factory()->create();
        $tarif = $this->tarifSatuan('Hari');
        $reservasi = Reservasi::create([
            'id_pemesan'       => $pemesan->id_pemesan,
            'id_tarif_sewa'    => $tarif->id_tarif_sewa,
            'id_admin'         => null,
            'kode_reservasi'   => 'RSV-TESTCANCEL',
            'kode_transaksi'   => 'TRX-TESTCANCEL',
            'tanggal_mulai'    => Carbon::today()->addDays(10)->toDateString(),
            'tanggal_selesai'  => Carbon::today()->addDays(11)->toDateString(),
            'durasi'           => 2,
            'jumlah_pengguna'  => 5,
            'keperluan'        => 'Acara',
            'harga_satuan'     => 1000,
            'total_biaya'      => 2000,
            'status_reservasi' => 'Menunggu',
            'lock_status'      => 'pending_approval',
        ]);

        $this->post('/reservasi/RSV-TESTCANCEL/batalkan')->assertRedirect();

        $reservasi->refresh();
        $this->assertSame('Dibatalkan', $reservasi->status_reservasi->value);

        $riwayat = $reservasi->riwayatStatus()->latest('id_riwayat')->first();
        $this->assertNotNull($riwayat, 'Perubahan status harus tercatat di Riwayat_Status');
        $this->assertNull($riwayat->id_admin, 'Pembatalan pemesan tidak punya admin');
        $this->assertSame('Dibatalkan oleh pemesan', $riwayat->keterangan);
        $this->assertNull($reservasi->tanggal_diproses, 'tanggal_diproses hanya diisi saat admin memproses');
    }

    public function test_multi_pilih_denah_satu_form_untuk_semua_ruangan(): void
    {
        // Pilih 3 ruangan di denah → isi SATU form jadwal → ketiganya masuk keranjang sekaligus.
        $jenis = \App\Models\JenisSewa::where('satuan', 'Jam')->firstOrFail();
        $tarifs = collect(range(1, 3))->map(function () use ($jenis) {
            $f = \App\Models\Fasilitas::factory()->create(['status_aktif' => 'Aktif', 'kapasitas' => 20]);

            return TarifSewa::factory()->create([
                'id_fasilitas' => $f->id_fasilitas, 'id_jenis_sewa' => $jenis->id_jenis_sewa, 'status_aktif' => 'Aktif',
            ]);
        });

        $tgl = Carbon::today()->addDays(4)->toDateString();
        $ids = $tarifs->map(fn ($t) => $t->id_fasilitas);

        $this->post('/reservasi/keranjang', [
            'id_fasilitas'    => $ids[0],
            'id_tarif_sewa'   => $tarifs[0]->id_tarif_sewa,
            'antrian'         => $ids[1].','.$ids[2],
            'tanggal_mulai'   => $tgl,
            'jam_mulai'       => '09:00',
            'jam_selesai'     => '11:00',
            'jumlah_pengguna' => 2,
            'keperluan'       => 'Rapat',
        ])->assertRedirect(route('reservasi.checkout.form'));

        $cart = session('reservasi_cart');
        $this->assertCount(3, $cart);
        $this->assertEqualsCanonicalizing($ids->all(), array_column($cart, 'id_fasilitas'));
        // Jadwal sama untuk semua ruangan.
        $this->assertSame([$tgl, $tgl, $tgl], array_column($cart, 'tanggal_mulai'));
    }

    public function test_convention_hall_harian_otomatis_satu_hari(): void
    {
        // CH hanya sewa harian 1 hari (8 jam): tanpa input tanggal_selesai pun harus lolos,
        // tanggal_selesai otomatis = tanggal_mulai dan durasi 1 hari.
        $jenis = \App\Models\JenisSewa::where('satuan', 'Hari')->firstOrFail();
        $fasilitas = \App\Models\Fasilitas::factory()->create([
            'kategori_fasilitas' => 'Convention Hall',
            'status_aktif'       => 'Aktif',
            'kapasitas'          => 75, // eksplisit — factory acak bisa < jumlah_pengguna
        ]);
        $tarif = TarifSewa::factory()->create([
            'id_fasilitas'  => $fasilitas->id_fasilitas,
            'id_jenis_sewa' => $jenis->id_jenis_sewa,
            'status_aktif'  => 'Aktif',
        ]);

        $tgl = Carbon::today()->addDays(9)->toDateString();
        $this->post('/reservasi/keranjang', [
            'id_fasilitas'    => $fasilitas->id_fasilitas,
            'id_tarif_sewa'   => $tarif->id_tarif_sewa,
            'tanggal_mulai'   => $tgl,
            // sengaja TANPA tanggal_selesai
            'jumlah_pengguna' => 50,
            'keperluan'       => 'Seminar',
        ])->assertRedirect(route('reservasi.checkout.form'));

        $item = session('reservasi_cart')[0];
        $this->assertSame($tgl, $item['tanggal_selesai']);
        $this->assertSame(1, $item['durasi']);
    }

    public function test_checkout_bulan_wajib_dokumen(): void
    {
        $tarif = $this->tarifSatuan('Bulan');
        $item = app(\App\Services\CartService::class)->buildItem($tarif, [
            'tanggal_mulai'   => Carbon::today()->addDays(7)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addDays(7)->addMonths(3)->toDateString(),
            'jumlah_pengguna' => 10,
            'keperluan'       => 'Kantor sementara',
        ]);

        // Tanpa file dokumen → harus gagal validasi.
        $this->withSession(['reservasi_cart' => [$item]])
            ->post('/reservasi/checkout', [
                'nama_lengkap' => 'PT Uji',
                'email'        => 'pt.uji@example.com',
                'no_telepon'   => '0215550123',
                'usia'         => 40,
                'pekerjaan'    => 'Direktur',
                'alamat'       => 'Jl. Korporat 9',
            ])
            ->assertSessionHasErrors('dokumen');
    }
}
