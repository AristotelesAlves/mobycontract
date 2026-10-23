<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Output;

final readonly class VistoriaFotoResult
{
    public function __construct(
        public int $id,
        public int $vistoriaId,
        public ?string $descricao,
        public string $nomeAnexo,
        public ?string $anexo,
    ) {}
}
