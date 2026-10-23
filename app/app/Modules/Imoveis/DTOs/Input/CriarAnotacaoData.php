<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

final readonly class CriarAnotacaoData
{
    public function __construct(
        public int $imovelId,
        public string $texto,
    ) {}
}
