<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pemesan>
 */
class PemesanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_lengkap' => fake()->name(),
            'alamat'       => fake()->address(),
            'usia'         => fake()->numberBetween(18, 60),
            'pekerjaan'    => fake()->jobTitle(),
            'no_telepon'   => fake()->numerify('08##########'),
            'email'        => fake()->unique()->safeEmail(),
        ];
    }
}
