{{--
    <x-denah> — denah interaktif SVG (db-spec-denah-layout.md).
    Props:
      - lantai              : nomor_lantai ('1','2','3A','3B','5')
      - statusPerFasilitas  : [kode_fasilitas => 'hijau'|'merah'|'kuning'] (dari AvailabilityService)
      - clickable           : true = mode pilih (pemesan, multi-select + bar ringkasan)
      - linkTemplate        : URL dengan placeholder __ID__ — mode clickable: tombol lanjut & chip;
                              mode read-only: klik poligon langsung navigasi (admin monitoring)
--}}
@props(['lantai', 'statusPerFasilitas' => [], 'clickable' => false, 'linkTemplate' => null])

@php
    use App\Support\Denah;

    $cfg = config('denah.lantai.'.$lantai);

    $db = \App\Models\Fasilitas::whereHas('lantai', fn ($q) => $q->where('nomor_lantai', $lantai))
        ->get(['id_fasilitas', 'kode_fasilitas', 'nama_fasilitas', 'kapasitas', 'status_aktif'])
        ->keyBy('kode_fasilitas');

    $cfgCodes = array_keys($cfg['ruangan'] ?? []);
    $hilangDiDb = array_values(array_diff($cfgCodes, $db->keys()->all()));
    $hilangDiConfig = array_values(array_diff($db->keys()->all(), $cfgCodes));
@endphp

@if (! $cfg)
    <div class="alert alert-danger"><i class="bi bi-exclamation-octagon me-1"></i>Denah lantai "{{ $lantai }}" tidak ditemukan di config/denah.php.</div>
@else
    @if ($hilangDiDb || $hilangDiConfig)
        <div class="alert alert-warning py-2 small">
            <i class="bi bi-exclamation-triangle me-1"></i><strong>Peringatan denah:</strong>
            @if ($hilangDiDb) kode di config tanpa data DB: <code>{{ implode(', ', $hilangDiDb) }}</code>.@endif
            @if ($hilangDiConfig) kode di DB tanpa poligon denah: <code>{{ implode(', ', $hilangDiConfig) }}</code>.@endif
        </div>
    @endif

    <div class="denah-wrap" data-denah data-clickable="{{ $clickable ? 1 : 0 }}" @if ($linkTemplate) data-link="{{ $linkTemplate }}" @endif>
        <svg viewBox="{{ $cfg['viewbox'] }}" width="100%" role="img" aria-label="Denah Lantai {{ $lantai }}" class="denah-svg">
            {{-- 1. Envelope: garis bantu bentuk gedung (tidak bisa diklik) --}}
            @if (($cfg['envelope'] ?? '') !== '')
                <polygon points="{{ $cfg['envelope'] }}" class="d-env"/>
            @endif

            {{-- 2. Area statis --}}
            @foreach ($cfg['area_statis'] ?? [] as $area)
                @php [$ax, $ay] = Denah::centroid($area['points']); @endphp
                <polygon points="{{ $area['points'] }}" class="d-static"/>
                <text x="{{ $ax }}" y="{{ $ay }}" class="d-static-lbl">{{ $area['label'] }}</text>
            @endforeach

            {{-- 3. Ruangan --}}
            @foreach ($cfg['ruangan'] as $kode => $points)
                @php
                    $f = $db->get($kode);
                    // Tidak Aktif SELALU merah; ruangan di luar peta status (mis. kategori lain
                    // pada lantai campuran) juga merah — tidak bisa dipilih dari alur ini.
                    $st = ($f && $f->status_aktif->value === 'Tidak Aktif')
                        ? 'merah'
                        : ($statusPerFasilitas[$kode] ?? 'merah');
                    [$cx, $cy] = Denah::centroid($points);
                    $short = Denah::labelPendek($kode);
                @endphp
                <g class="d-room st-{{ $st }}" data-kode="{{ $kode }}" data-label="{{ $short }}"
                   data-id="{{ $f?->id_fasilitas }}" data-st="{{ $st }}">
                    <title>{{ $f?->nama_fasilitas ?? $kode }}@if($f) · {{ $f->kapasitas }} orang @endif · {{ ['hijau' => 'Tersedia', 'kuning' => 'Sebagian terisi', 'merah' => 'Penuh / tidak aktif'][$st] }}</title>
                    <polygon points="{{ $points }}"/>
                    <text x="{{ $cx }}" y="{{ $cy }}" class="d-lbl {{ str_starts_with($short, 'M') ? 'sm' : 'lg' }}">{{ $short }}</text>
                </g>
            @endforeach
        </svg>

        @if ($clickable)
            <div class="denah-bar">
                <span class="fw-semibold small" data-count>0 fasilitas dipilih</span>
                <span class="denah-chips" data-chips></span>
                <a href="#" class="btn btn-brand btn-sm ms-auto disabled" data-go aria-disabled="true">
                    Isi Jadwal <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        @endif
    </div>

    @once
    <style>
        .denah-svg { display:block; background:#fff; border:1px solid #e4ebf2; border-radius:1rem; }
        .d-env { fill:none; stroke:#9fb0c0; stroke-width:1; }
        .d-static { fill:#eef2f6; stroke:#c3cfda; stroke-width:1; }
        .d-static-lbl { fill:#8093a7; font-size:10px; text-anchor:middle; dominant-baseline:middle; font-family:'DM Sans',sans-serif; pointer-events:none; }
        .d-room polygon { stroke-width:1.5; transition:filter .12s; }
        .d-room .d-lbl { text-anchor:middle; dominant-baseline:middle; font-family:'DM Sans',sans-serif; font-weight:700; pointer-events:none; }
        .d-room .d-lbl.sm { font-size:10px; }
        .d-room .d-lbl.lg { font-size:12px; }
        .d-room.st-hijau polygon { fill:#e9faf3; stroke:#25b47e; }
        .d-room.st-hijau .d-lbl { fill:#0d8a5f; }
        .d-room.st-kuning polygon { fill:#fff7e0; stroke:#e5b94e; }
        .d-room.st-kuning .d-lbl { fill:#9a6b00; }
        .d-room.st-merah polygon { fill:#fdeaea; stroke:#d95757; }
        .d-room.st-merah .d-lbl { fill:#c02929; }
        .d-room.st-merah { cursor:not-allowed; }
        .d-room.klik { cursor:pointer; }
        .d-room.klik:hover polygon { filter:brightness(.94); }
        .d-room.terpilih polygon { stroke:#176b87 !important; stroke-width:2 !important; fill:#dcecf5 !important; }
        .d-room.terpilih .d-lbl { fill:#176b87 !important; }
        .denah-bar { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-top:.65rem; padding:.6rem .85rem; background:#fff; border:1px solid #e4ebf2; border-radius:.8rem; }
        .denah-chips { display:flex; flex-wrap:wrap; gap:.3rem; }
        .denah-chips a { display:inline-block; padding:.18rem .55rem; border-radius:2rem; background:#dcecf5; color:#176b87; font-size:.75rem; font-weight:700; text-decoration:none; }
        .denah-chips a:hover { background:#c9e2ef; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-denah]').forEach(wrap => {
                const clickable = wrap.dataset.clickable === '1';
                const link = wrap.dataset.link || null;
                const dipilih = new Map(); // id -> {label, kode}

                const barCount = wrap.querySelector('[data-count]');
                const barChips = wrap.querySelector('[data-chips]');
                const barGo = wrap.querySelector('[data-go]');

                const urlFor = id => link ? link.replace('__ID__', id) : '#';

                const render = () => {
                    if (!barCount) return;
                    barCount.textContent = dipilih.size + ' fasilitas dipilih' + (dipilih.size ? ':' : '');
                    barChips.innerHTML = '';
                    dipilih.forEach((v, id) => {
                        const a = document.createElement('a');
                        a.href = urlFor(id);
                        a.textContent = v.label;
                        a.title = 'Isi jadwal ' + v.kode;
                        barChips.appendChild(a);
                    });
                    if (dipilih.size > 0) {
                        // Multi-select: ruangan pertama dibuka, sisanya diantrikan (?antrian=id2,id3)
                        // sehingga setelah tiap "Tambah ke Keranjang" otomatis lanjut ke ruangan berikutnya.
                        const ids = [...dipilih.keys()];
                        const antrian = ids.slice(1);
                        barGo.classList.remove('disabled');
                        barGo.removeAttribute('aria-disabled');
                        barGo.href = urlFor(ids[0]) + (antrian.length ? '&antrian=' + antrian.join(',') : '');
                        barGo.innerHTML = 'Isi Jadwal' + (dipilih.size > 1 ? ' (' + dipilih.size + ' ruangan)' : '') + ' <i class="bi bi-arrow-right"></i>';
                    } else {
                        barGo.classList.add('disabled');
                        barGo.setAttribute('aria-disabled', 'true');
                        barGo.href = '#';
                        barGo.innerHTML = 'Isi Jadwal <i class="bi bi-arrow-right"></i>';
                    }
                };

                wrap.querySelectorAll('.d-room').forEach(room => {
                    if (room.dataset.st === 'merah') return; // merah tidak merespons klik

                    room.classList.add('klik');
                    room.addEventListener('click', () => {
                        if (clickable) {
                            const id = room.dataset.id;
                            if (dipilih.has(id)) {
                                dipilih.delete(id);
                                room.classList.remove('terpilih');
                            } else {
                                dipilih.set(id, { label: room.dataset.label, kode: room.dataset.kode });
                                room.classList.add('terpilih');
                            }
                            render();
                        } else if (link) {
                            window.location.href = urlFor(room.dataset.id);
                        }
                    });
                });
            });
        });
    </script>
    @endonce
@endif
