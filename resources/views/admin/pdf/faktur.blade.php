{{--
  Template Faktur — layout IDENTIK dengan invoice resmi Cimahi Techno Park, Bahasa Indonesia.
  Dipakai untuk render PDF (dompdf/barryvdh). Variabel yang diharapkan:
    $inst        = config('institusi')
    $faktur      = ['no' => ..., 'tanggal' => '11 Juni 2026', 'waktu' => '11.00 - 16.00 WIB' | null]
    $pemesan     = ['nama' => ..., 'alamat' => "baris1\nbaris2\nbaris3"]
    $items       = [ ['no'=>1,'uraian'=>...,'tanggal'=>...,'waktu'=>...|null,'durasi'=>'5 Jam','tarif'=>40000,'jumlah'=>200000], ... ]
    $total       = 200000
    $terbilang   = 'Dua Ratus Ribu Rupiah'
    $adaKolomWaktu = bool  (true jika ada minimal satu item jenis Jam)
  Helper format rupiah: rp($n) => "40.000,-"
--}}
@php
  if (!function_exists('rp')) {
    function rp($n){ return number_format($n, 0, ',', '.') . ',-'; }
  }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<style>
  * { box-sizing: border-box; }
  body { font-family: DejaVu Sans, Arial, sans-serif; color:#000; margin:0; padding:24px 28px; font-size:12px; }
  table { border-collapse: collapse; }

  /* ===== HEADER — logo di kiri, identitas instansi benar-benar center di lebar halaman ===== */
  .hdr-table { width:100%; border:none; }
  .hdr-table td { vertical-align: top; border:none; padding:0; }
  .logo-cell { width:180px; text-align:left; }
  /* File logo punya banyak ruang kosong di sekelilingnya — dipotong (crop-zoom) di sini
     supaya lambangnya sendiri yang tampil besar, bukan ruang kosongnya. */
  .logo-frame { width:165px; height:165px; overflow:hidden; position:relative; }
  .logo-frame img { position:absolute; top:-27px; left:-80px; width:325px; height:auto; }
  /* dompdf tidak mendukung flex — pakai line-height agar teks LOGO benar-benar di tengah kotak */
  .logo-box { width:165px; height:165px; border:1px dashed #999; text-align:center; line-height:165px; font-size:11px; color:#999; }
  .spacer-cell { width:90px; }
  .inst { text-align:center; padding-top:8px; }
  .inst .l1 { font-weight:bold; font-size:14px; line-height:1.35; }
  .inst .l2 { font-weight:bold; font-size:14px; line-height:1.35; }
  .inst .l3 { font-weight:bold; font-size:14px; line-height:1.35; margin-bottom:5px; }
  .inst .addr { font-size:10.5px; line-height:1.55; }
  /* Alamat wajib SATU baris — jangan patah ke bawah */
  .inst .addr .alamat-1baris { white-space:nowrap; font-size:10px; }
  .rule { border:none; border-top:3px solid #000; margin:10px 0 4px; }
  .judul { text-align:center; font-size:26px; font-weight:bold; letter-spacing:1px; margin:6px 0 14px; }

  .mid-table { width:100%; border:none; margin-bottom:12px; }
  .mid-table > tbody > tr > td { border:none; vertical-align:top; padding:0; }
  .to-cell { width:55%; }
  .to-row td { border:none; padding:2px 0; font-size:13px; }
  .to-label { width:70px; }
  .to-colon { width:14px; }

  .meta { border-collapse:collapse; width:100%; }
  .meta td { border:1px solid #000; padding:7px 9px; font-size:13px; }
  .meta .k { font-weight:bold; width:38%; }

  .items { width:100%; border-collapse:collapse; margin-top:4px; }
  .items th, .items td { border:1px solid #444; padding:8px 6px; font-size:12px; text-align:center; vertical-align:middle; }
  .items th { font-weight:bold; background:#d9d9d9; }
  .items td.uraian { text-align:left; }
  .items .total-label { text-align:center; font-weight:bold; }
  .items .total-val { text-align:right; font-weight:bold; }

  /* Terbilang — di LUAR tabel item, tanpa kotak, tepat di bawah TOTAL (sesuai posisi pada invoice acuan) */
  .terbilang-luar { margin-top:8px; font-size:12px; }

  .foot-table { width:100%; border:none; margin-top:20px; }
  .foot-table > tbody > tr > td { border:none; vertical-align:top; padding:0; }
  /* Informasi Pembayaran — judul, sub-judul, dan tabel rekening dibingkai satu kotak, sama seperti invoice acuan */
  .pay-frame { border:1px solid #000; padding:8px; width:96%; }
  .pay-title { font-weight:normal; font-size:13px; margin-bottom:2px; }
  .pay-sub { font-weight:normal; font-size:11px; margin-bottom:6px; }
  .pay-box { border-collapse:collapse; width:100%; }
  .pay-box td { border:1px solid #000; padding:6px 8px; font-size:12px; font-weight:normal; }
  .pay-box .k { width:130px; }
  .pay-box .colon { width:10px; text-align:center; }
  .pay-box .v { font-weight:normal; }
  .other { font-weight:normal; font-size:12px; margin-top:10px; }
  .other-sub { font-weight:normal; font-size:11px; }

  /* Blok tanda tangan — rapi di tengah kolom kanan, ruang ttd cukup */
  .sign-cell { width:42%; text-align:center; padding-top:2px; }
  .sign-jabatan { font-size:13px; line-height:1.4; }
  .sign-nama { font-weight:bold; text-decoration:underline; margin-top:72px; font-size:13px; }
  .sign-nip { font-size:12px; margin-top:2px; }
</style>
</head>
<body>

  {{-- ===== HEADER ===== --}}
  <table class="hdr-table">
    <tr>
      <td class="logo-cell">
        @if(!empty($inst['logo_path']) && file_exists(public_path($inst['logo_path'])))
          <div class="logo-frame"><img src="{{ public_path($inst['logo_path']) }}"></div>
        @else
          <div class="logo-box">LOGO</div>
        @endif
      </td>
      <td class="inst">
        <div class="l1">{{ $inst['nama'] }}</div>
        <div class="l2">{{ $inst['departemen'] }}</div>
        <div class="l3">{{ $inst['unit'] }}</div>
        <div class="addr">
          <span class="alamat-1baris">{{ $inst['alamat'] }}</span><br>
          Telepon {{ $inst['telepon'] }}, Faks {{ $inst['fax'] }},<br>
          Situs {{ $inst['website'] }}<br>
          Surel {{ $inst['email'] }}
        </div>
      </td>
      <td class="spacer-cell"></td>
    </tr>
  </table>

  <hr class="rule">
  <div class="judul">FAKTUR</div>

  {{-- ===== KEPADA + META ===== --}}
  <table class="mid-table">
    <tr>
      <td class="to-cell">
        <table>
          <tr class="to-row"><td class="to-label">Kepada</td><td class="to-colon">:</td><td><strong>{{ $pemesan['nama'] }}</strong></td></tr>
        </table>
      </td>
      <td>
        <table class="meta">
          <tr><td class="k">No. Faktur</td><td>: {{ $faktur['no'] }}</td></tr>
          <tr><td class="k">Tanggal</td><td>: {{ $faktur['tanggal'] }}</td></tr>
          @if(!empty($faktur['waktu']))
            <tr><td class="k">Waktu</td><td>: {{ $faktur['waktu'] }}</td></tr>
          @endif
        </table>
      </td>
    </tr>
  </table>

  {{-- ===== TABEL ITEM ===== --}}
  @php $colspanTotal = $adaKolomWaktu ? 5 : 4; @endphp
  <table class="items">
    <thead>
      <tr>
        <th style="width:6%;">No.</th>
        <th style="width:{{ $adaKolomWaktu ? '26' : '34' }}%;">Uraian</th>
        <th style="width:14%;">Tanggal</th>
        @if($adaKolomWaktu)<th style="width:16%;">Waktu</th>@endif
        <th style="width:12%;">Durasi</th>
        <th style="width:13%;">Tarif Satuan (Rp)</th>
        <th style="width:15%;">Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      @foreach($items as $it)
        <tr>
          <td>{{ $it['no'] }}.</td>
          <td class="uraian">{{ $it['uraian'] }}</td>
          <td>{{ $it['tanggal'] }}</td>
          @if($adaKolomWaktu)<td>{{ $it['waktu'] ?? '-' }}</td>@endif
          <td>{{ $it['durasi'] }}</td>
          <td>{{ rp($it['tarif']) }}</td>
          <td>{{ rp($it['jumlah']) }}</td>
        </tr>
      @endforeach
      <tr>
        <td class="total-label" colspan="{{ $colspanTotal }}">TOTAL</td>
        <td class="total-val" colspan="2">Rp {{ rp($total) }}</td>
      </tr>
    </tbody>
  </table>
  <div class="terbilang-luar"><strong>Terbilang</strong> : {{ $terbilang }}</div>

  {{-- ===== FOOTER: PEMBAYARAN + TTD ===== --}}
  <table class="foot-table">
    <tr>
      <td style="width:58%;">
        <div class="pay-frame">
          <div class="pay-title"><b>Informasi Pembayaran</b></div>
          <div class="pay-sub">Pembayaran dapat dilakukan melalui rekening berikut:</div>
          <table class="pay-box">
            <tr><td class="k">Nama Bank</td><td class="colon">:</td><td class="v">{{ $inst['bank_nama'] }}</td></tr>
            <tr><td class="k">Nomor Rekening</td><td class="colon">:</td><td class="v">{{ $inst['bank_no_rekening'] }}</td></tr>
            <tr><td class="k">Atas Nama</td><td class="colon">:</td><td class="v">{{ $inst['bank_atas_nama'] }}</td></tr>
          </table>
        </div>
        <div class="other"><b>Metode Pembayaran Lain:</b></div>
        <div class="other-sub">Pembayaran tunai dapat dilakukan langsung ke kasir di BITC.</div>
      </td>
      <td class="sign-cell">
        <div class="sign-jabatan">{{ $inst['penandatangan_jabatan'] }}</div>
        <div class="sign-nama">{{ $inst['penandatangan_nama'] }}</div>
        <div class="sign-nip">NIP. {{ $inst['penandatangan_nip'] }}</div>
      </td>
    </tr>
  </table>

</body>
</html>