@extends('layouts.reservasi')
@section('title', 'Pilih Jenis Fasilitas')

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 1])
@endsection

@section('content')
    <div class="page-head mb-4 p-4 rounded-4 text-white" style="background:linear-gradient(115deg,#145f7c,#168b88)">
        <p class="eyebrow-sm mb-1" style="color:#a9e6dd">Langkah 1 dari 5</p>
        <h1 class="h3 mb-1">Mau reservasi ruang apa? ✨</h1>
        <p class="mb-0" style="opacity:.85">Pilih jenis fasilitas BITC — lengkap dengan gambaran ruang & fasilitas yang Anda dapatkan.</p>
    </div>

    @if ($kategori->isEmpty())
        <div class="alert alert-warning">Belum ada fasilitas aktif yang tersedia.</div>
    @else
        <div class="row g-3">
            @foreach ($kategori as $kat)
                @php $meta = \App\Support\KategoriMeta::get($kat); @endphp
                <div class="col-md-4">
                    <a href="{{ route('reservasi.jenis-sewa', ['kategori' => $kat]) }}" class="xcard hover h-100 overflow-hidden d-flex flex-column">
                        <div class="position-relative">
                            <img src="{{ $meta['gambar'] }}" alt="{{ $kat }}" style="width:100%; height:170px; object-fit:cover">
                            @if ($meta['lokasi'])
                                <span class="avail position-absolute top-0 end-0 m-2" style="background:rgba(255,255,255,.92); color:{{ $meta['warna'] }}"><i class="bi bi-layers"></i> {{ $meta['lokasi'] }}</span>
                            @endif
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="icon-tile" style="width:2.4rem; height:2.4rem; font-size:1rem; background:{{ $meta['warna'] }}1a; color:{{ $meta['warna'] }}"><i class="bi {{ $meta['ikon'] }}"></i></span>
                                <h2 class="h5 mb-0">{{ $kat }}</h2>
                            </div>
                            <p class="text-muted small mb-2">{{ $meta['desk'] }}</p>
                            <ul class="list-unstyled small text-muted mb-3">
                                @foreach (array_slice($meta['dapat'], 0, 3) as $d)
                                    <li><i class="bi bi-check-circle-fill me-1" style="color:{{ $meta['warna'] }}"></i>{{ $d }}</li>
                                @endforeach
                                <li class="ms-4 fst-italic">+ {{ count($meta['dapat']) - 3 }} fasilitas lainnya…</li>
                            </ul>
                            <div class="fw-bold mt-auto" style="color:{{ $meta['warna'] }}">Pilih {{ $kat }} <i class="bi bi-arrow-right"></i></div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
@endsection
