<?php

namespace App\Helpers;

/**
 * Terbilang Bahasa Indonesia untuk Faktur (revisi-faktur-bahasa-indonesia.md).
 * Tanpa package eksternal. Selalu Title Case dan diakhiri " Rupiah".
 */
class NumberToWords
{
    /**
     * Contoh: 1150000 → "Satu Juta Seratus Lima Puluh Ribu Rupiah".
     */
    public static function terbilang(int $angka): string
    {
        if ($angka === 0) {
            return 'Nol Rupiah';
        }

        return ucwords(trim(self::susun(abs($angka)))).' Rupiah';
    }

    private static function susun(int $n): string
    {
        $satuan = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];

        return match (true) {
            $n < 12                => $satuan[$n],
            $n < 20                => self::susun($n - 10).' belas',
            $n < 100               => self::susun(intdiv($n, 10)).' puluh'.($n % 10 ? ' '.self::susun($n % 10) : ''),
            $n < 200               => 'seratus'.($n - 100 ? ' '.self::susun($n - 100) : ''),          // BUKAN "satu ratus"
            $n < 1000              => self::susun(intdiv($n, 100)).' ratus'.($n % 100 ? ' '.self::susun($n % 100) : ''),
            $n < 2000              => 'seribu'.($n - 1000 ? ' '.self::susun($n - 1000) : ''),         // BUKAN "satu ribu"
            $n < 1_000_000         => self::susun(intdiv($n, 1000)).' ribu'.($n % 1000 ? ' '.self::susun($n % 1000) : ''),
            $n < 1_000_000_000     => self::susun(intdiv($n, 1_000_000)).' juta'.($n % 1_000_000 ? ' '.self::susun($n % 1_000_000) : ''),
            $n < 1_000_000_000_000 => self::susun(intdiv($n, 1_000_000_000)).' miliar'.($n % 1_000_000_000 ? ' '.self::susun($n % 1_000_000_000) : ''),
            default                => self::susun(intdiv($n, 1_000_000_000_000)).' triliun'.($n % 1_000_000_000_000 ? ' '.self::susun($n % 1_000_000_000_000) : ''),
        };
    }
}
