<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FakturController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MonitoringController;
use App\Http\Controllers\Admin\ReservasiAdminController;
use App\Http\Controllers\CekStatusController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReservasiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', HomeController::class)->name('home');

/*
| Alur Pemesan (publik, tanpa auth) — Stage 2.
| Catatan urutan: route dengan segmen literal (fasilitas, keranjang, checkout) didaftarkan
| sebelum route dinamis {kategori} agar tidak "tertelan" oleh parameter kategori.
*/
Route::prefix('reservasi')->name('reservasi.')->group(function () {
    Route::get('/', [ReservasiController::class, 'index'])->name('index');

    Route::get('/checkout', [ReservasiController::class, 'checkoutForm'])->name('checkout.form');
    Route::post('/checkout', [ReservasiController::class, 'checkout'])->name('checkout');
    Route::get('/sukses', [ReservasiController::class, 'sukses'])->name('sukses');

    Route::get('/fasilitas/{fasilitas}', [ReservasiController::class, 'fasilitas'])->name('fasilitas.show');

    Route::post('/keranjang', [ReservasiController::class, 'tambahKeranjang'])->name('keranjang.tambah');
    Route::delete('/keranjang/{index}', [ReservasiController::class, 'hapusKeranjang'])->name('keranjang.hapus');

    Route::post('/{kode_reservasi}/batalkan', [ReservasiController::class, 'batalkan'])->name('batalkan');

    Route::get('/{kategori}/jenis-sewa', [ReservasiController::class, 'jenisSewa'])->name('jenis-sewa');
    Route::get('/{kategori}/lantai', [ReservasiController::class, 'lantai'])->name('lantai');
    Route::get('/{kategori}/denah/{lantai}', [ReservasiController::class, 'denah'])->name('denah');
});

Route::get('/cek-status', [CekStatusController::class, 'form'])->name('cek-status.form');
Route::post('/cek-status', [CekStatusController::class, 'hasil'])->name('cek-status.hasil');

/*
| Alur Admin — Stage 3. Guard `admin` (tabel admin terpisah dari users).
*/
Route::prefix('admin')->name('admin.')->group(function () {
    // Login (hanya untuk yang belum login sebagai admin).
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
    });

    // Area terproteksi.
    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
        Route::get('/monitoring/fasilitas/{fasilitas}', [MonitoringController::class, 'detail'])->name('monitoring.detail');

        // Data Reservasi + verifikasi/persetujuan.
        Route::get('/reservasi', [ReservasiAdminController::class, 'index'])->name('reservasi.index');
        Route::get('/reservasi/{kodeReservasi}', [ReservasiAdminController::class, 'show'])->name('reservasi.show');
        Route::post('/reservasi/{kodeReservasi}/setujui', [ReservasiAdminController::class, 'setujui'])->name('reservasi.setujui');
        Route::post('/reservasi/{kodeReservasi}/tolak', [ReservasiAdminController::class, 'tolak'])->name('reservasi.tolak');
        Route::post('/dokumen/{dokumen}/verifikasi', [ReservasiAdminController::class, 'verifikasiDokumen'])->name('reservasi.dokumen.verifikasi');

        // Faktur — SATU faktur per kode reservasi/transaksi (multi-ruangan digabung 1 PDF).
        Route::post('/reservasi/{kodeReservasi}/faktur', [FakturController::class, 'cetak'])->name('reservasi.faktur.cetak');
        Route::get('/reservasi/{kodeReservasi}/faktur', [FakturController::class, 'unduh'])->name('reservasi.faktur.unduh');

        // Laporan.
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan');
        Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    });
});
