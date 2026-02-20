<?php

namespace App\Http\Controllers;

use App\Http\Requests\ItemPedidoRequest;
use App\Http\Requests\PedidoRequest;
use App\Models\HistoricoStatusPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PedidoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Pedido::with('fornecedor')->withCount('itens');

        if ($status = $request->get('status')) {
            $query->porStatus($status);
        }

        if ($fornecedorId = $request->get('fornecedor_id')) {
            $query->porFornecedor($fornecedorId);
        }

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        $pedidos = $query->orderByDesc('created_at')->paginate($request->get('per_page', 15));

        return response()->json($pedidos);
    }

    public function store(PedidoRequest $request): JsonResponse
    {
        $fornecedor = \App\Models\Fornecedor::findOrFail($request->fornecedor_id);

        if ($fornecedor->status === 'inativo') {
            return response()->json([
                'message' => 'Não é possível criar pedido para um fornecedor inativo.',
            ], 422);
        }

        $pedido = Pedido::create($request->validated());

        // Registra histórico
        HistoricoStatusPedido::create([
            'pedido_id'       => $pedido->id,
            'status_anterior' => null,
            'status_novo'     => $pedido->status,
            'observacao'      => 'Pedido criado.',
        ]);

        return response()->json([
            'message' => 'Pedido criado com sucesso.',
            'pedido'  => $pedido->load('fornecedor'),
        ], 201);
    }

    public function show(Pedido $pedido): JsonResponse
    {
        $pedido->load(['fornecedor', 'itens.produto', 'historicoStatus']);

        return response()->json($pedido);
    }

    public function update(PedidoRequest $request, Pedido $pedido): JsonResponse
    {
        if ($pedido->isBloqueado()) {
            return response()->json([
                'message' => 'Pedidos concluídos ou cancelados não podem ser editados.',
            ], 422);
        }

        $statusAnterior = $pedido->status;
        $pedido->update($request->validated());

        // Histórico de mudança de status
        if ($statusAnterior !== $pedido->status) {
            HistoricoStatusPedido::create([
                'pedido_id'       => $pedido->id,
                'status_anterior' => $statusAnterior,
                'status_novo'     => $pedido->status,
                'observacao'      => $request->get('observacao_status'),
            ]);
        }

        return response()->json([
            'message' => 'Pedido atualizado com sucesso.',
            'pedido'  => $pedido->load('fornecedor'),
        ]);
    }

    public function destroy(Pedido $pedido): JsonResponse
    {
        if ($pedido->status === Pedido::STATUS_CONCLUIDO) {
            return response()->json([
                'message' => 'Pedidos concluídos não podem ser excluídos.',
            ], 422);
        }

        $pedido->delete();

        return response()->json(['message' => 'Pedido excluído com sucesso.']);
    }

    // ─── Itens do Pedido ──────────────────────────────────────────────────────

    public function adicionarItem(ItemPedidoRequest $request, Pedido $pedido): JsonResponse
    {
        if ($pedido->isBloqueado()) {
            return response()->json([
                'message' => 'Não é possível adicionar itens a pedidos concluídos ou cancelados.',
            ], 422);
        }

        $produto = Produto::findOrFail($request->produto_id);

        // Produto deve estar ativo
        if ($produto->status !== 'ativo') {
            return response()->json([
                'message' => 'O produto selecionado não está ativo.',
            ], 422);
        }

        // Produto deve estar vinculado ao fornecedor do pedido
        $vinculado = $pedido->fornecedor->produtos()
            ->where('produto_id', $produto->id)
            ->exists();

        if (!$vinculado) {
            return response()->json([
                'message' => 'Este produto não está vinculado ao fornecedor do pedido.',
            ], 422);
        }

        $item = ItemPedido::create([
            'pedido_id'      => $pedido->id,
            'produto_id'     => $request->produto_id,
            'quantidade'     => $request->quantidade,
            'valor_unitario' => $request->valor_unitario,
            'valor_total'    => $request->quantidade * $request->valor_unitario,
        ]);

        return response()->json([
            'message' => 'Item adicionado com sucesso.',
            'item'    => $item->load('produto'),
            'pedido'  => $pedido->fresh(),
        ], 201);
    }

    public function removerItem(Pedido $pedido, ItemPedido $item): JsonResponse
    {
        if ($pedido->isBloqueado()) {
            return response()->json([
                'message' => 'Não é possível remover itens de pedidos concluídos ou cancelados.',
            ], 422);
        }

        if ($item->pedido_id !== $pedido->id) {
            return response()->json(['message' => 'Item não pertence a este pedido.'], 422);
        }

        $item->delete();

        return response()->json([
            'message' => 'Item removido com sucesso.',
            'pedido'  => $pedido->fresh(),
        ]);
    }

    // Produtos disponíveis para o fornecedor do pedido
    public function produtosDisponiveis(Pedido $pedido): JsonResponse
    {
        $produtos = $pedido->fornecedor->produtos()
            ->where('produtos.status', 'ativo')
            ->get(['produtos.id', 'produtos.nome', 'produtos.codigo_interno']);

        return response()->json($produtos);
    }
}