@extends('admin.layouts.app')
@section('title', 'Monitoring Fasilitas')

@php
    $warnaLantai = ['1' => '#2f7fd1', '2' => '#24aa9a', '3A' => '#7c5cd6', '3B' => '#e8833a', '5' => '#d6527c'];
    $wl = $warnaLantai[$lantai?->nomor_lantai] ?? '#176b87';
@endphp

@section('content')
    <div class="rounded-4 p-4 mb-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-3"
         style="background:linear-gradient(120deg, {{ $wl }}, {{ $wl }}bb); box-shadow:0 16px 34px -14px rgba(21,36,59,.35)" data-reveal>
        <div>
            <p class="mb-1" style="opacity:.8; font-size:.85rem"><i class="bi bi-grid-3x3-gap me-1"></i>Monitoring Fasilitas</p>
            <h2 class="h4 mb-0">Lantai {{ $lantai?->nomor_lantai }} · {{ $fasilitas->first()?->kategori_fasilitas ?? '-' }}</h2>
        </div>
        <div class="text-center px-3 py-2 rounded-3" style="background:rgba(255,255,255,.16)">
            <div class="fs-4 fw-bold brand-font">{{ $fasilitas->count() }}</div>
            <small>ruang di lantai ini</small>
        </div>
    </div>

    <form method="GET" class="xcard p-3 p-md-4 mb-4" data-reveal>
        <input type="hidden" name="lantai" value="{{ $lantai?->id_lantai }}">
        <div class="row g-2 g-md-3 align-items-end">
            <div class="col-6 col-md-3"><label class="form-label mb-1">Tanggal</label><input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="{{ $slot['tanggal_mulai'] }}"></div>
            <div class="col-6 col-md-3"><label class="form-label mb-1">s/d tanggal</label><input type="date" name="tanggal_selesai" class="form-control form-control-sm" value="{{ $slot['tanggal_selesai'] }}"></div>
            <div class="col-6 col-md-2"><button class="btn btn-brand btn-sm w-100"><i class="bi bi-search me-1"></i>Cek</button></div>
            <div class="col-12 col-md-auto ms-md-auto d-flex flex-wrap align-items-center gap-2">
                <span class="avail hijau"><i class="bi bi-check-circle"></i> Tersedia</span>
                <span class="avail kuning"><i class="bi bi-exclamation-circle"></i> Sebagian terisi</span>
                <span class="avail merah"><i class="bi bi-x-circle"></i> Penuh / tidak aktif</span>
            </div>
        </div>
        <small class="text-muted d-block mt-2"><i class="bi bi-info-circle me-1"></i>Pilih lantai lewat submenu Monitoring di sidebar.</small>
    </form>

    {{-- Denah interaktif SVG — klik ruangan untuk membuka detail monitoring --}}
    @php
        $statusByKode = $fasilitas->mapWithKeys(fn ($f) => [$f->kode_fasilitas => $status[$f->id_fasilitas] ?? 'merah'])->all();
        $tplDetail = route('admin.monitoring.detail', array_merge(['fasilitas' => '__ID__'], $slot));
    @endphp
    @if ($lantai)
        <x-denah :lantai="$lantai->nomor_lantai" :status-per-fasilitas="$statusByKode" :clickable="false" :link-template="$tplDetail"/>
        <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Klik ruangan pada denah untuk melihat detail & jadwal reservasinya. Ruangan merah = penuh atau tidak aktif.</p>
    @else
        <div class="alert alert-warning">Tidak ada lantai.</div>
    @endif
@endsection
