<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

use App\Modules\Imoveis\Models\Interessado;

final readonly class InteressadoResult
{
    public function __construct(
        public int $id,
        public int $imovelId,
        public string $nome,
        public string $telefone,
        public ?string $email,
        public string $tipo,
        public string $status,
        public ?string $criadoEm,
    ) {}

    public static function fromModel(Interessado $interessado): self
    {
        return new self(
            id: (int) $interessado->id,
            imovelId: (int) $interessado->imovel_id,
            nome: (string) $interessado->nome,
            telefone: (string) $interessado->telefone,
            email: $interessado->email !== null ? (string) $interessado->email : null,
            tipo: (string) $interessado->tipo,
            status: (string) $interessado->status,
            criadoEm: $interessado->created_at?->toISOString(),
        );
    }
}
