<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ItemPedidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'produto_id'     => ['required', 'exists:produtos,id'],
            'quantidade'     => ['required', 'integer', 'min:1'],
            'valor_unitario' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'produto_id.required'     => 'O produto é obrigatório.',
            'produto_id.exists'       => 'Produto não encontrado.',
            'quantidade.required'     => 'A quantidade é obrigatória.',
            'quantidade.min'          => 'A quantidade deve ser ao menos 1.',
            'valor_unitario.required' => 'O valor unitário é obrigatório.',
            'valor_unitario.min'      => 'O valor deve ser maior que zero.',
        ];
    }
}