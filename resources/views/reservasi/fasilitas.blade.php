@extends('layouts.reservasi')
@section('title', $fasilitas->nama_fasilitas)

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 4])
@endsection

@php
    $satuan = $jenis->satuan->value;
    $meta   = \App\Support\KategoriMeta::get($fasilitas->kategori_fasilitas);

    // Sewa Per Jam & Convention Hall (Per Hari): pemakaian selalu 1 hari (otomatis 8 jam)
    // — cukup satu field tanggal, tidak perlu pilih jam mulai/selesai secara manual.
    $sehariSaja = $satuan === 'Jam'
        || ($fasilitas->kategori_fasilitas === 'Convention Hall' && $satuan === 'Hari');

    // Ruangan tambahan dari denah multi-select (URL: ?antrian=12,34,...).
    // Eager-load lantai untuk menghindari N+1 di mini-card.
    $antrianIds = array_filter(explode(',', (string) request('antrian')));
    $ruangLain  = $antrianIds
        ? \App\Models\Fasilitas::with('lantai')->whereIn('id_fasilitas', $antrianIds)->get()
        : collect();

    // Semua ruangan terpilih (utama + antrian) — jadikan collection tunggal untuk iterasi.
    $semuaRuangan  = collect([$fasilitas])->concat($ruangLain);
    $jumlahRuangan = $semuaRuangan->count();
    $isMulti       = $jumlahRuangan > 1;

    // Kapasitas minimum (batas jumlah pengguna).
    $kapMin = $semuaRuangan->min('kapasitas');

    // Fasilitas bawaan (sama untuk semua ruangan yang berpasangan dengan satuan sewa ini).
    $bawaan = app(\App\Services\FasilitasBawaanService::class)->untuk($fasilitas, $satuan);

    // Tarif total (asumsi tarif per unit sama untuk ruangan sejenis).
    $totalTarif = $tarif->harga * $jumlahRuangan;
@endphp

@section('content')

    {{-- ══════════════════════════════════════════════════════════════
         Style scoped khusus halaman ini — palet monokrom teal.
         ══════════════════════════════════════════════════════════════ --}}
    <style>
        .fp-page { --fp-soft:#e6f2f4; --fp-radius:1rem; }

        /* Breadcrumb rapi */
        .fp-page .breadcrumb { font-size:.8rem; margin-bottom:1rem; }
        .fp-page .breadcrumb-item + .breadcrumb-item::before { color:#94a3b8; }
        .fp-page .breadcrumb-item a { color:var(--muted); text-decoration:none; font-weight:600; }
        .fp-page .breadcrumb-item a:hover { color:var(--primary); }
        .fp-page .breadcrumb-item.active { color:var(--ink); font-weight:700; }

        /* Header ringkas kolom kiri (mode multi) */
        .fp-head {
            display:flex; align-items:center; justify-content:space-between;
            gap:.6rem; margin-bottom:.9rem;
        }
        .fp-head b {
            font-family:'Plus Jakarta Sans',sans-serif; font-weight:800;
            font-size:1rem; color:var(--ink);
        }
        .fp-head .cnt {
            background:var(--fp-soft); color:var(--primary);
            font-size:.72rem; font-weight:700;
            padding:.32rem .75rem; border-radius:2rem;
            display:inline-flex; align-items:center; gap:.3rem;
        }

        /* Mini-card per ruangan (mode multi) */
        .fp-mini {
            display:flex; gap:1rem;
            background:#fff; border:1px solid var(--line);
            border-radius:var(--fp-radius); padding:.9rem;
            margin-bottom:.75rem;
            transition:border-color .15s ease, box-shadow .15s ease, transform .15s ease;
        }
        .fp-mini:hover {
            border-color:transparent;
            box-shadow:0 12px 28px -14px rgba(15,23,42,.16);
            transform:translateY(-2px);
        }
        .fp-mini .thumb {
            width:6.5rem; height:6.5rem; border-radius:.7rem;
            background-size:cover; background-position:center;
            background-color:#f7f9fc; flex:none;
        }
        .fp-mini .info { flex:1; min-width:0; display:flex; flex-direction:column; justify-content:center; }
        .fp-mini .eyebrow {
            font-size:.66rem; font-weight:700; letter-spacing:.14em;
            color:var(--primary); text-transform:uppercase; margin-bottom:.15rem;
        }
        .fp-mini h3 {
            font-family:'Plus Jakarta Sans',sans-serif; font-weight:800;
            font-size:1rem; color:var(--ink); margin:0 0 .5rem;
        }
        .fp-mini .meta { display:flex; flex-wrap:wrap; gap:.35rem; }
        .fp-chip {
            display:inline-flex; align-items:center; gap:.3rem;
            background:#f7f9fc; color:var(--ink);
            font-size:.72rem; font-weight:600; padding:.28rem .6rem;
            border-radius:2rem; border:1px solid var(--line);
        }
        .fp-chip i { color:var(--muted); }

        /* Card tarif ringkas */
        .fp-tarif {
            background:linear-gradient(120deg, var(--fp-soft) 0%, #f0f9fa 100%);
            border:1px solid rgba(14,107,125,.15);
            border-radius:var(--fp-radius); padding:1rem 1.2rem;
            margin-bottom:.75rem;
        }
        .fp-tarif small.lbl {
            display:block; color:var(--muted); font-size:.72rem; font-weight:600;
            margin-bottom:.35rem; letter-spacing:.02em;
        }
        .fp-tarif .row-t {
            display:flex; justify-content:space-between; align-items:baseline;
            font-size:.85rem; color:var(--muted);
        }
        .fp-tarif .row-t + .row-t {
            margin-top:.5rem; padding-top:.5rem;
            border-top:1px dashed rgba(14,107,125,.2);
        }
        .fp-tarif .row-t b {
            font-family:'Plus Jakarta Sans',sans-serif;
            color:var(--ink); font-weight:700;
        }
        .fp-tarif .total {
            font-family:'Plus Jakarta Sans',sans-serif;
            font-size:1.4rem; font-weight:800; color:var(--primary);
        }

        /* Card fasilitas termasuk */
        .fp-includes {
            background:#fff; border:1px solid var(--line);
            border-radius:var(--fp-radius); padding:1rem 1.2rem;
        }
        .fp-includes h4 {
            font-family:'Plus Jakarta Sans',sans-serif; font-weight:800;
            font-size:.9rem; margin:0 0 .7rem;
            display:flex; align-items:center; gap:.5rem; color:var(--ink);
        }
        .fp-includes h4 i { color:var(--primary); }
        .fp-includes ul {
            list-style:none; padding:0; margin:0;
            display:grid; grid-template-columns:1fr 1fr; gap:.35rem .8rem;
        }
        .fp-includes li {
            font-size:.82rem; color:var(--muted);
            display:flex; align-items:center; gap:.4rem;
        }
        .fp-includes li i { color:var(--primary); font-size:.8rem; }
        .fp-includes .note {
            font-size:.75rem; color:var(--muted);
            margin:.75rem 0 0; font-style:italic;
        }

        /* Hero card (mode single ruangan) */
        .fp-hero {
            background:#fff; border:1px solid var(--line);
            border-radius:var(--fp-radius); overflow:hidden;
            box-shadow:0 4px 14px -6px rgba(15,23,42,.06);
        }
        .fp-hero .foto-wrap { position:relative; }
        .fp-hero .foto {
            width:100%; height:220px; object-fit:cover; display:block;
            background:#f7f9fc;
        }
        .fp-hero .cat-badge {
            position:absolute; top:.85rem; left:.85rem;
            background:rgba(255,255,255,.95); backdrop-filter:blur(6px);
            color:var(--primary); font-weight:700; font-size:.72rem;
            padding:.35rem .8rem; border-radius:2rem;
            box-shadow:0 4px 12px rgba(15,23,42,.1);
            display:inline-flex; align-items:center; gap:.4rem;
        }
        .fp-hero .body { padding:1.4rem 1.4rem 1.5rem; }
        .fp-hero .body .eyebrow {
            font-size:.68rem; font-weight:700; letter-spacing:.14em;
            color:var(--primary); text-transform:uppercase; margin-bottom:.35rem;
        }
        .fp-hero .body h1 {
            font-family:'Plus Jakarta Sans',sans-serif; font-weight:800;
            font-size:1.35rem; color:var(--ink); margin:0 0 .75rem;
        }
        .fp-hero .meta { display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:1rem; }
        .fp-hero .fp-tarif { margin-bottom:1rem; }

        /* ─── Kolom kanan — Form Jadwal ─── */
        .fp-form {
            background:#fff; border:1px solid var(--line);
            border-radius:var(--fp-radius); padding:1.6rem;
            box-shadow:0 4px 14px -6px rgba(15,23,42,.06);
        }
        .fp-form-head {
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:.5rem; margin-bottom:1.3rem;
            padding-bottom:1rem; border-bottom:1px solid #eef2f6;
        }
        .fp-form-head h2 {
            font-family:'Plus Jakarta Sans',sans-serif; font-weight:800;
            font-size:1.15rem; color:var(--ink); margin:0;
        }
        .fp-form-head .satuan {
            background:var(--fp-soft); color:var(--primary);
            font-size:.72rem; font-weight:700; padding:.35rem .8rem; border-radius:2rem;
            display:inline-flex; align-items:center; gap:.35rem;
        }

        .fp-hint {
            display:flex; gap:.7rem; align-items:flex-start;
            background:var(--fp-soft); border:1px solid rgba(14,107,125,.15);
            border-radius:.75rem; padding:.85rem 1rem; margin-bottom:1.2rem;
            font-size:.85rem; color:var(--ink);
        }
        .fp-hint i { color:var(--primary); margin-top:.15rem; }
        .fp-hint b { font-family:'Plus Jakarta Sans',sans-serif; font-weight:700; }

        .fp-form .form-label {
            font-family:'Plus Jakarta Sans',sans-serif; font-weight:700;
            font-size:.85rem; color:var(--ink); margin-bottom:.4rem;
        }
        .fp-form .form-control {
            border-radius:.65rem; border-color:var(--line);
            font-size:.9rem; padding:.6rem .85rem;
            transition:border-color .15s ease, box-shadow .15s ease;
        }
        .fp-form .form-control:focus {
            border-color:var(--primary);
            box-shadow:0 0 0 3px rgba(14,107,125,.12);
        }
        .fp-form small.hint { color:var(--muted); font-size:.75rem; margin-top:.3rem; display:block; }

        .fp-info-slot {
            display:flex; gap:.55rem; align-items:center;
            background:#f7f9fc; border:1px solid var(--line);
            border-radius:.65rem; padding:.6rem .85rem;
            font-size:.82rem; color:var(--ink);
            height:calc(1.5em + 1.2rem + 2px);
        }
        .fp-info-slot i { color:var(--primary); }

        .fp-note {
            display:flex; gap:.55rem; align-items:flex-start;
            background:#eef7fb; border:1px solid #cee8f2;
            border-radius:.65rem; padding:.7rem .9rem;
            font-size:.8rem; color:#1a4a5f; margin-bottom:1rem;
        }
        .fp-note i { color:var(--primary); margin-top:.15rem; }

        .fp-submit {
            background:var(--primary); color:#fff;
            border:0; border-radius:.7rem;
            font-weight:700; font-size:.95rem;
            padding:.9rem 1.2rem; width:100%;
            display:inline-flex; align-items:center; justify-content:center; gap:.5rem;
            box-shadow:0 12px 26px -8px rgba(14,107,125,.4);
            transition:background .15s ease, transform .15s ease;
        }
        .fp-submit:hover { background:var(--primary-dark); transform:translateY(-1px); color:#fff; }

        @media (max-width: 991.98px) {
            .fp-mini .thumb { width:5.5rem; height:5.5rem; }
        }
    </style>

    <div class="fp-page">

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('reservasi.index') }}">Kategori</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('reservasi.denah', ['kategori' => $fasilitas->kategori_fasilitas, 'lantai' => $fasilitas->id_lantai, 'jenis' => $jenis->id_jenis_sewa]) }}">
                        Denah Lantai {{ $fasilitas->lantai->nomor_lantai }}
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    {{ $isMulti ? "$jumlahRuangan Ruangan Terpilih" : $fasilitas->nama_fasilitas }}
                </li>
            </ol>
        </nav>

        <div class="row g-4">

            {{-- ═════════════════════════════════════════════════════
                 KOLOM KIRI — Detail ruangan (single vs multi)
                 ═════════════════════════════════════════════════════ --}}
            <div class="col-lg-5" data-reveal>

                @if ($isMulti)
                    {{-- MODE MULTI: header + mini-card per ruangan + tarif + fasilitas --}}
                    <div class="fp-head">
                        <b>{{ $jumlahRuangan }} Ruangan Terpilih</b>
                        <span class="cnt"><i class="bi bi-collection"></i>{{ $jumlahRuangan }} unit</span>
                    </div>

                    @foreach ($semuaRuangan as $f)
                        @php
                            $fMeta = \App\Support\KategoriMeta::get($f->kategori_fasilitas);
                            $fFoto = $f->foto
                                ? \Illuminate\Support\Facades\Storage::url($f->foto)
                                : $fMeta['gambar'];
                        @endphp
                        <div class="fp-mini">
                            <div class="thumb" style="background-image:url('{{ $fFoto }}')"></div>
                            <div class="info">
                                <span class="eyebrow">Lantai {{ $f->lantai->nomor_lantai }} · {{ $f->kode_fasilitas }}</span>
                                <h3>{{ $f->nama_fasilitas }}</h3>
                                <div class="meta">
                                    <span class="fp-chip"><i class="bi bi-people"></i>{{ $f->kapasitas }} orang</span>
                                    <span class="fp-chip"><i class="bi bi-aspect-ratio"></i>{{ $f->luas }} m²</span>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Tarif ringkas --}}
                    <div class="fp-tarif">
                        <small class="lbl">
                            Tarif per {{ $satuan }}@if($satuan === 'Hari' || $satuan === 'Jam') · 8 jam/hari @endif
                        </small>
                        <div class="row-t">
                            <span>Rp {{ number_format($tarif->harga, 0, ',', '.') }} × {{ $jumlahRuangan }} ruangan</span>
                            <b>Rp {{ number_format($totalTarif, 0, ',', '.') }}</b>
                        </div>
                        <div class="row-t">
                            <span>Total tarif dasar</span>
                            <span class="total">Rp {{ number_format($totalTarif, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    {{-- Fasilitas termasuk (shared) --}}
                    <div class="fp-includes">
                        <h4><i class="bi bi-gift"></i>Fasilitas termasuk</h4>
                        <ul>
                            @foreach ($bawaan as $d)
                                <li><i class="bi bi-check-circle-fill"></i>{{ $d }}</li>
                            @endforeach
                        </ul>
                        @if ($fasilitas->kategori_fasilitas === 'Working Space' && $satuan === 'Bulan')
                            <p class="note"><i class="bi bi-info-circle me-1"></i>Sewa bulanan Working Space diserahkan kosong (tanpa meja &amp; kursi).</p>
                        @endif
                    </div>

                @else
                    {{-- MODE SINGLE: hero card besar --}}
                    @php
                        $fotoUtama = $fasilitas->foto
                            ? \Illuminate\Support\Facades\Storage::url($fasilitas->foto)
                            : $meta['gambar'];
                    @endphp
                    <div class="fp-hero">
                        <div class="foto-wrap">
                            <img src="{{ $fotoUtama }}" alt="{{ $fasilitas->nama_fasilitas }}" class="foto">
                            <span class="cat-badge"><i class="bi {{ $meta['ikon'] }}"></i>{{ $fasilitas->kategori_fasilitas }}</span>
                        </div>
                        <div class="body">
                            <p class="eyebrow">Lantai {{ $fasilitas->lantai->nomor_lantai }} · {{ $fasilitas->kode_fasilitas }}</p>
                            <h1>{{ $fasilitas->nama_fasilitas }}</h1>
                            <div class="meta">
                                <span class="fp-chip"><i class="bi bi-people"></i>{{ $fasilitas->kapasitas }} orang</span>
                                <span class="fp-chip"><i class="bi bi-aspect-ratio"></i>{{ $fasilitas->luas }} m²</span>
                            </div>

                            {{-- Tarif --}}
                            <div class="fp-tarif">
                                <small class="lbl">
                                    Tarif per {{ $satuan }}@if($satuan === 'Hari' || $satuan === 'Jam') · 8 jam/hari @endif
                                </small>
                                <span class="total">Rp {{ number_format($tarif->harga, 0, ',', '.') }}</span>
                            </div>

                            {{-- Fasilitas termasuk --}}
                            <div class="fp-includes">
                                <h4><i class="bi bi-gift"></i>Fasilitas termasuk</h4>
                                <ul>
                                    @foreach ($bawaan as $d)
                                        <li><i class="bi bi-check-circle-fill"></i>{{ $d }}</li>
                                    @endforeach
                                </ul>
                                @if ($fasilitas->kategori_fasilitas === 'Working Space' && $satuan === 'Bulan')
                                    <p class="note"><i class="bi bi-info-circle me-1"></i>Sewa bulanan Working Space diserahkan kosong (tanpa meja &amp; kursi).</p>
                                @endif
                            </div>

                            @if ($fasilitas->deskripsi)
                                <p class="text-muted small mt-3 mb-0 pt-3" style="border-top:1px solid var(--line)">{{ $fasilitas->deskripsi }}</p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            {{-- ═════════════════════════════════════════════════════
                 KOLOM KANAN — Form Jadwal Pemakaian
                 ═════════════════════════════════════════════════════ --}}
            <div class="col-lg-7" data-reveal>
                <div class="fp-form">
                    <div class="fp-form-head">
                        <h2>Jadwal Pemakaian</h2>
                        <span class="satuan"><i class="bi bi-clock-history"></i>Per {{ $satuan }}</span>
                    </div>

                    @if ($isMulti)
                        <div class="fp-hint">
                            <i class="bi bi-info-circle"></i>
                            <div>
                                <b>{{ $jumlahRuangan }} ruangan · satu isian.</b>
                                Jadwal berikut berlaku untuk semua ruangan terpilih.
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('reservasi.keranjang.tambah') }}">
                        @csrf
                        <input type="hidden" name="id_fasilitas" value="{{ $fasilitas->id_fasilitas }}">
                        <input type="hidden" name="id_tarif_sewa" value="{{ $tarif->id_tarif_sewa }}">
                        <input type="hidden" name="antrian" value="{{ request('antrian') }}">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $sehariSaja ? 'Tanggal pemakaian' : 'Tanggal mulai' }}</label>
                                <input type="date" name="tanggal_mulai" class="form-control" required
                                       min="{{ now()->toDateString() }}"
                                       value="{{ old('tanggal_mulai', request('tanggal_mulai')) }}">
                            </div>
                            @if ($sehariSaja)
                                <div class="col-md-6 mb-3 d-flex align-items-end">
                                    <div class="fp-info-slot">
                                        <i class="bi bi-clock-history"></i>
                                        <span>Durasi <strong>1 hari · 8 jam otomatis</strong></span>
                                    </div>
                                </div>
                            @else
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">
                                        Tanggal selesai
                                        @if($satuan==='Bulan')<small class="text-muted">(min. 3 bulan)</small>@endif
                                    </label>
                                    <input type="date" name="tanggal_selesai" class="form-control" required
                                           value="{{ old('tanggal_selesai', request('tanggal_selesai')) }}">
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Jumlah pengguna</label>
                            <input type="number" name="jumlah_pengguna" class="form-control" min="1" max="{{ $kapMin }}" required value="{{ old('jumlah_pengguna', 1) }}">
                            <small class="hint">Maksimal {{ $kapMin }} orang{{ $isMulti ? ' (kapasitas terkecil)' : '' }}.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keperluan</label>
                            <textarea name="keperluan" class="form-control" rows="2" placeholder="Contoh: rapat koordinasi tim" required>{{ old('keperluan') }}</textarea>
                        </div>

                        @if ($satuan === 'Hari')
                            <div class="fp-note">
                                <i class="bi bi-info-circle"></i>
                                <span>Sewa harian &mdash; <strong>1 hari = 8 jam</strong> mengikuti jam layanan gedung.</span>
                            </div>
                        @elseif ($satuan === 'Jam')
                            <div class="fp-note">
                                <i class="bi bi-clock"></i>
                                <span><strong>8 jam otomatis</strong> mengikuti jam layanan gedung (08:00&ndash;16:00).</span>
                            </div>
                        @elseif ($satuan === 'Bulan')
                            <div class="fp-note">
                                <i class="bi bi-file-earmark-text"></i>
                                <span>Sewa bulanan wajib melengkapi dokumen legalitas (Company Profile / KTP) saat checkout.</span>
                            </div>
                        @endif

                        <button type="submit" class="fp-submit">
                            <i class="bi bi-cart-plus"></i>
                            Tambah ke Keranjang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection