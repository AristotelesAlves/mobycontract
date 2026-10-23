<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

final readonly class CriarInteressadoData
{
    public function __construct(
        public int $imovelId,
        public string $nome,
        public string $telefone,
        public ?string $email = null,
        public string $tipo = 'Visitar',
    ) {}
}
