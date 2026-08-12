@extends('admin.layouts.app')
@section('title', 'Profil')

@section('content')
    <style>
        /* Kartu identitas — cover banner + avatar besar mengambang, ala halaman profil profesional */
        .pf-card { overflow:hidden; }
        .pf-cover {
            height:7rem; position:relative;
            background:linear-gradient(120deg,#0d2a3a 0%,var(--primary) 55%,var(--teal) 100%);
        }
        .pf-cover::after {
            content:''; position:absolute; inset:0;
            background:radial-gradient(20rem 9rem at 88% -25%, rgba(255,255,255,.22), transparent 60%);
        }
        .pf-body { padding:0 1.5rem 1.5rem; text-align:center; margin-top:-4rem; position:relative; }
        .pf-avatar-wrap { position:relative; width:8rem; height:8rem; margin:0 auto 1rem; }
        .pf-avatar {
            width:8rem; height:8rem; border-radius:1.4rem; display:grid; place-items:center;
            background:linear-gradient(135deg,var(--primary),var(--teal)); color:#fff;
            font-weight:800; font-size:2.6rem; font-family:'Plus Jakarta Sans',sans-serif;
            box-shadow:0 14px 30px -8px rgba(15,23,42,.35); overflow:hidden; border:4px solid #fff;
        }
        .pf-avatar img { width:100%; height:100%; object-fit:cover; }
        .pf-avatar-edit {
            position:absolute; right:-.2rem; bottom:-.2rem; width:2.4rem; height:2.4rem; border-radius:50%;
            background:#fff; border:2px solid var(--surface); color:var(--primary); display:grid; place-items:center;
            box-shadow:0 4px 10px rgba(21,36,59,.18); cursor:pointer; transition:background .15s ease;
        }
        .pf-avatar-edit:hover { background:var(--surface); }
        .pf-avatar-edit input { display:none; }
        .pf-name { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.25rem; }
        .pf-role-badge {
            display:inline-flex; align-items:center; gap:.3rem; background:var(--surface); color:var(--primary);
            font-size:.72rem; font-weight:700; padding:.32rem .8rem; border-radius:2rem; margin:.35rem 0 .6rem;
        }
        .pf-email { color:var(--muted); font-size:.85rem; }
        .pf-since { color:var(--muted); font-size:.76rem; margin-top:.15rem; }
        .pf-section-title { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:.95rem; margin-bottom:.2rem; }
        .pf-section-sub { color:var(--muted); font-size:.8rem; margin-bottom:1.1rem; }

        .pf-bio-list { text-align:left; margin-top:1.4rem; padding-top:1.2rem; border-top:1px solid var(--line); }
        .pf-bio-item { display:flex; align-items:flex-start; gap:.8rem; padding:.6rem 0; }
        .pf-bio-item + .pf-bio-item { border-top:1px solid var(--line); }
        .pf-bio-ic {
            display:grid; place-items:center; width:2.3rem; height:2.3rem; border-radius:.7rem;
            background:var(--surface); color:var(--primary); font-size:1rem; flex:none;
        }
        .pf-bio-item small { display:block; color:var(--muted); font-size:.66rem; font-weight:800;
            text-transform:uppercase; letter-spacing:.06em; margin-bottom:.15rem; }
        .pf-bio-val { font-size:.85rem; font-weight:600; color:var(--ink); word-break:break-word; }
        .pf-bio-empty { color:var(--muted); font-style:italic; font-weight:500; }

        .pf-pw-toggle { display:flex; align-items:center; justify-content:space-between; width:100%; text-align:left;
            background:var(--surface); border:1px solid var(--line); border-radius:.85rem; padding:.85rem 1.1rem;
            font-weight:700; color:var(--ink); transition:background .15s ease; }
        .pf-pw-toggle:hover { background:#eef2f6; }
        .pf-pw-toggle .car { transition:transform .2s ease; color:var(--muted); }
        .pf-pw-toggle[aria-expanded="true"] .car { transform:rotate(180deg); }
        .pf-pw-body { border:1px solid var(--line); border-top:0; border-radius:0 0 .85rem .85rem; padding:1.1rem; }
    </style>

    <div class="row g-3" data-reveal>
        {{-- Kiri — kartu identitas + foto, ala halaman profil profesional --}}
        <div class="col-lg-4">
            <div class="xcard pf-card h-100">
                <div class="pf-cover"></div>
                <div class="pf-body">
                    <div class="pf-avatar-wrap">
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
                    <span class="pf-role-badge"><i class="bi bi-patch-check-fill"></i>Administrator</span>
                    <div class="pf-email">{{ $me->email }}</div>
                    <div class="pf-since"><i class="bi bi-calendar3 me-1"></i>Terdaftar sejak {{ $me->created_at?->translatedFormat('d F Y') }}</div>

                    <div class="pf-bio-list">
                        <div class="pf-bio-item">
                            <span class="pf-bio-ic"><i class="bi bi-whatsapp"></i></span>
                            <div>
                                <small>WhatsApp</small>
                                @if ($me->no_whatsapp)
                                    <div class="pf-bio-val">{{ $me->no_whatsapp }}</div>
                                @else
                                    <div class="pf-bio-val pf-bio-empty">Belum diisi</div>
                                @endif
                            </div>
                        </div>
                        <div class="pf-bio-item">
                            <span class="pf-bio-ic"><i class="bi bi-geo-alt"></i></span>
                            <div>
                                <small>Alamat</small>
                                @if ($me->alamat)
                                    <div class="pf-bio-val">{{ $me->alamat }}</div>
                                @else
                                    <div class="pf-bio-val pf-bio-empty">Belum diisi</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <p class="text-muted small mt-3 mb-0 pt-3" style="border-top:1px solid var(--line)">
                        Format foto PNG atau JPG, maksimal 2 MB.
                    </p>
                </div>
            </div>
        </div>

        {{-- Kanan — form info & keamanan --}}
        <div class="col-lg-8">
            {{-- Informasi Akun (collapse) --}}
            <div class="mb-3">
                <button type="button" class="pf-pw-toggle" data-bs-toggle="collapse" data-bs-target="#pfInfo" aria-expanded="false">
                    <span><i class="bi bi-person-lines-fill me-2"></i>Edit Profil</span>
                    <i class="bi bi-chevron-down car"></i>
                </button>
                <div class="collapse" id="pfInfo">
                    <div class="pf-pw-body">
                        <form method="POST" action="{{ route('admin.profil.update') }}"
                              data-confirm="Nama dan email akun akan diperbarui." data-confirm-title="Simpan perubahan profil?"
                              data-icon="question" data-confirm-text="Ya, simpan">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Nama</label>
                                <input type="text" name="nama_admin" class="form-control" value="{{ old('nama_admin', $me->nama_admin) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email (dipakai untuk masuk)</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $me->email) }}" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">No. WhatsApp</label>
                                <input type="text" name="no_whatsapp" class="form-control" placeholder="Contoh: +6281234567890" value="{{ old('no_whatsapp', $me->no_whatsapp) }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $me->alamat) }}</textarea>
                            </div>

                            <button class="btn btn-brand"><i class="bi bi-check2 me-1"></i>Simpan Perubahan</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Keamanan — Ubah Password (collapse) --}}
            <div>
                <button type="button" class="pf-pw-toggle" data-bs-toggle="collapse" data-bs-target="#pfPassword" aria-expanded="false">
                    <span><i class="bi bi-shield-lock me-2"></i>Ubah Password</span>
                    <i class="bi bi-chevron-down car"></i>
                </button>
                <div class="collapse" id="pfPassword">
                    <div class="pf-pw-body">
                        <form method="POST" action="{{ route('admin.profil.password') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label class="form-label">Password Lama</label>
                                <input type="password" name="password_lama" class="form-control" required>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password Baru</label>
                                    <input type="password" name="password_baru" class="form-control" minlength="8" required>
                                    <small class="hint">Minimal 8 karakter.</small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Konfirmasi Password Baru</label>
                                    <input type="password" name="password_baru_confirmation" class="form-control" minlength="8" required>
                                </div>
                            </div>
                            <button class="btn btn-brand"><i class="bi bi-shield-check me-1"></i>Ubah Password</button>
                        </form>
                    </div>
                </div>
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
