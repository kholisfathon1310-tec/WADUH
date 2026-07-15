<?php

namespace App\Services;

use App\Enums\SatuanSewa;
use App\Models\TarifSewa;
use Illuminate\Support\Carbon;

/**
 * Keranjang reservasi pra-checkout — disimpan di Laravel session (BUKAN database),
 * sesuai db-spec-stage2-pemesan.md.
 */
class CartService
{
    private const SESSION_KEY = 'reservasi_cart';

    /** @return array<int, array> */
    public function items(): array
    {
        return array_values(session()->get(self::SESSION_KEY, []));
    }

    public function isEmpty(): bool
    {
        return $this->items() === [];
    }

    public function count(): int
    {
        return count($this->items());
    }

    public function total(): float
    {
        return (float) array_sum(array_column($this->items(), 'total_biaya'));
    }

    public function add(array $item): void
    {
        $items = $this->items();
        $items[] = $item;
        session()->put(self::SESSION_KEY, $items);
    }

    public function get(int $index): ?array
    {
        return $this->items()[$index] ?? null;
    }

    public function remove(int $index): ?array
    {
        $items = $this->items();
        if (! isset($items[$index])) {
            return null;
        }
        $removed = $items[$index];
        unset($items[$index]);
        session()->put(self::SESSION_KEY, array_values($items));

        return $removed;
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function hasBulan(): bool
    {
        foreach ($this->items() as $item) {
            if ($item['satuan'] === SatuanSewa::Bulan->value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Bangun satu item keranjang lengkap dari tarif + input jadwal yang sudah tervalidasi.
     * Menghitung tanggal_selesai (Jam), durasi, dan total_biaya sesuai satuan.
     *
     * @param  array  $data  Field jadwal tervalidasi (tanggal_mulai, tanggal_selesai, jam_mulai, jam_selesai, durasi, jumlah_pengguna, keperluan)
     */
    public function buildItem(TarifSewa $tarif, array $data): array
    {
        $tarif->loadMissing('fasilitas', 'jenisSewa');
        $satuan = $tarif->jenisSewa->satuan; // enum SatuanSewa
        $harga = (float) $tarif->harga;

        $tanggalMulai = $data['tanggal_mulai'];
        $jamMulai = null;
        $jamSelesai = null;

        switch ($satuan) {
            case SatuanSewa::Jam:
                $tanggalSelesai = $tanggalMulai; // per jam = satu hari
                $jamMulai = $data['jam_mulai'];
                $jamSelesai = $data['jam_selesai'];
                $durasi = $this->hitungJam($tanggalMulai, $jamMulai, $jamSelesai);
                break;

            case SatuanSewa::Hari:
                $tanggalSelesai = $data['tanggal_selesai'];
                $durasi = Carbon::parse($tanggalMulai)->diffInDays(Carbon::parse($tanggalSelesai)) + 1;
                break;

            case SatuanSewa::Bulan:
            default:
                $tanggalSelesai = $data['tanggal_selesai'];
                $durasi = max(1, Carbon::parse($tanggalMulai)->diffInMonths(Carbon::parse($tanggalSelesai)));
                break;
        }

        $total = $harga * $durasi;

        return [
            'id_fasilitas'    => $tarif->id_fasilitas,
            'id_tarif_sewa'   => $tarif->id_tarif_sewa,
            'id_jenis_sewa'   => $tarif->id_jenis_sewa,
            'satuan'          => $satuan->value,
            'nama_fasilitas'  => $tarif->fasilitas->nama_fasilitas,
            'kategori'        => $tarif->fasilitas->kategori_fasilitas,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'jam_mulai'       => $jamMulai,
            'jam_selesai'     => $jamSelesai,
            'durasi'          => (int) $durasi,
            'jumlah_pengguna' => (int) $data['jumlah_pengguna'],
            'keperluan'       => $data['keperluan'],
            'harga_satuan'    => $harga,
            'total_biaya'     => $total,
        ];
    }

    private function hitungJam(string $tanggal, string $jamMulai, string $jamSelesai): int
    {
        $mulai = Carbon::parse("$tanggal $jamMulai");
        $selesai = Carbon::parse("$tanggal $jamSelesai");

        // Dibulatkan ke atas ke jam penuh, minimal 1 jam.
        return max(1, (int) ceil($mulai->diffInMinutes($selesai) / 60));
    }
}
