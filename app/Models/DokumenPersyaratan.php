<?php

namespace App\Models;

use App\Enums\StatusVerifikasi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPersyaratan extends Model
{
    protected $table = 'dokumen_persyaratan';
    protected $primaryKey = 'id_dokumen';

    protected $fillable = [
        'id_reservasi',
        'jenis_dokumen',
        'nama_file',
        'lokasi_file',
        'tanggal_upload',
        'status_verifikasi',
    ];

    protected $casts = [
        'tanggal_upload' => 'datetime',
        'status_verifikasi' => StatusVerifikasi::class,
    ];

    public function reservasi(): BelongsTo
    {
        return $this->belongsTo(Reservasi::class, 'id_reservasi', 'id_reservasi');
    }
}
