@extends('layouts.reservasi')
@section('title', 'Pilih Jenis Sewa')

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 2])
@endsection

@php
    $ikon = ['Jam' => 'bi-clock', 'Hari' => 'bi-calendar-day', 'Bulan' => 'bi-calendar-month'];
    $desk = [
        'Jam'   => 'Cocok untuk rapat & sesi singkat. Bayar per jam pemakaian.',
        'Hari'  => 'Untuk acara atau kegiatan satu hari atau lebih, dengan 1 hari dihitung 8 jam pemakaian.',
        'Bulan' => 'Sewa jangka panjang untuk kantor / usaha (min. 3 bulan).',
    ];
@endphp

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('reservasi.index') }}">Kategori</a></li>
        <li class="breadcrumb-item active">{{ $kategori }}</li>
    </ol></nav>

    <div class="page-head mb-4 p-4 p-md-5 rounded-4 text-white" style="background:linear-gradient(115deg,#0d2a3a,#145f7c 55%,#168b88)" data-reveal>
        <p class="eyebrow-sm mb-1" style="color:#a9e6dd">Langkah 2 dari 5 · {{ $kategori }}</p>
        <h1 class="h3 mb-1">Pilih Jenis Sewa</h1>
        <p class="mb-0" style="opacity:.85">Pilih satuan sewa yang tersedia untuk kategori ini.</p>
    </div>

    <div class="row g-3" data-reveal>
        @foreach ($jenis as $j)
            <div class="col-md-4">
                <a href="{{ route('reservasi.lantai', ['kategori' => $kategori, 'jenis' => $j->id_jenis_sewa]) }}" class="xcard hover h-100 p-4">
                    <div class="icon-tile mb-3"><i class="bi {{ $ikon[$j->satuan->value] ?? 'bi-clock-history' }}"></i></div>
                    <h2 class="h5 mb-1">Per {{ $j->satuan->value }}</h2>
                    <p class="text-muted small mb-2">{{ $desk[$j->satuan->value] ?? '' }}</p>
                    <span class="badge text-bg-light border mb-3">Durasi min. {{ $j->durasi_minimum }} {{ strtolower($j->satuan->value) }}</span><br>
                    <span class="fw-bold" style="color:var(--primary)">Pilih <i class="bi bi-arrow-right"></i></span>
                </a>
            </div>
        @endforeach
    </div>
@endsection
