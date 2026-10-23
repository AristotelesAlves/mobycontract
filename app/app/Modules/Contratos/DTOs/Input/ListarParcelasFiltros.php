<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\StatusParcela;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarParcelasFiltros
{
    public function __construct(
        public int $contratoId,
        public ?StatusParcela $status = null,
        public ?string $texto = null,
        public int $pagina = 1,
        public int $tamanhoPagina = 20,
        public string $colunaOrdenacao = 'vencimento',
        public string $direcaoOrdenacao = 'asc',
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(int $contratoId, array $validated): self
    {
        $status = DadosDeEntrada::textoOpcional($validated, 'status');

        return new self(
            contratoId: $contratoId,
            status: $status === null ? null : StatusParcela::from($status),
            texto: DadosDeEntrada::textoOpcional($validated, 'texto'),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'vencimento',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'asc',
        );
    }
}
