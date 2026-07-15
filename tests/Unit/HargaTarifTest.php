<?php

namespace Tests\Unit;

use App\Models\Fasilitas;
use App\Support\HargaTarif;
use Tests\TestCase;

/** Unit: aturan harga db-spec-fasilitas-final.md. */
class HargaTarifTest extends TestCase
{
    public function test_tarif_flat_sesuai_spesifikasi(): void
    {
        $this->assertSame(50_000, HargaTarif::FLAT['Working Space']['Jam']);
        $this->assertSame(400_000, HargaTarif::FLAT['Working Space']['Hari']);
        $this->assertSame(40_000, HargaTarif::FLAT['Co-Working Space']['Jam']);
        $this->assertSame(250_000, HargaTarif::FLAT['Co-Working Space']['Hari']);
        $this->assertSame(1_800_000, HargaTarif::FLAT['Convention Hall']['Hari']);
        $this->assertArrayNotHasKey('Jam', HargaTarif::FLAT['Convention Hall']);   // CH tanpa Jam
        $this->assertArrayNotHasKey('Bulan', HargaTarif::FLAT['Convention Hall']); // CH tanpa Bulan
    }

    public function test_luas_efektif_pakai_default_bila_kosong(): void
    {
        $kubikal = new Fasilitas(['kategori_fasilitas' => 'Co-Working Space', 'luas' => null]);
        [$luas, $default] = HargaTarif::luasEfektif($kubikal);
        $this->assertSame(6.0, $luas);
        $this->assertTrue($default);

        $ruang = new Fasilitas(['kategori_fasilitas' => 'Working Space', 'luas' => null]);
        [$luas2] = HargaTarif::luasEfektif($ruang);
        $this->assertSame(25.0, $luas2);
    }

    public function test_harga_bulan_150rb_kali_luas(): void
    {
        $f = new Fasilitas(['kategori_fasilitas' => 'Working Space', 'luas' => 30]);
        [$harga, $default] = HargaTarif::hargaBulan($f);
        $this->assertSame(150_000.0 * 30, $harga);
        $this->assertFalse($default);
    }
}
