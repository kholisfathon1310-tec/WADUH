@extends('layouts.reservasi')
@section('title', $fasilitas->nama_fasilitas)

@php
    $meta = \App\Support\KategoriMeta::get($fasilitas->kategori_fasilitas);
    $urutanSatuan = ['Jam', 'Hari', 'Bulan'];
@endphp

@section('content')

    {{-- ══════════════════════════════════════════════════════════════
         Style scoped khusus halaman ini — sama seperti hero card reservasi,
         TANPA elemen form/aksi lanjut (halaman ini dead end, murni informasi).
         ══════════════════════════════════════════════════════════════ --}}
    <style>
        .fp-page { --fp-soft:#e6f2f4; --fp-radius:1rem; }

        .fp-page .breadcrumb { font-size:.8rem; margin-bottom:1rem; }
        .fp-page .breadcrumb-item + .breadcrumb-item::before { color:#94a3b8; }
        .fp-page .breadcrumb-item a { color:var(--muted); text-decoration:none; font-weight:600; }
        .fp-page .breadcrumb-item a:hover { color:var(--primary); }
        .fp-page .breadcrumb-item.active { color:var(--ink); font-weight:700; }

        .fp-includes { background:#fff; border:1px solid var(--line); border-radius:var(--fp-radius); padding:1rem 1.2rem; }
        .fp-includes h4 { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:.9rem; margin:0 0 .7rem; display:flex; align-items:center; gap:.5rem; color:var(--ink); }
        .fp-includes h4 i { color:var(--primary); }

        .fp-hero { background:#fff; border:1px solid var(--line); border-radius:var(--fp-radius); overflow:hidden; box-shadow:0 4px 14px -6px rgba(15,23,42,.06); }
        .fp-hero .foto-wrap { position:relative; }
        .fp-hero .foto { width:100%; height:280px; object-fit:cover; display:block; background:#f7f9fc; }
        .fp-carousel .carousel-item .foto { border-radius:0; }
        .fp-carousel .carousel-indicators { margin-bottom:.6rem; }
        .fp-carousel .carousel-indicators [data-bs-target] { width:.5rem; height:.5rem; border-radius:50%; background:#fff; opacity:.6; }
        .fp-carousel .carousel-indicators .active { opacity:1; }
        .fp-carousel .carousel-control-prev, .fp-carousel .carousel-control-next { width:2.75rem; opacity:0; transition:opacity .15s ease; }
        .fp-carousel:hover .carousel-control-prev, .fp-carousel:hover .carousel-control-next { opacity:1; }

        .fp-zoomable { cursor:zoom-in; transition:filter .15s ease; }
        .fp-zoomable:hover { filter:brightness(.92); }

        .fp-lightbox { position:fixed; inset:0; z-index:2000; background:rgba(8,15,25,.9); display:none; align-items:center; justify-content:center; padding:2.5rem; cursor:zoom-out; }
        .fp-lightbox.show { display:flex; }
        .fp-lightbox img { max-width:100%; max-height:100%; border-radius:.9rem; box-shadow:0 24px 60px rgba(0,0,0,.5); cursor:default; }
        .fp-lightbox .fp-lightbox-close { position:absolute; top:1.2rem; right:1.4rem; width:2.6rem; height:2.6rem; border-radius:50%; border:none; background:rgba(255,255,255,.15); color:#fff; font-size:1.3rem; display:grid; place-items:center; cursor:pointer; transition:background .15s ease; }
        .fp-lightbox .fp-lightbox-close:hover { background:rgba(255,255,255,.28); }

        .fp-hero .cat-badge { position:absolute; top:.85rem; left:.85rem; background:rgba(255,255,255,.95); backdrop-filter:blur(6px); color:var(--primary); font-weight:700; font-size:.72rem; padding:.35rem .8rem; border-radius:2rem; box-shadow:0 4px 12px rgba(15,23,42,.1); display:inline-flex; align-items:center; gap:.4rem; }
        .fp-hero .body { padding:1.4rem 1.4rem 1.5rem; }
        .fp-hero .body .eyebrow { font-size:.68rem; font-weight:700; letter-spacing:.14em; color:var(--primary); text-transform:uppercase; margin-bottom:.35rem; }
        .fp-hero .body h1 { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.35rem; color:var(--ink); margin:0 0 .75rem; }
        .fp-hero .meta { display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:1rem; }
        .fp-chip { display:inline-flex; align-items:center; gap:.3rem; background:#f7f9fc; color:var(--ink); font-size:.72rem; font-weight:600; padding:.28rem .6rem; border-radius:2rem; border:1px solid var(--line); }
        .fp-chip i { color:var(--muted); }

        /* Kartu harga per satuan — di sini murni informatif, TIDAK ADA tombol lanjut apa pun. */
        .fp-form { background:#fff; border:1px solid var(--line); border-radius:var(--fp-radius); padding:1.6rem; box-shadow:0 4px 14px -6px rgba(15,23,42,.06); }
        .fp-form-head { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; margin-bottom:1.3rem; padding-bottom:1rem; border-bottom:1px solid #eef2f6; }
        .fp-form-head h2 { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.15rem; color:var(--ink); margin:0; }

        .fp-tarif-row { border:1px solid var(--line); border-radius:.85rem; padding:.9rem 1.05rem; margin-bottom:.75rem; }
        .fp-tarif-row .fp-tarif-top { display:flex; align-items:baseline; justify-content:space-between; gap:.6rem; margin-bottom:.55rem; }
        .fp-tarif-row .satuan { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:.9rem; color:var(--ink); }
        .fp-tarif-row .harga { font-family:'Plus Jakarta Sans',sans-serif; font-size:1.1rem; font-weight:800; color:var(--primary); }
        .fp-tarif-row .bawaan { display:flex; flex-wrap:wrap; gap:.3rem; }
        .fp-tarif-row .bawaan .fp-chip { font-size:.68rem; padding:.22rem .55rem; }

        @media (max-width: 575.98px) { .fp-includes ul { grid-template-columns:1fr; } }
    </style>

    <div class="fp-page">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('fasilitas.index') }}">Fasilitas</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="{{ route('fasilitas.denah', ['kategori' => $fasilitas->kategori_fasilitas, 'lantai' => $fasilitas->id_lantai]) }}">
                        Denah Lantai {{ $fasilitas->lantai->nomor_lantai }}
                    </a>
                </li>
                <li class="breadcrumb-item active">{{ $fasilitas->nama_fasilitas }}</li>
            </ol>
        </nav>

        <div class="row g-4">

            {{-- ═══ KOLOM KIRI — Foto & info ruangan ═══ --}}
            <div class="col-lg-7" data-reveal>
                @php $fotoList = $fasilitas->fotoUrls(); @endphp
                <div class="fp-hero">
                    <div class="foto-wrap">
                        @if (count($fotoList) > 1)
                            <div id="fpCarousel" class="carousel slide fp-carousel">
                                <div class="carousel-inner">
                                    @foreach ($fotoList as $i => $src)
                                        <div class="carousel-item @if ($i === 0) active @endif">
                                            <img src="{{ $src }}" alt="{{ $fasilitas->nama_fasilitas }} — foto {{ $i + 1 }}" class="foto fp-zoomable" data-zoom-src="{{ $src }}">
                                        </div>
                                    @endforeach
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#fpCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Sebelumnya</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#fpCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Berikutnya</span>
                                </button>
                                <div class="carousel-indicators">
                                    @foreach ($fotoList as $i => $src)
                                        <button type="button" data-bs-target="#fpCarousel" data-bs-slide-to="{{ $i }}" @if ($i === 0) class="active" @endif aria-label="Foto {{ $i + 1 }}"></button>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <img src="{{ $fotoList[0] }}" alt="{{ $fasilitas->nama_fasilitas }}" class="foto fp-zoomable" data-zoom-src="{{ $fotoList[0] }}">
                        @endif
                        <span class="cat-badge"><i class="bi {{ $meta['ikon'] }}"></i>{{ $fasilitas->kategori_fasilitas }}</span>
                    </div>
                    <div class="body">
                        <p class="eyebrow">Lantai {{ $fasilitas->lantai->nomor_lantai }} · {{ $fasilitas->kode_fasilitas }}</p>
                        <h1>{{ $fasilitas->nama_fasilitas }}</h1>
                        <div class="meta">
                            <span class="fp-chip"><i class="bi bi-people"></i>{{ $fasilitas->kapasitas }} orang</span>
                            <span class="fp-chip"><i class="bi bi-aspect-ratio"></i>{{ $fasilitas->luas }} m²</span>
                        </div>

                        @if ($fasilitas->deskripsi)
                            <p class="text-muted small mt-3 mb-0 pt-3" style="border-top:1px solid var(--line)">{{ $fasilitas->deskripsi }}</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ═══ KOLOM KANAN — Harga per satuan (informasi saja) ═══ --}}
            <div class="col-lg-5" data-reveal>
                <div class="fp-form">
                    <div class="fp-form-head">
                        <h2>Harga &amp; Fasilitas</h2>
                    </div>

                    @forelse ($urutanSatuan as $satuan)
                        @continue (! $tarifPerSatuan->has($satuan))
                        @php $t = $tarifPerSatuan[$satuan]; @endphp
                        <div class="fp-tarif-row">
                            <div class="fp-tarif-top">
                                <span class="satuan">Per {{ $satuan }}</span>
                                <span class="harga">Rp {{ number_format($t->harga, 0, ',', '.') }}</span>
                            </div>
                            <div class="bawaan">
                                @foreach ($bawaanPerSatuan[$satuan] as $d)
                                    <span class="fp-chip"><i class="bi bi-check-circle-fill"></i>{{ $d }}</span>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small">Belum ada tarif aktif untuk fasilitas ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="fp-lightbox" id="fpLightbox">
        <button type="button" class="fp-lightbox-close" aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        <img src="" alt="" id="fpLightboxImg">
    </div>
    <script>
        (function () {
            const overlay = document.getElementById('fpLightbox');
            const img = document.getElementById('fpLightboxImg');
            if (!overlay || !img) return;

            const buka = (src, alt) => {
                img.src = src;
                img.alt = alt || '';
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            };
            const tutup = () => {
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            };

            document.querySelectorAll('.fp-zoomable').forEach((el) => {
                el.addEventListener('click', () => buka(el.dataset.zoomSrc, el.alt || el.getAttribute('aria-label')));
                el.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); buka(el.dataset.zoomSrc, el.alt || el.getAttribute('aria-label')); }
                });
            });
            overlay.addEventListener('click', (e) => { if (e.target === overlay) tutup(); });
            overlay.querySelector('.fp-lightbox-close').addEventListener('click', tutup);
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') tutup(); });
        })();
    </script>
@endsection
