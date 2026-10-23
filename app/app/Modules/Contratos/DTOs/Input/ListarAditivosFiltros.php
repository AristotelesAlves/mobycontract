<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\StatusAditivo;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarAditivosFiltros
{
    public function __construct(
        public int $contratoId,
        public ?StatusAditivo $status = null,
        public int $pagina = 1,
        public int $tamanhoPagina = 20,
        public string $colunaOrdenacao = 'created_at',
        public string $direcaoOrdenacao = 'desc',
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(int $contratoId, array $validated): self
    {
        $status = DadosDeEntrada::textoOpcional($validated, 'status');

        return new self(
            contratoId: $contratoId,
            status: $status === null ? null : StatusAditivo::from($status),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'created_at',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'desc',
        );
    }
}
