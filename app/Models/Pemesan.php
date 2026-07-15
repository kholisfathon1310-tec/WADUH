<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemesan extends Model
{
    use HasFactory;

    protected $table = 'pemesan';
    protected $primaryKey = 'id_pemesan';

    protected $fillable = [
        'nama_lengkap',
        'alamat',
        'usia',
        'pekerjaan',
        'no_telepon',
        'email',
    ];

    protected $casts = [
        'usia' => 'integer',
    ];

    public function reservasi(): HasMany
    {
        return $this->hasMany(Reservasi::class, 'id_pemesan', 'id_pemesan');
    }
}
