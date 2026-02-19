<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historico_status_pedido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('status_anterior')->nullable();
            $table->string('status_novo');
            $table->text('observacao')->nullable();
            $table->timestamps();

            $table->index('pedido_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historico_status_pedido');
    }
};