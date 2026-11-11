<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disciplinas', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        // Inserir disciplinas de 1º ao 10º ano
        $disciplinas = [
            ['nome' => 'Português', 'descricao' => 'Disciplina da Língua Portuguesa'],
            ['nome' => 'Matemática', 'descricao' => 'Disciplina de Cálculos e Lógica'],
            ['nome' => 'Ciências', 'descricao' => 'Estudo da Natureza e Fenômenos Naturais'],
            ['nome' => 'História', 'descricao' => 'Estudo do Passado Humano'],
            ['nome' => 'Geografia', 'descricao' => 'Estudo da Terra e Espaços Geográficos'],
            ['nome' => 'Inglês', 'descricao' => 'Disciplina da Língua Inglesa'],
            ['nome' => 'Educação Física', 'descricao' => 'Atividades Físicas e Saúde'],
            ['nome' => 'Artes', 'descricao' => 'Expressões Artísticas e Cultura'],
            ['nome' => 'Informática', 'descricao' => 'Tecnologia e Computação'],
            ['nome' => 'Religião', 'descricao' => 'Estudo de Valores e Filosofia'],
        ];

        $currentTimestamp = now();

        foreach ($disciplinas as &$disciplina) {
            $disciplina['created_at'] = $currentTimestamp;
            $disciplina['updated_at'] = $currentTimestamp;
        }

        DB::table('disciplinas')->insert($disciplinas);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disciplinas');
    }
};
