<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\DTOs\Input;

use App\Modules\Vistorias\Domain\Enums\StatusVistoria;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarVistoriasFiltros
{
    public function __construct(
        public int $contratoId,
        public ?StatusVistoria $status,
        public int $pagina,
        public int $tamanhoPagina,
        public string $colunaOrdenacao,
        public string $direcaoOrdenacao,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(int $contratoId, array $validated): self
    {
        $status = DadosDeEntrada::textoOpcional($validated, 'status');

        return new self(
            contratoId: $contratoId,
            status: $status === null ? null : StatusVistoria::from($status),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'created_at',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'desc',
        );
    }
}
