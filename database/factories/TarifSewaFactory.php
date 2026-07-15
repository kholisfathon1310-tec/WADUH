<?php

namespace Database\Factories;

use App\Enums\StatusAktif;
use App\Models\Fasilitas;
use App\Models\JenisSewa;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TarifSewa>
 */
class TarifSewaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id_fasilitas'  => Fasilitas::factory(),
            'id_jenis_sewa' => JenisSewa::query()->inRandomOrder()->value('id_jenis_sewa'),
            'harga'         => fake()->numberBetween(50_000, 3_000_000),
            'status_aktif'  => StatusAktif::Aktif->value,
        ];
    }
}
