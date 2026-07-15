<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('riwayat_status', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_riwayat');
            $table->unsignedInteger('id_reservasi');
            // Nullable: perubahan status yang dipicu pemesan (mis. pembatalan mandiri di Stage 2)
            // tidak punya admin. Perubahan oleh admin (Stage 3) tetap mengisi id_admin.
            $table->unsignedInteger('id_admin')->nullable();
            $table->enum('status_sebelumnya', ['Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan']);
            $table->enum('status_baru', ['Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan']);
            $table->dateTime('tanggal_perubahan');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index('id_reservasi');
            $table->index('id_admin');
            $table->foreign('id_reservasi')->references('id_reservasi')->on('reservasi')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_admin')->references('id_admin')->on('admin')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_status');
    }
};
