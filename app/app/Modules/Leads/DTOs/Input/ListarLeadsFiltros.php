<?php

declare(strict_types=1);

namespace App\Modules\Leads\DTOs\Input;

use App\Modules\Leads\Domain\Enums\SituacaoLead;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarLeadsFiltros
{
    public function __construct(
        public ?SituacaoLead $situacao = null,
        public ?string $texto = null,
        public int $pagina = 1,
        public int $tamanhoPagina = 20,
        public string $colunaOrdenacao = 'data_cadastro',
        public string $direcaoOrdenacao = 'desc',
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $situacao = DadosDeEntrada::textoOpcional($validated, 'status');

        return new self(
            situacao: $situacao === null ? null : SituacaoLead::from($situacao),
            texto: DadosDeEntrada::textoOpcional($validated, 'texto'),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'data_cadastro',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'desc',
        );
    }
}
