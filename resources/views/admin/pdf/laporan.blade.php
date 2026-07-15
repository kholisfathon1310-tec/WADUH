<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        @page { margin: 28px 26px 40px; }
        body { font-size: 10px; color: #000; background: #fff; }

        h1 { font-size: 15px; font-weight: bold; margin: 0 0 16px; }

        table { border-collapse: collapse; width: 100%; background: #fff; }
        th, td { border: 0.75px solid #333333; padding: 4px 6px; background: #fff; }
        thead th { background: #e5e5e5; color: #000; font-weight: bold; text-align: center; }

        .c { text-align: center; }
        .r { text-align: right; }
        .l { text-align: left; }
        .est { display: block; font-size: 7.5px; color: #888; }
        .total td { font-weight: bold; }

        .footer { position: fixed; bottom: -24px; left: 0; font-size: 8px; color: #888; }
    </style>
</head>
<body>
    <div class="footer">Dicetak oleh {{ $adminNama ?? '-' }} pada {{ $cetakWaktu }}</div>

    <h1>Data Fasilitas Gedung BITC per/{{ $judulTanggal }}</h1>

    <table>
        <thead>
            <tr>
                <th style="width:32px;">Nomor</th>
                <th class="l">Uraian</th>
                <th style="width:80px;">Volume</th>
                <th style="width:38px;">Satuan</th>
                <th style="width:110px;">Harga Per/Bulan (Rp)</th>
                <th style="width:70px;">Keterangan</th>
                <th style="width:34px;">ISI</th>
                <th style="width:60px;">Available</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($rows as $row)
            <tr>
                <td class="c">{{ $row['no'] }}</td>
                <td class="l">{{ $row['uraian'] }}</td>
                <td class="r">{{ $row['volume'] !== null ? number_format($row['volume'], 2, ',', '.').' m2' : '-' }}</td>
                <td class="c">1</td>
                <td class="r">
                    @if ($row['harga'] !== null)
                        Rp {{ number_format($row['harga'], 2, ',', '.') }}
                        @if ($row['estimasi'])<span class="est">({{ $row['estimasi'] }})</span>@endif
                    @else
                        -
                    @endif
                </td>
                <td class="c">{{ $row['keterangan'] }}</td>
                <td class="c">{{ $row['isi'] ? 1 : '' }}</td>
                <td class="c">{{ $row['available'] ? 1 : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="8" class="c">Tidak ada data untuk filter ini.</td></tr>
        @endforelse

        @if ($rows->isNotEmpty())
            <tr class="total">
                <td colspan="6" class="r">TOTAL</td>
                <td class="c">{{ $totalIsi }}</td>
                <td class="c">{{ $totalAvailable }}</td>
            </tr>
        @endif
        </tbody>
    </table>
</body>
</html>
