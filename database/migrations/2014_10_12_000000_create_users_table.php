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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->nullable()->unique();
            $table->string('telefone')->nullable()->unique();
            $table->string('codigo')->nullable()->unique();  //Codigo Usado Para Autenticar gerado com Sistema
            $table->string('password');
            $table->boolean('estado')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->foreignId('pessoa_id')->constrained('pessoas');  //Pessoa dono de documento
            $table->rememberToken();
            $table->timestamps();
        });

        // Comandos INSERT INTO para adicionar registros à tabela
        DB::table('users')->insert([
            ['pessoa_id' => 1, 'nome' => 'Jacinto Machava', 'email' => 'jacintomachava@gmail.com', 'telefone' => '844870386', 'password' => '$2y$10$9JUWblvH1RlIeDuScWF5He/IPWKt5Yt5lhgqqrOOCsz302PHzdQRG', 'created_at' => '2022-03-20', 'updated_at' => '2022-03-20'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
