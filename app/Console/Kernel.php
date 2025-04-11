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
        // Executa o comando de alertas de vencimento de faturas todos os dias às 8:00
        $schedule->command('app:send-invoice-due-reminders')
                ->dailyAt('08:00')
                ->withoutOverlapping()
                ->emailOutputOnFailure(config('mail.from.address'));
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