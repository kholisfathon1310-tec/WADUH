<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | WADUH Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root { --primary:#176b87; --primary-dark:#0f526b; --teal:#24aa9a; --ink:#15243b; }
        body { font-family:'DM Sans',sans-serif; min-height:100vh; display:grid; place-items:center;
               background:linear-gradient(135deg,#102135 0%,#0e2b3d 55%,#124553 100%); position:relative; overflow:hidden; }
        body::before, body::after { content:''; position:absolute; border-radius:50%; filter:blur(6px); opacity:.25; }
        body::before { width:26rem; height:26rem; background:#24aa9a; top:-9rem; left:-8rem; }
        body::after { width:20rem; height:20rem; background:#176b87; bottom:-7rem; right:-6rem; }
        h1,.brand { font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:-.02em; }
        .login-card { position:relative; z-index:1; width:min(420px, 92vw); background:#fff; border-radius:1.25rem; box-shadow:0 30px 70px rgba(5,20,32,.45); overflow:hidden; }
        .login-head { padding:1.9rem 2rem 0; text-align:center; }
        .brand-mark { display:inline-grid; width:3rem; height:3rem; place-items:center; color:#fff; background:linear-gradient(135deg,var(--primary),var(--teal)); border-radius:.9rem; font-size:1.35rem; box-shadow:0 10px 22px rgba(23,107,135,.35); }
        .form-control { border-radius:.65rem; border-color:#d8e2ea; padding:.65rem .9rem; }
        .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(23,107,135,.12); }
        .form-label { font-weight:600; font-size:.85rem; color:#3c4a5f; }
        .btn-login { background:var(--primary); border-color:var(--primary); color:#fff; font-weight:700; border-radius:.7rem; padding:.7rem; }
        .btn-login:hover { background:var(--primary-dark); color:#fff; }
        .input-group-text { background:#f4f8fa; border-color:#d8e2ea; color:#637189; border-radius:.65rem 0 0 .65rem; }
        .is-salah { border-color:#d95757 !important; background:#fffafa !important; animation:goyang .3s; }
        @keyframes goyang { 25% { transform:translateX(-4px); } 75% { transform:translateX(4px); } }
        .catatan-salah { display:flex; align-items:center; gap:.3rem; color:#c02929; font-size:.78rem; font-weight:600; margin-top:.3rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-head">
            <span class="brand-mark mb-2"><i class="bi bi-building"></i></span>
            <h1 class="h4 brand mb-1">WADUH<span style="color:var(--teal)">.</span> Admin</h1>
            <p class="text-muted small mb-0">Wadah Akses Digital Unit Hunian — kelola reservasi BITC</p>
        </div>
        <div class="p-4 px-md-4">
            @if (session('error'))
                <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-triangle me-1"></i>{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger py-2 small"><ul class="mb-0 ps-3">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="admin@waduh.test" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small" for="remember">Ingat saya di perangkat ini</label>
                </div>
                <button class="btn btn-login w-100"><i class="bi bi-box-arrow-in-right me-1"></i> Masuk</button>
            </form>
            <p class="text-center text-muted small mt-3 mb-0"><a href="{{ url('/') }}" class="text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke situs publik</a></p>
        </div>
    </div>
    <script>
        // Validasi klien ramah (tanpa bubble bawaan browser)
        document.querySelectorAll('form').forEach(f => {
            f.setAttribute('novalidate', '');
            f.addEventListener('submit', e => {
                const salah = [...f.querySelectorAll('input')].filter(el => ! el.checkValidity());
                if (! salah.length) return;
                e.preventDefault();
                salah.forEach(el => {
                    el.classList.add('is-salah');
                    const grup = el.closest('.input-group') || el;
                    grup.parentElement.querySelector('.catatan-salah')?.remove();
                    const note = document.createElement('div');
                    note.className = 'catatan-salah';
                    note.innerHTML = '<i class="bi bi-exclamation-circle-fill"></i>' +
                        (el.validity.valueMissing
                            ? (el.type === 'password' ? 'Password masih kosong.' : 'Email masih kosong.')
                            : 'Format email belum benar.');
                    grup.insertAdjacentElement('afterend', note);
                });
                salah[0].focus();
            });
            f.addEventListener('input', e => {
                e.target.classList.remove('is-salah');
                (e.target.closest('.input-group') || e.target).parentElement.querySelector('.catatan-salah')?.remove();
            }, true);
        });
    </script>
</body>
</html>
