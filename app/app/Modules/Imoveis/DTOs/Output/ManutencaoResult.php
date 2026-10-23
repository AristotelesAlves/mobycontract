<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

use App\Modules\Imoveis\Models\Manutencao;

final readonly class ManutencaoResult
{
    public function __construct(
        public int $id,
        public int $imovelId,
        public string $titulo,
        public ?string $descricao,
        public ?string $responsavel,
        public float $custo,
        public string $status,
        public ?string $criadoEm,
    ) {}

    public static function fromModel(Manutencao $manutencao): self
    {
        return new self(
            id: (int) $manutencao->id,
            imovelId: (int) $manutencao->imovel_id,
            titulo: (string) $manutencao->titulo,
            descricao: $manutencao->descricao !== null ? (string) $manutencao->descricao : null,
            responsavel: $manutencao->responsavel !== null ? (string) $manutencao->responsavel : null,
            custo: (float) $manutencao->custo,
            status: (string) $manutencao->status,
            criadoEm: $manutencao->created_at?->toISOString(),
        );
    }
}
