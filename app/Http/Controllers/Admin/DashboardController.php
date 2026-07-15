<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusAktif;
use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\Reservasi;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $perStatus = Reservasi::query()
            ->selectRaw('status_reservasi, COUNT(*) as jumlah')
            ->groupBy('status_reservasi')
            ->pluck('jumlah', 'status_reservasi');

        $statistik = [
            'total'      => (int) $perStatus->sum(),
            'menunggu'   => (int) $perStatus->get(StatusReservasi::Menunggu->value, 0),
            'disetujui'  => (int) $perStatus->get(StatusReservasi::Disetujui->value, 0),
            'ditolak'    => (int) $perStatus->get(StatusReservasi::Ditolak->value, 0),
            'dibatalkan' => (int) $perStatus->get(StatusReservasi::Dibatalkan->value, 0),
        ];

        $fasilitasPerKategori = Fasilitas::query()
            ->where('status_aktif', StatusAktif::Aktif->value)
            ->selectRaw('kategori_fasilitas, COUNT(*) as jumlah')
            ->groupBy('kategori_fasilitas')
            ->orderBy('kategori_fasilitas')
            ->pluck('jumlah', 'kategori_fasilitas');

        $terbaru = Reservasi::query()
            ->with(['pemesan', 'tarifSewa.fasilitas'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('statistik', 'fasilitasPerKategori', 'terbaru'));
    }
}
