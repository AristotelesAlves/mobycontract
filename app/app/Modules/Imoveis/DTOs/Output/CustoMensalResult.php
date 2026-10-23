<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

final readonly class CustoMensalResult
{
    public function __construct(
        public float $custoMensal,
        public float $valorAluguel,
        public float $valorCondominio,
        public float $valorIptuMensal,
    ) {}
}
