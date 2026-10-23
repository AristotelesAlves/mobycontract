<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\Http\Requests;

use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Shared\Http\Requests\RequisicaoBase;
use Illuminate\Validation\Rule;

final class CriarImovelRequest extends RequisicaoBase
{
    use NormalizaCamposDoImovel;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'titulo' => $this->textoLimitado($this->input('titulo')),
            'cep' => $this->somenteDigitos($this->input('cep')),
            'logradouro' => $this->textoLimitado($this->input('logradouro')),
            'numero' => $this->textoLimitado($this->input('numero')),
            'bairro' => $this->textoLimitado($this->input('bairro')),
            'cidade' => $this->textoLimitado($this->input('cidade')),
            'estado' => $this->estadoNormalizado($this->input('estado')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'titulo' => ['required', 'string', 'min:3', 'max:150'],
            'nomeProprietario' => ['nullable', 'string', 'max:150'],
            'tipo' => ['required', Rule::enum(TipoImovel::class)],
            'cep' => ['required', 'digits:8'],
            'logradouro' => ['required', 'string', 'max:180'],
            'numero' => ['required', 'string', 'max:20'],
            'complemento' => ['nullable', 'string', 'max:120'],
            'bairro' => ['nullable', 'string', 'max:120'],
            'cidade' => ['required', 'string', 'max:120'],
            'estado' => ['nullable', 'string', 'size:2'],
            'area' => ['required', 'numeric', 'gt:0'],
            'quartos' => ['required', 'integer', 'min:0'],
            'banheiros' => ['required', 'integer', 'min:0'],
            'vagasGaragem' => ['required', 'integer', 'min:0'],
            'valorAluguel' => ['required', 'numeric', 'gt:0'],
            'valorIptu' => ['nullable', 'numeric', 'min:0'],
            'valorCondominio' => ['nullable', 'numeric', 'min:0'],
            'aguaInclusa' => ['nullable', 'boolean'],
            'energiaInclusa' => ['nullable', 'boolean'],
            'internetInclusa' => ['nullable', 'boolean'],
            'gasIncluso' => ['nullable', 'boolean'],
            'matricula' => ['nullable', 'string', 'max:120'],
            'descricao' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'O título é obrigatório.',
            'tipo.required' => 'Selecione o tipo.',
            'cep.required' => 'CEP é obrigatório.',
            'cep.digits' => 'CEP deve ter 8 dígitos.',
            'logradouro.required' => 'Logradouro é obrigatório.',
            'numero.required' => 'Número é obrigatório.',
            'cidade.required' => 'A cidade é obrigatória.',
            'estado.size' => 'O estado deve ter 2 letras.',
            'area.gt' => 'Informe a área do imóvel.',
            'quartos.min' => 'Informe a quantidade de quartos.',
            'banheiros.min' => 'Informe a quantidade de banheiros.',
            'vagasGaragem.min' => 'Informe a quantidade de vagas.',
            'valorAluguel.gt' => 'Informe o valor do aluguel.',
            'valorIptu.min' => 'O IPTU não pode ser negativo.',
            'valorCondominio.min' => 'O condomínio não pode ser negativo.',
            'descricao.max' => 'Descrição muito longa.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'título',
            'tipo' => 'tipo',
            'cep' => 'CEP',
            'logradouro' => 'logradouro',
            'numero' => 'número',
            'complemento' => 'complemento',
            'bairro' => 'bairro',
            'cidade' => 'cidade',
            'estado' => 'estado',
            'area' => 'área',
            'quartos' => 'quartos',
            'banheiros' => 'banheiros',
            'vagasGaragem' => 'vagas de garagem',
            'valorAluguel' => 'valor do aluguel',
            'valorIptu' => 'valor do IPTU',
            'valorCondominio' => 'valor do condomínio',
            'matricula' => 'matrícula',
            'descricao' => 'descrição',
        ];
    }
}
