<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

final readonly class AlterarTaxaData
{
    public function __construct(
        public ?string $nome = null,
        public ?bool $opcional = null,
        public ?string $responsavel = null,
        public ?string $periodicidade = null,
        public ?float $valor = null,
        public ?bool $ativa = null,
    ) {}
}
