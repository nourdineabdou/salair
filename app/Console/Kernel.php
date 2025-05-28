<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\SalaireController;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {

        $schedule->call(function () {
            if(in_array(Carbon::now()->format('l'), ["Sunday" , "Saturday"])){
               return "Its wekkend";
            }else{
                $refrechBase =  new SalaireController();
                $refrechBase->save_comptes();
            }
            })->dailyAt("14:28");
        // $schedule->command('inspire')->hourly();
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
