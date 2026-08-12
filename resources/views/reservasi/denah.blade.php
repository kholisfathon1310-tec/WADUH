@extends('layouts.reservasi')
@section('title', 'Denah Lantai '.$lantai->nomor_lantai)

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 4])
@endsection

@php
    // Per Jam & Convention Hall: pemakaian selalu 1 hari (otomatis 8 jam) — filter cukup satu tanggal,
    // tidak perlu pilih jam mulai/selesai secara manual.
    $sehariSaja = $kategori === 'Convention Hall' || ($jenis && $jenis->satuan->value === 'Jam');
@endphp

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('reservasi.index') }}">Kategori</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.jenis-sewa', ['kategori' => $kategori]) }}">{{ $kategori }}</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.lantai', ['kategori' => $kategori, 'jenis' => $jenis?->id_jenis_sewa]) }}">Lantai</a></li>
        <li class="breadcrumb-item active">Lantai {{ $lantai->nomor_lantai }}</li>
    </ol></nav>

    <div data-reveal>
        <x-denah.schedule-filter
            :jenis-id="$jenis?->id_jenis_sewa"
            :sehari-saja="$sehariSaja"
            :jadwal="$slot"
        />
    </div>

    <div id="hasil-denah" data-filter-hasil>
        @include('reservasi.partials.denah-hasil')
    </div>

    <script>
        (function () {
            const form = document.querySelector('[data-filter-form]');
            const hasil = document.getElementById('hasil-denah');
            if (!form || !hasil) return;

            let controller = null;

            const terapkan = () => {
                controller?.abort();
                controller = new AbortController();

                const params = new URLSearchParams(new FormData(form));
                [...params.keys()].forEach((k) => { if (!params.get(k)) params.delete(k); });
                const url = form.getAttribute('action') || window.location.pathname;
                const urlLengkap = url + (params.toString() ? '?' + params.toString() : '');

                hasil.classList.add('opacity-50');
                fetch(urlLengkap, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, signal: controller.signal })
                    .then((r) => r.text())
                    .then((html) => {
                        hasil.innerHTML = html;
                        hasil.classList.remove('opacity-50');
                        window.initDenah?.(hasil);
                        window.history.replaceState(null, '', urlLengkap);
                    })
                    .catch((err) => {
                        if (err.name !== 'AbortError') hasil.classList.remove('opacity-50');
                    });
            };

            form.querySelectorAll('input[type="date"]').forEach((el) => {
                el.addEventListener('change', terapkan);
            });

            form.addEventListener('submit', (e) => { e.preventDefault(); terapkan(); });
        })();
    </script>
@endsection
