@extends('admin.layouts.app')
@section('title', 'Laporan Data Fasilitas')

@section('content')
    <form method="GET" action="{{ route('admin.laporan') }}" class="xcard p-3 mb-3">
        <div class="row g-2 align-items-end">
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Lantai</label>
                <select name="lantai" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarLantai as $l)<option value="{{ $l->id_lantai }}" @selected((string)($filter['lantai'] ?? '')===(string)$l->id_lantai)>Lantai {{ $l->nomor_lantai }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Kategori</label>
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarKategori as $k)<option value="{{ $k }}" @selected(($filter['kategori'] ?? '')===$k)>{{ $k }}</option>@endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    <option value="ISI" @selected(($filter['status'] ?? '')==='ISI')>ISI</option>
                    <option value="Available" @selected(($filter['status'] ?? '')==='Available')>Available</option>
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button class="btn btn-brand btn-sm flex-fill"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <a href="{{ route('admin.laporan') }}" class="btn btn-brand-outline btn-sm">Reset</a>
            </div>
        </div>
    </form>
    {{-- Tombol Ekspor PDF dipindah ke bawah tabel --}}

    <div class="row g-3 mb-3">
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#3a4653,#5d6b7a)"><i class="ic bi bi-lock"></i><div class="v">{{ $totalIsi }}</div><small>ISI (terpakai / non-aktif)</small></div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card" style="background:linear-gradient(135deg,#0d8a5f,#25b47e)"><i class="ic bi bi-unlock"></i><div class="v">{{ $totalAvailable }}</div><small>Available</small></div>
        </div>
    </div>

    <div class="xcard">
        <div class="xhead">
            <span><i class="bi bi-table me-1"></i>Data Fasilitas Gedung BITC per/{{ $judulTanggal }}</span>
            @if ($rows->count() > 10)
                <span class="d-flex align-items-center gap-2">
                    <span class="text-muted small fw-normal" id="infoBaris">10 dari {{ $rows->count() }} data</span>
                    <button type="button" class="btn btn-brand-outline btn-sm" id="btnSemua" onclick="toggleSemuaBaris()">
                        <i class="bi bi-chevron-double-down me-1"></i>Lihat Semua
                    </button>
                </span>
            @else
                <span class="badge text-bg-light border">{{ $rows->count() }} data</span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0 align-middle">
                <thead class="text-center">
                    <tr>
                        <th>Nomor</th><th class="text-start">Uraian</th><th>Volume</th><th>Satuan</th>
                        <th>Harga Per/Bulan (Rp)</th><th>Keterangan</th><th>ISI</th><th>Available</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($rows as $row)
                    <tr @if($loop->iteration > 10) class="baris-extra d-none" @endif>
                        <td class="text-center">{{ $row['no'] }}</td>
                        <td>{{ $row['uraian'] }}</td>
                        <td class="text-end">{{ $row['volume'] !== null ? number_format($row['volume'], 2, ',', '.').' m2' : '-' }}</td>
                        <td class="text-center">1</td>
                        <td class="text-end">
                            @if ($row['harga'] !== null)
                                Rp {{ number_format($row['harga'], 2, ',', '.') }}
                                @if ($row['estimasi'])<small class="text-muted d-block">({{ $row['estimasi'] }})</small>@endif
                            @else - @endif
                        </td>
                        <td class="text-center"><span class="avail {{ $row['keterangan']==='ISI' ? 'merah' : 'hijau' }}">{{ $row['keterangan'] }}</span></td>
                        <td class="text-center">{{ $row['isi'] ? 1 : '' }}</td>
                        <td class="text-center">{{ $row['available'] ? 1 : '' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted p-4">Tidak ada data untuk filter ini.</td></tr>
                @endforelse
                @if ($rows->isNotEmpty())
                    <tr class="fw-bold">
                        <td colspan="6" class="text-end">TOTAL</td>
                        <td class="text-center">{{ $totalIsi }}</td>
                        <td class="text-center">{{ $totalAvailable }}</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Ekspor di bawah tabel, rata kanan --}}
    <div class="d-flex justify-content-end mt-3">
        <a href="{{ route('admin.laporan.pdf', request()->query()) }}" class="btn btn-danger"><i class="bi bi-file-earmark-pdf me-1"></i>Ekspor PDF</a>
    </div>

    @if ($rows->count() > 10)
        <script>
            let semuaTampil = false;
            function toggleSemuaBaris() {
                semuaTampil = !semuaTampil;
                document.querySelectorAll('.baris-extra').forEach(tr => tr.classList.toggle('d-none', !semuaTampil));
                document.getElementById('btnSemua').innerHTML = semuaTampil
                    ? '<i class="bi bi-chevron-double-up me-1"></i>Tampilkan 10 Saja'
                    : '<i class="bi bi-chevron-double-down me-1"></i>Lihat Semua';
                document.getElementById('infoBaris').textContent = semuaTampil
                    ? 'seluruh {{ $rows->count() }} data'
                    : '10 dari {{ $rows->count() }} data';
            }
        </script>
    @endif
@endsection
