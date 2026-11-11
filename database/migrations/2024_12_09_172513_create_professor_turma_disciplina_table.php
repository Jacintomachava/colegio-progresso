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
        Schema::create('professor_turma_disciplina', function (Blueprint $table) {
            $table->id();
            $table->boolean('activo')->default(true);  //Sim ou Nao
            $table->foreignId('turma_id')->constrained('turmas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('ano_academico_id')->constrained('ano_academico')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('disciplina_id')->constrained('disciplinas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('professor_id')->constrained('professor')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('professor_turma_disciplina');
    }
};
