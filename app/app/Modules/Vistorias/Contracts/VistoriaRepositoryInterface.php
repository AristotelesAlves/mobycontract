<?php

declare(strict_types=1);

namespace App\Modules\Vistorias\Contracts;

use App\Modules\Vistorias\DTOs\Input\ListarVistoriasFiltros;
use App\Modules\Vistorias\DTOs\Output\VistoriaFotoResult;
use App\Modules\Vistorias\DTOs\Output\VistoriaResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface VistoriaRepositoryInterface
{
    /**
     * @return PaginatedResult<VistoriaResult>
     */
    public function paginar(ListarVistoriasFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $vistoriaId): ?VistoriaResult;

    public function buscarPorChaveAcesso(string $chaveAcesso): ?VistoriaResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): VistoriaResult;

    /** @param array<string, mixed> $atributos */
    public function atualizar(int $vistoriaId, array $atributos): void;

    public function excluir(int $vistoriaId): void;

    public function proximoNumero(int $contratoId, string $ano): string;

    /**
     * Substitui todos os itens da vistoria (padrão full-replace controlado).
     *
     * @param  list<array{ambiente: string, item: string, estado: string, observacao: string|null}>  $itens
     */
    public function substituirItens(int $vistoriaId, array $itens): void;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criarFoto(int $vistoriaId, array $atributos): VistoriaFotoResult;

    public function excluirFoto(int $vistoriaId, int $fotoId): void;
}
