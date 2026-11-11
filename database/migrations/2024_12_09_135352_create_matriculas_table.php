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
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->decimal('valor')->default(0);
            $table->decimal('desconto')->default(0);
            $table->string('entidade')->nullable();
            $table->string('referencia')->unique();
            $table->boolean('pagou')->default(false);  //Sim ou Nao
            $table->foreignId('periodo_id')->constrained('periodo')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('ano_academico_id')->constrained('ano_academico')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('classe_id')->constrained('classes')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
