@extends('layouts.reservasi')
@section('title', 'Checkout')

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 5])
@endsection

@section('content')
    <div class="page-head mb-4">
        <p class="eyebrow-sm mb-1">Langkah 5 dari 5</p>
        <h1 class="h3 mb-1">Checkout Reservasi</h1>
        <p class="text-muted mb-0">Periksa keranjang Anda, isi data diri sekali untuk semua fasilitas, lalu kirim.</p>
    </div>

    <div class="row g-4">
        {{-- Ringkasan keranjang --}}
        <div class="col-lg-5">
            <div class="xcard overflow-hidden">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background:var(--surface)">
                    <span class="fw-bold"><i class="bi bi-cart3 me-1"></i>Keranjang ({{ count($items) }})</span>
                    <a href="{{ route('reservasi.index') }}" class="btn btn-brand-outline btn-sm"><i class="bi bi-plus-lg"></i> Tambah fasilitas</a>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($items as $index => $item)
                        <li class="list-group-item p-3">
                            <div class="d-flex justify-content-between gap-2">
                                <div>
                                    <div class="fw-bold">{{ $item['nama_fasilitas'] }}
                                        <span class="badge text-bg-light border">Per {{ $item['satuan'] }}</span>
                                    </div>
                                    <div class="small text-muted mt-1">
                                        <i class="bi bi-calendar3 me-1"></i>{{ $item['tanggal_mulai'] }}@if($item['tanggal_selesai'] !== $item['tanggal_mulai']) → {{ $item['tanggal_selesai'] }}@endif
                                        @if($item['jam_mulai']) · <i class="bi bi-clock me-1"></i>{{ $item['jam_mulai'] }}–{{ $item['jam_selesai'] }}@endif
                                    </div>
                                    <div class="small text-muted">{{ $item['durasi'] }} {{ strtolower($item['satuan']) }} · {{ $item['jumlah_pengguna'] }} orang</div>
                                    <div class="fw-bold mt-1" style="color:var(--primary)">Rp {{ number_format($item['total_biaya'], 0, ',', '.') }}</div>
                                </div>
                                <form method="POST" action="{{ route('reservasi.keranjang.hapus', $index) }}"
                                      data-confirm="{{ $item['nama_fasilitas'] }} akan dikeluarkan dari keranjang."
                                      data-confirm-title="Hapus item ini?" data-icon="warning"
                                      data-confirm-text="Ya, hapus" data-confirm-color="#d95757">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger border-0" title="Hapus dari keranjang"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="p-3 d-flex justify-content-between fw-bold border-top" style="background:var(--surface)">
                    <span>Total</span><span style="color:var(--primary)">Rp {{ number_format($total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        {{-- Data diri + dokumen --}}
        <div class="col-lg-7">
            <div class="xcard p-4">
                <h2 class="h5 mb-3"><i class="bi bi-person-vcard me-1"></i>Data Diri Pemesan</h2>
                <form method="POST" action="{{ route('reservasi.checkout') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-7 mb-3"><label class="form-label">Nama lengkap</label><input name="nama_lengkap" class="form-control" placeholder="Nama sesuai identitas" value="{{ old('nama_lengkap') }}" required></div>
                        <div class="col-md-5 mb-3"><label class="form-label">Usia</label><input type="number" name="usia" class="form-control" min="17" max="120" value="{{ old('usia') }}" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-7 mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required></div>
                        <div class="col-md-5 mb-3"><label class="form-label">No. telepon</label><input name="no_telepon" class="form-control" placeholder="08xxxxxxxxxx" value="{{ old('no_telepon') }}" required></div>
                    </div>
                    <div class="mb-3"><label class="form-label">Pekerjaan</label><input name="pekerjaan" class="form-control" value="{{ old('pekerjaan') }}" required></div>
                    <div class="mb-3"><label class="form-label">Alamat</label><textarea name="alamat" class="form-control" rows="2" required>{{ old('alamat') }}</textarea></div>

                    @if ($hasBulan)
                        <div class="p-3 rounded-3 mb-3" style="background:#fff8e6; border:1px solid #f1e3b8;">
                            <p class="fw-bold small mb-1"><i class="bi bi-paperclip me-1"></i>Dokumen Persyaratan — wajib untuk sewa Bulanan</p>
                            <p class="text-muted small mb-2">Company Profile / legalitas perusahaan / fotokopi KTP penanggung jawab (PDF, JPG, PNG, maks 5 MB per file).</p>
                            @foreach ($items as $index => $item)
                                @if ($item['satuan'] === 'Bulan')
                                    <div class="mb-2" data-dok-group="{{ $index }}">
                                        <label class="form-label small mb-1">Dokumen untuk: <strong>{{ $item['nama_fasilitas'] }}</strong></label>
                                        <input type="file" name="dokumen[{{ $index }}][]" class="form-control form-control-sm mb-1" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                                        <button type="button" class="btn btn-sm btn-brand-outline" onclick="tambahFile({{ $index }})"><i class="bi bi-plus-lg me-1"></i>Tambah file lain</button>
                                    </div>
                                @endif
                            @endforeach
                            <script>
                                // Tambah input file baru agar pemesan mudah melampirkan beberapa dokumen.
                                function tambahFile(index) {
                                    const group = document.querySelector('[data-dok-group="' + index + '"]');
                                    const input = document.createElement('input');
                                    input.type = 'file'; input.name = 'dokumen[' + index + '][]';
                                    input.className = 'form-control form-control-sm mb-1';
                                    input.accept = '.pdf,.jpg,.jpeg,.png'; input.multiple = true;
                                    group.insertBefore(input, group.lastElementChild);
                                }
                            </script>
                        </div>
                    @endif

                    <button class="btn btn-success w-100 py-2"><i class="bi bi-check2-circle me-1"></i>Submit Reservasi</button>
                    <p class="text-muted small text-center mt-2 mb-0">Anda akan menerima kode reservasi untuk memantau status persetujuan.</p>
                </form>
            </div>
        </div>
    </div>
@endsection
