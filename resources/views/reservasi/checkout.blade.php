@extends('layouts.reservasi')
@section('title', 'Checkout')

@section('stepper')
    @include('reservasi.partials.stepper', ['step' => 5])
@endsection

@section('content')
    <style>
        .list-group-item { transition:background .15s ease; }
        .list-group-item:hover { background:#fbfdfe; }
        .checkout-cart { position:sticky; top:5.5rem; }
        @media (max-width: 991.98px) {
            /* Di layar sempit kolom keranjang & form ditumpuk — kalau tetap sticky,
               kartu keranjang akan "menempel" di atas dan menyita ruang saat mengisi form. */
            .checkout-cart { position:static; }
        }
    </style>

    <div class="page-head mb-4" data-reveal>
        <p class="eyebrow-sm mb-1">Langkah 5 dari 5</p>
        <h1 class="h3 mb-1">Checkout Reservasi</h1>
        <p class="text-muted mb-0">Periksa keranjang Anda, isi data diri sekali untuk semua fasilitas, lalu kirim.</p>
    </div>

    <div class="row g-4">
        {{-- Ringkasan keranjang — sticky supaya tetap terlihat saat form di kanan digulir --}}
        <div class="col-lg-5" data-reveal>
            <div class="xcard overflow-hidden checkout-cart">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center" style="background:var(--surface)">
                    <span class="fw-bold"><i class="bi bi-cart3 me-1"></i>Keranjang ({{ count($items) }})</span>
                    <a href="{{ route('reservasi.index') }}" class="btn btn-brand-outline btn-sm"><i class="bi bi-plus-lg"></i> Tambah fasilitas</a>
                </div>
                <ul class="list-group list-group-flush" style="max-height:26rem; overflow-y:auto;">
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
        <div class="col-lg-7" data-reveal>
            <div class="xcard p-4">
                <h2 class="h5 mb-3"><i class="bi bi-person-vcard me-1"></i>Data Diri Pemesan</h2>
                <form method="POST" action="{{ route('reservasi.checkout') }}" enctype="multipart/form-data"
                      data-confirm="Reservasi akan dikirim untuk diverifikasi admin. Pastikan data & jadwal sudah benar."
                      data-confirm-title="Kirim reservasi ini?" data-icon="question" data-confirm-text="Ya, kirim">
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
                        @php $ruangBulan = collect($items)->where('satuan', 'Bulan')->pluck('nama_fasilitas'); @endphp
                        <div class="p-3 rounded-4 mb-3" style="background:#fff8e6; border:1px solid #f1e3b8;">
                            <p class="fw-bold small mb-1"><i class="bi bi-paperclip me-1"></i>Dokumen Persyaratan (Wajib untuk Sewa Bulanan)</p>
                            <p class="text-muted small mb-2">Company Profile, legalitas perusahaan, atau fotokopi KTP penanggung jawab (PDF, JPG, PNG, maksimal 5 MB per file). Dokumen ini berlaku untuk {{ $ruangBulan->count() > 1 ? 'semua ruangan bulanan (' . $ruangBulan->implode(', ') . ')' : $ruangBulan->first() }} pada pemesanan ini, cukup diunggah sekali.</p>
                            <div class="mb-2" data-dok-group>
                                <div class="d-flex gap-1 align-items-center mb-1">
                                    <input type="file" name="dokumen[]" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png" multiple required>
                                </div>
                                <button type="button" class="btn btn-sm btn-brand-outline" onclick="tambahFile()"><i class="bi bi-plus-lg me-1"></i>Tambah file lain</button>
                            </div>
                            <script>
                                // Tambah baris input file baru, lengkap dengan tombol hapus (X) supaya
                                // pemesan bisa membatalkan baris ini kalau ternyata tidak jadi dipakai.
                                function tambahFile() {
                                    const group = document.querySelector('[data-dok-group]');
                                    const baris = document.createElement('div');
                                    baris.className = 'd-flex gap-1 align-items-center mb-1';

                                    const input = document.createElement('input');
                                    input.type = 'file'; input.name = 'dokumen[]';
                                    input.className = 'form-control form-control-sm';
                                    input.accept = '.pdf,.jpg,.jpeg,.png'; input.multiple = true;

                                    const hapus = document.createElement('button');
                                    hapus.type = 'button';
                                    hapus.className = 'btn btn-sm btn-outline-danger flex-shrink-0';
                                    hapus.title = 'Batalkan file ini';
                                    hapus.innerHTML = '<i class="bi bi-x-lg"></i>';
                                    hapus.onclick = () => baris.remove();

                                    baris.append(input, hapus);
                                    group.insertBefore(baris, group.lastElementChild);
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
