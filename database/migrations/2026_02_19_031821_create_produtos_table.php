<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->text('descricao')->nullable();
            $table->string('codigo_interno')->unique();
            $table->enum('status', ['ativo', 'inativo'])->default('ativo');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('codigo_interno');
            $table->index('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};