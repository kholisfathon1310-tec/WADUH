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

    // Rekap status untuk FloorHeader — dihitung dari data yang sudah dikirim controller, tanpa query baru.
    $jumlahKosong = $status->filter(fn ($s) => $s === 'hijau')->count();
    $jumlahTerisi = $status->filter(fn ($s) => $s !== 'hijau')->count();
@endphp

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('reservasi.index') }}">Kategori</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.jenis-sewa', ['kategori' => $kategori]) }}">{{ $kategori }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.lantai', ['kategori' => $kategori, 'jenis' => $jenis?->id_jenis_sewa]) }}">Lantai</a></li>
        <li class="breadcrumb-item active">Lantai {{ $lantai->nomor_lantai }}</li>
    </ol></nav>

    <div data-reveal>
        <x-denah.floor-header
            :nomor-lantai="$lantai->nomor_lantai"
            :kategori="$kategori"
            :satuan="$jenis?->satuan->value"
            :warna="$w"
            :total="$fasilitas->count()"
            :kosong="$jumlahKosong"
            :terisi="$jumlahTerisi"
        />
    </div>

    <div data-reveal>
        <x-denah.schedule-filter
            :jenis-id="$jenis?->id_jenis_sewa"
            :is-jam="$isJam"
            :sehari-saja="$sehariSaja"
            :jadwal="$slot"
        />
    </div>

    {{-- Denah interaktif SVG — koordinat presisi dari config/denah.php --}}
    @php
        $statusByKode = $fasilitas->mapWithKeys(fn ($f) => [$f->kode_fasilitas => $status[$f->id_fasilitas] ?? 'hijau'])->all();
        $tplDetail = route('reservasi.fasilitas.show', array_merge(['fasilitas' => '__ID__', 'jenis' => $jenis?->id_jenis_sewa], $slot));
    @endphp
    <x-denah :lantai="$lantai->nomor_lantai" :status-per-fasilitas="$statusByKode" :clickable="true" :link-template="$tplDetail" :jenis="$jenis?->id_jenis_sewa"/>

    <p class="text-muted small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>Klik ruangan <strong class="text-success">hijau/kuning</strong> untuk memilih (bisa lebih dari satu), lalu tekan <strong>Isi Jadwal</strong>. Ruangan <strong>merah</strong> penuh atau sedang tidak disewakan.</p>
@endsection
