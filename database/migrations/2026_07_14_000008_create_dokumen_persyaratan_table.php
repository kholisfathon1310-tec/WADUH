<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dokumen_persyaratan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_dokumen');
            $table->unsignedInteger('id_reservasi');
            $table->string('jenis_dokumen');
            $table->string('nama_file');
            $table->string('lokasi_file', 500);
            $table->dateTime('tanggal_upload');
            $table->enum('status_verifikasi', ['Menunggu', 'Valid', 'Tidak Valid']);
            $table->timestamps();

            $table->index('id_reservasi');
            $table->foreign('id_reservasi')->references('id_reservasi')->on('reservasi')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_persyaratan');
    }
};
