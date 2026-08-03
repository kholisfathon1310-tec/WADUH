@extends('admin.layouts.app')
@section('title', 'Dashboard')

@php
    $me = Auth::guard('admin')->user();

    $cards = [
        ['Menunggu', $statistik['menunggu'], 'linear-gradient(135deg,#d9a521,#e5b94e)', 'hourglass-split'],
        ['Disetujui', $statistik['disetujui'], 'linear-gradient(135deg,#0d8a5f,#25b47e)', 'check-circle'],
        ['Ditolak', $statistik['ditolak'], 'linear-gradient(135deg,#b23030,#d95757)', 'x-circle'],
        ['Dibatalkan', $statistik['dibatalkan'], 'linear-gradient(135deg,#3a4653,#5d6b7a)', 'slash-circle'],
    ];

    // Donut distribusi status (CSS conic-gradient)
    $total = max(1, $statistik['total']);
    $seg = [
        ['Menunggu', $statistik['menunggu'], '#e5b94e'],
        ['Disetujui', $statistik['disetujui'], '#25b47e'],
        ['Ditolak', $statistik['ditolak'], '#d95757'],
        ['Dibatalkan', $statistik['dibatalkan'], '#5d6b7a'],
    ];
    $stops = []; $acc = 0;
    foreach ($seg as [$lbl, $n, $c]) {
        $from = $acc; $acc += $n / $total * 100;
        if ($n > 0) $stops[] = "$c {$from}% {$acc}%";
    }
    $donut = $stops ? 'conic-gradient('.implode(', ', $stops).')' : 'conic-gradient(#e4ebf2 0% 100%)';

    $maxKat = max(1, (int) collect($fasilitasPerKategori)->max());
    $warnaKat = ['Working Space' => '#2f7fd1', 'Co-Working Space' => '#7c5cd6', 'Convention Hall' => '#d6527c'];
    $ikonKat = ['Working Space' => 'bi-briefcase', 'Co-Working Space' => 'bi-people', 'Convention Hall' => 'bi-bank'];
@endphp

@section('content')
<div class="dash">
    {{-- ===== Welcome banner ===== --}}
    <div class="dash-hero" data-reveal>
        <div class="dash-hero-mesh"></div>
        <div class="dash-hero-grid"></div>
        <div class="position-relative d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="dash-hero-eyebrow"><i class="bi bi-stars me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</span>
                <h2 class="dash-hero-title">Halo, {{ $me?->nama_admin }} 👋</h2>
                <p class="dash-hero-sub">
                    @if ($statistik['menunggu'] > 0)
                        Ada <strong>{{ $statistik['menunggu'] }} reservasi menunggu</strong> persetujuanmu hari ini.
                    @else
                        Tidak ada antrian persetujuan — semua reservasi sudah tertangani. 🎉
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reservasi.index', ['status' => 'Menunggu']) }}" class="dash-btn dash-btn-light"><i class="bi bi-hourglass-split me-1"></i>Proses Antrian</a>
                <a href="{{ route('admin.monitoring') }}" class="dash-btn dash-btn-ghost"><i class="bi bi-grid-3x3-gap me-1"></i>Monitoring</a>
            </div>
        </div>
    </div>

    {{-- ===== Stat tiles ===== --}}
    <div class="dash-tiles">
        @foreach ($cards as $i => [$label, $nilai, $grad, $ikon])
            <div class="dash-tile" data-reveal style="--tile-i:{{ $i }}">
                <span class="dash-tile-ic" style="background:{{ $grad }}"><i class="bi bi-{{ $ikon }}"></i></span>
                <span class="dash-tile-v">{{ $nilai }}</span>
                <span class="dash-tile-l">{{ $label }}</span>
            </div>
        @endforeach
    </div>

    {{-- ===== Bento grid ===== --}}
    <div class="dash-bento">
        {{-- Kiri: donut + fasilitas aktif, ditumpuk --}}
        <div class="dash-col-left">
            <div class="dash-card" data-reveal>
                <div class="dash-card-head"><span><i class="bi bi-pie-chart-fill me-2"></i>Distribusi Status</span><span class="dash-pill">{{ $statistik['total'] }} total</span></div>
                <div class="p-4 text-center">
                    <div class="dash-donut mx-auto" style="background:{{ $donut }}">
                        <div class="dash-donut-hole">
                            <div class="dash-donut-v">{{ $statistik['total'] }}</div>
                            <small class="text-muted">Reservasi</small>
                        </div>
                    </div>
                    <div class="dash-legend">
                        @foreach ($seg as [$lbl, $n, $c])
                            <div class="dash-legend-item">
                                <span class="dot" style="background:{{ $c }}"></span>
                                <span class="lbl">{{ $lbl }}</span>
                                <span class="n">{{ $n }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="dash-card" data-reveal>
                <div class="dash-card-head"><span><i class="bi bi-building me-2"></i>Fasilitas Aktif</span></div>
                <div class="p-3 pt-2">
                    @forelse ($fasilitasPerKategori as $kategori => $jumlah)
                        @php $wk = $warnaKat[$kategori] ?? '#176b87'; $ik = $ikonKat[$kategori] ?? 'bi-door-open'; @endphp
                        <div class="dash-meter">
                            <span class="dash-meter-ic" style="background:{{ $wk }}1a; color:{{ $wk }}"><i class="bi {{ $ik }}"></i></span>
                            <div class="dash-meter-body">
                                <div class="dash-meter-top"><span>{{ $kategori }}</span><span style="color:{{ $wk }}">{{ $jumlah }}</span></div>
                                <div class="dash-meter-track"><div class="dash-meter-fill" style="width:{{ round($jumlah / $maxKat * 100) }}%; background:{{ $wk }}"></div></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada fasilitas aktif.</p>
                    @endforelse
                    <a href="{{ route('admin.laporan') }}" class="dash-btn dash-btn-outline w-100 mt-2 justify-content-center"><i class="bi bi-file-earmark-bar-graph me-1"></i>Lihat Laporan</a>
                </div>
            </div>
        </div>

        {{-- Kanan: reservasi terbaru, feed modern (bukan tabel) --}}
        <div class="dash-card dash-col-right" data-reveal>
            <div class="dash-card-head">
                <span><i class="bi bi-clock-history me-2"></i>Reservasi Terbaru</span>
                <a href="{{ route('admin.reservasi.index') }}" class="dash-btn dash-btn-outline dash-btn-sm">Semua</a>
            </div>
            <div class="dash-feed">
                @forelse ($terbaru as $r)
                    <a href="{{ route('admin.reservasi.show', $r->kode_reservasi) }}" class="dash-feed-row">
                        <span class="initial-chip">{{ strtoupper(substr($r->pemesan->nama_lengkap, 0, 1)) }}</span>
                        <span class="dash-feed-body">
                            <span class="dash-feed-main">{{ $r->pemesan->nama_lengkap }}</span>
                            <span class="dash-feed-sub">{{ $r->tarifSewa->fasilitas->nama_fasilitas }} &middot; {{ $r->kode_reservasi }}</span>
                        </span>
                        <span class="chip {{ strtolower($r->status_reservasi->value) }}">{{ $r->status_reservasi->value }}</span>
                    </a>
                @empty
                    <p class="text-muted small p-3 mb-0">Belum ada reservasi.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== Dashboard bento redesign — hanya berlaku di halaman ini ===== */
    .dash { --d-primary:#176b87; --d-teal:#24aa9a; }

    @keyframes dashUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
    [data-reveal] { animation:dashUp .55s cubic-bezier(.2,.7,.3,1) both; }
    @media (prefers-reduced-motion: reduce) { [data-reveal] { animation:none; } }

    /* Hero */
    .dash-hero { position:relative; overflow:hidden; border-radius:1.5rem; padding:2.2rem 2.4rem; margin-bottom:1.25rem;
        background:linear-gradient(120deg,#0d2a3a,#134f66 45%,#127a72 85%); color:#fff; box-shadow:0 24px 50px -20px rgba(13,42,58,.55); }
    .dash-hero-mesh { position:absolute; inset:0;
        background:radial-gradient(38rem 20rem at 100% -20%, rgba(36,170,154,.35), transparent 60%),
                   radial-gradient(28rem 18rem at -10% 120%, rgba(23,107,135,.5), transparent 60%); }
    .dash-hero-grid { position:absolute; inset:0; opacity:.12;
        background-image:linear-gradient(rgba(255,255,255,.5) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.5) 1px, transparent 1px);
        background-size:28px 28px; mask-image:radial-gradient(60% 60% at 70% 30%, #000, transparent); }
    .dash-hero-eyebrow { display:inline-flex; align-items:center; font-size:.78rem; font-weight:700; color:#bfe9e0; letter-spacing:.03em; }
    .dash-hero-title { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.65rem; margin:.35rem 0 .3rem; }
    .dash-hero-sub { margin:0; opacity:.92; font-size:.94rem; max-width:34rem; }
    .dash-btn { display:inline-flex; align-items:center; padding:.6rem 1.1rem; border-radius:.8rem; font-weight:700; font-size:.88rem; text-decoration:none; transition:transform .15s ease, box-shadow .15s ease, background .15s ease; border:1.5px solid transparent; }
    .dash-btn-light { background:#fff; color:#0f526b; box-shadow:0 10px 22px -8px rgba(0,0,0,.3); }
    .dash-btn-light:hover { transform:translateY(-2px); color:#0f526b; }
    .dash-btn-ghost { background:rgba(255,255,255,.08); color:#fff; border-color:rgba(255,255,255,.35); }
    .dash-btn-ghost:hover { background:rgba(255,255,255,.16); color:#fff; transform:translateY(-2px); }
    .dash-btn-outline { background:#fff; color:var(--d-primary); border-color:#d6e4ea; }
    .dash-btn-outline:hover { border-color:var(--d-primary); color:var(--d-primary); background:#f2fafb; }
    .dash-btn-sm { padding:.4rem .8rem; font-size:.78rem; }

    /* Stat tiles */
    .dash-tiles { display:grid; grid-template-columns:repeat(4, 1fr); gap:1rem; margin-bottom:1.25rem; }
    .dash-tile { background:#fff; border:1px solid #e4ebf2; border-radius:1.15rem; padding:1.15rem 1.25rem; display:flex; flex-direction:column; gap:.6rem;
        box-shadow:0 3px 14px rgba(21,36,59,.05); transition:transform .2s ease, box-shadow .2s ease; animation-delay:calc(var(--tile-i) * .07s); }
    .dash-tile:hover { transform:translateY(-3px); box-shadow:0 16px 30px -12px rgba(21,36,59,.18); }
    .dash-tile-ic { width:2.6rem; height:2.6rem; border-radius:.85rem; display:grid; place-items:center; color:#fff; font-size:1.2rem; box-shadow:0 8px 16px -6px rgba(21,36,59,.35); }
    .dash-tile-v { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.9rem; line-height:1; color:#15243b; }
    .dash-tile-l { font-size:.82rem; font-weight:600; color:#637189; }

    /* Bento grid */
    .dash-bento { display:grid; grid-template-columns:minmax(0,1fr) minmax(0,1.55fr); gap:1.25rem; align-items:start; }
    .dash-col-left { display:flex; flex-direction:column; gap:1.25rem; }
    .dash-card { background:#fff; border:1px solid #e4ebf2; border-radius:1.15rem; box-shadow:0 3px 16px rgba(21,36,59,.055); overflow:hidden; }
    .dash-card-head { padding:1rem 1.25rem; border-bottom:1px solid #eef2f6; font-weight:700; display:flex; justify-content:space-between; align-items:center; gap:.5rem; }
    .dash-pill { background:#eef6f9; color:#0f526b; font-size:.72rem; font-weight:700; padding:.3rem .7rem; border-radius:2rem; }

    /* Donut */
    .dash-donut { width:172px; height:172px; border-radius:50%; position:relative; box-shadow:0 12px 28px -10px rgba(21,36,59,.28), inset 0 0 0 7px #fff; }
    .dash-donut-hole { position:absolute; inset:0; margin:auto; width:110px; height:110px; border-radius:50%; background:#fff; display:grid; place-items:center; box-shadow:0 2px 10px rgba(21,36,59,.08); }
    .dash-donut-v { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.5rem; color:var(--d-primary); }
    .dash-legend { display:grid; grid-template-columns:1fr 1fr; gap:.5rem .75rem; margin-top:1.4rem; text-align:left; }
    .dash-legend-item { display:flex; align-items:center; gap:.45rem; font-size:.82rem; color:#3c4a5f; font-weight:600; }
    .dash-legend-item .dot { width:.55rem; height:.55rem; border-radius:50%; flex:none; }
    .dash-legend-item .lbl { flex:1; }
    .dash-legend-item .n { font-weight:800; color:#15243b; }

    /* Fasilitas Aktif meters */
    .dash-meter { display:flex; align-items:center; gap:.8rem; padding:.5rem 0; }
    .dash-meter-ic { width:2.3rem; height:2.3rem; border-radius:.7rem; display:grid; place-items:center; font-size:1rem; flex:none; }
    .dash-meter-body { flex:1; min-width:0; }
    .dash-meter-top { display:flex; justify-content:space-between; font-size:.83rem; font-weight:600; margin-bottom:.35rem; }
    .dash-meter-track { height:.5rem; border-radius:1rem; background:#eef2f6; overflow:hidden; }
    .dash-meter-fill { height:100%; border-radius:1rem; transition:width .6s ease; }

    /* Reservasi Terbaru — activity feed */
    .dash-feed { display:flex; flex-direction:column; }
    .dash-feed-row { display:flex; align-items:center; gap:.85rem; padding:.85rem 1.25rem; text-decoration:none; color:inherit; border-bottom:1px solid #f1f4f8; transition:background .15s ease; }
    .dash-feed-row:last-child { border-bottom:0; }
    .dash-feed-row:hover { background:#f8fbfd; }
    .dash-feed-body { flex:1; min-width:0; display:flex; flex-direction:column; }
    .dash-feed-main { font-weight:700; font-size:.9rem; color:#15243b; }
    .dash-feed-sub { font-size:.78rem; color:#637189; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

    @media (max-width: 991.98px) {
        .dash-bento { grid-template-columns:1fr; }
    }
    @media (max-width: 575.98px) {
        .dash-tiles { grid-template-columns:repeat(2, 1fr); }
        .dash-hero { padding:1.6rem 1.4rem; }
    }
</style>
@endsection
