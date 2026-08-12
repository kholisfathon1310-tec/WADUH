@props([
    // Nomor lantai yang ingin ditampilkan (key dari config denah-layout.php).
    'lantai' => '1',

    // Map ['L1-R1' => 'hijau'|'kuning'|'merah', ...]
    // - hijau  = kosong (default)
    // - kuning = sebagian jam terisi
    // - merah  = penuh / tidak aktif
    // Kalau kode tidak ada di map → dianggap 'hijau'.
    'statusPerKode' => [],

    // Kalau true, ruangan yang bisa dipesan jadi <button> klik.
    'clickable' => false,

    // Template link saat clickable. Placeholder __KODE__ diganti kode fasilitas.
    // Contoh: '/reservasi/fasilitas/__KODE__?jenis=2'
    'linkTemplate' => null,

    // Judul & info override (opsional, kalau null pakai dari config).
    'title' => null,
    'info'  => null,
])

@php
    $data = config("denah-layout.{$lantai}");
    if (! $data) {
        // Fallback: lantai tidak dikenal
        $data = ['title' => "LANTAI {$lantai}", 'info' => 'Denah belum tersedia.', 'rows' => []];
    }

    $displayTitle    = $title ?? $data['title'];
    $displaySubtitle = $data['subtitle'] ?? null;
    $displayInfo     = $info  ?? ($data['info'] ?? '');

    // Helper: bangun class + attribut untuk 1 room
    $renderRoom = function (array $item) use ($statusPerKode, $clickable, $linkTemplate) {
        $tipe     = $item['tipe'] ?? 'ruangan';
        $isRuang  = $tipe === 'ruangan';
        $kode     = $item['kode']  ?? null;
        $label    = $item['label'] ?? ($kode ?? '');
        $luas     = $item['luas']  ?? null;
        $shape    = $item['shape'] ?? null;

        // Klasifikasi visual R (Working Space besar) vs K (Kubikal kecil)
        $labelUpper = strtoupper($label);
        $isR = $isRuang && str_starts_with($labelUpper, 'R');
        $isK = $isRuang && str_starts_with($labelUpper, 'K');

        // Status: kosong / terisi (map dari 'hijau'/'kuning'/'merah')
        $status = 'kosong';
        if ($isRuang && $kode) {
            $s = $statusPerKode[$kode] ?? 'hijau';
            $status = ($s === 'merah' || $s === 'kuning') ? 'terisi' : 'kosong';
        }

        $classes = ['d-room', $tipe];
        if ($shape) $classes[] = $shape;
        if ($isRuang) $classes[] = $status;
        if ($isR) $classes[] = 'is-rroom';
        if ($isK) $classes[] = 'is-kroom';

        $clickableThis = $isRuang && $clickable && $kode;
        if ($clickableThis) $classes[] = 'clickable';

        // Flex-grow: pakai `flex` kalau ada, atau `luas` (proporsional dengan luas),
        // fallback 1. Nilai flex-grow tidak boleh 0 supaya kartu tidak menghilang.
        $flexGrow = $item['flex'] ?? $luas ?? 1;

        // Href kalau clickable
        $href = null;
        if ($clickableThis && $linkTemplate) {
            $href = str_replace('__KODE__', urlencode($kode), $linkTemplate);
        }

        return compact('classes', 'label', 'luas', 'status', 'clickableThis', 'href', 'flexGrow', 'tipe');
    };
@endphp

{{-- CSS di-render SEKALI per halaman (walau komponen dipanggil berkali-kali).
     Tidak pakai @push supaya bisa jalan di layout apapun tanpa @stack. --}}
@once
<style>
        :root {
            --dl-green:    #1e8a3d;
            --dl-green-bg: #eafbf0;
            --dl-green-dk: #136127;
            --dl-red:      #d94f3d;
            --dl-red-bg:   #fdecea;
            --dl-blue-bg:  #eaf6fc;
            --dl-blue-bd:  #bfe3f5;
            --dl-gray-bg:  #eef0f2;
            --dl-gray-bd:  #dde1e5;
            --dl-corridor: #f7f8fa;
            --dl-ink:      #1c2430;
            --dl-sub:      #6b7480;
        }

        .denah-layout {
            font-family: 'Plus Jakarta Sans', 'DM Sans', 'Segoe UI', sans-serif;
            color: var(--dl-ink);
            background: linear-gradient(180deg, #fbfcfd 0%, #f4f6f8 100%);
            padding: 22px;
            border-radius: 20px;
        }
        .denah-layout * { box-sizing: border-box; }

        /* Header */
        .dl-header {
            display: flex; justify-content: space-between; align-items: center;
            flex-wrap: wrap; gap: 12px; margin-bottom: 20px;
        }
        .dl-title {
            font-weight: 800; font-size: 1.7rem; margin: 0; letter-spacing: -0.02em; color: var(--dl-ink);
        }
        .dl-title small {
            display: block; font-size: 0.8rem; font-weight: 600;
            color: var(--dl-green-dk); letter-spacing: 0.04em; margin-top: 2px;
        }
        .dl-info {
            background: #fff; border: 1px solid var(--dl-gray-bd); border-radius: 12px;
            padding: 10px 16px; font-size: 0.78rem; color: var(--dl-sub);
            box-shadow: 0 1px 3px rgba(20, 30, 40, 0.04);
        }
        .dl-info strong { color: var(--dl-ink); }

        /* Building container */
        .dl-building {
            position: relative; border: 1px solid var(--dl-gray-bd); border-radius: 16px;
            background: #fff; padding: 26px; overflow: hidden;
            box-shadow: 0 8px 24px rgba(20, 30, 45, 0.05);
        }

        /* Rows (flex mode) */
        .dl-row {
            display: flex; width: 100%; gap: 12px; margin-bottom: 12px;
        }
        .dl-row:last-child { margin-bottom: 0; }

        /* Grid mode (lantai 2, 3A, 3B) */
        .dl-grid { display: grid; gap: 12px; margin-bottom: 12px; }
        .dl-grid .d-room { height: 100%; }

        /* Room dasar */
        .d-room {
            position: relative; flex: 1 1 0; min-height: 96px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 4px; border: none; background: #fff; padding: 10px 8px;
            text-align: center; font-family: inherit; border-radius: 10px;
            text-decoration: none; color: inherit;
        }
        .d-room.clickable { cursor: pointer; }

        /* Ruangan bisa dipesan */
        .d-room.ruangan {
            border: 1.5px solid var(--dl-green); background: #fff;
            box-shadow: 0 1px 3px rgba(20, 120, 60, 0.06);
        }
        .d-room.ruangan.terisi {
            border-color: var(--dl-red); box-shadow: 0 1px 3px rgba(180, 60, 45, 0.06);
        }
        .d-room.ruangan.clickable:hover {
            background: var(--dl-green-bg); transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(30, 138, 61, 0.18); transition: 0.18s;
        }
        .d-room.ruangan.terisi.clickable:hover {
            background: var(--dl-red-bg); box-shadow: 0 8px 18px rgba(217, 79, 61, 0.18);
        }

        /* R (Working Space) — kartu besar & menonjol */
        .d-room.is-rroom { min-height: 150px; border-width: 2px; padding: 16px 10px; }
        .d-room.is-rroom .d-kode   { font-size: 1.15rem; }
        .d-room.is-rroom .d-luas   { font-size: 0.75rem; }
        .d-room.is-rroom .d-status { font-size: 0.65rem; padding: 3px 12px; }
        .dl-grid .d-room.is-rroom { min-height: 100%; }

        /* K (Kubikal) — kartu kecil */
        .d-room.is-kroom { min-height: 70px; padding: 6px 4px; border-width: 1.2px; }
        .d-room.is-kroom .d-kode   { font-size: 0.78rem; }
        .d-room.is-kroom .d-luas   { font-size: 0.6rem; }
        .d-room.is-kroom .d-status { font-size: 0.55rem; padding: 1px 7px; }

        /* Non-ruangan */
        .d-room.fasilitas { background: var(--dl-blue-bg); border: 1px solid var(--dl-blue-bd); }
        .d-room.servis    { background: var(--dl-gray-bg); border: 1px solid var(--dl-gray-bd); }
        .d-room.tangga {
            background: repeating-linear-gradient(0deg, #e6e9ec, #e6e9ec 4px, #eef0f2 4px, #eef0f2 8px);
            border: 1px solid var(--dl-gray-bd);
        }
        .d-room.koridor {
            background: var(--dl-corridor); flex: 1 1 100%; min-height: 56px;
            font-weight: 600; color: #9aa2ab; border: 1px dashed #d6dade;
            letter-spacing: 0.08em; font-size: 0.78rem;
        }
        .d-room.taman {
            background: #effaef; color: #2f7d32; font-size: 0.68rem;
            border: 1px solid #cdeecb;
        }
        .d-room.blank { background: transparent; border: none !important; }

        /* Ruangan angled (sudut kanan atas dipotong) */
        .d-room.angled { clip-path: polygon(0 0, 85% 0, 100% 25%, 100% 100%, 0 100%); }

        /* Isi kartu */
        .d-kode  { font-weight: 700; font-size: 0.9rem; color: var(--dl-ink); }
        .d-luas  { font-size: 0.66rem; color: var(--dl-sub); }
        .d-label { font-size: 0.7rem; color: #8a9099; font-weight: 600; letter-spacing: 0.03em; }
        .d-status {
            margin-top: 1px; font-size: 0.58rem; font-weight: 700;
            padding: 2px 9px; border-radius: 20px; letter-spacing: 0.04em;
        }
        .d-status.kosong { color: var(--dl-green-dk); border: 1px solid var(--dl-green); background: var(--dl-green-bg); }
        .d-status.terisi { color: #a0392b; border: 1px solid var(--dl-red);   background: var(--dl-red-bg); }

        /* Kompas */
        .dl-compass {
            position: absolute; bottom: 14px; right: 16px;
            font-weight: 700; color: #6b7480; background: #fff;
            border: 1px solid var(--dl-gray-bd); border-radius: 50%;
            width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;
            font-size: 0.62rem; box-shadow: 0 2px 6px rgba(20, 30, 40, 0.06);
        }

        /* Legenda */
        .dl-legend {
            display: flex; flex-wrap: wrap; gap: 20px; margin-top: 16px;
            padding: 14px 18px; border: 1px solid var(--dl-gray-bd);
            border-radius: 14px; font-size: 0.78rem; background: #fff;
        }
        .dl-legend-item { display: flex; align-items: center; gap: 8px; color: var(--dl-sub); }
        .dl-swatch { width: 16px; height: 16px; border-radius: 5px; display: inline-block; }
        .sw-ruangan   { border: 2px solid var(--dl-green); }
        .sw-terisi    { border: 2px solid var(--dl-red); }
        .sw-fasilitas { background: var(--dl-blue-bg); border: 1px solid var(--dl-blue-bd); }
        .sw-servis    { background: var(--dl-gray-bg); border: 1px solid var(--dl-gray-bd); }

        /* Convention Hall (Lantai 5) */
        .dl-hall { display: flex; flex-direction: column; gap: 24px; padding: 8px; }
        .dl-hall-panggung {
            background: var(--dl-gray-bg); text-align: center; padding: 18px;
            font-weight: 700; border-radius: 12px; letter-spacing: 0.06em; color: #5a6270;
        }
        .dl-hall-rows { display: flex; flex-direction: column; gap: 30px; padding: 14px 0; }
        .dl-hall-row { display: flex; justify-content: space-evenly; gap: 18px; }
        .dl-table {
            width: 92px; height: 92px; border-radius: 50%;
            border: 2px solid var(--dl-green); background: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            font-weight: 700; font-family: inherit; text-decoration: none; color: inherit;
            box-shadow: 0 2px 8px rgba(20, 120, 60, 0.08); transition: 0.18s;
        }
        .dl-table.terisi { border-color: var(--dl-red); box-shadow: 0 2px 8px rgba(180, 60, 45, 0.08); }
        .dl-table.clickable { cursor: pointer; }
        .dl-table.clickable:hover {
            background: var(--dl-green-bg); transform: scale(1.06) translateY(-2px);
            box-shadow: 0 8px 18px rgba(30, 138, 61, 0.2);
        }
        .dl-table-nomor { font-size: 1.15rem; color: var(--dl-ink); }
        .dl-table-kursi { font-size: 0.6rem; color: var(--dl-sub); font-weight: 500; }
        .dl-hall-footer { display: flex; gap: 12px; }
        .dl-mejapanjang {
            flex: 1; background: var(--dl-gray-bg); text-align: center;
            padding: 10px; font-weight: 600; border-radius: 10px;
            font-size: 0.85rem; color: #5a6270;
        }
        .dl-sound {
            width: 84px; background: var(--dl-gray-bg); display: flex;
            align-items: center; justify-content: center; font-size: 0.6rem;
            text-align: center; border-radius: 10px; color: #8a9099; font-weight: 600;
        }

        @media (max-width: 640px) {
            .d-room { min-height: 78px; flex: 1 1 45%; }
            .d-room.is-rroom { min-height: 110px; }
            .dl-table { width: 66px; height: 66px; }
        }
    </style>
@endonce

{{-- ═════════════════════════════════════════════════════════════
     RENDER DENAH
     ═════════════════════════════════════════════════════════════ --}}
<div class="denah-layout">
    <div class="dl-header">
        <h2 class="dl-title">
            {{ $displayTitle }}
            @if ($displaySubtitle)<small>{{ $displaySubtitle }}</small>@endif
        </h2>
        @if ($displayInfo)
            <div class="dl-info">{{ $displayInfo }}</div>
        @endif
    </div>

    <div class="dl-building">
        @if (isset($data['hall']))
            {{-- ═══ MODE HALL (Lantai 5) ═══ --}}
            @php $hall = $data['hall']; @endphp
            <div class="dl-hall">
                <div class="dl-hall-panggung">{{ $hall['panggung'] ?? 'PANGGUNG' }}</div>

                <div class="dl-hall-rows">
                    @php
                        $mejaChunks = array_chunk($hall['meja'] ?? [], 3);
                        $kodeHall   = $hall['kode_hall'] ?? null;
                        $statusHall = ($statusPerKode[$kodeHall] ?? 'hijau');
                        $mejaStatus = ($statusHall === 'merah' || $statusHall === 'kuning') ? 'terisi' : 'kosong';
                        $mejaHref   = ($clickable && $linkTemplate && $kodeHall)
                            ? str_replace('__KODE__', urlencode($kodeHall), $linkTemplate)
                            : null;
                    @endphp
                    @foreach ($mejaChunks as $chunk)
                        <div class="dl-hall-row">
                            @foreach ($chunk as $meja)
                                @if ($mejaHref)
                                    <a href="{{ $mejaHref }}" class="dl-table {{ $mejaStatus }} clickable">
                                        <span class="dl-table-nomor">{{ $meja['nomor'] }}</span>
                                        <span class="dl-table-kursi">{{ $meja['kursi'] }} kursi</span>
                                    </a>
                                @else
                                    <div class="dl-table {{ $mejaStatus }}">
                                        <span class="dl-table-nomor">{{ $meja['nomor'] }}</span>
                                        <span class="dl-table-kursi">{{ $meja['kursi'] }} kursi</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>

                @if (! empty($hall['footer']))
                    <div class="dl-hall-footer">
                        @foreach ($hall['footer'] as $item)
                            @if (($item['tipe'] ?? '') === 'meja-panjang')
                                <div class="dl-mejapanjang">{{ $item['label'] }}</div>
                            @else
                                <div class="dl-sound">{{ $item['label'] }}</div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            {{-- ═══ MODE GRID + ROWS (Lantai 1, 2, 3A, 3B) ═══ --}}

            {{-- Grid utama --}}
            @if (isset($data['grid_main']))
                @php $grid = $data['grid_main']; @endphp
                <div class="dl-grid" style="grid-template-columns: repeat({{ $grid['cols'] }}, 1fr);">
                    @foreach ($grid['cells'] as $cell)
                        @php
                            $r = $renderRoom($cell);
                            $col = $cell['col'] ?? 1;
                            $row = $cell['row'] ?? 1;
                            $rowSpan = $cell['row_span'] ?? null;
                            $gridRow = $rowSpan ? "{$row} / span {$rowSpan}" : $row;
                        @endphp
                        <div style="grid-column: {{ $col }}; grid-row: {{ $gridRow }};">
                            @if ($r['clickableThis'])
                                <a href="{{ $r['href'] }}" class="{{ implode(' ', $r['classes']) }}"
                                   style="flex-grow: {{ $r['flexGrow'] }};">
                                    <span class="d-kode">{{ $r['label'] }}</span>
                                    @if ($r['luas'])
                                        <span class="d-luas">{{ number_format($r['luas'], 2, ',', '.') }} m²</span>
                                    @endif
                                    <span class="d-status {{ $r['status'] }}">{{ strtoupper($r['status']) }}</span>
                                </a>
                            @else
                                <div class="{{ implode(' ', $r['classes']) }}" style="flex-grow: {{ $r['flexGrow'] }};">
                                    @if ($r['tipe'] === 'ruangan')
                                        <span class="d-kode">{{ $r['label'] }}</span>
                                        @if ($r['luas'])
                                            <span class="d-luas">{{ number_format($r['luas'], 2, ',', '.') }} m²</span>
                                        @endif
                                        <span class="d-status {{ $r['status'] }}">{{ strtoupper($r['status']) }}</span>
                                    @else
                                        <span class="d-label">{{ $r['label'] }}</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Baris SETELAH grid (khusus L3A: K1-K6 + R2 + R1) --}}
            @if (! empty($data['rows_after']))
                <div class="dl-row">
                    @foreach ($data['rows_after'] as $item)
                        @php $r = $renderRoom($item); @endphp
                        @if ($r['clickableThis'])
                            <a href="{{ $r['href'] }}" class="{{ implode(' ', $r['classes']) }}"
                               style="flex-grow: {{ $r['flexGrow'] }};">
                                <span class="d-kode">{{ $r['label'] }}</span>
                                @if ($r['luas'])
                                    <span class="d-luas">{{ number_format($r['luas'], 2, ',', '.') }} m²</span>
                                @endif
                                <span class="d-status {{ $r['status'] }}">{{ strtoupper($r['status']) }}</span>
                            </a>
                        @else
                            <div class="{{ implode(' ', $r['classes']) }}" style="flex-grow: {{ $r['flexGrow'] }};">
                                @if ($r['tipe'] === 'ruangan')
                                    <span class="d-kode">{{ $r['label'] }}</span>
                                    @if ($r['luas'])
                                        <span class="d-luas">{{ number_format($r['luas'], 2, ',', '.') }} m²</span>
                                    @endif
                                    <span class="d-status {{ $r['status'] }}">{{ strtoupper($r['status']) }}</span>
                                @else
                                    <span class="d-label">{{ $r['label'] }}</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Baris tambahan (koridor + area servis) --}}
            @foreach ($data['rows'] ?? [] as $row)
                <div class="dl-row">
                    @foreach ($row as $item)
                        @php $r = $renderRoom($item); @endphp
                        @if ($r['clickableThis'])
                            <a href="{{ $r['href'] }}" class="{{ implode(' ', $r['classes']) }}"
                               style="flex-grow: {{ $r['flexGrow'] }};">
                                <span class="d-kode">{{ $r['label'] }}</span>
                                @if ($r['luas'])
                                    <span class="d-luas">{{ number_format($r['luas'], 2, ',', '.') }} m²</span>
                                @endif
                                <span class="d-status {{ $r['status'] }}">{{ strtoupper($r['status']) }}</span>
                            </a>
                        @else
                            <div class="{{ implode(' ', $r['classes']) }}" style="flex-grow: {{ $r['flexGrow'] }};">
                                @if ($r['tipe'] === 'ruangan')
                                    <span class="d-kode">{{ $r['label'] }}</span>
                                    @if ($r['luas'])
                                        <span class="d-luas">{{ number_format($r['luas'], 2, ',', '.') }} m²</span>
                                    @endif
                                    <span class="d-status {{ $r['status'] }}">{{ strtoupper($r['status']) }}</span>
                                @else
                                    <span class="d-label">{{ $r['label'] }}</span>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endforeach
        @endif

        {{-- Kompas --}}
        <div class="dl-compass">N ↑</div>
    </div>

    {{-- Legenda --}}
    <div class="dl-legend">
        <div class="dl-legend-item"><span class="dl-swatch sw-ruangan"></span> Ruangan Kosong</div>
        <div class="dl-legend-item"><span class="dl-swatch sw-terisi"></span> Ruangan Terisi / Tidak Aktif</div>
        <div class="dl-legend-item"><span class="dl-swatch sw-fasilitas"></span> Fasilitas Umum</div>
        <div class="dl-legend-item"><span class="dl-swatch sw-servis"></span> Area Servis</div>
    </div>
</div>