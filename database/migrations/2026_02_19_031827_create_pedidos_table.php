<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fornecedor_id')->constrained('fornecedores')->onDelete('restrict');
            $table->date('data_pedido');
            $table->enum('status', ['aberto', 'processando', 'concluido', 'cancelado'])->default('aberto');
            $table->text('observacoes')->nullable();
            $table->decimal('valor_total', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('fornecedor_id');
            $table->index('data_pedido');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};