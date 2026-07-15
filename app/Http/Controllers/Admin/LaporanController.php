<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusAktif;
use App\Enums\StatusReservasi;
use App\Http\Controllers\Controller;
use App\Models\Fasilitas;
use App\Models\Lantai;
use App\Models\Laporan;
use App\Models\Reservasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Laporan rekap inventaris/okupansi STATIS fasilitas (bukan rekap transaksi per rentang).
 * Format & aturan kolom sesuai db-spec-faktur-laporan-format.md (versi final).
 */
class LaporanController extends Controller
{
    private const BULAN_ID = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request): View
    {
        return view('admin.laporan.index', array_merge($this->build($request), [
            'daftarLantai'   => Lantai::orderBy('id_lantai')->get(),
            'daftarKategori' => Fasilitas::select('kategori_fasilitas')->distinct()->orderBy('kategori_fasilitas')->pluck('kategori_fasilitas'),
            'filter'         => $request->only(['lantai', 'kategori', 'status']),
        ]));
    }

    public function exportPdf(Request $request): Response
    {
        $data = $this->build($request);

        Laporan::create([
            'id_admin'      => Auth::guard('admin')->id(),
            'tanggal'       => now(),
            'jenis_laporan' => $data['deskripsi'],
        ]);

        return Pdf::loadView('admin.pdf.laporan', $data)
            ->setPaper('a4', 'portrait')
            ->download('laporan-fasilitas-'.now()->format('Ymd-His').'.pdf');
    }

    /**
     * @return array{rows: \Illuminate\Support\Collection, totalIsi: int, totalAvailable: int,
     *               judulTanggal: string, deskripsi: string, adminNama: ?string, cetakWaktu: string}
     */
    private function build(Request $request): array
    {
        $statusFilter = $request->input('status'); // ISI | Available | (kosong=semua)
        $today = Carbon::today()->toDateString();

        $fasilitas = Fasilitas::query()
            ->with(['lantai', 'tarifSewa' => fn ($q) => $q->where('status_aktif', StatusAktif::Aktif->value)->with('jenisSewa')])
            ->when($request->filled('lantai'), fn ($q) => $q->where('id_lantai', $request->input('lantai')))
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori_fasilitas', $request->input('kategori')))
            ->orderBy('id_lantai')->orderBy('kode_fasilitas')
            ->get();

        // Fasilitas yang punya Reservasi aktif (Menunggu/Disetujui) mencakup tanggal cetak.
        $terisi = array_flip(
            Reservasi::query()
                ->join('tarif_sewa', 'tarif_sewa.id_tarif_sewa', '=', 'reservasi.id_tarif_sewa')
                ->whereIn('reservasi.status_reservasi', [StatusReservasi::Menunggu->value, StatusReservasi::Disetujui->value])
                ->whereDate('reservasi.tanggal_mulai', '<=', $today)
                ->whereDate('reservasi.tanggal_selesai', '>=', $today)
                ->distinct()
                ->pluck('tarif_sewa.id_fasilitas')
                ->all()
        );

        // Harga standar per kategori (tarif aktif) — fallback untuk fasilitas ISI/non-aktif
        // yang tidak punya tarif sendiri, supaya kolom harga tetap terisi.
        $standarKategori = $this->standarHargaKategori();

        $rows = collect();
        foreach ($fasilitas as $f) {
            $isi = $f->status_aktif !== StatusAktif::Aktif || isset($terisi[$f->id_fasilitas]);

            if ($statusFilter === 'ISI' && ! $isi) {
                continue;
            }
            if ($statusFilter === 'Available' && $isi) {
                continue;
            }

            [$harga, $estimasi] = $this->hargaBulanan($f, $standarKategori);

            $rows->push([
                'uraian'     => 'Lantai '.$f->lantai->nomor_lantai.' ('.$f->kode_fasilitas.')',
                'volume'     => $f->luas,
                'harga'      => $harga,
                'estimasi'   => $estimasi,
                'keterangan' => $isi ? 'ISI' : 'Available',
                'isi'        => $isi ? 1 : 0,
                'available'  => $isi ? 0 : 1,
            ]);
        }

        $rows = $rows->values()->map(fn ($row, $i) => array_merge(['no' => $i + 1], $row));

        return [
            'rows'         => $rows,
            'totalIsi'     => (int) $rows->sum('isi'),
            'totalAvailable' => (int) $rows->sum('available'),
            'judulTanggal' => $this->tanggalIndonesia(Carbon::today()),
            'deskripsi'    => $this->deskripsi($request),
            'adminNama'    => Auth::guard('admin')->user()?->nama_admin,
            'cetakWaktu'   => now()->format('d/m/Y H:i'),
        ];
    }

    /**
     * Harga per bulan: tarif Bulan sendiri → tarif Harian sendiri ×30 → standar kategori.
     * Fasilitas ISI (disewa tenant / non-aktif) tidak punya tarif sendiri, jadi memakai
     * harga standar kategori agar kolom harga selalu terisi sesuai harga sewa berlaku.
     *
     * @param  array<string, array{Bulan: ?float, Hari: ?float}>  $standar
     * @return array{0: ?float, 1: ?string} [harga, catatan kecil di bawah angka]
     */
    private function hargaBulanan(Fasilitas $fasilitas, array $standar): array
    {
        $tarif = $fasilitas->tarifSewa;

        if ($bulan = $tarif->first(fn ($t) => $t->jenisSewa->satuan->value === 'Bulan')) {
            return [(float) $bulan->harga, null];
        }
        if ($hari = $tarif->first(fn ($t) => $t->jenisSewa->satuan->value === 'Hari')) {
            return [(float) $hari->harga * 30, 'estimasi dari tarif harian'];
        }

        $std = $standar[$fasilitas->kategori_fasilitas] ?? null;
        if ($std && $std['Bulan'] !== null) {
            return [$std['Bulan'], null];
        }
        if ($std && $std['Hari'] !== null) {
            return [$std['Hari'] * 30, 'estimasi dari tarif harian'];
        }

        return [null, null];
    }

    /** @return array<string, array{Bulan: ?float, Hari: ?float}> harga standar per kategori dari tarif aktif */
    private function standarHargaKategori(): array
    {
        $map = [];

        \App\Models\TarifSewa::where('status_aktif', StatusAktif::Aktif->value)
            ->with(['jenisSewa', 'fasilitas'])
            ->get()
            ->each(function ($t) use (&$map) {
                $kategori = $t->fasilitas?->kategori_fasilitas;
                $satuan = $t->jenisSewa?->satuan?->value;
                if (! $kategori || ! in_array($satuan, ['Bulan', 'Hari'], true)) {
                    return;
                }
                $map[$kategori] ??= ['Bulan' => null, 'Hari' => null];
                // Ambil harga pertama per kategori+satuan sebagai standar.
                $map[$kategori][$satuan] ??= (float) $t->harga;
            });

        return $map;
    }

    private function tanggalIndonesia(Carbon $tanggal): string
    {
        return $tanggal->day.' '.self::BULAN_ID[$tanggal->month].' '.$tanggal->year;
    }

    private function deskripsi(Request $request): string
    {
        $bagian = [];
        if ($request->filled('lantai')) {
            $bagian[] = 'Lantai '.(Lantai::find($request->input('lantai'))?->nomor_lantai ?? $request->input('lantai'));
        }
        if ($request->filled('kategori')) {
            $bagian[] = $request->input('kategori');
        }
        if ($request->filled('status')) {
            $bagian[] = 'Status '.$request->input('status');
        }

        return 'Data Fasilitas Gedung BITC'.($bagian ? ' — '.implode(', ', $bagian) : '');
    }
}
