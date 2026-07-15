<?php

namespace App\Services;

use App\Enums\StatusReservasi;
use App\Models\Fasilitas;
use App\Models\Reservasi;
use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Logika ketersediaan fasilitas + hold pra-checkout (Cache).
 *
 * Dua sumber "blokir" ketersediaan:
 *   1. Baris Reservasi aktif (status Menunggu/Disetujui) yang jadwalnya bentrok.
 *   2. Cache hold aktif milik SESSION LAIN (hold pra-checkout, TTL 15 menit).
 *
 * Hold disimpan di Cache (bukan tabel) karena Reservasi.id_pemesan NOT NULL sedangkan
 * data pemesan baru tersedia saat checkout — lihat db-spec-stage2-pemesan.md.
 */
class AvailabilityService
{
    /** Menit TTL hold pra-checkout. */
    public const HOLD_TTL_MINUTES = 15;

    /**
     * Status reservasi yang dianggap masih memblokir slot.
     * (Method, bukan const — PHP 8.1 tak mengizinkan Enum->value di ekspresi konstan.)
     *
     * @return string[]
     */
    public static function statusAktif(): array
    {
        return [
            StatusReservasi::Menunggu->value,
            StatusReservasi::Disetujui->value,
        ];
    }

    // ---------------------------------------------------------------------
    // Hold pra-checkout (Cache)
    // ---------------------------------------------------------------------

    public function holdKey(int $fasilitasId, int $tarifId, string $tanggalMulai, ?string $jamMulai): string
    {
        // Format sesuai spesifikasi: hold:{id_fasilitas}:{id_tarif_sewa}:{tanggal_mulai}:{jam_mulai}
        return sprintf('hold:%d:%d:%s:%s', $fasilitasId, $tarifId, $tanggalMulai, $jamMulai ?: 'full');
    }

    private function indexKey(int $fasilitasId): string
    {
        return 'hold_index:'.$fasilitasId;
    }

    /**
     * Pasang hold untuk satu item keranjang. Value = session id pemilik hold.
     * TTL 15 menit di-refresh tiap pemanggilan.
     */
    public function putHold(array $item, string $sessionId): void
    {
        $key = $this->holdKey($item['id_fasilitas'], $item['id_tarif_sewa'], $item['tanggal_mulai'], $item['jam_mulai'] ?? null);
        $ttl = now()->addMinutes(self::HOLD_TTL_MINUTES);

        Cache::put($key, $sessionId, $ttl);

        // Index per fasilitas supaya hold bisa ditelusuri saat cek bentrok (driver file tak bisa scan key).
        $indexKey = $this->indexKey($item['id_fasilitas']);
        $index = Cache::get($indexKey, []);
        $index[$key] = [
            'key'             => $key,
            'id_tarif_sewa'   => $item['id_tarif_sewa'],
            'tanggal_mulai'   => $item['tanggal_mulai'],
            'tanggal_selesai' => $item['tanggal_selesai'] ?? $item['tanggal_mulai'],
            'jam_mulai'       => $item['jam_mulai'] ?? null,
            'jam_selesai'     => $item['jam_selesai'] ?? null,
            'session_id'      => $sessionId,
        ];
        Cache::put($indexKey, $index, $ttl);
    }

    /** Lepas hold satu item (mis. item dihapus dari keranjang atau sudah jadi Reservasi). */
    public function releaseHold(array $item): void
    {
        $key = $this->holdKey($item['id_fasilitas'], $item['id_tarif_sewa'], $item['tanggal_mulai'], $item['jam_mulai'] ?? null);
        Cache::forget($key);

        $indexKey = $this->indexKey($item['id_fasilitas']);
        $index = Cache::get($indexKey, []);
        unset($index[$key]);
        if ($index) {
            Cache::put($indexKey, $index, now()->addMinutes(self::HOLD_TTL_MINUTES));
        } else {
            Cache::forget($indexKey);
        }
    }

    /**
     * Hold aktif untuk sebuah fasilitas. Entri yang key utamanya sudah expired dibersihkan.
     * $excludeSessionId → keluarkan hold milik session tsb (hold sendiri tidak memblokir diri).
     */
    public function activeHoldsFor(int $fasilitasId, ?string $excludeSessionId = null): array
    {
        $indexKey = $this->indexKey($fasilitasId);
        $index = Cache::get($indexKey, []);
        $active = [];
        $changed = false;

        foreach ($index as $key => $descriptor) {
            if (! Cache::has($key)) {
                unset($index[$key]);
                $changed = true;
                continue;
            }
            if ($excludeSessionId !== null && ($descriptor['session_id'] ?? null) === $excludeSessionId) {
                continue;
            }
            $active[] = $descriptor;
        }

        if ($changed) {
            $index
                ? Cache::put($indexKey, $index, now()->addMinutes(self::HOLD_TTL_MINUTES))
                : Cache::forget($indexKey);
        }

        return $active;
    }

    // ---------------------------------------------------------------------
    // Deteksi bentrok
    // ---------------------------------------------------------------------

    /** Ada Reservasi aktif yang bentrok dengan slot yang diminta? */
    public function hasReservationConflict(int $fasilitasId, array $slot, ?int $ignoreReservasiId = null): bool
    {
        $rows = Reservasi::query()
            ->whereHas('tarifSewa', fn ($q) => $q->where('id_fasilitas', $fasilitasId))
            ->whereIn('status_reservasi', self::statusAktif())
            ->whereDate('tanggal_mulai', '<=', $slot['tanggal_selesai'])
            ->whereDate('tanggal_selesai', '>=', $slot['tanggal_mulai'])
            ->when($ignoreReservasiId, fn ($q) => $q->where('id_reservasi', '!=', $ignoreReservasiId))
            ->get(['id_reservasi', 'tanggal_mulai', 'tanggal_selesai', 'jam_mulai', 'jam_selesai']);

        foreach ($rows as $r) {
            $existing = [
                'tanggal_mulai'   => $r->tanggal_mulai->toDateString(),
                'tanggal_selesai' => $r->tanggal_selesai->toDateString(),
                'jam_mulai'       => $r->jam_mulai,
                'jam_selesai'     => $r->jam_selesai,
            ];
            if ($this->slotsConflict($slot, $existing)) {
                return true;
            }
        }

        return false;
    }

    /** Ada hold aktif dari session lain yang bentrok dengan slot yang diminta? */
    public function hasHoldConflict(int $fasilitasId, array $slot, string $currentSessionId): bool
    {
        foreach ($this->activeHoldsFor($fasilitasId, $currentSessionId) as $hold) {
            if ($this->slotsConflict($slot, $hold)) {
                return true;
            }
        }

        return false;
    }

    /** Slot benar-benar bebas: tidak bentrok Reservasi aktif maupun hold session lain. */
    public function slotAvailable(int $fasilitasId, array $slot, string $currentSessionId, ?int $ignoreReservasiId = null): bool
    {
        return ! $this->hasReservationConflict($fasilitasId, $slot, $ignoreReservasiId)
            && ! $this->hasHoldConflict($fasilitasId, $slot, $currentSessionId);
    }

    // ---------------------------------------------------------------------
    // Indikator warna untuk denah
    // ---------------------------------------------------------------------

    /**
     * Status warna sebuah fasilitas untuk slot yang sedang dilihat:
     *   hijau  = seluruh rentang bebas
     *   merah  = seluruh rentang bentrok
     *   kuning = sebagian bebas, sebagian bentrok (khusus rentang harian/bulanan)
     */
    public function statusFasilitas(Fasilitas $fasilitas, array $slot, string $currentSessionId): string
    {
        $fasilitasId = $fasilitas->id_fasilitas;

        // Per Jam: satu slot waktu — hanya bebas atau bentrok.
        if (! empty($slot['jam_mulai'])) {
            return $this->slotAvailable($fasilitasId, $slot, $currentSessionId) ? 'hijau' : 'merah';
        }

        // Harian/Bulanan: evaluasi per hari agar bisa mendeteksi "kuning".
        $mulai = Carbon::parse($slot['tanggal_mulai'])->startOfDay();
        $selesai = Carbon::parse($slot['tanggal_selesai'])->startOfDay();

        $bebas = 0;
        $bentrok = 0;
        foreach (CarbonPeriod::create($mulai, $selesai) as $hari) {
            $tgl = $hari->toDateString();
            $daySlot = ['tanggal_mulai' => $tgl, 'tanggal_selesai' => $tgl, 'jam_mulai' => null, 'jam_selesai' => null];
            $this->slotAvailable($fasilitasId, $daySlot, $currentSessionId) ? $bebas++ : $bentrok++;
        }

        if ($bentrok === 0) {
            return 'hijau';
        }

        return $bebas === 0 ? 'merah' : 'kuning';
    }

    // ---------------------------------------------------------------------
    // Helper overlap
    // ---------------------------------------------------------------------

    /** Dua slot bentrok? Kalau salah satu memakai seluruh hari, cukup overlap tanggal. */
    private function slotsConflict(array $a, array $b): bool
    {
        if (! $this->datesOverlap($a['tanggal_mulai'], $a['tanggal_selesai'], $b['tanggal_mulai'], $b['tanggal_selesai'])) {
            return false;
        }

        $aHourly = ! empty($a['jam_mulai']);
        $bHourly = ! empty($b['jam_mulai']);

        // Keduanya per jam (pasti satu hari & tanggalnya sudah overlap) → cek overlap jam.
        if ($aHourly && $bHourly) {
            return $this->timesOverlap(
                $this->normTime($a['jam_mulai']), $this->normTime($a['jam_selesai']),
                $this->normTime($b['jam_mulai']), $this->normTime($b['jam_selesai']),
            );
        }

        // Minimal satu sisi memblokir seluruh hari → overlap tanggal saja sudah bentrok.
        return true;
    }

    private function datesOverlap(string $aStart, string $aEnd, string $bStart, string $bEnd): bool
    {
        return $aStart <= $bEnd && $aEnd >= $bStart;
    }

    private function timesOverlap(string $aStart, string $aEnd, string $bStart, string $bEnd): bool
    {
        return $aStart < $bEnd && $aEnd > $bStart;
    }

    private function normTime(string $time): string
    {
        // Samakan ke HH:MM:SS agar perbandingan string konsisten ('09:00' vs '09:00:00').
        return Carbon::parse($time)->format('H:i:s');
    }
}
