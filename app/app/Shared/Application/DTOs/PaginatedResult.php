<?php

declare(strict_types=1);

namespace App\Shared\Application\DTOs;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * @template TItem
 */
final class PaginatedResult
{
    /**
     * @param  list<TItem>  $itens
     */
    public function __construct(
        public int $pagina,
        public int $tamanhoPagina,
        public int $totalPaginas,
        public int $total,
        public array $itens,
    ) {}

    /**
     * @template TEntrada
     *
     * @param  LengthAwarePaginator<int, TEntrada>  $paginator
     * @param  callable(TEntrada): TItem  $converter
     * @return self<TItem>
     */
    public static function fromPaginator(LengthAwarePaginator $paginator, callable $converter): self
    {
        /** @var list<TEntrada> $itensDaPagina */
        $itensDaPagina = $paginator->items();

        return new self(
            pagina: $paginator->currentPage(),
            tamanhoPagina: $paginator->perPage(),
            totalPaginas: $paginator->lastPage(),
            total: $paginator->total(),
            itens: array_map($converter, $itensDaPagina),
        );
    }
}
