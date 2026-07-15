<?php

namespace App\Models;

use App\Enums\SatuanSewa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisSewa extends Model
{
    protected $table = 'jenis_sewa';
    protected $primaryKey = 'id_jenis_sewa';

    protected $fillable = [
        'satuan',
        'durasi_minimum',
    ];

    protected $casts = [
        'satuan' => SatuanSewa::class, // DELTA #4: termasuk 'Bulan'
        'durasi_minimum' => 'integer',
    ];

    public function tarifSewa(): HasMany
    {
        return $this->hasMany(TarifSewa::class, 'id_jenis_sewa', 'id_jenis_sewa');
    }
}
