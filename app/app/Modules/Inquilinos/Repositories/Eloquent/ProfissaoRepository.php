<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\Repositories\Eloquent;

use App\Modules\Inquilinos\Contracts\ProfissaoRepositoryInterface;
use App\Modules\Inquilinos\Models\Profissao;

final readonly class ProfissaoRepository implements ProfissaoRepositoryInterface
{
    public function __construct(private Profissao $profissoes) {}

    /** @return list<array{id: int, nome: string}> */
    public function listar(): array
    {
        return array_values($this->profissoes->newQuery()
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(static fn (Profissao $profissao): array => [
                'id' => (int) $profissao->id,
                'nome' => (string) $profissao->nome,
            ])
            ->all());
    }
}
