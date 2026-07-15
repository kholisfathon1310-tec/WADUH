@extends('layouts.reservasi')
@section('title', $fasilitas->nama_fasilitas)

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 4])
@endsection

@php
    $satuan = $jenis->satuan->value;
    $meta = \App\Support\KategoriMeta::get($fasilitas->kategori_fasilitas);
    // Convention Hall: sewa harian hanya SATU hari (8 jam) — cukup satu field tanggal.
    $sehariSaja = $fasilitas->kategori_fasilitas === 'Convention Hall' && $satuan === 'Hari';
@endphp

@section('content')
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('reservasi.index') }}">Kategori</a></li>
        <li class="breadcrumb-item"><a href="{{ route('reservasi.denah', ['kategori' => $fasilitas->kategori_fasilitas, 'lantai' => $fasilitas->id_lantai, 'jenis' => $jenis->id_jenis_sewa]) }}">Denah Lantai {{ $fasilitas->lantai->nomor_lantai }}</a></li>
        <li class="breadcrumb-item active">{{ $fasilitas->nama_fasilitas }}</li>
    </ol></nav>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="xcard h-100 overflow-hidden d-flex flex-column">
                {{-- Foto fasilitas --}}
                <div class="position-relative">
                    <img src="{{ $fasilitas->foto ? \Illuminate\Support\Facades\Storage::url($fasilitas->foto) : $meta['gambar'] }}"
                         alt="{{ $fasilitas->nama_fasilitas }}" style="width:100%; height:210px; object-fit:cover">
                    <span class="avail position-absolute top-0 start-0 m-2" style="background:rgba(255,255,255,.92); color:{{ $meta['warna'] }}">
                        <i class="bi {{ $meta['ikon'] }}"></i> {{ $fasilitas->kategori_fasilitas }}
                    </span>
                </div>
                <div class="p-4 d-flex flex-column flex-grow-1">
                    <p class="eyebrow-sm mb-1">Lantai {{ $fasilitas->lantai->nomor_lantai }} · {{ $fasilitas->kode_fasilitas }}</p>
                    <h1 class="h4 mb-2">{{ $fasilitas->nama_fasilitas }}</h1>
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge text-bg-light border py-2 px-3"><i class="bi bi-people me-1"></i>{{ $fasilitas->kapasitas }} orang</span>
                        <span class="badge text-bg-light border py-2 px-3"><i class="bi bi-aspect-ratio me-1"></i>{{ $fasilitas->luas }} m²</span>
                    </div>

                    <div class="p-3 rounded-3 mb-3" style="background:var(--surface)">
                        <span class="text-muted small d-block">Tarif per {{ $satuan }} @if($satuan === 'Hari')<span class="fw-semibold">(1 hari = 8 jam)</span>@endif</span>
                        <span class="h4 fw-bold" style="color:var(--primary)">Rp {{ number_format($tarif->harga, 0, ',', '.') }}</span>
                    </div>

                    {{-- Fasilitas bawaan — dihitung dari config via FasilitasBawaanService (ikut jenis sewa) --}}
                    @php $bawaan = app(\App\Services\FasilitasBawaanService::class)->untuk($fasilitas, $satuan); @endphp
                    <p class="fw-bold small mb-2"><i class="bi bi-gift me-1" style="color:{{ $meta['warna'] }}"></i>Fasilitas yang Anda dapatkan (sewa per {{ strtolower($satuan) }}):</p>
                    <div class="row g-1 small text-muted mb-2">
                        @foreach ($bawaan as $d)
                            <div class="col-6"><i class="bi bi-check-circle-fill me-1" style="color:{{ $meta['warna'] }}"></i>{{ $d }}</div>
                        @endforeach
                    </div>
                    @if ($fasilitas->kategori_fasilitas === 'Working Space' && $satuan === 'Bulan')
                        <p class="small text-muted fst-italic mb-2"><i class="bi bi-info-circle me-1"></i>Sewa bulanan Working Space diserahkan kosong (tanpa meja & kursi).</p>
                    @endif
                    @if ($fasilitas->deskripsi)
                        <p class="text-muted small mb-0 mt-auto border-top pt-2">{{ $fasilitas->deskripsi }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="xcard p-4">
                <h2 class="h5 mb-1">Isi Jadwal <span class="badge text-bg-light border align-middle">Per {{ $satuan }}</span></h2>
                <p class="text-muted small mb-3">Lengkapi jadwal pemakaian, lalu tambahkan ke keranjang.</p>
                @php
                    $antrianIds = array_filter(explode(',', (string) request('antrian')));
                    $ruangLain = $antrianIds ? \App\Models\Fasilitas::whereIn('id_fasilitas', $antrianIds)->get(['nama_fasilitas', 'kapasitas']) : collect();
                    $kapMin = min([$fasilitas->kapasitas, ...$ruangLain->pluck('kapasitas')->all()]);
                @endphp
                @if ($ruangLain->isNotEmpty())
                    <div class="p-3 rounded-3 mb-3" style="background:#e8f4f8; border:1px solid #bfdde8;">
                        <div class="fw-bold small mb-1"><i class="bi bi-collection me-1" style="color:var(--primary)"></i>Isi sekali untuk {{ 1 + $ruangLain->count() }} ruangan sekaligus</div>
                        <div class="d-flex flex-wrap gap-1 mb-1">
                            <span class="badge text-bg-light border">{{ $fasilitas->nama_fasilitas }}</span>
                            @foreach ($ruangLain as $rl)<span class="badge text-bg-light border">{{ $rl->nama_fasilitas }}</span>@endforeach
                        </div>
                        <small class="text-muted">Jadwal & data di bawah berlaku untuk semua ruangan di atas — semuanya langsung masuk keranjang.</small>
                    </div>
                @endif
                <form method="POST" action="{{ route('reservasi.keranjang.tambah') }}">
                    @csrf
                    <input type="hidden" name="id_fasilitas" value="{{ $fasilitas->id_fasilitas }}">
                    <input type="hidden" name="id_tarif_sewa" value="{{ $tarif->id_tarif_sewa }}">
                    <input type="hidden" name="antrian" value="{{ request('antrian') }}">

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ $sehariSaja ? 'Tanggal pemakaian' : 'Tanggal mulai' }}</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required
                                   min="{{ now()->toDateString() }}"
                                   value="{{ old('tanggal_mulai', request('tanggal_mulai')) }}">
                        </div>
                        @if ($satuan === 'Jam')
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Jam mulai</label>
                                @include('reservasi.partials.pilih-jam', ['name' => 'jam_mulai', 'value' => old('jam_mulai', request('jam_mulai'))])
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Jam selesai</label>
                                @include('reservasi.partials.pilih-jam', ['name' => 'jam_selesai', 'value' => old('jam_selesai', request('jam_selesai'))])
                            </div>
                        @elseif ($sehariSaja)
                            {{-- Convention Hall: 1 hari saja — jam otomatis terhitung 8 jam, tanpa field selesai. --}}
                            <div class="col-md-6 mb-3 d-flex align-items-end">
                                <div class="p-2 px-3 rounded-3 w-100 small" style="background:var(--surface)">
                                    <i class="bi bi-clock-history me-1" style="color:var(--primary)"></i>
                                    Pemakaian <strong>1 hari (otomatis 8 jam)</strong> pada tanggal tersebut.
                                </div>
                            </div>
                        @else
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal selesai @if($satuan==='Bulan')<small class="text-muted">(min. 3 bulan)</small>@endif</label>
                                <input type="date" name="tanggal_selesai" class="form-control" required
                                       value="{{ old('tanggal_selesai', request('tanggal_selesai')) }}">
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Jumlah pengguna</label>
                        <input type="number" name="jumlah_pengguna" class="form-control" min="1" max="{{ $kapMin }}" required value="{{ old('jumlah_pengguna', 1) }}">
                        <small class="text-muted">Maksimal {{ $kapMin }} orang{{ $ruangLain->isNotEmpty() ? ' (kapasitas terkecil dari ruangan terpilih)' : '' }}.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keperluan</label>
                        <textarea name="keperluan" class="form-control" rows="2" placeholder="Contoh: rapat koordinasi tim" required>{{ old('keperluan') }}</textarea>
                    </div>

                    @if ($satuan === 'Hari')
                        <div class="alert alert-info py-2 small"><i class="bi bi-info-circle me-1"></i>Sewa harian: <strong>1 hari dihitung 8 jam pemakaian</strong> (mengikuti jam layanan gedung).</div>
                    @elseif ($satuan === 'Bulan')
                        <div class="alert alert-info py-2 small"><i class="bi bi-info-circle me-1"></i>Sewa bulanan memerlukan dokumen persyaratan (Company Profile / legalitas / KTP) yang diunggah saat checkout.</div>
                    @endif

                    <button class="btn btn-brand w-100 py-2"><i class="bi bi-cart-plus me-1"></i> Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
    </div>
@endsection
