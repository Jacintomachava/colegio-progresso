<?php

namespace App\Console\Commands;

use App\Services\PdfReaderService;
use App\Services\PineconeService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

// Extrairconteudo dos documentos para Base de dados Pinecone
class ImportarDocumentosPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:importar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importar documentos PDF para o Pinecone';

    protected $pdfReader;
    protected $pinecone;

    public function __construct(PdfReaderService $pdfReader, PineconeService $pinecone)
    {
        parent::__construct();
        $this->pdfReader = $pdfReader;
        $this->pinecone = $pinecone;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = \File::files(storage_path('app/docs'));

        foreach ($files as $file) {
            $this->info('Importando: '.$file->getFilename());

            $text = $this->pdfReader->extractText($file->getPathname());

            // Quebrar texto em pedaços de 1500 caracteres (ajuste se quiser)
            $chunks = str_split($text, 1500);

            foreach ($chunks as $i => $chunk) {
                $id = Str::slug($file->getFilename()).'_part_'.$i;
                $this->pinecone->upsert($id, $chunk);
                $this->info(" - Enviado chunk $i para Pinecone com id $id");
            }
        }

        $this->info('Importação concluída. com sucesso');

        return 0;
    }
    // Para Correr  php artisan pdf:importar    //Funciona
    // Para Correr  app(\App\Console\Commands\ImportarDocumentosPdf::class)->handle();
}
