<?php

namespace App\Http\Controllers;

use App\Enums\LockStatus;
use App\Enums\SatuanSewa;
use App\Enums\StatusAktif;
use App\Enums\StatusReservasi;
use App\Enums\StatusVerifikasi;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\TambahKeranjangRequest;
use App\Models\DokumenPersyaratan;
use App\Models\Fasilitas;
use App\Models\JenisSewa;
use App\Models\Lantai;
use App\Models\Pemesan;
use App\Models\Reservasi;
use App\Models\TarifSewa;
use App\Services\AvailabilityService;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReservasiController extends Controller
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly CartService $cart,
    ) {
    }

    /** Langkah 1 — pilih jenis fasilitas (kategori). */
    public function index(): View
    {
        $kategori = Fasilitas::query()
            ->where('status_aktif', StatusAktif::Aktif->value)
            ->select('kategori_fasilitas')
            ->distinct()
            ->orderBy('kategori_fasilitas')
            ->pluck('kategori_fasilitas');

        return view('reservasi.kategori', [
            'kategori'   => $kategori,
            'cartCount'  => $this->cart->count(),
        ]);
    }

    /** Langkah 2 — pilih jenis sewa yang tersedia untuk kategori. */
    public function jenisSewa(string $kategori): View
    {
        $this->pastikanKategoriAda($kategori);

        $jenis = JenisSewa::query()
            ->whereHas('tarifSewa', function ($q) use ($kategori) {
                $q->where('status_aktif', StatusAktif::Aktif->value)
                    ->whereHas('fasilitas', fn ($f) => $f
                        ->where('status_aktif', StatusAktif::Aktif->value)
                        ->where('kategori_fasilitas', $kategori));
            })
            ->orderBy('id_jenis_sewa')
            ->get();

        return view('reservasi.jenis-sewa', compact('kategori', 'jenis'));
    }

    /** Langkah 3 — pilih lantai yang punya fasilitas kategori+jenis ini. */
    public function lantai(Request $request, string $kategori): View
    {
        $this->pastikanKategoriAda($kategori);
        $jenis = $this->jenisDipilih($request);

        $lantai = Lantai::query()
            ->whereHas('fasilitas', fn ($f) => $this->fasilitasScope($f, $kategori, $jenis?->id_jenis_sewa))
            ->orderBy('nomor_lantai')
            ->get();

        return view('reservasi.lantai', compact('kategori', 'jenis', 'lantai'));
    }

    /** Langkah 4 — denah fasilitas per lantai dengan indikator warna ketersediaan. */
    public function denah(Request $request, string $kategori, Lantai $lantai): View
    {
        $this->pastikanKategoriAda($kategori);
        $jenis = $this->jenisDipilih($request);

        $fasilitas = Fasilitas::query()
            ->where('id_lantai', $lantai->id_lantai)
            ->where(fn ($f) => $this->fasilitasScope($f, $kategori, $jenis?->id_jenis_sewa))
            ->orderBy('nama_fasilitas')
            ->get();

        $slot = $this->slotDariRequest($request, $jenis?->satuan);
        $sessionId = $request->session()->getId();

        $status = $fasilitas->mapWithKeys(fn (Fasilitas $f) => [
            $f->id_fasilitas => $this->availability->statusFasilitas($f, $slot, $sessionId),
        ]);

        // Filter interaktif: request AJAX cukup dibalas fragmen hasil, tanpa layout.
        if ($request->ajax()) {
            return view('reservasi.partials.denah-hasil', compact('kategori', 'jenis', 'lantai', 'fasilitas', 'status', 'slot'));
        }

        return view('reservasi.denah', compact('kategori', 'jenis', 'lantai', 'fasilitas', 'status', 'slot'));
    }

    /** Detail fasilitas + form jadwal. */
    public function fasilitas(Request $request, Fasilitas $fasilitas): View
    {
        abort_if($fasilitas->status_aktif !== StatusAktif::Aktif, 404);
        $jenis = $this->jenisDipilih($request);

        // Tarif aktif untuk fasilitas + jenis yang dipilih.
        $tarif = TarifSewa::query()
            ->where('id_fasilitas', $fasilitas->id_fasilitas)
            ->where('status_aktif', StatusAktif::Aktif->value)
            ->when($jenis, fn ($q) => $q->where('id_jenis_sewa', $jenis->id_jenis_sewa))
            ->with('jenisSewa')
            ->first();

        abort_if($tarif === null, 404, 'Tarif untuk fasilitas & jenis sewa ini tidak tersedia.');

        return view('reservasi.fasilitas', [
            'fasilitas' => $fasilitas->load('lantai'),
            'tarif'     => $tarif,
            'jenis'     => $tarif->jenisSewa,
        ]);
    }

    /**
     * Tambah ke keranjang + pasang cache hold. Multi-select denah: SATU form jadwal
     * berlaku untuk semua ruangan terpilih (?antrian=id2,id3) — semua masuk sekaligus,
     * atomic (kalau satu ruangan bermasalah, tidak ada yang ditambahkan).
     */
    public function tambahKeranjang(TambahKeranjangRequest $request): RedirectResponse
    {
        $tarifUtama = $request->tarif();
        $data = $request->validated();
        $sessionId = $request->session()->getId();

        // Kumpulkan tarif semua ruangan: utama + antrian (jenis sewa sama dengan ruangan utama).
        $daftarTarif = collect([$tarifUtama]);
        $idsLain = array_values(array_filter(explode(',', (string) $request->input('antrian'))));
        foreach ($idsLain as $idFasilitas) {
            $t = TarifSewa::where('status_aktif', StatusAktif::Aktif->value)
                ->where('id_fasilitas', $idFasilitas)
                ->where('id_jenis_sewa', $tarifUtama->id_jenis_sewa)
                ->whereHas('fasilitas', fn ($q) => $q->where('status_aktif', StatusAktif::Aktif->value))
                ->with('fasilitas', 'jenisSewa')
                ->first();

            if (! $t) {
                return back()->withInput()->withErrors([
                    'antrian' => 'Salah satu ruangan yang dipilih sudah tidak tersedia untuk jenis sewa ini. Silakan pilih ulang di denah.',
                ]);
            }
            $daftarTarif->push($t);
        }

        // Bangun & periksa SEMUA item dulu — kapasitas & ketersediaan per ruangan.
        $items = [];
        $galat = [];
        foreach ($daftarTarif as $t) {
            $item = $this->cart->buildItem($t, $data);
            $slot = [
                'tanggal_mulai'   => $item['tanggal_mulai'],
                'tanggal_selesai' => $item['tanggal_selesai'],
                'jam_mulai'       => $item['jam_mulai'],
                'jam_selesai'     => $item['jam_selesai'],
            ];

            if ((int) $data['jumlah_pengguna'] > $t->fasilitas->kapasitas) {
                $galat[] = "{$t->fasilitas->nama_fasilitas}: kapasitas maksimal {$t->fasilitas->kapasitas} orang.";
            } elseif (! $this->availability->slotAvailable($item['id_fasilitas'], $slot, $sessionId)) {
                $galat[] = "{$t->fasilitas->nama_fasilitas}: jadwal tersebut baru saja terisi, pilih jadwal lain.";
            } else {
                $items[] = $item;
            }
        }

        if ($galat !== []) {
            return back()->withInput()->withErrors($galat);
        }

        foreach ($items as $item) {
            $this->availability->putHold($item, $sessionId);
            $this->cart->add($item);
        }

        $n = count($items);

        return redirect()->route('reservasi.checkout.form')->with('success', $n > 1
            ? "{$n} ruangan ditambahkan ke keranjang dengan jadwal yang sama. 🎉"
            : "\"{$items[0]['nama_fasilitas']}\" ditambahkan ke keranjang.");
    }

    /** Hapus item dari keranjang + lepas cache hold. */
    public function hapusKeranjang(int $index): RedirectResponse
    {
        $removed = $this->cart->remove($index);
        if ($removed) {
            $this->availability->releaseHold($removed);
        }

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    /** Form data diri + ringkasan keranjang. */
    public function checkoutForm(): View|RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('reservasi.index')->with('error', 'Keranjang masih kosong.');
        }

        return view('reservasi.checkout', [
            'items'    => $this->cart->items(),
            'total'    => $this->cart->total(),
            'hasBulan' => $this->cart->hasBulan(),
        ]);
    }

    /** Proses submit checkout: buat Pemesan + Reservasi per item (+ dokumen untuk Bulan). */
    public function checkout(CheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('reservasi.index')->with('error', 'Keranjang masih kosong.');
        }

        $data = $request->validated();
        $items = $this->cart->items();

        $hasil = DB::transaction(function () use ($request, $data, $items) {
            // 1. Cari/atau buat Pemesan berdasarkan email (UPDATE bila sudah ada, tidak duplikat).
            $pemesan = Pemesan::updateOrCreate(
                ['email' => $data['email']],
                [
                    'nama_lengkap' => $data['nama_lengkap'],
                    'alamat'       => $data['alamat'],
                    'usia'         => $data['usia'],
                    'pekerjaan'    => $data['pekerjaan'],
                    'no_telepon'   => $data['no_telepon'],
                ],
            );

            // 2. SATU kode simpel per checkout (mis. RSV-7K3M) untuk seluruh ruangan.
            //    Baris ke-2 dst diberi suffix internal (-2, ...) karena kode_reservasi UNIQUE per baris,
            //    tapi kode yang ditampilkan ke pemesan tetap satu: kode dasar (= kode_transaksi).
            $kodeDasar = $this->generateKode();
            $multi = count($items) > 1;
            $kodeReservasi = [];

            foreach ($items as $index => $item) {
                $reservasi = Reservasi::create([
                    'id_pemesan'       => $pemesan->id_pemesan,
                    'id_tarif_sewa'    => $item['id_tarif_sewa'],
                    'id_admin'         => null,
                    'kode_reservasi'   => $multi ? $kodeDasar.'-'.($index + 1) : $kodeDasar,
                    'kode_transaksi'   => $kodeDasar,
                    'tanggal_mulai'    => $item['tanggal_mulai'],
                    'tanggal_selesai'  => $item['tanggal_selesai'],
                    'jam_mulai'        => $item['jam_mulai'],
                    'jam_selesai'      => $item['jam_selesai'],
                    'durasi'           => $item['durasi'],
                    'jumlah_pengguna'  => $item['jumlah_pengguna'],
                    'keperluan'        => $item['keperluan'],
                    'harga_satuan'     => $item['harga_satuan'],
                    'total_biaya'      => $item['total_biaya'],
                    'status_reservasi' => StatusReservasi::Menunggu,
                    // Alur utama Stage 2 langsung pending_approval saat submit.
                    // (lock_status='temporary_hold' disediakan untuk pengembangan lanjutan — lihat spec.)
                    'lock_status'      => LockStatus::PendingApproval,
                    'lock_expires_at'  => null,
                    'tanggal_diproses' => null,
                ]);

                // 3. Item Bulan wajib punya dokumen persyaratan (sudah divalidasi CheckoutRequest).
                //    Satu lampiran (dokumen[]) berlaku untuk SEMUA ruangan Bulan pada transaksi ini,
                //    jadi disalin ke tiap baris Reservasi Bulan (skema id_reservasi tetap per-baris).
                if ($item['satuan'] === SatuanSewa::Bulan->value) {
                    $this->simpanDokumen($request, $reservasi);
                }

                $kodeReservasi[] = $reservasi->kode_reservasi;
            }

            return ['kode_transaksi' => $kodeDasar, 'kode_reservasi' => $kodeReservasi];
        });

        // 4 & 5. Setelah commit: lepas semua cache hold, kosongkan keranjang.
        foreach ($items as $item) {
            $this->availability->releaseHold($item);
        }
        $this->cart->clear();

        // 6. Tampilkan halaman notifikasi sukses.
        return redirect()->route('reservasi.sukses')->with('checkout', $hasil);
    }

    /** Halaman notifikasi sukses (kode_transaksi + daftar kode_reservasi). */
    public function sukses(): View|RedirectResponse
    {
        $checkout = session('checkout');
        if (! $checkout) {
            return redirect()->route('reservasi.index');
        }

        return view('reservasi.sukses', ['checkout' => $checkout]);
    }

    /** Pembatalan mandiri oleh pemesan. */
    public function batalkan(Request $request, string $kode_reservasi): RedirectResponse
    {
        $reservasi = Reservasi::where('kode_reservasi', $kode_reservasi)->firstOrFail();

        // Hanya boleh dibatalkan selama masih proses verifikasi (Menunggu) dan belum lewat tanggal.
        $bolehStatus = $reservasi->status_reservasi === StatusReservasi::Menunggu;
        $belumLewat = $reservasi->tanggal_mulai->startOfDay()->gte(Carbon::today());

        if (! $bolehStatus || ! $belumLewat) {
            return back()->with('error', 'Reservasi hanya dapat dibatalkan selama masih diverifikasi.');
        }

        // Observer otomatis mencatat Riwayat_Status (id_admin null = dibatalkan pemesan).
        $reservasi->keteranganRiwayat = 'Dibatalkan oleh pemesan';
        $reservasi->status_reservasi = StatusReservasi::Dibatalkan;
        $reservasi->save();

        return back()->with('success', "Reservasi {$reservasi->kode_reservasi} berhasil dibatalkan.");
    }

    // ------------------------------------------------------------------
    // Helper privat
    // ------------------------------------------------------------------

    private function pastikanKategoriAda(string $kategori): void
    {
        $ada = Fasilitas::where('status_aktif', StatusAktif::Aktif->value)
            ->where('kategori_fasilitas', $kategori)
            ->exists();
        abort_unless($ada, 404, 'Kategori fasilitas tidak ditemukan.');
    }

    /** Scope query fasilitas aktif untuk kategori (dan opsional punya tarif jenis tertentu). */
    private function fasilitasScope($query, string $kategori, ?int $idJenis)
    {
        return $query
            ->where('status_aktif', StatusAktif::Aktif->value)
            ->where('kategori_fasilitas', $kategori)
            ->when($idJenis, fn ($q) => $q->whereHas('tarifSewa', fn ($t) => $t
                ->where('status_aktif', StatusAktif::Aktif->value)
                ->where('id_jenis_sewa', $idJenis)));
    }

    private function jenisDipilih(Request $request): ?JenisSewa
    {
        $id = $request->integer('jenis');

        return $id ? JenisSewa::find($id) : null;
    }

    /** Bangun slot untuk pengecekan warna dari query, dengan default aman agar warna selalu tampil. */
    private function slotDariRequest(Request $request, ?SatuanSewa $satuan): array
    {
        $today = Carbon::today()->toDateString();

        if ($satuan === SatuanSewa::Jam) {
            return [
                'tanggal_mulai'   => $request->input('tanggal_mulai', $today),
                'tanggal_selesai' => $request->input('tanggal_mulai', $today),
                'jam_mulai'       => $request->input('jam_mulai', '08:00'),
                'jam_selesai'     => $request->input('jam_selesai', '16:00'), // jam operasional 08.00–16.00
            ];
        }

        $mulai = $request->input('tanggal_mulai', $today);
        $defaultSelesai = $satuan === SatuanSewa::Bulan
            ? Carbon::parse($mulai)->addMonths(3)->toDateString()
            : $mulai;

        return [
            'tanggal_mulai'   => $mulai,
            'tanggal_selesai' => $request->input('tanggal_selesai', $defaultSelesai),
            'jam_mulai'       => null,
            'jam_selesai'     => null,
        ];
    }

    /**
     * Kode reservasi pendek & mudah dibaca: RSV- + 4 karakter tanpa huruf/angka membingungkan
     * (tanpa O/0, I/L/1). Unik terhadap kode dasar maupun kode bersufiks (-1, -2, ...).
     */
    private function generateKode(): string
    {
        $charset = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

        do {
            $kode = 'RSV-'.substr(str_shuffle($charset), 0, 4);
        } while (
            Reservasi::where('kode_transaksi', $kode)
                ->orWhere('kode_reservasi', 'like', $kode.'%')
                ->exists()
        );

        return $kode;
    }

    private function simpanDokumen(Request $request, Reservasi $reservasi): void
    {
        $files = $request->file('dokumen', []);
        $files = is_array($files) ? $files : [$files];

        foreach (array_filter($files) as $file) {
            $path = $file->store('dokumen', 'public');
            DokumenPersyaratan::create([
                'id_reservasi'      => $reservasi->id_reservasi,
                'jenis_dokumen'     => 'Persyaratan Sewa Bulanan',
                'nama_file'         => $file->getClientOriginalName(),
                'lokasi_file'       => $path,
                'tanggal_upload'    => now(),
                'status_verifikasi' => StatusVerifikasi::Menunggu,
            ]);
        }
    }
}
