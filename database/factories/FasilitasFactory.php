<?php

namespace Database\Factories;

use App\Enums\StatusAktif;
use App\Models\Lantai;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fasilitas>
 */
class FasilitasFactory extends Factory
{
    public function definition(): array
    {
        $kategori = fake()->randomElement([
            'Ruang Rapat', 'Ruang Kelas', 'Convention Hall', 'Aula Serbaguna', 'Ruang Seminar',
        ]);

        return [
            'id_lantai'          => Lantai::query()->inRandomOrder()->value('id_lantai'),
            'kode_fasilitas'     => 'FAS-'.strtoupper(Str::random(6)),
            'nama_fasilitas'     => $kategori.' '.fake()->randomElement(['Melati', 'Anggrek', 'Cendana', 'Nusantara', 'Garuda', 'Merapi']),
            'kategori_fasilitas' => $kategori,
            'kapasitas'          => fake()->numberBetween(10, 300),
            'luas'               => fake()->randomFloat(2, 20, 500),
            'foto'               => null,
            'deskripsi'          => fake()->sentence(10),
            'status_aktif'       => StatusAktif::Aktif->value,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status_aktif' => StatusAktif::TidakAktif->value]);
    }
}
