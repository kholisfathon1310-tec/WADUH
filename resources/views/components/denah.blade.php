{{--
    <x-denah> — FloorPlan: denah interaktif SVG (db-spec-denah-layout.md).
    Bentuk denah (viewbox/envelope/area_statis/poligon ruangan) BERASAL dari
    config/denah.php dan TIDAK diubah oleh redesign ini — hanya styling, tooltip,
    dan panel detail (RoomDetail) yang ditambahkan/dipercantik.
    Props:
      - lantai              : nomor_lantai ('1','2','3A','3B','5')
      - statusPerFasilitas  : [kode_fasilitas => 'hijau'|'merah'|'kuning'] (dari AvailabilityService)
      - clickable           : true = mode pilih (pemesan, multi-select + bar ringkasan + RoomDetail)
      - linkTemplate        : URL dengan placeholder __ID__ — mode clickable: tombol lanjut, chip & RoomDetail;
                              mode read-only: klik poligon langsung navigasi (admin monitoring)
      - jenis               : id_jenis_sewa terpilih (opsional) — hanya dipakai untuk menampilkan
                              Harga di RoomDetail (mode clickable). Tidak memengaruhi alur/logic reservasi.
--}}
@props(['lantai', 'statusPerFasilitas' => [], 'clickable' => false, 'linkTemplate' => null, 'jenis' => null])

@php
    use App\Support\Denah;
    use App\Models\TarifSewa;
    use App\Enums\StatusAktif;

    $cfg = config('denah.lantai.'.$lantai);

    $db = \App\Models\Fasilitas::whereHas('lantai', fn ($q) => $q->where('nomor_lantai', $lantai))
        ->get(['id_fasilitas', 'kode_fasilitas', 'nama_fasilitas', 'kapasitas', 'luas', 'deskripsi', 'status_aktif'])
        ->keyBy('kode_fasilitas');

    // Harga per fasilitas untuk jenis sewa terpilih — hanya query tampilan (read-only),
    // tidak menyentuh controller/route maupun alur checkout.
    $hargaByFasilitas = ($clickable && $jenis)
        ? TarifSewa::whereIn('id_fasilitas', $db->pluck('id_fasilitas'))
            ->where('id_jenis_sewa', $jenis)
            ->where('status_aktif', StatusAktif::Aktif->value)
            ->pluck('harga', 'id_fasilitas')
        : collect();

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

    <div class="denah-wrap" data-denah data-reveal data-clickable="{{ $clickable ? 1 : 0 }}" @if ($linkTemplate) data-link="{{ $linkTemplate }}" @endif>
        <div class="denah-shell">
            {{-- RoomCard: setiap poligon berperilaku seperti kartu kecil (kode/luas/harga lewat tooltip & panel) --}}
            <div class="denah-stage">
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
                            $harga = $f ? $hargaByFasilitas->get($f->id_fasilitas) : null;
                            $statusLabel = ['hijau' => 'Kosong', 'kuning' => 'Sebagian terisi', 'merah' => 'Penuh / tidak aktif'][$st];
                        @endphp
                        <g class="d-room st-{{ $st }}"
                           data-kode="{{ $kode }}"
                           data-label="{{ $short }}"
                           data-id="{{ $f?->id_fasilitas }}"
                           data-st="{{ $st }}"
                           data-nama="{{ $f?->nama_fasilitas ?? $kode }}"
                           data-luas="{{ $f?->luas }}"
                           data-kapasitas="{{ $f?->kapasitas }}"
                           data-deskripsi="{{ $f?->deskripsi }}"
                           data-harga="{{ $harga }}"
                           data-status-label="{{ $statusLabel }}">
                            <polygon points="{{ $points }}"/>
                            <text x="{{ $cx }}" y="{{ $cy }}" class="d-lbl {{ str_starts_with($short, 'M') ? 'sm' : 'lg' }}">{{ $short }}</text>
                        </g>
                    @endforeach
                </svg>
            </div>

            {{-- RoomDetail: panel di sebelah kanan denah, hanya pada mode pilih (pemesan) --}}
            @if ($clickable)
                <x-denah.room-detail/>
            @endif
        </div>

        {{-- RoomLegend --}}
        <x-denah.legend :clickable="$clickable"/>

        @if ($clickable)
            <div class="denah-bar">
                <span class="fw-semibold small" data-count>0 fasilitas dipilih</span>
                <span class="denah-chips" data-chips></span>
                <a href="#" class="btn btn-brand btn-sm ms-auto disabled dn-go" data-go aria-disabled="true">
                    Isi Jadwal <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        @endif
    </div>

    {{-- RoomTooltip: kartu mengambang saat hover, mengikuti kursor --}}
    <div class="dn-tooltip" data-dn-tooltip hidden>
        <div class="dn-tt-head">
            <span class="dn-tt-kode" data-tt-kode></span>
            <span class="dn-tt-status" data-tt-status></span>
        </div>
        <div class="dn-tt-row" data-tt-luas-row><i class="bi bi-aspect-ratio"></i><span data-tt-luas></span></div>
        <div class="dn-tt-row" data-tt-harga-row hidden><i class="bi bi-tag"></i><span data-tt-harga></span></div>
        <div class="dn-tt-row" data-tt-kap-row><i class="bi bi-people"></i><span data-tt-kap></span></div>
        <div class="dn-tt-hint" data-tt-hint></div>
    </div>

    @once
    <style>
        /* ===== Palet khusus halaman Denah (tidak memengaruhi warna identitas di halaman lain) ===== */
        .denah-wrap { --dn-primary:#0F766E; --dn-secondary:#14B8A6; --dn-bg:#F8FAFC; --dn-border:#E2E8F0; --dn-danger:#EF4444; --dn-warning:#F59E0B; --dn-success:#22C55E; --dn-selected:#2563EB; }

        .denah-shell { display:flex; align-items:flex-start; gap:1rem; }
        .denah-stage { flex:1 1 auto; min-width:0; overflow-x:auto; padding-bottom:.25rem; }
        .denah-svg { display:block; background:#fff; border:1px solid var(--dn-border); border-radius:1.1rem; box-shadow:0 3px 14px rgba(15,23,42,.05); min-width:520px; }

        .d-env { fill:none; stroke:#cbd5e1; stroke-width:1; }
        .d-static { fill:#f1f5f9; stroke:#dbe3ea; stroke-width:1; }
        .d-static-lbl { fill:#94a3b8; font-size:10px; text-anchor:middle; dominant-baseline:middle; font-family:'DM Sans',sans-serif; pointer-events:none; }

        .d-room polygon { stroke-width:1.5; transition:filter .15s ease, transform .15s ease, stroke-width .15s ease; transform-box:fill-box; transform-origin:center; }
        .d-room .d-lbl { text-anchor:middle; dominant-baseline:middle; font-family:'DM Sans',sans-serif; font-weight:700; pointer-events:none; }
        .d-room .d-lbl.sm { font-size:10px; }
        .d-room .d-lbl.lg { font-size:12px; }

        .d-room.st-hijau polygon { fill:#ecfdf5; stroke:var(--dn-success); }
        .d-room.st-hijau .d-lbl { fill:#0d8a5f; }
        .d-room.st-kuning polygon { fill:#fffbeb; stroke:var(--dn-warning); }
        .d-room.st-kuning .d-lbl { fill:#92400e; }
        .d-room.st-merah polygon { fill:#fef2f2; stroke:var(--dn-danger); }
        .d-room.st-merah .d-lbl { fill:#b91c1c; }
        .d-room.st-merah { cursor:not-allowed; }

        .d-room.klik { cursor:pointer; }
        .d-room.klik:hover polygon { filter:brightness(.97) drop-shadow(0 4px 10px rgba(15,23,42,.14)); transform:scale(1.035); }

        .d-room.terpilih polygon { stroke:var(--dn-selected) !important; stroke-width:2.25 !important; fill:#eff6ff !important; }
        .d-room.terpilih .d-lbl { fill:var(--dn-selected) !important; }

        .denah-bar { display:flex; flex-wrap:wrap; align-items:center; gap:.6rem; margin-top:.85rem; padding:.7rem .95rem; background:#fff; border:1px solid var(--dn-border); border-radius:1rem; box-shadow:0 3px 14px rgba(15,23,42,.04); }
        .denah-chips { display:flex; flex-wrap:wrap; gap:.35rem; }
        .denah-chips a { display:inline-block; padding:.22rem .65rem; border-radius:2rem; background:#eff6ff; color:var(--dn-selected); font-size:.75rem; font-weight:700; text-decoration:none; transition:background .15s; }
        .denah-chips a:hover { background:#dbeafe; }
        .dn-go { border-radius:.65rem; font-weight:700; background:var(--dn-primary); border-color:var(--dn-primary); }
        .dn-go:not(.disabled):hover { background:#0c5f58; border-color:#0c5f58; }

        /* RoomTooltip */
        .dn-tooltip { position:fixed; z-index:1080; min-width:11rem; background:#0f172a; color:#f1f5f9; border-radius:.75rem; padding:.65rem .8rem; font-size:.78rem; box-shadow:0 14px 30px rgba(2,6,23,.28); pointer-events:none; transition:opacity .1s ease; }
        .dn-tt-head { display:flex; align-items:center; justify-content:space-between; gap:.6rem; margin-bottom:.35rem; }
        .dn-tt-kode { font-weight:800; font-family:'Plus Jakarta Sans',sans-serif; letter-spacing:.02em; }
        .dn-tt-status { font-size:.66rem; font-weight:700; padding:.15rem .5rem; border-radius:2rem; text-transform:uppercase; letter-spacing:.03em; }
        .dn-tt-status.hijau { background:rgba(34,197,94,.22); color:#86efac; }
        .dn-tt-status.kuning { background:rgba(245,158,11,.22); color:#fcd34d; }
        .dn-tt-status.merah { background:rgba(239,68,68,.22); color:#fca5a5; }
        .dn-tt-row { display:flex; align-items:center; gap:.45rem; opacity:.92; padding:.12rem 0; }
        .dn-tt-row i { width:1rem; text-align:center; opacity:.75; }
        .dn-tt-hint { margin-top:.35rem; padding-top:.35rem; border-top:1px solid rgba(255,255,255,.12); font-size:.7rem; opacity:.75; font-style:italic; }

        @media (max-width: 991.98px) {
            .denah-shell { flex-direction:column; }
        }
    </style>
    <script>
        // window.initDenah — bisa dipanggil ulang setelah konten denah diganti via AJAX
        // (filter interaktif), bukan cuma sekali saat DOMContentLoaded.
        window.initDenah = function (root) {
            const fmtRupiah = n => 'Rp ' + Number(n).toLocaleString('id-ID');

            (root || document).querySelectorAll('[data-denah]:not([data-denah-siap])').forEach(wrap => {
                wrap.setAttribute('data-denah-siap', '1');
                const clickable = wrap.dataset.clickable === '1';
                const link = wrap.dataset.link || null;
                const dipilih = new Map(); // id -> {label, kode}

                const barCount = wrap.querySelector('[data-count]');
                const barChips = wrap.querySelector('[data-chips]');
                const barGo = wrap.querySelector('[data-go]');

                const detail = wrap.querySelector('[data-dn-detail]');
                const ddEmpty = wrap.querySelector('[data-dn-empty]');
                const ddBody = wrap.querySelector('[data-dn-body]');
                const ddClose = wrap.querySelector('[data-dn-close]');

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

                // ===== RoomDetail: isi panel kanan dari data-* ruangan yang diklik =====
                const showDetail = room => {
                    if (!detail) return;
                    const st = room.dataset.st;
                    const statusLabel = room.dataset.statusLabel || '';

                    detail.querySelector('[data-dd-status]').textContent = statusLabel;
                    detail.querySelector('[data-dd-status]').className = 'dn-detail-status ' + st;
                    detail.querySelector('[data-dd-kode]').textContent = room.dataset.kode;
                    detail.querySelector('[data-dd-nama]').textContent = room.dataset.nama || '';
                    detail.querySelector('[data-dd-luas]').textContent = room.dataset.luas ? (room.dataset.luas.replace('.', ',') + ' m²') : '-';
                    detail.querySelector('[data-dd-kapasitas]').textContent = room.dataset.kapasitas ? (room.dataset.kapasitas + ' orang') : '-';

                    const hargaWrap = detail.querySelector('[data-dd-harga-wrap]');
                    if (room.dataset.harga) {
                        hargaWrap.hidden = false;
                        detail.querySelector('[data-dd-harga]').textContent = fmtRupiah(room.dataset.harga);
                    } else {
                        hargaWrap.hidden = true;
                    }

                    const deskEl = detail.querySelector('[data-dd-deskripsi]');
                    if (room.dataset.deskripsi) {
                        deskEl.hidden = false;
                        deskEl.textContent = room.dataset.deskripsi;
                    } else {
                        deskEl.hidden = true;
                    }

                    const btn = detail.querySelector('[data-dd-btn]');
                    if (st !== 'merah' && link) {
                        btn.href = urlFor(room.dataset.id);
                        btn.classList.remove('disabled');
                        btn.style.pointerEvents = '';
                        btn.style.opacity = '';
                    } else {
                        btn.href = '#';
                        btn.style.pointerEvents = 'none';
                        btn.style.opacity = '.55';
                    }

                    ddEmpty.hidden = true;
                    ddBody.hidden = false;
                };

                ddClose?.addEventListener('click', () => {
                    ddBody.hidden = true;
                    ddEmpty.hidden = false;
                });

                // ===== RoomTooltip: kartu mengambang saat hover, ikut kursor =====
                const tooltip = document.querySelector('[data-dn-tooltip]');
                const positionTooltip = evt => {
                    if (!tooltip) return;
                    const pad = 14;
                    let x = evt.clientX + pad;
                    let y = evt.clientY + pad;
                    const rect = tooltip.getBoundingClientRect();
                    if (x + rect.width > window.innerWidth - 8) x = evt.clientX - rect.width - pad;
                    if (y + rect.height > window.innerHeight - 8) y = evt.clientY - rect.height - pad;
                    tooltip.style.left = x + 'px';
                    tooltip.style.top = y + 'px';
                };
                const showTooltip = (room, evt) => {
                    if (!tooltip) return;
                    const st = room.dataset.st;
                    tooltip.querySelector('[data-tt-kode]').textContent = room.dataset.kode;
                    const ttStatus = tooltip.querySelector('[data-tt-status]');
                    ttStatus.textContent = room.dataset.statusLabel || '';
                    ttStatus.className = 'dn-tt-status ' + st;
                    tooltip.querySelector('[data-tt-luas]').textContent = room.dataset.luas ? (room.dataset.luas.replace('.', ',') + ' m²') : '-';
                    tooltip.querySelector('[data-tt-kap]').textContent = (room.dataset.kapasitas || '-') + ' orang';

                    const hargaRow = tooltip.querySelector('[data-tt-harga-row]');
                    if (room.dataset.harga) {
                        hargaRow.hidden = false;
                        tooltip.querySelector('[data-tt-harga]').textContent = fmtRupiah(room.dataset.harga);
                    } else {
                        hargaRow.hidden = true;
                    }

                    const hint = tooltip.querySelector('[data-tt-hint]');
                    if (clickable && st !== 'merah') {
                        hint.hidden = false;
                        hint.textContent = 'Klik untuk memilih';
                    } else if (!clickable && link) {
                        hint.hidden = false;
                        hint.textContent = 'Klik untuk lihat detail';
                    } else {
                        hint.hidden = true;
                    }

                    tooltip.hidden = false;
                    positionTooltip(evt);
                };
                const hideTooltip = () => { if (tooltip) tooltip.hidden = true; };

                wrap.querySelectorAll('.d-room').forEach(room => {
                    // Tooltip aktif untuk semua ruangan (termasuk merah) — sekadar info, tidak mengubah logic.
                    room.addEventListener('mouseenter', evt => showTooltip(room, evt));
                    room.addEventListener('mousemove', evt => positionTooltip(evt));
                    room.addEventListener('mouseleave', hideTooltip);

                    // Merah tidak bisa dipilih pemesan (mode clickable), tapi admin (mode read-only)
                    // tetap boleh membuka detail ruangan merah untuk lihat siapa pemesannya.
                    if (room.dataset.st === 'merah' && clickable) return;
                    if (room.dataset.st === 'merah' && !link) return;

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
                            showDetail(room);
                        } else if (link) {
                            window.location.href = urlFor(room.dataset.id);
                        }
                    });
                });
            });
        };
        document.addEventListener('DOMContentLoaded', () => window.initDenah());
    </script>
    @endonce
@endif
