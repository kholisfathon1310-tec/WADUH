@extends('layouts.reservasi')
@section('title', 'Denah Lantai '.$lantai->nomor_lantai)

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 4])
@endsection

@php
    $isJam = $jenis && $jenis->satuan->value === 'Jam';
    // Convention Hall: sewa harian hanya 1 hari (8 jam) — filter cukup satu tanggal.
    $sehariSaja = $kategori === 'Convention Hall';
    $warnaLantai = ['1' => '#2f7fd1', '2' => '#24aa9a', '3A' => '#7c5cd6', '3B' => '#e8833a', '5' => '#d6527c'];
    $w = $warnaLantai[$lantai->nomor_lantai] ?? '#176b87';
@endphp

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('reservasi.index') }}">Kategori</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.jenis-sewa', ['kategori' => $kategori]) }}">{{ $kategori }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.lantai', ['kategori' => $kategori, 'jenis' => $jenis?->id_jenis_sewa]) }}">Lantai</a></li>
        <li class="breadcrumb-item active">Lantai {{ $lantai->nomor_lantai }}</li>
    </ol></nav>

    {{-- Header lantai berwarna --}}
    <div class="page-head mb-3 p-4 rounded-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-3"
         style="background:linear-gradient(120deg, {{ $w }}, {{ $w }}bb)">
        <div>
            <p class="eyebrow-sm mb-1" style="color:rgba(255,255,255,.8)">Langkah 4 dari 5 · {{ $kategori }} @if($jenis)· Per {{ $jenis->satuan->value }}@endif</p>
            <h1 class="h3 mb-1">Denah Lantai {{ $lantai->nomor_lantai }} 🗺️</h1>
            <p class="mb-0" style="opacity:.9">Klik ruangan <strong>hijau</strong> untuk memesan — warna mengikuti jadwal yang Anda pilih.</p>
        </div>
        <div class="text-center px-3 py-2 rounded-3" style="background:rgba(255,255,255,.15); backdrop-filter:blur(6px)">
            <div class="display-6 fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif">{{ $lantai->nomor_lantai }}</div>
            <small>{{ $fasilitas->count() }} ruang</small>
        </div>
    </div>

    {{-- Filter jadwal --}}
    <form method="GET" class="xcard p-3 mb-3">
        <input type="hidden" name="jenis" value="{{ $jenis?->id_jenis_sewa }}">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">{{ $sehariSaja ? 'Tanggal pemakaian' : 'Tanggal mulai' }}</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ $slot['tanggal_mulai'] }}">
            </div>
            @if ($isJam)
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1">Jam mulai</label>
                    @include('reservasi.partials.pilih-jam', ['name' => 'jam_mulai', 'value' => $slot['jam_mulai'], 'required' => false, 'kecil' => true])
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label mb-1">Jam selesai</label>
                    @include('reservasi.partials.pilih-jam', ['name' => 'jam_selesai', 'value' => $slot['jam_selesai'], 'required' => false, 'kecil' => true])
                </div>
            @elseif ($sehariSaja)
                <div class="col-6 col-md-3 d-flex align-items-end pb-1">
                    <small class="text-muted"><i class="bi bi-clock-history me-1"></i>1 hari = otomatis 8 jam pemakaian</small>
                </div>
            @else
                <div class="col-6 col-md-3"><label class="form-label mb-1">Tanggal selesai</label><input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ $slot['tanggal_selesai'] }}"></div>
            @endif
            <div class="col-12 col-md-3"><button class="btn btn-brand btn-sm w-100"><i class="bi bi-search me-1"></i>Cek Ketersediaan</button></div>
            <div class="col-12 col-md-auto ms-md-auto small d-flex gap-2 align-items-center pb-1">
                <span class="avail hijau"><i class="bi bi-check-circle"></i> Tersedia</span>
                <span class="avail kuning"><i class="bi bi-exclamation-circle"></i> Sebagian</span>
                <span class="avail merah"><i class="bi bi-x-circle"></i> Penuh</span>
            </div>
        </div>
    </form>

    {{-- Denah interaktif SVG — koordinat presisi dari config/denah.php --}}
    @php
        $statusByKode = $fasilitas->mapWithKeys(fn ($f) => [$f->kode_fasilitas => $status[$f->id_fasilitas] ?? 'hijau'])->all();
        $tplDetail = route('reservasi.fasilitas.show', array_merge(['fasilitas' => '__ID__', 'jenis' => $jenis?->id_jenis_sewa], $slot));
    @endphp
    <x-denah :lantai="$lantai->nomor_lantai" :status-per-fasilitas="$statusByKode" :clickable="true" :link-template="$tplDetail"/>

    <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Klik ruangan <strong class="text-success">hijau/kuning</strong> untuk memilih (bisa lebih dari satu), lalu tekan <strong>Isi Jadwal</strong>. Ruangan <strong>merah</strong> penuh atau sedang tidak disewakan.</p>
@endsection
