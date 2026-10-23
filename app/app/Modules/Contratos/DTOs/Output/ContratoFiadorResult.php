<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Output;

final readonly class ContratoFiadorResult
{
    public function __construct(
        public int $id,
        public string $nome,
        public string $cpf,
        public string $telefone,
    ) {}
}
