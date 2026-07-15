<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Password plaintext di-hash otomatis (bcrypt) lewat cast 'password' => 'hashed' di model Admin.
        Admin::firstOrCreate(
            ['email' => 'admin@waduh.test'],
            [
                'nama_admin' => 'Administrator',
                'password'   => 'password',
            ],
        );
    }
}
