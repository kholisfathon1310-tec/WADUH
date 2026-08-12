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
            'gambar' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?auto=format&fit=crop&w=1000&q=80',
            'dapat'  => ['Ruang privat ber-AC', 'WiFi internet cepat', 'Meja & kursi kerja', 'Listrik & stopkontak', 'Akses lift & keamanan gedung', 'Area parkir'],
        ],
        'Co-Working Space' => [
            'ikon'   => 'bi-people',
            'warna'  => '#176b87',
            'desk'   => 'Kubikal & ruang bersama yang fleksibel untuk startup.',
            'lokasi' => 'Lantai 3A & 3B',
            'gambar' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1000&q=80',
            'dapat'  => ['Kubikal / meja kerja', 'WiFi internet cepat', 'Ruang ber-AC', 'Stopkontak per meja', 'Area komunal & diskusi', 'Akses pantry bersama'],
        ],
        'Convention Hall' => [
            'ikon'   => 'bi-bank',
            'warna'  => '#176b87',
            'desk'   => 'Aula besar untuk event, seminar, dan konvensi.',
            'lokasi' => 'Lantai 5',
            'gambar' => 'https://images.unsplash.com/photo-1505373877841-8d25f7d46678?auto=format&fit=crop&w=1000&q=80',
            'dapat'  => ['Aula luas ber-AC', 'Panggung & area utama', 'Sound system dasar', 'Kursi sesuai kapasitas', 'Area parkir luas', 'Toilet & akses publik'],
        ],
    ];

    private const DEFAULT = [
        'ikon'   => 'bi-door-open',
        'warna'  => '#176b87',
        'desk'   => 'Fasilitas gedung BITC.',
        'lokasi' => '',
        'gambar' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=1000&q=80',
        'dapat'  => ['Ruang ber-AC', 'WiFi internet', 'Akses gedung BITC'],
    ];

    /** @return array{ikon:string,warna:string,desk:string,lokasi:string,gambar:string,dapat:array} */
    public static function get(?string $kategori): array
    {
        return self::META[$kategori] ?? self::DEFAULT;
    }
}
