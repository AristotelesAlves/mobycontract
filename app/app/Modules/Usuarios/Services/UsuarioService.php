<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Services;

use App\Modules\Usuarios\Contracts\UsuarioRepositoryInterface;
use App\Modules\Usuarios\Contracts\UsuarioServiceInterface;
use App\Modules\Usuarios\DTOs\Input\AlterarUsuarioData;
use App\Modules\Usuarios\DTOs\Input\CriarUsuarioData;
use App\Modules\Usuarios\DTOs\Input\ListarUsuariosFiltros;
use App\Modules\Usuarios\DTOs\Output\UsuarioResult;
use App\Shared\Application\DTOs\PaginatedResult;
use App\Shared\Application\Exceptions\ConflitoDeUnicidadeException;
use App\Shared\Application\Exceptions\RecursoNaoEncontradoException;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\TransactionManager;
use App\Shared\Domain\Enums\Papel;

final readonly class UsuarioService implements UsuarioServiceInterface
{
    public function __construct(
        private UsuarioRepositoryInterface $usuarios,
        private TransactionManager $transacao,
        private DomainEventDispatcher $eventos,
    ) {}

    /**
     * @return PaginatedResult<UsuarioResult>
     */
    public function listar(ListarUsuariosFiltros $filtros): PaginatedResult
    {
        return $this->usuarios->paginar($filtros);
    }

    public function obter(int $usuarioId): UsuarioResult
    {
        $usuario = $this->usuarios->buscarPorId($usuarioId);

        if (! $usuario instanceof UsuarioResult) {
            throw RecursoNaoEncontradoException::usuario($usuarioId);
        }

        return $usuario;
    }

    public function armazenar(CriarUsuarioData $dados, int $autorId): UsuarioResult
    {
        if ($this->usuarios->existeEmail($dados->email)) {
            throw ConflitoDeUnicidadeException::emailJaCadastrado($dados->email);
        }

        return $this->transacao->executar(function () use ($dados, $autorId): UsuarioResult {
            $usuario = $this->usuarios->criar($dados->paraAtributos());
            $this->eventos->publicar('usuario.criado', [
                'usuarioId' => $usuario->id,
                'autorId' => $autorId,
            ]);

            return $usuario;
        });
    }

    public function alterar(AlterarUsuarioData $dados, int $autorId): UsuarioResult
    {
        $atual = $this->obter($dados->id);
        $this->garantirAutoAlteracaoPermitida($dados, $atual, $autorId);
        $this->garantirEmailDisponivel($dados, $atual);

        return $this->transacao->executar(function () use ($dados, $autorId): UsuarioResult {
            $usuario = $this->usuarios->atualizar($dados->id, $dados->paraAtributos());
            $this->eventos->publicar('usuario.alterado', [
                'usuarioId' => $usuario->id,
                'autorId' => $autorId,
            ]);

            return $usuario;
        });
    }

    public function alterarSenha(int $usuarioId, string $novaSenha, int $autorId): void
    {
        $this->obter($usuarioId);

        $this->transacao->executar(function () use ($usuarioId, $novaSenha, $autorId): void {
            $this->usuarios->atualizarSenha($usuarioId, $novaSenha);
            $this->eventos->publicar('usuario.senha_alterada', [
                'usuarioId' => $usuarioId,
                'autorId' => $autorId,
            ]);
        });
    }

    /**
     * @return list<UsuarioResult>
     */
    public function listarOperadores(): array
    {
        return $this->usuarios->listarOperadores();
    }

    private function garantirAutoAlteracaoPermitida(
        AlterarUsuarioData $dados,
        UsuarioResult $atual,
        int $autorId,
    ): void {
        if ($dados->id !== $autorId) {
            return;
        }

        if ($dados->papel instanceof Papel && $dados->papel->value !== $atual->papel) {
            throw new RegraNegocioException('Você não pode alterar o seu próprio papel.', [
                'papel' => 'O próprio usuário não pode alterar o próprio papel.',
            ], ['usuarioId' => $autorId]);
        }

        if ($dados->ativo === false) {
            throw new RegraNegocioException('Você não pode desativar a própria conta.', [
                'ativo' => 'O próprio usuário não pode se desativar.',
            ], ['usuarioId' => $autorId]);
        }
    }

    private function garantirEmailDisponivel(AlterarUsuarioData $dados, UsuarioResult $atual): void
    {
        if ($dados->email === null || $dados->email === $atual->email) {
            return;
        }

        if ($this->usuarios->existeEmail($dados->email, $atual->id)) {
            throw ConflitoDeUnicidadeException::emailJaCadastrado($dados->email);
        }
    }
}
