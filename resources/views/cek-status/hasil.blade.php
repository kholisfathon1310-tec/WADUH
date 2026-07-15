@extends('layouts.reservasi')
@section('title', 'Hasil Cek Status')

@php
    $badge = ['Menunggu' => 'warning', 'Disetujui' => 'success', 'Ditolak' => 'danger', 'Selesai' => 'secondary', 'Dibatalkan' => 'dark'];
@endphp

@section('content')
<style>
    .tracker { display:flex; align-items:flex-start; gap:0; margin:.75rem 0 .25rem; }
    .tstep { flex:1 1 0; text-align:center; position:relative; min-width:70px; }
    .tstep .tdot { width:2.1rem; height:2.1rem; border-radius:50%; display:grid; place-items:center; margin:0 auto .35rem; background:#e6edf3; color:#8a97a5; font-size:.95rem; position:relative; z-index:1; border:3px solid #fff; box-shadow:0 0 0 1px #e0e8ef; }
    .tstep .tlabel { font-size:.72rem; font-weight:700; color:#8a97a5; }
    .tstep::before { content:''; position:absolute; top:1.05rem; left:-50%; width:100%; height:3px; background:#e2e9f0; z-index:0; }
    .tstep:first-child::before { display:none; }
    .tstep.done .tdot { background:var(--teal); color:#fff; box-shadow:0 4px 12px rgba(36,170,154,.4); }
    .tstep.done .tlabel { color:var(--teal); }
    .tstep.done::before { background:var(--teal); }
    .tstep.now .tdot { background:var(--primary); color:#fff; box-shadow:0 4px 14px rgba(23,107,135,.45); animation:pulse 1.8s infinite; }
    .tstep.now .tlabel { color:var(--primary); }
    .tstep.now::before { background:var(--teal); }
    .tstep.bad .tdot { background:#d95757; color:#fff; box-shadow:0 4px 12px rgba(217,87,87,.4); }
    .tstep.bad .tlabel { color:#d95757; }
    .tstep.bad::before { background:#d95757; }
    .tstep.off .tdot { background:#3a4653; color:#fff; }
    .tstep.off .tlabel { color:#3a4653; }
    .tstep.off::before { background:#3a4653; }
    @keyframes pulse { 0%,100% { transform:scale(1) } 50% { transform:scale(1.12) } }
    .res-card { overflow:hidden; }
    .res-card .thumb { width:100%; height:100%; min-height:150px; object-fit:cover; }
</style>

    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('cek-status.form') }}">Cek Status</a></li>
        <li class="breadcrumb-item active">{{ $kode }}</li>
    </ol></nav>

    <div class="page-head mb-4 p-4 rounded-4 text-white d-flex flex-wrap justify-content-between align-items-center gap-2" style="background:linear-gradient(115deg,#145f7c,#168b88)">
        <div>
            <p class="eyebrow-sm mb-1" style="color:#a9e6dd">Status Reservasi</p>
            <h1 class="h4 mb-0">Kode: {{ $kode }}</h1>
        </div>
        @if ($reservasi->isNotEmpty())
            <div class="text-center px-3 py-2 rounded-3" style="background:rgba(255,255,255,.15)">
                <div class="fs-4 fw-bold">{{ $reservasi->count() }}</div>
                <small>reservasi ditemukan</small>
            </div>
        @endif
    </div>

    @if ($reservasi->isEmpty())
        <div class="xcard p-5 text-center">
            <div class="icon-tile mx-auto mb-3"><i class="bi bi-emoji-neutral"></i></div>
            <h2 class="h6">Tidak ada reservasi dengan kode tersebut</h2>
            <p class="text-muted small mb-3">Pastikan kode diketik lengkap, termasuk awalan TRX- atau RSV-.</p>
            <a href="{{ route('cek-status.form') }}" class="btn btn-brand-outline btn-sm px-4">Coba Lagi</a>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
        @foreach ($reservasi as $r)
            @php
                $status = $r->status_reservasi->value;
                $meta = \App\Support\KategoriMeta::get($r->tarifSewa->fasilitas->kategori_fasilitas);
                // Batal hanya boleh selama proses verifikasi (Menunggu) & tanggal belum lewat.
                $bolehBatal = $status === 'Menunggu' && $r->tanggal_mulai->startOfDay()->gte(\Illuminate\Support\Carbon::today());
                // Label yang ditampilkan ke pemesan: "Menunggu" → "Diverifikasi".
                $labelStatus = $status === 'Menunggu' ? 'Diverifikasi' : $status;

                // Tracker 3 langkah: Diajukan → Diverifikasi → hasil akhir (tanpa langkah "Selesai").
                $steps = match ($status) {
                    'Ditolak'    => [['Diajukan','done','send'], ['Diverifikasi','done','hourglass-split'], ['Ditolak','bad','x-lg']],
                    'Dibatalkan' => [['Diajukan','done','send'], ['Diverifikasi','done','hourglass-split'], ['Dibatalkan','off','slash-circle']],
                    'Menunggu'   => [['Diajukan','done','send'], ['Diverifikasi','now','hourglass-split'], ['Disetujui','','check-lg']],
                    default      => [['Diajukan','done','send'], ['Diverifikasi','done','hourglass-split'], ['Disetujui','done','check-lg']], // Disetujui / Selesai
                };
            @endphp
            <div class="xcard res-card">
                <div class="row g-0">
                    <div class="col-md-3 d-none d-md-block position-relative">
                        <img src="{{ $meta['gambar'] }}" class="thumb" alt="{{ $r->tarifSewa->fasilitas->kategori_fasilitas }}">
                        <span class="avail position-absolute top-0 start-0 m-2" style="background:rgba(255,255,255,.92); color:{{ $meta['warna'] }}">
                            <i class="bi {{ $meta['ikon'] }}"></i> {{ $r->tarifSewa->fasilitas->kategori_fasilitas }}
                        </span>
                    </div>
                    <div class="col-md-9">
                        <div class="p-3 p-md-4">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                                <div>
                                    <h2 class="h6 mb-1">{{ $r->tarifSewa->fasilitas->nama_fasilitas }}
                                        <span class="badge text-bg-{{ $badge[$status] ?? 'secondary' }}">{{ $labelStatus }}</span>
                                    </h2>
                                    <div class="small text-muted">
                                        <span class="me-2"><i class="bi bi-upc"></i> {{ $r->kode_reservasi }}</span>
                                        <span class="me-2"><i class="bi bi-receipt"></i> {{ $r->kode_transaksi }}</span>
                                        <span><i class="bi bi-person"></i> {{ $r->pemesan->nama_lengkap }}</span>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold fs-5" style="color:var(--primary)">Rp {{ number_format($r->total_biaya, 0, ',', '.') }}</div>
                                    <small class="text-muted">{{ $r->durasi }} {{ strtolower($r->tarifSewa->jenisSewa->satuan->value) }} · {{ $r->jumlah_pengguna }} org</small>
                                </div>
                            </div>

                            {{-- Tracker status --}}
                            <div class="tracker">
                                @foreach ($steps as [$lbl, $state, $ic])
                                    <div class="tstep {{ $state }}">
                                        <div class="tdot"><i class="bi bi-{{ $ic }}"></i></div>
                                        <div class="tlabel">{{ $lbl }}</div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge text-bg-light border py-2"><i class="bi bi-calendar3 me-1"></i>{{ $r->tanggal_mulai->translatedFormat('d M Y') }}@if($r->tanggal_selesai->ne($r->tanggal_mulai)) → {{ $r->tanggal_selesai->translatedFormat('d M Y') }}@endif</span>
                                    @if($r->jam_mulai)<span class="badge text-bg-light border py-2"><i class="bi bi-clock me-1"></i>{{ \Illuminate\Support\Str::substr($r->jam_mulai,0,5) }}–{{ \Illuminate\Support\Str::substr($r->jam_selesai,0,5) }} WIB</span>@endif
                                    <span class="badge text-bg-light border py-2">Per {{ $r->tarifSewa->jenisSewa->satuan->value }}</span>
                                </div>
                                @if ($bolehBatal)
                                    <form method="POST" action="{{ route('reservasi.batalkan', $r->kode_reservasi) }}"
                                          data-confirm="Reservasi {{ $r->kode_reservasi }} akan dibatalkan dan tidak dapat dikembalikan."
                                          data-confirm-title="Batalkan reservasi ini?" data-icon="warning"
                                          data-confirm-text="Ya, batalkan" data-confirm-color="#d95757">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-x-circle me-1"></i>Batalkan</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    @endif
@endsection
