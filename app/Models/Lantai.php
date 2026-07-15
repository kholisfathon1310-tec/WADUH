<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lantai extends Model
{
    protected $table = 'lantai';
    protected $primaryKey = 'id_lantai';

    protected $fillable = [
        'nomor_lantai',
        'gambar_denah',
    ];

    public function fasilitas(): HasMany
    {
        return $this->hasMany(Fasilitas::class, 'id_lantai', 'id_lantai');
    }
}
