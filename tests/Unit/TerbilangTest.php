<?php

namespace Tests\Unit;

use App\Helpers\NumberToWords;
use PHPUnit\Framework\TestCase;

/** Contoh wajib dari revisi-faktur-bahasa-indonesia.md bagian 3. */
class TerbilangTest extends TestCase
{
    public function test_contoh_wajib_dari_spesifikasi(): void
    {
        $this->assertSame('Dua Ratus Ribu Rupiah', NumberToWords::terbilang(200000));
        $this->assertSame('Satu Juta Delapan Ratus Ribu Rupiah', NumberToWords::terbilang(1800000));
        $this->assertSame('Satu Juta Seratus Lima Puluh Ribu Rupiah', NumberToWords::terbilang(1150000));
        $this->assertSame('Empat Puluh Ribu Rupiah', NumberToWords::terbilang(40000));
        $this->assertSame('Dua Ratus Lima Puluh Ribu Rupiah', NumberToWords::terbilang(250000));
    }

    public function test_kaidah_khusus_bahasa_indonesia(): void
    {
        $this->assertSame('Nol Rupiah', NumberToWords::terbilang(0));
        $this->assertSame('Sebelas Rupiah', NumberToWords::terbilang(11));
        $this->assertSame('Tujuh Belas Rupiah', NumberToWords::terbilang(17));
        $this->assertSame('Seratus Rupiah', NumberToWords::terbilang(100));           // bukan "Satu Ratus"
        $this->assertSame('Seribu Lima Ratus Rupiah', NumberToWords::terbilang(1500)); // bukan "Satu Ribu"
        $this->assertSame('Tiga Juta Tujuh Ratus Lima Puluh Ribu Rupiah', NumberToWords::terbilang(3750000));
    }
}
