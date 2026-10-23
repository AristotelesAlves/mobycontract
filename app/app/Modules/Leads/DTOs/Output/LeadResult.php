<?php

declare(strict_types=1);

namespace App\Modules\Leads\DTOs\Output;

use App\Modules\Leads\Models\Lead;
use DateTimeInterface;

final readonly class LeadResult
{
    public function __construct(
        public int $id,
        public string $nome,
        public string $telefone,
        public ?string $email,
        public ?string $imovelDeInteresse,
        public string $origem,
        public string $situacao,
        public ?string $observacoes,
        public ?string $dataCadastro,
        public ?string $convertidoEm,
    ) {}

    public static function fromModel(Lead $lead): self
    {
        return new self(
            id: (int) $lead->id,
            nome: (string) $lead->nome,
            telefone: (string) $lead->telefone,
            email: self::textoOuNulo($lead->email),
            imovelDeInteresse: self::textoOuNulo($lead->imovel_de_interesse),
            origem: (string) $lead->origem,
            situacao: (string) $lead->status,
            observacoes: self::textoOuNulo($lead->observacoes),
            dataCadastro: self::instante($lead->data_cadastro),
            convertidoEm: self::instante($lead->convertido_em),
        );
    }

    private static function textoOuNulo(mixed $valor): ?string
    {
        return is_scalar($valor) ? (string) $valor : null;
    }

    private static function instante(mixed $valor): ?string
    {
        return $valor instanceof DateTimeInterface ? $valor->format(DATE_ATOM) : null;
    }
}
