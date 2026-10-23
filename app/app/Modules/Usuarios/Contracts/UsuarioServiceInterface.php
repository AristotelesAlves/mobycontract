<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Contracts;

use App\Modules\Usuarios\DTOs\Input\AlterarUsuarioData;
use App\Modules\Usuarios\DTOs\Input\CriarUsuarioData;
use App\Modules\Usuarios\DTOs\Input\ListarUsuariosFiltros;
use App\Modules\Usuarios\DTOs\Output\UsuarioResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\ConflitoDeUnicidadeException;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\RegraNegocioException;

interface UsuarioServiceInterface
{
    /**
     * @return PaginatedResult<UsuarioResult>
     */
    public function listar(ListarUsuariosFiltros $filtros): PaginatedResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function obter(int $usuarioId): UsuarioResult;

    /**
     * @throws ConflitoDeUnicidadeException
     */
    public function armazenar(CriarUsuarioData $dados, int $autorId): UsuarioResult;

    /**
     * @throws RecursoNaoEncontradoException
     * @throws ConflitoDeUnicidadeException
     * @throws RegraNegocioException
     */
    public function alterar(AlterarUsuarioData $dados, int $autorId): UsuarioResult;

    /**
     * @throws RecursoNaoEncontradoException
     */
    public function alterarSenha(int $usuarioId, string $novaSenha, int $autorId): void;

    /**
     * @return list<UsuarioResult>
     */
    public function listarOperadores(): array;
}
