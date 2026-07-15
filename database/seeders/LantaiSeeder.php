<?php

namespace Database\Seeders;

use App\Models\Lantai;
use Illuminate\Database\Seeder;

class LantaiSeeder extends Seeder
{
    public function run(): void
    {
        // Lantai sesuai denah gedung BITC (tidak ada lantai 3 & 4 tunggal; 3 terbagi 3A/3B).
        foreach (['1', '2', '3A', '3B', '5'] as $nomor) {
            Lantai::firstOrCreate(
                ['nomor_lantai' => $nomor],
                ['gambar_denah' => null],
            );
        }
    }
}
