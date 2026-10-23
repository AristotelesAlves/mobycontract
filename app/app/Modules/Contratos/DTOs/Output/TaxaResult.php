<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

use App\Modules\Contratos\Models\ContratoTaxa;

final readonly class TaxaResult
{
    public function __construct(
        public int $id,
        public int $contratoId,
        public string $nome,
        public bool $opcional,
        public string $responsavel,
        public string $periodicidade,
        public float $valor,
        public bool $ativa,
        public ?string $criadoEm,
    ) {}

    public static function fromModel(ContratoTaxa $taxa): self
    {
        return new self(
            id: (int) $taxa->id,
            contratoId: (int) $taxa->contrato_id,
            nome: (string) $taxa->nome,
            opcional: (bool) $taxa->opcional,
            responsavel: (string) $taxa->responsavel,
            periodicidade: (string) $taxa->periodicidade,
            valor: (float) $taxa->valor,
            ativa: (bool) $taxa->ativa,
            criadoEm: $taxa->created_at?->toISOString(),
        );
    }
}
