{{--
    <x-denah.legend> — RoomLegend: badge status modern untuk denah interaktif.
    Props:
      - clickable : true = tampilkan juga status "Dipilih" (mode pemesan).
--}}
@props(['clickable' => false])

<div class="dn-legend">
    <span class="dn-leg dn-leg--hijau"><i class="bi bi-circle-fill"></i>Kosong</span>
    <span class="dn-leg dn-leg--kuning"><i class="bi bi-circle-fill"></i>Sebagian</span>
    <span class="dn-leg dn-leg--merah"><i class="bi bi-circle-fill"></i>Terisi</span>
    @if ($clickable)
        <span class="dn-leg dn-leg--biru"><i class="bi bi-circle-fill"></i>Dipilih</span>
    @endif
</div>

@once
    <style>
        .dn-legend { display:flex; flex-wrap:wrap; gap:.5rem; margin:.85rem 0 0; }
        .dn-leg { display:inline-flex; align-items:center; gap:.4rem; font-size:.76rem; font-weight:700; padding:.35rem .75rem; border-radius:2rem; background:#fff; border:1px solid #E2E8F0; color:#334155; }
        .dn-leg i { font-size:.5rem; }
        .dn-leg--hijau i { color:#22C55E; }
        .dn-leg--kuning i { color:#F59E0B; }
        .dn-leg--merah i { color:#EF4444; }
        .dn-leg--biru i { color:#2563EB; }
    </style>
@endonce
