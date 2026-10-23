<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

final readonly class CriarManutencaoData
{
    public function __construct(
        public int $imovelId,
        public string $titulo,
        public ?string $descricao = null,
        public ?string $responsavel = null,
        public ?float $custo = null,
    ) {}
}
