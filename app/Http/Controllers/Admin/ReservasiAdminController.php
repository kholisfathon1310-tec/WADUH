<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LockStatus;
use App\Enums\StatusReservasi;
use App\Enums\StatusVerifikasi;
use App\Http\Controllers\Controller;
use App\Models\DokumenPersyaratan;
use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\Lantai;
use App\Models\Reservasi;
use App\Services\ReservasiApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReservasiAdminController extends Controller
{
    public function __construct(private readonly ReservasiApprovalService $approval)
    {
    }

    /** Daftar seluruh Reservasi, dikelompokkan per kode_transaksi, dengan search & filter. */
    public function index(Request $request): View
    {
        $reservasi = Reservasi::query()
            ->with(['pemesan', 'tarifSewa.fasilitas.lantai', 'tarifSewa.jenisSewa', 'faktur'])
            ->when($request->filled('pemesan'), fn ($q) => $q->whereHas('pemesan',
                fn ($p) => $p->where('nama_lengkap', 'like', '%'.$request->input('pemesan').'%')))
            ->when($request->filled('kategori'), fn ($q) => $q->whereHas('tarifSewa.fasilitas',
                fn ($f) => $f->where('kategori_fasilitas', $request->input('kategori'))))
            ->when($request->filled('nama_fasilitas'), fn ($q) => $q->whereHas('tarifSewa.fasilitas',
                fn ($f) => $f->where('nama_fasilitas', 'like', '%'.$request->input('nama_fasilitas').'%')))
            ->when($request->filled('lantai'), fn ($q) => $q->whereHas('tarifSewa.fasilitas',
                fn ($f) => $f->where('id_lantai', $request->input('lantai'))))
            ->when($request->filled('jenis_sewa'), fn ($q) => $q->whereHas('tarifSewa.jenisSewa',
                fn ($j) => $j->where('satuan', $request->input('jenis_sewa'))))
            ->when($request->filled('status'), fn ($q) => $q->where('status_reservasi', $request->input('status')))
            ->when($request->filled('tanggal'), fn ($q) => $q
                ->whereDate('tanggal_mulai', '<=', $request->input('tanggal'))
                ->whereDate('tanggal_selesai', '>=', $request->input('tanggal')))
            ->latest('created_at')
            ->get();

        // Kelompokkan per kode_transaksi mempertahankan urutan terbaru.
        $grup = $reservasi->groupBy('kode_transaksi');

        // Filter/pencarian interaktif: request AJAX cukup dibalas fragmen hasil, tanpa layout.
        if ($request->ajax()) {
            return view('admin.reservasi.partials.hasil', compact('grup'));
        }

        return view('admin.reservasi.index', [
            'grup'          => $grup,
            'daftarLantai'  => Lantai::orderBy('id_lantai')->get(),
            'daftarKategori' => Fasilitas::select('kategori_fasilitas')->distinct()->orderBy('kategori_fasilitas')->pluck('kategori_fasilitas'),
            'daftarJenis'   => JenisSewa::orderBy('id_jenis_sewa')->get(),
            'daftarStatus'  => StatusReservasi::cases(),
            'filter'        => $request->only(['pemesan', 'kategori', 'nama_fasilitas', 'lantai', 'jenis_sewa', 'status', 'tanggal']),
        ]);
    }

    /**
     * Detail SATU pemesanan: menampilkan seluruh ruangan dalam kode reservasi/transaksi
     * yang sama (pemesan memesan 2 ruangan → admin cukup melihat 1 halaman detail).
     */
    public function show(string $kodeReservasi): View
    {
        $reservasi = $this->cari($kodeReservasi, ['pemesan', 'faktur']);

        $items = Reservasi::where('kode_transaksi', $reservasi->kode_transaksi)
            ->with(['tarifSewa.fasilitas.lantai', 'tarifSewa.jenisSewa', 'dokumenPersyaratan', 'riwayatStatus.admin', 'faktur'])
            ->orderBy('id_reservasi')
            ->get();

        // Checklist kelayakan per ruangan (key: id_reservasi).
        $checklists = $items->mapWithKeys(fn (Reservasi $it) => [
            $it->id_reservasi => $this->approval->checklist($it),
        ]);

        $pending = $items->where('status_reservasi', StatusReservasi::Menunggu);
        $bolehTolak = $pending->isNotEmpty();
        $bolehSetujui = $bolehTolak && $pending->every(
            fn (Reservasi $it) => collect($checklists[$it->id_reservasi])->every(fn ($c) => $c['passed'])
        );

        // Riwayat gabungan seluruh ruangan.
        $riwayat = $items->flatMap->riwayatStatus->sortByDesc('id_riwayat');

        return view('admin.reservasi.show', compact('reservasi', 'items', 'checklists', 'bolehSetujui', 'bolehTolak', 'riwayat'));
    }

    /** Setujui SELURUH ruangan Menunggu dalam pemesanan ini (checklist semua harus lolos). */
    public function setujui(string $kodeReservasi): RedirectResponse
    {
        $reservasi = $this->cari($kodeReservasi);

        $pending = Reservasi::where('kode_transaksi', $reservasi->kode_transaksi)
            ->where('status_reservasi', StatusReservasi::Menunggu->value)
            ->orderBy('id_reservasi')
            ->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'Tidak ada ruangan berstatus Menunggu pada pemesanan ini.');
        }
        foreach ($pending as $item) {
            if (! $this->approval->passes($item)) {
                return back()->with('error', 'Belum semua syarat terpenuhi. Periksa checklist tiap ruangan.');
            }
        }

        foreach ($pending as $item) {
            // Set id_admin sebelum status agar Observer mengisi tanggal_diproses & Riwayat_Status.
            $item->id_admin = Auth::guard('admin')->id();
            $item->lock_status = LockStatus::Confirmed;
            $item->status_reservasi = StatusReservasi::Disetujui;
            $item->save();
        }

        return back()->with('success', "Reservasi {$reservasi->kode_transaksi} disetujui ({$pending->count()} ruangan).");
    }

    /** Tolak SELURUH ruangan Menunggu dalam pemesanan ini; alasan tercatat via Observer. */
    public function tolak(Request $request, string $kodeReservasi): RedirectResponse
    {
        $data = $request->validate(
            ['alasan' => ['required', 'string', 'max:1000']],
            [
                'alasan.required' => 'Alasan penolakan wajib diisi. Alasan ini akan ditampilkan kepada pemesan pada riwayat status.',
                'alasan.max'      => 'Alasan terlalu panjang, maksimal 1000 karakter.',
            ],
        );

        $reservasi = $this->cari($kodeReservasi);

        $pending = Reservasi::where('kode_transaksi', $reservasi->kode_transaksi)
            ->where('status_reservasi', StatusReservasi::Menunggu->value)
            ->get();

        if ($pending->isEmpty()) {
            return back()->with('error', 'Tidak ada ruangan berstatus Menunggu pada pemesanan ini.');
        }

        foreach ($pending as $item) {
            $item->id_admin = Auth::guard('admin')->id();
            $item->keteranganRiwayat = $data['alasan'];
            $item->lock_status = LockStatus::Released;
            $item->status_reservasi = StatusReservasi::Ditolak;
            $item->save();
        }

        return back()->with('success', "Reservasi {$reservasi->kode_transaksi} ditolak ({$pending->count()} ruangan).");
    }

    /** Set status_verifikasi satu dokumen persyaratan. */
    public function verifikasiDokumen(Request $request, DokumenPersyaratan $dokumen): RedirectResponse
    {
        $data = $request->validate([
            'status_verifikasi' => ['required', 'string', 'in:'.implode(',', array_map(fn ($c) => $c->value, StatusVerifikasi::cases()))],
        ]);

        $dokumen->status_verifikasi = $data['status_verifikasi'];
        $dokumen->save();

        return back()->with('success', 'Status dokumen diperbarui.');
    }

    /** @param array<int,string> $with */
    private function cari(string $kodeReservasi, array $with = []): Reservasi
    {
        return Reservasi::where('kode_reservasi', $kodeReservasi)->with($with)->firstOrFail();
    }
}
