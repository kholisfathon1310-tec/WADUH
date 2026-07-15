@extends('layouts.reservasi')
@section('title', 'Cek Status Reservasi')

@section('content')
    <div class="xcard mx-auto p-4 p-md-5" style="max-width:560px;">
        <div class="icon-tile mb-3"><i class="bi bi-search"></i></div>
        <h1 class="h4 mb-2">Cek Status Reservasi</h1>
        <p class="text-muted">Masukkan <strong>kode reservasi</strong> yang Anda terima setelah checkout (satu kode berlaku untuk semua ruangan dalam satu pemesanan).</p>
        <form method="POST" action="{{ route('cek-status.hasil') }}">
            @csrf
            <div class="input-group input-group-lg">
                <input name="kode" class="form-control" placeholder="contoh: RSV-7K3M" value="{{ old('kode') }}" required>
                <button class="btn btn-brand px-4">Cek</button>
            </div>
        </form>
        <p class="text-muted small mt-3 mb-0"><i class="bi bi-info-circle me-1"></i>Reservasi dapat dibatalkan selama masih dalam proses verifikasi.</p>
    </div>
@endsection
