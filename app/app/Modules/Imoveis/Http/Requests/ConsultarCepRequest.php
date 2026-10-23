<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Shared\Http\Requests\RequisicaoBase;

final class ConsultarCepRequest extends RequisicaoBase
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cep' => ['required', 'regex:/^[0-9]{8}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cep.required' => 'Informe o CEP.',
            'cep.regex' => 'O CEP deve conter exatamente 8 dígitos numéricos.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $valor = $this->route('cep') ?? $this->query('cep') ?? $this->input('cep');
        $raw = is_string($valor) ? $valor : '';
        $limpo = preg_replace('/\D+/', '', $raw) ?? '';

        $this->merge(['cep' => $limpo]);
    }
}
