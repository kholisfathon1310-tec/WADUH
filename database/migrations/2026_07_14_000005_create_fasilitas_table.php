<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fasilitas', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_fasilitas');
            $table->unsignedInteger('id_lantai');
            $table->string('kode_fasilitas', 100)->unique();
            $table->string('nama_fasilitas');
            $table->string('kategori_fasilitas', 100);
            $table->integer('kapasitas');
            $table->decimal('luas', 10, 2);
            $table->string('foto')->nullable();
            $table->text('deskripsi')->nullable();
            $table->enum('status_aktif', ['Aktif', 'Tidak Aktif']);
            $table->timestamps();

            $table->index('id_lantai');
            $table->foreign('id_lantai')->references('id_lantai')->on('lantai')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fasilitas');
    }
};
