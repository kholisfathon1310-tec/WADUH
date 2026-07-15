<?php

namespace App\Http\Controllers;

use App\Enums\StatusAktif;
use App\Models\Lantai;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Landing page WADUH — Wadah Akses Digital Unit Hunian BITC.
     * Menampilkan info per lantai (kategori, jumlah ruang, jumlah tersedia) dari data real.
     */
    public function __invoke(): View
    {
        $lantai = Lantai::with('fasilitas')
            ->orderBy('id_lantai')
            ->get()
            ->map(function (Lantai $l) {
                $aktif = $l->fasilitas->where('status_aktif', StatusAktif::Aktif);

                return [
                    'id'       => $l->id_lantai,
                    'nomor'    => $l->nomor_lantai,
                    'kategori' => $l->fasilitas->first()?->kategori_fasilitas ?? '-',
                    'total'    => $l->fasilitas->count(),
                    'tersedia' => $aktif->count(),
                ];
            });

        return view('home', ['daftarLantai' => $lantai]);
    }
}
