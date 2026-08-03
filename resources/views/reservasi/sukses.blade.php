@extends('layouts.reservasi')
@section('title', 'Reservasi Berhasil')

@section('content')
    <style>
        @keyframes sukses-pop { 0% { transform:scale(.7); opacity:0; } 70% { transform:scale(1.08); } 100% { transform:scale(1); opacity:1; } }
        @keyframes sukses-ring { 0% { box-shadow:0 0 0 0 rgba(13,138,95,.35); } 100% { box-shadow:0 0 0 18px rgba(13,138,95,0); } }
        .sukses-ic { animation:sukses-pop .5s cubic-bezier(.2,.9,.3,1.3) both, sukses-ring 1.8s ease-out 1; }
        .sukses-kode { position:relative; overflow:hidden; }
        .sukses-kode::before { content:''; position:absolute; inset:0; background:radial-gradient(20rem 10rem at 50% -30%, rgba(23,107,135,.08), transparent 60%); pointer-events:none; }
    </style>
    <div class="xcard mx-auto text-center p-5" style="max-width:640px;" data-reveal>
        <div class="sukses-ic mx-auto mb-3" style="width:5.2rem;height:5.2rem;border-radius:50%;display:grid;place-items:center;background:linear-gradient(135deg,#e2f7ef,#d4f2e5);color:#0d8a5f;font-size:2.5rem;">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="h4 mb-2">Reservasi Berhasil Diajukan 🎉</h1>
        <p class="text-muted">Status awal <span class="badge text-bg-warning">Menunggu</span> persetujuan admin. Simpan kode berikut untuk mengecek status kapan saja.</p>

        <div class="sukses-kode p-4 rounded-4 my-3" style="background:var(--surface); border:1.5px dashed #b8cad5;">
            <span class="text-muted small d-block mb-1">Kode Reservasi Anda</span>
            <div class="display-6 fw-bold mb-1" style="color:var(--primary); letter-spacing:.08em;">{{ $checkout['kode_transaksi'] }}</div>
            @if (count($checkout['kode_reservasi']) > 1)
                <span class="text-muted small">Satu kode untuk {{ count($checkout['kode_reservasi']) }} ruangan yang Anda pesan.</span>
            @endif
        </div>

        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ route('cek-status.form') }}" class="btn btn-brand px-4"><i class="bi bi-search me-1"></i>Cek Status</a>
            <a href="{{ route('reservasi.index') }}" class="btn btn-brand-outline px-4">Reservasi Lagi</a>
        </div>
    </div>
@endsection
