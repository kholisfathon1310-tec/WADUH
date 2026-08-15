<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Models\Reservasi;
use Illuminate\Support\Carbon;
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

        // Jumlah RESERVASI per kategori fasilitas — hanya yang Disetujui/Selesai (reservasi
        // aktif/sah), sama seperti aturan di Laporan. Menunggu/Ditolak/Dibatalkan tidak dihitung.
        $reservasiPerKategori = Reservasi::query()
            ->whereIn('status_reservasi', [StatusReservasi::Disetujui->value, StatusReservasi::Selesai->value])
            ->join('tarif_sewa', 'reservasi.id_tarif_sewa', '=', 'tarif_sewa.id_tarif_sewa')
            ->join('fasilitas', 'tarif_sewa.id_fasilitas', '=', 'fasilitas.id_fasilitas')
            ->selectRaw('fasilitas.kategori_fasilitas, COUNT(*) as jumlah')
            ->groupBy('fasilitas.kategori_fasilitas')
            ->orderBy('fasilitas.kategori_fasilitas')
            ->pluck('jumlah', 'kategori_fasilitas');

        $terbaru = Reservasi::query()
            ->with(['pemesan', 'tarifSewa.fasilitas'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        // Tren reservasi 7 hari terakhir (termasuk hari ini) — untuk bar chart di dashboard.
        // Sama seperti grafik kategori: hanya yang Disetujui/Selesai (reservasi aktif/sah).
        // Diagregasi per DATE(created_at) lalu di-pad ke tanggal yang hilang supaya bar
        // selalu berjumlah tujuh, meskipun hari itu tidak ada reservasi.
        $mulai = now()->startOfDay()->subDays(6);
        $countPerHari = Reservasi::query()
            ->whereIn('status_reservasi', [StatusReservasi::Disetujui->value, StatusReservasi::Selesai->value])
            ->where('created_at', '>=', $mulai)
            ->selectRaw('DATE(created_at) as tgl, COUNT(*) as jumlah')
            ->groupBy('tgl')
            ->pluck('jumlah', 'tgl');

        $trend7Hari = collect(range(0, 6))->map(function ($i) use ($mulai, $countPerHari) {
            $tgl = $mulai->copy()->addDays($i);
            $key = $tgl->toDateString();

            return [
                'tanggal' => $tgl,
                'label'   => $tgl->translatedFormat('D'),   // Sen, Sel, ...
                'jumlah'  => (int) ($countPerHari[$key] ?? 0),
            ];
        })->all();

        return view('admin.dashboard', compact('statistik', 'reservasiPerKategori', 'terbaru', 'trend7Hari'));
    }
}