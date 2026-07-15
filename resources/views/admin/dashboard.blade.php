@extends('admin.layouts.app')
@section('title', 'Dashboard')

@php
    $me = Auth::guard('admin')->user();
    $badge = ['Menunggu'=>'warning','Disetujui'=>'success','Ditolak'=>'danger','Selesai'=>'secondary','Dibatalkan'=>'dark'];

    $cards = [
        ['Menunggu', $statistik['menunggu'], 'linear-gradient(135deg,#d9a521,#e5b94e)', 'hourglass-split'],
        ['Disetujui', $statistik['disetujui'], 'linear-gradient(135deg,#0d8a5f,#25b47e)', 'check-circle'],
        ['Ditolak', $statistik['ditolak'], 'linear-gradient(135deg,#b23030,#d95757)', 'x-circle'],
        ['Dibatalkan', $statistik['dibatalkan'], 'linear-gradient(135deg,#3a4653,#5d6b7a)', 'slash-circle'],
    ];

    // Donut distribusi status (CSS conic-gradient)
    $total = max(1, $statistik['total']);
    $seg = [
        ['Menunggu', $statistik['menunggu'], '#e5b94e'],
        ['Disetujui', $statistik['disetujui'], '#25b47e'],
        ['Ditolak', $statistik['ditolak'], '#d95757'],
        ['Dibatalkan', $statistik['dibatalkan'], '#5d6b7a'],
    ];
    $stops = []; $acc = 0;
    foreach ($seg as [$lbl, $n, $c]) {
        $from = $acc; $acc += $n / $total * 100;
        if ($n > 0) $stops[] = "$c {$from}% {$acc}%";
    }
    $donut = $stops ? 'conic-gradient('.implode(', ', $stops).')' : 'conic-gradient(#e4ebf2 0% 100%)';

    $maxKat = max(1, (int) collect($fasilitasPerKategori)->max());
    $warnaKat = ['Working Space' => '#2f7fd1', 'Co-Working Space' => '#7c5cd6', 'Convention Hall' => '#d6527c'];
@endphp

@section('content')
    {{-- Banner sambutan --}}
    <div class="rounded-4 p-4 mb-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-3 position-relative overflow-hidden"
         style="background:linear-gradient(115deg,#12303f,#176b87 60%,#1c8f80)">
        <div style="position:absolute; right:-4rem; top:-7rem; width:16rem; height:16rem; border:2.2rem solid rgba(255,255,255,.07); border-radius:50%"></div>
        <div>
            <p class="mb-1" style="opacity:.75; font-size:.85rem"><i class="bi bi-stars me-1"></i>{{ now()->translatedFormat('l, d F Y') }}</p>
            <h2 class="h4 mb-1">Halo, {{ $me?->nama_admin }} 👋</h2>
            <p class="mb-0" style="opacity:.85">
                @if ($statistik['menunggu'] > 0)
                    Ada <strong>{{ $statistik['menunggu'] }} reservasi menunggu</strong> persetujuanmu hari ini.
                @else
                    Tidak ada antrian persetujuan — semua reservasi sudah tertangani. 🎉
                @endif
            </p>
        </div>
        <div class="d-flex gap-2 position-relative">
            <a href="{{ route('admin.reservasi.index', ['status' => 'Menunggu']) }}" class="btn btn-light fw-bold" style="border-radius:.7rem"><i class="bi bi-hourglass-split me-1"></i>Proses Antrian</a>
            <a href="{{ route('admin.monitoring') }}" class="btn btn-outline-light fw-bold" style="border-radius:.7rem"><i class="bi bi-grid-3x3-gap me-1"></i>Monitoring</a>
        </div>
    </div>

    {{-- Stat cards gradien --}}
    <div class="row g-3 mb-4">
        @foreach ($cards as [$label, $nilai, $grad, $ikon])
            <div class="col-6 col-lg-3">
                <div class="stat-card h-100" style="background:{{ $grad }}">
                    <i class="ic bi bi-{{ $ikon }}"></i>
                    <div class="v">{{ $nilai }}</div>
                    <small>{{ $label }}</small>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        {{-- Donut distribusi status --}}
        <div class="col-lg-4">
            <div class="xcard h-100">
                <div class="xhead"><span><i class="bi bi-pie-chart me-1"></i>Distribusi Status</span><span class="badge text-bg-light border">{{ $statistik['total'] }} total</span></div>
                <div class="p-4 text-center">
                    <div class="mx-auto position-relative" style="width:170px;height:170px;border-radius:50%;background:{{ $donut }}">
                        <div class="position-absolute top-50 start-50 translate-middle bg-white rounded-circle d-grid place-items-center" style="width:110px;height:110px;display:grid;place-items:center">
                            <div>
                                <div class="fw-bold fs-3 brand-font" style="color:var(--primary)">{{ $statistik['total'] }}</div>
                                <small class="text-muted">Reservasi</small>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3">
                        @foreach ($seg as [$lbl, $n, $c])
                            <span class="avail" style="background:{{ $c }}1f; color:{{ $c }}"><span class="rounded-circle d-inline-block" style="width:.5rem;height:.5rem;background:{{ $c }}"></span>{{ $lbl }} · {{ $n }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Fasilitas aktif per kategori (progress) --}}
        <div class="col-lg-3">
            <div class="xcard h-100">
                <div class="xhead"><span><i class="bi bi-building me-1"></i>Fasilitas Aktif</span></div>
                <div class="p-3">
                    @forelse ($fasilitasPerKategori as $kategori => $jumlah)
                        @php $wk = $warnaKat[$kategori] ?? '#176b87'; @endphp
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small fw-semibold mb-1">
                                <span>{{ $kategori }}</span><span style="color:{{ $wk }}">{{ $jumlah }}</span>
                            </div>
                            <div class="progress" style="height:.55rem; border-radius:1rem">
                                <div class="progress-bar" style="width:{{ round($jumlah / $maxKat * 100) }}%; background:{{ $wk }}; border-radius:1rem"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada fasilitas aktif.</p>
                    @endforelse
                    <a href="{{ route('admin.laporan') }}" class="btn btn-brand-outline btn-sm w-100 mt-2"><i class="bi bi-file-earmark-bar-graph me-1"></i>Lihat Laporan</a>
                </div>
            </div>
        </div>

        {{-- Reservasi terbaru --}}
        <div class="col-lg-5">
            <div class="xcard h-100">
                <div class="xhead">
                    <span><i class="bi bi-clock-history me-1"></i>Reservasi Terbaru</span>
                    <a href="{{ route('admin.reservasi.index') }}" class="btn btn-brand-outline btn-sm">Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead><tr><th>Kode</th><th>Pemesan</th><th>Status</th></tr></thead>
                        <tbody>
                        @forelse ($terbaru as $r)
                            <tr>
                                <td>
                                    <a class="fw-bold text-decoration-none" style="color:var(--primary)" href="{{ route('admin.reservasi.show', $r->kode_reservasi) }}">{{ $r->kode_reservasi }}</a>
                                    <div class="small text-muted">{{ $r->tarifSewa->fasilitas->nama_fasilitas }}</div>
                                </td>
                                <td>{{ $r->pemesan->nama_lengkap }}</td>
                                <td><span class="badge text-bg-{{ $badge[$r->status_reservasi->value] ?? 'secondary' }}">{{ $r->status_reservasi->value }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-muted p-3">Belum ada reservasi.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
