<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

final readonly class AditivoAssinaturaResult
{
    public function __construct(
        public int $id,
        public string $papel,
        public string $nome,
        public bool $assinado,
        public ?string $dataAssinatura,
    ) {}
}
