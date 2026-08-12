<?php

namespace App\Http\Controllers;

use App\Models\Reservasi;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CekStatusController extends Controller
{
    /** Form input kode. */
    public function form(): View
    {
        return view('cek-status.form');
    }

    /** Cari reservasi by kode_transaksi (banyak baris) ATAU kode_reservasi tunggal. */
    public function hasil(Request $request): View
    {
        $data = $request->validate(
            ['kode' => ['required', 'string', 'max:100']],
            [
                'kode.required' => 'Kode reservasi wajib diisi, contoh RSV-7K3M.',
                'kode.max'      => 'Kode terlalu panjang, periksa kembali penulisannya.',
            ],
        );

        $kode = trim($data['kode']);

        $reservasi = Reservasi::query()
            ->with([
                'tarifSewa.fasilitas.lantai',
                'tarifSewa.jenisSewa',
                'pemesan',
                'dokumenPersyaratan',
                'riwayatStatus' => fn ($q) => $q->orderBy('tanggal_perubahan'),
            ])
            ->where('kode_transaksi', $kode)
            ->orWhere('kode_reservasi', $kode)
            ->orderBy('id_reservasi')
            ->get();

        return view('cek-status.hasil', [
            'kode'      => $kode,
            'reservasi' => $reservasi,
        ]);
    }
}
