<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

final readonly class CriarTaxaData
{
    public function __construct(
        public int $contratoId,
        public string $nome,
        public bool $opcional,
        public string $responsavel,
        public string $periodicidade,
        public float $valor,
        public bool $ativa = true,
    ) {}
}
