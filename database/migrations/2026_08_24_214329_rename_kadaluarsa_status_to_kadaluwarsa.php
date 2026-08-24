<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ganti ejaan status 'Kadaluarsa' -> 'Kadaluwarsa'. Enum di-widen dulu (memuat kedua nilai)
     * supaya baris existing bisa di-UPDATE tanpa ditolak database, baru dipersempit ke ejaan baru.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE reservasi MODIFY status_reservasi ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa', 'Kadaluwarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_sebelumnya ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa', 'Kadaluwarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_baru ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa', 'Kadaluwarsa') NOT NULL");

        DB::statement("UPDATE reservasi SET status_reservasi = 'Kadaluwarsa' WHERE status_reservasi = 'Kadaluarsa'");
        DB::statement("UPDATE riwayat_status SET status_sebelumnya = 'Kadaluwarsa' WHERE status_sebelumnya = 'Kadaluarsa'");
        DB::statement("UPDATE riwayat_status SET status_baru = 'Kadaluwarsa' WHERE status_baru = 'Kadaluarsa'");

        DB::statement("ALTER TABLE reservasi MODIFY status_reservasi ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluwarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_sebelumnya ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluwarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_baru ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluwarsa') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE reservasi MODIFY status_reservasi ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa', 'Kadaluwarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_sebelumnya ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa', 'Kadaluwarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_baru ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa', 'Kadaluwarsa') NOT NULL");

        DB::statement("UPDATE reservasi SET status_reservasi = 'Kadaluarsa' WHERE status_reservasi = 'Kadaluwarsa'");
        DB::statement("UPDATE riwayat_status SET status_sebelumnya = 'Kadaluarsa' WHERE status_sebelumnya = 'Kadaluwarsa'");
        DB::statement("UPDATE riwayat_status SET status_baru = 'Kadaluarsa' WHERE status_baru = 'Kadaluwarsa'");

        DB::statement("ALTER TABLE reservasi MODIFY status_reservasi ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_sebelumnya ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_baru ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa') NOT NULL");
    }
};
