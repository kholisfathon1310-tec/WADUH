<?php

namespace App\Support;

/**
 * Utilitas denah SVG: centroid poligon (area-terbobot) untuk posisi label,
 * sesuai rumus di db-spec-denah-layout.md — label harus selalu di dalam poligon,
 * termasuk poligon dengan sudut miring.
 */
class Denah
{
    /**
     * @param  string  $points  "x1,y1 x2,y2 ..."
     * @return array{0: float, 1: float} [cx, cy]
     */
    public static function centroid(string $points): array
    {
        $p = array_map(
            fn ($pair) => array_map('floatval', explode(',', $pair)),
            preg_split('/\s+/', trim($points)),
        );

        $n = count($p);
        $a = 0.0;
        $cx = 0.0;
        $cy = 0.0;

        for ($i = 0; $i < $n; $i++) {
            $j = ($i + 1) % $n;
            $f = $p[$i][0] * $p[$j][1] - $p[$j][0] * $p[$i][1];
            $a += $f;
            $cx += ($p[$i][0] + $p[$j][0]) * $f;
            $cy += ($p[$i][1] + $p[$j][1]) * $f;
        }

        $a *= 0.5;
        if (abs($a) < 0.001) {
            // Degenerate: rata-rata titik.
            $sx = array_sum(array_column($p, 0));
            $sy = array_sum(array_column($p, 1));

            return [$sx / $n, $sy / $n];
        }

        return [$cx / (6 * $a), $cy / (6 * $a)];
    }

    /** Label pendek tanpa prefix lantai: "3A-M1" → "M1", "L5-HALL" → "HALL". */
    public static function labelPendek(string $kode): string
    {
        $pos = strpos($kode, '-');

        return $pos === false ? $kode : substr($kode, $pos + 1);
    }
}
