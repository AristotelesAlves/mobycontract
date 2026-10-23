<?php

declare(strict_types=1);

namespace App\Modules\Regras\DTOs\Output;

use App\Modules\Regras\Models\RegraImobiliaria;

final readonly class RegraImobiliariaResult
{
    public function __construct(
        public int $id,
        public bool $contratoUnicoPorImovel,
        public bool $contratoUnicoPorInquilino,
    ) {}

    public static function fromModel(RegraImobiliaria $regra): self
    {
        return new self(
            id: (int) $regra->id,
            contratoUnicoPorImovel: (bool) $regra->contrato_unico_por_imovel,
            contratoUnicoPorInquilino: (bool) $regra->contrato_unico_por_inquilino,
        );
    }
}
