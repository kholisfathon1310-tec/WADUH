{{--
    Pemilih jam Indonesia — dropdown custom yang SELALU membuka ke bawah,
    berisi grid pill "07.00 WIB" s/d "21.00 WIB".
    Variabel: $name, $value (terpilih, format "08:00"), $required (default true), $kecil (ukuran kecil).
--}}
@php
    $required = $required ?? true;
    $kecil = $kecil ?? false;
    $value = $value ?? '';
    $labelTerpilih = $value ? str_replace(':', '.', $value) : null;
@endphp
<div class="jampicker {{ $kecil ? 'jam-kecil' : '' }}" data-jampicker>
    <input type="hidden" name="{{ $name }}" value="{{ $value }}" @if($required) required @endif>
    <button type="button" class="form-control {{ $kecil ? 'form-control-sm' : '' }} jam-btn {{ $labelTerpilih ? '' : 'jam-kosong' }}">
        <i class="bi bi-clock me-1"></i><span class="jam-label">{{ $labelTerpilih ?? '— pilih jam —' }}</span>
        <i class="bi bi-chevron-down jam-caret"></i>
    </button>
    <div class="jam-panel">
        <div class="jam-head"><i class="bi bi-clock-history me-1"></i>Jam operasional 08.00–16.00</div>
        <div class="jam-grid">
            @for ($h = 8; $h <= 16; $h++)
                @php $v = sprintf('%02d:00', $h); @endphp
                <button type="button" class="jam-opt {{ $value === $v ? 'aktif' : '' }}" data-val="{{ $v }}">{{ sprintf('%02d.00', $h) }}</button>
            @endfor
        </div>
    </div>
</div>

@once
<style>
    .jampicker { position:relative; }
    .jam-btn { display:flex; align-items:center; gap:.25rem; text-align:left; background:#fff; cursor:pointer; }
    .jam-btn .jam-label { flex:1; font-weight:600; color:var(--ink, #15243b); }
    .jam-btn.jam-kosong .jam-label { color:#8a97a5; font-weight:500; }
    .jam-btn .jam-caret { font-size:.7rem; color:#8a97a5; transition:transform .2s; }
    .jampicker.buka .jam-btn { border-color:var(--primary, #176b87); box-shadow:0 0 0 .2rem rgba(23,107,135,.12); }
    .jampicker.buka .jam-caret { transform:rotate(180deg); }

    /* Panel SELALU membuka ke bawah */
    .jam-panel { display:none; position:absolute; top:calc(100% + 6px); left:0; z-index:1050; min-width:238px;
        background:#fff; border:1px solid #dbe6ee; border-radius:.9rem; box-shadow:0 18px 40px rgba(21,36,59,.18);
        padding:.6rem; animation:jamMuncul .16s ease; }
    .jampicker.buka .jam-panel { display:block; }
    @keyframes jamMuncul { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:none; } }
    .jam-head { font-size:.72rem; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:#5b7286; padding:0 .25rem .45rem; }
    .jam-grid { display:grid; grid-template-columns:repeat(3, 1fr); gap:.35rem; max-height:222px; overflow-y:auto; padding-right:.15rem; }
    .jam-grid::-webkit-scrollbar { width:6px; } .jam-grid::-webkit-scrollbar-thumb { background:#cfdde8; border-radius:3px; }
    .jam-opt { border:1.5px solid #dbe6ee; background:#f8fbfd; border-radius:.6rem; padding:.42rem .25rem;
        font-weight:700; font-size:.85rem; color:#33475c; cursor:pointer; transition:all .12s; }
    .jam-opt:hover { border-color:var(--teal, #24aa9a); background:#e9faf3; color:#0d8a5f; transform:translateY(-1px); }
    .jam-opt.aktif { background:var(--primary, #176b87); border-color:var(--primary, #176b87); color:#fff; box-shadow:0 6px 14px rgba(23,107,135,.3); }
    .jam-kecil .jam-panel { min-width:210px; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tutupSemua = () => document.querySelectorAll('[data-jampicker].buka').forEach(p => p.classList.remove('buka'));

        document.querySelectorAll('[data-jampicker]').forEach(picker => {
            const tombol = picker.querySelector('.jam-btn');
            const hidden = picker.querySelector('input[type="hidden"]');
            const label = picker.querySelector('.jam-label');

            tombol.addEventListener('click', e => {
                e.stopPropagation();
                const buka = picker.classList.contains('buka');
                tutupSemua();
                if (! buka) picker.classList.add('buka');
            });

            picker.querySelectorAll('.jam-opt').forEach(opt => {
                opt.addEventListener('click', e => {
                    e.stopPropagation();
                    hidden.value = opt.dataset.val;
                    label.textContent = opt.dataset.val.replace(':', '.');
                    tombol.classList.remove('jam-kosong');
                    picker.querySelectorAll('.jam-opt').forEach(o => o.classList.toggle('aktif', o === opt));
                    picker.classList.remove('buka');
                    hidden.dispatchEvent(new Event('input', { bubbles: true })); // bersihkan tanda error
                });
            });
        });

        document.addEventListener('click', tutupSemua);
    });
</script>
@endonce
