<?php

namespace Database\Seeders;

use App\Enums\SatuanSewa;
use App\Models\JenisSewa;
use Illuminate\Database\Seeder;

class JenisSewaSeeder extends Seeder
{
    public function run(): void
    {
        // durasi_minimum: Jam & Hari minimal 1, Bulan minimal 3 (DELTA #4).
        $data = [
            ['satuan' => SatuanSewa::Jam,   'durasi_minimum' => 1],
            ['satuan' => SatuanSewa::Hari,  'durasi_minimum' => 1],
            ['satuan' => SatuanSewa::Bulan, 'durasi_minimum' => 3],
        ];

        foreach ($data as $row) {
            JenisSewa::firstOrCreate(
                ['satuan' => $row['satuan']],
                ['durasi_minimum' => $row['durasi_minimum']],
            );
        }
    }
}
