<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

final readonly class AlterarSituacaoAgendamentoData
{
    public function __construct(
        public string $status,
    ) {}
}
