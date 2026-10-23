<?php

declare(strict_types=1);

namespace App\Modules\Modelos\Contracts;

use App\Modules\Modelos\Domain\AnaliseDeVariaveis;
use App\Modules\Modelos\DTOs\Input\AlterarModeloData;
use App\Modules\Modelos\DTOs\Input\CriarModeloData;
use App\Modules\Modelos\DTOs\Input\ListarModelosFiltros;
use App\Modules\Modelos\DTOs\Input\ValidarConteudoData;
use App\Modules\Modelos\DTOs\Output\ModeloResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;

interface ModeloServiceInterface
{
    /**
     * @return PaginatedResult<ModeloResult>
     */
    public function listar(ListarModelosFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function obter(int $modeloId): ModeloResult;

    public function armazenar(CriarModeloData $dados, int $autorId): ModeloResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function alterar(AlterarModeloData $dados, int $autorId): ModeloResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function clonar(int $modeloId, int $autorId): ModeloResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function ativar(int $modeloId, int $autorId): ModeloResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function inativar(int $modeloId, int $autorId): ModeloResult;

    public function validarConteudo(ValidarConteudoData $dados): AnaliseDeVariaveis;
}
