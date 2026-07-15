<?php

namespace App\Models;

use App\Enums\StatusReservasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStatus extends Model
{
    protected $table = 'riwayat_status';
    protected $primaryKey = 'id_riwayat';

    protected $fillable = [
        'id_reservasi',
        'id_admin',
        'status_sebelumnya',
        'status_baru',
        'tanggal_perubahan',
        'keterangan',
    ];

    protected $casts = [
        'status_sebelumnya' => StatusReservasi::class,
        'status_baru' => StatusReservasi::class,
        'tanggal_perubahan' => 'datetime',
    ];

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi', 'id_reservasi');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }
}
