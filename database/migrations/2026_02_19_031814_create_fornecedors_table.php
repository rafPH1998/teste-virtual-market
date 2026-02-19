<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('cnpj', 18)->unique();
            $table->string('email');
            $table->string('telefone', 20);
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fornecedores');
    }
};