@extends('layouts.reservasi')
@section('title', 'Preview Denah Gedung BITC')

@section('content')
    @php
        // Ambil semua kunci lantai dari config (biar otomatis kalau ada lantai baru).
        $lantaiKeys = array_keys(config('denah-layout', []));

        // Lantai yang sedang aktif dari query ?lantai=... atau default lantai pertama.
        $lantaiAktif = request('lantai', $lantaiKeys[0] ?? '1');
        if (! in_array($lantaiAktif, $lantaiKeys, true)) {
            $lantaiAktif = $lantaiKeys[0] ?? '1';
        }
    @endphp

    {{-- Tab navigasi antar lantai --}}
    <div class="d-flex flex-wrap gap-2 mb-4" data-reveal>
        @foreach ($lantaiKeys as $key)
            @php $label = 'L' . $key; @endphp
            <a href="{{ url()->current() }}?lantai={{ $key }}"
               class="btn btn-sm {{ $key === $lantaiAktif ? 'btn-brand' : 'btn-outline-secondary' }}"
               style="border-radius:2rem; padding:.5rem 1.15rem; font-weight:700; font-size:.85rem;">
                {{ $label }}
            </a>
        @endforeach
    </div>

    {{-- Denah untuk lantai aktif --}}
    <div data-reveal>
        <x-denah-layout :lantai="$lantaiAktif" />
    </div>

    <p class="text-muted small mt-3">
        <i class="bi bi-info-circle me-1"></i>
        Halaman preview visual denah gedung BITC. Kode &amp; luas ruangan sesuai data resmi.
        Untuk melakukan reservasi, gunakan halaman
        <a href="{{ route('reservasi.index') }}">Reservasi</a>.
    </p>
@endsection