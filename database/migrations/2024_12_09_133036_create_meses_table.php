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
        Schema::create('meses', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->integer('numero')->unique();
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->timestamps();
        });

        // Dados para inserção inicial
        $dados = [
            ['id' => 1, 'nome' => 'Fevereiro', 'numero' => 2, 'data_inicio' => '2024-01-25', 'data_fim' => '2024-02-05', 'created_at' => '2024-03-22 13:51:48', 'updated_at' => '2024-03-22 13:51:48'],
            ['id' => 2, 'nome' => 'Março', 'numero' => 3, 'data_inicio' => '2024-02-25', 'data_fim' => '2024-03-05', 'created_at' => '2024-03-22 13:51:48', 'updated_at' => '2024-03-22 13:51:48'],
            ['id' => 3, 'nome' => 'Abril', 'numero' => 4, 'data_inicio' => '2024-03-25', 'data_fim' => '2024-04-05', 'created_at' => '2024-03-22 13:52:42', 'updated_at' => '2024-03-22 13:52:42'],
            ['id' => 4, 'nome' => 'Maio', 'numero' => 5, 'data_inicio' => '2024-04-25', 'data_fim' => '2024-05-05', 'created_at' => '2024-04-08 12:53:41', 'updated_at' => '2024-04-08 12:53:41'],
            ['id' => 5, 'nome' => 'Junho', 'numero' => 6, 'data_inicio' => '2024-05-25', 'data_fim' => '2024-06-05', 'created_at' => '2024-04-08 12:53:41', 'updated_at' => '2024-04-08 12:53:41'],
            ['id' => 6, 'nome' => 'Julho', 'numero' => 7, 'data_inicio' => '2024-06-25', 'data_fim' => '2024-07-05', 'created_at' => '2024-04-24 21:28:51', 'updated_at' => '2024-04-24 21:28:51'],
            ['id' => 7, 'nome' => 'Agosto', 'numero' => 8, 'data_inicio' => '2024-07-25', 'data_fim' => '2024-08-05', 'created_at' => '2024-04-24 21:29:30', 'updated_at' => '2024-04-24 21:29:30'],
            ['id' => 8, 'nome' => 'Setembro', 'numero' => 9, 'data_inicio' => '2024-08-25', 'data_fim' => '2024-09-05', 'created_at' => '2024-04-24 21:29:30', 'updated_at' => '2024-04-24 21:29:30'],
            ['id' => 9, 'nome' => 'Outubro', 'numero' => 10, 'data_inicio' => '2024-09-25', 'data_fim' => '2024-10-05', 'created_at' => '2024-04-24 21:30:52', 'updated_at' => '2024-04-24 21:30:52'],
            ['id' => 10, 'nome' => 'Novembro', 'numero' => 11, 'data_inicio' => '2024-10-25', 'data_fim' => '2024-11-05', 'created_at' => '2024-04-24 21:32:32', 'updated_at' => '2024-04-24 21:32:32'],
        ];

        DB::table('meses')->insert($dados);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meses');
    }
};
