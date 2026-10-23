<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Contracts;

use App\Modules\Usuarios\DTOs\Input\ListarUsuariosFiltros;
use App\Modules\Usuarios\DTOs\Output\UsuarioResult;
use App\Shared\Application\DTOs\PaginatedResult;

interface UsuarioRepositoryInterface
{
    /**
     * @return PaginatedResult<UsuarioResult>
     */
    public function paginar(ListarUsuariosFiltros $filtros): PaginatedResult;

    public function buscarPorId(int $usuarioId): ?UsuarioResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): UsuarioResult;

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $usuarioId, array $atributos): UsuarioResult;

    public function atualizarSenha(int $usuarioId, string $novaSenha): void;

    public function existeEmail(string $email, ?int $ignorarUsuarioId = null): bool;

    /**
     * @return list<UsuarioResult>
     */
    public function listarOperadores(): array;
}
