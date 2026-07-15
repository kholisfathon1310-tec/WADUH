<?php

/*
| Fasilitas bawaan (amenities) per kategori — SATU sumber kebenaran.
| Jangan duplikasi daftar ini di kolom `deskripsi` tabel Fasilitas;
| selalu tampilkan lewat App\Services\FasilitasBawaanService.
*/

return [
    'umum' => ['Pemakaian bersama Pantry', 'Toilet per lantai', 'Mushola', 'Area Parkir'],

    'internet' => [
        'Co-Working Space' => '10 Mbps',
        'Working Space'    => '20 Mbps',
        'Convention Hall'  => null, // tidak disebutkan, jangan tampilkan baris internet
    ],

    'per_kategori' => [
        'Convention Hall'  => ['Meja', 'Kursi', 'TV', 'Soundsystem', 'AC', 'Proyektor'],
        'Co-Working Space' => ['Meja', 'Kursi', 'Listrik', 'Wifi', 'AC'],
        'Working Space'    => ['AC'], // Meja & Kursi kondisional (hanya sewa Jam/Hari)
    ],
];
