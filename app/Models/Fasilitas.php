<?php

namespace App\Models;

use App\Enums\StatusAktif;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Fasilitas extends Model
{
    use HasFactory;

    protected $table = 'fasilitas';
    protected $primaryKey = 'id_fasilitas';

    protected $fillable = [
        'id_lantai',
        'kode_fasilitas',
        'nama_fasilitas',
        'kategori_fasilitas',
        'kapasitas',
        'luas',
        'foto',
        'deskripsi',
        'status_aktif',
    ];

    protected $casts = [
        'luas' => 'decimal:2',
        'status_aktif' => StatusAktif::class,
    ];

    public function lantai(): BelongsTo
    {
        return $this->belongsTo(Lantai::class, 'id_lantai', 'id_lantai');
    }

    public function tarifSewa(): HasMany
    {
        return $this->hasMany(TarifSewa::class, 'id_fasilitas', 'id_fasilitas');
    }
}
