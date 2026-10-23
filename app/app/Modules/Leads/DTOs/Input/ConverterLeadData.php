<?php

declare(strict_types=1);

namespace App\Modules\Leads\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ConverterLeadData
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public string $email,
        public string $telefone,
        public string $dataNascimento,
        public string $estadoCivil,
        public float $renda,
        public string $endereco,
        public ?string $rg = null,
        public ?string $profissao = null,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            nome: DadosDeEntrada::texto($validated, 'nome'),
            cpf: DadosDeEntrada::texto($validated, 'cpf'),
            email: DadosDeEntrada::texto($validated, 'email'),
            telefone: DadosDeEntrada::texto($validated, 'telefone'),
            dataNascimento: DadosDeEntrada::texto($validated, 'dataNascimento'),
            estadoCivil: DadosDeEntrada::texto($validated, 'estadoCivil'),
            renda: DadosDeEntrada::decimal($validated, 'renda'),
            endereco: DadosDeEntrada::texto($validated, 'endereco'),
            rg: isset($validated['rg']) && is_string($validated['rg']) ? $validated['rg'] : null,
            profissao: isset($validated['profissao']) && is_string($validated['profissao']) ? $validated['profissao'] : null,
        );
    }
}
