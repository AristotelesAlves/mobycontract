<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

final readonly class EtapaResult
{
    public function __construct(
        public int $ordem,
        public string $titulo,
        public bool $concluida,
        public bool $ativa,
        public ?int $assinaturaId,
    ) {}
}
