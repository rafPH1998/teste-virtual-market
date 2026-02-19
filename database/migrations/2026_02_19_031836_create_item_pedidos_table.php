<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('itens_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('produto_id')->constrained('produtos')->onDelete('restrict');
            $table->integer('quantidade')->unsigned();
            $table->decimal('valor_unitario', 10, 2);
            $table->decimal('valor_total', 12, 2);
            $table->timestamps();

            $table->index('pedido_id');
            $table->index('produto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('itens_pedido');
    }
};