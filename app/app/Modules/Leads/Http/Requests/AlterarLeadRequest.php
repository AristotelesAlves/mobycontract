<?php

declare(strict_types=1);

namespace App\Modules\Leads\Http\Requests;

use App\Modules\Leads\Domain\Enums\OrigemLead;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class AlterarLeadRequest extends RequisicaoBase
{
    use NormalizaCamposDoLead;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $normalizados = [];

        foreach (['nome', 'telefone'] as $campo) {
            if ($this->has($campo)) {
                $normalizados[$campo] = $this->textoLimitado($this->input($campo));
            }
        }

        if ($this->has('email')) {
            $normalizados['email'] = $this->textoOpcionalLimitado($this->input('email'));
        }

        $this->merge($normalizados);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['sometimes', 'required', 'string', 'max:150'],
            'telefone' => ['sometimes', 'required', 'string', 'max:30'],
            'email' => ['sometimes', 'nullable', 'email', 'max:180'],
            'imovelDeInteresse' => ['sometimes', 'nullable', 'string', 'max:255'],
            'origem' => ['sometimes', 'nullable', Rule::enum(OrigemLead::class)],
            'observacoes' => ['sometimes', 'nullable', 'string', 'max:200'],
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
