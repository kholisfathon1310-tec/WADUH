@extends('admin.layouts.app')
@section('title', 'Data Reservasi')

@section('content')
    <form method="GET" class="xcard p-3 mb-3">
        <div class="row g-2">
            <div class="col-md-3"><label class="form-label mb-1">Nama pemesan</label><input name="pemesan" class="form-control form-control-sm" placeholder="Cari nama…" value="{{ $filter['pemesan'] ?? '' }}"></div>
            <div class="col-md-2">
                <label class="form-label mb-1">Kategori</label>
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarKategori as $k)<option value="{{ $k }}" @selected(($filter['kategori'] ?? '')===$k)>{{ $k }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Jenis sewa</label>
                <select name="jenis_sewa" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarJenis as $j)<option value="{{ $j->satuan->value }}" @selected(($filter['jenis_sewa'] ?? '')===$j->satuan->value)>Per {{ $j->satuan->value }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua</option>
                    @foreach ($daftarStatus as $s)<option value="{{ $s->value }}" @selected(($filter['status'] ?? '')===$s->value)>{{ $s->value }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><label class="form-label mb-1">Tanggal reservasi</label><input type="date" name="tanggal" class="form-control form-control-sm" value="{{ $filter['tanggal'] ?? '' }}"></div>
            <div class="col-md-1 d-flex align-items-end gap-1">
                <button class="btn btn-brand btn-sm flex-fill" title="Terapkan filter"><i class="bi bi-funnel"></i></button>
                <a href="{{ route('admin.reservasi.index') }}" class="btn btn-brand-outline btn-sm" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </div>
    </form>

    <p class="text-muted small mb-2"><i class="bi bi-collection me-1"></i>{{ $grup->count() }} pemesanan · {{ $grup->flatten()->count() }} ruangan</p>

    @forelse ($grup as $kodeTransaksi => $baris)
        @php $first = $baris->first(); @endphp
        <div class="xcard mb-3 overflow-hidden">
            {{-- Header pemesanan: satu tombol Detail untuk seluruh ruangan --}}
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-3"
                 style="background:linear-gradient(90deg,#f2f8fb,#fbfdfe); border-bottom:1px solid var(--line)">
                <div>
                    <span class="fw-bold fs-6" style="color:var(--primary)">{{ $kodeTransaksi ?: '(tanpa kode)' }}</span>
                    <span class="cell-sub d-block mt-1"><i class="bi bi-person me-1"></i>{{ $first->pemesan->nama_lengkap }} · <i class="bi bi-clock me-1"></i>{{ $first->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge text-bg-light border py-2">{{ $baris->count() }} ruangan</span>
                    <span class="fw-bold" style="color:var(--primary)">Rp {{ number_format($baris->sum('total_biaya'), 0, ',', '.') }}</span>
                    <a href="{{ route('admin.reservasi.show', $first->kode_reservasi) }}" class="btn btn-sm btn-brand"><i class="bi bi-eye me-1"></i>Detail</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead><tr>
                        <th>Ruangan</th><th>Jenis</th><th>Periode</th><th class="text-end">Total</th><th class="text-center">Status</th>
                    </tr></thead>
                    <tbody>
                    @foreach ($baris as $r)
                        <tr>
                            <td>
                                <span class="cell-main">{{ $r->tarifSewa->fasilitas->nama_fasilitas }}</span>
                                <span class="cell-sub d-block">{{ $r->tarifSewa->fasilitas->kategori_fasilitas }} · Lt {{ $r->tarifSewa->fasilitas->lantai->nomor_lantai }}</span>
                            </td>
                            <td><span class="badge text-bg-light border">Per {{ $r->tarifSewa->jenisSewa->satuan->value }}</span></td>
                            <td class="small">
                                @if ($r->jam_mulai)
                                    <i class="bi bi-calendar3 me-1 text-muted"></i>{{ $r->tanggal_mulai->format('d/m/Y') }}
                                    <span class="text-muted">·</span> {{ \Illuminate\Support\Str::substr($r->jam_mulai,0,5) }}–{{ \Illuminate\Support\Str::substr($r->jam_selesai,0,5) }}
                                @else
                                    <i class="bi bi-calendar3 me-1 text-muted"></i>{{ $r->tanggal_mulai->format('d/m/Y') }} – {{ $r->tanggal_selesai->format('d/m/Y') }}
                                @endif
                            </td>
                            <td class="text-end fw-semibold">Rp {{ number_format($r->total_biaya, 0, ',', '.') }}</td>
                            <td class="text-center"><span class="chip {{ strtolower($r->status_reservasi->value) }}">{{ $r->status_reservasi->value }}</span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="xcard p-5 text-center">
            <div class="text-muted"><i class="bi bi-inbox fs-1 d-block mb-2"></i>Tidak ada reservasi yang cocok dengan filter.</div>
        </div>
    @endforelse
@endsection
