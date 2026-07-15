<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Admin login memakai guard `admin` (lihat config/auth.php). Extends Authenticatable —
 * dibutuhkan Stage 3 untuk autentikasi; password sudah bcrypt sejak seeder Stage 1.
 */
class Admin extends Authenticatable
{
    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'nama_admin',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        // Auto-hash (bcrypt) saat atribut password di-set.
        'password' => 'hashed',
    ];

    public function reservasi(): HasMany
    {
        return $this->hasMany(Reservasi::class, 'id_admin', 'id_admin');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatus::class, 'id_admin', 'id_admin');
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(Laporan::class, 'id_admin', 'id_admin');
    }
}
