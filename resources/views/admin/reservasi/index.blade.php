@extends('admin.layouts.app')
@section('title', 'Data Reservasi')

@section('content')
    <form method="GET" class="xcard p-3 p-md-4 mb-4" data-filter-form data-reveal>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Nama pemesan</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-search"></i></span>
                    <input name="pemesan" class="form-control border-start-0 ps-0" placeholder="Cari nama…" value="{{ $filter['pemesan'] ?? '' }}" autocomplete="off">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Kategori</label>
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarKategori as $k)<option value="{{ $k }}" @selected(($filter['kategori'] ?? '')===$k)>{{ $k }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Jenis sewa</label>
                <select name="jenis_sewa" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarJenis as $j)<option value="{{ $j->satuan->value }}" @selected(($filter['jenis_sewa'] ?? '')===$j->satuan->value)>Per {{ $j->satuan->value }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarStatus as $s)<option value="{{ $s->value }}" @selected(($filter['status'] ?? '')===$s->value)>{{ $s->value }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label mb-1">Tanggal reservasi</label>
                <input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $filter['tanggal'] ?? '' }}">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-3 pt-3" style="border-top:1px solid var(--line)">
            <a href="{{ route('admin.reservasi.index') }}" class="btn btn-brand-outline btn-sm px-3" data-filter-reset><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
        </div>
    </form>

    <div id="hasil-reservasi" data-filter-hasil>
        @include('admin.reservasi.partials.hasil')
    </div>

    <script>
        (function () {
            const form = document.querySelector('[data-filter-form]');
            const hasil = document.getElementById('hasil-reservasi');
            if (!form || !hasil) return;

            let timer = null;
            let controller = null;

            const terapkan = () => {
                clearTimeout(timer);
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

            // Dropdown & tanggal: langsung terapkan begitu berubah.
            form.querySelectorAll('select, input[type="date"]').forEach((el) => {
                el.addEventListener('change', terapkan);
            });

            // Nama pemesan: pencarian langsung sambil mengetik (debounce 350ms).
            const pencarian = form.querySelector('input[name="pemesan"]');
            pencarian?.addEventListener('input', () => {
                clearTimeout(timer);
                timer = setTimeout(terapkan, 350);
            });

            // Submit manual (tombol/​Enter) tetap dipertahankan, tapi tanpa reload halaman.
            form.addEventListener('submit', (e) => { e.preventDefault(); terapkan(); });

            // Tombol Reset: kosongkan SEMUA field ke default ("Semua"/kosong), bukan form.reset()
            // (yang hanya kembali ke filter awal saat halaman dimuat, mis. dari link ?status=Menunggu).
            const reset = form.querySelector('[data-filter-reset]');
            reset?.addEventListener('click', (e) => {
                e.preventDefault();
                form.querySelectorAll('input').forEach((el) => { el.value = ''; });
                form.querySelectorAll('select').forEach((el) => { el.selectedIndex = 0; });
                terapkan();
            });
        })();
    </script>
@endsection
