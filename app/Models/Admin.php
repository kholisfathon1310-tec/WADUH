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
        'foto',
        'no_whatsapp',
        'alamat',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        // Auto-hash (bcrypt) saat atribut password di-set.
        'password' => 'hashed',
    ];

    /** URL foto profil (disk 'public'), null kalau belum pernah unggah. */
    public function fotoUrl(): ?string
    {
        return $this->foto ? \Illuminate\Support\Facades\Storage::url($this->foto) : null;
    }

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
