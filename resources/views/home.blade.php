<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="WADUH Wadah Akses Digital Unit Hunian BITC. Reservasi Working Space, Co-Working Space, dan Convention Hall Gedung BITC Cimahi secara online.">
    <title>WADUH | Wadah Akses Digital Unit Hunian BITC</title>
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root { --ink:#15243b; --muted:#637189; --primary:#176b87; --primary-dark:#0f526b; --teal:#24aa9a;
                --surface:#f6f9fc; --line:#e4ebf2;
                --l1:#2f7fd1; --l2:#24aa9a; --l3a:#7c5cd6; --l3b:#e8833a; --l5:#d6527c; }
        html { scroll-behavior:smooth; scroll-padding-top:86px; }
        body { font-family:'DM Sans',sans-serif; color:var(--ink); background:#fff; line-height:1.6; -webkit-font-smoothing:antialiased; }
        h1,h2,h3,h4,.navbar-brand { font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-.04em; }
        ::selection { background:#bfe3ea; color:var(--primary-dark); }
        a:focus-visible, button:focus-visible, .nav-link:focus-visible { outline:2px solid var(--primary); outline-offset:2px; border-radius:.35rem; }

        /* Navbar — floating pill, senada dengan halaman reservasi */
        .navbar { padding:1.1rem 0; transition:all .3s ease; }
        .navbar .container { background:rgba(255,255,255,.7); border:1px solid rgba(228,235,242,.9); border-radius:1.5rem; padding:.5rem .5rem .5rem 1.1rem; backdrop-filter:blur(16px); box-shadow:0 14px 34px -20px rgba(21,36,59,.22); transition:box-shadow .3s ease, background .3s ease; }
        .navbar.scrolled { padding:.65rem 0; }
        .navbar.scrolled .container { background:rgba(255,255,255,.92); box-shadow:0 16px 36px -18px rgba(21,36,59,.22); }
        .navbar-brand { font-weight:800; color:var(--ink); font-size:1.2rem; }
        .brand-mark { display:inline-grid; width:2.25rem; height:2.25rem; place-items:center; color:#fff; background:linear-gradient(135deg,var(--primary),var(--teal)); border-radius:.75rem; box-shadow:0 7px 16px rgba(23,107,135,.25); }
        .nav-link { color:#4e5c70; font-weight:600; font-size:.9rem; border-radius:.75rem; padding:.55rem .95rem !important; transition:background .15s ease, color .15s ease; }
        .nav-link:hover,.nav-link.active { color:var(--primary); background:#eef6f9; }
        .btn-nav { color:#fff !important; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); border-radius:.85rem; padding:.6rem 1.1rem; font-weight:700; font-size:.88rem; box-shadow:0 8px 18px -8px rgba(23,107,135,.5); transition:transform .15s ease, box-shadow .15s ease; }
        .btn-nav:hover { transform:translateY(-1px); box-shadow:0 10px 22px -8px rgba(23,107,135,.6); }
        .dropdown-menu { border:1px solid var(--line); border-radius:1rem; box-shadow:0 18px 40px rgba(21,36,59,.12); padding:.5rem; }
        .dropdown-item { border-radius:.6rem; font-weight:600; font-size:.88rem; padding:.5rem .75rem; }
        .dropdown-item .dot { display:inline-block; width:.65rem; height:.65rem; border-radius:50%; margin-right:.5rem; }

        /* Hero */
        .hero { position:relative; overflow:hidden; padding:9.5rem 0 5rem;
                background:radial-gradient(60rem 30rem at 110% -10%, #d9f3ee 0%, transparent 60%),
                           radial-gradient(50rem 26rem at -15% 20%, #dcebfa 0%, transparent 55%),
                           linear-gradient(160deg,#f4fbfa 0%, #f4f8fd 100%); }
        .hero .badge-acr { background:#fff; border:1px solid var(--line); color:var(--primary); font-weight:700; border-radius:2rem; padding:.5rem 1rem; box-shadow:0 8px 20px rgba(21,36,59,.06); }
        .hero h1 { font-size:clamp(2.6rem,5.2vw,4.4rem); font-weight:800; line-height:1.08; }
        .hero h1 .grad { background:linear-gradient(90deg,var(--primary),var(--teal) 55%,var(--l3a)); -webkit-background-clip:text; background-clip:text; color:transparent; }
        .hero p.lead-copy { max-width:640px; color:var(--muted); font-size:1.08rem; line-height:1.8; }
        .btn-hero { display:inline-flex; align-items:center; gap:.55rem; padding:.85rem 1.25rem; border-radius:.75rem; font-weight:700; }
        .btn-hero.solid { color:#fff; background:var(--primary); box-shadow:0 12px 26px rgba(23,107,135,.28); }
        .btn-hero.solid:hover { background:var(--primary-dark); color:#fff; transform:translateY(-2px); }
        .btn-hero.ghost { color:var(--primary); background:#fff; border:1.5px solid #c3d9e2; }
        .btn-hero.ghost:hover { border-color:var(--primary); }
        .hero-stats { display:flex; flex-wrap:wrap; gap:1rem; margin-top:2.4rem; }
        .hstat { display:flex; align-items:center; gap:.75rem; background:#fff; border:1px solid var(--line); border-radius:1.15rem; padding:.85rem 1.3rem .85rem .85rem; box-shadow:0 10px 26px rgba(21,36,59,.06); transition:transform .2s ease, box-shadow .2s ease; }
        .hstat:hover { transform:translateY(-3px); box-shadow:0 16px 32px -8px rgba(21,36,59,.12); }
        .hstat .hstat-ic { display:grid; place-items:center; width:2.5rem; height:2.5rem; border-radius:.85rem; background:linear-gradient(135deg,#e5f4f3,#dcf0ee); color:var(--primary); font-size:1.1rem; flex:none; }
        .hstat b { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.4rem; color:var(--primary); display:block; line-height:1.1; }
        .hstat span { color:var(--muted); font-size:.78rem; font-weight:600; }
        .hero-art { position:relative; }
        .floor-stack { display:flex; flex-direction:column-reverse; gap:.55rem; max-width:340px; margin:auto; }
        .floor-chip { display:flex; align-items:center; gap:.8rem; background:#fff; border:1px solid var(--line); border-radius:1rem; padding:.8rem 1rem; box-shadow:0 12px 30px rgba(21,36,59,.08); transition:transform .2s; }
        .floor-chip:hover { transform:translateX(8px); }
        .floor-chip .fnum { display:grid; place-items:center; width:2.6rem; height:2.6rem; border-radius:.8rem; color:#fff; font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; }
        .floor-chip small { color:var(--muted); display:block; }

        /* Sections */
        .section { padding:5.5rem 0; }
        .eyebrow { display:flex; align-items:center; gap:.55rem; color:var(--primary); font-size:.75rem; font-weight:700; letter-spacing:.14em; text-transform:uppercase; margin-bottom:.8rem; }
        .eyebrow::before { content:''; width:1.9rem; height:2px; background:currentColor; }
        .section h2 { font-size:clamp(1.7rem,3vw,2.5rem); font-weight:800; }
        .xcard { background:#fff; border:1px solid var(--line); border-radius:1.15rem; transition:transform .2s, box-shadow .2s; }
        .xcard:hover { transform:translateY(-5px); box-shadow:0 18px 40px rgba(21,36,59,.1); }

        /* Lantai cards */
        .lantai-card { position:relative; overflow:hidden; border-radius:1.35rem; border:1px solid var(--line); background:#fff; height:100%; transition:transform .25s cubic-bezier(.2,.7,.3,1), box-shadow .25s ease; }
        .lantai-card .head { padding:1.3rem 1.4rem; color:#fff; display:flex; justify-content:space-between; align-items:center; position:relative; overflow:hidden; }
        .lantai-card .head::after { content:''; position:absolute; right:-2rem; bottom:-2.5rem; width:6.5rem; height:6.5rem; border-radius:50%; background:rgba(255,255,255,.14); }
        .lantai-card .head .big { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.9rem; font-weight:800; line-height:1; }
        .lantai-card .body { padding:1.2rem 1.4rem 1.4rem; }
        .lantai-card:hover { transform:translateY(-6px) scale(1.015); box-shadow:0 22px 44px -14px rgba(21,36,59,.2); }
        .pill { display:inline-flex; align-items:center; gap:.35rem; padding:.32rem .7rem; border-radius:2rem; font-size:.76rem; font-weight:700; }

        /* Jam operasional */
        .jam-panel { background:linear-gradient(120deg,#12303f,#176b87 60%,#1c8f80); color:#fff; border-radius:1.5rem; padding:2.6rem; position:relative; overflow:hidden; box-shadow:0 24px 50px -20px rgba(13,42,58,.4); }
        .jam-panel::after { content:''; position:absolute; right:-5rem; top:-9rem; width:18rem; height:18rem; border:2.6rem solid rgba(255,255,255,.07); border-radius:50%; }
        .jam-panel::before { content:''; position:absolute; inset:0; background:radial-gradient(30rem 16rem at -10% 110%, rgba(36,170,154,.35), transparent 60%); }
        .jam-row { position:relative; display:flex; justify-content:space-between; gap:1rem; padding:.7rem 0; border-bottom:1px dashed rgba(255,255,255,.22); font-size:.95rem; }
        .jam-row b { font-family:'Plus Jakarta Sans',sans-serif; }

        .step-card { text-align:center; padding:1.7rem 1.3rem; transition:transform .2s ease, box-shadow .2s ease; }
        .step-card:hover { transform:translateY(-4px); box-shadow:0 18px 36px -12px rgba(21,36,59,.15); }
        .step-card .n { display:grid; place-items:center; width:3.4rem; height:3.4rem; margin:0 auto 1rem; border-radius:1.1rem; color:#fff; font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; font-size:1.15rem; box-shadow:0 10px 22px -8px rgba(21,36,59,.3); }

        .cta-band { background:linear-gradient(115deg,#0d2a3a,#145f7c 55%,#168b88); border-radius:1.6rem; color:#fff; padding:3rem; position:relative; overflow:hidden; box-shadow:0 24px 50px -20px rgba(13,42,58,.45); }
        .cta-band::after { content:''; position:absolute; right:-4rem; top:-6rem; width:16rem; height:16rem; border-radius:50%; background:rgba(255,255,255,.06); }
        .cta-band > * { position:relative; }
        .site-footer { padding:2.4rem 0; color:var(--muted); border-top:1px solid var(--line); font-size:.85rem; }

        [data-reveal] { opacity:0; transform:translateY(22px); transition:opacity .6s ease, transform .6s ease; }
        [data-reveal].in { opacity:1; transform:none; }
        @media (prefers-reduced-motion: reduce) { [data-reveal] { opacity:1; transform:none; } }
    </style>
</head>
<body>
@php
    $warnaLantai = ['1' => 'var(--l1)', '2' => 'var(--l2)', '3A' => 'var(--l3a)', '3B' => 'var(--l3b)', '5' => 'var(--l5)'];
    $ikonKategori = ['Working Space' => 'bi-briefcase', 'Co-Working Space' => 'bi-people', 'Convention Hall' => 'bi-bank'];
    $deskKategori = [
        'Working Space'    => 'Ruang kerja privat untuk instansi & perusahaan.',
        'Co-Working Space' => 'Kubikal fleksibel untuk startup & pelaku kreatif.',
        'Convention Hall'  => 'Aula besar untuk event, seminar, dan konvensi.',
    ];
@endphp

<nav id="mainNav" class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="#beranda">
            <span class="brand-mark"><i class="bi bi-building"></i></span>
            <span>WADUH<span style="color:var(--teal)">.</span></span>
        </a>
        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu"><i class="bi bi-list fs-2"></i></button>
        <div id="navbarMenu" class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
                <li class="nav-item"><a class="nav-link active" href="#beranda">Beranda</a></li>
                <li class="nav-item"><a class="nav-link" href="#tentang">Tentang BITC</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#fasilitas" role="button" data-bs-toggle="dropdown">Fasilitas</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#fasilitas"><i class="bi bi-grid-3x3-gap me-2"></i>Semua Lantai</a></li>
                        <li><hr class="dropdown-divider"></li>
                        @foreach ($daftarLantai as $l)
                            <li>
                                <a class="dropdown-item" href="{{ route('reservasi.denah', ['kategori' => $l['kategori'], 'lantai' => $l['id']]) }}">
                                    <span class="dot" style="background:{{ $warnaLantai[$l['nomor']] ?? 'var(--primary)' }}"></span>
                                    Lantai {{ $l['nomor'] }} · {{ $l['kategori'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#kontak">Kontak</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('cek-status.form') }}">Cek Status</a></li>
                <li class="nav-item ms-lg-2"><a class="btn btn-nav" href="{{ route('admin.login') }}"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<main>
    {{-- HERO --}}
    <section id="beranda" class="hero">
        <div class="container position-relative">
            <div class="row align-items-center gy-5">
                <div class="col-lg-7" data-reveal>
                    <span class="badge-acr d-inline-block mb-3"><i class="bi bi-stars me-1"></i>Wadah Akses Digital Unit Hunian BITC</span>
                    <h1>Reservasi ruang di <span class="grad">Gedung BITC</span>, semudah beberapa klik.</h1>
                    <p class="lead-copy mt-3">WADUH adalah layanan resmi reservasi fasilitas Baros Information Technology Creative Center (BITC) - Mulai dari kubikal co-working, ruang kerja privat, sampai convention hall. Pilih ruang, atur jadwal, pantau status. Tanpa antrian.</p>
                    <div class="d-flex flex-wrap gap-3 mt-4">
                        <a href="{{ route('reservasi.index') }}" class="btn-hero solid text-decoration-none">Reservasi Sekarang <i class="bi bi-arrow-up-right"></i></a>
                        <a href="{{ route('cek-status.form') }}" class="btn-hero ghost text-decoration-none"><i class="bi bi-search"></i> Cek Status Reservasi</a>
                    </div>
                    <div class="hero-stats" data-reveal>
                        <div class="hstat"><span class="hstat-ic"><i class="bi bi-grid-3x3-gap-fill"></i></span><span><b>{{ $daftarLantai->sum('total') }}</b><span>Total Unit Ruang</span></span></div>
                        <div class="hstat"><span class="hstat-ic"><i class="bi bi-check-circle-fill"></i></span><span><b>{{ $daftarLantai->sum('tersedia') }}</b><span>Siap Direservasi</span></span></div>
                        <div class="hstat"><span class="hstat-ic"><i class="bi bi-building"></i></span><span><b>{{ $daftarLantai->count() }}</b><span>Lantai Aktif</span></span></div>
                        <div class="hstat"><span class="hstat-ic"><i class="bi bi-clock-history"></i></span><span><b>3</b><span>Jenis Sewa (Jam/Hari/Bulan)</span></span></div>
                    </div>
                </div>
                <div class="col-lg-5" data-reveal>
                    <div class="hero-art">
                        <div class="floor-stack">
                            @foreach ($daftarLantai as $l)
                                <a class="floor-chip text-decoration-none text-reset" href="{{ route('reservasi.denah', ['kategori' => $l['kategori'], 'lantai' => $l['id']]) }}">
                                    <span class="fnum" style="background:{{ $warnaLantai[$l['nomor']] ?? 'var(--primary)' }}">{{ $l['nomor'] }}</span>
                                    <span>
                                        <b>{{ $l['kategori'] }}</b>
                                        <small>{{ $l['tersedia'] }} dari {{ $l['total'] }} unit tersedia</small>
                                    </span>
                                    <i class="bi bi-chevron-right ms-auto text-muted"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- TENTANG BITC --}}
    <section id="tentang" class="section" style="background:#fff">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-6" data-reveal>
                    <p class="eyebrow">Tentang BITC</p>
                    <h2>Baros Information Technology Creative Center rumah ekosistem digital Cimahi.</h2>
                    <p class="text-muted mt-3" style="line-height:1.85">Gedung <strong>BITC</strong> di Jalan Raya Baros No. 78 dikelola oleh <strong>UPTD Cimahi Techno Park</strong> sebagai pusat pengembangan industri teknologi informasi dan ekonomi kreatif Kota Cimahi. Di dalamnya tersedia unit hunian usaha yang dapat disewa: ruang kerja privat, kubikal co-working, hingga convention hall untuk acara berskala besar.</p>
                    <p class="text-muted" style="line-height:1.85"><strong>WADUH (Wadah Akses Digital Unit Hunian)</strong> hadir agar seluruh proses melihat ketersediaan per lantai, mengajukan reservasi, dan cek status reservasi.</p>

                    {{-- Jam operasional (digabung ke Tentang BITC) --}}
                    <div class="xcard mt-4 overflow-hidden">
                        <div class="px-3 py-2 fw-bold d-flex align-items-center gap-2" style="background:linear-gradient(90deg,#eaf5f4,#eef5fa); border-bottom:1px solid var(--line)">
                            <i class="bi bi-clock" style="color:var(--primary)"></i> Jam Operasional Gedung BITC
                        </div>
                        <div class="px-3">
                            <div class="d-flex justify-content-between py-2 border-bottom small"><span class="text-muted">Senin – Jumat</span><strong>08.00 – 16.00 WIB</strong></div>
                            <div class="d-flex justify-content-between py-2 border-bottom small"><span class="text-muted">Sabtu</span><strong>08.00 – 12.00 WIB</strong></div>
                            <div class="d-flex justify-content-between py-2 small"><span class="text-muted">Minggu & Hari Libur</span><strong>Tutup</strong></div>
                        </div>
                        <div class="px-3 py-2 small text-muted" style="background:#fbfdfe; border-top:1px solid var(--line)">
                            <!-- <i class="bi bi-info-circle me-1"></i>Reservasi online lewat WADUH tetap bisa 24 jam. Pemakaian di luar jam layanan diatur lewat persetujuan admin. -->
                        </div>
                    </div>
                </div>
                <div class="col-lg-6" data-reveal>
                    <div class="row g-3">
                        <div class="col-6"><div class="xcard p-4 h-100"><span class="d-inline-grid mb-2" style="width:2.9rem;height:2.9rem;border-radius:.9rem;place-items:center;background:color-mix(in srgb, var(--l1) 14%, #fff);color:var(--l1)"><i class="bi bi-geo-alt fs-5"></i></span><h3 class="h6 mb-1">Lokasi Strategis</h3><p class="small text-muted mb-0">Jl. Raya Baros No. 78, Cimahi Selatan akses mudah dari tol Baros.</p></div></div>
                        <div class="col-6"><div class="xcard p-4 h-100"><span class="d-inline-grid mb-2" style="width:2.9rem;height:2.9rem;border-radius:.9rem;place-items:center;background:color-mix(in srgb, var(--l3a) 14%, #fff);color:var(--l3a)"><i class="bi bi-wifi fs-5"></i></span><h3 class="h6 mb-1">Fasilitas Penunjang</h3><p class="small text-muted mb-0">Internet, area parkir, lift, mushola, dan ruang publik kreatif.</p></div></div>
                        <div class="col-6"><div class="xcard p-4 h-100"><span class="d-inline-grid mb-2" style="width:2.9rem;height:2.9rem;border-radius:.9rem;place-items:center;background:color-mix(in srgb, var(--l2) 14%, #fff);color:var(--l2)"><i class="bi bi-shield-check fs-5"></i></span><h3 class="h6 mb-1">Dikelola Resmi</h3><p class="small text-muted mb-0">UPTD Cimahi Techno Park, Pemerintah Kota Cimahi.</p></div></div>
                        <div class="col-6"><div class="xcard p-4 h-100"><span class="d-inline-grid mb-2" style="width:2.9rem;height:2.9rem;border-radius:.9rem;place-items:center;background:color-mix(in srgb, var(--l3b) 14%, #fff);color:var(--l3b)"><i class="bi bi-lightning-charge fs-5"></i></span><h3 class="h6 mb-1">Proses Digital</h3><p class="small text-muted mb-0">Lihat Ruangan sampai checkout semuanya lewat WADUH.</p></div></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FASILITAS PER LANTAI --}}
    <section id="fasilitas" class="section" style="background:var(--surface)">
        <div class="container">
            <div class="text-center mb-5" data-reveal>
                <p class="eyebrow justify-content-center" style="justify-content:center">Fasilitas per Lantai</p>
                <h2>Lima lantai, tiga jenis ruang pilih yang pas untukmu.</h2>
                <p class="text-muted mt-2">Klik lantai untuk langsung melihat denah & ketersediaannya hari ini.</p>
            </div>
            <div class="row g-4">
                @foreach ($daftarLantai as $l)
                    @php $warna = $warnaLantai[$l['nomor']] ?? 'var(--primary)'; @endphp
                    <div class="col-md-6 col-xl-4" data-reveal>
                        <div class="lantai-card">
                            <div class="head" style="background:linear-gradient(120deg, {{ $warna }}, {{ $warna }}cc)">
                                <div>
                                    <small class="text-uppercase fw-bold" style="letter-spacing:.1em; opacity:.85">Lantai</small>
                                    <div class="big">{{ $l['nomor'] }}</div>
                                </div>
                                <i class="bi {{ $ikonKategori[$l['kategori']] ?? 'bi-door-open' }} fs-1" style="opacity:.9"></i>
                            </div>
                            <div class="body">
                                <h3 class="h5 mb-1">{{ $l['kategori'] }}</h3>
                                <p class="text-muted small mb-3">{{ $deskKategori[$l['kategori']] ?? '' }}</p>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <span class="pill" style="background:{{ $warna }}1a; color:{{ $warna }}"><i class="bi bi-door-open"></i> {{ $l['total'] }} unit</span>
                                    <span class="pill" style="background:#e2f7ef; color:#0d8a5f"><i class="bi bi-check-circle"></i> {{ $l['tersedia'] }} tersedia</span>
                                </div>
                                <a href="{{ route('reservasi.denah', ['kategori' => $l['kategori'], 'lantai' => $l['id']]) }}"
                                   class="btn w-100 fw-bold text-white" style="background:{{ $warna }}; border-radius:.65rem">
                                    Lihat Denah Lantai {{ $l['nomor'] }} <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- KONTAK --}}
    <section id="kontak" class="section" style="background:#fff">
        <div class="container">
            <div class="row gy-4 align-items-center">
                <div class="col-lg-6" data-reveal>
                    <p class="eyebrow">Kontak</p>
                    <h2>Butuh bantuan? Hubungi admin BITC.</h2>
                    <p class="text-muted mt-3" style="line-height:1.85">Mau reservasi secara langsung atau ingin melakukan pembayaran? Tim admin BITC siap membantu, cara tercepat lewat WhatsApp.</p>
                    <a class="btn-hero solid text-decoration-none mt-2" style="background:#25d366; box-shadow:0 12px 26px rgba(37,211,102,.3)"
                       href="https://wa.me/{{ config('institusi.whatsapp') }}?text={{ urlencode('Halo Admin BITC, saya ingin bertanya tentang reservasi fasilitas lewat WADUH.') }}"
                       target="_blank" rel="noopener">
                        <i class="bi bi-whatsapp"></i> Chat WhatsApp Admin BITC
                    </a>
                </div>
                <div class="col-lg-6" data-reveal>
                    <div class="jam-panel">
                        <h3 class="h5 mb-3"><i class="bi bi-headset me-2"></i>Kontak Resmi Gedung BITC</h3>
                        <div class="jam-row"><span><i class="bi bi-whatsapp me-1"></i>WhatsApp Admin</span><b><a class="text-white text-decoration-none" href="https://wa.me/{{ config('institusi.whatsapp') }}" target="_blank" rel="noopener">+{{ config('institusi.whatsapp') }}</a></b></div>
                        <div class="jam-row"><span><i class="bi bi-telephone me-1"></i>Telepon / Faks</span><b>{{ config('institusi.telepon') }}</b></div>
                        <div class="jam-row"><span><i class="bi bi-envelope me-1"></i>Email</span><b>{{ config('institusi.email') }}</b></div>
                        <div class="jam-row" style="border-bottom:0"><span><i class="bi bi-geo-alt me-1"></i>Alamat</span><b class="text-end" style="max-width:60%">Jl. Raya Baros No. 78, Cimahi Selatan</b></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CARA RESERVASI --}}
    <section class="section" style="background:var(--surface)">
        <div class="container">
            <div class="text-center mb-5" data-reveal>
                <p class="eyebrow" style="justify-content:center">Cara Kerja</p>
                <h2>Reservasi dalam 4 langkah singkat.</h2>
            </div>
            <div class="row g-4">
                <div class="col-md-3" data-reveal><div class="xcard step-card h-100"><span class="n" style="background:var(--l1)">1</span><h3 class="h6">Pilih Ruang</h3><p class="small text-muted mb-0">Telusuri kategori & denah per lantai, lihat ketersediaan real-time.</p></div></div>
                <div class="col-md-3" data-reveal><div class="xcard step-card h-100"><span class="n" style="background:var(--l3a)">2</span><h3 class="h6">Atur Jadwal</h3><p class="small text-muted mb-0">Sewa per jam, harian, atau bulanan bisa beberapa ruang sekaligus.</p></div></div>
                <div class="col-md-3" data-reveal><div class="xcard step-card h-100"><span class="n" style="background:var(--l3b)">3</span><h3 class="h6">Checkout</h3><p class="small text-muted mb-0">Isi data diri sekali, unggah dokumen (untuk sewa bulanan), dapat kode reservasi.</p></div></div>
                <div class="col-md-3" data-reveal><div class="xcard step-card h-100"><span class="n" style="background:var(--l5)">4</span><h3 class="h6">Pantau & Gunakan</h3><p class="small text-muted mb-0">Cek status persetujuan dengan kode reservasi, lalu gunakan ruangmu.</p></div></div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="section pt-0" style="background:var(--surface)">
        <div class="container">
            <div class="cta-band d-flex flex-wrap justify-content-between align-items-center gap-3" data-reveal>
                <div>
                    <h2 class="h3 mb-1">Siap pakai ruang di BITC?</h2>
                    <p class="mb-0" style="opacity:.8">Mulai reservasi sekarang</p>
                </div>
                <a href="{{ route('reservasi.index') }}" class="btn-hero solid text-decoration-none" style="background:#fff; color:var(--primary)">Mulai Reservasi <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="d-flex align-items-center gap-2"><span class="brand-mark" style="width:1.7rem;height:1.7rem;border-radius:.55rem;font-size:.85rem"><i class="bi bi-building"></i></span><span class="fw-bold" style="color:var(--ink)">WADUH</span> — Wadah Akses Digital Unit Hunian BITC · UPTD Cimahi Techno Park</span>
        <div class="d-flex gap-3">
            <a class="text-decoration-none text-muted" href="#tentang">Tentang</a>
            <a class="text-decoration-none text-muted" href="#fasilitas">Fasilitas</a>
            <a class="text-decoration-none text-muted" href="#kontak">Kontak</a>
            <a class="text-decoration-none text-muted" href="{{ route('cek-status.form') }}">Cek Status</a>
        </div>
        <span>&copy; {{ now()->year }} WADUH · BITC Cimahi</span>
    </div>
</footer>

<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script>
    const nav = document.getElementById('mainNav');
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 24);
    onScroll(); window.addEventListener('scroll', onScroll, { passive: true });

    const io = new IntersectionObserver(es => es.forEach(e => e.isIntersecting && e.target.classList.add('in')), { threshold: .12 });
    document.querySelectorAll('[data-reveal]').forEach(el => io.observe(el));
</script>
</body>
</html>
