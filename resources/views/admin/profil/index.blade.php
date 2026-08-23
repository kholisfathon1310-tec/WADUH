@extends('admin.layouts.app')
@section('title', 'Profil')

@section('content')
    <style>
        /* ===== Kartu hero — banner + avatar mengambang + aksi cepat, ala profil modern ===== */
        .pf-hero { overflow:visible; border:1px solid var(--line); }
        .pf-hero-banner {
            height:6.5rem; border-radius:1rem 1rem 0 0; position:relative; overflow:hidden;
            background:linear-gradient(120deg,#13294b 0%,var(--primary) 55%,#2f6fd6 100%);
        }
        .pf-hero-banner::after {
            content:''; position:absolute; inset:0;
            background:radial-gradient(24rem 10rem at 90% -30%, rgba(255,255,255,.25), transparent 60%);
        }
        .pf-hero-actions { position:absolute; top:1rem; right:1.1rem; display:flex; gap:.6rem; z-index:2; }
        .pf-pill {
            display:inline-flex; align-items:center; gap:.4rem; border:0; border-radius:2rem;
            padding:.5rem 1.05rem; font-size:.82rem; font-weight:700; box-shadow:0 6px 16px rgba(15,23,42,.18);
            transition:transform .15s ease, box-shadow .15s ease;
        }
        .pf-pill:hover { transform:translateY(-1px); }
        .pf-pill-light { background:#fff; color:var(--ink); }
        .pf-pill-solid { background:linear-gradient(135deg,var(--primary-dark,#0f526b),var(--primary)); color:#fff; }

        .pf-hero-body { padding:0 1.6rem 1.5rem; position:relative; }
        .pf-hero-avatar-wrap { position:relative; width:6.5rem; height:6.5rem; margin-top:-3.4rem; }
        .pf-avatar {
            width:6.5rem; height:6.5rem; border-radius:50%; display:grid; place-items:center;
            background:linear-gradient(135deg,var(--primary),var(--teal)); color:#fff;
            font-weight:800; font-size:2.1rem; font-family:'Plus Jakarta Sans',sans-serif;
            box-shadow:0 10px 24px -6px rgba(15,23,42,.35); overflow:hidden; border:4px solid #fff;
        }
        .pf-avatar img { width:100%; height:100%; object-fit:cover; }
        .pf-avatar-edit {
            position:absolute; right:-.1rem; bottom:-.1rem; width:2.1rem; height:2.1rem; border-radius:50%;
            background:#fff; border:2px solid var(--surface); color:var(--primary); display:grid; place-items:center;
            box-shadow:0 4px 10px rgba(21,36,59,.2); cursor:pointer; transition:background .15s ease;
        }
        .pf-avatar-edit:hover { background:var(--surface); }
        .pf-avatar-edit input { display:none; }

        .pf-name { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.4rem; color:var(--ink); margin-top:.85rem; }
        .pf-meta-row { display:flex; flex-wrap:wrap; align-items:center; gap:.6rem; margin-top:.4rem; }
        .pf-role-badge {
            display:inline-flex; align-items:center; gap:.35rem; background:var(--surface); color:var(--primary);
            font-size:.74rem; font-weight:700; padding:.32rem .85rem; border-radius:2rem;
        }
        .pf-since { color:var(--muted); font-size:.8rem; display:inline-flex; align-items:center; gap:.35rem; }

        .pf-divider { border:0; border-top:1px solid var(--line); margin:1.3rem 0; }

        .pf-chip-row { display:flex; flex-wrap:wrap; gap:1.5rem 2.5rem; }
        .pf-chip { display:flex; align-items:flex-start; gap:.8rem; }
        .pf-chip-full { margin-top:1.3rem; }
        .pf-chip-ic {
            display:grid; place-items:center; width:2.5rem; height:2.5rem; border-radius:50%; flex:none;
            background:var(--surface); color:var(--primary); font-size:1.05rem;
        }
        .pf-chip small { display:block; color:var(--muted); font-size:.68rem; font-weight:800;
            letter-spacing:.06em; margin-bottom:.15rem; }
        .pf-chip-val { font-size:.9rem; font-weight:600; color:var(--ink); word-break:break-word; }
        .pf-chip-empty { color:var(--muted); font-style:italic; font-weight:500; }

        /* ===== Panel form Edit Profil / Ubah Kata Sandi — dibuka lewat tombol pill di atas ===== */
        .pf-panel { border:1px solid var(--line); border-radius:1rem; padding:1.3rem 1.4rem; background:#fff; }
        .pf-panel-title { display:flex; align-items:center; gap:.6rem; font-weight:700; color:var(--ink); font-size:.95rem; margin-bottom:1rem; }
        .pf-panel-title .ic {
            display:grid; place-items:center; width:2.2rem; height:2.2rem; border-radius:.7rem;
            background:linear-gradient(135deg,var(--primary),var(--teal)); color:#fff; font-size:.95rem;
        }
        .pf-field-label { font-weight:600; font-size:.83rem; color:#3c4a5f; }
    </style>

    <div data-reveal>
        {{-- ===== Hero — identitas, kontak, dan aksi cepat ===== --}}
        <div class="xcard pf-hero mb-3">
            <div class="pf-hero-banner">
                <div class="pf-hero-actions">
                    <button type="button" class="pf-pill pf-pill-light" data-bs-toggle="collapse" data-bs-target="#pfInfo" aria-expanded="false">
                        <i class="bi bi-pencil-fill"></i>Edit Profil
                    </button>
                    <button type="button" class="pf-pill pf-pill-solid" data-bs-toggle="collapse" data-bs-target="#pfPassword" aria-expanded="false">
                        <i class="bi bi-shield-lock-fill"></i>Ubah Kata Sandi
                    </button>
                </div>
            </div>
            <div class="pf-hero-body">
                <div class="pf-hero-avatar-wrap">
                    <div class="pf-avatar">
                        @if ($me->fotoUrl())
                            <img src="{{ $me->fotoUrl() }}" alt="{{ $me->nama_admin }}">
                        @else
                            {{ strtoupper(substr($me->nama_admin, 0, 1)) }}
                        @endif
                    </div>
                    <label class="pf-avatar-edit" title="Ganti foto profil">
                        <i class="bi bi-camera-fill"></i>
                        <input type="file" id="fotoInput" accept="image/png,image/jpeg">
                    </label>
                </div>
                <form method="POST" action="{{ route('admin.profil.foto') }}" enctype="multipart/form-data" id="fotoForm" class="d-none">
                    @csrf
                </form>

                <div class="pf-name">{{ $me->nama_admin }}</div>
                <div class="pf-meta-row">
                    <span class="pf-role-badge"><i class="bi bi-patch-check-fill"></i>Administrator</span>
                    <span class="pf-since"><i class="bi bi-calendar3"></i>Terdaftar sejak {{ $me->created_at?->translatedFormat('d F Y') }}</span>
                </div>

                <hr class="pf-divider">

                <div class="pf-chip-row">
                    <div class="pf-chip">
                        <span class="pf-chip-ic"><i class="bi bi-envelope"></i></span>
                        <div>
                            <small>EMAIL</small>
                            <div class="pf-chip-val">{{ $me->email }}</div>
                        </div>
                    </div>
                    <div class="pf-chip">
                        <span class="pf-chip-ic"><i class="bi bi-whatsapp"></i></span>
                        <div>
                            <small>WHATSAPP</small>
                            @if ($me->no_whatsapp)
                                <div class="pf-chip-val">{{ $me->no_whatsapp }}</div>
                            @else
                                <div class="pf-chip-val pf-chip-empty">Belum diisi</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="pf-chip pf-chip-full">
                    <span class="pf-chip-ic"><i class="bi bi-geo-alt"></i></span>
                    <div>
                        <small>ALAMAT</small>
                        @if ($me->alamat)
                            <div class="pf-chip-val">{{ $me->alamat }}</div>
                        @else
                            <div class="pf-chip-val pf-chip-empty">Belum diisi</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Edit Profil (dibuka dari tombol pill di banner) ===== --}}
        <div class="collapse mb-3" id="pfInfo">
            <div class="pf-panel">
                <div class="pf-panel-title"><span class="ic"><i class="bi bi-person-lines-fill"></i></span>Edit Profil</div>
                <form method="POST" action="{{ route('admin.profil.update') }}"
                      data-confirm="Nama dan email akun akan diperbarui." data-confirm-title="Simpan perubahan profil?"
                      data-icon="warning" data-confirm-text="Ya, simpan">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label pf-field-label">Nama</label>
                            <input type="text" name="nama_admin" class="form-control" value="{{ old('nama_admin', $me->nama_admin) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label pf-field-label">Email (dipakai untuk masuk)</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $me->email) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label pf-field-label">No. WhatsApp</label>
                            <input type="text" name="no_whatsapp" class="form-control" placeholder="Contoh: +6281234567890" value="{{ old('no_whatsapp', $me->no_whatsapp) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label pf-field-label">Alamat</label>
                            <textarea name="alamat" class="form-control" rows="1">{{ old('alamat', $me->alamat) }}</textarea>
                        </div>
                    </div>

                    <button class="btn btn-brand mt-3"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
                </form>
            </div>
        </div>

        {{-- ===== Ubah Password (dibuka dari tombol pill di banner) ===== --}}
        <div class="collapse mb-3" id="pfPassword">
            <div class="pf-panel">
                <div class="pf-panel-title"><span class="ic"><i class="bi bi-shield-lock"></i></span>Ubah Kata sandi</div>
                <form method="POST" action="{{ route('admin.profil.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label pf-field-label">Kata Sandi Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label class="form-label pf-field-label">Kata Sandi Baru</label>
                            <input type="password" name="password_baru" class="form-control" minlength="8" required>
                            <small class="hint">Minimal 8 karakter.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label pf-field-label">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_baru_confirmation" class="form-control" minlength="8" required>
                        </div>
                    </div>
                    <button class="btn btn-brand"><i class="bi bi-shield-check me-1"></i>Ubah Kata Sandi</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Ganti foto: pilih file langsung submit form tersembunyi (tanpa perlu tombol simpan terpisah).
        (function () {
            const input = document.getElementById('fotoInput');
            const form = document.getElementById('fotoForm');
            if (!input || !form) return;
            input.addEventListener('change', () => {
                if (!input.files.length) return;
                const dt = new DataTransfer();
                dt.items.add(input.files[0]);
                let hidden = form.querySelector('input[name="foto"]');
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'file';
                    hidden.name = 'foto';
                    hidden.className = 'd-none';
                    form.appendChild(hidden);
                }
                hidden.files = dt.files;
                form.submit();
            });
        })();

        // Buka otomatis panel terkait kalau baru saja submit dengan error dari form itu.
        @if ($errors->has('nama_admin') || $errors->has('email'))
            document.addEventListener('DOMContentLoaded', () => {
                new bootstrap.Collapse(document.getElementById('pfInfo'), { show: true });
            });
        @endif
        @if ($errors->has('password_lama') || $errors->has('password_baru'))
            document.addEventListener('DOMContentLoaded', () => {
                new bootstrap.Collapse(document.getElementById('pfPassword'), { show: true });
            });
        @endif
    </script>
@endsection