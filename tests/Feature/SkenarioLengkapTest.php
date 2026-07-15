<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Faktur;
use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\Laporan;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Skenario END-TO-END lintas dua role: perjalanan pemesan dari browsing sampai
 * mendapat faktur yang diterbitkan admin, plus cabang penolakan & pembatalan.
 */
class SkenarioLengkapTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): Admin
    {
        return Admin::firstOrFail();
    }

    /** @return array{0: TarifSewa, 1: Fasilitas} */
    private function ruang(string $satuan, string $kategori = 'Co-Working Space', float $harga = 100_000): array
    {
        $jenis = JenisSewa::where('satuan', $satuan)->firstOrFail();
        $f = Fasilitas::factory()->create(['status_aktif' => 'Aktif', 'kapasitas' => 20, 'kategori_fasilitas' => $kategori]);
        $t = TarifSewa::factory()->create([
            'id_fasilitas' => $f->id_fasilitas, 'id_jenis_sewa' => $jenis->id_jenis_sewa,
            'status_aktif' => 'Aktif', 'harga' => $harga,
        ]);

        return [$t->load('fasilitas', 'jenisSewa'), $f];
    }

    private function dataDiri(array $override = []): array
    {
        return array_merge([
            'nama_lengkap' => 'Rina Skenario', 'email' => 'rina.skenario@example.com',
            'no_telepon' => '081234567890', 'usia' => 28, 'pekerjaan' => 'Wirausaha', 'alamat' => 'Jl. Skenario 1, Cimahi',
        ], $override);
    }

    public function test_perjalanan_lengkap_dua_ruangan_sampai_faktur_dan_laporan(): void
    {
        // ===== ROLE PEMESAN =====
        // 1. Halaman publik terbuka.
        $this->get('/')->assertOk()->assertSee('WADUH');
        $this->get('/reservasi')->assertOk();

        // 2. Pilih 2 ruangan dari denah (satu form untuk keduanya) → keranjang berisi 2.
        [$t1, $f1] = $this->ruang('Jam');
        [$t2, $f2] = $this->ruang('Jam');
        $tgl = Carbon::today()->addDays(30)->toDateString();

        $this->post('/reservasi/keranjang', [
            'id_fasilitas' => $f1->id_fasilitas, 'id_tarif_sewa' => $t1->id_tarif_sewa,
            'antrian' => (string) $f2->id_fasilitas,
            'tanggal_mulai' => $tgl, 'jam_mulai' => '10:00', 'jam_selesai' => '12:00',
            'jumlah_pengguna' => 3, 'keperluan' => 'Workshop UMKM',
        ])->assertRedirect(route('reservasi.checkout.form'));
        $this->assertCount(2, session('reservasi_cart'));
        $this->get('/reservasi/checkout')->assertOk()->assertSee($f1->nama_fasilitas)->assertSee($f2->nama_fasilitas);

        // 3. Checkout sekali → 2 baris Reservasi, SATU kode reservasi simpel.
        $this->post('/reservasi/checkout', $this->dataDiri())->assertRedirect(route('reservasi.sukses'));
        $rows = Reservasi::where('id_pemesan', \App\Models\Pemesan::where('email', 'rina.skenario@example.com')->value('id_pemesan'))->get();
        $this->assertCount(2, $rows);
        $kode = $rows->first()->kode_transaksi;
        $this->assertMatchesRegularExpression('/^RSV-[A-Z2-9]{4}$/', $kode);
        $this->assertSame([$kode], $rows->pluck('kode_transaksi')->unique()->values()->all());
        $this->assertSame(['Menunggu'], $rows->pluck('status_reservasi')->map->value->unique()->values()->all());
        $this->assertEmpty(session('reservasi_cart', []), 'keranjang kosong setelah checkout');

        // 4. Cek status: tracker "Diverifikasi", tombol Batalkan tersedia.
        $this->post('/cek-status', ['kode' => $kode])->assertOk()
            ->assertSee('Diverifikasi')->assertSee('Batalkan');

        // ===== ROLE ADMIN =====
        // 5. Login (kredensial salah dulu, lalu benar).
        $this->post('/admin/login', ['email' => 'admin@waduh.test', 'password' => 'salah'])->assertRedirect();
        $this->assertFalse(auth()->guard('admin')->check());
        $this->post('/admin/login', ['email' => 'admin@waduh.test', 'password' => 'password'])->assertRedirect(route('admin.dashboard'));

        // 6. Dashboard & antrian menampilkan pemesanan ini; detail satu halaman untuk 2 ruangan.
        $this->get('/admin/dashboard')->assertOk();
        $this->get('/admin/reservasi?status=Menunggu')->assertOk()->assertSee($kode);
        $this->get('/admin/reservasi/'.$rows->first()->kode_reservasi)->assertOk()
            ->assertSee('Checklist')->assertSee($f1->nama_fasilitas)->assertSee($f2->nama_fasilitas);

        // 7. Setujui SEMUA ruangan sekali klik → observer mengisi jejak.
        $this->post(route('admin.reservasi.setujui', $rows->first()->kode_reservasi))->assertRedirect();
        $rows->each(function (Reservasi $r) {
            $r->refresh();
            $this->assertSame('Disetujui', $r->status_reservasi->value);
            $this->assertSame('confirmed', $r->lock_status->value);
            $this->assertNotNull($r->tanggal_diproses);
            $this->assertSame(1, $r->riwayatStatus()->where('status_baru', 'Disetujui')->count());
        });

        // 8. Faktur: SATU untuk kedua ruangan; idempotent; berformat PDF.
        $res = $this->post(route('admin.reservasi.faktur.cetak', $rows->first()->kode_reservasi));
        $res->assertOk();
        $this->assertStringStartsWith('%PDF', $res->getContent());
        $this->post(route('admin.reservasi.faktur.cetak', $rows->last()->kode_reservasi))->assertOk();
        $this->assertSame(1, Faktur::whereIn('id_reservasi', $rows->pluck('id_reservasi'))->count());
        $this->assertStringStartsWith('INV/', Faktur::whereIn('id_reservasi', $rows->pluck('id_reservasi'))->value('nomor_faktur'));

        // 9. Laporan: halaman + ekspor PDF mencatat riwayat cetak.
        $this->get('/admin/laporan')->assertOk();
        $sebelum = Laporan::count();
        $pdf = $this->get(route('admin.laporan.pdf'));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        $this->assertSame($sebelum + 1, Laporan::count());

        // ===== KEMBALI KE PEMESAN =====
        // 10. Status kini Disetujui; pembatalan tidak lagi ditawarkan maupun diizinkan.
        $this->post('/cek-status', ['kode' => $kode])->assertOk()->assertSee('Disetujui')->assertDontSee('>Batalkan<');
        $this->post('/reservasi/'.$rows->first()->kode_reservasi.'/batalkan')->assertRedirect();
        $this->assertSame('Disetujui', $rows->first()->fresh()->status_reservasi->value, 'yang sudah diputus tidak bisa dibatalkan');
    }

    public function test_alur_penolakan_dengan_alasan_tercatat(): void
    {
        [$t, $f] = $this->ruang('Hari');
        $tgl = Carbon::today()->addDays(31)->toDateString();
        $this->post('/reservasi/keranjang', [
            'id_fasilitas' => $f->id_fasilitas, 'id_tarif_sewa' => $t->id_tarif_sewa,
            'tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl,
            'jumlah_pengguna' => 2, 'keperluan' => 'Uji tolak',
        ]);
        $this->post('/reservasi/checkout', $this->dataDiri(['email' => 'tolak@example.com']));
        $r = Reservasi::whereHas('pemesan', fn ($q) => $q->where('email', 'tolak@example.com'))->firstOrFail();

        $this->actingAs($this->admin(), 'admin');

        // Tanpa alasan → validasi menolak; dengan alasan → status Ditolak + jejak alasan.
        $this->post(route('admin.reservasi.tolak', $r->kode_reservasi), [])->assertSessionHasErrors('alasan');
        $this->post(route('admin.reservasi.tolak', $r->kode_reservasi), ['alasan' => 'Jadwal bentrok kegiatan internal'])->assertRedirect();

        $r->refresh();
        $this->assertSame('Ditolak', $r->status_reservasi->value);
        $this->assertSame('released', $r->lock_status->value);
        $this->assertSame('Jadwal bentrok kegiatan internal', $r->riwayatStatus()->latest('id_riwayat')->value('keterangan'));

        // Pemesan melihat hasil penolakan.
        $this->post('/cek-status', ['kode' => $r->kode_transaksi])->assertOk()->assertSee('Ditolak');
    }

    public function test_alur_pembatalan_mandiri_pemesan(): void
    {
        [$t, $f] = $this->ruang('Jam');
        $tgl = Carbon::today()->addDays(32)->toDateString();
        $this->post('/reservasi/keranjang', [
            'id_fasilitas' => $f->id_fasilitas, 'id_tarif_sewa' => $t->id_tarif_sewa,
            'tanggal_mulai' => $tgl, 'jam_mulai' => '13:00', 'jam_selesai' => '15:00',
            'jumlah_pengguna' => 2, 'keperluan' => 'Uji batal',
        ]);
        $this->post('/reservasi/checkout', $this->dataDiri(['email' => 'batal@example.com']));
        $r = Reservasi::whereHas('pemesan', fn ($q) => $q->where('email', 'batal@example.com'))->firstOrFail();

        $this->post('/reservasi/'.$r->kode_reservasi.'/batalkan')->assertRedirect();

        $r->refresh();
        $this->assertSame('Dibatalkan', $r->status_reservasi->value);
        $riwayat = $r->riwayatStatus()->latest('id_riwayat')->first();
        $this->assertNull($riwayat->id_admin, 'pembatalan mandiri tanpa admin');
        $this->assertNull($r->tanggal_diproses, 'bukan diproses admin');
    }

    public function test_alur_sewa_bulan_dokumen_wajib_valid_sebelum_disetujui(): void
    {
        Storage::fake('public');
        [$t, $f] = $this->ruang('Bulan');
        $mulai = Carbon::today()->addDays(33);

        // Checkout Bulan tanpa dokumen ditolak; dengan dokumen diterima.
        $isiKeranjang = fn () => $this->post('/reservasi/keranjang', [
            'id_fasilitas' => $f->id_fasilitas, 'id_tarif_sewa' => $t->id_tarif_sewa,
            'tanggal_mulai' => $mulai->toDateString(), 'tanggal_selesai' => $mulai->copy()->addMonths(3)->toDateString(),
            'jumlah_pengguna' => 4, 'keperluan' => 'Kantor startup',
        ]);
        $isiKeranjang();
        $this->post('/reservasi/checkout', $this->dataDiri(['email' => 'bulan@example.com']))
            ->assertSessionHasErrors('dokumen.0');

        $this->post('/reservasi/checkout', $this->dataDiri(['email' => 'bulan@example.com']) + [
            'dokumen' => [0 => [UploadedFile::fake()->create('company-profile.pdf', 120, 'application/pdf')]],
        ])->assertRedirect(route('reservasi.sukses'));

        $r = Reservasi::whereHas('pemesan', fn ($q) => $q->where('email', 'bulan@example.com'))->firstOrFail();
        $dok = $r->dokumenPersyaratan()->firstOrFail();
        $this->assertSame('Menunggu', $dok->status_verifikasi->value);
        Storage::disk('public')->assertExists($dok->lokasi_file);

        // Admin: sebelum dokumen Valid, setujui tertahan checklist.
        $this->actingAs($this->admin(), 'admin');
        $this->post(route('admin.reservasi.setujui', $r->kode_reservasi));
        $this->assertSame('Menunggu', $r->fresh()->status_reservasi->value, 'tertahan checklist dokumen');

        // Validasi dokumen (tombol ceklis) → lalu setujui berhasil.
        $this->post(route('admin.reservasi.dokumen.verifikasi', $dok->id_dokumen), ['status_verifikasi' => 'Valid']);
        $this->post(route('admin.reservasi.setujui', $r->kode_reservasi));
        $this->assertSame('Disetujui', $r->fresh()->status_reservasi->value);
    }

    public function test_proteksi_role_admin(): void
    {
        // Tanpa login: semua halaman admin dialihkan ke login.
        foreach (['/admin/dashboard', '/admin/monitoring', '/admin/reservasi', '/admin/laporan'] as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
        // Logout mengakhiri sesi.
        $this->actingAs($this->admin(), 'admin');
        $this->post('/admin/logout')->assertRedirect(route('admin.login'));
        $this->assertFalse(auth()->guard('admin')->check());
    }
}
