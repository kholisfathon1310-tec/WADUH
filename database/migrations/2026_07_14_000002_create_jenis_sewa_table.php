<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_sewa', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_jenis_sewa');
            // DELTA #4: satuan pakai ENUM('Jam','Hari','Bulan') — bukan cuma ENUM('Jam','Hari').
            $table->enum('satuan', ['Jam', 'Hari', 'Bulan']);
            $table->integer('durasi_minimum');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_sewa');
    }
};
