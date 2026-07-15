<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faktur', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->increments('id_faktur');
            $table->unsignedInteger('id_reservasi')->unique();
            $table->string('nomor_faktur', 100)->unique();
            $table->date('tanggal_faktur');
            $table->timestamps();

            $table->foreign('id_reservasi')->references('id_reservasi')->on('reservasi')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faktur');
    }
};
