<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | WADUH Admin</title>
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root { --ink:#15243b; --muted:#637189; --primary:#176b87; --primary-dark:#0f526b; --teal:#24aa9a; --surface:#f4f7fa; --line:#e4ebf2; --side:#0e1e31; --side2:#0a1523;
                --l1:#2f7fd1; --l2:#24aa9a; --l3a:#7c5cd6; --l3b:#e8833a; --l5:#d6527c; }
        body {
            font-family:'DM Sans',sans-serif; color:var(--ink); line-height:1.6; -webkit-font-smoothing:antialiased;
            background:
                radial-gradient(40rem 22rem at 105% -10%, #e2f2f0 0%, transparent 55%),
                var(--surface);
        }
        h1,h2,h3,h4,h5,h6,.brand-font { font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-.02em; }
        ::selection { background:#bfe3ea; color:var(--primary-dark); }

        @keyframes riseIn { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
        [data-reveal] { animation:riseIn .5s cubic-bezier(.2,.7,.3,1) both; }
        @media (prefers-reduced-motion: reduce) { [data-reveal] { animation:none; } }

        /* Frame — konsep BARU: sidebar terang, topbar flush (bukan pill mengambang) */
        .frame { display:flex; min-height:100vh; }
        .sidebar { width:268px; flex:none; background:#fff; border-right:1px solid var(--line); color:var(--ink); display:flex; flex-direction:column;
            position:sticky; top:0; height:100vh; overflow-y:auto; z-index:1046; transition:width .22s ease; }
        .sidebar::-webkit-scrollbar { width:6px; } .sidebar::-webkit-scrollbar-thumb { background:#dbe4ec; border-radius:3px; }
        .content { flex:1; min-width:0; display:flex; flex-direction:column; }
        .backdrop { display:none; }
        @media (max-width: 991.98px) {
            .sidebar { position:fixed; left:-280px; transition:left .25s ease; height:100vh; box-shadow:14px 0 40px -20px rgba(15,23,42,.25); }
            .sidebar.open { left:0; }
            .backdrop { display:none; position:fixed; inset:0; background:rgba(8,15,25,.5); backdrop-filter:blur(2px); z-index:1044; opacity:0; transition:opacity .25s ease; }
            .backdrop.show { display:block; opacity:1; }
        }

        /* Sidebar diciutkan (desktop) — hanya ikon, teks & submenu disembunyikan. */
        @media (min-width: 992px) {
            body.sidebar-collapsed .sidebar { width:84px; }
            body.sidebar-collapsed .side-brand { padding:1.5rem .5rem 1.2rem; justify-content:center; }
            body.sidebar-collapsed .side-brand .brand-font { display:none; }
            body.sidebar-collapsed .side-section { display:none; }
            body.sidebar-collapsed .side-link { justify-content:center; padding:.62rem; }
            body.sidebar-collapsed .side-link .lbl,
            body.sidebar-collapsed .side-link .caret,
            body.sidebar-collapsed .side-link .badge { display:none; }
            body.sidebar-collapsed .side-sub { display:none !important; }
        }
        .collapse-toggle-btn { border:none; background:var(--surface); border-radius:.7rem; padding:.35rem .6rem; transition:background .15s, color .15s; flex:none; color:#4b5a6e; }
        .collapse-toggle-btn:hover { background:#e9eff4; color:var(--primary-dark); }
        .collapse-toggle-btn i { transition:transform .22s ease; }
        body.sidebar-collapsed .collapse-toggle-btn i { transform:rotate(180deg); }

        /* Sidebar */
        /* Logo (lockup memanjang) di baris sendiri di atas, nama+tagline di bawahnya —
           dipisah jadi dua baris supaya logo tidak berebut ruang sempit dengan teks. */
        .side-brand { display:flex; flex-direction:column; align-items:flex-start; gap:.6rem; padding:1.4rem 1.4rem 1.15rem; color:var(--ink); text-decoration:none; font-weight:800; font-size:1.05rem; border-bottom:1px solid var(--line); margin-bottom:.6rem; }
        .side-brand .brand-mark { display:block; max-width:100%; }
        .side-brand .brand-mark img { height:auto; width:100%; max-width:7rem; display:block; }
        .side-brand .brand-font { line-height:1.3; }
        .side-brand small { display:block; font-size:.6rem; font-weight:600; color:var(--muted); letter-spacing:.09em; text-transform:uppercase; margin-top:.1rem; }
        /* Sidebar ciut — hanya logo yang tersisa, dipusatkan dan dibatasi lebarnya. */
        body.sidebar-collapsed .side-brand { align-items:center; }
        body.sidebar-collapsed .side-brand .brand-mark img { max-width:3.2rem; }
        .side-section { padding:1rem 1.4rem .5rem; font-size:.62rem; font-weight:800; letter-spacing:.14em; text-transform:uppercase; color:#94a3b8; }
        .side-nav { list-style:none; margin:0; padding:0 .85rem; display:flex; flex-direction:column; gap:.2rem; }
        .side-link { position:relative; display:flex; align-items:center; gap:.7rem; padding:.62rem .8rem; color:#4b5a6e; text-decoration:none; border-radius:.85rem; font-weight:600; font-size:.88rem; transition:background .18s ease, color .18s ease; }
        .side-link::before { content:''; position:absolute; left:-.85rem; top:50%; translate:0 -50%; width:3px; height:0; background:linear-gradient(180deg,var(--primary),var(--teal)); border-radius:0 3px 3px 0; transition:height .18s ease; }
        .side-link .mic { display:grid; place-items:center; width:2rem; height:2rem; border-radius:.65rem; font-size:.92rem; flex:none; color:#7c8ba1; background:var(--surface); transition:all .18s ease; }
        .side-link:hover { color:var(--primary-dark); background:var(--surface); }
        .side-link.active { color:var(--primary-dark); background:linear-gradient(90deg,rgba(23,107,135,.1),rgba(36,170,154,.05)); }
        .side-link.active::before { height:1.35rem; }
        .side-link.active .mic { color:#fff; background:linear-gradient(135deg,var(--primary),var(--teal)); box-shadow:0 6px 14px -4px rgba(23,107,135,.45); }
        .side-link .caret { margin-left:auto; transition:transform .2s; font-size:.68rem; opacity:.6; }
        .side-link[aria-expanded="true"] .caret { transform:rotate(90deg); }
        .side-sub { list-style:none; margin:.15rem 0 .4rem; padding:0 0 0 2.35rem; }
        .side-sub a { display:flex; align-items:center; gap:.55rem; padding:.42rem .75rem; margin:.05rem 0; color:#8290a3; text-decoration:none; border-left:2px solid var(--line); border-radius:0 .65rem .65rem 0; font-size:.82rem; font-weight:500; transition:all .18s ease; }
        .side-sub a:hover { color:var(--primary-dark); background:var(--surface); }
        .side-sub a.active { color:var(--primary-dark); border-left-color:var(--teal); background:var(--primary-soft, #e6f2f4); font-weight:700; }
        .side-sub .badge { margin-left:auto; }
        .side-bottom { margin-top:auto; }

        /* Topbar — flush, tanpa margin/rounded, menyatu dengan tepi konten */
        .topbar { background:rgba(255,255,255,.92); backdrop-filter:blur(14px); border-bottom:1px solid var(--line);
            padding:1rem 1.6rem; display:flex; align-items:center; gap:.9rem; position:sticky; top:0; z-index:1020; }
        .topbar h1 { font-size:1.1rem; font-weight:800; margin:0; }
        .topbar .crumb { font-size:.7rem; color:var(--muted); font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
        .burger { border:none; background:var(--surface); border-radius:.7rem; padding:.35rem .6rem; transition:background .15s, color .15s; flex:none; color:#4b5a6e; }
        .burger:hover { background:#e9eff4; color:var(--primary-dark); }

        /* Bel notifikasi */
        .bell-btn { position:relative; border:none; background:var(--surface); border-radius:.75rem; width:2.5rem; height:2.5rem; display:grid; place-items:center; color:#4b5a6e; transition:background .15s, color .15s; flex:none; }
        .bell-btn:hover { background:#e9eff4; color:var(--primary); }
        .bell-btn .dot { position:absolute; top:-.3rem; right:-.3rem; min-width:1.15rem; height:1.15rem; padding:0 .3rem; border-radius:2rem; background:#e5b94e; color:#3a2c00; font-size:.62rem; font-weight:800; display:grid; place-items:center; border:2px solid #fff; }

        .day-chip { align-items:center; background:var(--surface); color:#4b5a6e; font-size:.78rem; font-weight:700; padding:.45rem .85rem; border-radius:.75rem; }

        /* Profil admin (dropdown) */
        .profile-btn { display:flex; align-items:center; gap:.6rem; border:none; background:var(--surface); border-radius:.9rem; padding:.35rem .7rem .35rem .35rem; transition:background .15s; }
        .profile-btn:hover { background:#e9eff4; }
        .profile-btn .avatar { display:grid; place-items:center; width:2.15rem; height:2.15rem; border-radius:.7rem; background:linear-gradient(135deg,var(--primary),var(--teal)); color:#fff; font-weight:800; font-size:.85rem; flex:none; }
        .profile-btn .who { text-align:left; line-height:1.2; }
        .profile-btn .who .nm { font-size:.82rem; font-weight:700; color:var(--ink); max-width:9rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .profile-btn .who .rl { font-size:.68rem; color:var(--muted); }
        .profile-menu { border:1px solid var(--line); border-radius:1rem; box-shadow:0 18px 40px -14px rgba(21,36,59,.25); padding:.5rem; min-width:230px; }
        .profile-menu .pm-head { display:flex; align-items:center; gap:.65rem; padding:.55rem .6rem .75rem; border-bottom:1px solid var(--line); margin-bottom:.4rem; }
        .profile-menu .pm-head .avatar { display:grid; place-items:center; width:2.4rem; height:2.4rem; border-radius:.8rem; background:linear-gradient(135deg,var(--primary),var(--teal)); color:#fff; font-weight:800; flex:none; }
        .profile-menu .pm-head .nm { font-weight:700; font-size:.85rem; color:var(--ink); }
        .profile-menu .pm-head .em { font-size:.72rem; color:var(--muted); word-break:break-all; }
        .profile-menu .dropdown-item { border-radius:.65rem; padding:.5rem .6rem; font-size:.85rem; font-weight:600; color:#3c4a5f; }
        .profile-menu .dropdown-item:hover { background:var(--surface); color:var(--primary-dark); }
        .profile-menu .dropdown-item.text-danger:hover { background:#fdf1f1; color:#c02929 !important; }

        main.inner { padding:1.5rem 1.6rem 1.75rem; }

        /* Components */
        .xcard { background:#fff; border:1px solid var(--line); border-radius:1.35rem; box-shadow:0 4px 18px -4px rgba(21,60,73,.07); transition:box-shadow .2s ease, transform .2s ease; }
        .xcard .xhead { padding:1.05rem 1.3rem; border-bottom:1px solid var(--line); font-weight:700; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.5rem; background:#fbfdfe; border-radius:1.35rem 1.35rem 0 0; }
        .stat-card { border-radius:1.35rem; border:none; color:#fff; padding:1.3rem 1.4rem; position:relative; overflow:hidden; box-shadow:0 16px 32px -10px rgba(21,36,59,.24); transition:transform .2s ease, box-shadow .2s ease; }
        .stat-card:hover { transform:translateY(-3px); box-shadow:0 20px 38px -10px rgba(21,36,59,.28); }
        .stat-card::after { content:''; position:absolute; right:-2.2rem; bottom:-2.6rem; width:8rem; height:8rem; border-radius:50%; background:rgba(255,255,255,.12); }
        .stat-card .ic { position:absolute; right:1.1rem; top:1.1rem; font-size:1.5rem; opacity:.85; }
        .stat-card .v { font-size:1.85rem; font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; line-height:1.1; }
        .stat-card small { opacity:.88; font-weight:600; letter-spacing:.01em; }
        /* Tabel modern — kontras jelas antara header, baris, dan latar */
        .table { --bs-table-hover-bg:#eef7fa; margin-bottom:0; }
        .table thead th { background:linear-gradient(180deg,#eaf2f7,#e2edf4) !important; color:#1f4256; font-size:.73rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; padding:.85rem 1.1rem; border-bottom:2px solid #cfe0ea; white-space:nowrap; }
        .table td { vertical-align:middle; padding:1rem 1.1rem; border-color:#e7eef5; background:#fff; font-size:.885rem; border-left:0; border-right:0; }
        .table tbody tr:nth-child(even) td { background:#f8fbfd; }
        .table tbody tr:hover td { background:var(--bs-table-hover-bg); }
        .table tbody tr:last-child td { border-bottom:0; }
        .table tbody tr { transition:background .15s ease; }

        /* Chip status lembut — lebih enak dilihat daripada badge pekat */
        .chip { display:inline-flex; align-items:center; gap:.4rem; font-size:.74rem; font-weight:700; padding:.36rem .8rem; border-radius:2rem; white-space:nowrap; letter-spacing:.01em; }
        .chip::before { content:''; width:.5rem; height:.5rem; border-radius:50%; background:currentColor; }
        .chip.menunggu { background:#fff4d6; color:#9a6b00; }
        .chip.disetujui { background:#e2f7ef; color:#0d8a5f; }
        .chip.ditolak { background:#fde4e4; color:#c02929; }
        .chip.dibatalkan { background:#e9edf1; color:#495663; }
        .chip.selesai { background:#eef1f4; color:#5d6b7a; }
        .initial-chip { display:inline-grid; place-items:center; width:2.2rem; height:2.2rem; border-radius:.75rem; color:#fff; font-weight:700; font-size:.85rem; background:linear-gradient(135deg,var(--primary),var(--teal)); flex:none; box-shadow:0 6px 14px -4px rgba(23,107,135,.4); }
        .cell-main { font-weight:600; }
        .cell-sub { font-size:.78rem; color:var(--muted); }
        .btn-brand { background:linear-gradient(135deg,var(--primary),var(--primary-dark)); border-color:var(--primary); color:#fff; font-weight:700; border-radius:.8rem; transition:background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
        .btn-brand:hover { border-color:var(--primary-dark); color:#fff; box-shadow:0 8px 18px -6px rgba(23,107,135,.45); transform:translateY(-1px); }
        .btn-brand-outline { color:var(--primary); border:1.5px solid #c3d6de; border-radius:.8rem; font-weight:600; background:#fff; transition:all .15s ease; }
        .btn-brand-outline:hover { color:var(--primary-dark); border-color:var(--primary); background:#f2fafb; transform:translateY(-1px); }
        .form-control,.form-select { border-radius:.7rem; border-color:#d8e2ea; }
        .form-control:focus,.form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(23,107,135,.12); }
        .form-label { font-weight:600; font-size:.82rem; color:#3c4a5f; }
        .avail { display:inline-flex; align-items:center; gap:.35rem; font-size:.75rem; font-weight:700; padding:.3rem .65rem; border-radius:2rem; }
        .avail.hijau { background:#e2f7ef; color:#0d8a5f; }
        .avail.kuning { background:#fff4d6; color:#9a6b00; }
        .avail.merah { background:#fde4e4; color:#c02929; }

        /* Validasi klien ramah */
        .is-salah { border-color:#d95757 !important; background:#fffafa !important; box-shadow:0 0 0 .18rem rgba(217,87,87,.12) !important; animation:goyang .3s; }
        @keyframes goyang { 25% { transform:translateX(-4px); } 75% { transform:translateX(4px); } }
        .catatan-salah { display:flex; align-items:center; gap:.3rem; color:#c02929; font-size:.78rem; font-weight:600; margin-top:.3rem; }
    </style>
</head>
<body>
<script>
    // Terapkan status ciut/lebar sidebar sebelum konten dirender, biar tidak "kedip" saat load.
    if (localStorage.getItem('adminSidebarCollapsed') === '1') document.body.classList.add('sidebar-collapsed');
</script>
@php
    $me = Auth::guard('admin')->user();
    $sideLantai = \App\Models\Lantai::orderBy('id_lantai')->get(['id_lantai', 'nomor_lantai']);
    $menungguN = \App\Models\Reservasi::where('status_reservasi', 'Menunggu')->count();
    $warnaLantai = ['1' => '#2f7fd1', '2' => '#24aa9a', '3A' => '#7c5cd6', '3B' => '#e8833a', '5' => '#d6527c'];
    $warnaStatus = ['Menunggu' => '#e5b94e', 'Disetujui' => '#25b47e', 'Ditolak' => '#d95757', 'Dibatalkan' => '#495663', 'Selesai' => '#8a97a5'];
    $curStatus = request('status');
    $curLantai = request('lantai');
@endphp
<div class="frame">
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('admin.dashboard') }}" class="side-brand">
            <span class="brand-mark"><img src="{{ asset('images/logo_bitc_crop.png') }}" alt="Logo BITC"></span>
            <span class="brand-font">BITC<span style="color:var(--teal)">.</span><small>Panel Admin</small></span>
        </a>

        <div class="side-section">Menu Utama</div>
        <ul class="side-nav">
            <li>
                <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}" title="Dashboard">
                    <span class="mic"><i class="bi bi-speedometer2"></i></span> <span class="lbl">Dashboard</span>
                </a>
            </li>

            <li>
                <a class="side-link {{ request()->routeIs('admin.monitoring*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#subMonitoring"
                   aria-expanded="{{ request()->routeIs('admin.monitoring*') ? 'true' : 'false' }}" title="Monitoring">
                    <span class="mic"><i class="bi bi-grid-3x3-gap"></i></span>
                    <span class="lbl">Monitoring</span> <i class="bi bi-chevron-right caret"></i>
                </a>
                <ul class="side-sub collapse {{ request()->routeIs('admin.monitoring*') ? 'show' : '' }}" id="subMonitoring">
                    @foreach ($sideLantai as $l)
                        <li><a class="{{ request()->routeIs('admin.monitoring') && (string)$curLantai === (string)$l->id_lantai ? 'active' : '' }}"
                               href="{{ route('admin.monitoring', ['lantai' => $l->id_lantai]) }}">Lantai {{ $l->nomor_lantai }}</a></li>
                    @endforeach
                </ul>
            </li>

            <li>
                <a class="side-link {{ request()->routeIs('admin.reservasi*') ? 'active' : '' }}" href="{{ route('admin.reservasi.index') }}" title="Data Reservasi">
                    <span class="mic"><i class="bi bi-journal-check"></i></span>
                    <span class="lbl">Data Reservasi</span>
                    @if ($menungguN > 0)<span class="badge rounded-pill text-bg-warning ms-auto">{{ $menungguN }}</span>@endif
                </a>
            </li>

            <li>
                <a class="side-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}" href="{{ route('admin.laporan') }}" title="Laporan">
                    <span class="mic"><i class="bi bi-file-earmark-bar-graph"></i></span>
                    <span class="lbl">Laporan</span>
                </a>
            </li>

            <li>
                <a class="side-link {{ request()->routeIs('admin.profil*') ? 'active' : '' }}" href="{{ route('admin.profil') }}" title="Profil">
                    <span class="mic"><i class="bi bi-person-circle"></i></span>
                    <span class="lbl">Profil</span>
                </a>
            </li>
        </ul>

        <div class="side-bottom">
            <div class="side-nav" style="padding-top:.3rem;margin-top:.3rem;border-top:1px solid var(--line)">
                <form method="POST" action="{{ route('admin.logout') }}" data-confirm="Keluar dari panel admin?" data-icon="question">@csrf
                    <button type="submit" class="side-link w-100 text-start border-0 bg-transparent" style="color:#c02929" title="Keluar">
                        <span class="mic" style="color:#c02929"><i class="bi bi-box-arrow-right"></i></span>
                        <span class="lbl">Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>
    <div class="backdrop" id="backdrop"></div>

    <div class="content">
        <div class="topbar">
            <button class="burger d-lg-none" id="burgerBtn"><i class="bi bi-list"></i></button>
            <button class="collapse-toggle-btn d-none d-lg-inline-flex" id="collapseBtn" title="Ciutkan/lebarkan sidebar"><i class="bi bi-layout-sidebar-inset"></i></button>
            <button class="burger" onclick="history.back()" title="Kembali ke halaman sebelumnya"><i class="bi bi-arrow-left"></i></button>
            <div>
                <div class="crumb">WADUH Admin</div>
                <h1>@yield('title', 'Admin')</h1>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                @yield('actions')
                <span class="day-chip d-none d-md-inline-flex"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('d M Y') }}</span>

                <a href="{{ route('admin.reservasi.index', ['status' => 'Menunggu']) }}" class="bell-btn" title="Reservasi menunggu persetujuan">
                    <i class="bi bi-bell"></i>
                    @if ($menungguN > 0)<span class="dot">{{ $menungguN > 99 ? '99+' : $menungguN }}</span>@endif
                </a>

                @php $fotoAdmin = $me?->fotoUrl(); @endphp
                <div class="dropdown">
                    <button class="profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar">
                            @if ($fotoAdmin)<img src="{{ $fotoAdmin }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">
                            @else{{ strtoupper(substr($me?->nama_admin ?? 'A', 0, 1)) }}@endif
                        </span>
                        <span class="who d-none d-md-block">
                            <div class="nm">{{ $me?->nama_admin }}</div>
                            <div class="rl">Administrator</div>
                        </span>
                        <i class="bi bi-chevron-down small d-none d-md-inline" style="color:var(--muted)"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end profile-menu">
                        <div class="pm-head">
                            <span class="avatar">
                                @if ($fotoAdmin)<img src="{{ $fotoAdmin }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">
                                @else{{ strtoupper(substr($me?->nama_admin ?? 'A', 0, 1)) }}@endif
                            </span>
                            <div><div class="nm">{{ $me?->nama_admin }}</div><div class="em">{{ $me?->email }}</div></div>
                        </div>
                        <a href="{{ route('admin.profil') }}" class="dropdown-item w-100 text-start"><i class="bi bi-person-circle me-2"></i>Lihat Profil</a>
                    </div>
                </div>
            </div>
        </div>

        <main class="inner">
            @if ($errors->any())
                <div class="d-flex gap-3 align-items-start p-3 mb-3 rounded-4" style="background:linear-gradient(120deg,#fdf1f1,#fff7f4); border:1px solid #f0c9c9; border-left:4px solid #d95757;">
                    <span style="display:grid;place-items:center;flex:none;width:2.5rem;height:2.5rem;border-radius:.8rem;background:#fbdddd;color:#c02929;font-size:1.2rem"><i class="bi bi-exclamation-triangle"></i></span>
                    <div>
                        <div class="fw-bold" style="color:#a12c2c">Periksa kembali, terdapat {{ $errors->count() }} isian belum benar</div>
                        <ul class="mb-0 mt-1 small" style="color:#7c3a3a; list-style:none; padding:0">
                            @foreach ($errors->all() as $e)<li><i class="bi bi-arrow-right-short" style="color:#d95757"></i>{{ $e }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
<script>
    // Sidebar mobile: buka/tutup lewat tombol burger atau tap di luar (backdrop).
    (() => {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('backdrop');
        const burger = document.getElementById('burgerBtn');
        const buka = () => { sidebar.classList.add('open'); backdrop.classList.add('show'); };
        const tutup = () => { sidebar.classList.remove('open'); backdrop.classList.remove('show'); };
        burger.addEventListener('click', () => sidebar.classList.contains('open') ? tutup() : buka());
        backdrop.addEventListener('click', tutup);
        sidebar.querySelectorAll('a:not([data-bs-toggle])').forEach(a => a.addEventListener('click', tutup));
    })();

    // Sidebar desktop: ciutkan/lebarkan, status disimpan supaya tetap sama di halaman berikutnya.
    (() => {
        const collapseBtn = document.getElementById('collapseBtn');
        const setCollapsed = (on) => {
            document.body.classList.toggle('sidebar-collapsed', on);
            localStorage.setItem('adminSidebarCollapsed', on ? '1' : '0');
        };
        collapseBtn.addEventListener('click', () => setCollapsed(! document.body.classList.contains('sidebar-collapsed')));

        // Kalau sidebar sedang ciut, klik menu yang punya submenu (mis. Monitoring) akan
        // melebarkan sidebar dulu supaya submenu-nya kelihatan, bukan cuma toggle tak terlihat.
        document.querySelectorAll('#sidebar [data-bs-toggle="collapse"]').forEach(a => {
            a.addEventListener('click', () => {
                if (document.body.classList.contains('sidebar-collapsed')) setCollapsed(false);
            });
        });
    })();

    // Pop-up flash message — semua pakai modal penuh (bukan toast kecil di pojok).
    @if (session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), confirmButtonColor: '#176b87', confirmButtonText: 'Oke' });
    @endif
    @if (session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: @json(session('error')), confirmButtonColor: '#176b87', confirmButtonText: 'Oke, mengerti' });
    @endif

    // ===== Validasi klien ramah (mengganti bubble bawaan browser) =====
    const labelDari = el => {
        const wadah = el.closest('.mb-3, .mb-2, [class*="col-"]') || el.parentElement;
        const lbl = wadah?.querySelector('.form-label');
        return lbl ? lbl.textContent.replace('*', '').trim() : 'Kolom ini';
    };
    const pesanSalah = el => {
        const v = el.validity;
        if (v.valueMissing) return labelDari(el) + ' belum diisi.';
        if (v.typeMismatch && el.type === 'email') return 'Format email belum benar.';
        if (v.rangeUnderflow) return labelDari(el) + ' minimal ' + el.min + '.';
        if (v.rangeOverflow) return labelDari(el) + ' maksimal ' + el.max + '.';
        return labelDari(el) + ' belum sesuai format.';
    };
    const tandai = el => {
        el.classList.add('is-salah');
        const induk = el.closest('.input-group') || el;
        induk.parentElement.querySelector(':scope > .catatan-salah')?.remove();
        const note = document.createElement('div');
        note.className = 'catatan-salah';
        note.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i>' + pesanSalah(el);
        induk.insertAdjacentElement('afterend', note);
    };
    const bersihkan = el => {
        el.classList.remove('is-salah');
        (el.closest('.input-group') || el).parentElement.querySelector(':scope > .catatan-salah')?.remove();
    };
    document.querySelectorAll('form').forEach(f => {
        f.setAttribute('novalidate', '');
        f.addEventListener('submit', e => {
            const salah = [...f.querySelectorAll('input, select, textarea')].filter(el => ! el.disabled && ! el.checkValidity());
            if (! salah.length) return;
            e.preventDefault();
            e.stopImmediatePropagation();
            salah.forEach(tandai);
            salah[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => salah[0].focus({ preventScroll: true }), 350);
            Swal.fire({ icon: 'warning', title: 'Periksa kembali', text: salah.length + ' isian belum lengkap.', confirmButtonColor: '#176b87', confirmButtonText: 'Oke' });
        }, true);
        f.addEventListener('input', e => bersihkan(e.target), true);
        f.addEventListener('change', e => bersihkan(e.target), true);
    });
    @if ($errors->any())
        Swal.fire({ icon: 'warning', title: 'Periksa kembali', text: '{{ $errors->count() }} isian belum benar.', confirmButtonColor: '#176b87', confirmButtonText: 'Oke' });
    @endif

    // Dialog konfirmasi untuk form/tautan ber-atribut data-confirm — didelegasikan ke document
    // (bukan dipasang per elemen) supaya otomatis berlaku juga untuk konten yang disisipkan
    // belakangan lewat AJAX (mis. hasil filter interaktif), tanpa perlu pasang ulang listener.
    document.addEventListener('submit', e => {
        const f = e.target.closest('form[data-confirm]');
        if (!f || f.dataset.confirmed) return;
        e.preventDefault();
        Swal.fire({
            title: f.dataset.confirmTitle || 'Yakin?',
            text: f.dataset.confirm,
            icon: f.dataset.icon || 'question',
            showCancelButton: true,
            confirmButtonText: f.dataset.confirmText || 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: f.dataset.confirmColor || '#176b87',
            cancelButtonColor: '#8a97a5',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) { f.dataset.confirmed = 1; f.submit(); } });
    });

    document.addEventListener('click', e => {
        const a = e.target.closest('a[data-confirm]');
        if (!a) return;
        e.preventDefault();
        Swal.fire({
            title: a.dataset.confirmTitle || 'Yakin?',
            text: a.dataset.confirm,
            icon: a.dataset.icon || 'question',
            showCancelButton: true,
            confirmButtonText: a.dataset.confirmText || 'Ya, lanjutkan',
            cancelButtonText: 'Batal',
            confirmButtonColor: a.dataset.confirmColor || '#176b87',
            cancelButtonColor: '#8a97a5',
            reverseButtons: true,
        }).then(r => { if (r.isConfirmed) window.location.href = a.href; });
    });
</script>
</body>
</html>
