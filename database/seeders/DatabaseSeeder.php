<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LantaiSeeder::class,
            JenisSewaSeeder::class,
            AdminSeeder::class,
            FasilitasSeeder::class, // data real denah BITC
            TarifSewaSeeder::class, // tarif untuk fasilitas aktif
        ]);
    }
}
