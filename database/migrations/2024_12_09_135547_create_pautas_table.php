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
        Schema::create('pautas', function (Blueprint $table) {
            $table->id();
            $table->decimal('teste1Tr1')->default(0);
            $table->decimal('teste2Tr1')->default(0);
            $table->decimal('apTr1')->default(0);
            $table->decimal('teste1Tr2')->default(0);
            $table->decimal('teste2Tr2')->default(0);
            $table->decimal('apTr2')->default(0);
            $table->decimal('teste1Tr3')->default(0);
            $table->decimal('teste2Tr3')->default(0);
            $table->decimal('apTr3')->default(0);
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
        Schema::dropIfExists('pautas');
    }
};
