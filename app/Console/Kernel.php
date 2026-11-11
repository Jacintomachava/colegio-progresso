<?php

namespace App\Console;

use App\Jobs\AtualizarMultasJob;
use App\Jobs\GerarMensalidadeDezembroJob;
use App\Jobs\EnviarSMS;
use App\Jobs\CurriculoPautaJob;
use App\Jobs\EnviarReferenciaJob;
use App\Jobs\NotificarPagamentos;
use App\Jobs\LimparPautasInvalidasJob;
use App\Jobs\RenovacaoMatriculaJob;
use App\Jobs\GerarMensalidadesJob;
use App\Jobs\CancelarMulta;
use App\Jobs\GerarSmsNotasAlunos;
use App\Jobs\NotificarEncarregadosMensalidade;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Multa 25% no dia 5 para que ja passou um mes multa de 100%
        $schedule->job(new AtualizarMultasJob())->daily()->withoutOverlapping();

        $schedule->job(new EnviarReferenciaJob())->monthlyOn(20, '07:00')->withoutOverlapping();

        $schedule->job(new EnviarSMS())->everyMinute()->withoutOverlapping();

        $schedule->job(new NotificarPagamentos())->everyMinute()->withoutOverlapping(); 

        $schedule->job(new CancelarMulta())->monthlyOn(6, '13:40')->withoutOverlapping();

        // GERAR PAUTA
        $schedule->job(new CurriculoPautaJob())->everyTenMinutes()->withoutOverlapping();

        // GERAR RENOVACOES
        $schedule->job(new RenovacaoMatriculaJob())->everyTenMinutes()->withoutOverlapping();

        // GERAR MENSALIDADES
        $schedule->job(new GerarMensalidadesJob())->everyTenMinutes()->withoutOverlapping();

        // GERAR MENSALIDADES DEZEMBRO
        $schedule->job(new GerarMensalidadeDezembroJob())->everyMinute()->withoutOverlapping();

        $schedule->job(new LimparPautasInvalidasJob())->everyMinute()->withoutOverlapping();

        //Envio de SMS para usar Sistema
        //$schedule->job(new GerarSmsNotasAlunos())->everyMinute()->withoutOverlapping();

        
        // Multa 50% no dia 20
        // $schedule->job(new AtualizarMultasJob)->everyMinute(); //->monthlyOn(15, '00:00');

        // Registar na Tabela Mensagem As mensagem a enviar
        // $schedule->job(new NotificarEncarregadosMensalidade)->everyMinute();  //->monthlyOn(14, '14:14');

        // Enviar Mensagem SMS para contactos das 8:00h as 17:00h
        // ->monthlyOn(14, '14:14');
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
