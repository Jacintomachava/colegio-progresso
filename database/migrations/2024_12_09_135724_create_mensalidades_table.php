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
        Schema::create('mensalidades', function (Blueprint $table) {
            $table->id();
            $table->decimal('valor')->default(0);
            $table->decimal('desconto')->default(0);
            $table->boolean('pagou')->default(false);
            $table->foreignId('ano_academico_id')->constrained('ano_academico')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('mes_id')->constrained('meses')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('taxa_id')->constrained('taxas')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('aluno_id')->constrained('alunos')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensalidades');
    }
};
