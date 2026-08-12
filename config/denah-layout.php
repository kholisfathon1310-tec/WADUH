<?php

/*
|--------------------------------------------------------------------------
| Denah Layout — Data Ruangan per Lantai
|--------------------------------------------------------------------------
| Struktur denah gedung BITC per lantai untuk komponen <x-denah-layout />.
| Bentuk data:
|
|  '<nomor_lantai>' => [
|      'title'    => 'LANTAI 1',              // judul header
|      'subtitle' => null|string,              // opsional
|      'info'     => 'Total Ruangan: 3',
|
|      // Salah satu / kombinasi mode di bawah dipakai (dieksekusi berurutan):
|      'grid_main' => [                        // mode grid presisi (L2, L3A, L3B)
|          'cols'  => 4,
|          'cells' => [ {kode, label, luas, col, row, row_span?, shape?} ],
|      ],
|      'rows_after' => [ {kode, label, luas, flex} ],  // baris row-flex SETELAH grid
|      'rows'       => [ [ {kode, label, tipe, flex, shape?} ] ], // row-flex biasa
|      'hall'       => [ meja... ],                    // mode Convention Hall
|
| Field per item:
|  - kode   : kode unik → dipakai untuk look-up status (opsional untuk servis)
|  - label  : teks yang ditampilkan di kartu
|  - luas   : luas (m²) — hanya diisi untuk ruangan yang bisa dipesan
|  - tipe   : ruangan | fasilitas | servis | tangga | koridor | taman | blank
|  - flex   : proporsi lebar di dalam row (default 1)
|  - shape  : angled  → sudut kanan atas dipotong
|  - col/row/row_span : posisi di grid_main (khusus mode grid)
|
| CATATAN: kode 'ruangan' dipetakan ke kode_fasilitas WADUH via 'kode' field.
| Untuk ruangan yang belum ada di database (mis. 3A-R5/R6), tetap ditampilkan
| sebagai visual layout. Status lookup akan mengembalikan 'kosong' default.
|--------------------------------------------------------------------------
*/

return [

    // ─────────────────────────────────────────────────────────────
    // LANTAI 1 — Working Space (3 ruangan besar + balkon)
    // ─────────────────────────────────────────────────────────────
    '1' => [
        'title' => 'LANTAI 1',
        'info'  => 'Total Ruangan: 3 Working Space',
        'rows' => [
            // Baris 1: 3 ruangan + balkon angled di kanan
            [
                ['kode' => 'L1-R1', 'label' => 'R1', 'luas' => 44.94, 'tipe' => 'ruangan'],
                ['kode' => 'L1-R2', 'label' => 'R2', 'luas' => 44.94, 'tipe' => 'ruangan'],
                ['kode' => 'L1-R3', 'label' => 'R3', 'luas' => 34.97, 'tipe' => 'ruangan'],
                ['label' => 'BALKON', 'tipe' => 'taman', 'shape' => 'angled', 'flex' => 1],
            ],
            // Baris 2: koridor
            [
                ['label' => 'KORIDOR', 'tipe' => 'koridor'],
            ],
            // Baris 3: area servis (gudang, tangga, panel, dsb.)
            [
                ['label' => 'GUDANG',        'tipe' => 'servis',    'flex' => 2.2],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1],
                ['label' => 'R. PANEL',      'tipe' => 'servis',    'flex' => 0.8],
                ['label' => 'JANITOR',       'tipe' => 'servis',    'flex' => 0.8],
                ['label' => 'TOILET PRIA',   'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'TOILET WANITA', 'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'PANTRY',        'tipe' => 'fasilitas', 'flex' => 1.4],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1.2],
            ],
        ],
    ],

    // ─────────────────────────────────────────────────────────────
    // LANTAI 2 — Working Space (8 ruangan)
    // ─────────────────────────────────────────────────────────────
    '2' => [
        'title' => 'LANTAI 2',
        'info'  => 'Total Working Space: 8',
        'grid_main' => [
            'cols' => 4,
            'cells' => [
                ['kode' => 'L2-R2', 'label' => 'R2', 'luas' => 20.10, 'col' => 1, 'row' => 1],
                ['kode' => 'L2-R1', 'label' => 'R1', 'luas' => 19.56, 'col' => 1, 'row' => 2],
                ['kode' => 'L2-R4', 'label' => 'R4', 'luas' => 30.00, 'col' => 2, 'row' => 1],
                ['kode' => 'L2-R3', 'label' => 'R3', 'luas' => 23.00, 'col' => 2, 'row' => 2],
                ['kode' => 'L2-R5', 'label' => 'R5', 'luas' => 24.00, 'col' => 3, 'row' => 1],
                ['kode' => 'L2-R6', 'label' => 'R6', 'luas' => 27.00, 'col' => 3, 'row' => 2],
                ['kode' => 'L2-R7', 'label' => 'R7', 'luas' => 33.54, 'col' => 4, 'row' => 1, 'row_span' => 2, 'shape' => 'angled'],
            ],
        ],
        'rows' => [
            [['label' => 'KORIDOR', 'tipe' => 'koridor']],
            [
                ['kode' => 'L2-R8', 'label' => 'R8', 'luas' => 20.97, 'tipe' => 'ruangan', 'flex' => 1.6],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1],
                ['label' => 'R. PANEL',      'tipe' => 'servis',    'flex' => 0.8],
                ['label' => 'TOILET PRIA',   'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'TOILET WANITA', 'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'PANTRY',        'tipe' => 'fasilitas', 'flex' => 1.4],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1.2],
            ],
        ],
    ],

    // ─────────────────────────────────────────────────────────────
    // LANTAI 3A — Co-Working Space (27 kubikal K + 4 ruangan R)
    // ─────────────────────────────────────────────────────────────
    '3A' => [
        'title' => 'LANTAI 3A',
        'info'  => 'Co-Working: 27 kubikal · Working Space: 4 ruangan',
        'grid_main' => [
            'cols' => 11, // kolom 1-10 = grid kubikal, kolom 11 = R5/R4/R3 di sisi kanan
            'cells' => [
                // Baris 1: K19-K27 (kubikal) + R5 (ruangan angled di kanan)
                ['kode' => '3A-M19', 'label' => 'K19', 'luas' => 4.00, 'col' => 1, 'row' => 1],
                ['kode' => '3A-M20', 'label' => 'K20', 'luas' => 4.00, 'col' => 2, 'row' => 1],
                ['kode' => '3A-M21', 'label' => 'K21', 'luas' => 4.00, 'col' => 3, 'row' => 1],
                ['kode' => '3A-M22', 'label' => 'K22', 'luas' => 4.00, 'col' => 4, 'row' => 1],
                ['kode' => '3A-M23', 'label' => 'K23', 'luas' => 4.00, 'col' => 5, 'row' => 1],
                ['kode' => '3A-M24', 'label' => 'K24', 'luas' => 4.00, 'col' => 6, 'row' => 1],
                ['kode' => '3A-M25', 'label' => 'K25', 'luas' => 4.00, 'col' => 7, 'row' => 1],
                ['kode' => '3A-M26', 'label' => 'K26', 'luas' => 4.00, 'col' => 8, 'row' => 1],
                ['kode' => '3A-M27', 'label' => 'K27', 'luas' => 4.00, 'col' => 9, 'row' => 1],
                // Ruangan sisi kanan (kolom 11)
                ['kode' => '3A-R5', 'label' => 'R5', 'luas' => 14.70, 'col' => 11, 'row' => 1, 'shape' => 'angled'],

                // Baris 2: K17-K18 (kubikal kiri) + R4 di kanan
                ['kode' => '3A-M17', 'label' => 'K17', 'luas' => 4.00, 'col' => 1, 'row' => 2],
                ['kode' => '3A-M18', 'label' => 'K18', 'luas' => 4.00, 'col' => 2, 'row' => 2],
                ['kode' => '3A-R4', 'label' => 'R4', 'luas' => 20.97, 'col' => 11, 'row' => 2],

                // Baris 3: K7-K16 (10 kubikal) + R3 di kanan
                ['kode' => '3A-M7',  'label' => 'K7',  'luas' => 4.00, 'col' => 1, 'row' => 3],
                ['kode' => '3A-M8',  'label' => 'K8',  'luas' => 4.00, 'col' => 2, 'row' => 3],
                ['kode' => '3A-M9',  'label' => 'K9',  'luas' => 4.00, 'col' => 3, 'row' => 3],
                ['kode' => '3A-M10', 'label' => 'K10', 'luas' => 4.00, 'col' => 4, 'row' => 3],
                ['kode' => '3A-M11', 'label' => 'K11', 'luas' => 4.00, 'col' => 5, 'row' => 3],
                ['kode' => '3A-M12', 'label' => 'K12', 'luas' => 4.00, 'col' => 6, 'row' => 3],
                ['kode' => '3A-M13', 'label' => 'K13', 'luas' => 4.00, 'col' => 7, 'row' => 3],
                ['kode' => '3A-M14', 'label' => 'K14', 'luas' => 4.00, 'col' => 8, 'row' => 3],
                ['kode' => '3A-M15', 'label' => 'K15', 'luas' => 4.00, 'col' => 9, 'row' => 3],
                ['kode' => '3A-M16', 'label' => 'K16', 'luas' => 4.00, 'col' => 10, 'row' => 3],
                ['kode' => '3A-R3', 'label' => 'R3', 'luas' => 17.80, 'col' => 11, 'row' => 3],
            ],
        ],
        // Baris SETELAH grid (K1-K6 + R2 + R1)
        'rows_after' => [
            ['kode' => '3A-M1', 'label' => 'K1', 'luas' => 4.00, 'flex' => 1],
            ['kode' => '3A-M2', 'label' => 'K2', 'luas' => 4.00, 'flex' => 1],
            ['kode' => '3A-M3', 'label' => 'K3', 'luas' => 4.00, 'flex' => 1],
            ['kode' => '3A-M4', 'label' => 'K4', 'luas' => 4.00, 'flex' => 1],
            ['kode' => '3A-M5', 'label' => 'K5', 'luas' => 4.00, 'flex' => 1],
            ['kode' => '3A-M6', 'label' => 'K6', 'luas' => 4.00, 'flex' => 1],
            ['kode' => '3A-R2', 'label' => 'R2', 'luas' => 8.75, 'flex' => 2.2],
            ['kode' => '3A-R1', 'label' => 'R1', 'luas' => 6.95, 'flex' => 2.2],
        ],
        'rows' => [
            [['label' => 'KORIDOR', 'tipe' => 'koridor']],
            [
                ['kode' => '3A-R6', 'label' => 'R6', 'luas' => 4.60, 'tipe' => 'ruangan', 'flex' => 1.6],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1],
                ['label' => 'R. PANEL',      'tipe' => 'servis',    'flex' => 0.8],
                ['label' => 'TOILET PRIA',   'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'TOILET WANITA', 'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'PANTRY',        'tipe' => 'fasilitas', 'flex' => 1.4],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1.2],
            ],
        ],
    ],

    // ─────────────────────────────────────────────────────────────
    // LANTAI 3B — Co-Working Space (16 kubikal + 3 ruangan R)
    // ─────────────────────────────────────────────────────────────
    '3B' => [
        'title'    => 'LANTAI 3B',
        'subtitle' => 'GEDUNG BITC',
        'info'     => 'Total Working Space: 19',
        'grid_main' => [
            'cols' => 11, // kolom 1-9 = kubikal, 10 = R2, 11 = R1 (angled)
            'cells' => [
                // Baris 1 kubikal
                ['kode' => '3B-M2',  'label' => 'K2',  'luas' => 8.00, 'col' => 1, 'row' => 1],
                ['kode' => '3B-M4',  'label' => 'K4',  'luas' => 8.00, 'col' => 2, 'row' => 1],
                ['kode' => '3B-M6',  'label' => 'K6',  'luas' => 8.00, 'col' => 3, 'row' => 1],
                ['kode' => '3B-M8',  'label' => 'K8',  'luas' => 9.50, 'col' => 4, 'row' => 1],
                ['kode' => '3B-M10', 'label' => 'K10', 'luas' => 7.60, 'col' => 5, 'row' => 1],
                ['kode' => '3B-M12', 'label' => 'K12', 'luas' => 7.90, 'col' => 6, 'row' => 1],
                ['kode' => '3B-M14', 'label' => 'K14', 'luas' => 7.80, 'col' => 7, 'row' => 1],
                ['kode' => '3B-M15', 'label' => 'K15', 'luas' => 7.50, 'col' => 8, 'row' => 1],
                ['kode' => '3B-M16', 'label' => 'K16', 'luas' => 7.92, 'col' => 9, 'row' => 1],

                // Baris 2 kubikal
                ['kode' => '3B-M1',  'label' => 'K1',  'luas' => 8.00, 'col' => 1, 'row' => 2],
                ['kode' => '3B-M3',  'label' => 'K3',  'luas' => 7.80, 'col' => 2, 'row' => 2],
                ['kode' => '3B-M5',  'label' => 'K5',  'luas' => 7.90, 'col' => 3, 'row' => 2],
                ['kode' => '3B-M7',  'label' => 'K7',  'luas' => 7.80, 'col' => 4, 'row' => 2],
                ['kode' => '3B-M9',  'label' => 'K9',  'luas' => 8.04, 'col' => 5, 'row' => 2],
                ['kode' => '3B-M11', 'label' => 'K11', 'luas' => 7.60, 'col' => 6, 'row' => 2],
                ['kode' => '3B-M13', 'label' => 'K13', 'luas' => 7.60, 'col' => 7, 'row' => 2],

                // Ruangan besar sisi kanan (row_span 2)
                ['kode' => '3B-R2', 'label' => 'R2', 'luas' => 49.40, 'col' => 10, 'row' => 1, 'row_span' => 2, 'shape' => 'angled'],
                ['kode' => '3B-R1', 'label' => 'R1', 'luas' => 20.97, 'col' => 11, 'row' => 1, 'row_span' => 2, 'shape' => 'angled'],
            ],
        ],
        'rows' => [
            [['label' => 'KORIDOR', 'tipe' => 'koridor']],
            [
                ['kode' => '3B-R3', 'label' => 'R3', 'luas' => 4.60, 'tipe' => 'ruangan', 'flex' => 1.6],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1],
                ['label' => 'R. PANEL',      'tipe' => 'servis',    'flex' => 0.8],
                ['label' => 'TOILET PRIA',   'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'TOILET WANITA', 'tipe' => 'fasilitas', 'flex' => 1.2],
                ['label' => 'PANTRY',        'tipe' => 'fasilitas', 'flex' => 1.4],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'LIFT',          'tipe' => 'servis',    'flex' => 0.7],
                ['label' => 'TANGGA',        'tipe' => 'tangga',    'flex' => 1.2],
            ],
        ],
    ],

    // ─────────────────────────────────────────────────────────────
    // LANTAI 5 — Convention Hall (1 hall dengan tata letak 11 meja)
    // Visualisasi tata letak meja bundar dalam hall. Semua meja
    // terkait 1 fasilitas L5-HALL di database WADUH.
    // ─────────────────────────────────────────────────────────────
    '5' => [
        'title' => 'LANTAI 5',
        'info'  => 'Convention Hall · Kapasitas 75 orang',
        'hall'  => [
            'kode_hall' => 'L5-HALL',   // semua meja klik → detail L5-HALL
            'panggung'  => 'PANGGUNG',
            'meja'      => [
                // 11 meja bundar, dibagi jadi 4 baris (3-3-3-2 atau grup 3)
                ['nomor' => 1, 'kursi' => 5], ['nomor' => 2, 'kursi' => 5], ['nomor' => 3, 'kursi' => 5],
                ['nomor' => 4, 'kursi' => 5], ['nomor' => 5, 'kursi' => 5], ['nomor' => 6, 'kursi' => 5],
                ['nomor' => 7, 'kursi' => 5], ['nomor' => 8, 'kursi' => 5], ['nomor' => 9, 'kursi' => 5],
                ['nomor' => 10, 'kursi' => 5], ['nomor' => 11, 'kursi' => 5],
            ],
            'footer' => [
                ['label' => 'SOUND SYSTEM', 'tipe' => 'sound'],
                ['label' => 'MEJA PANJANG', 'tipe' => 'meja-panjang'],
                ['label' => 'SOUND SYSTEM', 'tipe' => 'sound'],
            ],
        ],
    ],
];