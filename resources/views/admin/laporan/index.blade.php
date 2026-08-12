@extends('admin.layouts.app')
@section('title', 'Laporan Data Reservasi')

@section('content')
    <form method="GET" action="{{ route('admin.laporan') }}" class="xcard p-3 p-md-4 mb-4" data-filter-form data-reveal>
        <div class="row g-2 g-md-3 align-items-end">
            <div class="col-6 col-md-4">
                <label class="form-label mb-1">Bulan</label>
                <select name="bulan" class="form-select form-select-sm">
                    @foreach ($daftarBulan as $angka => $nama)
                        <option value="{{ $angka }}" @selected($bulan === $angka)>{{ $nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-4">
                <label class="form-label mb-1">Tahun</label>
                <select name="tahun" class="form-select form-select-sm">
                    @foreach ($daftarTahun as $t)
                        <option value="{{ $t }}" @selected($tahun === $t)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <a href="{{ route('admin.laporan') }}" class="btn btn-brand-outline btn-sm w-100" data-filter-reset>Bulan Ini</a>
            </div>
        </div>
    </form>
    {{-- Tombol Ekspor PDF dipindah ke bawah tabel --}}

    <div id="hasil-laporan" data-filter-hasil>
        @include('admin.laporan.partials.hasil')
    </div>

    <script>
        let semuaTampil = false;
        function toggleSemuaBaris() {
            semuaTampil = !semuaTampil;
            document.querySelectorAll('.baris-extra').forEach(tr => tr.classList.toggle('d-none', !semuaTampil));
            const btn = document.getElementById('btnSemua');
            const info = document.getElementById('infoBaris');
            if (btn) btn.innerHTML = semuaTampil
                ? '<i class="bi bi-chevron-double-up me-1"></i>Tampilkan 10 Saja'
                : '<i class="bi bi-chevron-double-down me-1"></i>Lihat Semua';
            if (info) info.textContent = (semuaTampil ? 'seluruh ' : '10 dari ') + document.querySelectorAll('#hasil-laporan tbody tr:not(.fw-bold)').length + ' data';
        }

        (function () {
            const form = document.querySelector('[data-filter-form]');
            const hasil = document.getElementById('hasil-laporan');
            if (!form || !hasil) return;

            let controller = null;

            const terapkan = () => {
                semuaTampil = false;
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
                        window.history.replaceState(null, '', urlLengkap);
                    })
                    .catch((err) => {
                        if (err.name !== 'AbortError') hasil.classList.remove('opacity-50');
                    });
            };

            form.querySelectorAll('select').forEach((el) => {
                el.addEventListener('change', terapkan);
            });

            form.addEventListener('submit', (e) => { e.preventDefault(); terapkan(); });

            const reset = form.querySelector('[data-filter-reset]');
            reset?.addEventListener('click', (e) => {
                e.preventDefault();
                const now = new Date();
                form.querySelector('[name="bulan"]').value = String(now.getMonth() + 1);
                form.querySelector('[name="tahun"]').value = String(now.getFullYear());
                terapkan();
            });
        })();
    </script>
@endsection
