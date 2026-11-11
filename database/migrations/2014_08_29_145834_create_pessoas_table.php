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
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('telefone')->unique();
            $table->string('email')->nullable()->unique();
            $table->date('data_nascimento')->nullable();
            $table->string('avatar')->default('avatar.png');
            $table->enum('nivel_academico',[null,'Primáro', 'Secundário', 'Médio', 'Bacharel', 'Licenciatura', 'Mestre', 'Doutorado'])->default(null);
            $table->enum('genero', [null,'Masculino','Femenino'])->default(null);
            $table->enum('tipo_documento', [null,'B.I','Cedula','Passaporte', 'Carta de Condução'])->default(null);
            $table->string('numero_documento')->nullable()->unique();
            $table->string('local_emissao_documento')->nullable();
            $table->date('data_emissao_documento')->nullable();
            $table->string('endereco')->nullable();
            $table->string('naturalidade')->nullable();
            $table->string('nuit')->nullable()->unique();
            $table->string('pai')->nullable();
            $table->string('mae')->nullable();
            $table->string('profissao')->nullable();
            $table->string('local_trabalho')->nullable();
            $table->timestamps();
        });

        // Comandos INSERT INTO para adicionar registros à tabela
        DB::table('pessoas')->insert([
            ['nome' => 'Jacinto Machava', 'email' => 'jacintomachava@gmail.com', 'telefone' => '844870386', 'created_at' => '2022-03-20', 'updated_at' => '2022-03-20'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
