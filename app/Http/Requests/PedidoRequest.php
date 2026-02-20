<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fornecedor_id' => ['required', 'exists:fornecedores,id'],
            'data_pedido'   => ['required', 'date'],
            'status'        => ['required', Rule::in(['aberto', 'processando', 'concluido', 'cancelado'])],
            'observacoes'   => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'fornecedor_id.required' => 'O fornecedor é obrigatório.',
            'fornecedor_id.exists'   => 'Fornecedor não encontrado.',
            'data_pedido.required'   => 'A data do pedido é obrigatória.',
            'data_pedido.date'       => 'Data inválida.',
            'status.required'        => 'O status é obrigatório.',
            'status.in'              => 'Status inválido.',
        ];
    }
}