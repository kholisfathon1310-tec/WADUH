<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarif_sewa', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_tarif_sewa');
            $table->unsignedInteger('id_fasilitas');
            $table->unsignedInteger('id_jenis_sewa');
            $table->decimal('harga', 15, 2);
            $table->enum('status_aktif', ['Aktif', 'Tidak Aktif']);
            $table->timestamps();

            $table->index('id_fasilitas');
            $table->index('id_jenis_sewa');
            $table->foreign('id_fasilitas')->references('id_fasilitas')->on('fasilitas')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_jenis_sewa')->references('id_jenis_sewa')->on('jenis_sewa')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarif_sewa');
    }
};
