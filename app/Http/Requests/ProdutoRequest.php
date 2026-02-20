<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProdutoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $produtoId = $this->route('produto')?->id;

        return [
            'nome'           => ['required', 'string', 'max:255'],
            'descricao'      => ['nullable', 'string'],
            'codigo_interno' => ['required', 'string', 'max:100', Rule::unique('produtos', 'codigo_interno')->ignore($produtoId)->whereNull('deleted_at')],
            'status'         => ['required', Rule::in(['ativo', 'inativo'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'           => 'O nome é obrigatório.',
            'codigo_interno.required' => 'O código interno é obrigatório.',
            'codigo_interno.unique'   => 'Este código interno já está em uso.',
            'status.required'         => 'O status é obrigatório.',
        ];
    }
}