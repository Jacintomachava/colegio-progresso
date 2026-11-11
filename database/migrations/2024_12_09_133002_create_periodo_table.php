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
        Schema::create('periodo', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        DB::table('periodo')->insert([
            ['nome' => 'Manhã', 'descricao'=>'Das 7:00h as 12:00h' , 'created_at' => '2022-03-20', 'updated_at' => '2022-03-20'],
            ['nome' => 'Tarde', 'descricao'=>'Das 12:30h as 17:00h', 'created_at' => '2022-03-20', 'updated_at' => '2022-03-20'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo');
    }
};
