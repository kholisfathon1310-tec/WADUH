<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') | WADUH Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --ink:#15243b; --muted:#637189; --primary:#176b87; --primary-dark:#0f526b; --teal:#24aa9a; --surface:#f4f7fa; --line:#e4ebf2; --side:#0e1e31; --side2:#0a1523;
                --l1:#2f7fd1; --l2:#24aa9a; --l3a:#7c5cd6; --l3b:#e8833a; --l5:#d6527c; }
        body { font-family:'DM Sans',sans-serif; color:var(--ink); background:var(--surface); }
        h1,h2,h3,h4,h5,h6,.brand-font { font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-.02em; }

        /* Frame */
        .frame { display:flex; min-height:100vh; }
        .sidebar { width:268px; flex:none; background:linear-gradient(180deg,var(--side),var(--side2)); color:#cbd5e1; display:flex; flex-direction:column; position:sticky; top:0; height:100vh; overflow-y:auto; }
        .sidebar::-webkit-scrollbar { width:6px; } .sidebar::-webkit-scrollbar-thumb { background:#26405c; border-radius:3px; }
        .content { flex:1; min-width:0; display:flex; flex-direction:column; }
        @media (max-width: 991.98px) { .sidebar { position:fixed; z-index:1045; left:-280px; transition:left .25s; height:100vh; } .sidebar.open { left:0; box-shadow:0 0 0 100vmax rgba(8,15,25,.55); } }

        /* Sidebar */
        .side-brand { display:flex; align-items:center; gap:.65rem; padding:1.2rem 1.25rem; color:#fff; text-decoration:none; font-weight:800; font-size:1.1rem; }
        .side-brand .brand-mark { display:inline-grid; width:2.35rem; height:2.35rem; place-items:center; color:#fff; background:linear-gradient(135deg,var(--primary),var(--teal)); border-radius:.7rem; box-shadow:0 8px 18px rgba(23,107,135,.4); }
        .side-brand small { display:block; font-size:.6rem; font-weight:600; color:#7f93ab; letter-spacing:.1em; text-transform:uppercase; }
        .side-section { padding:1rem 1.35rem .4rem; font-size:.64rem; font-weight:700; letter-spacing:.16em; text-transform:uppercase; color:#546b84; }
        .side-nav { list-style:none; margin:0; padding:0 .8rem; }
        .side-link { display:flex; align-items:center; gap:.7rem; padding:.55rem .7rem; margin:.14rem 0; color:#c3cfdc; text-decoration:none; border-radius:.7rem; font-weight:600; font-size:.9rem; transition:all .15s; }
        .side-link .mic { display:grid; place-items:center; width:2rem; height:2rem; border-radius:.6rem; font-size:.95rem; flex:none; color:#8fb7c9; background:rgba(255,255,255,.06); transition:all .15s; }
        .side-link:hover { color:#fff; background:rgba(255,255,255,.06); }
        .side-link.active { color:#fff; background:rgba(255,255,255,.08); }
        .side-link.active .mic { color:#fff; background:linear-gradient(135deg,var(--primary),var(--teal)); box-shadow:0 6px 16px rgba(10,40,55,.4); }
        .side-link .caret { margin-left:auto; transition:transform .2s; font-size:.72rem; opacity:.7; }
        .side-link[aria-expanded="true"] .caret { transform:rotate(90deg); }
        .side-sub { list-style:none; margin:.15rem 0 .45rem; padding:0 0 0 2.35rem; }
        .side-sub a { display:flex; align-items:center; gap:.55rem; padding:.42rem .8rem; margin:.08rem 0; color:#93a5ba; text-decoration:none; border-left:2px solid rgba(255,255,255,.1); border-radius:0 .55rem .55rem 0; font-size:.85rem; font-weight:500; transition:all .15s; }
        .side-sub a:hover { color:#fff; background:rgba(255,255,255,.04); }
        .side-sub a.active { color:#fff; border-left-color:var(--teal); background:rgba(255,255,255,.06); font-weight:700; }
        .side-sub .badge { margin-left:auto; }
        .side-foot { margin-top:auto; padding:1rem 1.1rem 1.2rem; }
        .admin-card { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.08); border-radius:1rem; padding:.9rem; }
        .admin-chip { display:flex; align-items:center; gap:.6rem; margin-bottom:.75rem; }
        .admin-chip .avatar { display:grid; place-items:center; width:2.4rem; height:2.4rem; border-radius:.8rem; background:linear-gradient(135deg,var(--primary),var(--teal)); color:#fff; font-weight:800; }
        .admin-chip small { color:#7f93ab; font-size:.72rem; }

        /* Topbar */
        .topbar { background:rgba(255,255,255,.92); backdrop-filter:blur(12px); border-bottom:1px solid var(--line); padding:.8rem 1.5rem; display:flex; align-items:center; gap:1rem; position:sticky; top:0; z-index:1020; }
        .topbar h1 { font-size:1.12rem; font-weight:800; margin:0; }
        .topbar .crumb { font-size:.72rem; color:var(--muted); font-weight:600; letter-spacing:.08em; text-transform:uppercase; }
        .burger { border:1px solid var(--line); background:#fff; border-radius:.55rem; padding:.35rem .6rem; }

        main.inner { padding:1.5rem; }

        /* Components */
        .xcard { background:#fff; border:1px solid var(--line); border-radius:1.1rem; }
        .xcard .xhead { padding:.9rem 1.15rem; border-bottom:1px solid var(--line); font-weight:700; display:flex; justify-content:space-between; align-items:center; gap:.5rem; background:#fbfdfe; border-radius:1.1rem 1.1rem 0 0; }
        .stat-card { border-radius:1.1rem; border:none; color:#fff; padding:1.15rem 1.25rem; position:relative; overflow:hidden; box-shadow:0 12px 26px rgba(21,36,59,.14); }
        .stat-card::after { content:''; position:absolute; right:-2.2rem; bottom:-2.6rem; width:8rem; height:8rem; border-radius:50%; background:rgba(255,255,255,.12); }
        .stat-card .ic { position:absolute; right:1rem; top:1rem; font-size:1.5rem; opacity:.85; }
        .stat-card .v { font-size:1.8rem; font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; line-height:1.1; }
        .stat-card small { opacity:.85; font-weight:600; }
        /* Tabel modern — kontras jelas antara header, baris, dan latar */
        .table { --bs-table-hover-bg:#eef7fa; margin-bottom:0; }
        .table thead th { background:linear-gradient(180deg,#eaf2f7,#e2edf4) !important; color:#1f4256; font-size:.73rem; font-weight:800; text-transform:uppercase; letter-spacing:.07em; padding:.75rem 1rem; border-bottom:2px solid #cfe0ea; white-space:nowrap; }
        .table td { vertical-align:middle; padding:.9rem 1rem; border-color:#e7eef5; background:#fff; font-size:.885rem; border-left:0; border-right:0; }
        .table tbody tr:nth-child(even) td { background:#f8fbfd; }
        .table tbody tr:hover td { background:var(--bs-table-hover-bg); }
        .table tbody tr:last-child td { border-bottom:0; }
        .table tbody tr { transition:background .12s; }
        .xcard { box-shadow:0 3px 14px rgba(21,36,59,.06); }

        /* Chip status lembut — lebih enak dilihat daripada badge pekat */
        .chip { display:inline-flex; align-items:center; gap:.4rem; font-size:.74rem; font-weight:700; padding:.34rem .75rem; border-radius:2rem; white-space:nowrap; }
        .chip::before { content:''; width:.5rem; height:.5rem; border-radius:50%; background:currentColor; }
        .chip.menunggu { background:#fff4d6; color:#9a6b00; }
        .chip.disetujui { background:#e2f7ef; color:#0d8a5f; }
        .chip.ditolak { background:#fde4e4; color:#c02929; }
        .chip.dibatalkan { background:#e9edf1; color:#495663; }
        .chip.selesai { background:#eef1f4; color:#5d6b7a; }
        .initial-chip { display:inline-grid; place-items:center; width:2.15rem; height:2.15rem; border-radius:.7rem; color:#fff; font-weight:700; font-size:.85rem; background:linear-gradient(135deg,var(--primary),var(--teal)); flex:none; }
        .cell-main { font-weight:600; }
        .cell-sub { font-size:.78rem; color:var(--muted); }
        .btn-brand { background:var(--primary); border-color:var(--primary); color:#fff; font-weight:700; border-radius:.6rem; }
        .btn-brand:hover { background:var(--primary-dark); border-color:var(--primary-dark); color:#fff; }
        .btn-brand-outline { color:var(--primary); border:1.5px solid #c3d6de; border-radius:.6rem; font-weight:600; background:#fff; }
        .btn-brand-outline:hover { color:var(--primary-dark); border-color:var(--primary); background:#f2fafb; }
        .form-control,.form-select { border-radius:.55rem; border-color:#d8e2ea; }
        .form-control:focus,.form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(23,107,135,.12); }
        .form-label { font-weight:600; font-size:.82rem; color:#3c4a5f; }
        .avail { display:inline-flex; align-items:center; gap:.35rem; font-size:.75rem; font-weight:700; padding:.28rem .6rem; border-radius:2rem; }
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
            <span class="brand-mark"><i class="bi bi-building"></i></span>
            <span class="brand-font">WADUH<span style="color:var(--teal)">.</span><small>Wadah Akses Digital Unit Hunian</small></span>
        </a>

        <div class="side-section">Menu Utama</div>
        <ul class="side-nav">
            <li>
                <a class="side-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                    <span class="mic"><i class="bi bi-speedometer2"></i></span> Dashboard
                </a>
            </li>

            <li>
                <a class="side-link {{ request()->routeIs('admin.monitoring*') ? 'active' : '' }}" data-bs-toggle="collapse" href="#subMonitoring"
                   aria-expanded="{{ request()->routeIs('admin.monitoring*') ? 'true' : 'false' }}">
                    <span class="mic"><i class="bi bi-grid-3x3-gap"></i></span>
                    Monitoring <i class="bi bi-chevron-right caret"></i>
                </a>
                <ul class="side-sub collapse {{ request()->routeIs('admin.monitoring*') ? 'show' : '' }}" id="subMonitoring">
                    @foreach ($sideLantai as $l)
                        <li><a class="{{ request()->routeIs('admin.monitoring') && (string)$curLantai === (string)$l->id_lantai ? 'active' : '' }}"
                               href="{{ route('admin.monitoring', ['lantai' => $l->id_lantai]) }}">Lantai {{ $l->nomor_lantai }}</a></li>
                    @endforeach
                </ul>
            </li>

            <li>
                <a class="side-link {{ request()->routeIs('admin.reservasi*') ? 'active' : '' }}" href="{{ route('admin.reservasi.index') }}">
                    <span class="mic"><i class="bi bi-journal-check"></i></span>
                    Data Reservasi
                    @if ($menungguN > 0)<span class="badge rounded-pill text-bg-warning ms-auto">{{ $menungguN }}</span>@endif
                </a>
            </li>

            <li>
                <a class="side-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}" href="{{ route('admin.laporan') }}">
                    <span class="mic"><i class="bi bi-file-earmark-bar-graph"></i></span>
                    Laporan
                </a>
            </li>
        </ul>

        <div class="side-foot">
            <div class="admin-card">
                <div class="admin-chip">
                    <span class="avatar">{{ strtoupper(substr($me?->nama_admin ?? 'A', 0, 1)) }}</span>
                    <div><div class="fw-bold text-white small">{{ $me?->nama_admin }}</div><small>{{ $me?->email }}</small></div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}" data-confirm="Keluar dari panel admin?" data-icon="question">@csrf
                    <button class="btn btn-sm btn-outline-light w-100"><i class="bi bi-box-arrow-right me-1"></i>Keluar</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="content">
        <div class="topbar">
            <button class="burger d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
            <button class="burger" onclick="history.back()" title="Kembali ke halaman sebelumnya"><i class="bi bi-arrow-left"></i></button>
            <div>
                <div class="crumb">WADUH Admin</div>
                <h1>@yield('title', 'Admin')</h1>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                @yield('actions')
                <span class="badge text-bg-light border py-2 d-none d-md-inline"><i class="bi bi-calendar3 me-1"></i>{{ now()->translatedFormat('d M Y') }}</span>
            </div>
        </div>

        <main class="inner">
            @if ($errors->any())
                <div class="d-flex gap-3 align-items-start p-3 mb-3 rounded-4" style="background:linear-gradient(120deg,#fdf1f1,#fff7f4); border:1px solid #f0c9c9; border-left:4px solid #d95757;">
                    <span style="display:grid;place-items:center;flex:none;width:2.5rem;height:2.5rem;border-radius:.8rem;background:#fbdddd;color:#c02929;font-size:1.2rem"><i class="bi bi-exclamation-triangle"></i></span>
                    <div>
                        <div class="fw-bold" style="color:#a12c2c">Periksa kembali — {{ $errors->count() }} isian belum benar</div>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Toast pop-up untuk flash message
    const toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3200, timerProgressBar: true,
        didOpen: t => { t.onmouseenter = Swal.stopTimer; t.onmouseleave = Swal.resumeTimer; } });
    @if (session('success'))
        toast.fire({ icon: 'success', title: @json(session('success')), iconColor: '#24aa9a' });
    @endif
    @if (session('error'))
        Swal.fire({ icon: 'error', title: 'Ups!', text: @json(session('error')), confirmButtonColor: '#176b87', confirmButtonText: 'Oke, mengerti' });
    @endif

    // ===== Validasi klien ramah (mengganti bubble bawaan browser) =====
    const labelDari = el => {
        const wadah = el.closest('.mb-3, .mb-2, [class*="col-"]') || el.parentElement;
        const lbl = wadah?.querySelector('.form-label');
        return lbl ? lbl.textContent.replace('*', '').trim() : 'Kolom ini';
    };
    const pesanSalah = el => {
        const v = el.validity;
        if (v.valueMissing) return labelDari(el) + ' masih kosong — isi dulu ya.';
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
            toast.fire({ icon: 'warning', title: 'Ups! ' + salah.length + ' isian belum lengkap 👇', iconColor: '#e5b94e' });
        }, true);
        f.addEventListener('input', e => bersihkan(e.target), true);
        f.addEventListener('change', e => bersihkan(e.target), true);
    });
    @if ($errors->any())
        toast.fire({ icon: 'warning', title: 'Ups! {{ $errors->count() }} hal perlu diperbaiki 👇', iconColor: '#e5b94e' });
    @endif

    // Dialog konfirmasi kreatif untuk form ber-atribut data-confirm
    document.querySelectorAll('form[data-confirm]').forEach(f => {
        f.addEventListener('submit', e => {
            if (f.dataset.confirmed) return;
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
    });
</script>
</body>
</html>
