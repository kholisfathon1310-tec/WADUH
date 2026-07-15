<?php

namespace App\Services;

use App\Enums\SatuanSewa;
use App\Enums\StatusVerifikasi;
use App\Models\Reservasi;

/**
 * Checklist validasi sebelum admin boleh menyetujui satu Reservasi (Stage 3).
 * Dipakai bersama oleh halaman detail (tampilan checklist) dan aksi Setujui (backend).
 */
class ReservasiApprovalService
{
    /**
     * Jenis sewa yang boleh untuk tiap kategori. Kategori tak terdaftar → semua boleh.
     * (Nilai string literal — PHP 8.1 tak mengizinkan Enum->value di ekspresi konstan.)
     */
    private const SATUAN_KATEGORI = [
        'Convention Hall'  => ['Hari'],
        'Working Space'    => ['Jam', 'Hari', 'Bulan'],
        'Co-Working Space' => ['Jam', 'Hari', 'Bulan'],
    ];

    public function __construct(private readonly AvailabilityService $availability)
    {
    }

    /**
     * @return array<int, array{label: string, passed: bool, note: string}>
     */
    public function checklist(Reservasi $reservasi): array
    {
        $reservasi->loadMissing('tarifSewa.fasilitas', 'tarifSewa.jenisSewa', 'dokumenPersyaratan');
        $fasilitas = $reservasi->tarifSewa->fasilitas;
        $jenis = $reservasi->tarifSewa->jenisSewa;
        $satuan = $jenis->satuan;

        return [
            $this->cekBentrok($reservasi, $fasilitas),
            $this->cekJenisKategori($fasilitas->kategori_fasilitas, $satuan),
            $this->cekBulan($reservasi, $jenis, $satuan),
            $this->cekKapasitas($reservasi, $fasilitas),
        ];
    }

    public function passes(Reservasi $reservasi): bool
    {
        foreach ($this->checklist($reservasi) as $item) {
            if (! $item['passed']) {
                return false;
            }
        }

        return true;
    }

    private function cekBentrok(Reservasi $reservasi, $fasilitas): array
    {
        $slot = [
            'tanggal_mulai'   => $reservasi->tanggal_mulai->toDateString(),
            'tanggal_selesai' => $reservasi->tanggal_selesai->toDateString(),
            'jam_mulai'       => $reservasi->jam_mulai,
            'jam_selesai'     => $reservasi->jam_selesai,
        ];

        // Abaikan baris ini sendiri (statusnya Menunggu = aktif, akan cocok dengan dirinya).
        $bentrok = $this->availability->hasReservationConflict($fasilitas->id_fasilitas, $slot, $reservasi->id_reservasi);

        return [
            'label'  => 'Tidak bentrok dengan Reservasi Menunggu/Disetujui lain pada periode yang sama',
            'passed' => ! $bentrok,
            'note'   => $bentrok ? 'Ada reservasi lain yang bentrok pada fasilitas & periode ini.' : 'Tidak ada bentrok.',
        ];
    }

    private function cekJenisKategori(string $kategori, SatuanSewa $satuan): array
    {
        $allowed = self::SATUAN_KATEGORI[$kategori] ?? [SatuanSewa::Jam->value, SatuanSewa::Hari->value, SatuanSewa::Bulan->value];
        $ok = in_array($satuan->value, $allowed, true);

        return [
            'label'  => 'Jenis sewa sesuai kategori fasilitas',
            'passed' => $ok,
            'note'   => $ok ? "Sewa {$satuan->value} diperbolehkan untuk {$kategori}." : "Kategori {$kategori} tidak menerima sewa {$satuan->value}.",
        ];
    }

    private function cekBulan(Reservasi $reservasi, $jenis, SatuanSewa $satuan): array
    {
        if ($satuan !== SatuanSewa::Bulan) {
            return ['label' => 'Syarat sewa Bulan (durasi & dokumen)', 'passed' => true, 'note' => 'Tidak berlaku (bukan sewa Bulan).'];
        }

        $min = (int) $jenis->durasi_minimum;
        $durasiOk = $reservasi->durasi >= $min;

        $dokumen = $reservasi->dokumenPersyaratan;
        $adaDokumen = $dokumen->isNotEmpty();
        $semuaValid = $adaDokumen && $dokumen->every(fn ($d) => $d->status_verifikasi === StatusVerifikasi::Valid);

        $passed = $durasiOk && $semuaValid;
        $note = match (true) {
            ! $durasiOk   => "Durasi {$reservasi->durasi} bulan < minimum {$min} bulan.",
            ! $adaDokumen => 'Belum ada dokumen persyaratan.',
            ! $semuaValid => 'Masih ada dokumen yang belum berstatus Valid.',
            default       => "Durasi ≥ {$min} bulan dan semua dokumen Valid.",
        };

        return ['label' => "Sewa Bulan: durasi ≥ {$min} bulan & semua dokumen Valid", 'passed' => $passed, 'note' => $note];
    }

    private function cekKapasitas(Reservasi $reservasi, $fasilitas): array
    {
        $ok = $reservasi->jumlah_pengguna <= $fasilitas->kapasitas;

        return [
            'label'  => 'Jumlah pengguna tidak melebihi kapasitas',
            'passed' => $ok,
            'note'   => "{$reservasi->jumlah_pengguna} dari kapasitas {$fasilitas->kapasitas} orang.",
        ];
    }
}
