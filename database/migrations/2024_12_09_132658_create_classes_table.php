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
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->integer('numero')->unique();
            $table->timestamps();
        });

        // Comandos INSERT INTO das Classes
         // Inserir as 12 classes na tabela
         $classes = [];
         $currentTimestamp = now(); // Pega o timestamp atual
 
         for ($i = 1; $i <= 12; $i++) {
             $classes[] = [
                 'nome' => "{$i}ª Classe",
                 'numero' => $i,
                 'created_at' => $currentTimestamp,
                 'updated_at' => $currentTimestamp,
             ];
         }
 
         // Inserir os dados na tabela
         DB::table('classes')->insert($classes);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
