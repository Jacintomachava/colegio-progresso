<?php

namespace App\Console\Commands;

use App\Services\EmailService;
use App\Services\SMSService;
use Illuminate\Console\Command;

class EnvioSMS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    
    protected $signature = 'app:envio-s-m-s';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // SMSService::notificacaoSMS();
        // EmailService::notificacaoEmail();
    }
}
