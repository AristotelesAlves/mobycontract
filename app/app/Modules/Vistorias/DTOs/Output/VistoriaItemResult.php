<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Output;

final readonly class VistoriaItemResult
{
    public function __construct(
        public int $id,
        public int $vistoriaId,
        public string $ambiente,
        public string $item,
        public string $estado,
        public ?string $observacao,
    ) {}
}
