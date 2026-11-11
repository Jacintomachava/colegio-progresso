<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ano_academico', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->integer('ano')->unique();
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        // Inserir os Ano Academico
        $dados = [
            [
                'id' => 1,
                'nome' => 'Ano 2024',
                'ano' => 2024,
                'descricao' => 'Por um ensino livro',
                'created_at' => '2024-04-03 15:42:01',
                'updated_at' => '2024-04-03 15:42:01',
            ]
        ];

        DB::table('ano_academico')->insert($dados);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ano_academico');
    }
};
