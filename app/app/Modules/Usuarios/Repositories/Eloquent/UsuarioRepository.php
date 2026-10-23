<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Repositories\Eloquent;

use App\Modules\Usuarios\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\DTOs\Input\ListarUsuariosFiltros;
use App\Modules\Usuarios\DTOs\Output\UsuarioResult;
use App\Modules\Usuarios\Models\User;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Domain\Enums\Papel;
use Illuminate\Database\Eloquent\Builder;

final readonly class UsuarioRepository implements UsuarioRepositoryInterface
{
    public function __construct(
        private User $modelo,
    ) {}

    /**
     * @return PaginatedResult<UsuarioResult>
     */
    public function paginar(ListarUsuariosFiltros $filtros): PaginatedResult
    {
        $consulta = $this->modelo->newQuery();

        if ($filtros->ativo !== null) {
            $consulta->where('ativo', $filtros->ativo);
        }

        if ($filtros->papel instanceof Papel) {
            $consulta->where('papel', $filtros->papel->value);
        }

        if ($filtros->texto !== null && $filtros->texto !== '') {
            $termo = '%'.$filtros->texto.'%';
            $consulta->whereRaw('(unaccent(nome) ILIKE ? OR unaccent(email) ILIKE ?)', [$termo, $termo]);
        }

        $pagina = $consulta
            ->orderBy($filtros->colunaOrdenacao, $filtros->direcaoOrdenacao)
            ->paginate($filtros->tamanhoPagina, ['*'], 'pagina', $filtros->pagina);

        /** @var PaginatedResult<UsuarioResult> $resultado */
        $resultado = PaginatedResult::fromPaginator(
            $pagina,
            static fn (User $usuario): UsuarioResult => UsuarioResult::fromModel($usuario),
        );

        return $resultado;
    }

    public function buscarPorId(int $usuarioId): ?UsuarioResult
    {
        $usuario = $this->modelo->newQuery()->find($usuarioId);

        return $usuario === null ? null : UsuarioResult::fromModel($usuario);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function criar(array $atributos): UsuarioResult
    {
        $criado = $this->modelo->newQuery()->create($atributos);

        return UsuarioResult::fromModel($criado->refresh());
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    public function atualizar(int $usuarioId, array $atributos): UsuarioResult
    {
        $usuario = $this->modelo->newQuery()->findOrFail($usuarioId);
        $usuario->fill($atributos)->save();

        return UsuarioResult::fromModel($usuario->refresh());
    }

    public function atualizarSenha(int $usuarioId, string $novaSenha): void
    {
        $usuario = $this->modelo->newQuery()->findOrFail($usuarioId);
        $usuario->password = $novaSenha;
        $usuario->save();
    }

    public function existeEmail(string $email, ?int $ignorarUsuarioId = null): bool
    {
        return $this->modelo->newQuery()
            ->where('email', $email)
            ->when($ignorarUsuarioId !== null, fn (Builder $consulta): Builder => $consulta->whereKeyNot($ignorarUsuarioId))
            ->exists();
    }

    /**
     * @return list<UsuarioResult>
     */
    public function listarOperadores(): array
    {
        $operadores = $this->modelo->newQuery()
            ->where('papel', Papel::Operador->value)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get()
            ->map(static fn (User $usuario): UsuarioResult => UsuarioResult::fromModel($usuario))
            ->all();

        return array_values($operadores);
    }
}
