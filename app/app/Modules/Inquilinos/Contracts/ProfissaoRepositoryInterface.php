<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Contracts;

interface ProfissaoRepositoryInterface
{
    /** @return list<array{id: int, nome: string}> */
    public function listar(): array;
}
