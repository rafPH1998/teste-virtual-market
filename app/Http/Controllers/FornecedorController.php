<?php

namespace App\Http\Controllers;

use App\Http\Requests\FornecedorRequest;
use App\Models\Fornecedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FornecedorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Fornecedor::query();

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $fornecedores = $query->orderBy('nome')->paginate($request->get('per_page', 15));

        return response()->json($fornecedores);
    }

    public function store(FornecedorRequest $request): JsonResponse
    {
        $fornecedor = Fornecedor::create($request->validated());

        return response()->json([
            'message'     => 'Fornecedor cadastrado com sucesso.',
            'fornecedor'  => $fornecedor,
        ], 201);
    }

    public function show(Fornecedor $fornecedor): JsonResponse
    {
        return response()->json($fornecedor->load('produtos'));
    }

    public function update(FornecedorRequest $request, Fornecedor $fornecedor): JsonResponse
    {
        $fornecedor->update($request->validated());

        return response()->json([
            'message'    => 'Fornecedor atualizado com sucesso.',
            'fornecedor' => $fornecedor,
        ]);
    }

    public function destroy(Fornecedor $fornecedor): JsonResponse
    {
        if ($fornecedor->pedidos()->whereNotIn('status', ['cancelado'])->exists()) {
            return response()->json([
                'message' => 'Não é possível excluir um fornecedor com pedidos ativos.',
            ], 422);
        }

        $fornecedor->delete();

        return response()->json(['message' => 'Fornecedor excluído com sucesso.']);
    }

    // Lista todos os fornecedores sem paginação (para selects)
    public function all(): JsonResponse
    {
        $fornecedores = Fornecedor::ativo()->orderBy('nome')->get(['id', 'nome', 'cnpj', 'status', 'created_at']);

        return response()->json($fornecedores);
    }
}