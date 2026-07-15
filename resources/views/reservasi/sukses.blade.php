@extends('layouts.reservasi')
@section('title', 'Reservasi Berhasil')

@section('content')
    <div class="xcard mx-auto text-center p-5" style="max-width:640px;">
        <div class="mx-auto mb-3" style="width:5rem;height:5rem;border-radius:50%;display:grid;place-items:center;background:#e2f7ef;color:#0d8a5f;font-size:2.4rem;">
            <i class="bi bi-check-lg"></i>
        </div>
        <h1 class="h4 mb-2">Reservasi Berhasil Diajukan 🎉</h1>
        <p class="text-muted">Status awal <span class="badge text-bg-warning">Menunggu</span> persetujuan admin. Simpan kode berikut untuk mengecek status kapan saja.</p>

        <div class="p-4 rounded-3 my-3" style="background:var(--surface); border:1px dashed #c8d6e0;">
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
