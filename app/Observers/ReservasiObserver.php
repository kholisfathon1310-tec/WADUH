<?php

namespace App\Observers;

use App\Models\Reservasi;
use App\Models\RiwayatStatus;

class ReservasiObserver
{
    /**
     * Sebelum UPDATE ditulis: kalau status_reservasi berubah dan tanggal_diproses masih null,
     * isi otomatis (DELTA #5 — atribut relasi "Memproses"). Karena diset di event `updating`,
     * nilainya ikut ter-persist pada query UPDATE yang sama.
     */
    public function updating(Reservasi $reservasi): void
    {
        // tanggal_diproses = timestamp saat ADMIN memproses pertama kali (DELTA #5). Perubahan
        // status yang dipicu pemesan sendiri (mis. pembatalan mandiri di Stage 2, id_admin null)
        // tidak menghitung sebagai "diproses admin", jadi tanggal_diproses dibiarkan null.
        if (
            $reservasi->isDirty('status_reservasi')
            && is_null($reservasi->tanggal_diproses)
            && ! is_null($reservasi->id_admin)
        ) {
            $reservasi->tanggal_diproses = now();
        }
    }

    /**
     * Setelah UPDATE tersimpan: setiap perubahan status_reservasi WAJIB mencatat satu baris
     * Riwayat_Status (dibuat via observer, bukan manual di controller).
     */
    public function updated(Reservasi $reservasi): void
    {
        if (! $reservasi->wasChanged('status_reservasi')) {
            return;
        }

        RiwayatStatus::create([
            'id_reservasi'      => $reservasi->id_reservasi,
            'id_admin'          => $reservasi->id_admin,
            'status_sebelumnya' => $reservasi->getOriginal('status_reservasi'),
            'status_baru'       => $reservasi->status_reservasi,
            'tanggal_perubahan' => now(),
            'keterangan'        => $reservasi->keteranganRiwayat,
        ]);

        $reservasi->keteranganRiwayat = null;
    }
}
