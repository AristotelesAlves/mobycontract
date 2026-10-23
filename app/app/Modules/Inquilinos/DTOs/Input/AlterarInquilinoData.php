<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\DTOs\Input;

use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;
use App\Modules\Inquilinos\Domain\ValidadorDeDocumento;
use App\Modules\Inquilinos\DTOs\Output\InquilinoResult;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class AlterarInquilinoData
{
    /**
     * @param  array<string, mixed>  $campos
     */
    private function __construct(
        public int $id,
        private array $campos,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $id = DadosDeEntrada::inteiro($validated, 'id');

        unset($validated['id']);

        return new self($id, $validated);
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributosSobre(InquilinoResult $atual): array
    {
        return [
            'nome' => $this->texto('nome') ?? $atual->nome,
            'cpf' => $this->cpf() ?? $atual->cpf,
            'tipo_pessoa' => $this->tipoPessoa()->value ?? $atual->tipoPessoa,
            'rg' => $this->informado('rg') ? $this->textoOpcional('rg') : $atual->rg,
            'email' => $this->texto('email') ?? $atual->email,
            'telefone' => $this->texto('telefone') ?? $atual->telefone,
            'data_nascimento' => $this->texto('dataNascimento') ?? $atual->dataNascimento,
            'estado_civil' => $this->estadoCivil()->value ?? $atual->estadoCivil,
            'profissao' => $this->informado('profissao') ? $this->textoOpcional('profissao') : $atual->profissao,
            'renda' => $this->decimal('renda') ?? $atual->renda,
            'endereco' => $this->texto('endereco') ?? $atual->endereco,
        ];
    }

    private function informado(string $chave): bool
    {
        return DadosDeEntrada::existe($this->campos, $chave);
    }

    private function texto(string $chave): ?string
    {
        return $this->informado($chave) ? DadosDeEntrada::textoOpcional($this->campos, $chave) : null;
    }

    private function textoOpcional(string $chave): ?string
    {
        return DadosDeEntrada::textoOpcional($this->campos, $chave);
    }

    private function cpf(): ?string
    {
        $valor = $this->texto('cpf');

        return $valor === null ? null : ValidadorDeDocumento::somenteDigitos($valor);
    }

    private function decimal(string $chave): ?float
    {
        return $this->informado($chave) ? DadosDeEntrada::decimalOpcional($this->campos, $chave) : null;
    }

    private function tipoPessoa(): ?TipoPessoa
    {
        $valor = $this->texto('tipoPessoa');

        return $valor === null ? null : TipoPessoa::from($valor);
    }

    private function estadoCivil(): ?EstadoCivil
    {
        $valor = $this->texto('estadoCivil');

        return $valor === null ? null : EstadoCivil::from($valor);
    }
}
