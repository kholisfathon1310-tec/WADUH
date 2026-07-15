<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Faktur extends Model
{
    protected $table = 'faktur';
    protected $primaryKey = 'id_faktur';

    protected $fillable = [
        'id_reservasi',
        'nomor_faktur',
        'tanggal_faktur',
    ];

    protected $casts = [
        'tanggal_faktur' => 'date',
    ];

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi', 'id_reservasi');
    }
}
