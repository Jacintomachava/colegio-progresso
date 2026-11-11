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
        Schema::create('presencas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ano_academico_id')->constrained('ano_academico')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presencas');
    }
};
