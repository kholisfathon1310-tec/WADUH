<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Reservasi Fasilitas') | WADUH</title>
    <link href="{{ asset('vendor/fonts/fonts.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root { --ink:#15243b; --muted:#637189; --primary:#176b87; --primary-dark:#0f526b; --teal:#24aa9a; --surface:#f6f9fc; --line:#e4ebf2; }
        * { scrollbar-color: #c3d5de transparent; }
        body {
            font-family:'DM Sans',sans-serif; color:var(--ink); min-height:100vh; display:flex; flex-direction:column;
            line-height:1.6; -webkit-font-smoothing:antialiased;
            background:
                radial-gradient(46rem 26rem at 108% -8%, #ddf3ee 0%, transparent 60%),
                radial-gradient(36rem 22rem at -12% 8%, #e2eefb 0%, transparent 55%),
                var(--surface);
            background-attachment:fixed;
        }
        h1,h2,h3,h4,h5,.navbar-brand,.step-label { font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-.02em; }
        ::selection { background:#bfe3ea; color:var(--primary-dark); }

        @keyframes riseIn { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:none; } }
        [data-reveal] { animation:riseIn .55s cubic-bezier(.2,.7,.3,1) both; }
        @media (prefers-reduced-motion: reduce) { [data-reveal] { animation:none; } }

        /* Navbar — floating pill, gaya SaaS modern */
        .nav-shell { position:sticky; top:.85rem; z-index:1030; padding:0 1rem; }
        .topnav {
            background:rgba(255,255,255,.85); backdrop-filter:blur(16px); border:1px solid rgba(228,235,242,.9);
            border-radius:1.5rem; box-shadow:0 14px 34px -18px rgba(21,36,59,.28); padding:.4rem .5rem;
            max-width:1200px; margin:0 auto; transition:box-shadow .2s ease;
        }
        .navbar-brand { font-weight:800; color:var(--ink); font-size:1.15rem; padding-left:.4rem; }
        .navbar-brand span.t { color:var(--teal); }
        .brand-mark { display:inline-grid; width:2.15rem; height:2.15rem; place-items:center; color:#fff; background:linear-gradient(135deg,var(--primary),var(--teal)); border-radius:.75rem; box-shadow:0 6px 14px rgba(23,107,135,.28); }
        .topnav .nav-link { color:#4e5c70; font-weight:600; font-size:.9rem; border-radius:.8rem; padding:.55rem 1rem; transition:background .15s ease, color .15s ease; }
        .topnav .nav-link:hover, .topnav .nav-link.active { color:var(--primary); background:#eef6f9; }
        .btn-cart { position:relative; color:#fff; border:none; border-radius:.85rem; font-weight:700; background:linear-gradient(135deg,var(--primary),var(--primary-dark)); box-shadow:0 8px 18px -8px rgba(23,107,135,.5); transition:all .15s ease; }
        .btn-cart:hover { color:#fff; transform:translateY(-1px); box-shadow:0 10px 22px -8px rgba(23,107,135,.6); }
        .btn-cart .badge { position:absolute; top:-7px; right:-7px; background:var(--teal); }

        /* Stepper — pill progress yang lebih hidup */
        .stepper { display:flex; gap:.3rem; overflow-x:auto; padding:.3rem 0 .6rem; }
        .step { display:flex; align-items:center; gap:.5rem; flex:1 1 0; min-width:120px; }
        .step .dot { display:grid; place-items:center; width:2.1rem; height:2.1rem; border-radius:50%; font-weight:700; font-size:.85rem; background:#e6edf3; color:var(--muted); flex:none; transition:background .2s ease, box-shadow .2s ease, transform .2s ease; }
        .step .step-label { font-size:.78rem; font-weight:700; color:var(--muted); white-space:nowrap; }
        .step .bar { height:4px; border-radius:2px; background:#e2e9f0; flex:1; }
        .step.done .dot { background:var(--teal); color:#fff; }
        .step.done .bar { background:linear-gradient(90deg,var(--teal),var(--primary)); }
        .step.done .step-label { color:var(--teal); }
        .step.now .dot { background:var(--primary); color:#fff; box-shadow:0 0 0 5px rgba(23,107,135,.16); transform:scale(1.08); }
        .step.now .step-label { color:var(--primary); }

        /* Cards & buttons — lebih rounded & hangat */
        .xcard { background:#fff; border:1px solid var(--line); border-radius:1.35rem; box-shadow:0 4px 18px -4px rgba(21,60,73,.07); transition:transform .22s ease, box-shadow .22s ease, border-color .22s ease; }
        a.xcard { text-decoration:none; color:inherit; display:block; }
        .xcard.hover:hover { transform:translateY(-5px); box-shadow:0 20px 40px -12px rgba(21,60,73,.16); border-color:#bfe0e0; }
        .icon-tile { display:grid; place-items:center; width:3.1rem; height:3.1rem; border-radius:1rem; background:linear-gradient(135deg,#e5f4f3,#dcf0ee); color:var(--primary); font-size:1.3rem; }
        .btn-brand { background:linear-gradient(135deg,var(--primary),var(--primary-dark)); border-color:var(--primary); color:#fff; font-weight:700; border-radius:.85rem; transition:background .15s ease, border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
        .btn-brand:hover { border-color:var(--primary-dark); color:#fff; box-shadow:0 10px 22px -8px rgba(23,107,135,.45); transform:translateY(-1px); }
        .btn-brand-outline { color:var(--primary); border:1.5px solid #cfe0e6; border-radius:.85rem; font-weight:700; background:#fff; transition:all .15s ease; }
        .btn-brand-outline:hover { color:var(--primary-dark); border-color:var(--primary); background:#f4fafb; transform:translateY(-1px); }
        .btn-success { border-radius:.85rem; font-weight:700; }
        .form-control,.form-select { border-radius:.75rem; border-color:#d8e2ea; padding:.6rem .9rem; }
        .form-control:focus,.form-select:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(23,107,135,.12); }
        .form-label { font-weight:600; font-size:.86rem; color:#3c4a5f; }
        .breadcrumb { font-size:.83rem; }
        .breadcrumb a { color:var(--primary); text-decoration:none; font-weight:600; }
        .eyebrow-sm { color:var(--primary); font-size:.72rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
        .page-head h1 { font-weight:800; }
        /* Varian banner berwarna (mis. kategori/jenis-sewa/lantai) — hanya saat dikombinasikan dengan text-white */
        .page-head.text-white { position:relative; overflow:hidden; border-radius:1.5rem !important; box-shadow:0 20px 42px -18px rgba(15,60,80,.35); }
        .page-head.text-white::before { content:''; position:absolute; inset:0; background:radial-gradient(28rem 16rem at 105% -20%, rgba(255,255,255,.16), transparent 55%); pointer-events:none; }
        .page-head.text-white > * { position:relative; }
        .alert { border-radius:1rem; }

        /* Availability badges */
        .avail { display:inline-flex; align-items:center; gap:.35rem; font-size:.75rem; font-weight:700; padding:.32rem .65rem; border-radius:2rem; }
        .avail.hijau { background:#e2f7ef; color:#0d8a5f; }
        .avail.kuning { background:#fff4d6; color:#9a6b00; }
        .avail.merah { background:#fde4e4; color:#c02929; }

        .site-footer { margin-top:auto; padding:2rem 0 1.75rem; color:var(--muted); font-size:.83rem; background:transparent; border-top:1px solid var(--line); }

        /* Validasi klien yang ramah — mengganti bubble bawaan browser */
        .is-salah { border-color:#d95757 !important; background:#fffafa !important; box-shadow:0 0 0 .18rem rgba(217,87,87,.12) !important; animation:goyang .3s; }
        @keyframes goyang { 25% { transform:translateX(-4px); } 75% { transform:translateX(4px); } }
        .catatan-salah { display:flex; align-items:center; gap:.3rem; color:#c02929; font-size:.78rem; font-weight:600; margin-top:.3rem; }
    </style>
</head>
<body>
    <div class="nav-shell">
    <nav class="navbar navbar-expand-lg topnav">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                <span class="brand-mark"><i class="bi bi-building"></i></span>
                <span>WADUH<span class="t">.</span></span>
            </a>
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
                <i class="bi bi-list fs-3"></i>
            </button>
            <div id="nav" class="collapse navbar-collapse">
                <ul class="navbar-nav mx-auto gap-lg-1">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ url('/') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('reservasi.index') ? 'active' : '' }}" href="{{ route('reservasi.index') }}">Reservasi</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('reservasi.denah') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">Fasilitas</a>
                        <ul class="dropdown-menu shadow border-0" style="border-radius:1rem">
                            @php
                                $navLantai = \App\Models\Lantai::with(['fasilitas' => fn ($q) => $q->limit(1)])->orderBy('id_lantai')->get();
                                $navWarna = ['1' => '#2f7fd1', '2' => '#24aa9a', '3A' => '#7c5cd6', '3B' => '#e8833a', '5' => '#d6527c'];
                            @endphp
                            @foreach ($navLantai as $nl)
                                @if ($nl->fasilitas->isNotEmpty())
                                    <li>
                                        <a class="dropdown-item fw-semibold" href="{{ route('reservasi.denah', ['kategori' => $nl->fasilitas->first()->kategori_fasilitas, 'lantai' => $nl->id_lantai]) }}">
                                            <span class="d-inline-block rounded-circle me-2" style="width:.6rem;height:.6rem;background:{{ $navWarna[$nl->nomor_lantai] ?? '#176b87' }}"></span>
                                            Lantai {{ $nl->nomor_lantai }} · {{ $nl->fasilitas->first()->kategori_fasilitas }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('cek-status.*') ? 'active' : '' }}" href="{{ route('cek-status.form') }}">Cek Status</a></li>
                </ul>
                @php $cartN = app(\App\Services\CartService::class)->count(); @endphp
                <a href="{{ route('reservasi.checkout.form') }}" class="btn btn-cart btn-sm px-3 py-2">
                    <i class="bi bi-cart3 me-1"></i> Keranjang
                    @if ($cartN > 0)<span class="badge rounded-pill">{{ $cartN }}</span>@endif
                </a>
            </div>
        </div>
    </nav>
    </div>

    <main class="container py-4 py-md-5">
        {{-- Tombol kembali global --}}
        <button onclick="history.back()" class="btn btn-sm btn-brand-outline mb-3"><i class="bi bi-arrow-left me-1"></i>Kembali</button>

        @hasSection('stepper')
            <div class="mb-3">@yield('stepper')</div>
        @endif

        @if ($errors->any())
            <div class="err-card mb-3">
                <span class="err-ic"><i class="bi bi-emoji-frown"></i></span>
                <div>
                    <div class="fw-bold" style="color:#a12c2c">Ups, ada {{ $errors->count() }} hal yang perlu diperbaiki</div>
                    <div class="small text-muted mb-1">Lengkapi dulu ya, biar reservasimu bisa diproses:</div>
                    <ul class="err-list">
                        @foreach ($errors->all() as $e)<li><i class="bi bi-arrow-right-short"></i>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            </div>
            <style>
                .err-card { display:flex; gap:.9rem; align-items:flex-start; padding:1rem 1.15rem; background:linear-gradient(120deg,#fdf1f1,#fff7f4); border:1px solid #f0c9c9; border-left:4px solid #d95757; border-radius:1rem; box-shadow:0 8px 22px rgba(180,60,60,.08); }
                .err-ic { display:grid; place-items:center; flex:none; width:2.6rem; height:2.6rem; border-radius:.9rem; background:#fbdddd; color:#c02929; font-size:1.3rem; }
                .err-list { list-style:none; margin:0; padding:0; font-size:.88rem; color:#7c3a3a; }
                .err-list li { padding:.12rem 0; }
                .err-list i { color:#d95757; }
            </style>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="d-flex align-items-center gap-2"><span class="brand-mark" style="width:1.7rem;height:1.7rem;border-radius:.55rem;font-size:.85rem"><i class="bi bi-building"></i></span><span class="fw-bold">WADUH</span> — Wadah Akses Digital Unit Hunian BITC</span>
            <span>&copy; {{ now()->year }} WADUH · BITC Cimahi</span>
        </div>
    </footer>
    <script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
    <script>
        // Pop-up flash: sukses → toast manis, error → modal jelas
        const toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3400, timerProgressBar: true,
            didOpen: t => { t.onmouseenter = Swal.stopTimer; t.onmouseleave = Swal.resumeTimer; } });
        @if (session('success'))
            toast.fire({ icon: 'success', title: @json(session('success')), iconColor: '#24aa9a' });
        @endif
        @if (session('error'))
            Swal.fire({ icon: 'error', title: 'Maaf, ada kendala', text: @json(session('error')), confirmButtonColor: '#176b87', confirmButtonText: 'Oke, mengerti' });
        @endif
        @if (session('checkout'))
            Swal.fire({
                icon: 'success',
                title: 'Reservasi Terkirim! 🎉',
                html: 'Kode reservasi Anda:<br><strong style="font-size:1.6rem;color:#176b87;letter-spacing:.08em">{{ session('checkout')['kode_transaksi'] ?? '' }}</strong><br><small class="text-muted">Simpan kode ini untuk mengecek status reservasi.</small>',
                confirmButtonColor: '#176b87',
                confirmButtonText: 'Siap, sudah kusimpan',
            });
        @endif

        // ===== Validasi klien yang ramah (mengganti bubble bawaan browser) =====
        const labelDari = el => {
            const wadah = el.closest('.mb-3, .mb-2, [class*="col-"]') || el.parentElement;
            const lbl = wadah?.querySelector('.form-label');
            return lbl ? lbl.textContent.replace('*', '').trim() : 'Kolom ini';
        };
        const pesanSalah = el => {
            const v = el.validity;
            if (v.valueMissing) {
                if (el.type === 'file') return 'Lampirkan dulu dokumennya ya.';
                if (el.tagName === 'SELECT') return labelDari(el) + ' belum dipilih.';
                return labelDari(el) + ' masih kosong — isi dulu ya.';
            }
            if (v.typeMismatch && el.type === 'email') return 'Format email belum benar (contoh: nama@email.com).';
            if (v.rangeUnderflow) return labelDari(el) + ' minimal ' + el.min + '.';
            if (v.rangeOverflow) return labelDari(el) + ' maksimal ' + el.max + '.';
            if (v.tooLong) return labelDari(el) + ' terlalu panjang.';
            return labelDari(el) + ' belum sesuai format.';
        };
        // Input hidden (mis. pemilih jam custom) → tandai tombol/wadah yang terlihat.
        const wakilTerlihat = el => {
            if (el.type === 'hidden') return el.closest('[data-jampicker]')?.querySelector('.jam-btn') || el;
            return el;
        };
        const tandai = el => {
            const target = wakilTerlihat(el);
            target.classList.add('is-salah');
            const induk = target.closest('.input-group') || target.closest('[data-jampicker]') || target;
            induk.parentElement.querySelector(':scope > .catatan-salah')?.remove();
            const note = document.createElement('div');
            note.className = 'catatan-salah';
            note.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i>' + pesanSalah(el);
            induk.insertAdjacentElement('afterend', note);
        };
        const bersihkan = el => {
            const target = wakilTerlihat(el);
            target.classList.remove('is-salah');
            const induk = target.closest('.input-group') || target.closest('[data-jampicker]') || target;
            induk.parentElement.querySelector(':scope > .catatan-salah')?.remove();
        };
        document.querySelectorAll('form').forEach(f => {
            f.setAttribute('novalidate', '');
            f.addEventListener('submit', e => {
                const salah = [...f.querySelectorAll('input, select, textarea')].filter(el => ! el.disabled && ! el.checkValidity());
                if (! salah.length) return;
                e.preventDefault();
                e.stopImmediatePropagation(); // jangan lanjut ke dialog konfirmasi
                salah.forEach(tandai);
                const pertama = wakilTerlihat(salah[0]);
                pertama.scrollIntoView({ behavior: 'smooth', block: 'center' });
                setTimeout(() => pertama.focus({ preventScroll: true }), 350);
                toast.fire({ icon: 'warning', title: 'Ups! ' + salah.length + ' isian belum lengkap 👇', iconColor: '#e5b94e' });
            }, true);
            f.addEventListener('input', e => bersihkan(e.target), true);
            f.addEventListener('change', e => bersihkan(e.target), true);
        });
        @if ($errors->any())
            toast.fire({ icon: 'warning', title: 'Ups! {{ $errors->count() }} hal perlu diperbaiki 👇', iconColor: '#e5b94e' });
        @endif

        // Dialog konfirmasi untuk form ber-atribut data-confirm
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
