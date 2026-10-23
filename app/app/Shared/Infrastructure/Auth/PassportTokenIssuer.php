<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Auth;

use App\Modules\Usuarios\Models\User;
use App\Shared\Contracts\TokenIssuer;
use Illuminate\Support\Carbon;
use Laravel\Passport\Token;

final class PassportTokenIssuer implements TokenIssuer
{
    private const string NOME_TOKEN = 'mobicontract';

    /**
     * @return array{token: string, expiraEm: string|null}
     */
    public function emitir(User $usuario): array
    {
        $resultado = $usuario->createToken(self::NOME_TOKEN, $usuario->abilities());

        return [
            'token' => $resultado->accessToken,
            'expiraEm' => $this->expiraEm($resultado->token),
        ];
    }

    public function revogar(User $usuario): void
    {
        $usuario->tokens()->delete();
    }

    private function expiraEm(Token $token): ?string
    {
        $expira = $token->getAttribute('expires_at');

        return $expira instanceof \DateTimeInterface ? $expira->format(DATE_ATOM) : (is_string($expira) ? Carbon::parse($expira)->toIso8601String() : null);
    }
}
