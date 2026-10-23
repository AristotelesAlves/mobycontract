<?php

declare(strict_types=1);

namespace App\Modules\Contratos\Contracts;

use App\Modules\Contratos\DTOs\Input\FiadorData;
use App\Modules\Contratos\DTOs\Input\ListarContratosFiltros;
use App\Modules\Contratos\DTOs\Input\ListarParcelasFiltros;
use App\Modules\Contratos\DTOs\Output\ContratoResult;
use App\Modules\Contratos\DTOs\Output\ParcelaResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface ContratoRepositoryInterface
{
    /**
     * @return PaginatedResult<ContratoResult>
     */
    public function paginar(ListarContratosFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $contratoId): ?ContratoResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): ContratoResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $contratoId, array $atributos): ContratoResult;

    public function excluir(int $contratoId): bool;

    public function proximoNumero(string $ano): string;

    public function existeContratoAtivoParaImovel(int $imovelId, ?int $ignorarContratoId = null): bool;

    public function existeContratoAtivoParaInquilino(int $inquilinoId, ?int $ignorarContratoId = null): bool;

    /**
     * @return array<string, int>
     */
    public function totaisPorStatus(): array;

    /**
     * @param  list<FiadorData>  $fiadores
     */
    public function substituirFiadores(int $contratoId, array $fiadores): void;

    /**
     * @param  list<array{tipo: string, responsabilidade: string}>  $utilidades
     */
    public function registrarUtilidades(int $contratoId, array $utilidades): void;

    /**
     * @param  list<array{tipo: string, responsabilidade: string, valor_estimado: ?float}>  $encargos
     */
    public function registrarEncargosFixos(int $contratoId, array $encargos): void;

    /**
     * @param  list<array{papel: string, nome: string}>  $assinaturas
     */
    public function criarAssinaturas(int $contratoId, array $assinaturas): void;

    public function marcarAssinatura(int $contratoId, int $assinaturaId, string $momento): bool;

    public function todasAsAssinaturasAssinadas(int $contratoId): bool;

    public function possuiAssinatura(int $contratoId): bool;

    public function estornarParcelasNaoPagas(int $contratoId): void;

    /**
     * @param  list<array<string, mixed>>  $parcelas
     */
    public function criarParcelas(int $contratoId, array $parcelas): void;

    public function existeParcelaComReferencia(int $contratoId, string $referencia, ?int $ignorarParcelaId = null): bool;

    public function contarParcelas(int $contratoId): int;

    /**
     * @return PaginatedResult<ParcelaResult>
     */
    public function paginarParcelas(ListarParcelasFiltros $filtros): PaginatedResult;

    public function buscarParcela(int $contratoId, int $parcelaId): ?ParcelaResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizarParcela(int $parcelaId, array $atributos): ParcelaResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function registrarEncargo(int $contratoId, int $parcelaId, array $atributos): void;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function registrarHistorico(int $contratoId, array $atributos): void;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function registrarAlteracao(int $contratoId, array $atributos): void;
}
