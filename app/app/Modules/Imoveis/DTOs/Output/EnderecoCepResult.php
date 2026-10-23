<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Output;

final readonly class EnderecoCepResult
{
    public function __construct(
        public ?string $logradouro,
        public ?string $bairro,
        public ?string $cidade,
        public ?string $estado,
        public bool $encontrado,
    ) {}
}
