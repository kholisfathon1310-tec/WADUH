<?php

namespace App\Support;

/**
 * Metadata tampilan per kategori_fasilitas (gambar ilustrasi, ikon, warna,
 * dan daftar fasilitas yang didapat penyewa). Satu sumber untuk semua view.
 */
class KategoriMeta
{
    private const META = [
        'Working Space' => [
            'ikon'   => 'bi-briefcase',
            'warna'  => '#176b87',
            'desk'   => 'Ruang kerja privat untuk instansi & perusahaan.',
            'lokasi' => 'Lantai 1 & 2',
            'gambar' => 'images/lt1(home).png',
            'dapat'  => ['Ruang privat ber-AC', 'WiFi internet cepat', 'Meja & kursi kerja', 'Listrik & stopkontak', 'Akses lift & keamanan gedung', 'Area parkir'],
        ],
        'Co-Working Space' => [
            'ikon'   => 'bi-people',
            'warna'  => '#176b87',
            'desk'   => 'Kubikal & ruang bersama yang fleksibel untuk startup.',
            'lokasi' => 'Lantai 3A & 3B',
            'gambar' => 'images/lt3a(home).png',
            'dapat'  => ['Kubikal / meja kerja', 'WiFi internet cepat', 'Ruang ber-AC', 'Stopkontak per meja', 'Area komunal & diskusi', 'Akses pantry bersama'],
        ],
        'Convention Hall' => [
            'ikon'   => 'bi-bank',
            'warna'  => '#176b87',
            'desk'   => 'Aula besar untuk event, seminar, dan konvensi.',
            'lokasi' => 'Lantai 5',
            'gambar' => 'images/lt5 (5).png',
            'dapat'  => ['Aula luas ber-AC', 'Panggung & area utama', 'Sound system dasar', 'Kursi sesuai kapasitas', 'Area parkir luas', 'Toilet & akses publik'],
        ],
    ];

    private const DEFAULT = [
        'ikon'   => 'bi-door-open',
        'warna'  => '#176b87',
        'desk'   => 'Fasilitas gedung BITC.',
        'lokasi' => '',
        'gambar' => 'images/lt1(home).png',
        'dapat'  => ['Ruang ber-AC', 'WiFi internet', 'Akses gedung BITC'],
    ];

    /** @return array{ikon:string,warna:string,desk:string,lokasi:string,gambar:string,dapat:array} */
    public static function get(?string $kategori): array
    {
        return self::META[$kategori] ?? self::DEFAULT;
    }
}
