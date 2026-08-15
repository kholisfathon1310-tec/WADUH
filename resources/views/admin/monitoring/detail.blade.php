@extends('admin.layouts.app')
@section('title', 'Detail Fasilitas')

@section('actions')
    <a href="{{ route('admin.monitoring', ['lantai' => $fasilitas->id_lantai]) }}" class="btn btn-brand-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
@endsection

@section('content')
    @php
        $foto = $fasilitas->fotoUrls()[0] ?? null;
        $disetujui = $reservasiAktif->filter(fn ($r) => $r->status_reservasi === \App\Enums\StatusReservasi::Disetujui)->count();
        $menunggu = $reservasiAktif->filter(fn ($r) => $r->status_reservasi === \App\Enums\StatusReservasi::Menunggu)->count();
    @endphp
    <div class="row g-3">
        <div class="col-lg-5" data-reveal>
            <div class="xcard h-100">
                @if ($foto)
                    <img src="{{ $foto }}" alt="{{ $fasilitas->nama_fasilitas }}" class="w-100" style="height:180px; object-fit:cover">
                @endif
                <div class="xhead"><span><i class="bi bi-door-open me-1"></i>{{ $fasilitas->nama_fasilitas }}</span>
                    <span class="badge text-bg-{{ $fasilitas->status_aktif->value === 'Aktif' ? 'success' : 'secondary' }}">{{ $fasilitas->status_aktif->value }}</span>
                </div>
                <div class="p-3">
                    <table class="table table-sm mb-0">
                        <tr><th class="text-muted fw-semibold" style="width:38%">Kode</th><td>{{ $fasilitas->kode_fasilitas }}</td></tr>
                        <tr><th class="text-muted fw-semibold">Kategori</th><td>{{ $fasilitas->kategori_fasilitas }}</td></tr>
                        <tr><th class="text-muted fw-semibold">Lantai</th><td>{{ $fasilitas->lantai->nomor_lantai }}</td></tr>
                        <tr><th class="text-muted fw-semibold">Kapasitas</th><td>{{ $fasilitas->kapasitas }} orang</td></tr>
                        <tr><th class="text-muted fw-semibold">Luas</th><td>{{ $fasilitas->luas }} m²</td></tr>
                        @if ($fasilitas->deskripsi)
                            <tr><th class="text-muted fw-semibold">Deskripsi</th><td>{{ $fasilitas->deskripsi }}</td></tr>
                        @endif
                    </table>
                </div>
                @if ($tarifAktif->isNotEmpty())
                    <div class="xhead border-top"><span><i class="bi bi-tag me-1"></i>Tarif Sewa Aktif</span></div>
                    <div class="p-3 pt-2">
                        @foreach ($tarifAktif as $t)
                            <div class="d-flex justify-content-between align-items-center py-1 {{ ! $loop->last ? 'border-bottom' : '' }}">
                                <span class="small text-muted">Per {{ $t->jenisSewa->satuan->value }}</span>
                                <span class="fw-semibold">Rp {{ number_format($t->harga, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="col-lg-7" data-reveal>
            <div class="xcard h-100">
                <div class="xhead">
                    <span><i class="bi bi-calendar-week me-1"></i>Reservasi Aktif Tanggal {{ $slot['tanggal_mulai'] }}@if($slot['tanggal_selesai'] !== $slot['tanggal_mulai']) sampai {{ $slot['tanggal_selesai'] }}@endif</span>
                    <span class="d-flex gap-1">
                        @if ($disetujui) <span class="badge text-bg-success">{{ $disetujui }} disetujui</span> @endif
                        @if ($menunggu) <span class="badge text-bg-warning">{{ $menunggu }} menunggu</span> @endif
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Kode</th><th>Pemesan</th><th>Periode</th><th>Pengguna</th><th>Keperluan</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse ($reservasiAktif as $r)
                            <tr>
                                <td><a class="fw-bold text-decoration-none" style="color:var(--primary)" href="{{ route('admin.reservasi.show', $r->kode_reservasi) }}">{{ $r->kode_reservasi }}</a></td>
                                <td><a class="text-decoration-none" style="color:inherit" href="{{ route('admin.reservasi.show', $r->kode_reservasi) }}" title="Lihat detail reservasi {{ $r->pemesan->nama_lengkap }}">{{ $r->pemesan->nama_lengkap }}</a></td>
                                <td class="small">{{ $r->tanggal_mulai->format('d/m/Y') }}@if($r->jam_mulai) {{ \Illuminate\Support\Str::substr($r->jam_mulai,0,5) }}-{{ \Illuminate\Support\Str::substr($r->jam_selesai,0,5) }}@elseif($r->tanggal_selesai->ne($r->tanggal_mulai)) – {{ $r->tanggal_selesai->format('d/m/Y') }}@endif</td>
                                <td class="small">{{ $r->jumlah_pengguna }} orang</td>
                                <td class="small text-truncate" style="max-width:12rem" title="{{ $r->keperluan }}">{{ $r->keperluan }}</td>
                                <td><span class="badge text-bg-{{ $r->status_reservasi->value === 'Disetujui' ? 'success' : 'warning' }}">{{ $r->status_reservasi->value }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-muted p-3">Tidak ada reservasi aktif pada rentang ini.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
