<?php

namespace Tests\Unit;

use App\Models\Fasilitas;
use App\Services\FasilitasBawaanService;
use Tests\TestCase;

/** Unit: fasilitas bawaan per kategori (db-spec-fasilitas-final.md bagian 2). */
class FasilitasBawaanServiceTest extends TestCase
{
    private function daftar(string $kategori, ?string $jenis = null): array
    {
        return (new FasilitasBawaanService())->untuk(
            new Fasilitas(['kategori_fasilitas' => $kategori]),
            $jenis,
        );
    }

    public function test_working_space_jam_dan_hari_dapat_meja_kursi(): void
    {
        foreach (['Jam', 'Hari'] as $jenis) {
            $daftar = $this->daftar('Working Space', $jenis);
            $this->assertContains('Meja', $daftar);
            $this->assertContains('Kursi', $daftar);
        }
    }

    public function test_working_space_bulan_diserahkan_kosong_tanpa_meja_kursi(): void
    {
        $daftar = $this->daftar('Working Space', 'Bulan');
        $this->assertNotContains('Meja', $daftar);
        $this->assertNotContains('Kursi', $daftar);
        $this->assertContains('AC', $daftar);
    }

    public function test_kecepatan_internet_per_kategori(): void
    {
        $this->assertContains('Internet 20 Mbps', $this->daftar('Working Space', 'Jam'));
        $this->assertContains('Internet 10 Mbps', $this->daftar('Co-Working Space', 'Jam'));
        // Convention Hall: baris internet tidak ditampilkan sama sekali.
        $this->assertEmpty(array_filter($this->daftar('Convention Hall', 'Hari'), fn ($d) => str_starts_with($d, 'Internet')));
    }

    public function test_fasilitas_umum_selalu_ada(): void
    {
        foreach (['Working Space', 'Co-Working Space', 'Convention Hall'] as $kategori) {
            $daftar = $this->daftar($kategori);
            $this->assertContains('Pemakaian bersama Pantry', $daftar);
            $this->assertContains('Mushola', $daftar);
            $this->assertContains('Area Parkir', $daftar);
        }
    }

    public function test_convention_hall_dapat_perlengkapan_event(): void
    {
        $daftar = $this->daftar('Convention Hall', 'Hari');
        foreach (['Meja', 'Kursi', 'TV', 'Soundsystem', 'AC', 'Proyektor'] as $item) {
            $this->assertContains($item, $daftar);
        }
    }
}
