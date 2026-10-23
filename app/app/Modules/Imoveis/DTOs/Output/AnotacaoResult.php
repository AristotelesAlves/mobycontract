<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

use App\Modules\Imoveis\Models\Anotacao;

final readonly class AnotacaoResult
{
    public function __construct(
        public int $id,
        public int $imovelId,
        public string $texto,
        public int $autorId,
        public ?string $autorNome,
        public ?string $criadoEm,
    ) {}

    public static function fromModel(Anotacao $anotacao): self
    {
        return new self(
            id: (int) $anotacao->id,
            imovelId: (int) $anotacao->imovel_id,
            texto: (string) $anotacao->texto,
            autorId: (int) $anotacao->autor_id,
            autorNome: $anotacao->relationLoaded('autor') && $anotacao->autor !== null ? (string) $anotacao->autor->nome : null,
            criadoEm: $anotacao->created_at?->toISOString(),
        );
    }
}
