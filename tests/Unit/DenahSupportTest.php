<?php

namespace Tests\Unit;

use App\Support\Denah;
use PHPUnit\Framework\TestCase;

/** Unit: helper denah SVG (centroid poligon & label pendek). */
class DenahSupportTest extends TestCase
{
    public function test_centroid_persegi_di_tengah(): void
    {
        [$x, $y] = Denah::centroid('0,0 10,0 10,10 0,10');
        $this->assertEqualsWithDelta(5.0, $x, 0.001);
        $this->assertEqualsWithDelta(5.0, $y, 0.001);
    }

    public function test_centroid_poligon_miring_tetap_di_dalam_bentuk(): void
    {
        // L2-R7 dari config denah — poligon dinding miring.
        [$x, $y] = Denah::centroid('872,182 950,255 950,300 900,368 872,368');
        $this->assertGreaterThan(872, $x);
        $this->assertLessThan(950, $x);
        $this->assertGreaterThan(182, $y);
        $this->assertLessThan(368, $y);
    }

    public function test_centroid_degenerate_fallback_rata_rata(): void
    {
        [$x, $y] = Denah::centroid('0,0 10,0'); // garis, luas 0
        $this->assertEqualsWithDelta(5.0, $x, 0.001);
        $this->assertEqualsWithDelta(0.0, $y, 0.001);
    }

    public function test_label_pendek(): void
    {
        $this->assertSame('M1', Denah::labelPendek('3A-M1'));
        $this->assertSame('HALL', Denah::labelPendek('L5-HALL'));
        $this->assertSame('R8', Denah::labelPendek('L2-R8'));
        $this->assertSame('TANPADASH', Denah::labelPendek('TANPADASH'));
    }
}
