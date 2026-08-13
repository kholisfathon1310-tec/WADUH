<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        @page { margin: 22px 18px 36px; }
        body { font-size: 8px; color: #000; background: #fff; }

        h1 { font-size: 13px; font-weight: bold; margin: 0 0 10px; }

        table { border-collapse: collapse; width: 100%; background: #fff; table-layout: fixed; }
        th, td { border: 0.75px solid #333333; padding: 3px 4px; background: #fff; overflow-wrap: break-word; }
        thead th { background: #e5e5e5; color: #000; font-weight: bold; text-align: center; }
        thead { display: table-header-group; } /* judul kolom terulang tiap halaman baru */
        tr { page-break-inside: avoid; }

        .c { text-align: center; }
        .r { text-align: right; }
        .l { text-align: left; }
        .total td { font-weight: bold; }

        .footer { position: fixed; bottom: -20px; left: 0; font-size: 7.5px; color: #888; }
    </style>
</head>
<body>
    <div class="footer">Dicetak oleh {{ $adminNama ?? '-' }} pada {{ $cetakWaktu }}</div>

    <h1>Laporan Data Reservasi Bulan {{ $judulBulan }}</h1>

    <table>
        <colgroup>
            <col style="width:5%;">
            <col style="width:12%;">
            <col style="width:15%;">
            <col style="width:19%;">
            <col style="width:17%;">
            <col style="width:10%;">
            <col style="width:13%;">
            <col style="width:9%;">
        </colgroup>
        <thead>
            <tr>
                <th>Nomor</th>
                <th>Kode Reservasi</th>
                <th class="l">Nama Pemesan</th>
                <th class="l">Uraian</th>
                <th>Volume (m²)</th>
                <th>Periode Sewa</th>
                <th>Keterangan</th>
                <th>Total Harga (Rp)</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="c">{{ $row['no'] }}</td>
                <td class="c">{{ $row['kode_reservasi'] }}</td>
                <td class="l">{{ $row['nama'] ?: '-' }}</td>
                <td class="l">{{ $row['uraian'] }}</td>
                <td class="c">{{ number_format($row['volume'], 2, ',', '.') }}</td>
                <td class="c">{{ $row['periode'] }}</td>
                <td class="c">{{ $row['keterangan'] }}</td>
                <td class="r">Rp {{ number_format($row['total_harga'], 0, ',', '.') }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="c">Belum ada reservasi disetujui pada {{ $judulBulan }}.</td></tr>
        @endforelse

        @if ($rows->isNotEmpty())
            <tr class="total">
                <td colspan="6" class="r">TOTAL</td>
                <td class="r">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        @endif
        </tbody>
    </table>
</body>
</html>
