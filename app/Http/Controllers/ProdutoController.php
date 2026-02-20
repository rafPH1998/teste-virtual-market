<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProdutoRequest;
use App\Jobs\DesvincularFornecedoresEmMassa;
use App\Jobs\VincularFornecedoresEmMassa;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Produto::query();

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $produtos = $query->orderBy('nome')->paginate($request->get('per_page', 15));

        return response()->json($produtos);
    }

    public function store(ProdutoRequest $request): JsonResponse
    {
        $produto = Produto::create($request->validated());

        return response()->json([
            'message' => 'Produto cadastrado com sucesso.',
            'produto' => $produto,
        ], 201);
    }

    public function show(Produto $produto): JsonResponse
    {
        return response()->json($produto->load('fornecedores'));
    }

    public function update(ProdutoRequest $request, Produto $produto): JsonResponse
    {
        $produto->update($request->validated());

        return response()->json([
            'message' => 'Produto atualizado com sucesso.',
            'produto' => $produto,
        ]);
    }

    public function destroy(Produto $produto): JsonResponse
    {
        $produto->delete();

        return response()->json(['message' => 'Produto excluído com sucesso.']);
    }

    // Lista sem paginação para selects
    public function all(): JsonResponse
    {
        $produtos = Produto::ativo()->orderBy('nome')->get(['id', 'nome', 'codigo_interno']);

        return response()->json($produtos);
    }

    // ─── Vínculos ─────────────────────────────────────────────────────────────

    public function fornecedores(Produto $produto): JsonResponse
    {
        $vinculados = $produto->fornecedores()->get();

        return response()->json($vinculados);
    }

    public function vincular(Request $request, Produto $produto): JsonResponse
    {
        $request->validate([
            'fornecedor_id' => ['required', 'exists:fornecedores,id'],
        ]);

        $produto->fornecedores()->syncWithoutDetaching([$request->fornecedor_id]);

        return response()->json(['message' => 'Vínculo criado com sucesso.']);
    }

    public function desvincular(Produto $produto, int $fornecedorId): JsonResponse
    {
        $produto->fornecedores()->detach($fornecedorId);

        return response()->json(['message' => 'Vínculo removido com sucesso.']);
    }

    public function vincularEmMassa(Request $request, Produto $produto): JsonResponse
    {
        $request->validate([
            'fornecedor_ids'   => ['required', 'array', 'min:1'],
            'fornecedor_ids.*' => ['exists:fornecedores,id'],
        ]);

        VincularFornecedoresEmMassa::dispatch($produto->id, $request->fornecedor_ids);

        return response()->json([
            'message' => 'Operação de vínculo em massa enviada para processamento.',
        ]);
    }

    public function desvincularEmMassa(Request $request, Produto $produto): JsonResponse
    {
        $request->validate([
            'fornecedor_ids'   => ['required', 'array', 'min:1'],
            'fornecedor_ids.*' => ['exists:fornecedores,id'],
        ]);

        DesvincularFornecedoresEmMassa::dispatch($produto->id, $request->fornecedor_ids);

        return response()->json([
            'message' => 'Operação de desvínculo em massa enviada para processamento.',
        ]);
    }
}