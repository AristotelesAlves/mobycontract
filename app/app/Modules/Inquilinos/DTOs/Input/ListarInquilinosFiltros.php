<?php

declare(strict_types=1);

namespace App\Modules\Inquilinos\DTOs\Input;

use App\Modules\Inquilinos\Domain\Enums\FiltroDeContrato;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarInquilinosFiltros
{
    public function __construct(
        public ?FiltroDeContrato $situacao = null,
        public ?string $texto = null,
        public int $pagina = 1,
        public int $tamanhoPagina = 20,
        public string $colunaOrdenacao = 'nome',
        public string $direcaoOrdenacao = 'asc',
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $situacao = DadosDeEntrada::textoOpcional($validated, 'situacao');

        return new self(
            situacao: $situacao === null ? null : FiltroDeContrato::from($situacao),
            texto: DadosDeEntrada::textoOpcional($validated, 'texto'),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'nome',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'asc',
        );
    }
}
