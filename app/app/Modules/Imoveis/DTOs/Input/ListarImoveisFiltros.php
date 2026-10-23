<?php

declare(strict_types=1);

namespace App\Modules\Imoveis\DTOs\Input;

use App\Modules\Imoveis\Domain\Enums\SituacaoImovel;
use App\Modules\Imoveis\Domain\Enums\TipoImovel;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarImoveisFiltros
{
    public function __construct(
        public ?SituacaoImovel $situacao = null,
        public ?TipoImovel $tipo = null,
        public ?string $texto = null,
        public int $pagina = 1,
        public int $tamanhoPagina = 20,
        public string $colunaOrdenacao = 'titulo',
        public string $direcaoOrdenacao = 'asc',
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $situacao = DadosDeEntrada::textoOpcional($validated, 'status');
        $tipo = DadosDeEntrada::textoOpcional($validated, 'tipo');

        return new self(
            situacao: $situacao === null ? null : SituacaoImovel::from($situacao),
            tipo: $tipo === null ? null : TipoImovel::from($tipo),
            texto: DadosDeEntrada::textoOpcional($validated, 'texto'),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'titulo',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'asc',
        );
    }
}
