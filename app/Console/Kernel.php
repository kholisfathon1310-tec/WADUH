<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Bebaskan temporary_hold yang sudah kadaluwarsa agar tidak menahan ketersediaan fasilitas.
        $schedule->command('reservasi:release-expired-locks')
            ->everyMinute()
            ->withoutOverlapping();

        // Disetujui -> Selesai & Menunggu -> Kadaluwarsa otomatis begitu waktu penggunaan lewat.
        $schedule->command('reservasi:perbarui-status-otomatis')
            ->everyMinute()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
