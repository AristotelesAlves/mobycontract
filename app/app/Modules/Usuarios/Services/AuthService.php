<?php

declare(strict_types=1);

namespace App\Modules\Usuarios\Services;

use App\Modules\Usuarios\Contracts\AuthRepositoryContract;
use App\Modules\Usuarios\Models\User;
use App\Shared\Application\Exceptions\RegraNegocioException;
use App\Shared\Contracts\TokenIssuer;
use App\Shared\Contracts\VerificadorDeSenha;

final readonly class AuthService
{
    public function __construct(
        private AuthRepositoryContract $repositorioDeUsuarios,
        private TokenIssuer $emissorDeToken,
        private VerificadorDeSenha $verificadorDeSenha,
    ) {}

    /**
     * @return array{token: string, expiraEm: string|null, usuario: User}
     */
    public function autenticar(string $email, string $senha): array
    {
        $usuario = $this->repositorioDeUsuarios->localizarPorEmail($email);

        if (! $usuario instanceof User) {
            throw new RegraNegocioException('E-mail ou senha inválidos.');
        }

        if (! $this->verificadorDeSenha->conferir($senha, (string) $usuario->password)) {
            throw new RegraNegocioException('E-mail ou senha inválidos.');
        }

        if (! (bool) $usuario->ativo) {
            throw new RegraNegocioException('Usuário desativado. Procure o administrador.');
        }

        $token = $this->emissorDeToken->emitir($usuario);

        return [
            'token' => $token['token'],
            'expiraEm' => $token['expiraEm'],
            'usuario' => $usuario,
        ];
    }
}
