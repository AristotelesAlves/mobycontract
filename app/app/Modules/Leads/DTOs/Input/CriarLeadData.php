<?php

declare(strict_types=1);

namespace App\Modules\Leads\DTOs\Input;

use App\Modules\Leads\Domain\Enums\OrigemLead;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class CriarLeadData
{
    public function __construct(
        public string $nome,
        public string $telefone,
        public ?string $email,
        public ?string $imovelDeInteresse,
        public OrigemLead $origem,
        public ?string $observacoes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $origem = DadosDeEntrada::textoOpcional($validated, 'origem');

        return new self(
            nome: DadosDeEntrada::texto($validated, 'nome'),
            telefone: DadosDeEntrada::texto($validated, 'telefone'),
            email: DadosDeEntrada::textoOpcional($validated, 'email'),
            imovelDeInteresse: DadosDeEntrada::textoOpcional($validated, 'imovelDeInteresse'),
            origem: $origem === null ? OrigemLead::Outro : OrigemLead::from($origem),
            observacoes: DadosDeEntrada::textoOpcional($validated, 'observacoes'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function paraAtributos(): array
    {
        return [
            'nome' => $this->nome,
            'telefone' => $this->telefone,
            'email' => $this->email,
            'imovel_de_interesse' => $this->imovelDeInteresse,
            'origem' => $this->origem->value,
            'observacoes' => $this->observacoes,
        ];
    }
}
