<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_laporan');
            $table->unsignedInteger('id_admin');
            $table->date('tanggal');
            $table->string('jenis_laporan');
            $table->timestamps();

            $table->index('id_admin');
            $table->foreign('id_admin')->references('id_admin')->on('admin')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
