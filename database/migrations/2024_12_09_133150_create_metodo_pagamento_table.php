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
        Schema::create('metodo_pagamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('descricao')->nullable();
            $table->string('codigo')->unique();
            $table->timestamps();
        });

        // Inserir os métodos de pagamento
        $dados = [
            [
                'id' => 1,
                'nome' => 'Cash',
                'descricao' => 'Pronto Pagamento',
                'codigo' => 'dinheiro',
                'created_at' => '2024-04-03 15:42:01',
                'updated_at' => '2024-04-03 15:42:01',
            ],
            [
                'id' => 2,
                'nome' => 'Mpesa',
                'descricao' => 'Pagamento Via Carteira Móvel',
                'codigo' => 'mpesa',
                'created_at' => '2024-04-03 15:42:01',
                'updated_at' => '2024-04-03 15:42:01',
            ],
            [
                'id' => 3,
                'nome' => 'ER',
                'descricao' => 'Entidade Referencia',
                'codigo' => 'bim',
                'created_at' => '2024-04-03 15:42:01',
                'updated_at' => '2024-04-03 15:42:01',
            ]
        ];

        DB::table('metodo_pagamento')->insert($dados);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metodo_pagamento');
    }
};
