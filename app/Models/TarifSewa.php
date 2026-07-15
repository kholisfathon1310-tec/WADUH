<?php

namespace App\Models;

use App\Enums\StatusAktif;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TarifSewa extends Model
{
    use HasFactory;

    protected $table = 'tarif_sewa';
    protected $primaryKey = 'id_tarif_sewa';

    protected $fillable = [
        'id_fasilitas',
        'id_jenis_sewa',
        'harga',
        'status_aktif',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'status_aktif' => StatusAktif::class,
    ];

    public function fasilitas(): BelongsTo
    {
        return $this->belongsTo(Fasilitas::class, 'id_fasilitas', 'id_fasilitas');
    }

    public function jenisSewa(): BelongsTo
    {
        return $this->belongsTo(JenisSewa::class, 'id_jenis_sewa', 'id_jenis_sewa');
    }

    public function reservasi(): HasMany
    {
        return $this->hasMany(Reservasi::class, 'id_tarif_sewa', 'id_tarif_sewa');
    }

    /**
     * Tarif yang tersedia untuk reservasi baru: tarif aktif milik fasilitas yang juga aktif.
     * Business rule: fasilitas & tarif tidak aktif tidak boleh dipakai reservasi baru.
     */
    public function scopeTersedia(Builder $query): Builder
    {
        return $query
            ->where('status_aktif', StatusAktif::Aktif->value)
            ->whereHas('fasilitas', function (Builder $q) {
                $q->where('status_aktif', StatusAktif::Aktif->value);
            });
    }
}
