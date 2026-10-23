<?php

declare(strict_types=1);

namespace App\Modules\Contratos\DTOs\Input;

use App\Modules\Contratos\Domain\Enums\StatusContrato;
use App\Shared\Application\Support\DadosDeEntrada;

final readonly class ListarContratosFiltros
{
    public function __construct(
        public ?StatusContrato $status = null,
        public ?int $imovelId = null,
        public ?int $inquilinoId = null,
        public ?string $texto = null,
        public int $pagina = 1,
        public int $tamanhoPagina = 20,
        public string $colunaOrdenacao = 'created_at',
        public string $direcaoOrdenacao = 'desc',
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        $status = DadosDeEntrada::textoOpcional($validated, 'status');

        return new self(
            status: $status === null ? null : StatusContrato::from($status),
            imovelId: self::inteiroOpcional($validated, 'imovelId'),
            inquilinoId: self::inteiroOpcional($validated, 'inquilinoId'),
            texto: DadosDeEntrada::textoOpcional($validated, 'texto'),
            pagina: DadosDeEntrada::inteiro($validated, 'pagina', 1),
            tamanhoPagina: DadosDeEntrada::inteiro($validated, 'tamanhoPagina', 20),
            colunaOrdenacao: DadosDeEntrada::textoOpcional($validated, 'ordenarPor') ?? 'created_at',
            direcaoOrdenacao: DadosDeEntrada::textoOpcional($validated, 'direcao') ?? 'desc',
        );
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private static function inteiroOpcional(array $validated, string $chave): ?int
    {
        return DadosDeEntrada::existe($validated, $chave)
            ? DadosDeEntrada::inteiro($validated, $chave)
            : null;
    }
}
