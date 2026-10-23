<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

final readonly class CriarAgendamentoData
{
    public function __construct(
        public int $imovelId,
        public string $data,
        public string $hora,
        public string $interessado,
        public ?string $telefone = null,
    ) {}
}
