<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservasi', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_reservasi');
            $table->unsignedInteger('id_pemesan');
            $table->unsignedInteger('id_tarif_sewa');
            // id_admin nullable sampai reservasi diproses oleh admin.
            $table->unsignedInteger('id_admin')->nullable();
            $table->string('kode_reservasi', 100)->unique();
            // DELTA #2: kode_transaksi mengelompokkan beberapa baris Reservasi dari satu checkout.
            $table->string('kode_transaksi', 30)->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            // DELTA #1: jam_mulai TIME NULL — wajib untuk hitung durasi & cek bentrok Reservasi Per Jam.
            $table->time('jam_mulai')->nullable();
            // Reservasi Harian/Bulanan tidak memakai jam → jam_selesai ikut nullable (Stage 2).
            $table->time('jam_selesai')->nullable();
            $table->integer('durasi');
            $table->integer('jumlah_pengguna');
            $table->text('keperluan');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('total_biaya', 15, 2);
            $table->enum('status_reservasi', ['Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan']);
            // DELTA #3: lock_status + lock_expires_at untuk mekanisme hold ketersediaan.
            $table->enum('lock_status', ['temporary_hold', 'pending_approval', 'confirmed', 'released'])
                ->default('temporary_hold');
            $table->dateTime('lock_expires_at')->nullable();
            // DELTA #5: tanggal_diproses — atribut relasi "Memproses" (Admin 1:N Reservasi).
            $table->dateTime('tanggal_diproses')->nullable();
            $table->timestamps();

            $table->index('id_pemesan');
            $table->index('id_tarif_sewa');
            $table->index('id_admin');
            // DELTA #2: INDEX (bukan unique) pada kode_transaksi.
            $table->index('kode_transaksi');
            $table->foreign('id_pemesan')->references('id_pemesan')->on('pemesan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_tarif_sewa')->references('id_tarif_sewa')->on('tarif_sewa')->cascadeOnUpdate()->restrictOnDelete();
            // id_admin nullable → ON DELETE tetap RESTRICT sesuai standar.
            $table->foreign('id_admin')->references('id_admin')->on('admin')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservasi');
    }
};
