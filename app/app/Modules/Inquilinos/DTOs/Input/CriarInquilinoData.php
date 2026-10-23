<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\DTOs\Input;

use App\Modules\Inquilinos\Domain\Enums\EstadoCivil;
use App\Modules\Inquilinos\Domain\Enums\TipoPessoa;
use App\Modules\Inquilinos\Domain\ValidadorDeDocumento;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class CriarInquilinoData
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public TipoPessoa $tipoPessoa,
        public ?string $rg,
        public string $email,
        public string $telefone,
        public string $dataNascimento,
        public EstadoCivil $estadoCivil,
        public ?string $profissao,
        public float $renda,
        public string $endereco,
        public ?int $clienteId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $tipoPessoa = DadosDeEntrada::textoOpcional($validated, 'tipoPessoa');
        $clienteId = DadosDeEntrada::inteiro($validated, 'clienteId', 0);

        return new self(
            nome: DadosDeEntrada::texto($validated, 'nome'),
            cpf: ValidadorDeDocumento::somenteDigitos(DadosDeEntrada::texto($validated, 'cpf')),
            tipoPessoa: $tipoPessoa === null ? TipoPessoa::Pf : TipoPessoa::from($tipoPessoa),
            rg: DadosDeEntrada::textoOpcional($validated, 'rg'),
            email: DadosDeEntrada::texto($validated, 'email'),
            telefone: DadosDeEntrada::texto($validated, 'telefone'),
            dataNascimento: DadosDeEntrada::texto($validated, 'dataNascimento'),
            estadoCivil: EstadoCivil::from(DadosDeEntrada::texto($validated, 'estadoCivil')),
            profissao: DadosDeEntrada::textoOpcional($validated, 'profissao'),
            renda: DadosDeEntrada::decimal($validated, 'renda'),
            endereco: DadosDeEntrada::texto($validated, 'endereco'),
            clienteId: $clienteId === 0 ? null : $clienteId,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributos(): array
    {
        return [
            'nome' => $this->nome,
            'cpf' => $this->cpf,
            'tipo_pessoa' => $this->tipoPessoa->value,
            'rg' => $this->rg,
            'email' => $this->email,
            'telefone' => $this->telefone,
            'data_nascimento' => $this->dataNascimento,
            'estado_civil' => $this->estadoCivil->value,
            'profissao' => $this->profissao,
            'renda' => $this->renda,
            'endereco' => $this->endereco,
            'cliente_id' => $this->clienteId,
        ];
    }
}
