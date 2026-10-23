<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Requests;

use App\Modules\Leads\Domain\Enums\OrigemLead;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class CriarLeadRequest extends RequisicaoBase
{
    use NormalizaCamposDoLead;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nome' => $this->textoLimitado($this->input('nome')),
            'telefone' => $this->textoLimitado($this->input('telefone')),
            'email' => $this->textoOpcionalLimitado($this->input('email')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:150'],
            'telefone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:180'],
            'imovelDeInteresse' => ['nullable', 'string', 'max:255'],
            'origem' => ['nullable', Rule::enum(OrigemLead::class)],
            'observacoes' => ['nullable', 'string', 'max:200'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome completo é obrigatório.',
            'telefone.required' => 'O telefone é obrigatório.',
            'email.email' => 'E-mail inválido.',
            'observacoes.max' => 'Observação deve ter no máximo 200 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nome' => 'nome',
            'telefone' => 'telefone',
            'email' => 'e-mail',
            'imovelDeInteresse' => 'imóvel de interesse',
            'origem' => 'origem',
            'observacoes' => 'observações',
        ];
    }
}
