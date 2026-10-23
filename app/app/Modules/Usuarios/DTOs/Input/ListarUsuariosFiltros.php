<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\DTOs\Input;

use App\Shared\Application\Support\DadosDeEntrada;
use App\Shared\Domain\Enums\Papel;

final readonly class ListarUsuariosFiltros
{
    public function __construct(
        public ?bool $ativo = null,
        public ?Papel $papel = null,
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
        $papel = DadosDeEntrada::textoOpcional($validated, 'papel');

        return new self(
            ativo: DadosDeEntrada::booleanoOpcional($validated, 'ativo'),
            papel: $papel === null ? null : Papel::from($papel),
            texto: DadosDeEntrada::textoOpcional($validated, 'texto'),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'nome',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'asc',
        );
    }
}
