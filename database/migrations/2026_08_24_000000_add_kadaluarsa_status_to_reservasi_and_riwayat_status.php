<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE reservasi MODIFY status_reservasi ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_sebelumnya ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_baru ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan', 'Kadaluarsa') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE riwayat_status MODIFY status_baru ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan') NOT NULL");
        DB::statement("ALTER TABLE riwayat_status MODIFY status_sebelumnya ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan') NOT NULL");
        DB::statement("ALTER TABLE reservasi MODIFY status_reservasi ENUM('Menunggu', 'Disetujui', 'Ditolak', 'Selesai', 'Dibatalkan') NOT NULL");
    }
};
