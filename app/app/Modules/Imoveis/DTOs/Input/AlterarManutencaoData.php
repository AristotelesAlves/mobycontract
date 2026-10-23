<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

final readonly class AlterarManutencaoData
{
    public function __construct(
        public ?string $titulo = null,
        public ?string $descricao = null,
        public ?string $responsavel = null,
        public ?float $custo = null,
        public ?string $status = null,
    ) {}
}
