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
        Schema::create('provincias', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('codigo')->unique();
            $table->timestamps();
        });

        // Comandos INSERT INTO para adicionar registros à tabela
        DB::table('provincias')->insert([
            ['created_at' => '2024-03-20', 'updated_at' => '2024-03-20', 'codigo' => 'MP', 'nome' => 'Maputo'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'GZ', 'nome' => 'Gaza'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'IB', 'nome' => 'Inhambane'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'SF', 'nome' => 'Sofala'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'MI', 'nome' => 'Manica'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'ZB', 'nome' => 'Zambezia'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'TE', 'nome' => 'Tete'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'NP', 'nome' => 'Nampula'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'CD', 'nome' => 'Cabo Delgado'],
            ['created_at' => '2024-02-20', 'updated_at' => '2024-02-20', 'codigo' => 'NS', 'nome' => 'Niassa'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provincias');
    }
};
