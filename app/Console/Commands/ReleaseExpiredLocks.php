<?php

namespace App\Console\Commands;

use App\Enums\LockStatus;
use App\Models\Reservasi;
use Illuminate\Console\Command;

class ReleaseExpiredLocks extends Command
{
    /**
     * @var string
     */
    protected $signature = 'reservasi:release-expired-locks';

    /**
     * @var string
     */
    protected $description = 'Ubah lock_status Reservasi yang masih temporary_hold dan sudah lewat lock_expires_at menjadi released';

    public function handle(): int
    {
        $affected = Reservasi::query()
            ->where('lock_status', LockStatus::TemporaryHold->value)
            ->whereNotNull('lock_expires_at')
            ->where('lock_expires_at', '<=', now())
            ->update(['lock_status' => LockStatus::Released->value]);

        $this->info("Selesai. {$affected} reservasi temporary_hold yang expired diubah menjadi 'released'.");

        return self::SUCCESS;
    }
}
