@extends('layouts.reservasi')
@section('title', 'Cek Status Reservasi')

@section('content')
<style>
    .cs-hero { background:linear-gradient(118deg,#124f6b 0%,#176b87 45%,#1b9487 100%); border-radius:1.4rem; position:relative; overflow:hidden; }
    .cs-hero::before, .cs-hero::after { content:''; position:absolute; border-radius:50%; background:rgba(255,255,255,.07); }
    .cs-hero::before { width:340px; height:340px; top:-160px; right:-90px; }
    .cs-hero::after { width:220px; height:220px; bottom:-120px; left:-60px; }
    .cs-hero .inner { position:relative; z-index:1; }
    .cs-badge { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.14); color:#c9f2ea; font-size:.74rem; font-weight:700; letter-spacing:.1em; text-transform:uppercase; padding:.35rem .85rem; border-radius:2rem; border:1px solid rgba(255,255,255,.18); }
    .cs-search { background:#fff; border-radius:1rem; padding:.45rem; box-shadow:0 18px 44px rgba(10,40,55,.35); display:flex; gap:.45rem; }
    .cs-search input { border:0; font-size:1.05rem; font-weight:600; letter-spacing:.06em; box-shadow:none !important; }
    .cs-search input::placeholder { font-weight:500; letter-spacing:.02em; color:#9aa8b8; }
    .cs-search .btn { border-radius:.75rem; white-space:nowrap; }
    .cs-hint { color:#bfe3de; font-size:.83rem; }
    .cs-hint code { background:rgba(255,255,255,.16); color:#fff; padding:.1rem .45rem; border-radius:.4rem; font-weight:700; }

    .legend-card { border-radius:1rem; height:100%; transition:transform .2s, box-shadow .2s; }
    .legend-card:hover { transform:translateY(-3px); box-shadow:0 12px 26px rgba(21,36,59,.09); }
    .legend-dot { width:2.5rem; height:2.5rem; border-radius:.8rem; display:grid; place-items:center; font-size:1.1rem; flex:none; }

    .how-step { display:flex; gap:.85rem; align-items:flex-start; }
    .how-num { width:1.9rem; height:1.9rem; border-radius:50%; background:#e5f4f3; color:var(--primary); display:grid; place-items:center; font-weight:800; font-size:.85rem; flex:none; }
</style>

    {{-- Hero + kolom pencarian --}}
    <div class="cs-hero p-4 p-md-5 mb-4 text-white">
        <div class="inner mx-auto text-center" style="max-width:640px;">
            <span class="cs-badge mb-3"><i class="bi bi-shield-check"></i> Lacak Reservasi Anda</span>
            <h1 class="h3 fw-bold mt-3 mb-2">Cek Status Reservasi</h1>
            <p class="mb-4" style="color:#d3ecf1">Masukkan kode yang Anda terima setelah checkout untuk melihat progres verifikasi, jadwal, dan rincian biaya reservasi Anda.</p>

            <form method="POST" action="{{ route('cek-status.hasil') }}">
                @csrf
                <div class="cs-search">
                    <span class="d-none d-sm-grid align-self-center ps-3 pe-1" style="color:var(--primary); font-size:1.2rem;"><i class="bi bi-upc-scan"></i></span>
                    <input name="kode" class="form-control form-control-lg text-uppercase" placeholder="contoh: TRX-A1B2 atau RSV-7K3M"
                           value="{{ old('kode') }}" maxlength="100" autocomplete="off" autofocus required>
                    <button class="btn btn-brand btn-lg px-4"><i class="bi bi-search me-md-2"></i><span class="d-none d-md-inline">Cek Status</span></button>
                </div>
            </form>

            <p class="cs-hint mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                Kode <code>TRX-</code> menampilkan semua ruangan dalam satu pemesanan, kode <code>RSV-</code> menampilkan satu ruangan saja.
            </p>
        </div>
    </div>

    {{-- Legenda status --}}
    <p class="eyebrow-sm mb-2">Arti Status</p>
    <div class="row g-3 mb-4">
        @foreach ([
            ['bi-hourglass-split', '#9a6b00', '#fff4d6', 'Diverifikasi', 'Reservasi sedang diperiksa admin. Anda masih dapat membatalkannya pada tahap ini.'],
            ['bi-check-circle',    '#0d8a5f', '#e2f7ef', 'Disetujui',    'Reservasi diterima — fasilitas siap digunakan sesuai jadwal yang dipesan.'],
            ['bi-x-circle',        '#c02929', '#fde4e4', 'Ditolak',      'Reservasi tidak dapat diproses. Alasan penolakan tercantum di halaman hasil.'],
            ['bi-slash-circle',    '#3a4653', '#e8ecf1', 'Dibatalkan',   'Reservasi dibatalkan oleh pemesan sebelum diproses admin.'],
        ] as [$ikon, $warna, $bg, $judul, $desk])
            <div class="col-6 col-lg-3">
                <div class="xcard legend-card p-3">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="legend-dot" style="background:{{ $bg }}; color:{{ $warna }}"><i class="bi {{ $ikon }}"></i></span>
                        <span class="fw-bold" style="color:{{ $warna }}">{{ $judul }}</span>
                    </div>
                    <p class="small text-muted mb-0">{{ $desk }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Panduan singkat --}}
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="xcard p-4 h-100">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-signpost-2 me-2" style="color:var(--primary)"></i>Bagaimana cara kerjanya?</h2>
                <div class="d-flex flex-column gap-3">
                    <div class="how-step">
                        <span class="how-num">1</span>
                        <div><strong class="d-block small">Masukkan kode reservasi</strong>
                        <span class="small text-muted">Kode dikirim lewat pop-up konfirmasi setelah Anda menyelesaikan checkout.</span></div>
                    </div>
                    <div class="how-step">
                        <span class="how-num">2</span>
                        <div><strong class="d-block small">Lihat progres verifikasi</strong>
                        <span class="small text-muted">Pantau tahapan Diajukan → Diverifikasi → Disetujui beserta rincian jadwal dan biaya.</span></div>
                    </div>
                    <div class="how-step">
                        <span class="how-num">3</span>
                        <div><strong class="d-block small">Batalkan bila perlu</strong>
                        <span class="small text-muted">Selama status masih <em>Diverifikasi</em> dan tanggal belum lewat, reservasi dapat dibatalkan langsung dari halaman hasil.</span></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="xcard p-4 h-100 d-flex flex-column">
                <h2 class="h6 fw-bold mb-3"><i class="bi bi-question-circle me-2" style="color:var(--teal)"></i>Kode hilang?</h2>
                <p class="small text-muted">Kode reservasi hanya ditampilkan sekali setelah checkout. Jika terlupa, hubungi pengelola gedung BITC dengan menyebutkan nama pemesan dan tanggal sewa.</p>
                <div class="mt-auto pt-2">
                    <a href="{{ route('reservasi.index') }}" class="btn btn-brand-outline btn-sm px-3"><i class="bi bi-plus-circle me-1"></i>Buat Reservasi Baru</a>
                </div>
            </div>
        </div>
    </div>
@endsection
