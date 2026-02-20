<?php

use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

// ─── API ──────────────────────────────────────────────────────────────────────
Route::prefix('api')->group(function () {

    // Fornecedores
    Route::get('fornecedores/all', [FornecedorController::class, 'all']);
    Route::apiResource('fornecedores', FornecedorController::class);

    // Produtos
    Route::get('produtos/all', [ProdutoController::class, 'all']);
    Route::apiResource('produtos', ProdutoController::class);

    // Vínculos produto ↔ fornecedor
    Route::get('produtos/{produto}/fornecedores', [ProdutoController::class, 'fornecedores']);
    Route::post('produtos/{produto}/vincular', [ProdutoController::class, 'vincular']);
    Route::delete('produtos/{produto}/fornecedores/{fornecedor}', [ProdutoController::class, 'desvincular']);
    Route::post('produtos/{produto}/vincular-massa', [ProdutoController::class, 'vincularEmMassa']);
    Route::post('produtos/{produto}/desvincular-massa', [ProdutoController::class, 'desvincularEmMassa']);

    // Pedidos
    Route::apiResource('pedidos', PedidoController::class);
    Route::get('pedidos/{pedido}/produtos-disponiveis', [PedidoController::class, 'produtosDisponiveis']);
    Route::post('pedidos/{pedido}/itens', [PedidoController::class, 'adicionarItem']);
    Route::delete('pedidos/{pedido}/itens/{item}', [PedidoController::class, 'removerItem']);
});

// ─── SPA — deve ficar SEMPRE por último ───────────────────────────────────────
Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');