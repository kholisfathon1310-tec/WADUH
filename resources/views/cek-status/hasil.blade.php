@extends('layouts.reservasi')
@section('title', 'Hasil Cek Status')

@php
    $badge = ['Menunggu' => 'warning', 'Disetujui' => 'success', 'Ditolak' => 'danger', 'Selesai' => 'primary', 'Dibatalkan' => 'dark', 'Kadaluwarsa' => 'info'];
@endphp

@section('content')
<style>
    /* Header hasil — segaris dengan hero halaman form */
    .rs-head { background:linear-gradient(118deg,#0d2a3a 0%,#176b87 45%,#1b9487 100%); border-radius:1.6rem; position:relative; overflow:hidden; box-shadow:0 24px 50px -20px rgba(13,42,58,.45); }
    .rs-head::before { content:''; position:absolute; width:300px; height:300px; border-radius:50%; background:rgba(255,255,255,.07); top:-140px; right:-70px; }
    .rs-head::after { content:''; position:absolute; inset:0; background:radial-gradient(28rem 16rem at 10% 120%, rgba(36,170,154,.28), transparent 60%); }
    .rs-head .inner { position:relative; z-index:1; }
    .rs-kode { font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:.08em; }
    .btn-salin { background:rgba(255,255,255,.14); border:1px solid rgba(255,255,255,.25); color:#fff; border-radius:.7rem; font-size:.8rem; font-weight:700; transition:background .15s ease; }
    .btn-salin:hover { background:rgba(255,255,255,.24); color:#fff; }
    .rs-stat { background:rgba(255,255,255,.13); border:1px solid rgba(255,255,255,.16); border-radius:1rem; min-width:8.5rem; backdrop-filter:blur(6px); }
    .rs-stat .v { font-size:1.15rem; font-weight:800; }
    .rs-stat .k { font-size:.72rem; color:#c9e8ec; }

    /* Tracker status */
    .tracker { display:flex; align-items:flex-start; gap:0; margin:.9rem 0 .25rem; }
    .tstep { flex:1 1 0; text-align:center; position:relative; min-width:70px; }
    .tstep .tdot { width:2.1rem; height:2.1rem; border-radius:50%; display:grid; place-items:center; margin:0 auto .35rem; background:#e6edf3; color:#8a97a5; font-size:.95rem; position:relative; z-index:1; border:3px solid #fff; box-shadow:0 0 0 1px #e0e8ef; }
    .tstep .tlabel { font-size:.72rem; font-weight:700; color:#8a97a5; }
    .tstep::before { content:''; position:absolute; top:1.05rem; left:-50%; width:100%; height:3px; background:#e2e9f0; z-index:0; }
    .tstep:first-child::before { display:none; }
    .tstep.done .tdot { background:var(--teal); color:#fff; box-shadow:0 4px 12px rgba(36,170,154,.4); }
    .tstep.done .tlabel { color:var(--teal); }
    .tstep.done::before { background:var(--teal); }
    .tstep.now .tdot { background:var(--primary); color:#fff; box-shadow:0 4px 14px rgba(23,107,135,.45); animation:pulse 1.8s infinite; }
    .tstep.now .tlabel { color:var(--primary); }
    .tstep.now::before { background:var(--teal); }
    .tstep.bad .tdot { background:#d95757; color:#fff; box-shadow:0 4px 12px rgba(217,87,87,.4); }
    .tstep.bad .tlabel { color:#d95757; }
    .tstep.bad::before { background:#d95757; }
    .tstep.off .tdot { background:#3a4653; color:#fff; }
    .tstep.off .tlabel { color:#3a4653; }
    .tstep.off::before { background:#3a4653; }
    @keyframes pulse { 0%,100% { transform:scale(1) } 50% { transform:scale(1.12) } }

    .res-card { overflow:hidden; transition:box-shadow .2s ease; }
    .res-card:hover { box-shadow:0 16px 34px -12px rgba(21,36,59,.14); }
    .res-card .thumb { width:100%; height:100%; min-height:170px; object-fit:cover; }

    /* Galeri foto ruangan — carousel (kalau >1 foto) + zoom, sama seperti halaman detail ruangan */
    .rs-carousel, .rs-carousel .carousel-inner, .rs-carousel .carousel-item { height:100%; min-height:170px; }
    .rs-carousel .carousel-indicators { margin-bottom:.5rem; }
    .rs-carousel .carousel-indicators [data-bs-target] { width:.45rem; height:.45rem; border-radius:50%; background:#fff; opacity:.6; }
    .rs-carousel .carousel-indicators .active { opacity:1; }
    .rs-carousel .carousel-control-prev, .rs-carousel .carousel-control-next { width:2.4rem; opacity:0; transition:opacity .15s ease; }
    .rs-carousel:hover .carousel-control-prev, .rs-carousel:hover .carousel-control-next { opacity:1; }

    .fp-zoomable { cursor:zoom-in; transition:filter .15s ease; }
    .fp-zoomable:hover { filter:brightness(.92); }
    .fp-lightbox {
        position:fixed; inset:0; z-index:2000; background:rgba(8,15,25,.9);
        display:none; align-items:center; justify-content:center; padding:2.5rem; cursor:zoom-out;
    }
    .fp-lightbox.show { display:flex; }
    .fp-lightbox img { max-width:100%; max-height:100%; border-radius:.9rem; box-shadow:0 24px 60px rgba(0,0,0,.5); cursor:default; }
    .fp-lightbox .fp-lightbox-close {
        position:absolute; top:1.2rem; right:1.4rem; width:2.6rem; height:2.6rem; border-radius:50%;
        border:none; background:rgba(255,255,255,.15); color:#fff; font-size:1.3rem;
        display:grid; place-items:center; cursor:pointer; transition:background .15s ease;
    }
    .fp-lightbox .fp-lightbox-close:hover { background:rgba(255,255,255,.28); }

    /* Pemberitahuan alasan (ditolak / dibatalkan) */
    .rs-alasan { border-radius:.8rem; padding:.7rem .9rem; font-size:.85rem; display:flex; gap:.6rem; align-items:flex-start; }
    .rs-alasan.tolak { background:#fdf1f1; border:1px solid #f0c9c9; color:#7c3a3a; }
    .rs-alasan.batal { background:#eef1f5; border:1px solid #d8dfe8; color:#4a5568; }

    /* Dokumen persyaratan */
    .dok-chip { display:inline-flex; align-items:center; gap:.4rem; font-size:.76rem; font-weight:600; padding:.35rem .7rem; border-radius:2rem; border:1px solid; }
    .dok-chip.menunggu { background:#fff8e6; border-color:#f0dfae; color:#9a6b00; }
    .dok-chip.valid { background:#e2f7ef; border-color:#bce8d6; color:#0d8a5f; }
    .dok-chip.tidak-valid { background:#fde4e4; border-color:#f3c2c2; color:#c02929; }

    /* Riwayat status (timeline) */
    .btn-riwayat { font-size:.78rem; font-weight:700; color:var(--primary); background:none; border:0; padding:0; }
    .btn-riwayat:hover { color:var(--primary-dark); }
    .btn-riwayat[aria-expanded="true"] .bi-chevron-down { transform:rotate(180deg); }
    .btn-riwayat .bi-chevron-down { transition:transform .2s; display:inline-block; }
    .timeline { list-style:none; margin:.75rem 0 0; padding:0 0 0 1.1rem; border-left:2px solid #e2e9f0; }
    .timeline li { position:relative; padding:0 0 .85rem .9rem; }
    .timeline li:last-child { padding-bottom:.1rem; }
    .timeline li::before { content:''; position:absolute; left:-1.48rem; top:.28rem; width:.72rem; height:.72rem; border-radius:50%; background:#cdd8e2; border:2px solid #fff; box-shadow:0 0 0 1px #cdd8e2; }
    .timeline li.hijau::before { background:var(--teal); box-shadow:0 0 0 1px var(--teal); }
    .timeline li.merah::before { background:#d95757; box-shadow:0 0 0 1px #d95757; }
    .timeline .tgl { font-size:.72rem; color:var(--muted); }
</style>

    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('cek-status.form') }}">Cek Status</a></li>
        <li class="breadcrumb-item active">{{ $kode }}</li>
    </ol></nav>

    @if ($reservasi->isEmpty())
        {{-- ===== Tidak ditemukan ===== --}}
        <div class="xcard p-5 text-center mx-auto" style="max-width:560px;">
            <div class="icon-tile mx-auto mb-3" style="background:#fff4d6; color:#9a6b00;"><i class="bi bi-search"></i></div>
            <h1 class="h5 mb-2">Kode <span style="color:var(--primary)">{{ $kode }}</span> tidak ditemukan</h1>
            <p class="text-muted small mb-4">Pastikan kode diketik lengkap beserta awalannya, yaitu <strong>TRX-</strong> untuk satu pemesanan utuh atau <strong>RSV-</strong> untuk satu ruangan. Huruf besar dan huruf kecil tidak berpengaruh, namun tanda hubung harus ikut ditulis.</p>
            <div class="d-flex justify-content-center gap-2 flex-wrap">
                <a href="{{ route('cek-status.form') }}" class="btn btn-brand px-4"><i class="bi bi-arrow-counterclockwise me-1"></i>Coba Lagi</a>
                <a href="{{ route('reservasi.index') }}" class="btn btn-brand-outline px-4">Buat Reservasi Baru</a>
            </div>
        </div>
    @else
        @php
            $pemesan = $reservasi->first()->pemesan;
            $totalSemua = $reservasi->sum('total_biaya');
            $diajukanPada = $reservasi->min('created_at');
        @endphp

        {{-- ===== Header ringkasan ===== --}}
        <div class="rs-head p-4 p-md-5 mb-4 text-white" data-reveal>
            <div class="inner d-flex flex-wrap justify-content-between align-items-end gap-3">
                <div>
                    <p class="eyebrow-sm mb-2" style="color:#a9e6dd">Hasil Pencarian</p>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h1 class="h3 fw-bold mb-0 rs-kode">{{ $kode }}</h1>
                        <button type="button" class="btn btn-sm btn-salin" data-salin="{{ $kode }}">
                            <i class="bi bi-clipboard me-1"></i>Salin
                        </button>
                    </div>
                    <p class="mb-0 mt-2 small" style="color:#d3ecf1">
                        <i class="bi bi-person me-1"></i>{{ $pemesan->nama_lengkap }}
                        @if ($diajukanPada)
                            <span class="mx-1">·</span><i class="bi bi-calendar-plus me-1"></i>diajukan {{ $diajukanPada->translatedFormat('d M Y H:i') }}
                        @endif
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <div class="rs-stat text-center px-3 py-2">
                        <div class="v">{{ $reservasi->count() }}</div>
                        <div class="k">Ruang Dipesan</div>
                    </div>
                    <div class="rs-stat text-center px-3 py-2">
                        <div class="v">Rp {{ number_format($totalSemua, 0, ',', '.') }}</div>
                        <div class="k">Total Biaya</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column gap-3">
        @foreach ($reservasi as $r)
            @php
                $status = $r->status_reservasi->value;
                $meta = \App\Support\KategoriMeta::get($r->tarifSewa->fasilitas->kategori_fasilitas);
                // Batal hanya boleh selama proses verifikasi (Menunggu) & tanggal belum lewat.
                $bolehBatal = $status === 'Menunggu' && $r->tanggal_mulai->startOfDay()->gte(\Illuminate\Support\Carbon::today());
                // Label yang ditampilkan ke pemesan: "Menunggu" → "Diverifikasi".
                $labelStatus = $status === 'Menunggu' ? 'Diverifikasi' : $status;

                // Tracker 3 langkah: Diajukan → Diverifikasi → hasil akhir (tanpa langkah "Selesai").
                $steps = match ($status) {
                    'Ditolak'    => [['Diajukan','done','send'], ['Diverifikasi','done','hourglass-split'], ['Ditolak','bad','x-lg']],
                    'Dibatalkan' => [['Diajukan','done','send'], ['Diverifikasi','done','hourglass-split'], ['Dibatalkan','off','slash-circle']],
                    'Kadaluwarsa' => [['Diajukan','done','send'], ['Diverifikasi','bad','hourglass-split'], ['Kadaluwarsa','bad','x-lg']],
                    'Menunggu'   => [['Diajukan','done','send'], ['Diverifikasi','now','hourglass-split'], ['Disetujui','','check-lg']],
                    default      => [['Diajukan','done','send'], ['Diverifikasi','done','hourglass-split'], ['Disetujui','done','check-lg']], // Disetujui / Selesai
                };

                // Alasan penolakan/pembatalan/kadaluwarsa diambil dari riwayat status terakhir yang relevan.
                $alasan = in_array($status, ['Ditolak', 'Dibatalkan', 'Kadaluwarsa'], true)
                    ? $r->riwayatStatus->last(fn ($h) => $h->status_baru->value === $status)?->keterangan
                    : null;

                $fotoList = $r->tarifSewa->fasilitas->fotoUrls();
            @endphp
            <div class="xcard res-card" data-reveal>
                <div class="row g-0">
                    <div class="col-md-3 d-none d-md-block position-relative">
                        @if (count($fotoList) > 1)
                            <div id="rsCarousel{{ $r->id_reservasi }}" class="carousel slide rs-carousel">
                                <div class="carousel-inner">
                                    @foreach ($fotoList as $i => $src)
                                        <div class="carousel-item @if ($i === 0) active @endif">
                                            <img src="{{ $src }}" class="thumb fp-zoomable" data-zoom-src="{{ $src }}" alt="{{ $r->tarifSewa->fasilitas->nama_fasilitas }} — foto {{ $i + 1 }}">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#rsCarousel{{ $r->id_reservasi }}" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Sebelumnya</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#rsCarousel{{ $r->id_reservasi }}" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Berikutnya</span>
                                </button>
                                <div class="carousel-indicators">
                                    @foreach ($fotoList as $i => $src)
                                        <button type="button" data-bs-target="#rsCarousel{{ $r->id_reservasi }}" data-bs-slide-to="{{ $i }}" @if ($i === 0) class="active" @endif aria-label="Foto {{ $i + 1 }}"></button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <img src="{{ $fotoList[0] }}" class="thumb fp-zoomable" data-zoom-src="{{ $fotoList[0] }}" alt="{{ $r->tarifSewa->fasilitas->nama_fasilitas }}">
                        @endif
                        <span class="avail position-absolute top-0 start-0 m-2" style="background:rgba(255,255,255,.92); color:{{ $meta['warna'] }}; z-index:5; pointer-events:none;">
                            <i class="bi {{ $meta['ikon'] }}"></i> {{ $r->tarifSewa->fasilitas->kategori_fasilitas }}
                        </span>
                        @if ($r->tarifSewa->fasilitas->lantai)
                            <span class="avail position-absolute bottom-0 start-0 m-2" style="background:rgba(21,36,59,.75); color:#fff; z-index:5; pointer-events:none;">
                                <i class="bi bi-layers"></i> Lantai {{ $r->tarifSewa->fasilitas->lantai->nomor_lantai }}
                            </span>
                        @endif
                    </div>
                    <div class="col-md-9">
                        <div class="p-3 p-md-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div>
                                    <h2 class="h6 mb-1">{{ $r->tarifSewa->fasilitas->nama_fasilitas }}
                                        <span class="badge text-bg-{{ $badge[$status] ?? 'secondary' }}">{{ $labelStatus }}</span>
                                    </h2>
                                    <div class="small text-muted">
                                        <span class="me-2"><i class="bi bi-upc"></i> {{ $r->kode_reservasi }}</span>
                                        <span class="me-2"><i class="bi bi-receipt"></i> {{ $r->kode_transaksi }}</span>
                                        @if ($r->keperluan)<span><i class="bi bi-chat-left-text"></i> {{ \Illuminate\Support\Str::limit($r->keperluan, 60) }}</span>@endif
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-5" style="color:var(--primary)">Rp {{ number_format($r->total_biaya, 0, ',', '.') }}</div>
                                    <small class="text-muted">{{ $r->durasi }} {{ strtolower($r->tarifSewa->jenisSewa->satuan->value) }} × Rp {{ number_format($r->harga_satuan, 0, ',', '.') }} · {{ $r->jumlah_pengguna }} org</small>
                                </div>
                            </div>

                            {{-- Tracker status --}}
                            <div class="tracker">
                                @foreach ($steps as [$lbl, $state, $ic])
                                    <div class="tstep {{ $state }}">
                                        <div class="tdot"><i class="bi bi-{{ $ic }}"></i></div>
                                        <div class="tlabel">{{ $lbl }}</div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Alasan ditolak/dibatalkan --}}
                            @if ($status === 'Ditolak')
                                <div class="rs-alasan tolak mt-2">
                                    <i class="bi bi-exclamation-octagon flex-shrink-0 mt-1"></i>
                                    <div><strong>Alasan penolakan:</strong> {{ $alasan ?: 'Tidak dicantumkan. Silakan hubungi pengelola gedung untuk informasi lebih lanjut.' }}</div>
                                </div>
                            @elseif ($status === 'Dibatalkan')
                                <div class="rs-alasan batal mt-2">
                                    <i class="bi bi-slash-circle flex-shrink-0 mt-1"></i>
                                    <div>Reservasi ini telah dibatalkan{{ $alasan ? '. '.$alasan : ' oleh pemesan.' }}</div>
                                </div>
                            @elseif ($status === 'Kadaluwarsa')
                                <div class="rs-alasan tolak mt-2">
                                    <i class="bi bi-clock-history flex-shrink-0 mt-1"></i>
                                    <div>Reservasi ini kadaluwarsa karena belum diproses admin hingga melewati waktu penggunaan yang diajukan.</div>
                                </div>
                            @elseif ($status === 'Disetujui' && $r->tanggal_diproses)
                                <div class="small mt-2" style="color:#0d8a5f"><i class="bi bi-patch-check-fill me-1"></i>Disetujui pada {{ $r->tanggal_diproses->translatedFormat('d M Y H:i') }}. Tunjukkan kode reservasi Anda saat datang.</div>
                            @endif

                            {{-- Jadwal --}}
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <span class="badge text-bg-light border py-2"><i class="bi bi-calendar3 me-1"></i>{{ $r->tanggal_mulai->translatedFormat('d M Y') }}@if($r->tanggal_selesai->ne($r->tanggal_mulai)) → {{ $r->tanggal_selesai->translatedFormat('d M Y') }}@endif</span>
                                @if($r->jam_mulai)<span class="badge text-bg-light border py-2"><i class="bi bi-clock me-1"></i>{{ \Illuminate\Support\Str::substr($r->jam_mulai,0,5) }}–{{ \Illuminate\Support\Str::substr($r->jam_selesai,0,5) }} WIB</span>@endif
                                <span class="badge text-bg-light border py-2">Per {{ $r->tarifSewa->jenisSewa->satuan->value }}</span>
                            </div>

                            {{-- Dokumen persyaratan --}}
                            @if ($r->dokumenPersyaratan->isNotEmpty())
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                                    <span class="small text-muted fw-semibold"><i class="bi bi-folder2-open me-1"></i>Dokumen:</span>
                                    @foreach ($r->dokumenPersyaratan as $dok)
                                        @php
                                            $vs = $dok->status_verifikasi->value; // Menunggu | Valid | Tidak Valid
                                            $vClass = ['Menunggu' => 'menunggu', 'Valid' => 'valid', 'Tidak Valid' => 'tidak-valid'][$vs] ?? 'menunggu';
                                            $vIkon = ['Menunggu' => 'bi-hourglass-split', 'Valid' => 'bi-check-circle-fill', 'Tidak Valid' => 'bi-x-circle-fill'][$vs] ?? 'bi-hourglass-split';
                                        @endphp
                                        <span class="dok-chip {{ $vClass }}" title="{{ $dok->nama_file }} ({{ $vs }})">
                                            <i class="bi {{ $vIkon }}"></i>{{ $dok->jenis_dokumen }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Riwayat + aksi --}}
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                                @if ($r->riwayatStatus->isNotEmpty())
                                    <button class="btn-riwayat" type="button" data-bs-toggle="collapse" data-bs-target="#riwayat-{{ $r->id_reservasi }}" aria-expanded="false">
                                        <i class="bi bi-clock-history me-1"></i>Riwayat Status <i class="bi bi-chevron-down ms-1"></i>
                                    </button>
                                @else
                                    <span></span>
                                @endif
                                <div class="d-flex gap-2">
                                    <a href="{{ route('cek-status.bukti-reservasi', $r->kode_reservasi) }}" class="btn btn-sm btn-brand-outline">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i>Unduh Bukti Reservasi
                                    </a>
                                    @if ($bolehBatal)
                                        <form method="POST" action="{{ route('reservasi.batalkan', $r->kode_reservasi) }}"
                                              data-confirm="Reservasi {{ $r->kode_reservasi }} akan dibatalkan dan tidak dapat dikembalikan."
                                              data-confirm-title="Batalkan reservasi ini?" data-icon="warning"
                                              data-confirm-text="Ya, batalkan" data-confirm-color="#d95757">
                                            @csrf
                                            <input type="hidden" name="kode" value="{{ $kode }}">
                                            <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Batalkan</button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            @if ($r->riwayatStatus->isNotEmpty())
                                <div class="collapse" id="riwayat-{{ $r->id_reservasi }}">
                                    <ul class="timeline">
                                        <li class="hijau">
                                            <div class="fw-semibold small">Reservasi diajukan</div>
                                            <div class="tgl">{{ $r->created_at?->translatedFormat('d M Y H:i') }}</div>
                                        </li>
                                        @foreach ($r->riwayatStatus as $h)
                                            @php
                                                $sb = $h->status_baru->value;
                                                $liClass = $sb === 'Ditolak' ? 'merah' : (in_array($sb, ['Disetujui', 'Selesai'], true) ? 'hijau' : '');
                                            @endphp
                                            <li class="{{ $liClass }}">
                                                <div class="fw-semibold small">{{ $sb === 'Menunggu' ? 'Diverifikasi' : $sb }}</div>
                                                @if ($h->keterangan)<div class="small text-muted">{{ $h->keterangan }}</div>@endif
                                                <div class="tgl">{{ $h->tanggal_perubahan?->translatedFormat('d M Y H:i') }}</div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('cek-status.form') }}" class="btn btn-brand-outline btn-sm px-4"><i class="bi bi-search me-1"></i>Cek Kode Lain</a>
        </div>

        {{-- Lightbox foto — sama seperti halaman detail ruangan, dipakai bersama semua kartu di atas --}}
        <div class="fp-lightbox" id="fpLightbox">
            <button type="button" class="fp-lightbox-close" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
            <img src="" alt="" id="fpLightboxImg">
        </div>
        <script>
            (function () {
                const overlay = document.getElementById('fpLightbox');
                const img = document.getElementById('fpLightboxImg');
                if (!overlay || !img) return;

                const buka = (src, alt) => {
                    img.src = src;
                    img.alt = alt || '';
                    overlay.classList.add('show');
                    document.body.style.overflow = 'hidden';
                };
                const tutup = () => {
                    overlay.classList.remove('show');
                    document.body.style.overflow = '';
                };

                document.querySelectorAll('.fp-zoomable').forEach((el) => {
                    el.addEventListener('click', () => buka(el.dataset.zoomSrc, el.alt));
                });
                overlay.addEventListener('click', (e) => { if (e.target === overlay) tutup(); });
                overlay.querySelector('.fp-lightbox-close').addEventListener('click', tutup);
                document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutup(); });
            })();
        </script>
    @endif
@endsection
