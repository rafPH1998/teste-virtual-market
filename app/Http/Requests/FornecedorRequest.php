<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FornecedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $fornecedorId = $this->route('fornecedor')?->id;

        return [
            'nome'     => ['required', 'string', 'max:255'],
            'cnpj'     => ['required', 'string', 'max:18'],
            'email'    => ['required', 'email', 'max:255'],
            'telefone' => ['required', 'string', 'max:20'],
            'status'   => ['required', Rule::in(['ativo', 'inativo'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nome.required'     => 'O nome é obrigatório.',
            'cnpj.required'     => 'O CNPJ é obrigatório.',
            'email.required'    => 'O e-mail é obrigatório.',
            'email.email'       => 'Informe um e-mail válido.',
            'telefone.required' => 'O telefone é obrigatório.',
            'status.required'   => 'O status é obrigatório.',
            'status.in'         => 'Status inválido.',
        ];
    }
}