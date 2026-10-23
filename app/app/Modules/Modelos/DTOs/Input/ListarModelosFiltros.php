<?php

declare(strict_types=1);

namespace App\Modules\Modelos\DTOs\Input;

use App\Modules\Modelos\Domain\Enums\TipoModelo;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarModelosFiltros
{
    public function __construct(
        public ?TipoModelo $tipo,
        public ?bool $ativo,
        public ?string $texto,
        public int $pagina,
        public int $tamanhoPagina,
        public string $ordenarPor,
        public string $direcao,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $tipo = DadosDeEntrada::textoOpcional($validated, 'tipo');

        return new self(
            tipo: $tipo === null ? null : TipoModelo::from($tipo),
            ativo: DadosDeEntrada::booleanoOpcional($validated, 'ativo'),
            texto: DadosDeEntrada::textoOpcional($validated, 'texto'),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            ordenarPor: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'codigo',
            direcao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'asc',
        );
    }
}
