<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Services;

use App\Modules\Inquilinos\Contracts\ProfissaoRepositoryInterface;

final readonly class ProfissaoService
{
    public function __construct(private ProfissaoRepositoryInterface $profissoes) {}

    /** @return list<array{id: int, nome: string}> */
    public function listar(): array
    {
        return $this->profissoes->listar();
    }
}
