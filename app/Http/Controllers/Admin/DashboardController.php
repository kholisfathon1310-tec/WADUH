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
        // Periode chart tren: 'harian' (7 hari terakhir) atau 'bulanan' (12 bulan terakhir).
        // Dipilih lewat query string ?periode=, default 'harian'. Nilai tak dikenal → fallback harian.
        $periode = request('periode', 'harian');
        $periode = in_array($periode, ['harian', 'bulanan'], true) ? $periode : 'harian';

        $perStatus = Reservasi::query()
            ->selectRaw('status_reservasi, COUNT(*) as jumlah')
            ->groupBy('status_reservasi')
            ->pluck('jumlah', 'status_reservasi');

        $statistik = [
            'total'      => (int) $perStatus->sum(),
            'menunggu'   => (int) $perStatus->get(StatusReservasi::Menunggu->value, 0),
            'disetujui'  => (int) $perStatus->get(StatusReservasi::Disetujui->value, 0),
            'selesai'    => (int) $perStatus->get(StatusReservasi::Selesai->value, 0),
            'ditolak'    => (int) $perStatus->get(StatusReservasi::Ditolak->value, 0),
            'dibatalkan' => (int) $perStatus->get(StatusReservasi::Dibatalkan->value, 0),
            'kadaluarsa' => (int) $perStatus->get(StatusReservasi::Kadaluarsa->value, 0),
        ];

        // Jumlah RESERVASI per kategori fasilitas — snapshot yang SEDANG BERLANGSUNG (Disetujui
        // saja). Begitu selesai dipakai (status Selesai), reservasinya lepas dari hitungan ini
        // supaya panel ini selalu mencerminkan pemakaian aktif saat ini, bukan riwayat.
        $reservasiPerKategori = Reservasi::query()
            ->where('status_reservasi', StatusReservasi::Disetujui->value)
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

        // Tren reservasi untuk bar chart di dashboard — dua mode:
        // - harian : 7 hari terakhir (termasuk hari ini), label "Sen, Sel, ..."
        // - bulanan: 12 bulan terakhir (termasuk bulan ini), label "Jan, Feb, ..."
        // Beda dengan panel kategori (yang cuma menghitung Disetujui): tren riwayat pemakaian
        // ini tetap menghitung reservasi yang sudah Selesai, supaya angkanya tidak berkurang
        // begitu masa pakainya berakhir — riwayat bulan/hari yang sudah lewat harus tetap utuh.
        // Selalu di-pad ke seluruh rentang supaya jumlah bar tetap konsisten meskipun
        // ada hari/bulan tanpa reservasi sama sekali.
        $statusAktif = [StatusReservasi::Disetujui->value, StatusReservasi::Selesai->value];

        if ($periode === 'bulanan') {
            $mulai = now()->startOfMonth()->subMonths(11);

            $countPerBulan = Reservasi::query()
                ->whereIn('status_reservasi', $statusAktif)
                ->where('created_at', '>=', $mulai)
                ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as bln, COUNT(*) as jumlah")
                ->groupBy('bln')
                ->pluck('jumlah', 'bln');

            $trendChart = collect(range(0, 11))->map(function ($i) use ($mulai, $countPerBulan) {
                $bln = $mulai->copy()->addMonths($i);
                $key = $bln->format('Y-m');

                return [
                    'tanggal' => $bln,
                    'label'   => $bln->translatedFormat('M'),   // Jan, Feb, ...
                    'jumlah'  => (int) ($countPerBulan[$key] ?? 0),
                    'isAktif' => $bln->isCurrentMonth(),
                ];
            })->all();
        } else {
            $mulai = now()->startOfDay()->subDays(6);

            $countPerHari = Reservasi::query()
                ->whereIn('status_reservasi', $statusAktif)
                ->where('created_at', '>=', $mulai)
                ->selectRaw('DATE(created_at) as tgl, COUNT(*) as jumlah')
                ->groupBy('tgl')
                ->pluck('jumlah', 'tgl');

            $trendChart = collect(range(0, 6))->map(function ($i) use ($mulai, $countPerHari) {
                $tgl = $mulai->copy()->addDays($i);
                $key = $tgl->toDateString();

                return [
                    'tanggal' => $tgl,
                    'label'   => $tgl->translatedFormat('D'),   // Sen, Sel, ...
                    'jumlah'  => (int) ($countPerHari[$key] ?? 0),
                    'isAktif' => $tgl->isToday(),
                ];
            })->all();
        }

        return view('admin.dashboard', compact('statistik', 'reservasiPerKategori', 'terbaru', 'trendChart', 'periode'));
    }
}