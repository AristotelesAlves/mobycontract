<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\DTOs\Output;

final readonly class IndicadoresInquilinoResult
{
    public function __construct(
        public int $idade,
        public bool $possuiContratoAtivo,
        public ?float $compromissoRenda,
        public float $limiteSeguro,
        public bool $alertaCompromisso,
        public int $contratosVigentes,
        public int $contratosEncerrados,
        public int $parcelasAtrasadas,
    ) {}
}
