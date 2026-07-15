@extends('layouts.reservasi')
@section('title', 'Pilih Lantai')

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 3])
@endsection

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('reservasi.index') }}">Kategori</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.jenis-sewa', ['kategori' => $kategori]) }}">{{ $kategori }}</a></li>
        <li class="breadcrumb-item active">Lantai</li>
    </ol></nav>

    @php $warnaLantai = ['1' => '#2f7fd1', '2' => '#24aa9a', '3A' => '#7c5cd6', '3B' => '#e8833a', '5' => '#d6527c']; @endphp

    <div class="page-head mb-4 p-4 rounded-4 text-white" style="background:linear-gradient(115deg,#145f7c,#168b88)">
        <p class="eyebrow-sm mb-1" style="color:#a9e6dd">Langkah 3 dari 5 · {{ $kategori }} @if($jenis) · Per {{ $jenis->satuan->value }} @endif</p>
        <h1 class="h3 mb-1">Pilih lantai 🏢</h1>
        <p class="mb-0" style="opacity:.85">Lantai berikut memiliki fasilitas yang cocok dengan pilihan Anda.</p>
    </div>

    <div class="row g-3">
        @forelse ($lantai as $l)
            @php $w = $warnaLantai[$l->nomor_lantai] ?? '#176b87'; @endphp
            <div class="col-6 col-md-3">
                <a href="{{ route('reservasi.denah', ['kategori' => $kategori, 'lantai' => $l->id_lantai, 'jenis' => $jenis?->id_jenis_sewa]) }}"
                   class="xcard hover text-center p-4 h-100" style="border-bottom:4px solid {{ $w }}">
                    <div class="mx-auto mb-2" style="width:3.6rem;height:3.6rem;border-radius:1rem;display:grid;place-items:center;background:{{ $w }};color:#fff;font-weight:800;font-size:1.3rem;font-family:'Plus Jakarta Sans',sans-serif;box-shadow:0 10px 20px {{ $w }}44">{{ $l->nomor_lantai }}</div>
                    <div class="fw-bold">Lantai {{ $l->nomor_lantai }}</div>
                    <div class="small" style="color:{{ $w }}">Lihat denah <i class="bi bi-arrow-right"></i></div>
                </a>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-warning">Tidak ada lantai dengan fasilitas yang cocok.</div></div>
        @endforelse
    </div>
@endsection
