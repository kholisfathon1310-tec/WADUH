{{--
  Template PDF "Bukti Reservasi" — dokumen ringkas untuk pemesan (BUKAN faktur/invoice
  pembayaran; untuk itu lihat resources/views/admin/pdf/faktur.blade.php). Tujuannya
  sebagai bukti tunjuk saat survei lapangan di lokasi BITC, sehingga kode reservasi
  ditampilkan besar & jelas.
  Dirender lewat dompdf (barryvdh/laravel-dompdf) — font DejaVu Sans dipakai (bukan
  DM Sans/Plus Jakarta Sans dari web) karena dompdf tidak bisa memuat webfont .woff2
  tanpa konversi lokal; ini konsisten dengan admin/pdf/faktur.blade.php yang sudah ada.
  Variabel yang diharapkan:
    $kodeTransaksi = "TRX-XXXXXX"
    $pemesan       = "Nama Lengkap"
    $items         = [ ['no'=>1,'kode'=>'RSV-xxxx','unit'=>'R1','nama'=>...,'kategori'=>...,
                         'lantai'=>'1','tanggal'=>...,'waktu'=>...|null,'durasi'=>'2 Jam',
                         'total'=>200000], ... ]
    $total         = 200000
    $diterbitkan   = "4 Agustus 2026, 10.00 WIB"
--}}
@php
    if (! function_exists('rp')) {
        function rp($n) { return 'Rp '.number_format($n, 0, ',', '.'); }
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
    * { box-sizing: border-box; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color:#15243b; margin:0; padding:26px 30px; font-size:12px; }
    table { border-collapse: collapse; width:100%; }

    /* Header */
    .hdr-table td { vertical-align: middle; border:none; padding:0; }
    .brand-mark { width:44px; height:44px; text-align:center; line-height:44px; font-size:20px; font-weight:bold;
                  color:#fff; background:#176b87; border-radius:10px; }
    .brand-name { font-size:17px; font-weight:bold; color:#15243b; padding-left:12px; }
    .brand-name .t { color:#24aa9a; }
    .brand-sub { font-size:10.5px; color:#637189; padding-left:12px; margin-top:2px; }
    .hdr-right { text-align:right; font-size:10.5px; color:#637189; }
    .rule { border:none; border-top:2px solid #176b87; margin:14px 0 0; }

    /* Judul + kode besar */
    .judul-box { text-align:center; margin:18px 0 20px; }
    .judul { font-size:13px; letter-spacing:3px; text-transform:uppercase; color:#637189; font-weight:bold; margin-bottom:8px; }
    .kode-besar { font-size:34px; font-weight:bold; letter-spacing:2px; color:#176b87;
                  border:2px solid #176b87; border-radius:12px; padding:12px 20px; display:inline-block; background:#f0f8f7; }

    /* Info pemesan */
    .info-table td { border:none; padding:3px 0; font-size:12px; }
    .info-table .k { width:110px; color:#637189; }
    .info-table .v { font-weight:bold; }

    /* Tabel item */
    .items { margin-top:14px; }
    .items th, .items td { border:1px solid #cfd9e0; padding:7px 6px; font-size:10.5px; text-align:center; vertical-align:middle; }
    .items th { background:#eef6f9; color:#0f526b; font-weight:bold; text-transform:uppercase; letter-spacing:.4px; font-size:9.5px; }
    .items td.left { text-align:left; }
    .items td.kode, .items td.unit { white-space:nowrap; }
    .items .total-label { text-align:right; font-weight:bold; background:#f6f9fc; }
    .items .total-val { text-align:right; font-weight:bold; color:#176b87; background:#f6f9fc; }

    /* Footer */
    .footer-note { margin-top:22px; padding:10px 12px; background:#f6f9fc; border:1px solid #e4ebf2; border-radius:8px; font-size:10px; color:#637189; }
    .terbit { margin-top:14px; font-size:10.5px; color:#637189; }
    .terbit b { color:#15243b; }
</style>
</head>
<body>

    {{-- ===== HEADER ===== --}}
    <table class="hdr-table">
        <tr>
            <td style="width:44px;"><div class="brand-mark">W</div></td>
            <td>
                <div class="brand-name">WADUH<span class="t">.</span></div>
                <div class="brand-sub">BITC Cimahi Techno Park (Baros Information Technology Creative Centre)</div>
            </td>
            <td class="hdr-right">
                Wadah Akses Digital Unit Hunian<br>
                Jl. Raya Baros No. 78, Cimahi Selatan
            </td>
        </tr>
    </table>
    <hr class="rule">

    {{-- ===== JUDUL + KODE BESAR ===== --}}
    <div class="judul-box">
        <div class="judul">Bukti Reservasi</div>
        <div class="kode-besar">{{ $kodeTransaksi }}</div>
    </div>

    {{-- ===== INFO PEMESAN ===== --}}
    <table class="info-table">
        <tr><td class="k">Nama Pemesan</td><td class="v">: {{ $pemesan }}</td></tr>
        <tr><td class="k">Jumlah Ruangan</td><td class="v">: {{ count($items) }} ruangan</td></tr>
    </table>

    {{-- ===== TABEL RUANGAN ===== --}}
    <table class="items">
        <thead>
            <tr>
                <th style="width:3%;">No.</th>
                <th style="width:14%;">Kode Reservasi</th>
                <th style="width:8%;">Unit</th>
                <th style="width:21%;">Ruangan / Jenis</th>
                <th style="width:6%;">Lantai</th>
                <th style="width:14%;">Tanggal</th>
                <th style="width:12%;">Waktu</th>
                <th style="width:10%;">Durasi</th>
                <th style="width:12%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $it)
                <tr>
                    <td>{{ $it['no'] }}</td>
                    <td class="kode"><strong>{{ $it['kode'] }}</strong></td>
                    <td class="unit">{{ $it['unit'] }}</td>
                    <td class="left">{{ $it['nama'] }}<br><span style="color:#637189">{{ $it['kategori'] }}</span></td>
                    <td>{{ $it['lantai'] }}</td>
                    <td>{{ $it['tanggal'] }}</td>
                    <td>{{ $it['waktu'] ?? '-' }}</td>
                    <td>{{ $it['durasi'] }}</td>
                    <td>{{ rp($it['total']) }}</td>
                </tr>
            @endforeach
            <tr>
                <td class="total-label" colspan="8">TOTAL KESELURUHAN</td>
                <td class="total-val">{{ rp($total) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen ini adalah <strong>bukti reservasi</strong> yang diterbitkan otomatis oleh sistem WADUH sebagai
        konfirmasi bahwa pemesan di atas telah melakukan reservasi fasilitas BITC Cimahi Techno Park. Tunjukkan
        dokumen ini beserta kode reservasi saat verifikasi di lokasi. Dokumen ini bukan bukti pembayaran resmi.
    </div>

    <div class="terbit">Diterbitkan pada: <b>{{ $diterbitkan }}</b></div>

</body>
</html>
