<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            // Dibutuhkan oleh checkbox "Ingat saya di perangkat ini" pada form login admin
            // (Auth::guard('admin')->attempt($credentials, $remember)) — tanpa kolom ini,
            // login dengan remember=true gagal dengan SQL error kolom tidak ditemukan.
            $table->rememberToken()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
