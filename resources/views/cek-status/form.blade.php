@extends('layouts.reservasi')
@section('title', 'Cek Status Reservasi')

@section('content')
{{-- ══════════════════════════════════════════════════════════════
     Style scoped khusus halaman ini — palet monokrom teal
     agar konsisten dengan homepage. Tidak menyentuh layout global.
     ══════════════════════════════════════════════════════════════ --}}
<style>
    .cs-page {
        /* Override lokal — hanya berlaku di dalam wrapper ini. */
        --primary:      #0e6b7d;
        --primary-dark: #084b58;
        --primary-soft: #e6f2f4;
        --ink:          #0f172a;
        --muted:        #64748b;
        --soft:         #94a3b8;
        --line:         #e5e9ef;
        --line-soft:    #eef2f6;
        --surface:      #f7f9fc;

        --radius:       1rem;
        --radius-lg:    1.35rem;

        --shadow-sm:    0 6px 16px -6px rgba(15,23,42,.08);
        --shadow-md:    0 14px 34px -14px rgba(15,23,42,.14);
        --shadow-lg:    0 26px 50px -20px rgba(15,23,42,.20);
    }

    /* ────────── HERO ────────── */
    .cs-hero {
        background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-radius:var(--radius-lg);
        color:#fff;
        padding:3.5rem 2rem;
        position:relative; overflow:hidden;
        box-shadow:var(--shadow-lg);
    }
    /* Aksen bulat dua sudut — tenang, satu tone putih tipis. */
    .cs-hero::before, .cs-hero::after {
        content:''; position:absolute; border-radius:50%;
        background:rgba(255,255,255,.06); pointer-events:none;
    }
    .cs-hero::before { width:22rem; height:22rem; top:-9rem; right:-6rem; }
    .cs-hero::after  { width:14rem; height:14rem; bottom:-6rem; left:-4rem; }
    .cs-hero .inner { position:relative; z-index:1; max-width:680px; margin:0 auto; text-align:center; }

    .cs-badge {
        display:inline-flex; align-items:center; gap:.5rem;
        background:rgba(255,255,255,.14); color:#d5f0f2;
        font-size:.72rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase;
        padding:.4rem .9rem; border-radius:2rem;
        border:1px solid rgba(255,255,255,.2);
    }
    .cs-hero h1 { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:clamp(1.7rem, 3vw, 2.3rem); letter-spacing:-.03em; color:#fff; margin:1.1rem 0 .75rem; }
    .cs-hero p.sub { color:rgba(255,255,255,.85); font-size:1rem; line-height:1.7; margin:0 0 2rem; max-width:520px; margin-left:auto; margin-right:auto; }

    /* ────────── SEARCH FORM ────────── */
    .cs-search {
        background:#fff; border-radius:1.15rem;
        padding:.4rem .4rem .4rem .45rem;
        display:flex; gap:.4rem; align-items:stretch;
        box-shadow:0 20px 44px -12px rgba(0,0,0,.35);
    }
    .cs-search .scan-ic {
        display:none; align-items:center; padding:0 .35rem 0 .5rem;
        color:var(--primary); font-size:1.25rem;
    }
    @media (min-width: 576px) { .cs-search .scan-ic { display:flex; } }
    .cs-search input {
        flex:1; border:0; background:transparent;
        font-size:1rem; font-weight:600; letter-spacing:.06em; color:var(--ink);
        padding:.85rem .75rem;
        outline:none; box-shadow:none !important;
    }
    .cs-search input::placeholder { font-weight:500; letter-spacing:.02em; color:#94a3b8; text-transform:uppercase; font-size:.9rem; }
    .cs-search .btn-cek {
        display:inline-flex; align-items:center; gap:.5rem;
        padding:.75rem 1.3rem; border-radius:.9rem;
        background:var(--primary); color:#fff; border:0;
        font-weight:700; font-size:.95rem; white-space:nowrap;
        transition:background .15s ease, transform .15s ease;
    }
    .cs-search .btn-cek:hover { background:var(--primary-dark); transform:translateY(-1px); }
    .cs-search .btn-cek .label-full { display:none; }
    @media (min-width: 576px) { .cs-search .btn-cek .label-full { display:inline; } }

    /* ────────── HINT DI BAWAH SEARCH ────────── */
    .cs-hint {
        color:rgba(255,255,255,.85); font-size:.85rem;
        margin:1.1rem 0 0;
        display:flex; gap:.5rem; align-items:flex-start; justify-content:center;
        line-height:1.6;
    }
    .cs-hint i { color:rgba(255,255,255,.7); margin-top:.15rem; }
    .cs-hint code {
        background:rgba(255,255,255,.14); color:#fff;
        padding:.15rem .5rem; border-radius:.4rem;
        font-family:'DM Sans',sans-serif; font-weight:700; font-size:.8rem;
    }

    /* ────────── EYEBROW SECTION ────────── */
    .cs-page .eyebrow-line {
        display:inline-flex; align-items:center; gap:.6rem;
        color:var(--primary); font-size:.75rem; font-weight:700;
        letter-spacing:.16em; text-transform:uppercase; margin:2.5rem 0 1rem;
    }
    .cs-page .eyebrow-line::before {
        content:''; width:1.8rem; height:2px; background:currentColor;
    }

    /* ────────── LEGENDA STATUS CARD ────────── */
    .status-card {
        background:#fff; border:1px solid var(--line); border-radius:var(--radius);
        padding:1.2rem 1.25rem; height:100%;
        transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .status-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:transparent; }
    .status-card .head { display:flex; align-items:center; gap:.7rem; margin-bottom:.7rem; }
    .status-card .dot {
        width:2.5rem; height:2.5rem; border-radius:.75rem; flex:none;
        display:grid; place-items:center; font-size:1.1rem;
    }
    .status-card .title { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1rem; color:var(--ink); }
    .status-card .desc { color:var(--muted); font-size:.85rem; line-height:1.6; margin:0; }

    /* Warna semantic pill (bg soft + icon berwarna) — dipertahankan
       karena statusnya beda arti; hanya di-calm-kan supaya nggak "warna warni". */
    .dot-menunggu { background:#fff4d6; color:#b47f00; }
    .dot-setujui  { background:#e2f7ef; color:#0d8a5f; }
    .dot-tolak    { background:#fde4e4; color:#c02929; }
    .dot-batal    { background:#eef1f5; color:#475569; }

    /* ────────── PANDUAN CARD ────────── */
    .guide-card {
        background:#fff; border:1px solid var(--line); border-radius:var(--radius);
        padding:1.6rem; height:100%;
        transition:transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }
    .guide-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:transparent; }
    .guide-card h2 {
        display:flex; align-items:center; gap:.6rem;
        font-family:'Plus Jakarta Sans',sans-serif; font-weight:800;
        font-size:1.05rem; color:var(--ink); margin:0 0 1.3rem;
    }
    .guide-card h2 i {
        display:grid; place-items:center; width:2.2rem; height:2.2rem;
        border-radius:.6rem; background:var(--primary-soft); color:var(--primary);
        font-size:1rem;
    }

    .step-row { display:flex; gap:.9rem; align-items:flex-start; }
    .step-row + .step-row { margin-top:1.1rem; padding-top:1.1rem; border-top:1px solid var(--line-soft); }
    .step-row .n {
        display:grid; place-items:center;
        width:2rem; height:2rem; border-radius:.5rem;
        background:var(--primary-soft); color:var(--primary);
        font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:.88rem;
        flex:none;
    }
    .step-row .body b {
        display:block; font-family:'Plus Jakarta Sans',sans-serif; font-weight:700;
        color:var(--ink); font-size:.94rem; margin-bottom:.15rem;
    }
    .step-row .body span { color:var(--muted); font-size:.86rem; line-height:1.55; }

    .cs-page .btn-reservasi-baru {
        display:inline-flex; align-items:center; gap:.5rem;
        padding:.7rem 1.1rem; border-radius:.7rem;
        color:var(--primary); background:#fff;
        border:1.5px solid var(--line); font-weight:700; font-size:.9rem;
        text-decoration:none;
        transition:border-color .15s ease, color .15s ease, background .15s ease, transform .15s ease;
    }
    .cs-page .btn-reservasi-baru:hover { border-color:var(--primary); background:var(--primary-soft); transform:translateY(-1px); }
</style>

<div class="cs-page">

    {{-- ══════════════════════════════════════════════════════════════
         HERO — judul + form pencarian kode
         ══════════════════════════════════════════════════════════════ --}}
    <div class="cs-hero" data-reveal>
        <div class="inner">
            <span class="cs-badge"><i class="bi bi-shield-check"></i> Lacak Reservasi Anda</span>
            <h1>Cek Status Reservasi</h1>
            <p class="sub">Masukkan kode yang Anda terima setelah checkout untuk melihat progres verifikasi, jadwal, dan rincian biaya reservasi Anda.</p>

            <form method="POST" action="{{ route('cek-status.hasil') }}">
                @csrf
                <div class="cs-search">

                    <input name="kode" class="text-uppercase" placeholder="Contoh: TRX-A1B2 atau RSV-7K3M"
                           value="{{ old('kode') }}" maxlength="100" autocomplete="off" autofocus required>
                    <button type="submit" class="btn-cek">
                        <i class="bi bi-search"></i>
                        <span class="label-full">Cek Status</span>
                    </button>
                </div>
            </form>

            <p class="cs-hint">
                <i class="bi bi-info-circle"></i>
            </p>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         LEGENDA STATUS
         ══════════════════════════════════════════════════════════════ --}}
    <p class="eyebrow-line">Arti Status</p>
    <div class="row g-3" data-reveal>
        @foreach ([
            ['bi-hourglass-split', 'dot-menunggu', 'Diverifikasi', 'Reservasi sedang diperiksa admin. Anda masih dapat membatalkannya pada tahap ini.'],
            ['bi-check-circle',    'dot-setujui',  'Disetujui',    'Reservasi diterima. Fasilitas siap digunakan sesuai jadwal yang dipesan.'],
            ['bi-x-circle',        'dot-tolak',    'Ditolak',      'Reservasi tidak dapat diproses. Alasan penolakan tercantum di halaman hasil.'],
            ['bi-slash-circle',    'dot-batal',    'Dibatalkan',   'Reservasi dibatalkan oleh pemesan sebelum diproses admin.'],
        ] as [$ikon, $kelasWarna, $judul, $desk])
            <div class="col-6 col-lg-3">
                <div class="status-card">
                    <div class="head">
                        <span class="dot {{ $kelasWarna }}"><i class="bi {{ $ikon }}"></i></span>
                        <span class="title">{{ $judul }}</span>
                    </div>
                    <p class="desc">{{ $desk }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         PANDUAN — cara kerja + kode hilang
         ══════════════════════════════════════════════════════════════ --}}
    <p class="eyebrow-line">Panduan Singkat</p>
    <div class="row g-3" data-reveal>
        <div class="col-lg-7">
            <div class="guide-card">
                <h2><i class="bi bi-signpost-2"></i>Bagaimana cara kerjanya?</h2>

                <div class="step-row">
                    <span class="n">1</span>
                    <div class="body">
                        <b>Masukkan kode reservasi</b>
                        <span>Kode dikirim lewat pop-up konfirmasi setelah Anda menyelesaikan checkout.</span>
                    </div>
                </div>
                <div class="step-row">
                    <span class="n">2</span>
                    <div class="body">
                        <b>Lihat progres verifikasi</b>
                        <span>Pantau tahapan <em>Diajukan → Diverifikasi → Disetujui</em> beserta rincian jadwal dan biaya.</span>
                    </div>
                </div>
                <div class="step-row">
                    <span class="n">3</span>
                    <div class="body">
                        <b>Batalkan bila perlu</b>
                        <span>Selama status masih <em>Diverifikasi</em> dan tanggal belum lewat, reservasi dapat dibatalkan langsung dari halaman hasil.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="guide-card d-flex flex-column">
                <h2><i class="bi bi-question-circle"></i>Kode hilang?</h2>
                <p style="color:var(--muted); font-size:.9rem; line-height:1.65;">Kode reservasi hanya ditampilkan sekali setelah checkout. Jika terlupa, hubungi pengelola gedung BITC dengan menyebutkan <strong>nama pemesan</strong> dan <strong>tanggal sewa</strong> Anda.</p>
                <div class="mt-auto pt-2">
                    <a href="{{ route('reservasi.index') }}" class="btn-reservasi-baru">
                        <i class="bi bi-plus-circle"></i> Buat Reservasi Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection