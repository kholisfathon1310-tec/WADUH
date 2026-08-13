<?php

namespace Tests\Feature;

use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\Pemesan;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/** PDF Bukti Reservasi (dokumen milik pemesan, terpisah dari Admin\FakturController) tidak
 *  punya cakupan uji sebelumnya — tes ini memastikan halaman kop surat & logo BITC yang
 *  ter-crop render tanpa error dan menghasilkan PDF yang valid. */
class BuktiReservasiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bukti_reservasi_pdf_render_ok(): void
    {
        $jenis = JenisSewa::where('satuan', 'Jam')->firstOrFail();
        $f = Fasilitas::factory()->create(['status_aktif' => 'Aktif']);
        $t = TarifSewa::factory()->create([
            'id_fasilitas' => $f->id_fasilitas, 'id_jenis_sewa' => $jenis->id_jenis_sewa,
            'status_aktif' => 'Aktif', 'harga' => 50000,
        ]);
        $p = Pemesan::factory()->create();

        $r = Reservasi::create([
            'id_pemesan' => $p->id_pemesan, 'id_tarif_sewa' => $t->id_tarif_sewa, 'id_admin' => null,
            'kode_reservasi' => 'RSV-BUKTITEST', 'kode_transaksi' => 'TRX-BUKTITEST',
            'tanggal_mulai' => Carbon::today()->addDays(10)->toDateString(),
            'tanggal_selesai' => Carbon::today()->addDays(10)->toDateString(),
            'jam_mulai' => '09:00', 'jam_selesai' => '11:00', 'durasi' => 2,
            'jumlah_pengguna' => 2, 'keperluan' => 'Uji render bukti reservasi', 'harga_satuan' => 50000,
            'total_biaya' => 100000, 'status_reservasi' => 'Menunggu', 'lock_status' => 'pending_approval',
        ]);

        $res = $this->get(route('cek-status.bukti-reservasi', $r->kode_reservasi));
        $res->assertOk();
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }
}
