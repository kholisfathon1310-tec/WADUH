<?php

/*
| Master data luas & tarif Bulan per ruangan — sumber: docs/hargasewa.md.
| JANGAN diubah/dibulatkan tanpa instruksi eksplisit; nilai di sini harus
| identik dengan tabel di docs/hargasewa.md (kode, luas, harga sewa, jumlah
| unit adalah master data yang tidak boleh diubah sepihak).
| Convention Hall (L5-HALL) tidak ada di sini karena hargasewa.md tidak
| memberi luas/Harga-Bulan untuk unit itu (Convention Hall tidak punya
| tarif Bulan sama sekali — lihat HargaTarif::PUNYA_BULAN).
| Tarif Jam/Hari TIDAK di sini karena sudah seragam per kategori dan sudah
| cocok dengan hargasewa.md — tetap di App\Support\HargaTarif::FLAT.
*/

return [
    // ===== LANTAI 1 =====
    'L1-R1' => ['luas' => 44.94, 'bulan' => 6_741_000],
    'L1-R2' => ['luas' => 44.94, 'bulan' => 6_741_000],
    'L1-R3' => ['luas' => 34.97, 'bulan' => 5_245_500],

    // ===== LANTAI 2 =====
    'L2-R1' => ['luas' => 19.56, 'bulan' => 2_934_000],
    'L2-R2' => ['luas' => 20.10, 'bulan' => 3_015_000],
    'L2-R3' => ['luas' => 23.00, 'bulan' => 3_450_000],
    'L2-R4' => ['luas' => 30.00, 'bulan' => 4_500_000],
    'L2-R5' => ['luas' => 24.00, 'bulan' => 3_600_000],
    'L2-R6' => ['luas' => 27.00, 'bulan' => 4_050_000],
    'L2-R7' => ['luas' => 33.54, 'bulan' => 5_031_000],
    'L2-R8' => ['luas' => 20.97, 'bulan' => 3_146_175],

    // ===== LANTAI 3A — Working Space =====
    '3A-R1' => ['luas' => 6.95, 'bulan' => 1_042_500],
    '3A-R2' => ['luas' => 8.75, 'bulan' => 1_312_500],
    '3A-R3' => ['luas' => 17.80, 'bulan' => 2_670_000],
    '3A-R4' => ['luas' => 20.97, 'bulan' => 3_146_175],
    '3A-R5' => ['luas' => 14.70, 'bulan' => 2_205_000],
    '3A-R6' => ['luas' => 4.60, 'bulan' => 690_000],

    // ===== LANTAI 3A — Coworking Space (K1-K27, seragam 4,00 m²) =====
    '3A-K1' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K2' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K3' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K4' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K5' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K6' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K7' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K8' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K9' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K10' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K11' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K12' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K13' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K14' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K15' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K16' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K17' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K18' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K19' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K20' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K21' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K22' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K23' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K24' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K25' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K26' => ['luas' => 4.00, 'bulan' => 600_000],
    '3A-K27' => ['luas' => 4.00, 'bulan' => 600_000],

    // ===== LANTAI 3B — Working Space =====
    '3B-R1' => ['luas' => 20.97, 'bulan' => 3_146_175],
    '3B-R2' => ['luas' => 49.40, 'bulan' => 7_410_000],
    '3B-R3' => ['luas' => 4.60, 'bulan' => 690_000],

    // ===== LANTAI 3B — Coworking Space =====
    '3B-K1' => ['luas' => 8.00, 'bulan' => 1_200_000],
    '3B-K2' => ['luas' => 8.00, 'bulan' => 1_200_000],
    '3B-K3' => ['luas' => 7.80, 'bulan' => 1_170_000],
    '3B-K4' => ['luas' => 8.00, 'bulan' => 1_200_000],
    '3B-K5' => ['luas' => 7.90, 'bulan' => 1_185_000],
    '3B-K6' => ['luas' => 8.00, 'bulan' => 1_200_000],
    '3B-K7' => ['luas' => 7.80, 'bulan' => 1_170_000],
    '3B-K8' => ['luas' => 9.50, 'bulan' => 1_425_000],
    '3B-K9' => ['luas' => 8.04, 'bulan' => 1_206_000],
    '3B-K10' => ['luas' => 7.60, 'bulan' => 1_140_000],
    '3B-K11' => ['luas' => 7.60, 'bulan' => 1_140_000],
    '3B-K12' => ['luas' => 7.90, 'bulan' => 1_185_000],
    '3B-K13' => ['luas' => 7.60, 'bulan' => 1_140_000],
    '3B-K14' => ['luas' => 7.80, 'bulan' => 1_170_000],
    '3B-K15' => ['luas' => 7.50, 'bulan' => 1_125_000],
    '3B-K16' => ['luas' => 7.92, 'bulan' => 1_188_000],
];
