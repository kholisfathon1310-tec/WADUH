@extends('admin.layouts.app')
@section('title', 'Detail Reservasi')

@php
    $r = $reservasi;
    $adaDisetujui = $items->contains(fn ($it) => $it->status_reservasi->value === 'Disetujui');
    $totalSemua = $items->sum('total_biaya');
@endphp

@section('actions')
    <a href="{{ route('admin.reservasi.index') }}" class="btn btn-brand-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
@endsection

@section('content')
<style>
    .info-grid { display:grid; grid-template-columns:38% 1fr; }
    .info-grid > div { padding:.6rem .95rem; border-bottom:1px solid #eef3f8; font-size:.9rem; }
    .info-grid > div:nth-child(4n+1), .info-grid > div:nth-child(4n+2) { background:#fbfdfe; }
    .info-grid .k { color:var(--muted); font-weight:600; }
    .info-grid > div:nth-last-child(-n+2) { border-bottom:0; }
    .timeline { position:relative; padding-left:1.4rem; }
    .timeline::before { content:''; position:absolute; left:.42rem; top:.4rem; bottom:.4rem; width:2px; background:#dfe8f0; border-radius:2px; }
    .timeline .titem { position:relative; padding:.35rem 0 1rem; }
    .timeline .titem::before { content:''; position:absolute; left:-1.4rem; top:.5rem; width:.9rem; height:.9rem; border-radius:50%; background:#fff; border:3px solid var(--teal); box-shadow:0 2px 6px rgba(21,36,59,.12); }
    .timeline .titem.first::before { border-color:var(--primary); }
    .hero-strip { display:flex; flex-wrap:wrap; border-top:1px solid var(--line); }
    .hero-strip .hs { flex:1 1 140px; padding:.95rem 1.15rem; border-right:1px solid var(--line); }
    .hero-strip .hs:last-child { border-right:0; }
    .hero-strip .hs small { color:var(--muted); font-weight:600; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; display:block; }
    .hero-strip .hs .val { font-weight:700; }
    .room-card { border:1px solid var(--line); border-radius:1rem; overflow:hidden; transition:box-shadow .2s ease; }
    .room-card:hover { box-shadow:0 6px 18px rgba(21,36,59,.07); }
    .room-card + .room-card { margin-top:1rem; }
    .room-head { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.5rem; padding:.85rem 1.1rem; background:#f7fafc; border-bottom:1px solid var(--line); }
</style>

    {{-- Hero pemesanan --}}
    <div class="xcard overflow-hidden mb-3" data-reveal>
        <div class="p-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <small class="text-muted fw-semibold text-uppercase" style="letter-spacing:.06em">Kode Reservasi</small>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="h4 mb-0 fw-bold" style="color:var(--primary)">{{ $r->kode_transaksi }}</span>
                    <span class="badge text-bg-light border">{{ $items->count() }} ruangan</span>
                </div>
            </div>
            <div>
                @if ($adaDisetujui)
                    {{-- Idempotent: klik pertama menerbitkan, berikutnya mengunduh PDF yang sama (1 faktur utk semua ruangan). --}}
                    <form method="POST" action="{{ route('admin.reservasi.faktur.cetak', $r->kode_reservasi) }}">@csrf
                        <button class="btn btn-brand"><i class="bi bi-receipt me-1"></i>Cetak Faktur</button>
                    </form>
                @endif
            </div>
        </div>
        <div class="hero-strip">
            <div class="hs"><small>Pemesan</small><span class="val">{{ $r->pemesan->nama_lengkap }}</span></div>
            <div class="hs"><small>Diajukan</small><span class="val">{{ $r->created_at->format('d/m/Y H:i') }}</span></div>
            <div class="hs"><small>Jumlah Ruangan</small><span class="val">{{ $items->count() }}</span></div>
            <div class="hs"><small>Total Keseluruhan</small><span class="val" style="color:var(--primary)">Rp {{ number_format($totalSemua, 0, ',', '.') }}</span></div>
        </div>
    </div>

    <div class="row g-3" data-reveal>
        {{-- Kiri --}}
        <div class="col-lg-7">
            {{-- Ruangan yang dipesan --}}
            <div class="xcard mb-3">
                <div class="xhead"><span><i class="bi bi-door-open me-1"></i>Ruangan yang Dipesan</span>
                    <span class="badge text-bg-light border">{{ $items->count() }}</span></div>
                <div class="p-3">
                    @foreach ($items as $it)
                        @php
                            $fas = $it->tarifSewa->fasilitas;
                            $satuan = $it->tarifSewa->jenisSewa->satuan->value;
                            $cl = $checklists[$it->id_reservasi];
                            $lolos = collect($cl)->every(fn ($c) => $c['passed']);
                        @endphp
                        <div class="room-card">
                            <div class="room-head">
                                <div>
                                    <span class="fw-bold">{{ $fas->nama_fasilitas }}</span>
                                    <span class="cell-sub d-block">{{ $fas->kategori_fasilitas }} · Lantai {{ $fas->lantai->nomor_lantai }} · {{ $fas->kode_fasilitas }}</span>
                                </div>
                                <span class="chip {{ strtolower($it->status_reservasi->value) }}">{{ $it->status_reservasi->value }}</span>
                            </div>
                            <div class="info-grid">
                                <div class="k">Periode</div>
                                <div>
                                    @if ($it->jam_mulai)
                                        {{ $it->tanggal_mulai->translatedFormat('d M Y') }} · {{ \Illuminate\Support\Str::substr($it->jam_mulai,0,5) }}–{{ \Illuminate\Support\Str::substr($it->jam_selesai,0,5) }} WIB
                                    @else
                                        {{ $it->tanggal_mulai->translatedFormat('d M Y') }} – {{ $it->tanggal_selesai->translatedFormat('d M Y') }}
                                    @endif
                                    · {{ $it->durasi }} {{ strtolower($satuan) }}
                                </div>
                                <div class="k">Harga</div>
                                <div>Rp {{ number_format($it->harga_satuan, 0, ',', '.') }} / {{ strtolower($satuan) }}@if($satuan === 'Hari') <span class="text-muted small">(1 hari = 8 jam)</span>@endif → <strong style="color:var(--primary)">Rp {{ number_format($it->total_biaya, 0, ',', '.') }}</strong></div>
                                <div class="k">Pengguna · Keperluan</div>
                                <div>{{ $it->jumlah_pengguna }} orang · {{ $it->keperluan }}</div>
                                <div class="k">Fasilitas bawaan</div>
                                <div class="small text-muted">{{ implode(' · ', app(\App\Services\FasilitasBawaanService::class)->untuk($fas, $satuan)) }}</div>
                                <div class="k">Checklist kelayakan</div>
                                <div>
                                    @foreach ($cl as $c)
                                        <div class="small">
                                            <i class="bi bi-{{ $c['passed'] ? 'check-circle-fill text-success' : 'x-circle-fill text-danger' }}"></i>
                                            {{ $c['label'] }} <span class="text-muted">— {{ $c['note'] }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Identitas pemesan (tanpa kotak inisial) --}}
            <div class="xcard mb-3">
                <div class="xhead"><span><i class="bi bi-person-vcard me-1"></i>Identitas Pemesan</span></div>
                <div class="info-grid">
                    <div class="k">Nama</div><div class="fw-semibold">{{ $r->pemesan->nama_lengkap }}</div>
                    <div class="k">Email</div><div>{{ $r->pemesan->email }}</div>
                    <div class="k">No. Telepon</div><div>{{ $r->pemesan->no_telepon }}</div>
                    <div class="k">Usia · Pekerjaan</div><div>{{ $r->pemesan->usia }} tahun · {{ $r->pemesan->pekerjaan }}</div>
                    <div class="k">Alamat</div><div>{{ $r->pemesan->alamat }}</div>
                </div>
            </div>

            {{-- Dokumen persyaratan seluruh ruangan --}}
            @php $semuaDokumen = $items->flatMap(fn ($it) => $it->dokumenPersyaratan->map(fn ($d) => ['dok' => $d, 'ruang' => $it->tarifSewa->fasilitas->nama_fasilitas])); @endphp
            <div class="xcard">
                <div class="xhead"><span><i class="bi bi-paperclip me-1"></i>Dokumen Persyaratan</span>
                    <span class="badge text-bg-light border">{{ $semuaDokumen->count() }} file</span></div>
                <div class="p-3">
                    @forelse ($semuaDokumen as $row)
                        @php $dok = $row['dok']; @endphp
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 {{ ! $loop->last ? 'border-bottom' : '' }} py-2">
                            <div>
                                <div class="cell-main small">{{ $dok->jenis_dokumen }} <span class="text-muted fw-normal">· {{ $row['ruang'] }}</span></div>
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($dok->lokasi_file) }}" target="_blank" class="cell-sub text-decoration-none">{{ $dok->nama_file }} <i class="bi bi-box-arrow-up-right"></i></a>
                                <span class="badge text-bg-{{ ['Menunggu'=>'warning','Valid'=>'success','Tidak Valid'=>'danger'][$dok->status_verifikasi->value] }} ms-1">{{ $dok->status_verifikasi->value }}</span>
                            </div>
                            @if ($dok->status_verifikasi->value !== 'Valid')
                                {{-- Satu tombol ceklis; setelah Valid tombol hilang. --}}
                                <form method="POST" action="{{ route('admin.reservasi.dokumen.verifikasi', $dok->id_dokumen) }}"
                                      data-confirm="Dokumen {{ $dok->nama_file }} akan ditandai VALID."
                                      data-confirm-title="Validasi dokumen ini?" data-icon="question"
                                      data-confirm-text="Ya, valid" data-confirm-color="#25b47e">@csrf
                                    <input type="hidden" name="status_verifikasi" value="Valid">
                                    <button class="btn btn-sm btn-outline-success" title="Tandai Valid"><i class="bi bi-check-lg"></i> Validasi</button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-muted small mb-0"><i class="bi bi-inbox me-1"></i>Tidak ada dokumen persyaratan untuk pemesanan ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Kanan --}}
        <div class="col-lg-5">
            @if ($bolehTolak)
                <div class="xcard mb-3">
                    <div class="xhead"><span><i class="bi bi-ui-checks me-1"></i>Aksi Persetujuan</span></div>
                    <div class="p-3">
                        <p class="small text-muted mb-2">Keputusan berlaku untuk <strong>seluruh ruangan Menunggu</strong> pada pemesanan ini. Checklist tiap ruangan ada di kartu ruangan.</p>
                        <form method="POST" action="{{ route('admin.reservasi.setujui', $r->kode_reservasi) }}" class="d-grid mb-2"
                              data-confirm="Seluruh ruangan Menunggu pada {{ $r->kode_transaksi }} akan disetujui."
                              data-confirm-title="Setujui pemesanan ini?" data-icon="question"
                              data-confirm-text="Ya, setujui" data-confirm-color="#25b47e">@csrf
                            <button class="btn btn-success" @disabled(! $bolehSetujui)><i class="bi bi-check2-circle me-1"></i>Setujui Semua</button>
                        </form>
                        @unless ($bolehSetujui)
                            <p class="small text-danger mb-2"><i class="bi bi-info-circle me-1"></i>Tombol aktif setelah checklist semua ruangan hijau.</p>
                        @endunless

                        <button class="btn btn-outline-danger w-100" type="button" data-bs-toggle="collapse" data-bs-target="#formTolak"><i class="bi bi-x-circle me-1"></i>Tolak Pemesanan</button>
                        <div class="collapse mt-2" id="formTolak">
                            <form method="POST" action="{{ route('admin.reservasi.tolak', $r->kode_reservasi) }}"
                                  data-confirm="Seluruh ruangan Menunggu pada pemesanan ini akan ditolak."
                                  data-confirm-title="Kirim penolakan?" data-icon="warning"
                                  data-confirm-text="Ya, tolak" data-confirm-color="#d95757">@csrf
                                <label class="form-label">Alasan penolakan <span class="text-danger">*</span></label>
                                <textarea name="alasan" class="form-control mb-2" rows="3" placeholder="Tulis alasan yang jelas untuk pemesan…" required>{{ old('alasan') }}</textarea>
                                <button class="btn btn-danger btn-sm w-100">Kirim Penolakan</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <div class="xcard">
                <div class="xhead"><span><i class="bi bi-clock-history me-1"></i>Riwayat Status</span></div>
                <div class="p-3">
                    <div class="timeline">
                        @forelse ($riwayat as $riw)
                            <div class="titem {{ $loop->first ? 'first' : '' }}">
                                <div class="small fw-bold">{{ $riw->status_sebelumnya->value }} <i class="bi bi-arrow-right mx-1 text-muted"></i> {{ $riw->status_baru->value }}</div>
                                <div class="cell-sub">{{ $riw->tanggal_perubahan?->format('d/m/Y H:i') }} · {{ $riw->id_admin ? ($riw->admin?->nama_admin ?? 'Admin') : 'Pemesan (mandiri)' }}</div>
                                @if ($riw->keterangan)<div class="small fst-italic mt-1 p-2 rounded-2" style="background:#f6f9fc">"{{ $riw->keterangan }}"</div>@endif
                            </div>
                        @empty
                            <p class="text-muted small mb-0">Belum ada perubahan status.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
